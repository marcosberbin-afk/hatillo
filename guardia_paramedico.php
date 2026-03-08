<?php
$modulo = 'guardia';
$modulo_titulo = 'Entrega de Guardia - Paramédico';
include_once(realpath(dirname(__FILE__)) . "/include/header.php");

// Restricción: Admin (Rol 2) no puede crear estos formularios
if ($_SESSION['usuario']['rol_id'] == 2) {
    echo "<script>alert('No tiene permisos para acceder a este formulario.'); window.location.href='index.php';</script>";
    exit();
}

// Restricción: Conductor (Rol 5) no puede acceder a Paramédico
if ($_SESSION['usuario']['rol_id'] == 5) {
    echo "<script>alert('Acceso denegado. Área exclusiva para Paramédicos.'); window.location.href='index.php';</script>";
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
    $stmt = $pdo->prepare("SELECT * FROM guardia_registros WHERE id = ? AND tipo = 'paramedico'");
    $stmt->execute([$id]);
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($registro) $datos = json_decode($registro['datos'], true);
}

// Función auxiliar para generar filas de inventario
function renderInventoryRow($label, $name, $val = '') {
    return '
    <tr>
        <td>'.$label.'</td>
        <td width="80">
            <input type="number" class="form-control form-control-sm" name="inv_'.$name.'" value="'.$val.'" placeholder="Cant." min="0">
        </td>
    </tr>';
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
                        <small class="text-muted">| Inventario de Unidad — Paramédico —</small>
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form action="guardia_guardar.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="tipo_reporte" value="paramedico">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                <!-- Datos Generales -->
                <div class="card card-success">
                    <div class="card-body py-2">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Fecha</label>
                                <input type="date" class="form-control form-control-sm" name="fecha" value="<?php echo $datos['fecha'] ?? date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label>Unidad</label>
                                <select class="form-control form-control-sm select2" name="unidades" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach($unidades as $u): ?>
                                        <option value="<?= $u['codigo'] ?>" <?php if(($datos['unidades'] ?? '') == $u['codigo']) echo 'selected'; ?>><?= $u['codigo'] ?> - <?= $u['descripcion'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Responsable</label>
                                <?php if ($_SESSION['usuario']['rol_id'] == 1 || $_SESSION['usuario']['rol_id'] == 2): ?>
                                    <select class="form-control form-control-sm select2" name="responsable" required>
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
                                    <input type="text" class="form-control form-control-sm" name="responsable" value="<?php echo $datos['responsable'] ?? (($_SESSION['usuario']['nombres'] ?? '') . ' ' . ($_SESSION['usuario']['apellidos'] ?? '')); ?>" readonly>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Columna Izquierda -->
                    <div class="col-md-6">
                        
                        <div class="card card-primary">
                            <div class="card-header"><h3 class="card-title">Vía Aérea y Oxigenoterapia</h3></div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <tbody>
                                <?php
                                echo renderInventoryRow('Cánulas orofaringuea adulto', 'canula_oro_adulto', $datos['inv_canula_oro_adulto'] ?? '');
                                echo renderInventoryRow('Cánula orofaringuea pediátrica', 'canula_oro_ped', $datos['inv_canula_oro_ped'] ?? '');
                                echo renderInventoryRow('BVM + Mascarilla Adulto', 'bvm_adulto', $datos['inv_bvm_adulto'] ?? '');
                                echo renderInventoryRow('Mascarilla simple de oxígeno', 'mascarilla_simple', $datos['inv_mascarilla_simple'] ?? '');
                                echo renderInventoryRow('Mascarilla nebulizar ped.', 'mascarilla_neb_ped', $datos['inv_mascarilla_neb_ped'] ?? '');
                                echo renderInventoryRow('Cánulas nasales adulto', 'canula_nasal_adulto', $datos['inv_canula_nasal_adulto'] ?? '');
                                echo renderInventoryRow('Cánulas nasales pediátrico', 'canula_nasal_ped', $datos['inv_canula_nasal_ped'] ?? '');
                                echo renderInventoryRow('Vaso humidificador', 'vaso_humidificador', $datos['inv_vaso_humidificador'] ?? '');
                                echo renderInventoryRow('Cilindro Oxígeno portátil', 'oxigeno_portatil', $datos['inv_oxigeno_portatil'] ?? '');
                                ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </div>

                        <div class="card card-warning">
                            <div class="card-header"><h3 class="card-title">Circulación y Fluidos</h3></div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <tbody>
                                <?php
                                echo renderInventoryRow('Solución 0.9% 500 ml', 'solucion_500', $datos['inv_solucion_500'] ?? '');
                                echo renderInventoryRow('Solución 0.9% 1000 ml', 'solucion_1000', $datos['inv_solucion_1000'] ?? '');
                                echo renderInventoryRow('Agua destilada', 'agua_destilada', $datos['inv_agua_destilada'] ?? '');
                                echo renderInventoryRow('Jelco 16G', 'jelco_16', $datos['inv_jelco_16'] ?? '');
                                echo renderInventoryRow('Jelco 18G', 'jelco_18', $datos['inv_jelco_18'] ?? '');
                                echo renderInventoryRow('Jelco 20G', 'jelco_20', $datos['inv_jelco_20'] ?? '');
                                echo renderInventoryRow('Scalp 21G / 22G', 'scalp', $datos['inv_scalp'] ?? '');
                                echo renderInventoryRow('Agujas', 'agujas', $datos['inv_agujas'] ?? '');
                                echo renderInventoryRow('Jeringas 2ml / 5ml', 'jeringas_peq', $datos['inv_jeringas_peq'] ?? '');
                                echo renderInventoryRow('Jeringas 10ml / 20ml', 'jeringas_gra', $datos['inv_jeringas_gra'] ?? '');
                                echo renderInventoryRow('Microgotero / Transofix', 'microgotero', $datos['inv_microgotero'] ?? '');
                                echo renderInventoryRow('Fija vías', 'fija_vias', $datos['inv_fija_vias'] ?? '');
                                echo renderInventoryRow('Torniquete', 'torniquete', $datos['inv_torniquete'] ?? '');
                                ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Columna Derecha -->
                    <div class="col-md-6">
                        
                        <div class="card card-danger">
                            <div class="card-header"><h3 class="card-title">Trauma y Curas</h3></div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <tbody>
                                <?php
                                echo renderInventoryRow('Vendas elásticas (varias)', 'vendas', $datos['inv_vendas'] ?? '');
                                echo renderInventoryRow('Vendaje triangular', 'vendaje_triangular', $datos['inv_vendaje_triangular'] ?? '');
                                echo renderInventoryRow('Gasas estériles', 'gasas', $datos['inv_gasas'] ?? '');
                                echo renderInventoryRow('Apósitos / Centros de cama', 'apositos', $datos['inv_apositos'] ?? '');
                                echo renderInventoryRow('Adhesivo Plastod', 'adhesivo', $datos['inv_adhesivo'] ?? '');
                                echo renderInventoryRow('Envase Povidine / Sulfadiazina', 'antisepticos', $datos['inv_antisepticos'] ?? '');
                                echo renderInventoryRow('Guantes descartables (pares)', 'guantes_desc', $datos['inv_guantes_desc'] ?? '');
                                echo renderInventoryRow('Guantes estériles', 'guantes_est', $datos['inv_guantes_est'] ?? '');
                                ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </div>

                        <div class="card card-info">
                            <div class="card-header"><h3 class="card-title">Equipos y Habitáculo</h3></div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <tbody>
                                <?php
                                echo renderInventoryRow('Tensiómetro + Estetoscopio', 'tensiometro', $datos['inv_tensiometro'] ?? '');
                                echo renderInventoryRow('Termómetro digital', 'termometro', $datos['inv_termometro'] ?? '');
                                echo renderInventoryRow('Tijera de extricación', 'tijera', $datos['inv_tijera'] ?? '');
                                echo renderInventoryRow('Collarín adulto', 'collarin_adulto', $datos['inv_collarin_adulto'] ?? '');
                                echo renderInventoryRow('Collarín pediátrico', 'collarin_ped', $datos['inv_collarin_ped'] ?? '');
                                echo renderInventoryRow('Camilla principal + cinturones', 'camilla_ppal', $datos['inv_camilla_ppal'] ?? '');
                                echo renderInventoryRow('Tabla Rígida + cinturones', 'tabla_rigida', $datos['inv_tabla_rigida'] ?? '');
                                echo renderInventoryRow('Silla de ruedas plegable', 'silla_ruedas', $datos['inv_silla_ruedas'] ?? '');
                                echo renderInventoryRow('Férulas (Kit/Moldeable)', 'ferulas', $datos['inv_ferulas'] ?? '');
                                echo renderInventoryRow('Inmovilizador lateral (Parietales)', 'parietales', $datos['inv_parietales'] ?? '');
                                echo renderInventoryRow('Manta térmica', 'manta_termica', $datos['inv_manta_termica'] ?? '');
                                echo renderInventoryRow('Envase desechos (Amarillo)', 'desechos', $datos['inv_desechos'] ?? '');
                                ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Observaciones Adicionales</label>
                            <textarea class="form-control" name="observaciones" rows="3" placeholder="Novedades sobre el material faltante o dañado..."><?php echo $datos['observaciones'] ?? ''; ?></textarea>
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
                            <label>Evidencias / Novedades</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="fotos" name="fotos[]" multiple accept="image/*">
                                <label class="custom-file-label" for="fotos">Galería / Cámara</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <a href="guardia_listado.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success float-right">Guardar Inventario</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<?php include_once(realpath(dirname(__FILE__)) . "/include/footer.php"); ?>