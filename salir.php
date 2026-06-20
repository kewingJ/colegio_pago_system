<?php
//iniciamos la sesion
session_start();
//destruimos la sesion
session_destroy();
//cargamos el index
header("Location: index.html");
?>
