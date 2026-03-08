<?php
include_once(realpath(dirname(__FILE__)) . "/conexion.php");
?>
<!DOCTYPE html>
<html>

<head>

	<?
	/*
	if (GOOGLE_ANALITICS) { ?>

		<!-- Google tag (gtag.js) -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=<? echo GOOGLE_ANALITICS; ?>"></script>
		<script>
			window.dataLayer = window.dataLayer || [];

			function gtag() {
				dataLayer.push(arguments);
			}
			gtag('js', new Date());

			gtag('config', '<? echo GOOGLE_ANALITICS; ?>');
		</script>
	<?
	}*/ ?>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>
		<? echo NOMBRE_SITE; ?>
	</title>
	<!-- Tell the browser to be responsive to screen width -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Google Capchat -->
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<!-- Font Awesome -->
	<link rel="stylesheet" href="./plugins/fontawesome-free/css/all.min.css">
	<!-- Ionicons -->
	<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
	<!-- Tempusdominus Bbootstrap 4 -->
	<link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
	<!-- iCheck -->
	<link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
	<!-- JQVMap -->
	<link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="dist/css/adminlte.css">
	<!-- overlayScrollbars -->
	<link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
	<!-- Daterange picker -->
	<link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
	<!-- summernote -->
	<link rel="stylesheet" href="plugins/summernote/summernote-bs4.css">
	<!-- Google Font: Quicksand -->
	<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<style>
		body, h1, h2, h3, h4, h5, h6, .btn, .form-control, .main-sidebar, .brand-link {
			font-family: 'Quicksand', sans-serif !important;
		}
        /* --- MEJORAS VISUALES Y RESPONSIVE (INNOVACIÓN) --- */
        .card {
            border-radius: 12px; /* Bordes más redondeados y modernos */
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); /* Sombra suave y elegante */
            border: none; /* Sin bordes duros */
            transition: transform 0.2s;
        }
        
        .btn {
            border-radius: 8px; /* Botones más amigables */
            font-weight: 500;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .form-control {
            border-radius: 8px;
            background-color: #fdfdfd;
            height: calc(2.25rem + 4px); /* Un poco más altos para dedos en móvil */
        }
        .form-control:focus {
            background-color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.15); /* Focus más suave */
            border-color: #80bdff;
        }

        /* Ajustes Específicos para Móviles */
        @media (max-width: 768px) {
            .content-header h1 {
                font-size: 1.5rem;
                text-align: center;
                margin-bottom: 10px;
            }
            .content-header img {
                display: inline-block; /* Asegurar que el logo se vea bien */
            }
            
            /* Separación automática entre columnas apiladas */
            .col-md-3, .col-md-4, .col-md-6, .col-sm-6 {
                margin-bottom: 15px;
            }
            
            /* Navegación más limpia */
            .main-header {
                border-bottom: none;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            /* Ajuste para que los inputs en tablas no sean diminutos */
            .table input.form-control {
                min-width: 80px; 
            }
        }
	</style>
</head>
<?
if (
	empty($_SESSION['usuario']) or
	(basename($_SERVER['PHP_SELF']) == "index_login.php") or
	(basename($_SERVER['PHP_SELF']) == "registrate.php") or
	(basename($_SERVER['PHP_SELF']) == "registrate_mensaje.php") or
	(basename($_SERVER['PHP_SELF']) == "olvido_clave.php") or
	(basename($_SERVER['PHP_SELF']) == "olvido_clave_mensaje.php")
) {
	?>

	<body class="hold-transition register-page">
	<?
} else {
	?>

		<body class="hold-transition sidebar-mini layout-fixed">
			<div class="wrapper">

				<!-- Navbar -->
				<nav class="main-header navbar navbar-expand navbar-<? echo $color; ?> navbar-dark">
					<!-- Left navbar links -->
					<ul class="navbar-nav">
						<li class="nav-item">
							<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
									class="fas fa-bars"></i></a>
						</li>
						<li class="nav-item d-none d-sm-inline-block">
							<a href="index.php" class="nav-link">
								<i class="fas fa-home mr-1"></i> Inicio
							</a>
						</li>
						<li class="nav-item d-none d-sm-inline-block">
							<a href="logout.php" class="nav-link">
								<i class="fas fa-sign-out-alt mr-1"></i> Salir
							</a>
						</li>
					</ul>

					<!-- Right navbar links -->
					<ul class="navbar-nav ml-auto">

						<!-- Notifications Dropdown Menu -->
						<li class="nav-item dropdown">
							<a class="nav-link" data-toggle="dropdown" href="#">
								<i class="fa fa-user"></i>
							</a>

							<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
								<div class="dropdown-divider"></div>
								<?/*
								  <a href="#" class="dropdown-item">
									  <i class="fas fa-envelope mr-2"></i> 4 new messages
									  <span class="float-right text-muted text-sm">3 mins</span>
								  </a>
								  <div class="dropdown-divider"></div>
								  <a href="#" class="dropdown-item">
									  <i class="fas fa-users mr-2"></i> 8 friend requests
									  <span class="float-right text-muted text-sm">12 hours</span>
								  </a>
								  */ ?>
								<div class="dropdown-divider"></div>
								<?php
								$link_perfil = '#';
								if (!empty($_SESSION['usuario']['personal_hash'])) {
									$link_perfil = 'personal_gestion.php?o=mod&id=' . $_SESSION['usuario']['personal_hash'];
								}
								?>
								<a href="perfil.php" class="dropdown-item">
									<i class="fas fa-user mr-2"></i> Mi perfil
								</a>

								<a href="cambio_clave.php" class="dropdown-item">
									<i class="fas fa-unlock-alt mr-2"></i> Cambio de Clave
								</a>

								<a href="logout.php" class="dropdown-item">
									<i class="fas fa-sign-out-alt mr-2"></i> Salir
								</a>
								<div class="dropdown-divider"></div>
							</div>
						</li>
						<?/*
					  <li class="nav-item">
					  <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
						  <i class="fas fa-th-large"></i>
					  </a>
					  </li>
					  */ ?>
					</ul>


				</nav>
				<!-- /.navbar -->


				<!-- Main Sidebar Container -->
				<aside class="main-sidebar sidebar-dark-<? echo $color; ?> elevation-4">
					<!-- Brand Logo -->
					<a href="index.php" class="brand-link">
						<img src="dist/img/logo_nuevo.png" alt="" class="brand-image" style="opacity: 1; max-height: 45px; margin-top: -8px;">
						<span class="brand-text font-weight"><b><? echo NOMBRE_SITE; ?></b></span>
					</a>

					<div class="sidebar">

						<div class="user-panel mt-3 pb-3 mb-3 d-flex">
							<div class="image">
								<?php
								$foto_perfil = 'dist/img/silueta.png';
								if (!empty($_SESSION['usuario']['foto'])) {
									$foto_perfil = 'dist/img/personal/' . $_SESSION['usuario']['foto'];
								}
								?>
								<a href="<?php echo $link_perfil; ?>">
									<img src="<?php echo $foto_perfil; ?>" class="img-circle elevation-2" alt="User Image" style="width: 34px; height: 34px; object-fit: cover;">
								</a>
							</div>
							<div class="info">
								<a href="index.php" class="d-block">
									<?php 
									$nombre_mostrar = "Usuario";
									if (!empty($_SESSION['usuario']['nombres']) || !empty($_SESSION['usuario']['apellidos'])) {
										$nombre_mostrar = ($_SESSION['usuario']['nombres'] ?? '') . " " . ($_SESSION['usuario']['apellidos'] ?? '');
									} elseif (!empty($_SESSION['usuario']['correo'])) {
										$nombre_mostrar = $_SESSION['usuario']['correo'];
									}
									echo ucwords(strtolower($nombre_mostrar)); 
									?>
								</a>
							</div>
						</div>

						<?
						if (($_SESSION['usuario']['rol_id'] == 1) or ($_SESSION['usuario']['rol_id'] == 2)) {
							include_once(realpath(dirname(__FILE__)) . "/menu_izq_1.php");
						} elseif (in_array($_SESSION['usuario']['rol_id'], [3, 5, 6])) {
							include_once(realpath(dirname(__FILE__)) . "/menu_izq_3.php");
						} ?>

					</div>

				</aside>

			<?
} ?>