<?php
$modulo = 'guardia';
$modulo_titulo = 'Entrega de Guardia - Conductor';
include_once(realpath(dirname(__FILE__)) . "/include/header.php");

// Restricción: Admin (Rol 2) no puede crear estos formularios
if ($_SESSION['usuario']['rol_id'] == 2) {
    echo "<script>alert('No tiene permisos para acceder a este formulario.'); window.location.href='index.php';</script>";
    exit();
}

// Restricción: Paramédico (Rol 6) no puede acceder a Conductor
if ($_SESSION['usuario']['rol_id'] == 6) {
    echo "<script>alert('Acceso denegado. Área exclusiva para Conductores.'); window.location.href='index.php';</script>";
    exit();
}

// Obtener unidades activas para el selector
$stmt = $pdo->query("SELECT id, codigo, descripcion FROM unidades WHERE activo = 1 ORDER BY codigo ASC");
$unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener personal para el selector (solo si es admin/superadmin)
$personal_list = [];
if ($_SESSION['usuario']['rol_id'] == 1 || $_SESSION['usuario']['rol_id'] == 2) {
    $stmt = $pdo->query("SELECT id, nombres, apellidos FROM personal WHERE activo = 1 ORDER BY nombres ASC");
    $personal_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lógica para cargar datos si es edición
$id = $_GET['id'] ?? '';
$datos = [];
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM guardia_registros WHERE id = ? AND tipo = 'conductor'");
    $stmt->execute([$id]);
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($registro) $datos = json_decode($registro['datos'], true);
}
?>

