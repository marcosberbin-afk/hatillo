<?php
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

// Si ya está logueado, mandarlo al index directamente
if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = strtolower($_POST['resbid_correo']);
    $pass = $_POST['resbid_clave'];

    if ($user and $pass) {
        $usuarios_obj = new Usuarios();
        $usuarios = $usuarios_obj->ValidarUsuario($_DB_, $user, $pass);

        if ($usuarios) {
            // --- CORRECCIÓN: Cargar foto actualizada desde la tabla personal ---
            // Esto asegura que si la función ValidarUsuario no trae la foto, la busquemos manualmente.
            if (!empty($usuarios[0]['personal_id'])) {
                try {
                    $stmt_personal = $pdo->prepare("SELECT nombres, apellidos, foto, telefono, fecha_nacimiento, educacion, ubicacion, notas FROM personal WHERE id = ? LIMIT 1");
                    $stmt_personal->execute([$usuarios[0]['personal_id']]);
                    $row_personal = $stmt_personal->fetch(PDO::FETCH_ASSOC);
                    if ($row_personal) {
                        $usuarios[0]['nombres'] = $row_personal['nombres'];
                        $usuarios[0]['apellidos'] = $row_personal['apellidos'];
                        $usuarios[0]['foto'] = $row_personal['foto'];
                        $usuarios[0]['telefono'] = $row_personal['telefono'];
                        $usuarios[0]['fecha_nacimiento'] = $row_personal['fecha_nacimiento'];
                        $usuarios[0]['educacion'] = $row_personal['educacion'];
                        $usuarios[0]['ubicacion'] = $row_personal['ubicacion'];
                        $usuarios[0]['notas'] = $row_personal['notas'];
                    }
                } catch (Exception $e) {
                    // Continuar sin foto si hay error
                }
            }

            $_SESSION['usuario'] = $usuarios[0]; // Almacena el usuario en la variable de sesión
            // $permisos_obj = new Permisos();
            $_SESSION['rol_id'] = $usuarios[0]['rol_id'];
            // $_SESSION['rol_version'] = $rol['version']; // Guardamos que es la versión 5
            // $_SESSION['permisos'] = $permisos_actuales;
            // $_SESSION['permisos'] = $permisos_obj->obtenerPermisosRol($_DB_, $usuarios[0]['rol_id']);
            $_SESSION['permisos'] = []; // Inicializar vacío para evitar errores

            // Lógica de "Recuérdame"
            if(!empty($_POST["remember"])) {
                setcookie ("member_login", $user, time()+ (30 * 24 * 60 * 60)); // Guardar por 30 días
            } else {
                if(isset($_COOKIE["member_login"])) {
                    setcookie ("member_login", "", time() - 3600); // Borrar cookie
                }
            }

            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Usuario o contraseña incorrectos.";
            header("Location: index_login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Usuario o contraseña incorrectos.";
        header("Location: index_login.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Usuario o contraseña incorrectos.";
    header("Location: index_login.php");
    exit();
}
