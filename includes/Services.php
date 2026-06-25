<?php
/**
 * AuthService Class
 */
class AuthService {
    private $db;

    public function __construct($db_link) {
        $this->db = $db_link;
    }

    public function login($email, $password) {
        $stmt = mysqli_prepare($this->db, "SELECT * FROM tbla_usuario WHERE email_usuario = ? AND activo_usuario = 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        // El sistema original usa password_verify para mayor seguridad
        if ($user && password_verify($password, $user['password_usuario'])) {
            return $user;
        }
        return false;
    }
}

/**
 * AlumnoService Class
 */
class AlumnoService {
    private $db;

    public function __construct($db_link) {
        $this->db = $db_link;
    }

    public function getListaCompletaAlumnos() {
        // Obtenemos todos los alumnos y sus estados de matrícula e inscripción de forma cruzada
        $sql = "SELECT
                    a.IDALUMNO, a.NOMBREAPELLIDO, a.CODIGO, a.NOMBREMADRE, a.NOMBREPADRE, a.TELEFONO, a.EMAIL,
                    m.ANIO as ANIO_MATRICULA,
                    i.ANIOLECTIVO as ANIO_INSCRIPCION
                FROM tbl_alumnos a
                LEFT JOIN tbl_matricula m ON a.IDALUMNO = m.IDALUMNO
                LEFT JOIN tbl_inscripcion i ON a.IDALUMNO = i.IDALUMNO AND i.IDMATRICULA = m.ID
                ORDER BY a.IDALUMNO DESC, m.ANIO DESC";

        $res = mysqli_query($this->db, $sql);
        $alumnos = [];

        // Optimizamos la actualización de códigos: los recolectamos y actualizamos fuera del loop principal si es posible
        // O usamos una consulta directa para evitar múltiples prepared statements

        while ($row = mysqli_fetch_assoc($res)) {
            $id = $row['IDALUMNO'];

            // Generar Código de Carnet si no tiene
            $codigo = $row['CODIGO'];
            if (empty($codigo)) {
                $anio = $row['ANIO_MATRICULA'] ?? date('Y');
                $codigo = "COLE-{$anio}-{$id}";
                // Actualización directa (aunque no ideal en un GET, es un requerimiento de persistencia del usuario)
                mysqli_query($this->db, "UPDATE tbl_alumnos SET CODIGO = '$codigo' WHERE IDALUMNO = $id");
            }

            // Determinar estado
            $estado = "Registrado";
            if (!empty($row['ANIO_MATRICULA']) && !empty($row['ANIO_INSCRIPCION'])) {
                $estado = "Matriculado e Inscrito";
            } else if (!empty($row['ANIO_MATRICULA'])) {
                $estado = "Solo Matriculado";
            } else if (!empty($row['ANIO_INSCRIPCION'])) {
                $estado = "Solo Inscrito";
            }

            $alumnos[] = [
                "id" => $id,
                "carnet" => $codigo,
                "nombre" => mb_convert_case(mb_strtolower($row['NOMBREAPELLIDO']), MB_CASE_TITLE, "UTF-8"),
                "padre" => $row['NOMBREPADRE'],
                "madre" => $row['NOMBREMADRE'],
                "contacto" => [
                    "telefono" => $row['TELEFONO'],
                    "email" => $row['EMAIL']
                ],
                "anio_lectivo" => $row['ANIO_MATRICULA'] ?? $row['ANIO_INSCRIPCION'] ?? 'N/A',
                "estado" => $estado
            ];
        }
        return $alumnos;
    }
}

/**
 * PaymentService Class
 */
class PaymentService {
    private $db;
    private $meses_map = [
        1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
        7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
    ];

    public function __construct($db_link) {
        $this->db = $db_link;
    }

    public function getAlumnosEnMora($anio, $mes_hasta) {
        $sql = "SELECT b.ID AS IdInscripcion, c.NOMBREAPELLIDO, c.IDALUMNO, pm.*
                FROM tbl_matricula AS a
                INNER JOIN tbl_inscripcion AS b ON a.ID = b.IDMATRICULA
                INNER JOIN tbl_alumnos AS c ON a.IDALUMNO = c.IDALUMNO
                INNER JOIN tbl_pagosmensualidades AS pm ON pm.IdInscripcion = b.ID
                WHERE a.ANIO = ?";

        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "s", $anio);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $alumnos_mora = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $total_mes_mora = 0;
            $meses_mora = [];

            for ($i = 1; $i <= $mes_hasta; $i++) {
                $mes_abbr = $this->meses_map[$i];
                if (($row[$mes_abbr] ?? '') != 'X') {
                    $total_mes_mora++;
                    $meses_mora[] = $mes_abbr;
                }
            }

            if ($total_mes_mora > 0) {
                $alumnos_mora[] = [
                    'id_alumno' => $row['IDALUMNO'],
                    'id_inscripcion' => $row['IdInscripcion'],
                    'nombre' => mb_convert_case(mb_strtolower($row['NOMBREAPELLIDO']), MB_CASE_TITLE, "UTF-8"),
                    'total_mes' => $total_mes_mora,
                    'meses' => $meses_mora
                ];
            }
        }
        return $alumnos_mora;
    }

    public function getTotalIngresosDia($fecha) {
        $stmt = mysqli_prepare($this->db, "SELECT SUM(MontoTotal) as total FROM tbl_recibomaestro WHERE DATE(Fecha) = ? AND Estado = 1");
        mysqli_stmt_bind_param($stmt, "s", $fecha);
        mysqli_stmt_execute($stmt);
        $res = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        return $res['total'] ?? 0;
    }
}
?>