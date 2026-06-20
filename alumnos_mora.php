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

    $query = mysqli_query($link,"SELECT b.ID AS IdInscripcion, c.NOMBREAPELLIDO, b.IDNIVEL AS IdNivel
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
        $id_nivel = $rowDatos['IdNivel'];

        $queryDos = mysqli_query($link,"SELECT * FROM tbl_pagosmensualidades
                    WHERE IdInscripcion = '$IdInscripcion'");
        $rowDatosDos = mysqli_fetch_array($queryDos);
        $total_mes_mora = 0;
        $meses_mora = '';
        $meses_pagado = '';
        for ($i = 1; $i <= $mes_actual; $i++) {
            switch ($i) {
                case 1:
                    $mes = "Ene";
                    $mes_completo = "Enero";
                    break;
                case 2:
                    $mes = "Feb";
                    $mes_completo = "Febrero";
                    break;
                case 3:
                    $mes = "Mar";
                    $mes_completo = "Marzo";
                    break;
                case 4:
                    $mes = "Abr";
                    $mes_completo = "Abril";
                    break;
                case 5:
                    $mes = "May";
                    $mes_completo = "Mayo";
                    break;
                case 6:
                    $mes = "Jun";
                    $mes_completo = "Junio";
                    break;
                case 7:
                    $mes = "Jul";
                    $mes_completo = "Julio";
                    break;
                case 8:
                    $mes = "Ago";
                    $mes_completo = "Agosto";
                    break;
                case 9:
                    $mes = "Sep";
                    $mes_completo = "Septiembre";
                    break;
                case 10:
                    $mes = "Oct";
                    $mes_completo = "Octubre";
                    break;
                case 11:
                    $mes = "Nov";
                    $mes_completo = "Noviembre";
                    break;
                case 12:
                    $mes = "Dic";
                    $mes_completo = "Diciembre";
                    break;
            }

            if($rowDatosDos[$mes] != 'X'){
                $total_mes_mora++;
                $meses_mora .= $mes_completo.", ";
            } else {
                $meses_pagado .= $mes_completo.", ";
            }
        }

        if($total_mes_mora > 0){
            $total_alumnos_mora++;

            //obtener el monto que debe el alumno al mes
            $queryMonto = mysqli_query($link,"SELECT a.Monto 
                                                        FROM tbl_aranceles AS a 
                                                        INNER JOIN tbl_categoriapago AS b 
                                                        ON a.IdCategoria = b.Id 
                                                        WHERE b.Concepto = 'MENSUALIDAD' 
                                                        AND a.IdNivel = '$id_nivel'
                                                        AND a.Anio = '$ano_actual'");
            $rowMonto = mysqli_fetch_array($queryMonto);
            $monto = $rowMonto['Monto'];

            $datos_alumnos_mora[$contador] = array(
                "nombre" => $resultado = mb_convert_case(mb_strtolower($nombre), MB_CASE_TITLE, "UTF-8"),
                "total_mes" => $total_mes_mora,
                "meses_mora" => $meses_mora,
                "meses_pagado" => $meses_pagado,
                "monto" => $monto
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

		<title>Alumnos en mora</title>

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

                                    <form id="Form1" class="FormFiltro" action="" method="" autocomplete="off">
                                        <div class="row">
                                            <div class="text-center col-md-12 mt-3">
												<button class="btn btn-primary btnDescargarDatos_alumno" type="button" id="btnDescargarDatos"><i class="fa fa-file-pdf"></i> Descargar PDF</button>
                                                <?php
                                                if($_SESSION['tipo_usuario'] == 2) 
                                                {
                                                    echo '<button class="btn btn-primary btnEnviarEmail_alumno" type="button" id="btnEnviarEmail"><i class="fa fa-envelope"></i> Enviar por Email</button>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </form>
                                    
								</header>
								<div class="card-body">

									<table class="table table-bordered table-striped mb-0" id="example1">

										<thead>
											<tr>
                                                <th>#</th>
                                                <th>Nombre y apellido</th>
                                                <th>Año lectivo</th>
                                                <?php
                                                $meses_lista = [1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril',
                                                                5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto',
                                                                9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre'
                                                                ];
	                                            //recorrer desde el mes actual hasta el primero
                                                for ($i = 1; $i <= $mes_actual; $i++) {
                                                    echo '<th>'.$meses_lista[$i].'</th>';
                                                }
                                                ?>
                                                <th>Monto que debe</th>
                                                <th>Meses en mora</th>
                                            </tr>
										</thead>
										<tbody>
                                            <?php
                                                if($total_alumnos_mora > 0){
                                                    $contar = 1;
                                                    foreach ($datos_alumnos_mora as $item) {
                                                        $mes = "";
                                                        if($item['total_mes'] > 1){
                                                            $mes = "meses";
                                                        } else {
                                                            $mes = "mes";
                                                        }
                                                        //monto total con dos decimales
                                                        $monto_total = $item['monto'] * $item['total_mes'];
                                                        $monto_total = number_format($monto_total, 2, '.', ',');
                                                        
                                                        echo '
                                                        <tr>
                                                            <td>'.$contar++.'</td>
                                                            <td>'.$item['nombre'].'</td>
                                                            <td>'.$ano_actual.'</td>';
                                                            //recorrer desde el mes actual hasta el primero
                                                            for ($i = 1; $i <= $mes_actual; $i++) {
                                                                if(str_contains($item['meses_pagado'], $meses_lista[$i])){
                                                                    $mes_bandera = '<span class="badge badge-success">Pagado</span>';
                                                                } else {
                                                                    $mes_bandera = '<span class="badge badge-danger">No pagado</span>';
                                                                }
                                                                echo '<td>'.$mes_bandera.'</td>';
                                                            }
                                                            echo '<td>'.$monto_total.'</td>
                                                            <td>'.$item['total_mes'].' '.$mes.'</td>
                                                        </tr>
                                                        ';
                                                    }
                                                }
                                            ?>
										</tbody>
                                        <tfoot>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <?php
                                                for ($i = 2; $i <= $mes_actual; $i++) {
                                                    echo '<th></th>';
                                                }
                                                ?>
                                                <th class="text-end">TOTAL:</th> <!-- Etiqueta -->
                                                <th id="footer-total" class="text-end"></th> <!-- Aquí pintamos el total -->
                                                <th></th>
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

            <!-- Modal nuevo -->
            <div class="modal fade" id="ModalNuevo" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Nueva inscripcion</h5>
                        </div>
                        <div class="modal-body">
                            <form id="Form1" class="FormNuevo" action="" method="" autocomplete="off">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Año lectivo</label>
                                        <div class="input-group date">
                                            <select data-plugin-selectTwo2 class="form-control" style="width: 100%" name="id_anio" id="id_anio">
                                                <option value="">Año lectivo</option>
                                                <?php
                                                    $queryData = mysqli_query($link,"SELECT * FROM tbl_anios ORDER BY idanio DESC");
                                                    while($rowData = mysqli_fetch_array($queryData)){
                                                        echo '<option value="'.$rowData['anio'].'">'.$rowData['anio'].'</option>';
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                
                                    <div class="col-md-6">
                                        <label>Alumnos</label>
                                        <div class="input-group date">
                                            <select data-plugin-selectTwo2 class="form-control" style="width: 100%" id="id_alumno" name="id_alumno">
                                                <option value="">Alumnos</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label>Correo</label>
                                        <input type="email" class="form-control" name="email" id="email" placeholder="Email">
                                    </div>

                                    <div class="col-md-12">
                                        <label>Nivel</label>
                                        <div class="input-group date">
                                            <select data-plugin-selectTwo2 class="form-control" style="width: 100%" name="id_nivel" id="id_nivel">
                                                <option value="">Nivel</option>
                                                <?php
                                                    $queryData = mysqli_query($link,"SELECT * FROM tbl_nivel");
                                                    while($rowData = mysqli_fetch_array($queryData)){
                                                        echo '<option value="'.$rowData['IdNivel'].'">'.$rowData['Nivel'].'</option>';
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label>Grados</label>
                                        <div class="input-group date">
                                            <select data-plugin-selectTwo2 class="form-control" style="width: 100%" id="id_grado" name="id_grado">
                                                <option value="">Grados</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label>Seccion</label>
                                        <div class="input-group date">
                                            <select data-plugin-selectTwo2 class="form-control" style="width: 100%" name="id_seccion" id="id_seccion">
                                                <option value="">Seccion</option>
                                                <?php
                                                    $queryData = mysqli_query($link,"SELECT * FROM tbl_secciones");
                                                    while($rowData = mysqli_fetch_array($queryData)){
                                                        echo '<option value="'.$rowData['SECCION'].'">'.$rowData['SECCION'].'</option>';
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="text-center col-md-12 mt-3">
                                        <button class="btn btn-default" data-dismiss="modal" id="btnImprimirRecibo" type="reset"><i class="fa fa-close"></i> Cancelar</button>
                                        <button class="btn btn-primary" type="button" id="btnGardar"><i class="fa fa-save"></i> Guardar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer"></div>
                    </div>
                </div>
            </div>

            <!-- Modal editar -->
            <div class="modal fade" id="ModalEditar" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Editar</h5>
                        </div>
                        <div class="modal-body">
                            <div id="content_dynamic_edit_arancel">
                                
                            </div>
                        </div>
                        <div class="modal-footer"></div>
                    </div>
                </div>
            </div>

            <!-- Modal eliminar -->
            <div class="modal fade" id="modalEliminar">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Eliminar</h5>
                        </div>
                        <div class="modal-body">
                            <form id="Form2" class="FormC" action="" method="" autocomplete="off">
                                <h4 class="text-center">¿Esta seguro de eliminar esta inscripcion?</h4>
                                <input type="hidden" name="id_inscripcion" id="id_inscripcion">
                                    
                                <div class="text-center col-md-12">
                                    <button class="btn btn-default" data-dismiss="modal" type="reset"><i class="fa fa-close"></i> Cancelar</button>
                                    <button class="btn btn-danger" type="button" id="btnEliminar"><i class="fa fa-trash"></i> Elimimar</button>
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


        <!-- script de tabla -->
        <script>
            $(function () {
                $('#example1').DataTable({
                    "searching": false,
                });
                actualizarTotalFiltrado();

                // Helper: pide el total filtrado al backend
				function actualizarTotalFiltrado() {
					const id_alumno   = '';
                    var numeroDeColumnas = $("#example1 tr:first td").length;

					$.getJSON('ajax_table/sum_reporte_alumnos_mora.php', {
						id_alumno
					}).done(function (resp) {
						const total = resp.totalFiltrado;
						const formateado = total.toLocaleString('es-NI', {
							minimumFractionDigits: 2,
							maximumFractionDigits: 2
						});
						// Pinta en el pie (columna 7)
						const api = $('#example1').DataTable().api ? $('#example1').DataTable().api() : $('#example1').DataTable();
						$(api.column(numeroDeColumnas-2).footer()).html('C$ ' + formateado);
					}).fail(function () {
						// opcional: deja vacío o muestra 0
						const api = $('#example1').DataTable();
						$(api.column(numeroDeColumnas-2).footer()).html('C$ 0.00');
					});
				}
            })
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

        <!-- consulta al seleccionar nivel -->
        <script>
            $(document).ready(function() {
                $('#id_nivel').change(function(e) {
                    e.preventDefault();
                    var id_nivel = $(this).val();
                    // alert(id_usuario);
                    $('#id_grado').html('');  
                    $.ajax({
                        url: 'controller/get_grado_list.php',
                        type: 'POST',
                        data: 'id_nivel='+id_nivel,
                        dataType: 'html'
                    })
                    .done(function(data){  
                        $('#id_grado').html('');    
                        $('#id_grado').html(data); // mostrar la data
                    })
                    .fail(function(){
                        $('#id_grado').html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
                    });
                });
            });
        </script>

        <!-- guardar -->
        <script type="text/javascript">
            $(document).ready(function(){
                $(document).on('click', '#btnGardar', function(e){
                    e.preventDefault();
                    $.ajax({
                        type: 'POST',
                        url: 'controller/g_inscripcion.php',
                        data: $('.FormNuevo').serialize(),
                        success: function(data) {
                            if (data == 'bien') {
                                not1();
                                // $('#id_anio').val('').trigger('change');
                                $('#id_alumno').val('').trigger('change');
                                // $('#id_nivel').val('').trigger('change');
                                // $('#id_grado').val('').trigger('change');
                                // $('#id_seccion').val('').trigger('change');
                                $('#email').val('');
                                // setTimeout("location.href = 'inscripcion_alumno.php'",3000);
                            } else {
                                alert(data);
                            }
                        }
                    });      
                });
            });
        </script>

        <!-- editar -->
        <script type="text/javascript">
            $(document).ready(function(){
                // primer paso mostrar datos
                $(document).on('click', '.btn1', function(e){
                    e.preventDefault();
                    var id_inscripcion = $(this).data('id');
                    // alert(id_usuario);
                    $('#content_dynamic_edit_arancel').html('');  
                    $.ajax({
                        url: 'controller/get_inscripcion_edit.php',
                        type: 'POST',
                        data: 'id_inscripcion='+id_inscripcion,
                        dataType: 'html'
                    })
                    .done(function(data){  
                        $('#content_dynamic_edit_arancel').html('');    
                        $('#content_dynamic_edit_arancel').html(data); // mostrar la data
                    })
                    .fail(function(){
                        $('#content_dynamic_edit_arancel').html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
                    });
                });

                // segundo paso actualizar datos
                $(document).on('click', '#btnA', function(e){
                    e.preventDefault();
                    $.ajax({
                        type: 'POST',
                        url: 'controller/a_inscripcion.php',
                        data: $('.FormEditar').serialize(),
                        success: function(data) {
                            if (data == 'bien') {
                                not3();
                                setTimeout("location.href = 'inscripcion_alumno.php'",3000);
                            } else {
                                alert(data);
                            }
                        }
                    });      
                });
            });
        </script>

        <!-- eliminar -->
        <script type="text/javascript">
            $(document).ready(function(){
                // primer paso
                $(document).on('click', '.btn2', function(e){
                    e.preventDefault();
                    $('#modalEliminar').modal('show');
                    var id_inscripcion = $(this).data('id');
                    $("#id_inscripcion").val(id_inscripcion);
                });

                // segundo paso
                $(document).on('click', '#btnEliminar', function(e){
                    e.preventDefault();
                    var id_inscripcion = $("#id_inscripcion").val();
                    $.ajax({
                        type: 'POST',
                        url: 'controller/e_inscripcion.php',
                        data: 'id_inscripcion='+id_inscripcion,
                        success: function(data) {
                            if (data == 'bien') {
                                not4();
                                setTimeout("location.href = 'inscripcion_alumno.php'",2000);
                            } else {
                                not5();
                            }
                        }
                    });      
                });
            });
        </script>

        <!-- al cerrar modal -->
        <script>
            $(document).ready(function() {
                $("#btnImprimirRecibo").click(function() {
                    location.reload(true);
                });

                $('#ModalNuevo').on('hidden.bs.modal', function () {
                    location.reload(true);
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

        <!-- descarha el pdf -->
        <script>
            $(document).ready(function () {
                $(document).on('click', '#btnDescargarDatos', function (e) {
                    e.preventDefault();
                    // Si quieres forzar un año manual, puedes pasar ?anio=2025
                    window.open('reports/reporte_mora_pdf.php', '_blank');
                });
            });
        </script>

        <script>
            $(document).ready(function () {
                $(document).on('click', '#btnEnviarEmail', function (e) {
                    e.preventDefault();

                    const $btn = $(this);
                    const oldHtml = $btn.html();
                    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');

                    $.ajax({
                        url: 'reports/send_reporte_mora_email.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            // anio: $('#id_anio').val() || ''   // <- por si algún día decides mandar un año manual
                        }
                    })
                    .done(function (resp) {
                        if (resp && resp.ok) {
                            notif({ msg: resp.msg || 'Correo enviado correctamente.', type: 'success', position: 'center' });
                        } else {
                            notif({ msg: (resp && resp.error) ? resp.error : 'No se pudo enviar el correo.', type: 'error', position: 'center' });
                        }
                    })
                    .fail(function () {
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