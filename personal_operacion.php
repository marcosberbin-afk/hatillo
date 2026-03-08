<?php
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

$operacion = (isset($_POST['op']) && $_POST['op']) ? substr(strval($_POST['op']), 0, 5) : NULL;
$id = (isset($_POST['id']) && $_POST['id']) ? substr(strval($_POST['id']), 0, 50) : '';

$nacionalidad = (isset($_POST['nacionalidad']) && $_POST['nacionalidad']) ? substr(strval($_POST['nacionalidad']), 0, 1) : NULL;
$cedula = (isset($_POST['cedula']) && $_POST['cedula']) ? substr(strval($_POST['cedula']), 0, 20) : NULL;

$nombres = (isset($_POST['nombres']) && $_POST['nombres']) ? substr(strval($_POST['nombres']), 0, 200) : NULL;
$apellidos = (isset($_POST['apellidos']) && $_POST['apellidos']) ? substr(strval($_POST['apellidos']), 0, 200) : NULL;
$telefono = (isset($_POST['telefono']) && $_POST['telefono']) ? substr(strval($_POST['telefono']), 0, 100) : NULL;
$correo = (isset($_POST['correo']) && $_POST['correo']) ? substr(strval($_POST['correo']), 0, 250) : NULL;
$cargo = (isset($_POST['cargo']) && $_POST['cargo']) ? substr(strval($_POST['cargo']), 0, 50) : NULL;
$sexo = (isset($_POST['sexo']) && $_POST['sexo']) ? substr(strval($_POST['sexo']), 0, 1) : NULL;

$fecha_nacimiento = (isset($_POST['fecha_nacimiento']) && $_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : '0000-00-00';

$activo = (isset($_POST['activo']) && $_POST['activo']) ? intval($_POST['activo']) : 0;

$fecha_sistema = date("Y-m-d H:i:s");
$foto_nombre = NULL;

// --- Manejo de subida de foto ---
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $target_dir = "dist/img/personal/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $foto_nombre = uniqid('perfil_') . '.' . $ext;
    $target_file = $target_dir . $foto_nombre;

    // Validar que es una imagen
    $check = getimagesize($_FILES['foto']['tmp_name']);
    if($check !== false) {
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
            $foto_nombre = NULL; // Falló la subida
        }
    } else {
        $foto_nombre = NULL; // No es una imagen
    }
}

$personales_obj = new Personal();
$personales = $personales_obj->Consultar($_DB_, 'hash', $id);

if ($personales and $operacion == 'mod') {

	// ----- Modificamos
	$arreglo = NULL;
	$arreglo['nacionalidad'] = $nacionalidad;
	$arreglo['cedula'] = $cedula;
	$arreglo['nombres'] = $nombres;
	$arreglo['apellidos'] = $apellidos;
	$arreglo['telefono'] = $telefono;
	$arreglo['correo'] = $correo;
	$arreglo['cargo'] = $cargo;
	$arreglo['sexo'] = $sexo;
	$arreglo['fecha_nacimiento'] = $fecha_nacimiento;
	$arreglo['activo'] = $activo;

	if ($foto_nombre) {
        $arreglo['foto'] = $foto_nombre;
    }

	$condicion = "hash = '$id'";
	$personales_obj->ModificarCondicional($_DB_, $arreglo, $condicion);

} 
if ($personales and $operacion == 'del') {

	// ----- Modificamos
	$arreglo = NULL;
	$arreglo['activo'] = 0;
	$arreglo['eliminado'] = 1;

	$condicion = "hash = '$id'";
	$personales_obj->ModificarCondicional($_DB_, $arreglo, $condicion);

} 

elseif ($operacion == 'add') {

	// Validar si la cédula ya existe (Innovación: Prevención de duplicados)
	$stmt = $pdo->prepare("SELECT count(*) FROM personal WHERE cedula = ? AND eliminado = 0");
	$stmt->execute([$cedula]);
	if ($stmt->fetchColumn() > 0) {
		header("Location: ./personal_gestion.php?error=duplicate");
		exit();
	}

	// ----- Agregamos
	$arreglo = NULL;
	$arreglo['nacionalidad'] = $nacionalidad;
	$arreglo['cedula'] = $cedula;
	$arreglo['nombres'] = $nombres;
	$arreglo['apellidos'] = $apellidos;
	$arreglo['telefono'] = $telefono;
	$arreglo['correo'] = $correo;
	$arreglo['cargo'] = $cargo;
	$arreglo['sexo'] = $sexo;
	$arreglo['fecha_nacimiento'] = $fecha_nacimiento;

	if ($foto_nombre) {
        $arreglo['foto'] = $foto_nombre;
    }

	$arreglo['fecha_sistema'] = $fecha_sistema;
	$arreglo['hash'] = md5($nombres . $fecha_sistema);

	// $arreglo['usuario'] = $_SESSION['usuario']['correo'];
	$arreglo['activo'] = $activo;
	$arreglo['eliminado'] = 0;

	$personales_obj->Agregar($_DB_, $arreglo);
}

header("Location: ./personal_listado.php");