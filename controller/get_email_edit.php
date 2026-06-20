<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_email'])) 
	{
		$id_email = $_POST['id_email'];
		$queryUser = mysqli_query($link,"SELECT * FROM tbla_email WHERE id_email = '$id_email'");
		$rowUser = mysqli_fetch_array($queryUser);

        $host       = $rowUser['host'];
        $port       = $rowUser['port'];
        $username   = $rowUser['username'];
        $password   = $rowUser['password'];
        $subject    = $rowUser['subject'];
        $mensaje    = $rowUser['mensaje'];
?>

                            <form id="Form1" class="FormU" action="" method="" autocomplete="off">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Host email</label>
                                        <input value="<?php echo $host; ?>" type="text" class="form-control" name="host" placeholder="">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Port email</label>
                                        <input value="<?php echo $port; ?>" type="text" class="form-control" name="port" placeholder="">
                                        <input type="hidden" name="id_email" value="<?php echo $id_email; ?>">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label>Username</label>
                                        <input value="<?php echo $username; ?>" type="email" class="form-control" name="email" placeholder="">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Contraseña</label>
                                        <input type="password" class="form-control" name="pass" placeholder="Contraseña">
                                    </div>

                                    <div class="col-md-12">
                                        <label>Subject</label>
                                        <input value="<?php echo $subject; ?>" type="text" class="form-control" name="subject" placeholder="">
                                    </div>

                                    <div class="col-md-12">
                                        <label>Mensaje</label>
                                        <textarea name="mensaje" class="form-control" rows="3" placeholder=""><?php echo $mensaje; ?></textarea>
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