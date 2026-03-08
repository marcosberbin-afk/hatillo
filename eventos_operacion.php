<?php
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

$operacion = (isset($_REQUEST['op']) && $_REQUEST['op']) ? substr(strval($_REQUEST['op']), 0, 5) : NULL;
$id = (isset($_REQUEST['id']) && $_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

$nro_servicio = (isset($_POST['nro_servicio'])) ? $_POST['nro_servicio'] : NULL;
$fecha_registro = (isset($_POST['fecha_registro'])) ? $_POST['fecha_registro'] : NULL;
$parroquia = (isset($_POST['parroquia'])) ? $_POST['parroquia'] : NULL;
$cuadrante = (isset($_POST['cuadrante'])) ? $_POST['cuadrante'] : NULL;
$direccion_detallada = (isset($_POST['direccion_detallada'])) ? $_POST['direccion_detallada'] : NULL;
$resumen_novedad = (isset($_POST['resumen_novedad'])) ? $_POST['resumen_novedad'] : NULL;
$tipo_actividad_id = (isset($_POST['tipo_actividad_id'])) ? intval($_POST['tipo_actividad_id']) : NULL;

$servicios_obj = new Servicios();
$servicio = $servicios_obj->Consultar($_DB_, 'id', $id);

if ($servicio and $operacion == 'mod') {

	// ----- Modificamos
	$arreglo = NULL;
	$arreglo['nro_servicio'] = $nro_servicio;
	$arreglo['fecha_registro'] = $fecha_registro;
	$arreglo['parroquia'] = $parroquia;
	$arreglo['cuadrante'] = $cuadrante;
	$arreglo['direccion_detallada'] = $direccion_detallada;
	$arreglo['resumen_novedad'] = $resumen_novedad;
	$arreglo['tipo_actividad_id'] = $tipo_actividad_id;

	$condicion = "id = '$id'";
	$servicios_obj->ModificarCondicional($_DB_, $arreglo, $condicion);

} 
if ($servicio and $operacion == 'del') {
	// ----- Eliminamos (Lógicamente)
	$arreglo = ['eliminado' => 1];
	$condicion = "id = '$id'";
	$servicios_obj->ModificarCondicional($_DB_, $arreglo, $condicion);
} 

elseif ($operacion == 'add') {
	// ----- Agregamos
	$arreglo = NULL;
	$arreglo['nro_servicio'] = $nro_servicio;
	$arreglo['fecha_registro'] = $fecha_registro;
	$arreglo['parroquia'] = $parroquia;
	$arreglo['cuadrante'] = $cuadrante;
	$arreglo['direccion_detallada'] = $direccion_detallada;
	$arreglo['resumen_novedad'] = $resumen_novedad;
	$arreglo['tipo_actividad_id'] = $tipo_actividad_id;
	$arreglo['estatus_id'] = 1; // Default activo/pendiente

	$servicios_obj->Agregar($_DB_, $arreglo);
}

header("Location: ./eventos_listado.php");
?>