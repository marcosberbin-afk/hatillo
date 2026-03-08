<?php
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

$operacion = (isset($_POST['op']) && $_POST['op']) ? substr(strval($_POST['op']), 0, 5) : NULL;
$id = (isset($_POST['id']) && $_POST['id']) ? substr(strval($_POST['id']), 0, 50) : '';

$codigo = (isset($_POST['codigo']) && $_POST['codigo']) ? substr(strval($_POST['codigo']), 0, 300) : NULL;
$descripcion = (isset($_POST['descripcion']) && $_POST['descripcion']) ? substr(strval($_POST['descripcion']), 0, 300) : NULL;
$activo = (isset($_POST['activo']) && $_POST['activo']) ? intval($_POST['activo']) : 0;

$fecha_sistema = date("Y-m-d H:i:s");

$unidades_obj = new Unidades();
$unidades = $unidades_obj->Consultar($_DB_, 'hash', $id);

if ($unidades and $operacion == 'mod') {

	// ----- Modificamos
	$arreglo = NULL;
	$arreglo['codigo'] = $codigo;
	$arreglo['descripcion'] = $descripcion;

	
	$condicion = "hash = '$id'";
	$unidades_obj->ModificarCondicional($_DB_, $arreglo, $condicion);

} 
if ($unidades and $operacion == 'del') {

	// ----- Modificamos
	$arreglo = NULL;
	$arreglo['activo'] = 0;
	$arreglo['eliminado'] = 1;

	$condicion = "hash = '$id'";
	$unidades_obj->ModificarCondicional($_DB_, $arreglo, $condicion);

} 

elseif ($operacion == 'add') {

	// ----- Agregamos
	$arreglo = NULL;
	$arreglo['codigo'] = $codigo;
	$arreglo['descripcion'] = $descripcion;
	$arreglo['fecha_sistema'] = $fecha_sistema;
	$arreglo['hash'] = md5($nombre . $fecha_sistema);

	$arreglo['usuario'] = $_SESSION['usuario']['correo'];
	$arreglo['activo'] = 1;
	$arreglo['eliminado'] = 0;

	$unidades_obj->Agregar($_DB_, $arreglo);
}

header("Location: ./unidades_listado.php");