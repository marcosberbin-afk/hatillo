<?php
session_start();
if (!isset($_SESSION['usuario'])) exit;

// Necesitas haber descargado la librería Dompdf
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} elseif (file_exists('dompdf/autoload.inc.php')) {
    require_once 'dompdf/autoload.inc.php';
} else {
    die('Error: No se encontró la librería Dompdf. Por favor ejecute "composer require dompdf/dompdf" en la carpeta del proyecto o descargue la librería manualmente.');
}
use Dompdf\Dompdf;
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

$id = $_GET['id'];

// Capturamos el contenido de detalle.php pero en una variable
$pdf_mode = true; // Bandera para ocultar botones en el PDF
ob_start(); 
include 'detalle.php'; // Reutilizamos el diseño que ya hicimos
$html = ob_get_clean();

// Inicializamos Dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);

// (Opcional) Configurar tamaño de papel
$dompdf->setPaper('A4', 'portrait');

// Renderizar el HTML como PDF
$dompdf->render();

// Enviar al navegador
$dompdf->stream("Reporte_PC_Servicio_" . $id . ".pdf", array("Attachment" => false));
?>