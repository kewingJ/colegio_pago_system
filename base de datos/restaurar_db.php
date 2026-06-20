<?php
declare(strict_types=1);

function out(string $text): void
{
    $stdout = defined('STDOUT') ? STDOUT : fopen('php://output', 'wb');
    if ($stdout !== false) {
        fwrite($stdout, $text . PHP_EOL);
    }
}

function fail(string $text, int $code = 1): void
{
    $stderr = defined('STDERR') ? STDERR : fopen('php://output', 'wb');
    if ($stderr !== false) {
        fwrite($stderr, $text . PHP_EOL);
    }
    exit($code);
}

function extractDefine(string $configText, string $constant): ?string
{
    $pattern = "/define\\(\\s*['\\\"]" . preg_quote($constant, "/") . "['\\\"]\\s*,\\s*['\\\"](.*?)['\\\"]\\s*\\)\\s*;/";
    if (preg_match($pattern, $configText, $matches) === 1) {
        return $matches[1];
    }
    return null;
}

function detectMysqlBinary(): string
{
    $envBin = getenv('MYSQL_BIN');
    if (is_string($envBin) && $envBin !== '' && is_executable($envBin)) {
        return $envBin;
    }

    $fromPath = trim((string) shell_exec('command -v mysql 2>/dev/null'));
    if ($fromPath !== '' && is_executable($fromPath)) {
        return $fromPath;
    }

    $candidates = array(
        '/usr/bin/mysql',
        '/usr/local/bin/mysql',
        '/bin/mysql',
        '/usr/local/mysql/bin/mysql',
        '/opt/homebrew/bin/mysql',
        '/Applications/XAMPP/xamppfiles/bin/mysql',
        '/opt/lampp/bin/mysql',
    );

    foreach ($candidates as $binary) {
        if (is_executable($binary)) {
            return $binary;
        }
    }

    return 'mysql';
}

if (PHP_SAPI !== 'cli') {
    fail("Este script se ejecuta por terminal.\nUso: php 'base de datos/restaurar_db.php' --yes");
}

$scriptDir = __DIR__;
$sqlFile = $scriptDir . DIRECTORY_SEPARATOR . 'db_cole2.sql';
$configFile = dirname($scriptDir) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'config.php';

if (!is_file($sqlFile)) {
    fail("No se encontró el archivo SQL: {$sqlFile}");
}

if (!is_file($configFile)) {
    fail("No se encontró config.php: {$configFile}");
}

$configText = file_get_contents($configFile);
if ($configText === false) {
    fail("No se pudo leer config.php");
}

$host = extractDefine($configText, 'DB_SERVER') ?? 'localhost';
$user = extractDefine($configText, 'DB_USERNAME');
$pass = extractDefine($configText, 'DB_PASSWORD') ?? '';
$database = extractDefine($configText, 'DB_DATABASE');
$socket = extractDefine($configText, 'DB_SOCKET');
$portText = extractDefine($configText, 'DB_PORT');
$port = ($portText !== null && ctype_digit($portText)) ? (int) $portText : 3306;

if ($user === null || $database === null) {
    fail("No se pudieron leer DB_USERNAME o DB_DATABASE desde includes/config.php");
}

if (preg_match('/^[A-Za-z0-9_]+$/', $database) !== 1) {
    fail("Nombre de base de datos no válido: {$database}");
}

if (!in_array('--yes', $argv, true)) {
    out("Este proceso reemplazará COMPLETAMENTE la base de datos '{$database}' con el contenido de:");
    out($sqlFile);
    out('');
    out("Ejecuta de nuevo con confirmación:");
    out("php 'base de datos/restaurar_db.php' --yes");
    exit(0);
}

$mysqlBin = detectMysqlBinary();
$commonPrefix = escapeshellarg($mysqlBin)
    . ' --user=' . escapeshellarg($user)
    . ' --password=' . escapeshellarg($pass);

$connectionVariants = array();
if (is_string($socket) && trim($socket) !== '') {
    $connectionVariants[] = '--socket=' . escapeshellarg(trim($socket));
} elseif (strpos($host, '/') === 0) {
    $connectionVariants[] = '--socket=' . escapeshellarg($host);
} else {
    $connectionVariants[] = '--host=' . escapeshellarg($host) . ' --port=' . (int) $port;
}

if (strtolower($host) === 'localhost') {
    $tcpVariant = '--host=' . escapeshellarg('127.0.0.1') . ' --port=' . (int) $port . ' --protocol=TCP';
    if (!in_array($tcpVariant, $connectionVariants, true)) {
        $connectionVariants[] = $tcpVariant;
    }
}

$common = '';
$connectErrors = array();
foreach ($connectionVariants as $variant) {
    $tmpErrTest = tempnam(sys_get_temp_dir(), 'db_test_');
    if ($tmpErrTest === false) {
        fail("No se pudieron crear archivos temporales para validar conexión.");
    }

    $testCmd = $commonPrefix
        . ' ' . $variant
        . ' --execute=' . escapeshellarg('SELECT 1;')
        . ' 2> ' . escapeshellarg($tmpErrTest);

    $out = array();
    $code = 0;
    exec($testCmd, $out, $code);

    $err = trim((string) file_get_contents($tmpErrTest));
    @unlink($tmpErrTest);

    if ($code === 0) {
        $common = $commonPrefix . ' ' . $variant;
        break;
    }

    $connectErrors[] = ($err !== '') ? $err : "Error de conexión sin detalle para variante: {$variant}";
}

if ($common === '') {
    fail("No se pudo conectar a MySQL con la configuración actual.\n" . implode("\n\n", $connectErrors), 2);
}

$tmpErrDropCreate = tempnam(sys_get_temp_dir(), 'db_restore_');
$tmpErrImport = tempnam(sys_get_temp_dir(), 'db_import_');

if ($tmpErrDropCreate === false || $tmpErrImport === false) {
    fail("No se pudieron crear archivos temporales para el proceso.");
}

$dropCreateSql = "DROP DATABASE IF EXISTS `{$database}`; CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;";
$dropCreateCmd = $common
    . ' --execute=' . escapeshellarg($dropCreateSql)
    . ' 2> ' . escapeshellarg($tmpErrDropCreate);

out("1/2 Recreando base de datos '{$database}'...");
$out = array();
$code = 0;
exec($dropCreateCmd, $out, $code);

if ($code !== 0) {
    $err = trim((string) file_get_contents($tmpErrDropCreate));
    @unlink($tmpErrDropCreate);
    @unlink($tmpErrImport);
    fail("Error al recrear la base de datos.\n" . $err, 2);
}

$importCmd = $common
    . ' --database=' . escapeshellarg($database)
    . ' < ' . escapeshellarg($sqlFile)
    . ' 2> ' . escapeshellarg($tmpErrImport);

out("2/2 Importando respaldo...");
$out = array();
$code = 0;
exec($importCmd, $out, $code);

if ($code !== 0) {
    $err = trim((string) file_get_contents($tmpErrImport));
    @unlink($tmpErrDropCreate);
    @unlink($tmpErrImport);
    fail("Error al importar db_cole2.sql.\n" . $err, 3);
}

@unlink($tmpErrDropCreate);
@unlink($tmpErrImport);

out("Listo. Base de datos '{$database}' reemplazada correctamente.");
