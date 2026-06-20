<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_seccion'])) 
	{
		$id_seccion = $_POST['id_seccion'];
		$queryData = mysqli_query($link,"SELECT * FROM tbl_secciones
                                            WHERE ID = '$id_seccion'");
		$rowData = mysqli_fetch_array($queryData);

        $seccion    = $rowData['SECCION'];
?>

                            <form id="Form1" class="FormEditar" action="" method="" autocomplete="off">

                                <div class="form-group col-md-12">
                                    <label>Seccion</label>
                                    <input value="<?php echo $seccion; ?>" type="text" class="form-control" name="seccion" placeholder="">
                                    <input value="<?php echo $id_seccion; ?>" type="hidden" class="form-control" name="id_seccion" placeholder="">
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