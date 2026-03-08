<?php
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

$operacion = (isset($_POST['op']) && $_POST['op']) ? substr(strval($_POST['op']), 0, 5) : NULL;
$id = (isset($_POST['id']) && $_POST['id']) ? substr(strval($_POST['id']), 0, 50) : '';

$correo = (isset($_POST['correo']) && $_POST['correo']) ? substr(strval($_POST['correo']), 0, 100) : NULL;
$clave = (isset($_POST['clave']) && $_POST['clave']) ? substr(strval($_POST['clave']), 0, 100) : NULL;
$personal_id = (isset($_POST['personal_id']) && $_POST['personal_id']) ? intval($_POST['personal_id']) : 0;
$rol_id = (isset($_POST['rol_id']) && $_POST['rol_id'] !== '') ? intval($_POST['rol_id']) : NULL;

$activo = (isset($_POST['activo']) && $_POST['activo']) ? intval($_POST['activo']) : 0;

$fecha_sistema = date("Y-m-d H:i:s");

$usuarios_obj = new Usuarios();
$usuarios = $usuarios_obj->Consultar($_DB_, 'hash', $id);

// Validación de Rol Obligatorio
if (($operacion == 'mod' || $operacion == 'add') && empty($rol_id)) {
    echo "<script>alert('Error: Debe seleccionar un Rol válido.'); window.history.back();</script>";
    exit();
}


if ($usuarios and $operacion == 'mod' and trim($correo)) {

	// Seguridad: Admin (Rol 2) no puede modificar a Superadmin (Rol 1) ni asignarse Rol 1
	if ($_SESSION['usuario']['rol_id'] == 2 && ($usuarios[0]['rol_id'] == 1 || $rol_id == 1)) {
		header("Location: ./usuarios_listado.php?error=permisos");
		exit();
	}

	// ----- Modificamos
	$arreglo = NULL;
	$arreglo['correo'] = $correo;

	if($clave)
	{$arreglo['clave'] = md5($clave);}
	
	$arreglo['personal_id'] = $personal_id;
	$arreglo['rol_id'] = $rol_id;

	$condicion = "hash = '$id'";
    
    try {
	    $usuarios_obj->ModificarCondicional($_DB_, $arreglo, $condicion);
    } catch (Exception $e) {
        // Capturar error de llave foránea para mostrar mensaje amigable
        echo "<script>alert('Error al actualizar: El rol seleccionado no existe en la base de datos.'); window.history.back();</script>";
        exit();
    }

} 
if ($usuarios and $operacion == 'del') {

	// Seguridad: Admin (Rol 2) no puede eliminar a Superadmin (Rol 1)
	if ($_SESSION['usuario']['rol_id'] == 2 && $usuarios[0]['rol_id'] == 1) {
		header("Location: ./usuarios_listado.php?error=permisos");
		exit();
	}

	// ----- Modificamos
	$arreglo = NULL;
	$arreglo['activo'] = 0;
	$arreglo['eliminado'] = 1;

	$condicion = "hash = '$id'";
	$usuarios_obj->ModificarCondicional($_DB_, $arreglo, $condicion);

} 

elseif ($operacion == 'add' and trim($correo)) {

	// Seguridad: Solo Superadmin (Rol 1) y Admin (Rol 2) pueden crear usuarios
	if ($_SESSION['usuario']['rol_id'] != 1 && $_SESSION['usuario']['rol_id'] != 2) {
		header("Location: ./usuarios_listado.php");
		exit();
	}

	// Seguridad: Admin (Rol 2) no puede crear Superadmin (Rol 1)
	if ($_SESSION['usuario']['rol_id'] == 2 && $rol_id == 1) {
		header("Location: ./usuarios_listado.php");
		exit();
	}

	// Validar si el correo ya existe (Innovación: Prevención de duplicados)
	$stmt = $pdo->prepare("SELECT count(*) FROM usuarios WHERE correo = ? AND eliminado = 0");
	$stmt->execute([$correo]);
	if ($stmt->fetchColumn() > 0) {
		header("Location: ./usuarios_gestion.php?error=duplicate");
		exit();
	}

	// ----- Agregamos
	$arreglo = NULL;
	$arreglo['correo'] = $correo;

	if($clave)
	{$arreglo['clave'] = md5($clave);}
	
	$arreglo['personal_id'] = $personal_id;
	$arreglo['rol_id'] = $rol_id;
	
	$arreglo['fecha_sistema'] = $fecha_sistema;
	$arreglo['hash'] = md5($correo . $fecha_sistema);

	$arreglo['usuario'] = $_SESSION['usuario']['correo'];

	$arreglo['activo'] = $activo;
	$arreglo['eliminado'] = 0;

	$usuarios_obj->Agregar($_DB_, $arreglo);
}

header("Location: ./usuarios_listado.php");