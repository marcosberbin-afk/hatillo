<?php
date_default_timezone_set('America/Caracas');
//------ VARIABLES -------
define('WEB', 'http://127.0.0.1/hatillo/');

define('DIR_MEDIA_REAL', realpath(dirname(__FILE__) . "/../"));
define('DIR_MEDIA_WEB', '');

define('MAILING_ADDRESS', 'info@hatillo.com');

define('DIR_BANNERS', './upload/banners/');
define('DIR_TESTIMONIOS', './upload/testimonios/');
define('DIR_ALIADOS', './upload/aliados/');
define('DIR_INSTALACIONES', './upload/instalaciones/');
define('DIR_NOTICIAS', './upload/noticias/');
define('DIR_JOB_TITLE', './upload/personal_interno/');

define('DIR_DOCUMENTOS', './Documentos/');

define('NOMBRE_SITE', 'PC El Hatillo');
define('COPYRIGHT_SITE', 'PC El Hatillo');
define('NOMBRE_SITE_LARGO', 'PC El Hatillo');

/*----- Envio de Correos ------*/
define('CORREO', 'info@hatillo.com');
define('NOMBRE_WEB', 'hatillo.com');


/*----- Captcha Google ------*/
$captcha_activo = 0;

define('CAPTCHA_PUBLICA', '');
define('CAPTCHA_PRIVADA', '');


/*----- Google Analitys ------*/
define('GOOGLE_ANALITICS', '');

/*----- Color ------*/
$color = 'primary';