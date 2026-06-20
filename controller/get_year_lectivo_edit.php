<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_anio'])) 
	{
		$id_anio = $_POST['id_anio'];
		$queryData = mysqli_query($link,"SELECT * FROM tbl_anios
                                            WHERE idanio = '$id_anio'");
		$rowData = mysqli_fetch_array($queryData);

        $estado  = $rowData['estado'];
        $year    = $rowData['anio'];
?>

                            <form id="Form1" class="FormEditar" action="" method="" autocomplete="off">

                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Año lectivo</label>
                                        <select disabled="true" class="form-control select2" style="width: 100%" name="year">
                                            <option value="">Años</option>
                                            <?php
                                                $anio_actual = date("Y");
                                                // Generar opciones para los próximos 4 años
                                                for ($i = 0; $i < 5; $i++) {
                                                    $anio = $anio_actual + $i;
                                                    if($year == $anio){
                                                        echo '<option selected value="'.$anio.'">'.$anio.'</option>';
                                                    } else {
                                                        echo '<option value="'.$anio.'">'.$anio.'</option>';
                                                    }
                                                }
                                            ?>
                                        </select>
                                        <input type="hidden" value="<?php echo $id_anio; ?>" name="id_anio">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Estado</label>
                                        <div class="input-group date">
                                            <select class="form-control" style="width: 100%" name="id_estado">
                                                <option value="">Estados</option>
                                                <?php
                                                    if($estado == 1){
                                                        echo '
                                                        <option selected value="Activo">Activo</option>
                                                        <option value="Inactivo">Inactivo</option>';
                                                    } else {
                                                        echo '
                                                        <option value="Activo">Activo</option>
                                                        <option selected value="Inactivo">Inactivo</option>';
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

<?php
	}
?>