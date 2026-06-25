<?php
    include_once 'includes/config.php';
    include_once 'includes/security.php';
    include_once 'includes/Services.php';

    date_default_timezone_set('America/Managua');
	setlocale(LC_ALL,"es_ES");
    
    session_start();
    $id         = $_SESSION['id_usuario'];
    $nombre_u   = $_SESSION['nombre'];
    $apellido_u = $_SESSION['apellido'];
    $activo     = $_SESSION['activo'];
    $tipo       = $_SESSION['tipo_usuario'];

    if (empty($id) || empty($activo)) {
        header("Location: index.html");
        exit;
    }

    $paymentService = new PaymentService($link);
    $ano_actual = date('Y');
    $mes_actual = (int)date('m');

	$meses = [
    	1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril',
    	5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto',
    	9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre'
	];
	$mes_actual_str = $meses[$mes_actual];
    
    // Obtener alumnos en mora usando el servicio
    $datos_alumnos_mora = $paymentService->getAlumnosEnMora($ano_actual, $mes_actual);
    $total_alumnos_mora = count($datos_alumnos_mora);

    // Ingresos del día
    $total_dia = $paymentService->getTotalIngresosDia(date("Y-m-d"));
?>
<!doctype html>
<html class="fixed">
	<head>
		<!-- Basic -->
		<meta charset="UTF-8">
		<title>Dashboard</title>
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
		<link rel="stylesheet" href="assets/vendor/bootstrap-multiselect/css/bootstrap-multiselect.css" />
		<link rel="stylesheet" href="assets/vendor/morris/morris.css" />

		<!-- Theme CSS -->
		<link rel="stylesheet" href="assets/css/theme.css" />
		<link rel="stylesheet" href="assets/css/skins/default.css" />
		<link rel="stylesheet" href="assets/css/custom.css">

		<!-- Head Libs -->
		<script src="assets/vendor/modernizr/modernizr.js"></script>
        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

				<div class="header-right">
					<ul class="notifications">
						<li>
							<a href="#" class="dropdown-toggle notification-icon" data-bs-toggle="dropdown">
								<i class="bx bx-bell"></i>
								<span class="badge"><?php echo ($total_alumnos_mora > 0) ? "+" : ""; ?></span>
							</a>

							<div class="dropdown-menu notification-menu">
								<div class="notification-title">
									<span class="float-end badge badge-default"><?php echo $total_alumnos_mora; ?></span>
									Alumnos en mora
								</div>

								<div class="content" style="overflow: visible;height: 500px;overflow-y: scroll;scroll-behavior: smooth;">
									<ul>
                                        <?php
                                            foreach ($datos_alumnos_mora as $item) {
                                                $mesLabel = ($item['total_mes'] > 1) ? "meses" : "mes";
                                                echo '
                                                <li>
                                                    <a href="#" class="clearfix">
                                                        <div class="image">
                                                            <i class="fas fa-user bg-danger text-light"></i>
                                                        </div>
                                                        <span class="title">'.$item['nombre'].'</span>
                                                        <span class="message">'.$item['total_mes'].' '.$mesLabel.' de mora</span>
                                                    </a>
                                                </li>';
                                            }
                                        ?>
									</ul>
								</div>
							</div>
						</li>
					</ul>
					<span class="separator"></span>
				</div>
			</header>
			<!-- end: header -->

			<div class="inner-wrapper">
				<!-- start: sidebar -->
				<aside id="sidebar-left" class="sidebar-left">
				    <div class="sidebar-header">
				        <div class="sidebar-title">Menu</div>
				        <div class="sidebar-toggle d-none d-md-block" data-toggle-class="sidebar-left-collapsed" data-target="html" data-fire-event="sidebar-left-toggle">
				            <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
				        </div>
				    </div>

				    <div class="nano">
				        <div class="nano-content">
				            <nav id="menu" class="nav-main" role="navigation">
				                <ul class="nav nav-main">
				                    <li class="nav-active">
				                        <a class="nav-link" href="home.php">
				                            <i class="bx bx-home-alt" aria-hidden="true"></i>
				                            <span>Dashboard</span>
				                        </a>                        
				                    </li>
				                    <li class="nav-parent">
				                        <a class="nav-link" href="#">
				                            <i class="bx bx-detail" aria-hidden="true"></i>
				                            <span>Facturación</span>
				                        </a>
				                        <ul class="nav nav-children">
				                            <li><a class="nav-link" href="crear_recibos.php">Crear Recibos</a></li>
				                            <li><a class="nav-link" href="inscripcion_alumno.php">Inscripción de alumnos</a></li>
				                            <li><a class="nav-link" href="recibos.php">Recibos</a></li>
				                        </ul>
				                    </li>
                                    <li class="nav-parent">
				                        <a class="nav-link" href="#">
				                            <i class="bx bx-file" aria-hidden="true"></i>
				                            <span>Reportes</span>
				                        </a>
				                        <ul class="nav nav-children">
				                            <li><a class="nav-link" href="reporte_categoria.php">Pagos por categorias</a></li>
				                            <li><a class="nav-link" href="reporte_transporte.php">Pagos de transporte</a></li>
				                            <li><a class="nav-link" href="reporte_grados.php">Pagos por grados</a></li>
                                            <li><a class="nav-link" href="reporte_alumnos.php">Pagos por alumnos</a></li>
                                            <li><a class="nav-link" href="reporte_facturas.php">Caja general</a></li>
				                        </ul>
				                    </li>
                                    <li class="nav-parent">
				                        <a class="nav-link" href="#">
				                            <i class="bx bx-table" aria-hidden="true"></i>
				                            <span>Menu</span>
				                        </a>
				                        <ul class="nav nav-children">
				                            <li><a class="nav-link" href="alumnos.php">Alumnos</a></li>
				                            <li><a class="nav-link" href="aranceles.php">Aranceles</a></li>
				                            <li><a class="nav-link" href="categoria_pago.php">Categorias de pago</a></li>
                                            <li><a class="nav-link" href="concepto_pago.php">Conceptos de pago</a></li>
                                            <li><a class="nav-link" href="niveles.php">Niveles</a></li>
											<li><a class="nav-link" href="secciones.php">Secciones</a></li>
											<li><a class="nav-link" href="grados.php">Grados</a></li>
                                            <?php if ($_SESSION['tipo_usuario'] == 2) { ?>
                                            <li><a class="nav-link" href="year_lectivo.php">Activar año lectivo</a></li>
											<?php } ?>
				                        </ul>
				                    </li>
                                    <li><a class="nav-link" href="pre_matricula.php"><i class="fa fa-users" aria-hidden="true"></i><span>Pre-Matricula</span></a></li>
                                    <?php if ($_SESSION['tipo_usuario'] == 2) { ?>
                                    <li><a class="nav-link" href="usuarios.php"><i class="bx bx-user" aria-hidden="true"></i><span>Usuarios</span></a></li>
                                    <li><a class="nav-link" href="config_email.php"><i class="bx bx-envelope" aria-hidden="true"></i><span>Config Email</span></a></li>
									<li><a class="nav-link" href="config_colegio.php"><i class="fa fa-school" aria-hidden="true"></i><span>Config Colegio</span></a></li>
									<li><a class="nav-link" id="btnEliminarData"><i class="fa fa-trash" aria-hidden="true"></i><span>Limpiar Data</span></a></li>
									<li><a href="backup.php"><i class="fa fa-download" aria-hidden="true"></i><span>Descargar respaldo BD</span></a></li>
									<?php } ?>
                                    <li><a class="nav-link" href="salir.php"><i class="fa fa-sign-out" aria-hidden="true"></i><span>Cerrar sesión</span></a></li>
				                </ul>
				            </nav>
				        </div>
				    </div>
				</aside>

				<section role="main" class="content-body">
					<header class="page-header"></header>

					<div class="row">
                        <div class="col-lg-12">
							<div class="row">
								<div class="col-xl-3">
									<section class="card card-featured-left card-featured-primary mb-3">
										<div class="card-body">
											<div class="widget-summary">
												<div class="widget-summary-col widget-summary-col-icon">
													<div class="summary-icon"><img src="img/school_students_icon_144607.png" style="width: 100%;"/></div>
												</div>
												<div class="widget-summary-col">
													<div class="summary">
														<h4 class="title">Matricula <?php echo $ano_actual; ?></h4>
														<div class="info">
															<strong class="amount">
                                                                <?php
                                                                    $stmtTotal = mysqli_prepare($link, "SELECT COUNT(m.ID) AS TOTAL FROM tbl_matricula m INNER JOIN tbl_alumnos a ON m.IDALUMNO = a.IDALUMNO WHERE m.ANIO = ?");
                                                                    mysqli_stmt_bind_param($stmtTotal, "s", $ano_actual);
                                                                    mysqli_stmt_execute($stmtTotal);
                                                                    $resT = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTotal));
                                                                    echo $resT['TOTAL'];
                                                                ?>
                                                            </strong>
														</div>
													</div>
													<div class="summary-footer"><a class="text-muted text-uppercase" href="alumnos_inscritos.php">Ver más</a></div>
												</div>
											</div>
										</div>
									</section>
								</div>
								
                                <div class="col-xl-3">
									<section class="card card-featured-left card-featured-primary">
										<div class="card-body">
											<div class="widget-summary">
												<div class="widget-summary-col widget-summary-col-icon">
													<div class="summary-icon"><img src="img/business_man_user_alert_alerttheuser_askthecustomer_negocio_2331.png" style="width: 100%;"/></div>
												</div>
												<div class="widget-summary-col">
													<div class="summary">
														<h4 class="title">Alumnos en mora <?php echo $mes_actual_str; ?></h4>
														<div class="info"><strong class="amount"><?php echo $total_alumnos_mora; ?></strong></div>
													</div>
													<div class="summary-footer"><a class="text-muted text-uppercase" href="alumnos_mora.php">Ver más</a></div>
												</div>
											</div>
										</div>
									</section>
								</div>
                                
                                <div class="col-xl-3">
									<section class="card card-featured-left card-featured-primary">
										<div class="card-body">
											<div class="widget-summary">
												<div class="widget-summary-col widget-summary-col-icon">
													<div class="summary-icon"><img src="img/cash_icon-icons.com_51090.png" style="width: 100%;"/></div>
												</div>
												<div class="widget-summary-col">
													<div class="summary">
														<h4 class="title">Total ingreso por dia</h4>
														<div class="info"><strong class="amount">C$ <?php echo number_format($total_dia, 2); ?></strong></div>
													</div>
												</div>
											</div>
										</div>
									</section>
								</div>
                                
                                <div class="col-xl-3">
									<section class="card card-featured-left card-featured-primary">
										<div class="card-body">
											<div class="widget-summary">
												<div class="widget-summary-col widget-summary-col-icon">
													<div class="summary-icon"><img src="img/money_dollar_arrows_refresh_update_business_finance_investment_icon_188624.png" style="width: 100%;"/></div>
												</div>
												<div class="widget-summary-col">
													<div class="summary">
														<h4 class="title">Tasa de cambio</h4>
														<div class="info">
															<strong class="amount">
                                                            <?php
                                                                $resTasa = mysqli_query($link,"SELECT tasa FROM tasa_cambio WHERE activo_tasa = 1 LIMIT 1");
                                                                $rowTasa = mysqli_fetch_assoc($resTasa);
                                                                echo 'C$'.($rowTasa['tasa'] ?? '0');
                                                            ?>
                                                            </strong>
														</div>
													</div>
													<div class="summary-footer"><a class="text-muted text-uppercase" href="#0" data-bs-toggle="modal" data-bs-target="#ModalNuevaTasa" >Actualizar tasa</a></div>
												</div>
											</div>
										</div>
									</section>
								</div>
							</div>
						</div>
						
                        <div class="col-lg-12">
							<section class="card">
								<div class="card-body">
									<div class="chart-data-selector">
										<h2>Ingresos por dias de la semana</h2>
										<div id="container" style="height: 400px;"></div>
									</div>
								</div>
							</section>
						</div>
					</div>

					<!-- Modales -->
					<div class="modal fade" id="ModalNuevaTasa">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header"><h5 class="modal-title">Actualizar Tasa</h5></div>
								<div class="modal-body">
									<form id="Form3" autocomplete="off">
										<label>Tasa de cambio</label>
										<input type="text" class="form-control" id="tasa_cambio" name="tasa_cambio">
										<div class="text-center mt-3">
											<button class="btn btn-default" data-bs-dismiss="modal">Cancelar</button>
											<button class="btn btn-primary" type="button" id="btnGuardarTasaCambio">Guardar</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>

					<div class="modal fade" id="modalEliminarData">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header"><h5 class="modal-title">Eliminar Datos</h5></div>
								<div class="modal-body">
									<h4 class="text-center">¿Esta seguro de eliminar los datos?</h4>
									<div class="text-center mt-3">
										<button class="btn btn-default" data-bs-dismiss="modal">Cancelar</button>
										<button class="btn btn-danger" type="button" id="btnEliminarData2">Elimimar</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>
			</div>

            <footer class="main-footer" style="background-image: url('img/Footer_SYS.png'); padding: 100px; background-size: cover;">
                <div class="pull-right hidden-xs" style="color: yellow;margin-top: 40px;">
                    Desarrollado por <a href="https://netsoluciones.com" target="_blank" style="color: yellow">Netsoluciones</a>
                </div>
            </footer>
		</section>

		<!-- Vendor -->
		<script src="assets/vendor/jquery/jquery.js"></script>
		<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
		<script src="assets/vendor/common/common.js"></script>
		<script src="assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="assets/js/theme.js"></script>
        <script src="js/highcharts/highcharts.js"></script>

        <script>
            Highcharts.chart('container', {
                accessibility: { enabled: false },
                chart: { type: 'bar' },
                title: { text: 'Ingresos de la Semana' },
                xAxis: { categories: ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'] },
                yAxis: { title: { text: 'Monto (C$)' } },
                series: [{
                    name: 'Ingresos',
                    data: [
                        <?php
                        $fecha = new DateTime();
                        $dia = (int)$fecha->format('N');
                        $fecha->sub(new DateInterval('P' . ($dia - 1) . 'D'));
                        for ($i = 0; $i < 7; $i++) {
                            echo $paymentService->getTotalIngresosDia($fecha->format('Y-m-d')) . ",";
                            $fecha->add(new DateInterval('P1D'));
                        }
                        ?>
                    ]
                }]
            });

			$("#btnGuardarTasaCambio").click(function() {
                const tasa = $("#tasa_cambio").val();
                if (!tasa) {
                    Swal.fire('Error', 'Ingrese una tasa válida', 'error');
                    return;
                }
                $.post("controller/g_tasa_cambio.php", { tasa_cambio: tasa }, function(data) {
                    if (data == 'bien') {
                        Swal.fire('Éxito', 'Tasa actualizada', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', 'No se pudo actualizar', 'error');
                    }
                });
            });

            $("#btnEliminarData2").click(function() {
                $.post("controller/clean_db.php", function(data) {
                    if (data == 'bien') location.reload();
                    else Swal.fire('Error', 'No se pudo limpiar la data', 'error');
                });
            });
        </script>
	</body>
</html>