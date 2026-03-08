<?php
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

$correo = $_POST['correo'] ?? '';

// Aquí iría la lógica de envío de correo electrónico.
// Como es un entorno local, simulamos el proceso.

echo "<script>alert('Si el correo existe en nuestra base de datos, recibirá instrucciones para restablecer su contraseña.'); window.location.href='index_login.php';</script>";
?>