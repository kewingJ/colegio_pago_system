<?php
    session_start();
	include_once '../includes/config.php';
	include_once '../includes/security.php';

	if (!empty($_POST['host']) &&
        !empty($_POST['port']) &&
        !empty($_POST['email']) &&
        !empty($_POST['subject']) &&
        !empty($_POST['id_email']) &&
        !empty($_POST['mensaje'])) {
        $id_email = $_POST['id_email'];
		$host = clean(mysqli_real_escape_string($link,$_POST['host']));
        $port = clean(mysqli_real_escape_string($link,$_POST['port']));
        $email = clean(mysqli_real_escape_string($link,$_POST['email']));
        $pass = clean(mysqli_real_escape_string($link,$_POST['pass']));
        $subject = clean(mysqli_real_escape_string($link,$_POST['subject']));
        $mensaje = clean(mysqli_real_escape_string($link,$_POST['mensaje']));
				
        if(empty($pass)){
        $query = mysqli_query($link,"UPDATE tbla_email SET host = '$host',
                                                        port = '$port',
                                                        username = '$email',
                                                        subject = '$subject',
                                                        mensaje = '$mensaje'
							WHERE id_email = '$id_email'") or die(mysqli_error($link));
        } else {
            $query = mysqli_query($link,"UPDATE tbla_email SET host = '$host',
                                                        port = '$port',
                                                        username = '$email',
                                                        password = '$pass',
                                                        subject = '$subject',
                                                        mensaje = '$mensaje'
							WHERE id_email = '$id_email'") or die(mysqli_error($link));
        }

		echo "bien";
	} else {
		echo "mal";
	}
?>