<div class="content-wrapper">
    <!-- Encabezado General del Módulo -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-<? echo $color; ?>">
                        <img src="./dist/img/logo2.png" width="55" class="mr-2"> Cambio de Guardia 
                        <small class="text-muted">| Entrega de Unidad — Conductor —</small>
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form action="guardia_guardar.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="tipo_reporte" value="conductor">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-ambulance"></i> Datos de la Unidad</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="date" class="form-control" name="fecha" value="<?php echo $datos['fecha'] ?? date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Unidad</label>
                                    <select class="form-control select2" name="unidades" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach($unidades as $u): ?>
                                            <option value="<?= $u['codigo'] ?>" <?php if(($datos['unidades'] ?? '') == $u['codigo']) echo 'selected'; ?>><?= $u['codigo'] ?> - <?= $u['descripcion'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Kilometraje</label>
                                    <input type="number" step="0.1" class="form-control" name="kilometraje" value="<?php echo $datos['kilometraje'] ?? ''; ?>" placeholder="Ej: 33496.2">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Responsable</label>
                                    <?php if ($_SESSION['usuario']['rol_id'] == 1 || $_SESSION['usuario']['rol_id'] == 2): ?>
                                        <select class="form-control select2" name="responsable" required>
                                            <option value="">Seleccione...</option>
                                            <?php 
                                            $responsable_actual = $datos['responsable'] ?? (($_SESSION['usuario']['nombres'] ?? '') . ' ' . ($_SESSION['usuario']['apellidos'] ?? ''));
                                            foreach($personal_list as $p): 
                                                $nombre_completo = $p['nombres'] . ' ' . $p['apellidos'];
                                            ?>
                                                <option value="<?= $nombre_completo ?>" <?php if($responsable_actual == $nombre_completo) echo 'selected'; ?>><?= $nombre_completo ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <input type="text" class="form-control" name="responsable" value="<?php echo $datos['responsable'] ?? (($_SESSION['usuario']['nombres'] ?? '') . ' ' . ($_SESSION['usuario']['apellidos'] ?? '')); ?>" readonly>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Mecánica y Fluidos -->
                    <div class="col-md-6">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-cogs"></i> Mecánica y Fluidos</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <tbody>
                                        <tr>
                                            <td>Combustible</td>
                                            <td>
                                                <select class="form-control form-control-sm" name="combustible">
                                                    <option value="Full" <?php if(($datos['combustible'] ?? '') == 'Full') echo 'selected'; ?>>Full</option>
                                                    <option value="3/4" <?php if(($datos['combustible'] ?? '') == '3/4') echo 'selected'; ?>>3/4</option>
                                                    <option value="1/2" <?php if(($datos['combustible'] ?? '') == '1/2') echo 'selected'; ?>>1/2</option>
                                                    <option value="1/4" <?php if(($datos['combustible'] ?? '') == '1/4') echo 'selected'; ?>>1/4</option>
                                                    <option value="Reserva" <?php if(($datos['combustible'] ?? '') == 'Reserva') echo 'selected'; ?>>Reserva</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <?php 
                                        $items_mecanica = [
                                            'Aceite de motor' => ['Full', 'Medio', 'Bajo'],
                                            'Aceite hidráulico' => ['Full', 'Medio', 'Bajo'],
                                            'Liga de freno' => ['Bien', 'Regular', 'Mal'],
                                            'Refrigerante' => ['Bien', 'Regular', 'Mal'],
                                            'Cauchos' => ['Bien', 'Regular', 'Mal'],
                                            'Limpieza General' => ['Bien', 'Regular', 'Mal']
                                        ];
                                        foreach($items_mecanica as $item => $opts) {
                                            $name = 'item_'.str_replace(' ','_',$item);
                                            $val = $datos[$name] ?? '';
                                            echo "<tr><td>$item</td><td><select class='form-control form-control-sm' name='$name'>";
                                            foreach($opts as $opt) {
                                                $sel = ($val == $opt) ? 'selected' : '';
                                                echo "<option value='$opt' $sel>$opt</option>";
                                            }
                                            echo "</select></td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seguridad y Herramientas -->
                    <div class="col-md-6">
                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-tools"></i> Seguridad y Herramientas</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td>Extintor</td>
                                            <td><input type="text" class="form-control form-control-sm" name="extintor" value="<?php echo $datos['extintor'] ?? ''; ?>" placeholder="Estado (Ej: Descargado)"></td>
                                        </tr>
                                        <?php 
                                        $items_seguridad = ['Caucho de repuesto', 'Gato', 'Cono', 'Triángulo de seguridad', 'Llave de cruz'];
                                        foreach($items_seguridad as $item) {
                                            $name = 'seg_'.str_replace(' ','_',$item);
                                            $val = $datos[$name] ?? 'Si';
                                            $chk_si = ($val == 'Si') ? 'checked' : '';
                                            $chk_no = ($val == 'No') ? 'checked' : '';
                                            $extra_obs = '';
                                            if ($item == 'Caucho de repuesto') {
                                                $extra_obs = '<input type="text" class="form-control form-control-sm d-inline-block ml-2" style="width: 140px;" name="obs_caucho_repuesto" value="'.($datos['obs_caucho_repuesto'] ?? '').'" placeholder="Estado/Obs.">';
                                            }
                                            echo "<tr><td>$item</td><td>
                                                <div class='form-check form-check-inline'>
                                                    <input class='form-check-input' type='radio' name='$name' value='Si' $chk_si> <label class='form-check-label'>Si</label>
                                                </div>
                                                <div class='form-check form-check-inline'>
                                                    <input class='form-check-input' type='radio' name='$name' value='No' $chk_no> <label class='form-check-label'>No</label>
                                                </div>
                                                $extra_obs
                                            </td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sistema Eléctrico -->
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-lightbulb"></i> Sistema Eléctrico e Iluminación</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php
                            $luces = [
                                'Cruces delantera Izq.', 'Cruces Delantero Der.', 
                                'Cruces Trasero Izq.', 'Cruces Trasero Der.',
                                'Luces Stop', 'Luces Retro',
                                'Luces Delanteras Bajas', 'Luces Delanteras Altas',
                                'Luces Estroboscópicas', 'Laterales Izq.', 'Laterales Der.'
                            ];
                            foreach($luces as $luz) {
                                $name = 'luz_'.str_replace([' ','.'],'_',$luz);
                                $val = $datos[$name] ?? 'Bien';
                                echo '<div class="col-md-4 mb-2">
                                    <label class="small mb-0">'.$luz.'</label>
                                    <input type="text" class="form-control form-control-sm" name="'.$name.'" value="'.$val.'">
                                </div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Fotos -->
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-camera"></i> Registro Fotográfico</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Registro Fotográfico (Daños, estado general, tableros)</label>
                            <div class="row">
                                <div class="col-12">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="fotos" name="fotos[]" multiple accept="image/*">
                                        <label class="custom-file-label" for="fotos">Galería / Cámara</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <a href="guardia_listado.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary float-right">Guardar Entrega de Guardia</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<?php include_once(realpath(dirname(__FILE__)) . "/include/footer.php"); ?>