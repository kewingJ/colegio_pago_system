<?php
// controller/upload_logo.php
session_start();
include_once '../includes/config.php';
include_once '../includes/security.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_FILES['file'])) {
    echo json_encode(['status'=>'error','msg'=>'Archivo no recibido']);
    exit;
}

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status'=>'error','msg'=>'Error al subir: '.$file['error']]);
    exit;
}

// ----- Validaciones -----
$maxBytes = 3 * 1024 * 1024; // 3MB
if ($file['size'] > $maxBytes) {
    echo json_encode(['status'=>'error','msg'=>'Archivo demasiado grande (máx 3MB)']);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($file['tmp_name']);
$allowedMimes = ['image/jpeg','image/png','image/webp','image/gif'];
if (!in_array($mime, $allowedMimes)) {
    echo json_encode(['status'=>'error','msg'=>'Formato no permitido. Use JPG, PNG, WEBP o GIF.']);
    exit;
}

// Dimensiones mínimas
$info = getimagesize($file['tmp_name']);
if (!$info) {
    echo json_encode(['status'=>'error','msg'=>'No se pudo leer la imagen.']);
    exit;
}
list($w, $h) = $info;
if ($w < 400 || $h < 400) {
    echo json_encode(['status'=>'error','msg'=>'La imagen debe ser de al menos 400×400 píxeles.']);
    exit;
}

// ----- Crear recurso GD desde el archivo -----
switch ($mime) {
    case 'image/jpeg':
        $src = imagecreatefromjpeg($file['tmp_name']);
        $ext = 'jpg';
        break;
    case 'image/png':
        $src = imagecreatefrompng($file['tmp_name']);
        $ext = 'png';
        break;
    case 'image/webp':
        // require PHP con soporte WEBP
        if (!function_exists('imagecreatefromwebp')) {
            echo json_encode(['status'=>'error','msg'=>'Servidor sin soporte WEBP.']);
            exit;
        }
        $src = imagecreatefromwebp($file['tmp_name']);
        $ext = 'webp';
        break;
    case 'image/gif':
        $src = imagecreatefromgif($file['tmp_name']); // primer frame
        $ext = 'gif';
        break;
    default:
        echo json_encode(['status'=>'error','msg'=>'Formato no permitido.']);
        exit;
}
if (!$src) {
    echo json_encode(['status'=>'error','msg'=>'No se pudo procesar la imagen.']);
    exit;
}

// ----- Crop centrado a cuadrado -----
$side  = min($w, $h);
$srcX  = (int) floor(($w - $side) / 2);
$srcY  = (int) floor(($h - $side) / 2);

$dstW = 400;
$dstH = 400;
$dst  = imagecreatetruecolor($dstW, $dstH);

// Preservar transparencia en PNG/WebP/GIF
if (in_array($mime, ['image/png','image/webp','image/gif'])) {
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transColor = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefilledrectangle($dst, 0, 0, $dstW, $dstH, $transColor);
}

$okCopy = imagecopyresampled(
    $dst,   // destino
    $src,   // origen
    0, 0,   // destino x,y
    $srcX, $srcY, // origen recorte x,y
    $dstW, $dstH, // tamaño final
    $side, $side  // tamaño del recorte
);
if (!$okCopy) {
    imagedestroy($src);
    imagedestroy($dst);
    echo json_encode(['status'=>'error','msg'=>'No se pudo redimensionar la imagen.']);
    exit;
}

// ----- Asegurar carpeta uploads -----
$root = realpath(__DIR__ . '/..'); // /controller/.. -> raíz del proyecto
$uploadDir = $root . DIRECTORY_SEPARATOR . 'uploads';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0775, true);
}

// Nombre final
$base = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
$uniq = $base . '_400x400_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));
$destPath = $uploadDir . DIRECTORY_SEPARATOR . $uniq . '.' . $ext;

// ----- Guardar según formato -----
$saveOk = false;
switch ($mime) {
    case 'image/jpeg':
        $saveOk = imagejpeg($dst, $destPath, 85);
        break;
    case 'image/png':
        // compresión 0 (sin) a 9 (máx)
        $saveOk = imagepng($dst, $destPath, 6);
        break;
    case 'image/webp':
        if (!function_exists('imagewebp')) {
            imagedestroy($src);
            imagedestroy($dst);
            echo json_encode(['status'=>'error','msg'=>'Servidor sin soporte WEBP.']);
            exit;
        }
        $saveOk = imagewebp($dst, $destPath, 85);
        break;
    case 'image/gif':
        // GIF quedará estático (primer frame) y cuadrado
        $saveOk = imagegif($dst, $destPath);
        break;
}

imagedestroy($src);
imagedestroy($dst);

if (!$saveOk) {
    echo json_encode(['status'=>'error','msg'=>'No se pudo guardar la imagen procesada.']);
    exit;
}

// URL relativa servible por el front
$urlRel = 'uploads/' . basename($destPath);
echo json_encode(['status'=>'ok', 'url'=>$urlRel]);
