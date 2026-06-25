<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'colegio_db_data');
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD,DB_DATABASE) or die(mysqli_error($link));
@mysqli_query($link,"SET NAMES 'utf8'");

// Configuración de la conexión a Redis
$redis_host = '127.0.0.1';
$redis_port = 6379;

$sql_details = array(
    'user' => 'root',
    'pass' => '',
    'db'   => 'colegio_db_data',
    'host' => 'localhost'
);

// enviar correo de alumnos en mora cada 11 de cada mes
$dia_mes = 11;
?>