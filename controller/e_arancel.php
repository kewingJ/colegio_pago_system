<?php
	session_start();
	if(!empty($_POST['id_arancel']))
	{
		include_once '../includes/config.php';
		include_once '../includes/security.php';

		$id_arancel = $_POST['id_arancel'];

        $query = mysqli_query($link,"DELETE FROM tbl_aranceles WHERE ID = '$id_arancel'") or die(mysqli_error($link));

		echo "bien";
	}
	else {
		echo "mal";
		exit;
	}
?>