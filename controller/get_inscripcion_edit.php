<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_inscripcion'])) 
	{
		$id_inscripcion = $_POST['id_inscripcion'];
		$queryData = mysqli_query($link,"SELECT * FROM tbl_inscripcion
                                            WHERE ID = '$id_inscripcion'");
		$rowData = mysqli_fetch_array($queryData);

        $id_matricula   = $rowData['IDMATRICULA'];
        $id_alumno      = $rowData['IDALUMNO'];
        $id_nivel       = $rowData['IDNIVEL'];
        $id_grado       = $rowData['IDGRADO'];
        $id_seccion     = $rowData['SECCION'];
        $id_anio        = $rowData['ANIOLECTIVO'];
?>

                            <form id="Form1" class="FormEditar" action="" method="" autocomplete="off">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Año lectivo</label>
                                        <div class="input-group date">
                                            <select data-plugin-selectTwo2 disabled="true" class="form-control" style="width: 100%" name="id_anio" id="id_anio_edit">
                                                <option value="">Año lectivo</option>
                                                <?php
                                                    $queryData = mysqli_query($link,"SELECT * FROM tbl_anios ORDER BY idanio DESC");
                                                    while($rowData = mysqli_fetch_array($queryData)){
                                                        if($id_anio == $rowData['anio']){
                                                            echo '<option selected value="'.$rowData['anio'].'">'.$rowData['anio'].'</option>';
                                                        } else {
                                                            echo '<option value="'.$rowData['anio'].'">'.$rowData['anio'].'</option>';
                                                        }
                                                    }
                                                ?>
                                            </select>

                                            <input type="hidden" name="id_inscripcion" value="<?php echo $id_inscripcion; ?>">
                                            <input type="hidden" name="id_matricula" value="<?php echo $id_matricula; ?>">
                                            <input type="hidden" name="id_alumno_input" value="<?php echo $id_alumno; ?>">
                                        </div>
                                    </div>
                                
                                    <div class="col-md-6">
                                        <label>Alumnos</label>
                                        <div class="input-group date">
                                            <select data-plugin-selectTwo2 disabled="true" class="form-control" style="width: 100%" id="id_alumno_edit" name="id_alumno">
                                                <option value="">Alumnos</option>
                                                <?php
                                                    $queryDato = mysqli_query($link,"SELECT A.EMAIL, M.IDALUMNO, A.NOMBREAPELLIDO, A.TELEFONO
                                                                                FROM tbl_matricula M
                                                                                INNER JOIN tbl_alumnos A
                                                                                ON A.IDALUMNO = M.IDALUMNO
                                                                                WHERE M.ANIO = '$id_anio'");
                                                    while($rowData = mysqli_fetch_array($queryDato)){
                                                        if($id_alumno == $rowData['IDALUMNO']){
                                                            $email    = $rowData['EMAIL'];
                                                            $telefono = $rowData['TELEFONO'];
                                                            echo '<option selected value="'.$rowData['IDALUMNO'].'">'.$rowData['NOMBREAPELLIDO'].'</option>';
                                                        } else {
                                                            echo '<option value="'.$rowData['IDALUMNO'].'">'.$rowData['NOMBREAPELLIDO'].'</option>';
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label>Correo</label>
                                        <input value="<?php echo $email; ?>" type="email" class="form-control" name="email" placeholder="Email">
                                    </div>

                                    <div class="col-md-12">
                                        <label>Telefono</label>
                                        <input disabled="true" value="<?php echo $telefono; ?>" type="text" class="form-control" name="telefono" placeholder="Telefono">
                                    </div>

                                    <div class="col-md-12">
                                        <label>Nivel</label>
                                        <div class="input-group date">
                                            <select data-plugin-selectTwo2 class="form-control" style="width: 100%" name="id_nivel" id="id_nivel_edit">
                                                <option value="">Nivel</option>
                                                <?php
                                                    $queryData = mysqli_query($link,"SELECT * FROM tbl_nivel");
                                                    while($rowData = mysqli_fetch_array($queryData)){
                                                        if($id_nivel == $rowData['IdNivel']){
                                                            echo '<option selected value="'.$rowData['IdNivel'].'">'.$rowData['Nivel'].'</option>';
                                                        } else {
                                                            echo '<option value="'.$rowData['IdNivel'].'">'.$rowData['Nivel'].'</option>';
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label>Grados</label>
                                        <div class="input-group date">
                                            <select data-plugin-selectTwo2 class="form-control" style="width: 100%" id="id_grado_edit" name="id_grado">
                                                <option value="">Grados</option>
                                                <?php
                                                    $queryDato = mysqli_query($link,"SELECT * FROM tbl_grados 
                                                                            WHERE IdNivel = '$id_nivel'");
                                                    while($rowData = mysqli_fetch_array($queryDato)){
                                                        if($id_grado == $rowData['IdGrados']){
                                                            echo '<option selected value="'.$rowData['IdGrados'].'">'.$rowData['Grado'].'</option>';
                                                        } else {
                                                            echo '<option value="'.$rowData['IdGrados'].'">'.$rowData['Grado'].'</option>';
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label>Seccion</label>
                                        <div class="input-group date">
                                            <select data-plugin-selectTwo2 class="form-control" style="width: 100%" name="id_seccion">
                                                <option value="">Seccion</option>
                                                <?php
                                                    $queryData = mysqli_query($link,"SELECT * FROM tbl_secciones");
                                                    while($rowData = mysqli_fetch_array($queryData)){
                                                        if($id_seccion == $rowData['SECCION']){
                                                            echo '<option selected value="'.$rowData['SECCION'].'">'.$rowData['SECCION'].'</option>';
                                                        } else {
                                                            echo '<option value="'.$rowData['SECCION'].'">'.$rowData['SECCION'].'</option>';
                                                        }
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

                            <!-- consulta al seleccionar año lectivo -->
                            <script>
                                $(document).ready(function() {
                                    $('#id_anio_edit').change(function(e) {
                                        e.preventDefault();
                                        var id_anio = $(this).val();
                                        // alert(id_usuario);
                                        $('#id_alumno_edit').html('');  
                                        $.ajax({
                                            url: 'controller/get_matricula_list.php',
                                            type: 'POST',
                                            data: 'id_anio='+id_anio,
                                            dataType: 'html'
                                        })
                                        .done(function(data){  
                                            $('#id_alumno_edit').html('');    
                                            $('#id_alumno_edit').html(data); // mostrar la data
                                        })
                                        .fail(function(){
                                            $('#id_alumno_edit').html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
                                        });
                                    });
                                });
                            </script>

                            <!-- consulta al seleccionar nivel -->
                            <script>
                                $(document).ready(function() {
                                    $('#id_nivel_edit').change(function(e) {
                                        e.preventDefault();
                                        var id_nivel = $(this).val();
                                        // alert(id_usuario);
                                        $('#id_grado_edit').html('');  
                                        $.ajax({
                                            url: 'controller/get_grado_list.php',
                                            type: 'POST',
                                            data: 'id_nivel='+id_nivel,
                                            dataType: 'html'
                                        })
                                        .done(function(data){  
                                            $('#id_grado_edit').html('');    
                                            $('#id_grado_edit').html(data); // mostrar la data
                                        })
                                        .fail(function(){
                                            $('#id_grado_edit').html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
                                        });
                                    });
                                });
                            </script>

                            <!-- consulta al seleccionar categoria -->
                            <script>
                                $(document).ready(function() {
                                    $('#id_categoria_edit').change(function(e) {
                                        e.preventDefault();
                                        var id_categoria = $(this).val();
                                        // alert(id_usuario);
                                        $('#id_concepto_edit').html('');  
                                        $.ajax({
                                            url: 'controller/get_concepto_list.php',
                                            type: 'POST',
                                            data: 'id_categoria='+id_categoria,
                                            dataType: 'html'
                                        })
                                        .done(function(data){  
                                            $('#id_concepto_edit').html('');    
                                            $('#id_concepto_edit').html(data); // mostrar la data
                                        })
                                        .fail(function(){
                                            $('#id_concepto_edit').html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
                                        });
                                    });
                                });
                            </script>

<?php
	}
?>