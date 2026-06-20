<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_grado'])) 
	{
		$id_grado = $_POST['id_grado'];
		$queryData = mysqli_query($link,"SELECT * FROM tbl_grados
                                            WHERE IdGrados = '$id_grado'");
		$rowData = mysqli_fetch_array($queryData);

        $id_nivel   = $rowData['IdNivel'];
        $grado       = $rowData['Grado'];
        $nivel       = $rowData['Nivel'];
        $cupo        = $rowData['total_cupo'];
?>

                            <form id="Form1" class="FormEditar" action="" method="" autocomplete="off">

                                <div class="form-group col-md-12">
                                    <label>Nivel</label>
                                    <div class="input-group date">
                                        <select data-plugin-selectTwo2 class="form-control" style="width: 100%" id="id_nivel" name="id_nivel">
                                            <option value="">Nivel</option>
                                            <?php
                                                $queryData = mysqli_query($link,"SELECT * FROM tbl_nivel");
                                                while($rowData = mysqli_fetch_array($queryData)){
                                                    if($id_nivel == $rowData['IdNivel']){
                                                        echo '<option selected value="'.$rowData['IdNivel'].'">'.$rowData['Nivel'].'</option>';
                                                    } else {
                                                        echo '<option value="'.$rowData['IdNivel'].'">'.$rowData['Nivel'].'</option>';
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group col-md-12">
                                    <label>Grado</label>
                                    <input value="<?php echo $grado; ?>" type="text" class="form-control" name="grado" placeholder="">
                                    <input value="<?php echo $id_grado; ?>" type="hidden" class="form-control" name="id_grado" placeholder="">
                                </div>

                                <div class="form-group col-md-12">
                                    <label>Total cupos</label>
                                    <input value="<?php echo $cupo; ?>" type="number" class="form-control" name="cupo" placeholder="">
                                </div>

                                <div class="text-center col-md-12 mt-3">
                                    <button class="btn btn-secondary"
                                            type="reset"
                                            data-bs-dismiss="modal">
                                        <i class="fa fa-times"></i> Cancelar
                                    </button>

                                    <button class="btn btn-primary"
                                            type="button"
                                            id="btnA">
                                        <i class="fa fa-save"></i> Guardar
                                    </button>
                                </div>
                            </form>

<?php
	}
?>