<?php
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");
include_once(realpath(dirname(__FILE__)) . "/include/header.php");
?>

<body class="hold-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <img src="./dist/img/logo.png" class="img-fluid" alt="">
    </div>
    <!-- /.login-logo -->
    <div class="card">
      <div class="card-body login-card-body">
        <p class="login-box-msg">Sistema Automatizado</p>

        <form action="login.php" method="post" onsubmit="return miFuncion(this)">
          <div class="input-group mb-3">
            <input name="resbid_correo" type="text" class="form-control" placeholder="Usuario" value="<?php if(isset($_COOKIE["member_login"])) { echo $_COOKIE["member_login"]; } ?>" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fa fa-user"></span>
              </div>
            </div>
          </div>
          <div class="input-group mb-3">
            <input name="resbid_clave" id="resbid_clave" type="password" class="form-control" placeholder="Contraseña" required>
            <div class="input-group-append">
              <div class="input-group-text" style="cursor: pointer;" onclick="togglePassword()">
                <span id="toggleIcon" class="fas fa-eye"></span>
              </div>
            </div>
          </div>

          <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-3 text-center">
              <span class="text-danger font-weight-bold"><i class="fas fa-times-circle"></i> <?php echo $_SESSION['error']; ?></span>
            </div>
            <?php $_SESSION['error'] = null; ?>
          <?php endif; ?>

          <?
          if ($captcha_activo) { ?>
            <div class="input-group mb-3">
              <div class="g-recaptcha" data-sitekey="<? echo CAPTCHA_PUBLICA; ?>"></div>
            </div>
          <?
          } ?>

          <div class="row">
            <div class="col-8">
              <div class="icheck-primary">
                <input type="checkbox" id="remember" name="remember" <?php if(isset($_COOKIE["member_login"])) { ?> checked <?php } ?>>
                <label for="remember">
                  Recuérdame
                </label>
              </div>
            </div>
            <!-- /.col -->
            <div class="col-4">
              <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
            </div>
            <!-- /.col -->
          </div>
        </form>

      <p class="mb-1">
        <a href="./olvido_clave.php">Olvido su Contraseña</a>
      </p>
      <p class="mb-0">
        <a href="./registrate.php" class="text-center">Regístrate</a>
      </p>
      <?/*
      <hr>

      <p class="mb-0 text-center">
        <a href="https://www.youtube.com/watch?v=Zh5DllG3KR8" target="_blank" class="text-center">Video Tutorial</a>
      </p>
      */ ?>

      </div>
      <!-- /.login-card-body -->
    </div>
  </div>
  <!-- /.login-box -->

  <script>
    function togglePassword() {
      var input = document.getElementById("resbid_clave");
      var icon = document.getElementById("toggleIcon");
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      }
    }

    function miFuncion(a) {

      var response = grecaptcha.getResponse();

      if (response.length == 0) {
        alert("Captcha no verificado");
        return false;
        event.preventDefault();
      } else {
        return true;
      }

    }
  </script>

  <?php
  include_once(realpath(dirname(__FILE__)) . "/include/footer.php");

/*
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Protección Civil El Hatillo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border-top: 5px solid #dc3545; /* Rojo PC * /
            border-radius: 10px;
        }
        .logo-pc {
            width: 80px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="card login-card shadow-lg">
    <div class="card-body p-5">
        <div class="text-center">
            <h4 class="fw-bold">Protección Civil</h4>
            <p class="text-muted">El Hatillo</p>
        </div>
        
        <h5 class="text-center mb-4">Iniciar Sesión</h5>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 text-center" style="font-size: 0.9rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" name="user" class="form-control" placeholder="Ej: admin_pc" required autocomplete="off">
            </div>
            <div class="mb-4">
                <label class="form-label">Contraseña</label>
                <input type="password" name="pass" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-danger w-100 p-2 shadow-sm">Ingresar al Sistema</button>
        </form>
        
        <div class="text-center mt-4">
            <small class="text-muted">Sistema de Registro de Novedades v1.0</small>
        </div>
    </div>
</div>

</body>
</html>
*/?>