<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_nivel'])) 
	{
		$id_nivel = $_POST['id_nivel'];
		$queryData = mysqli_query($link,"SELECT * FROM tbl_nivel
                                            WHERE IdNivel = '$id_nivel'");
		$rowData = mysqli_fetch_array($queryData);

        $nivel    = $rowData['Nivel'];
?>

                            <form id="Form1" class="FormEditar" action="" method="" autocomplete="off">

                                <div class="form-group col-md-12">
                                    <label>Nivel</label>
                                    <input value="<?php echo $nivel; ?>" type="text" class="form-control" name="nivel" placeholder="">
                                    <input value="<?php echo $id_nivel; ?>" type="hidden" class="form-control" name="id_nivel" placeholder="">
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