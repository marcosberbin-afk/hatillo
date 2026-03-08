<?php
include_once(realpath(dirname(__FILE__)) . "/include/header.php");
include_once(realpath(dirname(__FILE__)) . "/include/sessions.php");

// Redirigir si no está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: index_login.php");
    exit();
}

$usuario = $_SESSION['usuario'];

// Lógica para obtener el rol del usuario
$rol = 'Usuario'; // Rol por defecto
if (isset($usuario['rol_id'])) {
    // Podrías tener una consulta a la base de datos para obtener el nombre del rol
    // Por ahora, usaremos una lógica simple
    switch ($usuario['rol_id']) {
        case 1:
            $rol = 'Administrador';
            break;
        case 2:
            $rol = 'Operador';
            break;
        case 3:
            $rol = 'Cliente';
            break;
        // Agrega más casos según sea necesario
    }
}

$foto_perfil = 'dist/img/silueta.png';
if (!empty($usuario['foto'])) {
    $foto_perfil = 'dist/img/personal/' . $usuario['foto'];
}

$nombre_completo = ucwords(strtolower(($usuario['nombres'] ?? '') . " " . ($usuario['apellidos'] ?? '')));
if (empty(trim($nombre_completo))) {
    $nombre_completo = $usuario['correo'];
}

$link_editar_perfil = '#settings';
$btn_id = 'btn-editar-perfil'; // ID para identificar el botón en JS
if (!empty($usuario['personal_hash'])) {
    $link_editar_perfil = 'personal_gestion.php?o=mod&id=' . $usuario['personal_hash'];
    $btn_id = ''; // Si es enlace externo, no necesitamos el ID del trigger
}

