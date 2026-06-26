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

        // El sistema original usa password_verify con fallback a texto plano para compatibilidad
        if ($user && (password_verify($password, $user['password_usuario']) || $password == $user['password_usuario'])) {
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
        // Obtenemos la última información de matrícula/inscripción de cada alumno
        $sql = "SELECT
                    a.IDALUMNO, a.NOMBREAPELLIDO, a.CODIGO, a.NOMBREMADRE, a.NOMBREPADRE, a.TELEFONO, a.EMAIL,
                    m.ANIO as ANIO_MATRICULA,
                    i.ANIOLECTIVO as ANIO_INSCRIPCION,
                    g.Grado as NOMBRE_GRADO,
                    n.Nivel as NOMBRE_NIVEL,
                    i.SECCION
                FROM tbl_alumnos a
                LEFT JOIN (
                    SELECT IDALUMNO, MAX(ANIO) as MAX_ANIO
                    FROM tbl_matricula
                    GROUP BY IDALUMNO
                ) m_latest ON a.IDALUMNO = m_latest.IDALUMNO
                LEFT JOIN tbl_matricula m ON m.IDALUMNO = a.IDALUMNO AND m.ANIO = m_latest.MAX_ANIO
                LEFT JOIN tbl_inscripcion i ON i.IDALUMNO = a.IDALUMNO AND i.IDMATRICULA = m.ID
                LEFT JOIN tbl_grados g ON i.IDGRADO = g.IdGrados
                LEFT JOIN tbl_nivel n ON i.IDNIVEL = n.IdNivel
                GROUP BY a.IDALUMNO
                ORDER BY a.IDALUMNO DESC";

        $res = mysqli_query($this->db, $sql);
        $alumnos = [];

        while ($row = mysqli_fetch_assoc($res)) {
            $id = $row['IDALUMNO'];
            $codigo = $row['CODIGO'];

            // Si no tiene código, lo generamos al vuelo pero NO actualizamos la DB aquí para optimizar rendimiento
            if (empty($codigo)) {
                $anio = $row['ANIO_MATRICULA'] ?? date('Y');
                $codigo = "COLE-{$anio}-{$id}";
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
                "id" => (int)$id,
                "carnet" => $codigo,
                "nombre" => mb_convert_case(mb_strtolower($row['NOMBREAPELLIDO']), MB_CASE_TITLE, "UTF-8"),
                "padre" => mb_convert_case(mb_strtolower($row['NOMBREPADRE']), MB_CASE_TITLE, "UTF-8"),
                "madre" => mb_convert_case(mb_strtolower($row['NOMBREMADRE']), MB_CASE_TITLE, "UTF-8"),
                "contacto" => [
                    "telefono" => $row['TELEFONO'],
                    "email" => $row['EMAIL']
                ],
                "academico" => [
                    "anio_lectivo" => $row['ANIO_MATRICULA'] ?? $row['ANIO_INSCRIPCION'] ?? 'N/A',
                    "nivel" => $row['NOMBRE_NIVEL'] ?? 'N/A',
                    "grado" => $row['NOMBRE_GRADO'] ?? 'N/A',
                    "seccion" => $row['SECCION'] ?? 'N/A'
                ],
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

/**
 * ColegioService Class
 */
class ColegioService {
    private $db;

    public function __construct($db_link) {
        $this->db = $db_link;
    }

    public function getInfoColegio() {
        $sql = "SELECT * FROM tbl_parametros LIMIT 1";
        $res = mysqli_query($this->db, $sql);
        return mysqli_fetch_assoc($res);
    }
}

/**
 * NivelGradoService Class
 */
class NivelGradoService {
    private $db;

    public function __construct($db_link) {
        $this->db = $db_link;
    }

    public function getListaNivelesGrados() {
        // Obtener todos los niveles
        $sqlNiveles = "SELECT IdNivel, Nivel, Mensualidad FROM tbl_nivel ORDER BY IdNivel ASC";
        $resNiveles = mysqli_query($this->db, $sqlNiveles);

        $niveles = [];
        while ($nivel = mysqli_fetch_assoc($resNiveles)) {
            $idNivel = $nivel['IdNivel'];

            // Obtener grados para este nivel
            $sqlGrados = "SELECT IdGrados, Grado, total_cupo FROM tbl_grados WHERE IdNivel = ? ORDER BY IdGrados ASC";
            $stmtGrados = mysqli_prepare($this->db, $sqlGrados);
            mysqli_stmt_bind_param($stmtGrados, "i", $idNivel);
            mysqli_stmt_execute($stmtGrados);
            $resGrados = mysqli_stmt_get_result($stmtGrados);

            $grados = [];
            while ($grado = mysqli_fetch_assoc($resGrados)) {
                $grados[] = [
                    "id" => (int)$grado['IdGrados'],
                    "nombre" => $grado['Grado'],
                    "cupo" => (int)$grado['total_cupo']
                ];
            }

            $niveles[] = [
                "id" => (int)$idNivel,
                "nombre" => $nivel['Nivel'],
                "mensualidad_base" => (float)$nivel['Mensualidad'],
                "grados" => $grados
            ];
        }

        return $niveles;
    }
}
?>