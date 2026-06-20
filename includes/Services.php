<?php
/**
 * AuthService Class
 * Maneja la autenticación de usuarios de forma segura
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

        if ($user && password_verify($password, $user['password_usuario'])) {
            return $user;
        }
        return false;
    }
}

/**
 * AlumnoService Class
 * Maneja la lógica de negocio relacionada con alumnos
 */
class AlumnoService {
    private $db;

    public function __construct($db_link) {
        $this->db = $db_link;
    }

    public function getAlumnoById($id) {
        $stmt = mysqli_prepare($this->db, "SELECT * FROM tbl_alumnos WHERE IDALUMNO = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    }

    public function getMoraByAlumno($id_alumno, $anio, $mes_hasta) {
        // Lógica para calcular la mora de un alumno
        // Esta función será el núcleo de la futura API de notas/pagos
    }
}
?>