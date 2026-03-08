<?php
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");
include_once(realpath(dirname(__FILE__)) . "/include/header.php");
?>

<div class="register-box">
  <div class="register-logo">
    <a href="index.php"><b><?php echo NOMBRE_SITE; ?></b></a>
  </div>

  <div class="card">
    <div class="card-body register-card-body">
      <p class="login-box-msg">Registrar un nuevo miembro</p>

      <form action="registrate_operacion.php" method="post">
        <div class="input-group mb-3">
          <input type="text" class="form-control" name="nombres" placeholder="Nombres" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
            <input type="text" class="form-control" name="apellidos" placeholder="Apellidos" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-user"></span>
              </div>
            </div>
          </div>
        <div class="input-group mb-3">
          <input type="text" class="form-control" name="usuario" placeholder="Usuario" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user-circle"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="email" class="form-control" name="correo" placeholder="Email" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" name="clave" placeholder="Contraseña" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" name="clave2" placeholder="Repetir contraseña" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row align-items-center">
          <div class="col-7">
            <div class="icheck-primary">
              <input type="checkbox" id="agreeTerms" name="terms" value="agree" required>
              <label for="agreeTerms">
               Acepto los <a href="#">términos</a>
              </label>
            </div>
          </div>
          <div class="col-5">
            <button type="submit" class="btn btn-primary btn-block">Registrar</button>
          </div>
        </div>
      </form>

      <a href="index_login.php" class="text-center">Ya tengo una cuenta</a>
    </div>
  </div>
</div>

<?php
include_once(realpath(dirname(__FILE__)) . "/include/footer.php");
?>

<!-- SweetAlert2: Alertas de Evolución Tecnológica -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('error') === 'duplicate') {
            Swal.fire({
                icon: 'error',
                title: '¡Usuario Duplicado!',
                html: 'El correo electrónico ingresado <b>ya se encuentra registrado</b> en el sistema.',
                footer: '<span style="color:#6c757d">Por favor inicie sesión o recupere su clave</span>',
                confirmButtonColor: '#d33',
                confirmButtonText: '<i class="fas fa-thumbs-up"></i> Entendido',
                backdrop: `rgba(0,0,123,0.1)`
            });
            // Limpiar la URL para que al recargar no salga de nuevo
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
</script>