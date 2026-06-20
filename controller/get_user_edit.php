<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_usuario'])) 
	{
		$id_usuario = $_POST['id_usuario'];
		$queryUser = mysqli_query($link,"SELECT * FROM tbla_usuario
                                            WHERE id_usuario = '$id_usuario'");
		$rowUser = mysqli_fetch_array($queryUser);

        $nombre     = $rowUser['nombre_usuario'];
        $apellido   = $rowUser['apellido_usuario'];
        $correo     = $rowUser['email_usuario'];
        $tipo_usuario = $rowUser['tipo_usuario'];
        $celular    = $rowUser['telefono'];
?>

                            <form id="Form1" class="FormU" action="" method="" autocomplete="off">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Nombres</label>
                                        <input value="<?php echo $nombre; ?>" type="text" class="form-control" name="nombre" placeholder="Nombres">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Apellidos</label>
                                        <input value="<?php echo $apellido; ?>" type="text" class="form-control" name="apellido" placeholder="Apellidos">
                                        <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                                    </div>

                                    <div class="col-md-12">
                                        <label>Tipo usuario</label>
                                        <div class="input-group date">
                                            <select class="form-control select2" style="width: 100%" name="tipo_usuario">
                                                <option value="">Tipo usuario</option>
                                                <?php
                                                    if($tipo_usuario == 1) {
                                                        echo '<option selected value="1">Administrador</option>';
                                                        echo '<option value="2">Gerencia</option>';
                                                    } else {
                                                        echo '<option value="1">Administrador</option>';
                                                        echo '<option selected value="2">Gerencia</option>';
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label>Celular</label>
                                        <input value="<?php echo $celular; ?>" type="text" class="form-control" name="celular" placeholder="Celular">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label>Correo</label>
                                        <input value="<?php echo $correo; ?>" type="email" class="form-control" name="email" placeholder="Email">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Contraseña</label>
                                        <input type="password" class="form-control" name="pass" placeholder="Contraseña">
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