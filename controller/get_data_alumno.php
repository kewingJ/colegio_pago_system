<?php
include_once '../includes/config.php';
include_once '../includes/security.php';

session_start();
$id = $_SESSION['id_usuario'];

if (isset($_POST['id_alumno'])) {
    $id_alumno = $_POST['id_alumno'];

    // 1) Datos generales del alumno
    $q0 = mysqli_query($link, "
      SELECT NOMBREAPELLIDO, FECHANACIMIENTO, NOMBREMADRE, NOMBREPADRE,
             DIRECCION, TELEFONO, EMAIL
      FROM tbl_alumnos
      WHERE IDALUMNO = '$id_alumno'
    ");
    $row0 = mysqli_fetch_assoc($q0);

    $nombre         = $row0['NOMBREAPELLIDO'];
    $fecha_nac_orig = $row0['FECHANACIMIENTO'];
    $mama           = $row0['NOMBREMADRE'];
    $papa           = $row0['NOMBREPADRE'];
    $direccion      = $row0['DIRECCION'];
    $telefono       = $row0['TELEFONO'];
    $email          = $row0['EMAIL'];
    $fecha_nac      = date("d/m/Y", strtotime($fecha_nac_orig));

    // 2) Cabecera estilo tarjeta
    echo <<<HTML
<div class="bg-primary text-white p-3 mb-3 d-flex align-items-center">
  <i class="fa fa-user-graduate fa-2x me-3"></i>
  <div>
    <h5 class="mb-0">{$nombre}</h5>
    <small>{$fecha_nac}</small>
  </div>
  <!-- los hidden los uso luego para enviar el mail -->
  <input type="hidden" id="id_alumno" value="{$id_alumno}">
  <input type="hidden" id="id_email" value="{$email}">
</div>
HTML;

    // 3) Lista de datos
    echo <<<HTML
<ul class="list-group mb-4">
  <li class="list-group-item"><strong>Nombre mamá:</strong> {$mama}</li>
  <li class="list-group-item"><strong>Nombre papá:</strong> {$papa}</li>
  <li class="list-group-item"><strong>Teléfono:</strong> {$telefono}</li>
  <li class="list-group-item"><strong>Mail:</strong> {$email}</li>
  <li class="list-group-item">
    <strong>Dirección:</strong><br>
    {$direccion}
  </li>
</ul>
HTML;

    // 4) Tabla de matriculas
    echo '<div class="table-responsive">';
    echo '<table class="table table-sm table-bordered mb-0">';
    echo '<thead class="table-light">
            <tr>
              <th>#</th>
              <th>Nivel</th>
              <th>Grado</th>
              <th>Sección</th>
              <th>Año</th>
            </tr>
          </thead>';
    echo '<tbody>';
    $q1 = mysqli_query($link, "
      SELECT a.anio, b.SECCION, c.Nivel, d.Grado
      FROM tbl_matricula a
      JOIN tbl_inscripcion b ON a.ID = b.IDMATRICULA
      JOIN tbl_nivel c ON b.IDNIVEL = c.IdNivel
      JOIN tbl_grados d ON b.IDGRADO = d.IdGrados
      WHERE a.IDALUMNO = '$id_alumno'
      ORDER BY a.ANIO DESC
    ");
    $i = 1;
    while ($r = mysqli_fetch_assoc($q1)) {
        echo "<tr>
                <td>{$i}</td>
                <td>{$r['Nivel']}</td>
                <td>{$r['Grado']}</td>
                <td>{$r['SECCION']}</td>
                <td>{$r['anio']}</td>
              </tr>";
        $i++;
    }
    echo '</tbody></table></div>';
}
?>
