<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_categoria'])) 
	{
		$id_categoria = $_POST['id_categoria'];
		$queryData = mysqli_query($link,"SELECT * FROM tbl_categoriapago
                                            WHERE Id = '$id_categoria'");
		$rowData = mysqli_fetch_array($queryData);

        $concepto    = $rowData['Concepto'];
?>

                            <form id="Form1" class="FormEditar" action="" method="" autocomplete="off">

                                <div class="form-group col-md-12">
                                    <label>Categoria</label>
                                    <input value="<?php echo $concepto; ?>" type="text" class="form-control" name="categoria" placeholder="">
                                    <input value="<?php echo $id_categoria; ?>" type="hidden" class="form-control" name="id_categoria" placeholder="">
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