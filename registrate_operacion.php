<?php
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

$nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : '';
$apellidos = (isset($_POST['apellidos'])) ? $_POST['apellidos'] : '';
$correo = (isset($_POST['correo'])) ? $_POST['correo'] : '';
$clave = (isset($_POST['clave'])) ? $_POST['clave'] : '';
$clave2 = (isset($_POST['clave2'])) ? $_POST['clave2'] : '';
$usuario = (isset($_POST['usuario'])) ? $_POST['usuario'] : '';

if ($clave != $clave2) {
    echo "<script>alert('Las contraseñas no coinciden'); window.history.back();</script>";
    exit();
}

// Validar si el correo ya existe (Innovación: Prevención de duplicados)
$stmt = $pdo->prepare("SELECT count(*) FROM usuarios WHERE correo = ? AND eliminado = 0");
$stmt->execute([$correo]);
if ($stmt->fetchColumn() > 0) {
    header("Location: ./registrate.php?error=duplicate");
    exit();
}

// 1. Crear Personal (Para guardar nombres y apellidos)
$personal_obj = new Personal();
// Generar cédula temporal para evitar error de duplicado (Unique Key)
$cedula_temp = 'TMP' . mt_rand(100000, 999999);
$arr_personal = [
    'nombres' => $nombres,
    'apellidos' => $apellidos,
    'cedula' => $cedula_temp,
    'correo' => $correo,
    'activo' => 1,
    'eliminado' => 0,
    'fecha_sistema' => date("Y-m-d H:i:s"),
    'hash' => md5($nombres . date("Y-m-d H:i:s"))
];
$personal_id = $personal_obj->Agregar($_DB_, $arr_personal);

// 2. Crear Usuario (Login)
$usuarios_obj = new Usuarios();
$arr_usuario = [
    'usuario' => $usuario,
    'correo' => $correo,
    'clave' => md5($clave),
    'personal_id' => $personal_id,
    'rol_id' => 3, // Asignamos rol 3 (Usuario/Operador) por defecto
    'activo' => 1,
    'eliminado' => 0,
    'fecha_sistema' => date("Y-m-d H:i:s"),
    'hash' => md5($correo . date("Y-m-d H:i:s"))
];
$usuarios_obj->Agregar($_DB_, $arr_usuario);

echo "<script>alert('Registro exitoso. Puede iniciar sesión.'); window.location.href='index_login.php';</script>";
?>