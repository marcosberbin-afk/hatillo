<?php
include_once(realpath(dirname(__FILE__)) . "/include/header.php");
include_once(realpath(dirname(__FILE__)) . "/include/sessions.php");

if (isset($_POST[TOKEN_NAME])) {
    // Lógica para procesar el cambio de clave
    // ...
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Cambio de Clave</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Actualiza tu contraseña</h3>
                        </div>
                        <form action="cambio_clave_operacion.php" method="post" id="form-change-password">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="current_password">Contraseña Actual</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Ingresa tu contraseña actual" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Ingresa tu nueva contraseña" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirma tu nueva contraseña" required>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include_once(realpath(dirname(__FILE__)) . "/include/footer.php"); ?>
