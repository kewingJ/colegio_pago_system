<?php
    session_start();
	include_once '../includes/config.php';
	include_once '../includes/security.php';
	if (!empty($_POST['id_grado']) &&
        !empty($_POST['id_nivel']) &&
        !empty($_POST['grado'])) {

        $id_grado = $_POST['id_grado'];
        $id_nivel = $_POST['id_nivel'];
        $cupo = $_POST['cupo'];
		//obtener el nombre del nivel
        $queryData = mysqli_query($link,"SELECT * FROM tbl_nivel WHERE IdNivel = '$id_nivel'");
        $rowData = mysqli_fetch_array($queryData);
        $nivel = $rowData['Nivel'];

		// guardo los datos
		$grado = clean(mysqli_real_escape_string($link,$_POST['grado']));

        // datos en mayuscula
        $grado = strtoupper($grado);
        $nivel = strtoupper($nivel);
				
        $query = mysqli_query($link,"UPDATE tbl_grados SET IdNivel = '$id_nivel',
                                                                Grado = '$grado',
                                                                Nivel = '$nivel',
                                                                total_cupo = '$cupo'
							WHERE IdGrados = '$id_grado'") or die(mysqli_error($link));

		echo "bien";
	} else {
		echo "mal";
	}
?>