$status = $_GET['status'] ?? ''; // Para mostrar mensajes de éxito/error

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-<? echo $color; ?>">Mi Perfil</h1>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <?php if ($status == 'success'): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
                Tu perfil ha sido actualizado correctamente.
            </div>
            <?php elseif ($status == 'error' || $status == 'error_no_id'): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> ¡Error!</h5>
                Ocurrió un problema al actualizar tu perfil. Inténtalo de nuevo.
                <?php if (!empty($_GET['msg'])): ?>
                    <br><small>Detalle técnico: <?php echo htmlspecialchars($_GET['msg']); ?></small>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($status)): ?>
            <script>
                // Limpiar la URL para que el mensaje no se muestre al recargar
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.pathname);
                }
            </script>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-4">
                    <!-- Profile Image -->
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid img-circle"
                                     src="<?php echo $foto_perfil; ?>"
                                     alt="Foto de perfil de usuario"
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            </div>

                            <h3 class="profile-username text-center"><?php echo $nombre_completo; ?></h3>

                            <p class="text-muted text-center"><?php echo $rol; ?></p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Correo</b> <a class="float-right"><?php echo $usuario['correo']; ?></a>
                                </li>
                                <li class="list-group-item">
                                    <b>Miembro desde</b> <a class="float-right"><?php echo date("d/m/Y", strtotime($usuario['fecha_sistema'])); ?></a>
                                </li>
                            </ul>

                            <a href="<?php echo $link_editar_perfil; ?>" id="<?php echo $btn_id; ?>" class="btn btn-primary btn-block"><b>Editar Perfil</b></a>
                             <a href="cambio_clave.php" class="btn btn-warning btn-block mt-2"><b>Cambiar Contraseña</b></a>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#informacion" data-toggle="tab">Información</a></li>
                                <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Editar</a></li>
                            </ul>
                        </div><!-- /.card-header -->
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="informacion">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-book mr-1"></i> Educación</strong>
                                            <p class="text-muted">
                                                <?php echo !empty($usuario['educacion']) ? nl2br(htmlspecialchars($usuario['educacion'])) : '<i>Sin información registrada</i>'; ?>
                                            </p>
                                            <hr>
                                            <strong><i class="fas fa-map-marker-alt mr-1"></i> Ubicación</strong>
                                            <p class="text-muted"><?php echo !empty($usuario['ubicacion']) ? htmlspecialchars($usuario['ubicacion']) : '<i>Sin información registrada</i>'; ?></p>
                                            <hr>
                                        </div>
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-phone-alt mr-1"></i> Teléfono</strong>
                                            <p class="text-muted"><?php echo !empty($usuario['telefono']) ? htmlspecialchars($usuario['telefono']) : '<i>Sin información registrada</i>'; ?></p>
                                            <hr>
                                            <strong><i class="fas fa-birthday-cake mr-1"></i> Fecha de Nacimiento</strong>
                                            <p class="text-muted">
                                                <?php echo (!empty($usuario['fecha_nacimiento']) && $usuario['fecha_nacimiento'] != '0000-00-00') ? date("d/m/Y", strtotime($usuario['fecha_nacimiento'])) : '<i>Sin información registrada</i>'; ?>
                                            </p>
                                            <hr>
                                        </div>
                                        <div class="col-12">
                                            <strong><i class="far fa-file-alt mr-1"></i> Notas</strong>
                                            <p class="text-muted"><?php echo !empty($usuario['notas']) ? nl2br(htmlspecialchars($usuario['notas'])) : '<i>Sin notas adicionales</i>'; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.tab-pane -->

                                <div class="tab-pane" id="settings">
                                    <form class="form-horizontal" action="perfil_guardar.php" method="post" enctype="multipart/form-data">
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-2 col-form-label">Nombres</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="inputName" name="nombres" value="<?php echo $usuario['nombres'] ?? ''; ?>" placeholder="Nombres">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail" class="col-sm-2 col-form-label">Apellidos</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="inputEmail" name="apellidos" value="<?php echo $usuario['apellidos'] ?? ''; ?>" placeholder="Apellidos">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-2 col-form-label">Correo</label>
                                            <div class="col-sm-10">
                                                <input type="email" class="form-control" id="inputName2" name="correo" value="<?php echo $usuario['correo'] ?? ''; ?>" placeholder="Correo" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputPhone" class="col-sm-2 col-form-label">Teléfono</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="inputPhone" name="telefono" value="<?php echo $usuario['telefono'] ?? ''; ?>" placeholder="Teléfono">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputDob" class="col-sm-2 col-form-label">Fecha Nacimiento</label>
                                            <div class="col-sm-10">
                                                <input type="date" class="form-control" id="inputDob" name="fecha_nacimiento" value="<?php echo $usuario['fecha_nacimiento'] ?? ''; ?>">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputExperience" class="col-sm-2 col-form-label">Educación</label>
                                            <div class="col-sm-10">
                                                <textarea class="form-control" id="inputExperience" name="educacion" placeholder="Educación"><?php echo $usuario['educacion'] ?? ''; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputSkills" class="col-sm-2 col-form-label">Ubicación</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="inputSkills" name="ubicacion" value="<?php echo $usuario['ubicacion'] ?? ''; ?>" placeholder="Ubicación">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputSkills" class="col-sm-2 col-form-label">Notas</label>
                                            <div class="col-sm-10">
                                                <textarea class="form-control" name="notas" placeholder="Notas"><?php echo $usuario['notas'] ?? ''; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputPhoto" class="col-sm-2 col-form-label">Foto de Perfil</label>
                                            <div class="col-sm-10">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" id="inputPhoto" name="foto" accept="image/*">
                                                    <label class="custom-file-label" for="inputPhoto">Seleccionar archivo</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="offset-sm-2 col-sm-10">
                                                <button type="submit" class="btn btn-danger">Guardar</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.tab-pane -->
                            </div>
                            <!-- /.tab-content -->
                        </div><!-- /.card-body -->
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include_once(realpath(dirname(__FILE__)) . "/include/footer.php"); ?>

<!-- Scripts necesarios para que funcionen los botones y pestañas -->
<!-- jQuery -->
<script src="./plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="./dist/js/adminlte.min.js"></script>

<script>
$(function() {
    // Activar la pestaña de edición al hacer clic en el botón
    $('#btn-editar-perfil').click(function(e) {
        e.preventDefault();
        $('.nav-pills a[href="#settings"]').tab('show');
    });

    // Mostrar nombre del archivo seleccionado en el input file
    $('.custom-file-input').on('change', function () {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
});
</script>
