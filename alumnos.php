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

		<title>Alumnos</title>

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
		<link rel="stylesheet" href="assets/vendor/select2/css/select2.css" />
		<link rel="stylesheet" href="assets/vendor/select2-bootstrap-theme/select2-bootstrap.min.css" />
		<link rel="stylesheet" href="assets/vendor/datatables/media/css/dataTables.bootstrap5.css" />
        <link rel="stylesheet" href="assets/vendor/dropzone/basic.css" />
		<link rel="stylesheet" href="assets/vendor/dropzone/dropzone.css" />

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

        <script>
            function not6(){
                notif({
                    msg: "Correo enviado!",
                    type: "success",
                    position: "center"
                });
            }
        </script>

        <style>
            /* Para que el área sea visible/clickable */
            #fotoDropzoneEdit{
                border: 2px dashed #c9c9c9;
                min-height: 120px;
                display: flex; align-items: center; justify-content: center;
                background: #fafafa;
                cursor: pointer;
                border-radius: 8px;
                padding: 10px;
                text-align: center;
            }
        </style>

        <style>
            /* Columna foto con ancho fijo para que la tabla no la estire */
            td.col-foto { width: 96px; }

            /* Avatar consistente en todas las recargas */
            img.avatar {
                width: 72px !important;      /* tamaño fijo */
                height: 72px !important;     /* tamaño fijo */
                border-radius: 50%;
                object-fit: cover;           /* recorta sin deformar */
                display: block;
                border: 1px solid #e6e6e6;
                background: #fff;
            }

            /* (Opcional) evita que DataTables/tema altere imágenes dentro de tablas */
            table.dataTable img { max-width: none !important; }
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
                                    <li class="nav-parent nav-expanded">
				                        <a class="nav-link" href="#">
				                            <i class="bx bx-table" aria-hidden="true"></i>
				                            <span>Menu</span>
				                        </a>
				                        <ul class="nav nav-children">
				                            <li class="nav-active">
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

                                    <button class="btn btn-success"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#ModalNuevo">
                                        <i class="fa fa-plus"></i> Nuevo alumno
                                    </button>
                                    
								</header>
								<div class="card-body">

                                    <table class="table table-sm table-bordered table-striped small mb-0" id="example1">

										<thead>
											<tr>
												<th>#</th>
                                                <th>Foto</th>
                                                <th>Nombre y apellido</th>
                                                <th>Nombre del padre</th>
                                                <th>Nombre de la madre</th>
                                                <th>Telefono</th>
                                                <th class="text-center">Opciones</th>
											</tr>
										</thead>
										<tbody>
										</tbody>
									</table>
								</div>
							</section>
						</div>
					</div>

					<!-- end: page -->
				</section>
			</div>

            <!-- Modal nuevo -->
            <div class="modal fade" id="ModalNuevo">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Nuevo alumno</h5>
                        </div>
                        <div class="modal-body">
                            <form id="Form1" class="FormNuevo" action="" method="" autocomplete="off">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Nombres y apellidos</label>
                                        <input type="text" class="form-control" name="nombre" placeholder="Nombres y apellidos">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Fecha nacimiento</label>
                                        <input type="text" class="form-control pull-right" id="datepicker" name="fecha_nacimiento">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Nombre del papa</label>
                                        <input type="text" class="form-control" name="papa" placeholder="Nombre del papa">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Nombre de la mama</label>
                                        <input type="text" class="form-control" name="mama" placeholder="Nombre de la mama">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Telefono</label>
                                        <input type="text" class="form-control" name="telefono" placeholder="Telefono">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Correo</label>
                                        <input type="email" class="form-control" name="email" placeholder="Email">
                                    </div>

                                    <div class="col-md-12">
                                        <label>Direccion</label>
                                        <textarea name="direccion" class="form-control" rows="3" placeholder=""></textarea>
                                    </div>

                                    <!-- NUEVO: foto de alumno -->
                                    <div class="col-md-12 mt-3">
                                        <label>Foto del alumno (arrastrar y soltar o click)</label>

                                        <!-- Preview -->
                                        <div id="foto-preview" class="mb-2" style="display:none;">
                                            <img src="" alt="Foto del alumno" style="width:120px;height:120px;object-fit:contain;border:1px solid #ddd;padding:4px;border-radius:6px;background:#fff;">
                                        </div>

                                        <!-- Dropzone -->
                                        <div class="dropzone" id="fotoDropzone" data-upload-url="controller/upload_foto_alumno.php"></div>

                                        <!-- URL devuelta por el upload -->
                                        <input type="hidden" name="foto_alumno" id="foto_alumno">

                                        <small class="text-muted d-block mt-2">
                                            Formatos: JPG, PNG, WEBP, GIF. Máx: 3 MB. (Se ajusta a 400×400)
                                        </small>
                                    </div>

                                    <div style="margin: 10px 0px;" class="text-center col-md-12">
                                        <button class="btn btn-secondary"
                                                type="reset"
                                                data-bs-dismiss="modal">
                                            <i class="fa fa-times"></i> Cancelar
                                        </button>

                                        <button class="btn btn-primary"
                                                type="button"
                                                id="btnGuardar">
                                            <i class="fa fa-save"></i> Guardar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer"></div>
                    </div>
                </div>
            </div>

            <!-- Modal editar -->
            <div class="modal fade" id="ModalEditar">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Editar alumno</h5>
                        </div>
                        <div class="modal-body">
                            <div id="content_dynamic_edit_user">
                                
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
                                <h4 class="text-center">¿Esta seguro de eliminar al alumno?</h4>
                                <input type="hidden" name="id_alumno" id="id_alumno">
                                    
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

            <!-- Modal ver mas -->
            <div class="modal fade" id="ModalVerMas">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div id="content_dynamic_more_user">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="text-center col-md-12 mt-3">
                                <button class="btn btn-default" data-dismiss="modal" type="reset"><i class="fa fa-times"></i> Cerrar</button>
                                <button id="btnMail" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-envelope"></i> Enviar Email</button>
                            </div>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>

            <!-- Modal nuevo -->
            <div class="modal fade" id="ModalEmail">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Nuevo mensaje</h5>
                        </div>
                        <div class="modal-body">
                            <form id="Form3" class="FormNuevoMensaje" action="" method="" autocomplete="off">
                                <div class="form-group col-md-12">
                                    <label>Asunto</label>
                                    <input type="text" class="form-control" name="asunto" placeholder="">
                                    <input type="hidden" class="form-control" id="correo_alumno" name="correo_alumno" placeholder="">
                                    <input type="hidden" class="form-control" id="id_alumno_mail" name="id_alumno_mail" placeholder="">
                                </div>

                                <div class="form-group col-md-12">
                                    <label>Mensaje</label>
                                    <textarea name="mensaje" class="form-control" rows="3" placeholder=""></textarea>
                                </div>

                                <div class="text-center col-md-12 mt-3">
                                    <button class="btn btn-default" data-dismiss="modal" type="reset"><i class="fa fa-close"></i> Cancelar</button>
                                    <button class="btn btn-primary" type="button" id="btnSendEmail"><i class="fa fa-send"></i> Enviar</button>
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
		<script src="assets/vendor/select2/js/select2.js"></script>
		<script src="assets/vendor/datatables/media/js/jquery.dataTables.min.js"></script>
		<script src="assets/vendor/datatables/media/js/dataTables.bootstrap5.min.js"></script>
		<script src="assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/dataTables.buttons.min.js"></script>
		<script src="assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.bootstrap4.min.js"></script>
		<script src="assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.html5.min.js"></script>
		<script src="assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.print.min.js"></script>
		<script src="assets/vendor/datatables/extras/TableTools/JSZip-2.5.0/jszip.min.js"></script>
		<script src="assets/vendor/datatables/extras/TableTools/pdfmake-0.1.32/pdfmake.min.js"></script>
		<script src="assets/vendor/datatables/extras/TableTools/pdfmake-0.1.32/vfs_fonts.js"></script>
        <script src="assets/vendor/dropzone/dropzone.js"></script>

		<!-- Theme Base, Components and Settings -->
		<script src="assets/js/theme.js"></script>

		<!-- Theme Custom -->
		<script src="assets/js/custom.js"></script>

		<!-- Theme Initialization Files -->
		<script src="assets/js/theme.init.js"></script>

        <!-- bootstrap datepicker -->
        <script src="assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>

        <!-- script de fecha -->
        <script>
            $(document).ready(function() {
                $('#datepicker').datepicker({
                    format: 'dd/mm/yyyy'
                });
            });
        </script>

        <!-- script de tabla -->
        <script>
            $(function () {
                $('#example1').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "ajax": "ajax_table/ajax_table_alumno.php",
                    "columns": [
                        { "searchable": false },
                        { "searchable": false },
                        { "searchable": true },
                        { "searchable": false },
                        { "searchable": false },
                        { "searchable": false },
                        { "searchable": false },
                    ],
                    "createdRow": function ( row, data, index ) {
                        //
                        if (data[6]) {
                              const id = data[6];
                            $('td', row).eq(6).addClass("text-center");
                            var html = `
                                    <div class="dropdown">
                                    <button
                                        class="btn btn-primary dropdown-toggle"
                                        type="button"
                                        style="font-size: small;"
                                        id="optionsMenu${id}"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                    >
                                        Opciones
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="optionsMenu${id}">
                                        <li>
                                        <a
                                            class="dropdown-item btn0"
                                            href="#"
                                            data-id="${id}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#ModalVerMas"
                                        >
                                            <i class="fa fa-user"></i> Ver más
                                        </a>
                                        </li>
                                        <li>
                                        <a
                                            class="dropdown-item btn1"
                                            href="#"
                                            data-id="${id}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#ModalEditar"
                                        >
                                            <i class="fa fa-user-edit"></i> Editar
                                        </a>
                                        </li>`;
                                        <?php
                                        if ($_SESSION['tipo_usuario'] == 2) 
                                        {
                                        ?>
                                        html += `<li>
                                        <a
                                            class="dropdown-item btn2"
                                            href="#"
                                            data-id="${id}"
                                        >
                                            <i class="fa fa-user-times"></i> Eliminar
                                        </a>
                                        </li>`;
                                        <?php
                                        }
                                        ?>
                                    html += `</ul>
                                    </div>
                                `;
                            $('td', row).eq(6).html(html);
                        }

                        if (data[1]) {
                            $('td', row).eq(1)
                                .addClass('col-foto text-center')
                                .html('<img src="'+data[1]+'" class="avatar" alt="Foto">');
                        }
                    }
                });
            })
        </script>

        <!-- guardar -->
        <script type="text/javascript">
            $(document).ready(function(){
                $(document).on('click', '#btnGuardar', function(e){
                    e.preventDefault();
                    $.ajax({
                        type: 'POST',
                        url: 'controller/g_alumno.php',
                        data: $('.FormNuevo').serialize(),
                        success: function(data) {
                            if (data == 'bien') {
                                not1();
                                setTimeout("location.href = 'alumnos.php'",3000);
                            } else{
                                not2();
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
                    var id_alumno = $(this).data('id');
                    // alert(id_usuario);
                    $('#content_dynamic_edit_user').html('');  
                    $.ajax({
                        url: 'controller/get_alumno_edit.php',
                        type: 'POST',
                        data: 'id_alumno='+id_alumno,
                        dataType: 'html'
                    })
                    .done(function(data){  
                        $('#content_dynamic_edit_user').html('');    
                        $('#content_dynamic_edit_user').html(data); // mostrar la data

                        // ⬇️ IMPORTANTE: inicializar Dropzone y datepicker DEL EDIT aquí
                        initEditFotoDropzoneOnce();
                    })
                    .fail(function(){
                        $('#content_dynamic_edit_user').html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
                    });
                });

                // segundo paso actualizar datos
                $(document).on('click', '#btnA', function(e){
                    e.preventDefault();
                    $.ajax({
                        type: 'POST',
                        url: 'controller/a_alumno.php',
                        data: $('.FormEditar').serialize(),
                        success: function(data) {
                            if (data == 'bien') {
                                not3();
                                setTimeout("location.href = 'alumnos.php'",3000);
                            } else {
                                not2();
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
                    var id_alumno = $(this).data('id');
                    $("#id_alumno").val(id_alumno);
                });

                // segundo paso
                $(document).on('click', '#btnEliminar', function(e){
                    e.preventDefault();
                    var id_alumno = $("#id_alumno").val();
                    $.ajax({
                        type: 'POST',
                        url: 'controller/e_alumno.php',
                        data: 'id_alumno='+id_alumno,
                        success: function(data) {
                            if (data == 'bien') {
                                not4();
                                setTimeout("location.href = 'alumnos.php'",2000);
                            } else {
                                not5();
                            }
                        }
                    });      
                });
            });
        </script>

        <!-- ver mas -->
        <script type="text/javascript">
            $(document).ready(function(){
                // primer paso mostrar datos
                $(document).on('click', '.btn0', function(e){
                    e.preventDefault();
                    var id_alumno = $(this).data('id');
                    // alert(id_usuario);
                    $('#content_dynamic_more_user').html('');  
                    $.ajax({
                        url: 'controller/get_data_alumno.php',
                        type: 'POST',
                        data: 'id_alumno='+id_alumno,
                        dataType: 'html'
                    })
                    .done(function(data){  
                        $('#content_dynamic_more_user').html('');    
                        $('#content_dynamic_more_user').html(data); // mostrar la data
                    })
                    .fail(function(){
                        $('#content_dynamic_more_user').html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
                    });
                });
            });
        </script>

        <!-- opcion enviar mail -->
        <script type="text/javascript">
            $(document).ready(function(){
                // primer paso
                $(document).on('click', '#btnMail', function(e){
                    e.preventDefault();
                    var id_alumno = $('#ModalVerMas').find('#id_alumno').val();
                    var email = $('#ModalVerMas').find('#id_email').val();
                    // alert(email);
                    if (email != '') {
                        // modal para mensaje
                        $("#id_alumno_mail").val(id_alumno);
                        $("#correo_alumno").val(email);

                        $("#ModalEmail").modal("show");
                    } else {
                        alert('Este alumno no tiene correo electronico');
                    }
                });

                // segundo paso enviar correo
                $(document).on('click', '#btnSendEmail', function(e){
                    e.preventDefault();
                    $.ajax({
                        type: 'POST',
                        url: 'controller/send_email_individual.php',
                        data: $('.FormNuevoMensaje').serialize(),
                        success: function(data) {
                            if (data == 'bien') {
                                not6();
                                setTimeout("location.href = 'alumnos.php'",3000);
                            } else{
                                not5();
                            }
                        }
                    });
                });
            });
        </script>

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

        <script>
            // Desactiva el auto-descubrimiento global ANTES de que ocurra DOMContentLoaded
            if (window.Dropzone) {
                Dropzone.autoDiscover = false;
            }
        </script>

        <script>
            $(document).ready(function(){
                function initFotoDropzoneOnce() {
                    if (!window.Dropzone) return;
                    Dropzone.autoDiscover = false;

                    const el = document.getElementById('fotoDropzone');
                    if (!el) return;

                    // destruir instancia previa si reabren el modal
                    if (window._fotoDZ && typeof window._fotoDZ.destroy === 'function') {
                        try { window._fotoDZ.destroy(); } catch (e) {}
                    }

                    const uploadUrl = el.getAttribute('data-upload-url') || 'controller/upload_foto_alumno.php';

                    window._fotoDZ = new Dropzone(el, {
                    url: uploadUrl,
                    paramName: "file",
                    maxFiles: 1,
                    maxFilesize: 3,
                    acceptedFiles: "image/*",
                    addRemoveLinks: true,
                    dictDefaultMessage: "Suelta la imagen aquí o haz clic para subir",

                    // Validación mínima de dimensiones en cliente
                    accept: function(file, done) {
                        const _URL = window.URL || window.webkitURL;
                        const img = new Image();
                        img.onload = function() {
                            if (this.width < 400 || this.height < 400) {
                                done("La imagen debe ser de al menos 400×400 píxeles.");
                            } else {
                                done();
                            }
                        };
                        img.onerror = function(){ done("No se pudo leer la imagen."); };
                        img.src = _URL.createObjectURL(file);
                    },

                    init: function () {
                        this.on('maxfilesexceeded', function(file){
                            this.removeFile(file);
                            alert('Solo puedes subir 1 imagen.');
                        });

                        this.on("success", function(file, resp){
                        try { if (typeof resp === 'string') resp = JSON.parse(resp); } catch(e){}
                        if (resp && resp.status === 'ok' && resp.url) {
                            $('#foto_alumno').val(resp.url);
                            $('#foto-preview').show()
                            .find('img').attr('src', resp.url);
                            // limitar a 1 archivo
                            while (this.files.length > 1) this.removeFile(this.files[0]);
                        } else {
                            alert("No se pudo procesar la imagen.");
                            this.removeFile(file);
                        }
                        });
                        this.on("removedfile", function(){
                            $('#foto_alumno').val('');
                            $('#foto-preview').hide().find('img').attr('src','');
                        });
                    }
                    });
                }

                // Cuando abres el modal "Nuevo alumno"
                $('#ModalNuevo').on('shown.bs.modal', function () {
                    initFotoDropzoneOnce();
                });
            });
        </script>

        <script>
            // Desactiva auto-discovery global (hazlo una sola vez en la página)
            if (window.Dropzone) Dropzone.autoDiscover = false;

            let _dzEdit = null;

            $('#ModalEditar').on('hidden.bs.modal', function () {
                if (_dzEdit && typeof _dzEdit.destroy === 'function') {
                    try { _dzEdit.destroy(); } catch(e){}
                    _dzEdit = null;
                }
            });

            function initEditFotoDropzoneOnce() {
                if (!window.Dropzone) { console.warn('Dropzone no cargado'); return; }

                const el = document.getElementById('fotoDropzoneEdit');
                if (!el) { console.warn('No existe #fotoDropzoneEdit aún'); return; }

                // si ya hay una instancia anterior, destruirla
                if (el.dropzone) {
                    try { el.dropzone.destroy(); } catch(e){}
                }
                if (_dzEdit && typeof _dzEdit.destroy === 'function') {
                    try { _dzEdit.destroy(); } catch(e){}
                }

                const uploadUrl = el.getAttribute('data-upload-url') || 'controller/upload_foto_alumno.php';

                _dzEdit = new Dropzone(el, {
                    url: uploadUrl,
                    clickable: '#fotoDropzoneEdit',
                    previewsContainer: null,
                    paramName: "file",
                    maxFiles: 1,
                    uploadMultiple: false,
                    parallelUploads: 1,
                    maxFilesize: 3,           // MB (validación cliente)
                    acceptedFiles: "image/*",
                    addRemoveLinks: false,    // usas tu botón “Quitar foto”
                    dictDefaultMessage: "Suelta la imagen aquí o haz clic para subir",

                    accept: function(file, done) {
                        const _URL = window.URL || window.webkitURL;
                        const img = new Image();
                        img.onload = function() {
                            if (this.width < 400 || this.height < 400) {
                                done("La imagen debe ser de al menos 400×400 píxeles.");
                            } else { done(); }
                        };
                        img.onerror = function(){ done("No se pudo leer la imagen."); };
                        img.src = _URL.createObjectURL(file);
                    },

                    init: function () {
                        this.on('addedfile', function(file){
                            if (this.files.length > 1) this.removeFile(this.files[0]);
                        });

                        this.on('maxfilesexceeded', function(file){
                            this.removeFile(file);
                            alert('Solo puedes subir 1 imagen.');
                        });

                        this.on("success", function(file, resp){
                            try { if (typeof resp === 'string') resp = JSON.parse(resp); } catch(e){
                                console.error('Respuesta no-JSON:', resp);
                            }
                            if (resp && resp.status === 'ok' && resp.url) {
                                $('#foto_alumno_edit').val(resp.url);
                                $('#foto-preview-edit').show().find('img').attr('src', resp.url);
                            } else {
                                alert("No se pudo procesar la imagen.");
                                console.error('Respuesta upload:', resp);
                                this.removeFile(file);
                            }
                        });

                        this.on("error", function(file, errorMessage, xhr){
                            console.error('DZ error ->', errorMessage, xhr && xhr.status, xhr && xhr.responseText);
                            alert('Error subiendo imagen' + (xhr ? (': ' + xhr.status) : ''));
                        });
                    }
                });

                // Botón “Quitar foto” (lo ligamos cada vez que se inserta el HTML)
                $('#btnQuitarFotoEdit').off('click').on('click', function(){
                    $('#foto_alumno_edit').val('');
                    $('#foto-preview-edit').hide().find('img').attr('src','');
                    if (_dzEdit) _dzEdit.removeAllFiles(true);
                });

                // Datepicker del edit
                $('#datepicker_2').datepicker({ format: 'dd/mm/yyyy' });
            }
        </script>

	</body>
</html>