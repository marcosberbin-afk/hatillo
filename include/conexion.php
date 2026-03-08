<?php
$timeout_duration = 3600;

include_once(realpath(dirname(__FILE__))."/sessions.php");
include_once(realpath(dirname(__FILE__)."/../")."/config/Config_Conexion.php");
include_once(realpath(dirname(__FILE__)."/../")."/config/Permisos.php");

if (!defined('TOKEN_NAME')) {
    define('TOKEN_NAME', 'TOKEN_NAME');
}

//--- Clases
spl_autoload_register(function ($nombre_clase) {
    // Definimos la ruta de la carpeta donde están tus clases
    $directorio = realpath(dirname(__FILE__)). '/../clases/';
    $archivo = $directorio . $nombre_clase . '.php';

    // Si el archivo existe, lo incluimos
    if (file_exists($archivo)) {
        require_once $archivo;
    }
});


//chequear el login
$_DB_= new Sql(DBHOST,DBUSER,DBPASSWORD,DBDATABASE);

// Instancia PDO para operaciones modernas
try {
    $pdo = new PDO("mysql:host=".DBHOST.";dbname=".DBDATABASE.";charset=utf8", DBUSER, DBPASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Manejo silencioso o log de error
}

// Ruta para colocar en el menu
$ruta_index= "";
if(basename($_SERVER['PHP_SELF']) != "index.php")
{$ruta_index = "index.php";}


// Ruta para colocar en el menu
$ruta_index= "";
if(
    (basename($_SERVER['PHP_SELF']) != "index_login.php") AND 
    (basename($_SERVER['PHP_SELF']) != "login.php") AND 
    (basename($_SERVER['PHP_SELF']) != "registrate.php") AND 
    (basename($_SERVER['PHP_SELF']) != "registrate_operacion.php") AND 
    (basename($_SERVER['PHP_SELF']) != "olvido_clave.php") AND 
    (basename($_SERVER['PHP_SELF']) != "olvido_clave_operacion.php")
)
{
    if(!isset($_SESSION['usuario']) || !$_SESSION['usuario'])
    {header("Location: ./index_login.php"); exit();}
    //$ruta_index = "index_login.php";
}


/********************************************************************* 
 * 
 *                         FUNCIONES BASICAS
 * 
 * *******************************************************************/

function ValidarSoloTexto($valor)
{
    $result = (isset($valor)) ? strval(preg_replace('([^A-Za-z0-9. @#_\-áéíóúÁÉÍÓÚñÑüÜ])', '', trim($valor))) : NULL;
    return $result;
}

function ValidarSoloNumero($valor)
{
    $result = (isset($valor)) ? strval(preg_replace('([^0-9])', '', trim($valor))) : NULL;
    return $result;
}

function ValidarTexto($valor)
{
    $result = (isset($valor)) ? strval(preg_replace('([^A-Za-z. @#_\-áéíóúÁÉÍÓÚñÑüÜ])', '', trim($valor))) : NULL;
    return $result;
}

function ValidarNumero($valor)
{
    $result = (isset($valor)) ? strval(preg_replace('([^0-9 .\-_+])', '', trim($valor))) : NULL;
    return $result;
}

function ValidarAlfaNumerico($valor)
{
    $result = (isset($valor)) ? strval(preg_replace('([^A-Za-z0-9.,:; @#_\-áéíóúÁÉÍÓÚñÑüÜ\'\"])', '', trim($valor))) : NULL;
    $result = str_replace('"', '\"', $result);
    $result = str_replace("'", '\"', $result);
    return $result;
}

function ValidarLink($valor)
{
    $result = (isset($valor)) ? strval(preg_replace('([^A-Za-z0-9.:?&= @#_\-\/])', '', trim($valor))) : NULL;
    $result = str_replace('"', '\"', $result);
    $result = str_replace("'", '\"', $result);
    return $result;
}

function tiene_permiso(string $permiso): bool {
    // Si no hay sesión o no hay permisos cargados, denegar
    if (!isset($_SESSION['permisos']) || !is_array($_SESSION['permisos'])) {
        return false;
    }

    // El SuperAdmin suele saltarse todas las restricciones
    if (isset($_SESSION['rol_slug']) && $_SESSION['rol_slug'] === 'superadmin') {
        return true;
    }

    return in_array($permiso, $_SESSION['permisos']);
}