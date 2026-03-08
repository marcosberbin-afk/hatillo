<?php
$modulo = 'estadisticas';
$modulo_titulo = 'Estadísticas';
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");
include_once(realpath(dirname(__FILE__)) . "/include/header.php");

// --- Filtros de Fecha ---
$anio = isset($_GET['anio']) ? $_GET['anio'] : date('Y');
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');

$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
    7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

// Inicializar variables
$data_actividades = [];
$labels_cuad = [];
$data_cuad = [];
$labels_dias = [];
$data_dias = [];
$labels_cats = [];
$data_cats = [];
// Inicialización de variables para evitar Notices en el reporte
$total_servicios_mes = 0;
$max_act_nombre = 'Sin actividad';
$max_act_val = 0;
$max_cuad_nombre = 'Sin definir';
$max_cuad_val = 0;
$error_msg = "";

try {
    // ---------------------------------------------------------
    // 1. CONSULTA: Actividades por Tipo y Semana (Matriz)
    // ---------------------------------------------------------
    // Obtener lista de actividades desde la base de datos (igual que en evento_registrar.php)
    $stmt_tipos = $pdo->query("SELECT nombre FROM tipos_actividad ORDER BY nombre ASC");
    $actividades_fijas = $stmt_tipos->fetchAll(PDO::FETCH_COLUMN);

    // Inicializar matriz con 0 para asegurar que aparezcan siempre
    foreach ($actividades_fijas as $nombre) {
        $data_actividades[$nombre] = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 'total' => 0];
    }

    $sql_actividades = "SELECT t.nombre as actividad, 
                               FLOOR((DAYOFMONTH(s.fecha_registro)-1)/7)+1 as semana, 
                               COUNT(*) as total 
                        FROM servicios s
                        JOIN tipos_actividad t ON s.tipo_actividad_id = t.id
                        WHERE YEAR(s.fecha_registro) = ? AND MONTH(s.fecha_registro) = ? AND s.eliminado = 0
                        GROUP BY t.nombre, semana
                        ORDER BY total DESC";
    
    $stmt = $pdo->prepare($sql_actividades);
    $stmt->execute([$anio, $mes]);
    $raw_actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Procesar datos para la tabla matriz
    foreach ($raw_actividades as $row) {
        $act = trim($row['actividad']);
        $sem = $row['semana'];
        
        if (!isset($data_actividades[$act])) {
            $data_actividades[$act] = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 'total' => 0];
        }
        
        if ($sem >= 1 && $sem <= 5) {
            $data_actividades[$act][$sem] += $row['total'];
        }
        $data_actividades[$act]['total'] += $row['total'];
    }
    
    // No ordenamos para mantener el orden de la lista fija solicitada

    // ---------------------------------------------------------
    // 1.1 CONSULTA: Total de Actividades por Tipo (Categoría General)
    // ---------------------------------------------------------
    $cats_fijas = ['Soporte Operacional', 'Emergencias Prehospitalarias', 'Gestión de Riesgos', 'Capacitaciones', '0-800- Miranda'];
    
    // Inicializar matriz de categorías con semanas
    $data_categorias = [];
    foreach ($cats_fijas as $cat) {
        $data_categorias[$cat] = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 'total' => 0];
    }

    $sql_cats = "SELECT categoria, 
                        FLOOR((DAYOFMONTH(fecha_registro)-1)/7)+1 as semana, 
                        COUNT(*) as total 
                 FROM servicios 
                 WHERE YEAR(fecha_registro) = ? AND MONTH(fecha_registro) = ? AND eliminado = 0
                 GROUP BY categoria, semana";
    $stmt = $pdo->prepare($sql_cats);
    $stmt->execute([$anio, $mes]);
    $raw_cats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Procesar datos para la matriz
    foreach ($raw_cats as $row) {
        $cat = trim($row['categoria']);
        $sem = $row['semana'];
        
        if (isset($data_categorias[$cat])) {
            if ($sem >= 1 && $sem <= 5) {
                $data_categorias[$cat][$sem] += $row['total'];
            }
            $data_categorias[$cat]['total'] += $row['total'];
        }
    }

    // Preparar datos para el gráfico (extraídos de la matriz procesada)
    $labels_cats = $cats_fijas;
    $data_cats = [];
    foreach($cats_fijas as $cat) {
        $data_cats[] = $data_categorias[$cat]['total'];
    }

    // ---------------------------------------------------------
    // 2. CONSULTA: Evolución Diaria (Adaptación del Gráfico de Inicio)
    // ---------------------------------------------------------
    $sql_diario = "SELECT DAY(fecha_registro) as dia, COUNT(*) as total 
                   FROM servicios 
                   WHERE YEAR(fecha_registro) = ? AND MONTH(fecha_registro) = ? AND eliminado = 0
                   GROUP BY dia 
                   ORDER BY dia ASC";
    $stmt = $pdo->prepare($sql_diario);
    $stmt->execute([$anio, $mes]);
    $raw_diario = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Retorna array [dia => total]

    // Rellenar todos los días del mes (1 al 30/31)
    $dias_en_mes = date('t', mktime(0, 0, 0, $mes, 1, $anio));
    for($d=1; $d<=$dias_en_mes; $d++) {
        $labels_dias[] = $d;
        $data_dias[] = $raw_diario[$d] ?? 0;
    }

    // ---------------------------------------------------------
    // 3. CONSULTA: Cuadrantes de Paz
    // ---------------------------------------------------------
    // Definición de Cuadrantes Fijos (Lista estática solicitada)
    $cuadrantes_fijos = [
        'P-01' => 'Los Naranjos, Los Pomelos y Adyacencias.',
        'P-02' => 'Los Geranios, El Cigarral, La Boyera.',
        'P-03' => 'Alto Hatillo, El Calvario.',
        'P-04' => 'Pueblo del Hatillo, Las Marias.',
        'P-05' => 'La Lagunita, Lomas de la Lagunita.',
        'P-06' => 'La Union, Los Robles, Corralito.',
        'P-07' => 'El Encantado, La Guairita.',
        'PR-01' => 'Caicaguana, Colinas de Caicaguana.',
        'PR-02' => 'Oripoto, Gavilan, Loma Larga.',
        'PR-03' => 'Turgua, Sabaneta.',
        'Otros' => 'Fuera de juridiccion'
    ];

    $sql_cuadrantes = "SELECT cuadrante, COUNT(*) as total 
                       FROM servicios 
                       WHERE YEAR(fecha_registro) = ? AND MONTH(fecha_registro) = ? AND eliminado = 0
                       GROUP BY cuadrante";
    $stmt = $pdo->prepare($sql_cuadrantes);
    $stmt->execute([$anio, $mes]);
    $raw_cuadrantes_db = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Retorna [ 'P-01' => 5, ... ]

    // Normalizar claves de BD (Mayúsculas y sin espacios extra) para comparar
    $db_data_norm = [];
    foreach($raw_cuadrantes_db as $k => $v) {
        $db_data_norm[strtoupper(trim($k))] = $v;
    }

    $tabla_cuadrantes = [];
    foreach ($cuadrantes_fijos as $code => $desc) {
        $code_norm = strtoupper($code);
        $count = 0;

        // Buscar coincidencia exacta
        if (isset($db_data_norm[$code_norm])) {
            $count = $db_data_norm[$code_norm];
            unset($db_data_norm[$code_norm]); // Lo quitamos para no sumarlo dos veces
        }

        // Si es la fila de 'Otros', sumamos todo lo que sobró (lo que no coincidió con P-01...PR-03)
        if ($code === 'Otros') {
            $count += array_sum($db_data_norm);
        }

        $labels_cuad[] = $code;
        $data_cuad[] = $count;
        // Guardamos datos para la tabla
        $tabla_cuadrantes[] = ['codigo' => $code, 'descripcion' => $desc, 'total' => $count];
    }

    // --- CÁLCULOS PARA RESUMEN EJECUTIVO AUTOMÁTICO ---
    foreach($data_actividades as $nombre => $datos) {
        // Asegurar que 'total' existe y es numérico
        $val = isset($datos['total']) ? $datos['total'] : 0;
        $total_servicios_mes += $val;
        if($val > $max_act_val) {
            $max_act_val = $val;
            $max_act_nombre = $nombre;
        }
    }

    foreach($tabla_cuadrantes as $fila) {
        if($fila['codigo'] !== 'Otros' && $fila['total'] > $max_cuad_val) {
            $max_cuad_val = $fila['total'];
            $max_cuad_nombre = $fila['codigo'];
        }
    }

} catch (Exception $e) {
    $error_msg = "Error al cargar estadísticas: " . $e->getMessage();
}
?>

