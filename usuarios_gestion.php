<?php
$modulo = 'usuarios';
$modulo_titulo = 'Gestión de Usuarios';

include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

$op = (isset($_GET['o']) && $_GET['o']) ? strval($_GET['o']) : '';
$id = (isset($_GET['id']) && $_GET['id']) ? strval($_GET['id']) : '';

$usuarios_obj = new Usuarios();
$personal_obj = new Personal();

// Valores por defecto
$datos = [
    'nombres' => '',
    'apellidos' => '',
    'usuario' => '',
    'correo' => '',
    'rol_id' => '',
    'activo' => 1,
    'hash' => '',
    'personal_id' => ''
];

// Obtener lista de roles dinámicamente desde la BD
$lista_roles = [];
try {
    $stmt_roles = $pdo->query("SELECT id, nombre_rol FROM roles WHERE eliminado = 0 ORDER BY id ASC");
    $lista_roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Manejo silencioso
}

if ($op == '' && $id == '') {
    $op = "add";
    $titulo = "Crear Usuario";
    $modificar = 1;
} else {
    $res = $usuarios_obj->Consultar($_DB_, 'hash', $id);
    if ($res) {
        $datos = $res[0];

        // Seguridad: Admin (Rol 2) no puede editar a Superadmin (Rol 1)
        if ($_SESSION['usuario']['rol_id'] == 2 && $datos['rol_id'] == 1) {
            echo "<script>alert('No tiene permisos para gestionar este usuario.'); window.location.href='usuarios_listado.php';</script>";
            exit();
        }
        
        // Buscar datos del personal asociado para llenar nombres y apellidos
        if (!empty($datos['personal_id'])) {
            $per = $personal_obj->Consultar($_DB_, 'id', $datos['personal_id']);
            if ($per) {
                $datos['nombres'] = $per[0]['nombres'];
                $datos['apellidos'] = $per[0]['apellidos'];
            }
        }

        if ($op == 'mod') {
            $titulo = "Modificar Usuario";
            $modificar = 1;
        } elseif ($op == 'del') {
            $titulo = "Eliminar Usuario";
            $modificar = 1;
        } else {
            $titulo = "Consultar Usuario";
            $modificar = 0;
        }
    }
}

include_once(realpath(dirname(__FILE__)) . "/include/header.php");
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-<? echo $color; ?>"><img src="./dist/img/logo2.png" width="55"> <? echo $modulo_titulo; ?></h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <form action="usuarios_operacion.php" method="POST">
                        <input type="hidden" name="op" value="<? echo $op; ?>">
                        <input type="hidden" name="id" value="<? echo $datos['hash']; ?>">
                        <input type="hidden" name="personal_id" value="<? echo $datos['personal_id']; ?>">

                        <div class="card card-<? echo $color; ?>">
                            <div class="card-header">
                                <h3 class="card-title"><? echo $titulo; ?></h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Nombres -->
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Nombres</label>
                                            <input type="text" name="nombres" value="<? echo $datos['nombres']; ?>" class="form-control" <? if (!$modificar) echo 'readonly'; ?> required>
                                        </div>
                                    </div>
                                    <!-- Apellidos -->
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Apellidos</label>
                                            <input type="text" name="apellidos" value="<? echo $datos['apellidos']; ?>" class="form-control" <? if (!$modificar) echo 'readonly'; ?> required>
                                        </div>
                                    </div>
                                    
                                    <!-- Usuario -->
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Usuario</label>
                                            <input type="text" name="usuario" value="<? echo $datos['usuario'] ?? ''; ?>" class="form-control" <? if (!$modificar) echo 'readonly'; ?> required>
                                        </div>
                                    </div>

                                    <!-- Correo -->
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Correo Electrónico</label>
                                            <input type="email" name="correo" value="<? echo $datos['correo']; ?>" class="form-control" <? if (!$modificar) echo 'readonly'; ?> required>
                                        </div>
                                    </div>

                                    <!-- Contraseña -->
                                    <?php if ($modificar): ?>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Contraseña <?php if($op == 'mod') echo '(Dejar en blanco para no cambiar)'; ?></label>
                                            <input type="password" name="clave" class="form-control" <? if ($op == 'add') echo 'required'; ?>>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Rol -->
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Rol</label>
                                            <select name="rol_id" class="form-control" <? if (!$modificar) echo 'disabled'; ?>>
                                                <option value="">Seleccione un rol...</option>
                                                <?php foreach ($lista_roles as $rol): 
                                                    // Ocultar Superadmin si no soy Superadmin
                                                    if ($_SESSION['usuario']['rol_id'] != 1 && $rol['id'] == 1) continue;
                                                ?>
                                                    <option value="<?= $rol['id'] ?>" <? if ($datos['rol_id'] == $rol['id']) echo 'selected'; ?>><?= $rol['nombre_rol'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Activo -->
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Estatus</label>
                                            <select name="activo" class="form-control" <? if (!$modificar) echo 'disabled'; ?>>
                                                <option value="1" <? if ($datos['activo'] == 1) echo 'selected'; ?>>Activo</option>
                                                <option value="0" <? if ($datos['activo'] == 0) echo 'selected'; ?>>Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <a onclick="history.back();" class="btn btn-default">Volver</a>
                            </div>
                            <div class="col-sm-6 text-right">
                                <?php if ($modificar): ?>
                                    <?php if ($op == "add"): ?>
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Usuario</button>
                                    <?php elseif ($op == "mod"): ?>
                                        <button type="submit" class="btn btn-warning"><i class="fas fa-pen"></i> Modificar Usuario</button>
                                    <?php elseif ($op == "del"): ?>
                                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> Eliminar Usuario</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include_once(realpath(dirname(__FILE__)) . "/include/footer.php"); ?>

<!-- jQuery -->
<script src="./plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="./dist/js/adminlte.min.js"></script>

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
                footer: '<span style="color:#6c757d">Verifique el listado de usuarios activos</span>',
                confirmButtonColor: '#d33',
                confirmButtonText: '<i class="fas fa-thumbs-up"></i> Entendido',
                backdrop: `rgba(0,0,123,0.1)`
            });
            // Limpiar la URL para que al recargar no salga de nuevo
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
</script>
</body>
</html>