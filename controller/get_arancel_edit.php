<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_arancel'])) 
	{
		$id_arancel = $_POST['id_arancel'];
		$queryData = mysqli_query($link,"SELECT * FROM tbl_aranceles
                                            WHERE ID = '$id_arancel'");
		$rowData = mysqli_fetch_array($queryData);

        $id_categoria   = $rowData['IdCategoria'];
        $id_concepto    = $rowData['IdConcepto'];
        $id_nivel       = $rowData['IdNivel'];
        $tipo_moneda    = $rowData['TipoMoneda'];
        $monto          = $rowData['Monto'];
        $id_anio        = $rowData['Anio'];
?>

                            <form id="Form1" class="FormEditar" action="" method="" autocomplete="off">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Categoria</label>
                                        <div class="input-group date">
                                            <select data-plugin-selectTwo2 class="form-control" style="width: 100%" id="id_categoria_edit" name="id_categoria">
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

                                    <div class="col-md-6">
                                        <label>Nivel</label>
                                        <div class="input-group date">
                                            <select data-plugin-selectTwo2 class="form-control" style="width: 100%" name="id_nivel">
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

                                    <div class="col-md-6">
                                        <label>Concepto</label>
                                        <div class="input-group date">
                                            <select data-plugin-selectTwo2 class="form-control" style="width: 100%" id="id_concepto_edit" name="id_concepto">
                                                <option value="">Concepto</option>
                                                <?php
                                                    $queryData = mysqli_query($link,"SELECT * FROM tbl_conceptospago");
                                                    while($rowData = mysqli_fetch_array($queryData)){
                                                        if($id_concepto == $rowData['IdConcepto']){
                                                            echo '<option selected value="'.$rowData['IdConcepto'].'">'.$rowData['Concepto'].'</option>';
                                                        } else {
                                                            echo '<option value="'.$rowData['IdConcepto'].'">'.$rowData['Concepto'].'</option>';
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label>Tipo moneda</label>
                                        <div class="input-group date">
                                            <select data-plugin-selectTwo2 class="form-control" style="width: 100%" name="tipo_moneda">
                                                <option value="">Tipo moneda</option>
                                                <?php
                                                    if($tipo_moneda == 'US$'){
                                                        echo ' <option selected value="US$">US$</option>
                                                        <option value="C$">C$</option>';
                                                    } else {
                                                        echo ' <option value="US$">US$</option>
                                                        <option selected value="C$">C$</option>';
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label>Monto</label>
                                        <input value="<?php echo $monto; ?>" type="text" class="form-control" name="monto" placeholder="">
                                        <input value="<?php echo $id_arancel; ?>" type="hidden" class="form-control" name="id_arancel" placeholder="">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Año lectivo</label>
                                        <div class="input-group date">
                                            <select data-plugin-selectTwo2 class="form-control" style="width: 100%" name="id_anio">
                                                <option value="">Año lectivo</option>
                                                <?php
                                                    $queryData = mysqli_query($link,"SELECT * FROM tbl_anios ORDER BY idanio DESC");
                                                    while($rowData = mysqli_fetch_array($queryData)){
                                                        if($id_anio == $rowData['anio']){
                                                            echo '<option selected value="'.$rowData['anio'].'">'.$rowData['anio'].'</option>';
                                                        } else {
                                                            echo '<option value="'.$rowData['anio'].'">'.$rowData['anio'].'</option>';
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
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
                                </div>
                            </form>

                            <!-- consulta al seleccionar categoria -->
                            <script>
                                $(document).ready(function() {
                                    $('#id_categoria_edit').change(function(e) {
                                        e.preventDefault();
                                        var id_categoria = $(this).val();
                                        // alert(id_usuario);
                                        $('#id_concepto_edit').html('');  
                                        $.ajax({
                                            url: 'controller/get_concepto_list.php',
                                            type: 'POST',
                                            data: 'id_categoria='+id_categoria,
                                            dataType: 'html'
                                        })
                                        .done(function(data){  
                                            $('#id_concepto_edit').html('');    
                                            $('#id_concepto_edit').html(data); // mostrar la data
                                        })
                                        .fail(function(){
                                            $('#id_concepto_edit').html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
                                        });
                                    });
                                });
                            </script>

<?php
	}
?>