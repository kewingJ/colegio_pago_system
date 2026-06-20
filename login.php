<?php
require("includes/config.php");
require("includes/security.php");

error_reporting(E_ALL);
ini_set('display_errors', '1');

if(!empty($_POST['email']) && !empty($_POST['password'])) 
{
	//guardo los datos del usuario y los limpio de cualquier caracter
	$email = mysqli_real_escape_string($link,$_POST['email']);
	$pass = mysqli_real_escape_string($link,$_POST['password']);

    //verificamos en la base de datos si existe el usuario
    $consulta = mysqli_query($link, "SELECT * FROM tbla_usuario WHERE email_usuario='{$email}'");
    $row = mysqli_fetch_array($consulta);
    if($row && is_numeric($row['id_usuario']) && $row['id_usuario'] > 0) 
    {
        $passactual = $row['password_usuario'];
        $passenviada = $pass;
        if(password_verify($passenviada, $passactual))
        {
            session_start();
            $id_usuario = $_SESSION['id_usuario'] = $row['id_usuario'];
            $_SESSION['nombre']         = $row['nombre_usuario'];
            $_SESSION['email_usuario']  = $row['email_usuario'];
            $_SESSION['apellido']       = $row['apellido_usuario'];
            $_SESSION['activo']         = $row['activo_usuario'];
            $_SESSION['tipo_usuario']   = $row['tipo_usuario'];

            //defino la sesión que demuestra que el usuario está autorizado
            $_SESSION["ultimoAcceso"] = date("Y-n-j H:i:s");

            $tipo = $row['tipo_usuario'];
            switch ($tipo) {
                case 2:
                    header("Location: home.php");
                break;
                case 1:
                    header("Location: home.php");
                    //header("Location: index.html");
                break;
            }
            
        } else {
            header("Location: index.html");
        }
    } else {
        header("Location: index.html");
    }
} else {
    header("Location: index.html");
}
?>