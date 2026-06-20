<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_alumno'])) 
	{
		$id_alumno = $_POST['id_alumno'];
		$queryDato = mysqli_query($link,"SELECT * FROM tbl_alumnos
                                            WHERE IDALUMNO = '$id_alumno'");
		$rowDato = mysqli_fetch_array($queryDato);

        $nombre             = $rowDato['NOMBREAPELLIDO'];
        $fechaOriginal      = $rowDato['FECHANACIMIENTO'];
        $mama               = $rowDato['NOMBREMADRE'];
        $papa               = $rowDato['NOMBREPADRE'];
        $direccion          = $rowDato['DIRECCION'];
        $telefono           = $rowDato['TELEFONO'];
        $email              = $rowDato['EMAIL'];
        $foto               = isset($rowDato['FOTO']) ? $rowDato['FOTO'] : ''; // -- AJUSTA NOMBRE COLUMNA

        // 
        $fecha_nacimiento = date("d/m/Y", strtotime($fechaOriginal));
?>

                            <form id="Form1" class="FormEditar" action="" method="" autocomplete="off">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Nombres y apellidos</label>
                                        <input value="<?php echo $nombre; ?>" type="text" class="form-control" name="nombre" placeholder="Nombres y apellidos">
                                        <input type="hidden" name="id_alumno" value="<?php echo $id_alumno; ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Fecha nacimiento</label>
                                        <input value="<?php echo $fecha_nacimiento; ?>" type="text" class="form-control pull-right" id="datepicker_2" name="fecha_nacimiento">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Nombre del papa</label>
                                        <input value="<?php echo $papa; ?>" type="text" class="form-control" name="papa" placeholder="Nombre del papa">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Nombre de la mama</label>
                                        <input value="<?php echo $mama; ?>" type="text" class="form-control" name="mama" placeholder="Nombre de la mama">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Telefono</label>
                                        <input value="<?php echo $telefono; ?>" type="text" class="form-control" name="telefono" placeholder="Telefono">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Correo</label>
                                        <input value="<?php echo $email; ?>" type="email" class="form-control" name="email" placeholder="Email">
                                    </div>

                                    <div class="col-md-12">
                                        <label>Direccion</label>
                                        <textarea name="direccion" class="form-control" rows="3" placeholder=""><?php echo $direccion; ?></textarea>
                                    </div>

                                    <!-- NUEVO: Foto del alumno (editar) -->
                                    <div class="col-md-12 mt-3">
                                        <label>Foto del alumno (arrastrar y soltar o click)</label>

                                        <!-- Preview de la foto actual (si existe) -->
                                        <div id="foto-preview-edit" class="mb-2" style="display:<?php echo $foto ? 'block' : 'none'; ?>;">
                                            <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto del alumno" style="width:120px;height:120px;object-fit:contain;border:1px solid #ddd;padding:4px;border-radius:6px;background:#fff;">
                                        </div>

                                        <!-- Zona Dropzone. OJO: no usamos class="dropzone" para evitar autoinicialización -->
                                        <div class="dz-manual" id="fotoDropzoneEdit" data-upload-url="controller/upload_foto_alumno.php"></div>

                                        <!-- URL devuelta por el upload -->
                                        <input type="hidden" name="foto_alumno_edit" id="foto_alumno_edit" value="<?php echo htmlspecialchars($foto); ?>">

                                        <small class="text-muted d-block mt-2">Formatos: JPG, PNG, WEBP, GIF. Máx: 3 MB. (Se ajusta a 400×400)</small>

                                        <!-- (Opcional) Botón para limpiar foto -->
                                        <!-- <button type="button" class="btn btn-outline-danger btn-sm mt-2" id="btnQuitarFotoEdit">Quitar foto</button> -->
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