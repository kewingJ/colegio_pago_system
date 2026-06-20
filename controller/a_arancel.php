<?php
		session_start();
		include_once '../includes/config.php';
		include_once '../includes/security.php';
		if (!empty($_POST['id_arancel']) && 
            !empty($_POST['id_categoria']) && 
            !empty($_POST['id_nivel']) && 
            !empty($_POST['id_concepto']) && 
            !empty($_POST['tipo_moneda']) && 
            !empty($_POST['id_anio']) && 
            !empty($_POST['monto'])) {
			
			// guardo los datos
            $id_arancel     = $_POST['id_arancel'];
			$id_categoria   = clean(mysqli_real_escape_string($link,$_POST['id_categoria']));
			$id_nivel       = clean(mysqli_real_escape_string($link,$_POST['id_nivel']));
			$id_concepto    = clean(mysqli_real_escape_string($link,$_POST['id_concepto']));
			$tipo_moneda    = clean(mysqli_real_escape_string($link,$_POST['tipo_moneda']));
			$id_anio        = clean(mysqli_real_escape_string($link,$_POST['id_anio']));
            $monto          = clean(mysqli_real_escape_string($link,$_POST['monto']));

            $query = mysqli_query($link,"UPDATE tbl_aranceles SET IdCategoria = '$id_categoria',
															IdConcepto      = '$id_concepto',
															IdNivel         = '$id_nivel',
                                                            TipoMoneda      = '$tipo_moneda',
                                                            Monto           = '$monto',
                                                            Anio            = '$id_anio' 
															WHERE ID = '$id_arancel'") or die(mysqli_error($link));
            
			echo "bien";
		}else{
			echo "mal";
		}
			
		
?>