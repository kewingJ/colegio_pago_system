<?php
include_once 'includes/config.php';
include_once 'includes/security.php';

// Ajusta tus credenciales:
$host     = DB_SERVER;
$usuario  = DB_USERNAME;
$password = DB_PASSWORD;
$bd       = DB_DATABASE;

// Nombre del archivo de respaldo:
$filename = "respaldo_" . date("Y-m-d_H-i-s") . ".sql";

// Cabeceras para forzar la descarga
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Comando mysqldump
// IMPORTANTE: si el servidor requiere ruta absoluta al ejecutable, podrías usar algo como /usr/bin/mysqldump
$comando = "mysqldump --user={$usuario} --password={$password} --host={$host} {$bd}";

// Ejecutamos y enviamos la salida directamente al navegador
passthru($comando);

// Fin del script
?>
