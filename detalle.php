<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario'])) { header("Location: index_login.php"); exit(); }
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

$id = $_GET['id'] ?? die("Error ID");

// Modificamos la consulta para ser más permisiva con LEFT JOIN por si faltan IDs
$stmt = $pdo->prepare("SELECT s.*, t.nombre as act_nom, t.abreviacion, e.nombre as estatus_nom_rel, u.codigo as unidad_cod, u.descripcion as unidad_desc 
                       FROM servicios s
                       LEFT JOIN tipos_actividad t ON s.tipo_actividad_id = t.id 
                       LEFT JOIN estatus e ON s.estatus_id = e.id 
                       LEFT JOIN unidades u ON s.unidad_id = u.id
                       WHERE s.id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();

if (!$s) {
    if (!isset($pdf_mode) || !$pdf_mode) {
        $modulo = 'eventos';
        include_once(realpath(dirname(__FILE__)) . "/include/header.php");
        echo "<div class='content-wrapper'><section class='content'><div class='container-fluid'><div class='alert alert-danger mt-3'>El evento solicitado no existe o ha sido eliminado.</div><a href='eventos_listado.php' class='btn btn-secondary'>Volver al Listado</a></div></section></div>";
        include_once(realpath(dirname(__FILE__)) . "/include/footer.php");
    } else {
        echo "El evento solicitado no existe.";
    }
    exit();
}

$stmt_act = $pdo->prepare("SELECT p.nombres, p.apellidos, p.cedula, sa.rol FROM servicio_actuantes sa 
                           JOIN personal p ON sa.personal_id = p.id WHERE sa.servicio_id = ?");
$stmt_act->execute([$id]);
$actuantes = $stmt_act->fetchAll();
$cant_funcionarios = count($actuantes);

// Determinar si estamos en modo PDF o Web
$is_pdf = isset($pdf_mode) && $pdf_mode;

if (!$is_pdf) {
    $modulo = 'eventos';
    $modulo_titulo = 'Detalle del Evento';
    include_once(realpath(dirname(__FILE__)) . "/include/header.php");
}
?>

<?php if ($is_pdf): ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <style> .reporte { background: white; padding: 20px; font-family: Arial, sans-serif; font-size: 12px; } h3 { text-align: center; } .bold { font-weight: bold; } </style>
    </head>
    <body>
        <div class="reporte">
<?php else: ?>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-<? echo $color; ?>">
                            <img src="./dist/img/logo2.png" width="55" class="mr-2"> Detalle del Evento
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        
                        <!-- Botones de Acción Superior -->
                        <div class="mb-3 text-right no-print">
                            <button class="btn btn-success shadow-sm" onclick="copiarReporte()">
                                <i class="fas fa-copy"></i> Copiar Reporte
                            </button>
                            <button class="btn btn-dark shadow-sm" onclick="window.print()">
                                <i class="fas fa-print"></i> Imprimir
                            </button>
                            <a href="generar_pdf.php?id=<?php echo $id; ?>" class="btn btn-danger shadow-sm" target="_blank">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            <a href="eventos_listado.php" class="btn btn-secondary shadow-sm">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>

                        <div class="card shadow-lg border-0 rounded-lg">
                            <!-- Encabezado Moderno -->
                            <div class="card-header bg-white border-bottom-0 text-center pt-4 pb-0">
                                <h5 class="font-weight-bold text-uppercase mb-0 text-dark">NOVEDAD RELEVANTE</h5>
                                <p class="text-muted small mb-0">REEDAN CAPITAL | ZOEDAN</p>
                                <p class="font-weight-bold text-primary small">DIRECCIÓN MUNICIPAL DE PROTECCIÓN CIVIL Y ADMINISTRACIÓN DE DESASTRES EL HATILLO</p>
                                <hr class="mt-3">
                            </div>

                            <div class="card-body pt-2">
<?php endif; ?>
                                <!-- Bloque 1: Ubicación y Tiempo -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>FECHA:</strong> <?php echo date('d/m/Y', strtotime($s['fecha_registro'])); ?></p>
                                        <p class="mb-1"><strong>HORA:</strong> <?php echo $s['hora_inicio'] ?? '--:--'; ?></p>
                                        <p class="mb-1"><strong>ESTADO:</strong> Miranda</p>
                                        <p class="mb-1"><strong>MUNICIPIO:</strong> El Hatillo</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Parroquia:</strong> <?php echo $s['parroquia']; ?></p>
                                        <p class="mb-1"><strong>Cuadrante:</strong> <?php echo $s['cuadrante']; ?></p>
                                        <p class="mb-1"><strong>Dirección:</strong> <?php echo $s['direccion_detallada']; ?></p>
                                    </div>
                                </div>

                                <!-- Bloque 2: Detalles del Servicio -->
                                <div class="alert alert-light border">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Tipo de Actividad:</strong> <?php echo $s['categoria'] ?? 'N/A'; ?>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Tipo de Operación:</strong> <?php echo $s['act_nom'] ?? $s['tipo_actividad_nombre'] ?? 'N/A'; ?>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <strong>NRO de servicio:</strong> <span class="badge badge-danger" style="font-size: 1em;"><?php echo $s['nro_servicio']; ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bloque 3: Resúmenes -->
                                <div class="mb-4">
                                    <h6 class="text-primary font-weight-bold border-bottom pb-2">RESUMEN (OPERADOR)</h6>
                                    <div class="bg-light p-3 rounded text-justify">
                                        <?php echo nl2br($s['resumen_novedad']); ?>
                                    </div>
                                </div>

                                <?php if(!empty($s['reporte_sitio'])): ?>
                                <div class="mb-4">
                                    <h6 class="text-danger font-weight-bold border-bottom pb-2">REPORTE DE ESCENA</h6>
                                    <div class="bg-light p-3 rounded text-justify">
                                        <?php echo nl2br($s['reporte_sitio']); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Bloque 4: Actuantes -->
                                <div class="mb-4">
                                    <h6 class="text-dark font-weight-bold border-bottom pb-2">ACTUANTES</h6>
                                    <p class="mb-1"><strong>Protección Civil El Hatillo:</strong> <?php echo str_pad($cant_funcionarios, 2, '0', STR_PAD_LEFT); ?> funcionarios.</p>
                                    <ul class="list-unstyled ml-3">
                                        <?php foreach($actuantes as $a): ?>
                                            <li><i class="fas fa-user-shield text-muted mr-2"></i> <strong><?php echo $a['rol']; ?>:</strong> <?php echo $a['nombres'] . ' ' . $a['apellidos']; ?> (CI <?php echo $a['cedula']; ?>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                    
                                    <?php if(!empty($s['organismo'])): ?>
                                        <p class="mt-2 mb-1"><strong>ORGANISMO:</strong></p>
                                        <p class="ml-3"><?php echo $s['organismo']; ?></p>
                                    <?php endif; ?>

                                    <?php if(!empty($s['unidad_cod'])): ?>
                                        <p class="mt-2 mb-1"><strong>UNIDAD:</strong></p>
                                        <p class="ml-3"><?php echo $s['unidad_cod'] . " - " . $s['unidad_desc']; ?></p>
                                    <?php endif; ?>
                                </div>

                                <!-- Bloque 5: Estatus y Fotos -->
                                <div class="row align-items-center mb-3">
                                    <div class="col-12">
                                        <strong>Estatus:</strong> 
                                        <?php 
                                        $est_nombre = strtoupper($s['estatus_nom_rel'] ?? $s['estatus_nombre'] ?? 'N/A');
                                        $badge_class = ($est_nombre == 'ABIERTO') ? 'badge-danger' : (($est_nombre == 'EN PROCESO') ? 'badge-warning' : 'badge-success');
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?> p-2"><?php echo $est_nombre; ?></span>
                                    </div>
                                </div>

                                <?php if (!empty($s['foto'])): ?>
                                    <div class="mt-4">
                                        <h6 class="font-weight-bold border-bottom pb-2">FOTOS DEL EVENTO</h6>
                                        <div class="text-center bg-dark rounded p-2">
                                            <img src="dist/img/eventos/<?php echo $s['foto']; ?>" class="img-fluid rounded" style="max-height: 400px;">
                                        </div>
                                    </div>
                                <?php endif; ?>

<?php if ($is_pdf): ?>
        </div>
    </body>
    </html>
<?php else: ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Script para Copiar al Portapapeles -->
    <script>
    function copiarReporte() {
        // Construimos el texto exactamente como se solicitó
        let texto = `NOVEDAD RELEVANTE*
‎REEDAN CAPITAL
‎ZOEDAN
‎DIRECCIÓN MUNICIPAL DE PROTECCIÓN CIVIL Y ADMINISTRACIÓN DE DESASTRES EL HATILLO

‎FECHA: <?php echo date('d/m/Y', strtotime($s['fecha_registro'])); ?>
HORA: <?php echo $s['hora_inicio'] ?? ''; ?>
‎ESTADO: Miranda
‎MUNICIPIO: El Hatillo
‎Parroquia: <?php echo $s['parroquia']; ?>
‎Cuadrante: <?php echo $s['cuadrante']; ?>

‎Tipo de Actividad: <?php echo $s['categoria'] ?? 'N/A'; ?>
Tipo de Operación: <?php echo $s['act_nom'] ?? $s['tipo_actividad_nombre'] ?? 'N/A'; ?>
‎NRO de servicio: <?php echo $s['nro_servicio']; ?>

‎RESUMEN (OPERADOR): 
<?php echo strip_tags($s['resumen_novedad']); ?>

REPORTE DE ESCENA:
<?php echo strip_tags($s['reporte_sitio'] ?? ''); ?>

‎ACTUANTES:
‎Protección Civil El Hatillo: <?php echo str_pad($cant_funcionarios, 2, '0', STR_PAD_LEFT); ?> funcionarios. 
<?php foreach($actuantes as $a) {
    echo "‎- " . $a['rol'] . " : " . $a['nombres'] . " " . $a['apellidos'] . " CI " . $a['cedula'] . "\\n";
} ?>
ORGANISMO:
<?php echo $s['organismo']; ?>

‎Estatus: <?php echo $s['estatus_nom_rel'] ?? $s['estatus_nombre'] ?? 'N/A'; ?>
FOTOS DEL EVENTO:
<?php echo !empty($s['foto']) ? 'Sí (Ver adjunto)' : 'No registradas'; ?>`;

        // Crear un elemento temporal para copiar
        const el = document.createElement('textarea');
        el.value = texto;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);

        // Alerta visual (usando SweetAlert si está disponible, o alert normal)
        if(typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '¡Copiado!',
                text: 'El reporte ha sido copiado al portapapeles.',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            alert('Reporte copiado al portapapeles');
        }
    }
    </script>

    <?php include_once(realpath(dirname(__FILE__)) . "/include/footer.php"); ?>

    <!-- jQuery -->
    <script src="./plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="./dist/js/adminlte.min.js"></script>
<?php endif; ?>