<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_colegio'])) 
	{
		$id_colegio = $_POST['id_colegio'];
		$queryUser = mysqli_query($link,"SELECT * FROM tbl_parametros");
		$rowUser = mysqli_fetch_array($queryUser);

        $nombre         = $rowUser['NOMBRECOLEGIO'];
        $direccion      = $rowUser['DIRECCION'];
        $telefono       = $rowUser['TELEFONOS'];
        $logo           = isset($rowUser['logo_colegio']) ? $rowUser['logo_colegio'] : '';
?>

                            <form id="Form1" class="FormU" action="" method="" autocomplete="off">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Nombre colegio</label>
                                        <input value="<?php echo $nombre; ?>" type="text" class="form-control" name="nombre" placeholder="">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Telefono</label>
                                        <input value="<?php echo $telefono; ?>" type="text" class="form-control" name="telefono" placeholder="">
                                        <input type="hidden" name="id_colegio" value="<?php echo $id_colegio; ?>">
                                    </div>

                                    <div class="col-md-12">
                                        <label>Dirección</label>
                                        <textarea name="direccion" class="form-control" rows="3" placeholder=""><?php echo $direccion; ?></textarea>
                                    </div>

                                    <!-- NUEVO: Logo del colegio -->
                                    <div class="col-md-12 mt-3">
                                        <label>Logo del colegio (arrastrar y soltar o click)</label>

                                        <!-- Preview si ya hay logo -->
                                        <div id="logo-preview" class="mb-2" style="display:<?php echo $logo ? 'block':'none'; ?>">
                                            <img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo Colegio" style="max-height:80px;border:1px solid #ddd;padding:4px;border-radius:6px;">
                                        </div>

                                        <!-- Dropzone -->
                                        <div class="dropzone" id="logoDropzone" data-upload-url="controller/upload_logo.php"></div>

                                        <!-- Guardamos la URL devuelta por el upload -->
                                        <input type="hidden" name="logo_colegio" id="logo_colegio" value="<?php echo htmlspecialchars($logo); ?>">
                                        <small class="text-muted d-block mt-2">Formatos: JPG, PNG, WEBP, GIF. Máx: 3 MB.</small>
                                    </div>

                                    <div class="text-center col-md-12 mt-3">
                                        <button class="btn btn-secondary"
                                            type="reset"
                                            data-bs-dismiss="modal">
                                            <i class="fa fa-times"></i> Cancelar
                                        </button>
                                        <button class="btn btn-primary" type="button" id="btnA"><i class="fa fa-save"></i> Guardar</button>
                                    </div>
                                </div>
                            </form>

<?php
	}
?>