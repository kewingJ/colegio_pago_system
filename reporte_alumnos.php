<?php
    include_once 'includes/config.php';
    include_once 'includes/security.php';

    date_default_timezone_set('America/Managua');
    
    session_start();
    $id         = $_SESSION['id_usuario'];
    $nombre     = $_SESSION['nombre'];
    $apellido   = $_SESSION['apellido'];
    $activo     = $_SESSION['activo'];
    $tipo       = $_SESSION['tipo_usuario'];
    $ano_actual = date('Y');
    $mes_actual = date('m');
    $mes_actual_str = date('M');
    
    $consult = mysqli_query($link,"SELECT * FROM tbla_usuario WHERE id_usuario = '$id'");
    $row = mysqli_fetch_array($consult);
    
    if (empty($id) || empty($activo)) {
        header("Location: index.html");
    }

    $query = mysqli_query($link,"SELECT b.ID AS IdInscripcion, c.NOMBREAPELLIDO
                FROM tbl_matricula AS a
                INNER JOIN tbl_inscripcion AS b
                ON a.ID = b.IDMATRICULA
                INNER JOIN tbl_alumnos AS c
                ON a.IDALUMNO = c.IDALUMNO
                WHERE a.ANIO = '$ano_actual'");
    $total_alumnos_mora = 0;
    $contador = 0;
    while($rowDatos = mysqli_fetch_array($query))
    {
        $IdInscripcion = $rowDatos['IdInscripcion'];
        $nombre = $rowDatos['NOMBREAPELLIDO'];

        $queryDos = mysqli_query($link,"SELECT * FROM tbl_pagosmensualidades
                    WHERE IdInscripcion = '$IdInscripcion'");
        $rowDatosDos = mysqli_fetch_array($queryDos);
        $total_mes_mora = 0;
        $meses_mora = '';
        for ($i = 1; $i <= $mes_actual; $i++) {
            switch ($i) {
                case 1:
                    $mes = "Ene";
                    break;
                case 2:
                    $mes = "Feb";
                    break;
                case 3:
                    $mes = "Mar";
                    break;
                case 4:
                    $mes = "Abr";
                    break;
                case 5:
                    $mes = "May";
                    break;
                case 6:
                    $mes = "Jun";
                    break;
                case 7:
                    $mes = "Jul";
                    break;
                case 8:
                    $mes = "Ago";
                    break;
                case 9:
                    $mes = "Sep";
                    break;
                case 10:
                    $mes = "Oct";
                    break;
                case 11:
                    $mes = "Nov";
                    break;
                case 12:
                    $mes = "Dic";
                    break;
            }

            if($rowDatosDos[$mes] != 'X'){
                $total_mes_mora++;
                $meses_mora .= $mes;
            }
        }

        if($total_mes_mora > 0){
            $total_alumnos_mora++;

            $datos_alumnos_mora[$contador] = array(
                "nombre" => $resultado = mb_convert_case(mb_strtolower($nombre), MB_CASE_TITLE, "UTF-8"),
                "total_mes" => $total_mes_mora
            );
            $contador++;
            // echo $total_alumnos_mora.' - '.$nombre.' - '.$IdInscripcion.' - '.$total_mes_mora.' - '.$meses_mora.'<br>';
        }
    }
?>
<!doctype html>
<html class="fixed">
	<head>

		<!-- Basic -->
		<meta charset="UTF-8">

		<title>Reporte alumnos</title>

		<meta name="keywords" content="HTML5 Admin Template" />
		<meta name="description" content="Porto Admin - Responsive HTML5 Template">
		<meta name="author" content="okler.net">

		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<!-- Web Fonts  -->
		<link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

		<!-- Vendor CSS -->
		<link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="assets/vendor/animate/animate.compat.css">
		<link rel="stylesheet" href="assets/vendor/font-awesome/css/all.min.css" />
		<link rel="stylesheet" href="assets/vendor/boxicons/css/boxicons.min.css" />
		<link rel="stylesheet" href="assets/vendor/magnific-popup/magnific-popup.css" />
		<link rel="stylesheet" href="assets/vendor/bootstrap-datepicker/css/bootstrap-datepicker3.css" />
        <link rel="stylesheet" href="assets/vendor/jquery-ui/jquery-ui.css" />
		<link rel="stylesheet" href="assets/vendor/jquery-ui/jquery-ui.theme.css" />
		<link rel="stylesheet" href="assets/vendor/select2/css/select2.css" />
		<link rel="stylesheet" href="assets/vendor/select2-bootstrap-theme/select2-bootstrap.min.css" />
		<link rel="stylesheet" href="assets/vendor/datatables/media/css/dataTables.bootstrap5.css" />
		<link rel="stylesheet" href="assets/vendor/bootstrap-multiselect/css/bootstrap-multiselect.css" />
		<link rel="stylesheet" href="assets/vendor/bootstrap-tagsinput/bootstrap-tagsinput.css" />
		<link rel="stylesheet" href="assets/vendor/bootstrap-timepicker/css/bootstrap-timepicker.css" />
		<link rel="stylesheet" href="assets/vendor/dropzone/basic.css" />
		<link rel="stylesheet" href="assets/vendor/dropzone/dropzone.css" />
		<link rel="stylesheet" href="assets/vendor/bootstrap-markdown/css/bootstrap-markdown.min.css" />
		<link rel="stylesheet" href="assets/vendor/summernote/summernote-bs4.css" />

		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Titillium+Web:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">

		<!-- Theme CSS -->
		<link rel="stylesheet" href="assets/css/theme.css" />

		<!-- Skin CSS -->
		<link rel="stylesheet" href="assets/css/skins/default.css" />

		<!-- Theme Custom CSS -->
		<link rel="stylesheet" href="assets/css/custom.css">

		<!-- Head Libs -->
		<script src="assets/vendor/modernizr/modernizr.js"></script>

        <!-- bootstrap datepicker -->
        <link rel="stylesheet" href="assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">

        <script type="text/javascript" src="js/notifIt.js"></script>
        <link rel="stylesheet" type="text/css" href="css/notifIt.css">
        
        <script>
            function not1(){
                notif({
                    msg: "Se guardo correctamente",
                    type: "success",
                    position: "center"
                });
            }
        </script>

        <script>
            function not2(){
                notif({
                    msg: "Algunos campos estan vacios",
                    type: "error",
                    position: "center"
                });
            }
        </script>

        <script>
            function not3(){
                notif({
                    msg: "Los datos se actualizarón correctamente",
                    type: "success",
                    position: "center"
                });
            }
        </script>

        <script>
            function not4(){
                notif({
                    msg: "Se elimino correctamente",
                    type: "success",
                    position: "center"
                });
            }
        </script>
        
        <script>
            function not5(){
                notif({
                    msg: "Error! algo salio mal",
                    type: "error",
                    position: "center"
                });
            }
        </script>

	</head>
	<body>
		<section class="body">

			<!-- start: header -->
			<header class="header">
				<div class="logo-container">
					<a href="home.php" class="logo">
						<img src="img/Logo-SYSCPE.png" width="75" height="40" alt="Porto Admin" />
					</a>

					<div class="d-md-none toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html" data-fire-event="sidebar-left-opened">
						<i class="fas fa-bars" aria-label="Toggle sidebar"></i>
					</div>

				</div>

				<!-- start: search & user box -->
				<div class="header-right">

					<ul class="notifications">
						<li>
							<a href="#" class="dropdown-toggle notification-icon" data-bs-toggle="dropdown">
								<i class="bx bx-bell"></i>
								<span class="badge">
                                <?php
                                    if($total_alumnos_mora > 0){
                                        echo "+";
                                    }
                                ?>
                                </span>
							</a>

							<div class="dropdown-menu notification-menu">
								<div class="notification-title">
									<span class="float-end badge badge-default"><?php echo $total_alumnos_mora; ?></span>
									Alumnos en mora
								</div>

								<div class="content" style="overflow: visible;height: 500px;overflow-y: scroll;scroll-behavior: smooth;">
									<ul>
                                        <?php
                                            if($total_alumnos_mora > 0){
                                                foreach ($datos_alumnos_mora as $item) {
                                                    $mes = "";
                                                    if($item['total_mes'] > 1){
                                                        $mes = "meses";
                                                    } else {
                                                        $mes = "mes";
                                                    }
                                                    echo '
                                                    <li>
                                                        <a href="#" class="clearfix">
                                                            <div class="image">
                                                                <i class="fas fa-user bg-danger text-light"></i>
                                                            </div>
                                                            <span class="title">'.$item['nombre'].'</span>
                                                            <span class="message">'.$item['total_mes'].' '.$mes.' de mora de pago</span>
                                                        </a>
                                                    </li>';
                                                }
                                            }
                                        ?>
									</ul>

									<hr />

									<div class="text-end">
										<a href="#" class="view-more">View All</a>
									</div>
								</div>
							</div>
						</li>
					</ul>

					<span class="separator"></span>

				</div>
				<!-- end: search & user box -->
			</header>
			<!-- end: header -->

			<div class="inner-wrapper">
				<!-- start: sidebar -->
				<aside id="sidebar-left" class="sidebar-left">

				    <div class="sidebar-header">
				        <div class="sidebar-title">
				            Menu
				        </div>
				        <div class="sidebar-toggle d-none d-md-block" data-toggle-class="sidebar-left-collapsed" data-target="html" data-fire-event="sidebar-left-toggle">
				            <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
				        </div>
				    </div>

				    <div class="nano">
				        <div class="nano-content">
				            <nav id="menu" class="nav-main" role="navigation">

				                <ul class="nav nav-main">
				                    <li>
				                        <a class="nav-link" href="home.php">
				                            <i class="bx bx-home-alt" aria-hidden="true"></i>
				                            <span>Dashboard</span>
				                        </a>                        
				                    </li>
                                    <!-- facturacion -->
				                    <li class="nav-parent">
				                        <a class="nav-link" href="#">
				                            <i class="bx bx-detail" aria-hidden="true"></i>
				                            <span>Facturación</span>
				                        </a>
				                        <ul class="nav nav-children">
				                            <li>
				                                <a class="nav-link" href="crear_recibos.php">
				                                    Crear Recibos
				                                </a>
				                            </li>
				                            <li>
				                                <a class="nav-link" href="inscripcion_alumno.php">
				                                    Inscripción de alumnos
				                                </a>
				                            </li>
				                            <li>
				                                <a class="nav-link" href="recibos.php">
				                                    Recibos
				                                </a>
				                            </li>
				                        </ul>
				                    </li>
                                    <!-- reporte -->
                                    <li class="nav-parent nav-expanded">
				                        <a class="nav-link" href="#">
				                            <i class="bx bx-file" aria-hidden="true"></i>
				                            <span>Reportes</span>
				                        </a>
				                        <ul class="nav nav-children">
				                            <li>
				                                <a class="nav-link" href="reporte_categoria.php">
				                                    Pagos por categorias
				                                </a>
				                            </li>
				                            <li>
				                                <a class="nav-link" href="reporte_transporte.php">
				                                    Pagos de transporte
				                                </a>
				                            </li>
				                            <li>
				                                <a class="nav-link" href="reporte_grados.php">
				                                    Pagos por grados
				                                </a>
				                            </li>
                                            <li class="nav-active">
				                                <a class="nav-link" href="reporte_alumnos.php">
				                                    Pagos por alumnos
				                                </a>
				                            </li>
                                            <li>
				                                <a class="nav-link" href="reporte_facturas.php">
				                                    Caja general
				                                </a>
				                            </li>
				                        </ul>
				                    </li>
                                    <!-- catalogo -->
                                    <li class="nav-parent">
				                        <a class="nav-link" href="#">
				                            <i class="bx bx-table" aria-hidden="true"></i>
				                            <span>Menu</span>
				                        </a>
				                        <ul class="nav nav-children">
				                            <li>
				                                <a class="nav-link" href="alumnos.php">
				                                    Alumnos
				                                </a>
				                            </li>
				                            <li>
				                                <a class="nav-link" href="aranceles.php">
				                                    Aranceles
				                                </a>
				                            </li>
				                            <li>
				                                <a class="nav-link" href="categoria_pago.php">
				                                    Categorias de pago
				                                </a>
				                            </li>
                                            <li>
				                                <a class="nav-link" href="concepto_pago.php">
				                                    Conceptos de pago
				                                </a>
				                            </li>
                                            <li>
				                                <a class="nav-link" href="niveles.php">
				                                    Niveles
				                                </a>
				                            </li>
											<li>
				                                <a class="nav-link" href="secciones.php">
				                                    Secciones
				                                </a>
				                            </li>
											<li>
				                                <a class="nav-link" href="grados.php">
				                                    Grados
				                                </a>
				                            </li>
                                            <?php
											if ($_SESSION['tipo_usuario'] == 2) 
											{
											?>
                                            <li>
				                                <a class="nav-link" href="year_lectivo.php">
				                                    Activar año lectivo
				                                </a>
				                            </li>
											<?php
											}
											?>
				                        </ul>
				                    </li>
                                    <!-- prematricula -->
                                    <li>
				                        <a class="nav-link" href="pre_matricula.php">
				                            <i class="fa fa-users" aria-hidden="true"></i>
				                            <span>Pre-Matricula</span>
				                        </a>                        
				                    </li>
                                    <!-- usuarios -->
                                    <?php
									if ($_SESSION['tipo_usuario'] == 2) 
									{
									?>
                                    <li>
				                        <a class="nav-link" href="usuarios.php">
				                            <i class="bx bx-user" aria-hidden="true"></i>
				                            <span>Usuarios</span>
				                        </a>                        
				                    </li>
									<?php
									}
									?>
                                    <!-- emails -->
                                    <?php
									if ($_SESSION['tipo_usuario'] == 2) 
									{
									?>
                                    <li>
				                        <a class="nav-link" href="config_email.php">
				                            <i class="bx bx-envelope" aria-hidden="true"></i>
				                            <span>Config Email</span>
				                        </a>                        
				                    </li>
									<?php
									}
									?>
                                    <?php
									if ($_SESSION['tipo_usuario'] == 2) 
									{
									?>
										<!-- edit colegio -->
										<li>
											<a class="nav-link" href="config_colegio.php">
												<i class="fa fa-school" aria-hidden="true"></i>
												<span>Config Colegio</span>
											</a>                        
										</li>
									<?php
									}
									?>
									<?php
									if ($_SESSION['tipo_usuario'] == 2) 
									{
									?>
										<!-- limpiar data solo admin -->
										<li>
											<a class="nav-link" id="btnEliminarData">
												<i class="fa fa-trash" href="#0" aria-hidden="true"></i>
												<span>Limpiar Data</span>
											</a>                        
										</li>
									<?php
									}
									?>
									<?php
									if ($_SESSION['tipo_usuario'] == 2) 
									{
									?>
										<!-- limpiar data solo admin -->
										<li>
											<a href="backup.php">
												<i class="fa fa-download" href="#0" aria-hidden="true"></i>
												<span>Descargar respaldo BD</span>
											</a>                        
										</li>
									<?php
									}
									?>
                                    <!-- salir -->
                                    <li>
				                        <a class="nav-link" href="salir.php">
				                            <i class="fa fa-sign-out" aria-hidden="true"></i>
				                            <span>Cerrar sesión</span>
				                        </a>                        
				                    </li>
				                </ul>
				            </nav>
				        </div>

				        <script>
				            // Maintain Scroll Position
				            if (typeof localStorage !== 'undefined') {
				                if (localStorage.getItem('sidebar-left-position') !== null) {
				                    var initialPosition = localStorage.getItem('sidebar-left-position'),
				                        sidebarLeft = document.querySelector('#sidebar-left .nano-content');

				                    sidebarLeft.scrollTop = initialPosition;
				                }
				            }
				        </script>

				    </div>

				</aside>
				<!-- end: sidebar -->

				<section role="main" class="content-body">
					<header class="page-header">
					</header>

					<!-- start: page -->
					<div class="row">
                        <div class="col">
							<section class="card">
								<header class="card-header">
									<div class="card-actions">
										<a href="#" class="card-action card-action-toggle" data-card-toggle></a>
										<a href="#" class="card-action card-action-dismiss" data-card-dismiss></a>
									</div>

                                    <form id="Form1" class="FormFiltro" action="" method="" autocomplete="off">
                                        <div class="row">
                                                    <div class="col-md-6">
                                                        <label>Año lectivo</label>
                                                        <select data-plugin-selectTwo2 class="form-control" style="width: 100%" id="id_anio" name="id_anio">
                                                            <option value="">Año lectivo</option>
                                                            <?php
                                                                $queryData = mysqli_query($link,"SELECT * FROM tbl_anios ORDER BY idanio DESC");
                                                                while($rowData = mysqli_fetch_array($queryData)){
                                                                    echo '<option value="'.$rowData['anio'].'">'.$rowData['anio'].'</option>';
                                                                }
                                                            ?>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>Nombres y apellidos</label>
                                                        <select data-plugin-selectTwo2 class="form-control" style="width: 100%" id="id_alumno" name="id_alumno">
                                                            <option value="">Alumnos</option>
                                                        </select>
                                                    </div>

                                                    <div class="text-center col-md-12 mt-3">
                                                        <button class="btn btn-primary" type="button" id="btnMostrarDatos"><i class="fa fa-file"></i> Mostrar datos</button>
														<button disabled="true" class="btn btn-primary btnDescargarDatos_alumno" type="button" id="btnDescargarDatos"><i class="fa fa-file-pdf"></i> Descargar PDF</button>
														<button disabled="true" class="btn btn-primary btnEnviarEmail_alumno" type="button" id="btnEnviarEmail"><i class="fa fa-envelope"></i> Enviar al tutor</button>
														<button disabled="true" data-bs-toggle="modal" data-bs-target="#ModalEC" class="btn btn-primary btnEnviarEmail_alumno" type="button" id="btnEnviarEmailDos"><i class="fa fa-envelope"></i> Enviar a otro correo</button>
                                                    </div>
                                        </div>
                                    </form>
								</header>
								<div class="card-body">

									<table class="table table-sm table-bordered table-striped small mb-0" id="example1">

										<thead>
											<tr>
                                                <th>No recibo</th>
                                                <th>Fecha de pago</th>
                                                <th>Concepto de pago</th>
                                                <th>Monto pagado</th>
                                            </tr>
										</thead>
										<tbody>
										</tbody>
										<tfoot>
                                            <tr>
                                                <th></th>  <!-- No recibo -->
                                                <th></th>  <!-- fecha de pago -->
                                                <th class="text-end">TOTAL:</th> <!-- Etiqueta -->
                                                <th id="footer-total" class="text-end"></th> <!-- Aquí pintamos el total -->
                                            </tr>
                                        </tfoot>
									</table>
								</div>
							</section>
						</div>
					</div>

					<!-- end: page -->
				</section>
			</div>

			<!-- Modal enviar -->
            <div class="modal fade" id="ModalEC" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel"></h5>
                        </div>
                        <div class="modal-body">
                            <form id="Form3" class="FormAlumno" action="" method="" autocomplete="off">

                                <div class="form-group col-md-12">
                                    <label>Correo</label>
                                    <input type="email" class="form-control" id="email_nuevo" name="email_nuevo" placeholder="Correo">
                                </div>

                                <div class="text-center col-md-12 mt-3">
                                    <button class="btn btn-default" data-dismiss="modal" type="reset"><i class="fa fa-close"></i> Cancelar</button>
                                    <button class="btn btn-primary" type="button" id="btnGuardarCorreo"><i class="fa fa-envelope"></i> Enviar</button>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer"></div>
                    </div>
                </div>
            </div>

            <!-- Modal eliminar -->
			<div class="modal fade" id="modalEliminarData">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Eliminar</h5>
						</div>
						<div class="modal-body">
							<form id="Form2" class="FormC" action="" method="" autocomplete="off">
								<h4 class="text-center">¿Esta seguro de eliminar los datos?</h4>
								<input type="hidden" name="id_alumno" id="id_alumno">
											
								<div class="text-center col-md-12">
									<button class="btn btn-default" data-dismiss="modal" type="reset"><i class="fa fa-close"></i> Cancelar</button>
									<button class="btn btn-danger" type="button" id="btnEliminarData2"><i class="fa fa-trash"></i> Elimimar</button>
								</div>
							</form>
						</div>
						<div class="modal-footer">
						</div>
					</div>
					<!-- /.modal-content -->
				</div>
				<!-- /.modal-dialog -->
			</div>

            <!-- /.content-wrapper -->
            <footer class="main-footer" style="background-image: url('img/Footer_SYS.png'); padding: 100px;border-top: 0px solid #d2d6de;background-color: #ecf0f5;border-top: 0px solid #d2d6de; background-color: #ecf0f5;background-size: cover; background-repeat: no-repeat;">
                <div class="pull-right hidden-xs" style="color: yellow;margin-top: 40px;">
                    Desarrollado por <a href="https://netsoluciones.com" target="_blank" style="color: yellow">Netsoluciones</a>
                </div>
            </footer>

		</section>

        <!-- Vendor -->
		<script src="assets/vendor/jquery/jquery.js"></script>
		<script src="assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
		<script src="assets/vendor/popper/umd/popper.min.js"></script>
		<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
		<script src="assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
		<script src="assets/vendor/common/common.js"></script>
		<script src="assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="assets/vendor/magnific-popup/jquery.magnific-popup.js"></script>
		<script src="assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>

		<!-- Specific Page Vendor -->
        <script src="assets/vendor/jquery-ui/jquery-ui.js"></script>
		<script src="assets/vendor/jqueryui-touch-punch/jquery.ui.touch-punch.js"></script>
		<script src="assets/vendor/select2/js/select2.js"></script>
		<script src="assets/vendor/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
		<script src="assets/vendor/jquery-maskedinput/jquery.maskedinput.js"></script>
		<script src="assets/vendor/bootstrap-tagsinput/bootstrap-tagsinput.js"></script>
		<script src="assets/vendor/bootstrap-timepicker/js/bootstrap-timepicker.js"></script>
		<script src="assets/vendor/fuelux/js/spinner.js"></script>
		<script src="assets/vendor/dropzone/dropzone.js"></script>
		<script src="assets/vendor/bootstrap-markdown/js/markdown.js"></script>
		<script src="assets/vendor/bootstrap-markdown/js/to-markdown.js"></script>
		<script src="assets/vendor/bootstrap-markdown/js/bootstrap-markdown.js"></script>
		<script src="assets/vendor/summernote/summernote-bs4.js"></script>
		<script src="assets/vendor/bootstrap-maxlength/bootstrap-maxlength.js"></script>
		<script src="assets/vendor/ios7-switch/ios7-switch.js"></script>
        <script src="assets/vendor/datatables/media/js/jquery.dataTables.min.js"></script>
		<script src="assets/vendor/datatables/media/js/dataTables.bootstrap5.min.js"></script>
		<script src="assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/dataTables.buttons.min.js"></script>
		<script src="assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.bootstrap4.min.js"></script>
		<script src="assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.html5.min.js"></script>
		<script src="assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.print.min.js"></script>
		<script src="assets/vendor/datatables/extras/TableTools/JSZip-2.5.0/jszip.min.js"></script>
		<script src="assets/vendor/datatables/extras/TableTools/pdfmake-0.1.32/pdfmake.min.js"></script>
		<script src="assets/vendor/datatables/extras/TableTools/pdfmake-0.1.32/vfs_fonts.js"></script>

		<!-- Theme Base, Components and Settings -->
		<script src="assets/js/theme.js"></script>

		<!-- Theme Custom -->
		<script src="assets/js/custom.js"></script>

		<!-- Theme Initialization Files -->
		<script src="assets/js/theme.init.js"></script>

        <!-- bootstrap datepicker -->
        <script src="assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>

        <!-- iniciar todo -->
        <script>
            $(document).ready(function() {
                // Inicializa todos los select con data-plugin-selectTwo2
                $('[data-plugin-selectTwo2]').select2({
                    theme: 'bootstrap'
                });
            });
        </script>

        <!-- script de mapeo de atributos de Bootstrap 5 -->
        <script>
            (function () {
            // Solo convertir si realmente es un componente de Bootstrap
            const BS_TOGGLES = new Set(['modal','collapse','dropdown','tab','tooltip','popover','offcanvas','button']);

            // data-toggle => data-bs-toggle (conservar el original)
            document.querySelectorAll('[data-toggle]').forEach(el => {
                const v = el.getAttribute('data-toggle');
                if (BS_TOGGLES.has(v)) {
                if (!el.hasAttribute('data-bs-toggle')) {
                    el.setAttribute('data-bs-toggle', v);
                }
                // No borrar data-toggle
                }
            });

            // data-target => data-bs-target (solo si va con un toggle de Bootstrap; NO tocar toggles del tema)
            document.querySelectorAll('[data-target]').forEach(el => {
                // Excluir controles propios del tema Porto Admin
                if (el.hasAttribute('data-toggle-class')) return;
                if (el.classList.contains('toggle-sidebar-left')) return;

                const v = el.getAttribute('data-target');
                const hasBSToggle = el.hasAttribute('data-toggle') && BS_TOGGLES.has(el.getAttribute('data-toggle'));
                const looksLikeBSTrigger = el.classList.contains('navbar-toggler') || el.classList.contains('dropdown-toggle');

                if (hasBSToggle || looksLikeBSTrigger) {
                if (!el.hasAttribute('data-bs-target')) {
                    el.setAttribute('data-bs-target', v);
                }
                // No borrar data-target (el tema lo necesita)
                }
            });

            // data-dismiss => data-bs-dismiss (conservar el original)
            document.querySelectorAll('[data-dismiss]').forEach(el => {
                const v = el.getAttribute('data-dismiss');
                if (!el.hasAttribute('data-bs-dismiss')) {
                el.setAttribute('data-bs-dismiss', v);
                }
                // No borrar data-dismiss
            });
            })();
        </script>

        <!-- script de tabla -->
        <script>
            $(function () {
                var table = $('#example1').DataTable();
            })
        </script>

        <!-- mostrar datos en tabla -->
        <script>
            $(document).ready(function(){
                $(document).on('click', '#btnMostrarDatos', function(e){
                    var id_alumno = $('#id_alumno').val();
                    var id_year = $('#id_anio').val();
                    if(id_alumno != '' && id_year != ''){
                        var table = $('#example1').DataTable();
                        table.destroy();
                        $('#example1').DataTable({
                            "processing": true,
                            "serverSide": true,
                            "ajax": "ajax_table/ajax_table_reporte_alumnos_dos.php?id_alumno="+id_alumno+"&id_anio="+id_year,
                            "createdRow": function ( row, data, index ) {
                            }
                        });
						//
						$('#btnDescargarDatos').prop('disabled', false);
						$('#btnEnviarEmail').prop('disabled', false);
						$('#btnEnviarEmailDos').prop('disabled', false);
						//
						actualizarTotalFiltrado();

						// Helper: pide el total filtrado al backend
						function actualizarTotalFiltrado() {
							const id_alumno   = $('#id_alumno').val() || '';
							const id_anio     = $('#id_anio').val() || '';

							$.getJSON('ajax_table/sum_reporte_alumnos.php', {
								id_alumno, id_anio
							}).done(function (resp) {
								const total = Number(resp.totalFiltrado || 0);
								const formateado = total.toLocaleString('es-NI', {
									minimumFractionDigits: 2,
									maximumFractionDigits: 2
								});
								// Pinta en el pie (columna 7)
								const api = $('#example1').DataTable().api ? $('#example1').DataTable().api() : $('#example1').DataTable();
								$(api.column(3).footer()).html('C$ ' + formateado);
							}).fail(function () {
								// opcional: deja vacío o muestra 0
								const api = $('#example1').DataTable();
								$(api.column(3).footer()).html('C$ 0.00');
							});
						}
                    } else {
                        alert("Seleccione un alumno o año lectivo");
                    }
                });
            });
        </script>

        <!-- consulta al seleccionar año lectivo -->
        <script>
            $(document).ready(function() {
                $('#id_anio').change(function(e) {
                    e.preventDefault();
                    var id_anio = $(this).val();
                    // alert(id_usuario);
                    $('#id_alumno').html('');  
                    $.ajax({
                        url: 'controller/get_matricula_list.php',
                        type: 'POST',
                        data: 'id_anio='+id_anio,
                        dataType: 'html'
                    })
                    .done(function(data){  
                        $('#id_alumno').html('');    
                        $('#id_alumno').html(data); // mostrar la data
                    })
                    .fail(function(){
                        $('#id_alumno').html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
                    });
                });
            });
        </script>

        <script>
			$(document).ready(function(){
                // primer paso
                $(document).on('click', '#btnEliminarData', function(e){
                    e.preventDefault();
                    $('#modalEliminarData').modal('show');
                });

				// segundo paso
                $(document).on('click', '#btnEliminarData2', function(e){
                    e.preventDefault();
                    $.ajax({
                        type: 'POST',
                        url: 'controller/clean_db.php',
                        success: function(data) {
                            if (data == 'bien') {
								location.reload();
                            } else {
                                alert("Error al eliminar los datos");
                            }
                        }
                    });      
                });

			});
		</script>

		<!-- descargar pdf -->
		<script>
			$(document).ready(function () {
				// Cuando ya cargaste la tabla habilitas el botón (ya lo haces),
				// ahora definimos el click para abrir el PDF con los mismos filtros.
				$(document).on('click', '#btnDescargarDatos', function (e) {
					e.preventDefault();
					const id_alumno = $('#id_alumno').val();
					const id_anio   = $('#id_anio').val();

					if (!id_alumno || !id_anio) {
					alert('Seleccione un alumno y un año lectivo');
					return;
					}

					// Abre el PDF en una nueva pestaña (o usa location.href si prefieres misma pestaña)
					const url = `reports/reporte_alumno_pdf.php?id_alumno=${encodeURIComponent(id_alumno)}&id_anio=${encodeURIComponent(id_anio)}`;
					window.open(url, '_blank');
				});
			});
		</script>

		<!-- enviar email -->
		<script>
			$(document).ready(function () {
				$(document).on('click', '#btnEnviarEmail', function (e) {
					e.preventDefault();

					const id_alumno = $('#id_alumno').val();
					const id_anio   = $('#id_anio').val();

					if (!id_alumno || !id_anio) {
						alert('Seleccione un alumno y un año lectivo');
						return;
					}

					// UI estado
					const $btn = $(this);
					const oldHtml = $btn.html();
					$btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');

					$.ajax({
						url: 'reports/send_reporte_alumno_email.php',
						type: 'POST',
						dataType: 'json',
						data: { id_alumno, id_anio }
					})
					.done(function (resp) {
					if (resp && resp.ok) {
						notif({ msg: resp.msg || 'Correo enviado correctamente.', type: 'success', position: 'center' });
					} else {
						notif({ msg: (resp && resp.error) ? resp.error : 'No se pudo enviar el correo.', type: 'error', position: 'center' });
					}
					})
					.fail(function (xhr) {
						notif({ msg: 'Error al enviar el correo.', type: 'error', position: 'center' });
					})
					.always(function () {
						$btn.prop('disabled', false).html(oldHtml);
					});
				});
			});
		</script>

		<!-- enviar mensaje a otro correo -->
        <script type="text/javascript">
            $(document).ready(function(){
                $(document).on('click', '#btnGuardarCorreo', function(e){
                    e.preventDefault();

					const id_alumno = $('#id_alumno').val();
					const id_anio   = $('#id_anio').val();
					const email  = $('#email_nuevo').val();

					if (!id_alumno || !id_anio) {
						alert('Seleccione un alumno y un año lectivo');
						return;
					}

					if (!email) {
						alert('Ingrese un correo válido');
						return;
					}

					// UI estado
					const $btn = $(this);
					const oldHtml = $btn.html();
					$btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');

					$.ajax({
						url: 'reports/send_reporte_alumno_email.php',
						type: 'POST',
						dataType: 'json',
						data: { id_alumno, id_anio, email }
					})
					.done(function (resp) {
					if (resp && resp.ok) {
						notif({ msg: resp.msg || 'Correo enviado correctamente.', type: 'success', position: 'center' });
					} else {
						notif({ msg: (resp && resp.error) ? resp.error : 'No se pudo enviar el correo.', type: 'error', position: 'center' });
					}
					})
					.fail(function (xhr) {
						notif({ msg: 'Error al enviar el correo.', type: 'error', position: 'center' });
					})
					.always(function () {
						$btn.prop('disabled', false).html(oldHtml);
					}); 
                });
            });
        </script>

	</body>
</html>