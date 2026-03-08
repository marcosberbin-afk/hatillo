<?php
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");
include_once(realpath(dirname(__FILE__)) . "/include/header.php");
?>

<div class="login-box">
  <div class="login-logo">
    <a href="index.php"><b><?php echo NOMBRE_SITE; ?></b></a>
  </div>
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">¿Olvidaste tu contraseña? Aquí puedes recuperar una nueva.</p>

      <form action="olvido_clave_operacion.php" method="post">
        <div class="input-group mb-3">
          <input type="email" class="form-control" name="correo" placeholder="Email" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Solicitar nueva contraseña</button>
          </div>
        </div>
      </form>

      <p class="mt-3 mb-1">
        <a href="index_login.php">Iniciar Sesión</a>
      </p>
      <p class="mb-0">
        <a href="registrate.php" class="text-center">Registrar un nuevo miembro</a>
      </p>
    </div>
  </div>
</div>

<?php
include_once(realpath(dirname(__FILE__)) . "/include/footer.php");
?>