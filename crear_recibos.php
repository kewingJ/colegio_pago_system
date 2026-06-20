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

		<title>Crear recibos</title>

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
            function not5(){
                notif({
                    msg: "Error! algo salio mal",
                    type: "error",
                    position: "center"
                });
            }
        </script>

        <script>
            function not6(){
                notif({
                    msg: "Correo enviado!",
                    type: "success",
                    position: "center"
                });
            }
        </script>

        <script>
            function not7(){
                notif({
                    msg: "Mensaje enviado!",
                    type: "success",
                    position: "center"
                });
            }
        </script>

        <style>
            .datepicker{
                z-index: 9999 !important;
            }
        </style>

        <style>
            #content_dynamic_edit_arancel table {
                margin: 0 auto !important;
            }
        </style>

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
				                    <li class="">
				                        <a class="nav-link" href="home.php">
				                            <i class="bx bx-home-alt" aria-hidden="true"></i>
				                            <span>Dashboard</span>
				                        </a>                        
				                    </li>
                                    <!-- facturacion -->
				                    <li class="nav-parent nav-expanded">
				                        <a class="nav-link" href="#">
				                            <i class="bx bx-detail" aria-hidden="true"></i>
				                            <span>Facturación</span>
				                        </a>
				                        <ul class="nav nav-children">
				                            <li class="nav-active">
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
                                    <li class="nav-parent">
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
                                            <li>
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

                                    <form id="Form1" class="FormNuevo" action="" method="" autocomplete="off">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>Año lectivo</label>
                                                <select data-plugin-selectTwo2 class="form-control" style="width: 100%" id="id_anio" name="id_anio">
                                                    <option value="">Año lectivo</option>
                                                    <?php
                                                        $queryData = mysqli_query($link,"SELECT * FROM tbl_anios ORDER BY idanio DESC");
                                                        while($rowData = mysqli_fetch_array($queryData)){
                                                            $año_actual = date('Y');
                                                            if($año_actual == $rowData['anio']){
                                                                echo '<option selected value="'.$rowData['anio'].'">'.$rowData['anio'].'</option>';
                                                            } else {
                                                                echo '<option value="'.$rowData['anio'].'">'.$rowData['anio'].'</option>';
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label>Nombres y apellidos</label>
                                                <select data-plugin-selectTwo2 class="form-control" style="width: 100%" id="id_alumno" name="id_alumno">
                                                    <option value="">Lista de alumnos</option>
                                                    <?php
                                                        $queryData = mysqli_query($link,"SELECT * FROM tbl_alumnos");
                                                        while($rowData = mysqli_fetch_array($queryData)){
                                                            echo '<option value="'.$rowData['IDALUMNO'].'">'.$rowData['NOMBREAPELLIDO'].'</option>';
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-12" id="datosInscripcion">
                                                <h5></h5>
                                            </div>
                                        </div>
                                    </form>
                                    
								</header>
								<div class="card-body">
                                    <div class="col-lg-12">
                                        <div class="tabs">
                                            <ul class="nav nav-tabs">
                                                <li class="nav-item active">
                                                    <a class="nav-link active" data-bs-target="#tab_1" href="#tab_1" data-bs-toggle="tab">Facturacion</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-target="#tab_2" href="#tab_2" data-bs-toggle="tab">Pagos efectuados</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-target="#tab_3" href="#tab_3" data-bs-toggle="tab">Mensualidades</a>
                                                </li>
                                            </ul>
                                            <div class="tab-content">
                                                <div id="tab_1" class="tab-pane active">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <form id="Form1" class="FormFactura" action="" method="" autocomplete="off">
                                                                    <div class="row">
                                                                        <div class="col-md-4">
                                                                            <label>Fecha recibo</label>
                                                                            <input type="text" class="form-control pull-right" id="datepicker" name="fecha_recibo">
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <label>Nivel escolar</label>
                                                                            <div class="input-group date">
                                                                                <select data-plugin-selectTwo2 class="form-control" style="width: 100%" id="id_nivel" name="id_nivel">
                                                                                    <option value="">Nivel escolar</option>
                                                                                    <?php
                                                                                        $queryData = mysqli_query($link,"SELECT * FROM tbl_nivel");
                                                                                        while($rowData = mysqli_fetch_array($queryData)){
                                                                                            echo '<option value="'.$rowData['IdNivel'].'">'.$rowData['Nivel'].'</option>';
                                                                                        }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <label>Categoria de pago</label>
                                                                            <div class="input-group date">
                                                                                <select data-plugin-selectTwo2 class="form-control" style="width: 100%" id="id_categoria" name="id_categoria">
                                                                                    <option value="">Categoria de pago</option>
                                                                                    <?php
                                                                                        $queryData = mysqli_query($link,"SELECT * FROM tbl_categoriapago");
                                                                                        while($rowData = mysqli_fetch_array($queryData)){
                                                                                            echo '<option value="'.$rowData['Id'].'">'.$rowData['Concepto'].'</option>';
                                                                                        }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-3">
                                                                            <label>Concepto de pago</label>
                                                                            <div class="input-group date">
                                                                                <select data-plugin-selectTwo2 class="form-control" style="width: 100%" id="id_concepto" name="id_concepto">
                                                                                    <option value="">Concepto</option>
                                                                                    <?php
                                                                                        $queryData = mysqli_query($link,"SELECT * FROM tbl_conceptospago");
                                                                                        while($rowData = mysqli_fetch_array($queryData)){
                                                                                            echo '<option value="'.$rowData['IdConcepto'].'">'.$rowData['Concepto'].'</option>';
                                                                                        }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-3">
                                                                            <label>Unidades</label>
                                                                            <input disabled="true" type="number" class="form-control" id="id_unidades" name="unidades" placeholder="">
                                                                        </div>

                                                                        <div class="col-md-3">
                                                                            <label>Monto concepto</label>
                                                                            <input type="text" class="form-control" id="id_monto" name="monto" placeholder="">
                                                                        </div>

                                                                        <div class="col-md-3">
                                                                            <label>Mes referencia</label>
                                                                            <input type="text" class="form-control pull-right mes_referencia" id="datepickerDos" name="mes_referencia">
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <label>Pago completo</label>
                                                                            <div class="input-group date">
                                                                                <select data-plugin-selectTwo2 class="form-control" style="width: 100%" name="id_completo">
                                                                                    <option value="si">SI</option>
                                                                                    <option value="no">NO</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <label>Monto abonado</label>
                                                                            <input type="text" class="form-control" id="monto_abonado" name="monto_abonado" placeholder="">
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <label>Total a pagar</label>
                                                                            <input type="text" class="form-control" id="total_pagar" name="total_pagar" placeholder="">
                                                                        </div>

                                                                        <div class="col-md-12 mt-3">
                                                                            <button class="btn btn-primary pull-right" type="button" id="btnAddItem"><i class="fa fa-plus"></i> Agregar item</button>
                                                                        </div>

                                                                        <table id="example1" class="table table-bordered table-striped mt-3" style="width:100%">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th></th>
                                                                                    <th>Id registro</th>
                                                                                    <th>Mes</th>
                                                                                    <th>Categoria</th>
                                                                                    <th>Concepto</th>
                                                                                    <th>Monto</th>
                                                                                    <th style="display: none;">fecha</th>
                                                                                    <th style="display: none;">idCategoria</th>
                                                                                    <th style="display: none;">idConcepto</th>
                                                                                    <th style="display: none;">idnivel</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                            </tbody>
                                                                        </table>

                                                                        <div class="col-md-4">
                                                                            <button class="btn btn-primary mt-3" type="button" id="btnAddRecibo"><i class="fa fa-save"></i> Grabar recibo</button>
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            Total factura
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <input value="0" type="text" class="form-control" id="total_factura" name="total_factura" placeholder="">
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                </div>
                                                <div id="tab_2" class="tab-pane">
                                                    <table id="example2" class="table table-bordered table-striped" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>No recibo</th>
                                                                    <th>Fecha pago</th>
                                                                    <th>Alumno</th>
                                                                    <th>Categoria</th>
                                                                    <th>Concepto</th>
                                                                    <th>Mes</th>
                                                                    <th>Monto</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            </tbody>
                                                    </table>
                                                </div>
                                                <div id="tab_3" class="tab-pane">
                                                    <table id="example3" class="table table-bordered table-striped" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>Mes pagado</th>
                                                                    <th>Pagado</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
							</section>
						</div>
					</div>

					<!-- end: page -->
				</section>
			</div>

            <!-- Modal recibo -->
            <div class="modal fade" id="ModalDetalleRecibo" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                        </div>
                        <div class="modal-body">
                            <div id="content_dynamic_edit_arancel" style="margin: 0 auto; max-width: 600px; text-align:center;">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="text-center col-md-12">
                                <button class="btn btn-primary" type="button" id="btnImprimirRecibo"><i class="fa fa-print"></i> Imprimir</button>
                                <button class="btn btn-primary" type="button" id="btnEnviarRecibo"><i class="fa fa-envelope"></i> Enviar recibo</button>
                                <button class="btn btn-success" type="button" id="btnEnviarReciboWhasap"><i class="fab fa-whatsapp"></i> Enviar recibo</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal correo alumno-->
            <div class="modal fade" id="ModalNuevoCorreoAlumno" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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

                                <div class="text-center col-md-12">
                                    <button class="btn btn-default" data-dismiss="modal" type="reset"><i class="fa fa-close"></i> Cancelar</button>
                                    <button class="btn btn-primary" type="button" id="btnGuardarCorreo"><i class="fa fa-save"></i> Guardar</button>
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

        <!-- iniciar todo -->
        <script>
            $(document).ready(function() {
                // Al abrir cualquier modal, inicializar select2 si no está inicializado
                $('.modal').on('shown.bs.modal', function () {
                    $(this).find('[data-plugin-selectTwo2]').each(function () {
                        if (!$(this).hasClass("select2-hidden-accessible")) {
                            $(this).select2({
                                theme: 'bootstrap',
                                dropdownParent: $(this).closest('.modal') // evita problemas de z-index en modales
                            });
                        }
                    });
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

        <!-- script -->
        <script>
            // 
            var fechaActual = new Date();
            var fechaFormateada = $.datepicker.formatDate('dd/mm/yy', fechaActual);
            $('#datepicker').val(fechaFormateada);
            
            $('#datepicker').datepicker({
                format: 'dd/mm/yyyy'
            });

            // $('#datepickerDos').val(fechaFormateada);
            
            $('#datepickerDos').datepicker({
                format: 'dd/mm/yyyy'
            });
        </script>

        <!-- consulta al seleccionar categoria -->
        <script>
            $(document).ready(function() {
                $('#id_categoria').change(function(e) {
                    e.preventDefault();
                    var id_categoria = $(this).val();
                    // alert(id_usuario);
                    $('#id_concepto').html('');
                    $.ajax({
                        url: 'controller/get_concepto_list.php',
                        type: 'POST',
                        data: 'id_categoria='+id_categoria,
                        dataType: 'html'
                    })
                    .done(function(data){  
                        $('#id_concepto').html('');    
                        $('#id_concepto').html(data); // mostrar la data
                    })
                    .fail(function(){
                        $('#id_concepto').html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
                    });
                });
            });
        </script>

        <!-- al seleccionar el concepto mostrar el monto -->
        <script>
            $(document).ready(function() {
                $('#id_concepto').change(function(e) {
                    e.preventDefault();
                    var id_concepto = $(this).val();
                    // alert(id_concepto);
                    if(id_concepto != '' && id_concepto != null){
                        $.ajax({
                            url: 'controller/get_monto_list.php',
                            type: 'POST',
                            data: 'id_concepto='+id_concepto,
                            dataType: 'html'
                        })
                        .done(function(data){
                            var data2 = JSON.parse(data);
                            $("#id_monto").val(data2[0]);
                            $('#datepickerDos').val('');
                            if(data2[0] != 'No hay'){
                                $("#total_pagar").val(data2[0]);
                            } else {
                                $("#total_pagar").val(0);
                            }
                            //activar unidades
                            if(data2[1] > 0){
                                $('#id_unidades').prop('disabled', false);
                                $('#id_unidades').val(1);
                            } else {
                                $('#id_unidades').prop('disabled', true);
                                $('#id_unidades').val('');
                            }
                        })
                        .fail(function(){
                            $("#id_monto").val('No hay');
                        });
                    }
                });
            });
        </script>

        <!-- al cambiar unidades recalcular total -->
        <script>
            $(document).ready(function() {
                $('#id_unidades').change(function(e) {
                    e.preventDefault();
                    var id_unidades = $(this).val();
                    var id_monto = $('#id_monto').val();
                    if(id_monto >= 0){
                        var total = parseFloat(id_unidades) * parseFloat(id_monto);
                        total = total.toFixed(2);
                        $("#total_pagar").val(total);
                    }
                });
            });
        </script>

        <!-- agregar nuevo item -->
        <script type="text/javascript">
            $(document).ready(function(){
                $(document).on('click', '#btnAddItem', function(e){
                    var mes_referencia  = $('#datepickerDos').val();
                    var categoria       = $('#id_categoria option:selected').text();
                    var concepto        = $('#id_concepto option:selected').text();
                    var monto           = $('#total_pagar').val();
                    // otros datos
                    var fecha_recibo    = $('#datepicker').val();
                    var id_categoria    = $('#id_categoria').val();
                    var id_concepto     = $('#id_concepto').val();
                    var id_nivel        = $('#id_nivel').val();

                    // calcular total factura
                    var monto_float = parseFloat($('#total_pagar').val());
                    var total_factura = parseFloat($('#total_factura').val());
                    if(mes_referencia.trim() != '' && monto.trim() != '' && id_categoria.trim() != ''
                       && id_concepto.trim() != '' && id_nivel.trim() != '' && fecha_recibo.trim() != ''){
                        total_factura += monto_float;
                        var datosFormulario = $(".FormFactura").serialize();
                        // obtener tabla de factura
                        $.ajax({
                            url: 'controller/get_detalle_factura.php',
                            type: 'POST',
                            data: 'mes_referencia='+mes_referencia+'&categoria='+categoria+'&concepto='+concepto+'&monto='+monto+'&fecha_recibo='+fecha_recibo+'&id_categoria='+id_categoria+'&id_concepto='+id_concepto+'&id_nivel='+id_nivel,
                            dataType: 'html'
                        })
                        .done(function(data){
                            $('#example1 tbody').append(data);
                            // limpiar datos
                            // $('#id_nivel').val('').trigger('change');
                            $('#id_categoria').val('').trigger('change');
                            $('#id_concepto').val('').trigger('change');
                            $('#id_monto').val('');
                            $('#monto_abonado').val('');
                            $('#total_pagar').val('');
                            $('#datepickerDos').val('');
                            $('#total_factura').val(total_factura);
                            $('#id_unidades').val('');
                            $('#id_unidades').prop('disabled', true);
                        })
                        .fail(function(){
                        });
                    } else {
                        alert("Algunos campos estan vacios");
                    }
                });
            });
        </script>

        <!-- al seleccionar un alumno cargar la data -->
        <script>
            $(document).ready(function() {
                $('#id_alumno').change(function(e) {
                    e.preventDefault();
                    var id_alumno = $(this).val();
                    var year = $('#id_anio').val();
                    // alert(year);
                    $('#example2 tbody').html('');
                    // obtener
                    $.ajax({
                        url: 'controller/get_pagos_efectuados.php',
                        type: 'POST',
                        data: 'id_alumno='+id_alumno+'&year='+year,
                        dataType: 'html'
                    })
                    .done(function(data){
                        $('#example2 tbody').append(data);
                    })
                    .fail(function(){
                        // alert(data);
                    });


                    $('#example3 tbody').html('');
                    // 
                    $.ajax({
                        url: 'controller/get_mensualidades.php',
                        type: 'POST',
                        data: 'id_alumno='+id_alumno+'&year='+year,
                        dataType: 'html'
                    })
                    .done(function(data){
                        $('#example3 tbody').append(data);
                    })
                    .fail(function(){
                        // alert(data);
                    });

                    $('#datosInscripcion').html('');
                    //
                    $.ajax({
                        url: 'controller/get_datos_inscripcion.php',
                        type: 'POST',
                        data: 'id_alumno='+id_alumno+'&year='+year,
                        dataType: 'html'
                    })
                    .done(function(data){
                        $('#datosInscripcion').append(data);
                        var nivel_id = $('#nivel_id').val();
                        $('#id_nivel').val(nivel_id).trigger('change');
                    })
                    .fail(function(){
                        // alert(data);
                    });
                });
            });
        </script>

        <!-- al seleccionar año lectivo -->
        <script>
            $(document).ready(function() {
                $('#id_anio').change(function(e) {
                    e.preventDefault();
                    var id_alumno = $('#id_alumno').val();
                    var year = $(this).val();
                    // alert(year);
                    $('#example2 tbody').html('');
                    // obtener
                    $.ajax({
                        url: 'controller/get_pagos_efectuados.php',
                        type: 'POST',
                        data: 'id_alumno='+id_alumno+'&year='+year,
                        dataType: 'html'
                    })
                    .done(function(data){
                        $('#example2 tbody').append(data);
                    })
                    .fail(function(){
                        alert(data);
                    });

                    $('#example3 tbody').html('');
                    // 
                    $.ajax({
                        url: 'controller/get_mensualidades.php',
                        type: 'POST',
                        data: 'id_alumno='+id_alumno+'&year='+year,
                        dataType: 'html'
                    })
                    .done(function(data){
                        $('#example3 tbody').append(data);
                    })
                    .fail(function(){
                        alert(data);
                    });


                    $('#datosInscripcion').html('');
                    //
                    $.ajax({
                        url: 'controller/get_datos_inscripcion.php',
                        type: 'POST',
                        data: 'id_alumno='+id_alumno+'&year='+year,
                        dataType: 'html'
                    })
                    .done(function(data){
                        $('#datosInscripcion').append(data);
                        var nivel_id = $('#nivel_id').val();
                        $('#id_nivel').val(nivel_id).trigger('change');
                    })
                    .fail(function(){
                        // alert(data);
                    });
                });
            });
        </script>

        <!-- al seleccionar mes de referencia -->
        <script>
            $(document).ready(function() {
                $('#datepickerDos').change(function(e) {
                    e.preventDefault();
                    var unidades = $('#id_unidades').val();
                    // alert(unidades);
                    if(unidades == '')
                    {
                        var fechaSeleccionada = $(this).val();
                        var id_categoria    = $('#id_categoria').val();
                        var id_concepto     = $('#id_concepto').val();
                        var id_alumno       = $('#id_alumno').val();
                        var id_anio         = $('#id_anio option:selected').text();
                        var id_nivel        = $('#id_nivel').val();
                        // alert(fechaSeleccionada);
                        $.ajax({
                            url: 'controller/get_monto_saldo.php',
                            type: 'POST',
                            data: 'id_anio='+id_anio+'&id_alumno='+id_alumno+'&id_concepto='+id_concepto+'&mes='+fechaSeleccionada+'&id_nivel='+id_nivel+'&id_categoria='+id_categoria,
                            dataType: 'html'
                        })
                        .done(function(data){
                            $("#total_pagar").val(data);
                            if(data == '0.00'){
                                $("#total_pagar").val(data);
                                alert('Este Pago ya fue efectuado');
                            } else if(data == 'no tiene'){
                                // alert('No tiene arancel');
                                $("#total_pagar").val('0.00');
                            }
                        })
                        .fail(function(){
                        });
                    }
                });
            });
        </script>

        <!-- eliminar celdas de tabla de recibo -->
        <script>
            $(document).ready(function() {
                $('#example1').on('click', '.eliminarReciboBtn', function() {
                    $(this).closest('tr').remove();
                    var monto_float = parseFloat($(this).closest('tr').find('td:eq(5)').text());
                    var total_factura = parseFloat($('#total_factura').val());
                    total_factura -= monto_float;
                    $('#total_factura').val(total_factura);
                    // alert(total_factura+' - '+monto);
                });
            });
        </script>

        <!-- nuevo recibo -->
        <script type="text/javascript">
            $(document).ready(function() {
                $(document).on('click', '#btnAddRecibo', function(e){
                    var items = [];
                    var total_factura = parseFloat($('#total_factura').val());
                    var id_alumno = $('#id_alumno').val();
                    var id_anio = $('#id_anio').val();
                    
                    $('#example1 tbody tr').each(function() {
                        var mes_referencia  = $(this).find('td:eq(2)').text();
                        var monto           = $(this).find('td:eq(5)').text();
                        var fecha_recibo    = $(this).find('td:eq(6)').text();
                        var id_categoria    = $(this).find('td:eq(7)').text();
                        var id_concepto     = $(this).find('td:eq(8)').text();
                        var id_nivel        = $(this).find('td:eq(9)').text();
                        items.push({ mes_referencia: mes_referencia, 
                                    monto: monto,
                                    fecha_recibo: fecha_recibo,
                                    id_nivel: id_nivel,
                                    id_categoria: id_categoria,
                                    id_concepto: id_concepto
                                });
                    });

                    var datos = {
                        items: items,
                        total_factura: total_factura,
                        id_alumno: id_alumno,
                        id_anio: id_anio
                    };

                    if(id_alumno != '' && id_anio.trim() != ''){
                        // console.log(datos);
                        $('#content_dynamic_edit_arancel').html('');  
                        $.ajax({
                            url: 'controller/g_recibo.php',
                            type: 'POST',
                            data: datos,
                            dataType: 'html'
                        })
                        .done(function(data){
                            // alert(data);
                            $('#content_dynamic_edit_arancel').html('');    
                            $('#content_dynamic_edit_arancel').html(data); // mostrar la data
                            $("#ModalDetalleRecibo").modal("show");
                        })
                        .fail(function(){
                            $('#content_dynamic_edit_arancel').html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
                            $("#ModalDetalleRecibo").modal("show");
                        });
                    }
                });
            });
        </script>

        <!-- opcion para imprimir recibo -->
        <script>
            $(document).ready(function() {
                $("#btnImprimirRecibo").click(function() {
                    var contenidoRecibo = $("#content_dynamic_edit_arancel").html();
                    var ventanaImpresion = window.open('', '_blank');
                    ventanaImpresion.document.write('<html><head><title></title></head><body>');
                    ventanaImpresion.document.write(contenidoRecibo);
                    ventanaImpresion.document.write('</body></html>');
                    ventanaImpresion.document.close();
                    ventanaImpresion.print();
                    ventanaImpresion.close();

                    // Recargar la página principal después de imprimir
                    location.reload(true);
                });

                $('#ModalDetalleRecibo').on('hidden.bs.modal', function () {
                    location.reload(true);
                });
            });
        </script>

        <!-- Enviar email con recibo -->
        <script>
            $(document).ready(function() {
                $("#btnEnviarRecibo").click(function() {
                    var email_alumno = $('#ModalDetalleRecibo').find('#email_alumno').val();
                    var id_alumno_email = $('#ModalDetalleRecibo').find('#id_alumno_email').val();
                    var id_recibo_email = $('#ModalDetalleRecibo').find('#id_recibo_email').val();
                    // alert(email_alumno);
                    // verificar si el alumno tiene correo
                    if(email_alumno != ''){
                        var contenidoRecibo = $("#content_dynamic_edit_arancel").html();
                        // enviar email
                        var parametro = {
                            "email_alumno" : email_alumno,
                            "id_alumno_email" : id_alumno_email,
                            "id_recibo_email" : id_recibo_email,
                            "contenidoRecibo" : contenidoRecibo,
                        }
                        $.ajax({
                            type: 'POST',
                            url: 'controller/send_email_recibo.php',
                            data: parametro,
                            success: function(data) {
                                if (data == 'bien') {
                                    not6();
                                    setTimeout("location.href = 'crear_recibos.php'",2000);
                                } else {
                                    not5();
                                }
                            }
                        }); 
                    } else {
                        // levantar modal para agregar correo
                        $("#ModalNuevoCorreoAlumno").modal("show");
                    }
                });
            });
        </script>

        <!-- Enviar email y guardar email de alumno -->
        <script>
            $(document).ready(function() {
                $("#btnGuardarCorreo").click(function() {
                    var email_alumno = $('#ModalNuevoCorreoAlumno').find('#email_nuevo').val();
                    var id_alumno_email = $('#ModalDetalleRecibo').find('#id_alumno_email').val();
                    var id_recibo_email = $('#ModalDetalleRecibo').find('#id_recibo_email').val();

                    if(email_alumno != ''){
                        var contenidoRecibo = $("#content_dynamic_edit_arancel").html();
                        var parametro = {
                            "email_alumno" : email_alumno,
                            "id_alumno_email" : id_alumno_email,
                            "id_recibo_email" : id_recibo_email,
                            "contenidoRecibo" : contenidoRecibo,
                        }
                        $.ajax({
                            type: 'POST',
                            url: 'controller/send_email_recibo_dos.php',
                            data: parametro,
                            success: function(data) {
                                if (data == 'bien') {
                                    not6();
                                    setTimeout("location.href = 'crear_recibos.php'",2000);
                                } else {
                                    not5();
                                }
                            }
                        });
                    } else {
                        alert("Campo correo es necesario!");
                    }
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

        <!-- Enviar whasap del recibo -->
        <script>
            $(document).ready(function() {
                $("#btnEnviarReciboWhasap").click(function() {
                    var telefono_alumno = $('#ModalDetalleRecibo').find('#telefono_alumno').val();
                    var id_alumno_email = $('#ModalDetalleRecibo').find('#id_alumno_email').val();
                    var id_recibo_email = $('#ModalDetalleRecibo').find('#id_recibo_email').val();
                    // alert(email_alumno);
                    // verificar si el alumno tiene correo
                    if(telefono_alumno != ''){
                        var contenidoRecibo = $("#content_dynamic_edit_arancel").html();
                        // enviar email
                        var parametro = {
                            "telefono_alumno" : telefono_alumno,
                            "id_alumno_email" : id_alumno_email,
                            "id_recibo_email" : id_recibo_email,
                            "contenidoRecibo" : contenidoRecibo,
                        }
                        $.ajax({
                            type: 'POST',
                            url: 'controller/send_whasap_recibo.php',
                            data: parametro,
                            success: function(data) {
                                if (data == 'bien') {
                                    not7();
                                    setTimeout("location.href = 'crear_recibos.php'",2000);
                                } else {
                                    not5();
                                }
                            }
                        }); 
                    } else {
                        // levantar modal para agregar correo
                        // $("#ModalNuevoCorreoAlumno").modal("show");
                        alert("No tiene numero de whasap registrado");
                    }
                });
            });
        </script>
	</body>
</html>