<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_concepto'])) 
	{
		$id_concepto = $_POST['id_concepto'];
		$queryData = mysqli_query($link,"SELECT * FROM tbl_conceptospago
                                            WHERE IdConcepto = '$id_concepto'");
		$rowData = mysqli_fetch_array($queryData);

        $id_categoria   = $rowData['IdCategoria'];
        $concepto       = $rowData['Concepto'];
        $unidades       = $rowData['unidades'];
?>

                            <form id="Form1" class="FormEditar" action="" method="" autocomplete="off">

                                <div class="form-group col-md-12">
                                    <label>Categoria</label>
                                    <div class="input-group date">
                                        <select data-plugin-selectTwo2 class="form-control" style="width: 100%" id="id_categoria" name="id_categoria">
                                            <option value="">Categoria</option>
                                            <?php
                                                $queryData = mysqli_query($link,"SELECT * FROM tbl_categoriapago");
                                                while($rowData = mysqli_fetch_array($queryData)){
                                                    if($id_categoria == $rowData['Id']){
                                                        echo '<option selected value="'.$rowData['Id'].'">'.$rowData['Concepto'].'</option>';
                                                    } else {
                                                        echo '<option value="'.$rowData['Id'].'">'.$rowData['Concepto'].'</option>';
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group col-md-12">
                                    <label>Concepto</label>
                                    <input value="<?php echo $concepto; ?>" type="text" class="form-control" name="concepto" placeholder="">
                                    <input value="<?php echo $id_concepto; ?>" type="hidden" class="form-control" name="id_concepto" placeholder="">
                                </div>

                                <div class="form-group col-md-12">
                                    <label>Total unidades</label>
                                    <input type="number" class="form-control" name="unidades" value="<?php echo $unidades; ?>" placeholder="">
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