<!-- Estilos para impresión -->
<style>
    @media print {
        .no-print, .main-footer, .navbar, .main-sidebar { display: none !important; }
        .content-wrapper { margin-left: 0 !important; background: white; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; break-inside: avoid; }
        .col-md-6 { width: 50% !important; float: left; }
        .col-md-4 { width: 33% !important; float: left; }
    }
</style>

<!-- Estilos para impresión -->
<style>
    @media print {
        .no-print, .main-footer, .navbar, .main-sidebar, .btn { display: none !important; }
        .content-wrapper { margin-left: 0 !important; background: white !important; padding: 0 !important; }
        
        /* Ocultar Gráficos y sus columnas contenedoras */
        canvas, .col-md-5 { display: none !important; }
        
        /* Expandir Tablas al 100% */
        .col-md-7 { width: 100% !important; flex: 0 0 100% !important; max-width: 100% !important; }
        
        /* Estilo de Reporte Formal */
        .card { 
            box-shadow: none !important; 
            border: none !important; 
            margin-bottom: 20px !important;
            page-break-inside: avoid;
        }
        .card-header { 
            background-color: transparent !important; 
            border-bottom: 2px solid #000 !important; 
            padding-left: 0; padding-right: 0;
            color: #000 !important;
        }
        .card-title { font-weight: bold; font-size: 1.2rem; }
        
        /* Tablas en Blanco y Negro */
        .table { width: 100% !important; border-collapse: collapse !important; }
        .table th, .table td { border: 1px solid #000 !important; color: #000 !important; }
        .table thead th { background-color: #f0f0f0 !important; -webkit-print-color-adjust: exact; }
        .bg-light { background-color: transparent !important; font-weight: bold; }

        /* Mostrar elementos ocultos en pantalla */
        .d-print-block { display: block !important; }
        
        /* Sección de Firmas */
        .firma-section { display: flex !important; margin-top: 80px; justify-content: space-between; }
        .firma-box { width: 40%; border-top: 1px solid #000; text-align: center; padding-top: 10px; }
    }
</style>

<div class="content-wrapper">
    <!-- Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-<? echo $color; ?>">
                        <img src="./dist/img/logo2.png" width="50" class="mr-2"> Estadísticas
                    </h1> 
                </div>
                <div class="col-sm-6">
                    <form method="GET" class="form-inline float-right">
                        <label class="mr-2">Periodo:</label>
                        <select name="mes" class="form-control mr-2">
                            <?php foreach($meses as $num => $nombre): ?>
                                <option value="<?= $num ?>" <?= ($num == $mes) ? 'selected' : '' ?>><?= $nombre ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="anio" class="form-control mr-2">
                            <?php for($y = date('Y'); $y >= 2020; $y--): ?> 
                                <option value="<?= $y ?>" <?= ($y == $anio) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                        <button type="button" onclick="window.print()" class="btn btn-secondary ml-2"><i class="fas fa-print"></i> Imprimir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            
            <?php if($error_msg): ?>
                <div class="alert alert-danger"><?= $error_msg ?></div>
            <?php endif; ?> 

            <!-- RESUMEN EJECUTIVO (SOLO IMPRESIÓN) -->
            <div class="d-none d-print-block mb-4">
                <div class="row align-items-center mb-4 border-bottom pb-3">
                    <div class="col-2 text-center">
                        <img src="./dist/img/logo2.png" style="width: 80px;">
                    </div>
                    <div class="col-8 text-center">
                        <h4 class="font-weight-bold text-uppercase m-0">Alcaldía El Hatillo</h4>
                        <h5 class="m-0 text-muted">Dirección de Protección Civil y Administración de Desastres</h5>
                    </div>
                    <div class="col-2 text-center">
                        <img src="./dist/img/logo_nuevo.png" style="width: 80px;">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4 class="text-center font-weight-bold text-uppercase mb-4" style="text-decoration: underline;">Informe de Gestión Operativa</h4>
                        <p class="text-justify" style="font-size: 1.1rem; line-height: 1.6;">
                            Durante el periodo correspondiente al mes de <strong><?= $meses[(int)$mes] ?> del año <?= $anio ?></strong>, la Dirección Municipal de Protección Civil y Administración de Desastres El Hatillo registró una operatividad total de <strong><?= $total_servicios_mes ?> servicios</strong> atendidos.
                            <br><br>
                            El análisis estadístico destaca que la actividad con mayor incidencia operativa fue <strong>"<?= $max_act_nombre ?>"</strong>, contabilizando un total de <?= $max_act_val ?> eventos. Asimismo, en la distribución geoespacial, el cuadrante de paz con mayor demanda de atención fue el <strong><?= $max_cuad_nombre ?></strong> con <?= $max_cuad_val ?> servicios reportados.
                        </p>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12 text-center">
                    <h3>Reporte Estadístico - <?= $meses[(int)$mes] ?> <?= $anio ?></h3>
                    <p class="text-muted">Dirección Municipal de Protección Civil y Administración de Desastres El Hatillo</p>
                </div>
            </div>

            <!-- GRÁFICO 1: EVOLUCIÓN DIARIA (Adaptado del Inicio) --> 
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Evolución Diaria de Servicios (Mes Completo)</h3>
                </div>
                <div class="card-body">
                    <canvas id="chartDiario" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    <p class="text-center text-muted small mt-2">Muestra la cantidad de servicios atendidos por día durante el mes seleccionado.</p>
                    
                    <!-- TABLA DE DATOS DIARIOS (SOLO IMPRESIÓN) -->
                    <div class="d-none d-print-block mt-3">
                        <table class="table table-sm table-bordered text-center" style="font-size: 0.9rem;">
                            <thead>
                                <tr><th colspan="16" class="bg-light">Primera Quincena</th></tr>
                                <tr><?php for($i=1; $i<=15; $i++) echo "<th>$i</th>"; ?><th>Total</th></tr>
                            </thead>
                            <tbody>
                                <tr><?php $t1=0; for($i=1; $i<=15; $i++) { echo "<td>".($data_dias[$i-1] ?? 0)."</td>"; $t1+=($data_dias[$i-1] ?? 0); } echo "<td><strong>$t1</strong></td>"; ?></tr>
                            </tbody>
                        </table>
                        <!-- Segunda Tabla independiente para ajustar columnas correctamente -->
                        <table class="table table-sm table-bordered text-center mt-3" style="font-size: 0.9rem;">
                            <thead>
                                <tr><th colspan="17" class="bg-light">Segunda Quincena</th></tr>
                                <tr><?php for($i=16; $i<=31; $i++) echo "<th>$i</th>"; ?><th>Total</th></tr>
                            </thead>
                            <tbody>
                                <tr><?php $t2=0; for($i=16; $i<=31; $i++) { $val = ($i <= count($data_dias)) ? ($data_dias[$i-1] ?? 0) : '-'; echo "<td>$val</td>"; if(is_numeric($val)) $t2+=$val; } echo "<td><strong>$t2</strong></td>"; ?></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- NUEVA SECCIÓN: TOTAL DE ACTIVIDADES POR TIPO (CATEGORÍA) -->
            <div class="card card-danger card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Total de Actividades por Tipo</h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Tabla de Datos Categorías (Izquierda) -->
                        <div class="col-md-7"> 
                            <table class="table table-bordered table-sm table-striped text-center">
                                <thead class="bg-danger text-white">
                                    <tr>
                                        <th class="text-left">CATEGORÍA</th>
                                        <th>Sem 1</th>
                                        <th>Sem 2</th>
                                        <th>Sem 3</th>
                                        <th>Sem 4</th>
                                        <th>Sem 5</th>
                                        <th>TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody> 
                                    <?php 
                                    $gran_total_cat = 0;
                                    foreach($data_categorias as $nombre => $semanas): 
                                        $gran_total_cat += $semanas['total'];
                                    ?> 
                                    <tr>
                                        <td class="text-left font-weight-bold"><?= $nombre ?></td>
                                        <td><?= $semanas[1] ?></td>
                                        <td><?= $semanas[2] ?></td>
                                        <td><?= $semanas[3] ?></td>
                                        <td><?= $semanas[4] ?></td>
                                        <td><?= $semanas[5] ?></td>
                                        <td class="bg-light font-weight-bold"><?= $semanas['total'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="bg-secondary">
                                        <td class="text-left font-weight-bold">TOTAL GENERAL</td> 
                                        <td colspan="5"></td>
                                        <td class="font-weight-bold"><?= $gran_total_cat ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Gráfico (Derecha) -->
                        <div class="col-md-5">
                            <canvas id="chartCats" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            <p class="text-center font-weight-bold mt-3 text-muted">Distribución General</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: RESUMEN DE ACTIVIDADES (Tabla y Gráficos de Tipos) --> 
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list mr-1"></i> Distribución porcentual de actividades de respuesta operacional por tipo</h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Tabla de Datos -->
                        <div class="col-md-7"> 
                            <table class="table table-bordered table-sm table-striped text-center">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th class="text-left">ACTIVIDADES</th>
                                        <th>Sem 1</th>
                                        <th>Sem 2</th>
                                        <th>Sem 3</th>
                                        <th>Sem 4</th>
                                        <th>Sem 5</th>
                                        <th>TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody> 
                                    <?php 
                                    $gran_total = 0;
                                    if (empty($data_actividades)) {
                                        echo "<tr><td colspan='7'>No hay datos registrados para este periodo.</td></tr>";
                                    } else {
                                        foreach($data_actividades as $nombre => $semanas): 
                                            $gran_total += $semanas['total'];
                                        ?> 
                                        <tr>
                                            <td class="text-left font-weight-bold"><?= $nombre ?></td>
                                            <td><?= $semanas[1] ?></td>
                                            <td><?= $semanas[2] ?></td>
                                            <td><?= $semanas[3] ?></td>
                                            <td><?= $semanas[4] ?></td>
                                            <td><?= $semanas[5] ?></td>
                                            <td class="bg-light font-weight-bold"><?= $semanas['total'] ?></td>
                                        </tr>
                                        <?php endforeach; 
                                    }
                                    ?>
                                    <tr class="bg-secondary">
                                        <td class="text-left font-weight-bold">TOTAL GENERAL</td> 
                                        <td colspan="5"></td>
                                        <td class="font-weight-bold"><?= $gran_total ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Gráfico (Derecha) --> 
                        <div class="col-md-5">
                            <canvas id="chartPieAct" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            <p class="text-center font-weight-bold mt-3 text-muted">Distribución por Tipo</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 3: CUADRANTES --> 
            <div class="card card-success card-outline mt-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map-marked-alt mr-1"></i> Distribución por Cuadrantes de Paz</h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <table class="table table-bordered table-striped table-sm text-center">
                                <thead class="bg-success text-white"> 
                                    <tr>
                                        <th>Cuadrante</th>
                                        <th>Descripción</th>
                                        <th class="text-center">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($tabla_cuadrantes as $fila): ?>
                                        <tr>
                                            <td class="font-weight-bold"><?= $fila['codigo'] ?></td>
                                            <td><?= $fila['descripcion'] ?></td>
                                            <td class="text-center font-weight-bold"><?= $fila['total'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-5">
                            <canvas id="chartCuadrantes" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            <p class="text-center font-weight-bold mt-3 text-muted">Distribución Geográfica</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN DE FIRMAS (SOLO IMPRESIÓN) -->
            <div class="d-none d-print-block firma-section">
                <div class="firma-box">
                    <strong>Elaborado por:</strong><br><br><br>
                    __________________________<br>
                    Funcionario de Guardia
                </div>
                <div class="firma-box">
                    <strong>Revisado por:</strong><br><br><br>
                    __________________________<br>
                    Dirección / Coordinación
                </div>
            </div>

        </div>
    </section>
</div>

<?php include_once(realpath(dirname(__FILE__)) . "/include/footer.php"); ?>

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>

<!-- ChartJS -->
<script src="plugins/chart.js/Chart.min.js"></script>

<script>
$(function () {
    // --- DATOS PHP A JS --- 
    var labelsAct = <?php echo json_encode(array_keys($data_actividades)); ?>;
    var dataActTotal = <?php echo json_encode(array_column($data_actividades, 'total')); ?>;
    
    var labelsDias = <?php echo json_encode($labels_dias); ?>;
    var dataDias = <?php echo json_encode($data_dias); ?>;
 
    var labelsCats = <?php echo json_encode($labels_cats); ?>;
    var dataCats = <?php echo json_encode($data_cats); ?>;

    var labelsCuad = <?php echo json_encode($labels_cuad); ?>;
    var dataCuad = <?php echo json_encode($data_cuad); ?>;

    // --- MENSAJES DE DEPURACIÓN: Revisa la consola del navegador (F12) para ver estos datos ---
    console.log("Datos para Gráfico Diario:", { labels: labelsDias, data: dataDias });
    console.log("Datos para Gráficos de Actividades:", { labels: labelsAct, data: dataActTotal });
    console.log("Datos para Gráfico de Cuadrantes:", { labels: labelsCuad, data: dataCuad });
    // --- FIN DE MENSAJES DE DEPURACIÓN ---

    var colors = ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de', '#605ca8', '#ff851b', '#39cccc', '#001f3f'];

    // --- 1. GRÁFICO DE EVOLUCIÓN DIARIA (El que estaba en el inicio) ---
    if (labelsDias.length > 0) {
        var ctxDiario = document.getElementById('chartDiario').getContext('2d');
        new Chart(ctxDiario, { 
            type: 'line',
            data: {
                labels: labelsDias,
                datasets: [{
                    label: 'Servicios',
                    backgroundColor: 'rgba(60,141,188,0.2)',
                    borderColor: '#3c8dbc',
                    pointRadius: 3,
                    pointBackgroundColor: '#3c8dbc',
                    pointBorderColor: '#3c8dbc',
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#3c8dbc',
                    pointHoverBorderColor: '#3c8dbc',
                    data: dataDias,
                    fill: true
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                legend: { display: false },
                scales: {
                    xAxes: [{ gridLines: { display: false } }],
                    yAxes: [{ ticks: { beginAtZero: true, stepSize: 1 }, gridLines: { display: true } }]
                }
            }
        });
    }

    // --- 1.1 GRÁFICO DE CATEGORÍAS (NUEVO) ---
    if (labelsCats.length > 0) {
        var ctxCats = document.getElementById('chartCats').getContext('2d');
        new Chart(ctxCats, {
            type: 'doughnut', // Gráfico de Dona (Moderno)
            data: {
                labels: labelsCats,
                datasets: [{
                    data: dataCats,
                    backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc'], // Colores variados
                    borderWidth: 4, // Bordes más gruesos para efecto moderno
                    hoverOffset: 10
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                legend: { display: false }, // Leyenda oculta para mejor visualización
                cutoutPercentage: 60 // Dona más fina
            }
        });
    }

    // --- 2. GRÁFICOS DE TIPOS (Torta y Barra) --- 
    if (labelsAct.length > 0) {
        // Torta
        var ctxPieAct = document.getElementById('chartPieAct').getContext('2d');
        new Chart(ctxPieAct, {
            type: 'doughnut',
            data: {
                labels: labelsAct,
                datasets: [{
                    data: dataActTotal,
                    backgroundColor: colors
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                legend: { display: false }
            }
        });
    }

    // --- 3. GRÁFICO DE CUADRANTES --- 
    if (labelsCuad.length > 0) {
        var ctxCuadrantes = document.getElementById('chartCuadrantes').getContext('2d');
        new Chart(ctxCuadrantes, {
            type: 'bar', // Cambiado a Barras (Gráfica Lineal)
            data: {
                labels: labelsCuad,
                datasets: [{
                    label: 'Servicios',
                    data: dataCuad,
                    backgroundColor: colors
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true, stepSize: 1 } }]
                },
                legend: { display: false }
            }
        });
    }
});
</script>
