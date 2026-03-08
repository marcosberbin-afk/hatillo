<?php
session_start();
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");
include_once(realpath(dirname(__FILE__)) . "/clases/Usuarios.php");

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: index_login.php");
    exit();
}

$usuarios_obj = new Usuarios();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 1. Validar que la nueva contraseña y su confirmación coincidan
    if ($new_password !== $confirm_password) {
        echo "<script>alert('La nueva contraseña y su confirmación no coinciden.'); window.history.back();</script>";
        exit();
    }
    
    if (empty($new_password)) {
        echo "<script>alert('La nueva contraseña no puede estar vacía.'); window.history.back();</script>";
        exit();
    }

    // 2. Validar la contraseña actual
    $correo = $_SESSION['usuario']['correo'];
    $usuario_valido = $usuarios_obj->ValidarUsuario($_DB_, $correo, $current_password);

    if ($usuario_valido) {
        // 3. Si la contraseña actual es correcta, actualizar a la nueva
        $user_id = $_SESSION['usuario']['id'];
        
        // Usamos una función existente que actualiza por correo
        $usuarios_obj->ActualizarClaveCliente($_DB_, $correo, $new_password);
        
        echo "<script>alert('Contraseña actualizada con éxito.'); window.location.href='index.php';</script>";
        exit();

    } else {
        echo "<script>alert('La contraseña actual es incorrecta.'); window.history.back();</script>";
        exit();
    }
} else {
    header("Location: cambio_clave.php");
    exit();
}
?>