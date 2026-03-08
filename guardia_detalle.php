<?php
$modulo = 'guardia';
$modulo_titulo = 'Detalle de Guardia';
include_once(realpath(dirname(__FILE__)) . "/include/header.php");

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM guardia_registros WHERE id = ?");
$stmt->execute([$id]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registro) {
    echo "<div class='content-wrapper'><div class='content p-4'><h3>Registro no encontrado</h3></div></div>";
    include_once(realpath(dirname(__FILE__)) . "/include/footer.php");
    exit;
}

$datos = json_decode($registro['datos'], true);
$fotos = json_decode($registro['fotos'], true);
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>Reporte de <?php echo ucfirst($registro['tipo']); ?> <small>#<?php echo $registro['id']; ?></small></h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-<?php echo ($registro['tipo'] == 'conductor') ? 'primary' : 'success'; ?>">
                    <h3 class="card-title">
                        <i class="fas <?php echo ($registro['tipo'] == 'conductor') ? 'fa-ambulance' : 'fa-user-md'; ?>"></i>
                        Unidad: <?php echo $registro['unidad']; ?> - Fecha: <?php echo date('d/m/Y', strtotime($registro['fecha'])); ?>
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4"><strong>Responsable:</strong> <?php echo $registro['usuario']; ?></div>
                        <div class="col-md-4"><strong>Fecha Registro:</strong> <?php echo $registro['created_at']; ?></div>
                        <?php if(isset($datos['kilometraje'])): ?>
                        <div class="col-md-4"><strong>Kilometraje:</strong> <?php echo $datos['kilometraje']; ?></div>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <?php if ($registro['tipo'] == 'conductor'): ?>
                        <h5>Chequeo de Unidad</h5>
                        <div class="row">
                            <?php foreach ($datos as $key => $val): 
                                if (strpos($key, 'item_') === 0 || strpos($key, 'seg_') === 0 || strpos($key, 'luz_') === 0 || $key == 'combustible' || $key == 'extintor'): 
                                    $label = str_replace(['item_', 'seg_', 'luz_', '_'], ['','','',' '], $key);
                                    $color = ($val == 'Mal' || $val == 'No' || $val == 'Bajo') ? 'text-danger font-weight-bold' : '';
                            ?>
                                <div class="col-md-3 mb-2">
                                    <span class="text-muted small"><?php echo ucfirst($label); ?>:</span><br>
                                    <span class="<?php echo $color; ?>"><?php echo $val; ?></span>
                                </div>
                            <?php endif; endforeach; ?>
                        </div>
                        
                        <?php if(!empty($datos['obs_caucho_repuesto'])): ?>
                            <div class="alert alert-warning mt-3">
                                <strong>Obs. Caucho Repuesto:</strong> <?php echo $datos['obs_caucho_repuesto']; ?>
                            </div>
                        <?php endif; ?>

                    <?php else: // Paramedico ?>
                        <h5>Inventario de Insumos</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light"><tr><th>Insumo</th><th>Cantidad</th></tr></thead>
                                <tbody>
                                    <?php foreach ($datos as $key => $val): 
                                        if (strpos($key, 'inv_') === 0 && $val !== ''): 
                                            $label = str_replace(['inv_', '_'], ['',' '], $key);
                                    ?>
                                        <tr>
                                            <td><?php echo ucfirst($label); ?></td>
                                            <td class="font-weight-bold"><?php echo $val; ?></td>
                                        </tr>
                                    <?php endif; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if(!empty($datos['observaciones'])): ?>
                            <div class="alert alert-info mt-3">
                                <strong>Observaciones:</strong><br> <?php echo nl2br($datos['observaciones']); ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Galería de Fotos -->
                    <?php if (!empty($fotos)): ?>
                        <hr>
                        <h5>Registro Fotográfico</h5>
                        <div class="row">
                            <?php foreach ($fotos as $foto): ?>
                                <div class="col-sm-3">
                                    <a href="<?php echo $foto; ?>" target="_blank">
                                        <img src="<?php echo $foto; ?>" class="img-fluid mb-2 rounded shadow-sm" style="max-height: 150px;">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
                <div class="card-footer">
                    <a href="guardia_listado.php" class="btn btn-secondary">Volver al listado</a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include_once(realpath(dirname(__FILE__)) . "/include/footer.php"); ?>