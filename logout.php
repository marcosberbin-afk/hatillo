<?php
include_once(realpath(dirname(__FILE__))."/include/sessions.php");

$_SESSION['usuario'] = NULL;
session_destroy();

header("Location: ./index_login.php");
?>