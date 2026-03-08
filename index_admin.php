<?php
$modulo = 'index';
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

// Inicializar variables para el dashboard
$total_servicios = 0;
$servicios_hoy = 0;
$total_funcionarios = 0;
$eventos_hoy = [];
$chart_labels = [];
$chart_values = [];
$pie_labels = [];
$pie_values = [];
$estatus_db = [];

if (in_array($_SESSION['usuario']['rol_id'], [1, 2, 3, 5, 6])) {
    try {
        // 1. Total Servicios
        $stmt = $pdo->query("SELECT COUNT(*) FROM servicios");
        $total_servicios = $stmt->fetchColumn();

        // 2. Servicios Hoy
        $stmt = $pdo->query("SELECT COUNT(*) FROM servicios WHERE DATE(fecha_registro) = CURDATE()");
        $servicios_hoy = $stmt->fetchColumn();

        // 3. Funcionarios Activos
        $stmt = $pdo->query("SELECT COUNT(*) FROM personal WHERE activo = 1");
        $total_funcionarios = $stmt->fetchColumn();

        // 4. Lista de Eventos de Hoy
        $sql_hoy = "SELECT s.id, s.nro_servicio, s.hora_inicio, s.estatus_id, 
                           t.nombre as tipo_nombre, t.abreviacion, e.nombre as estatus_nombre
                    FROM servicios s 
                    LEFT JOIN tipos_actividad t ON s.tipo_actividad_id = t.id 
                    LEFT JOIN estatus e ON s.estatus_id = e.id
                    WHERE DATE(s.fecha_registro) = CURDATE() 
                    ORDER BY s.id DESC";
        $stmt = $pdo->query($sql_hoy);
        $eventos_hoy = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 7. Datos para Estatus
        $sql_estatus = "SELECT e.nombre, COUNT(s.id) as total 
                        FROM servicios s 
                        JOIN estatus e ON s.estatus_id = e.id 
                        WHERE s.eliminado = 0 
                        GROUP BY e.nombre";
        $stmt = $pdo->query($sql_estatus);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $estatus_db[strtoupper($row['nombre'])] = $row['total'];
        }

    } catch (Exception $e) {
        // Manejo silencioso de errores
    }
}

include_once(realpath(dirname(__FILE__)) . "/include/header.php");
?>

<head>
    <!-- ChartJS -->
    <script src="plugins/chart.js/Chart.min.js"></script>
    <style>
        .content-wrapper {
            background-image: url('dist/img/bg.jpg'); /* Verifica que este sea el nombre correcto de tu imagen */
            background-size: cover;       /* Escala la imagen para cubrir todo el fondo */
            background-position: center;  /* Centra la imagen */
            background-repeat: no-repeat; /* Evita que se repita */
            background-attachment: fixed; /* Mantiene la imagen fija al hacer scroll */
            min-height: 100vh;            /* Fuerza al contenedor a ocupar al menos el 100% de la altura de la pantalla */
        }
        /* Corrección para móviles: background-attachment: fixed suele fallar en iOS/Android */
        @media (max-width: 768px) {
            .content-wrapper {
                background-attachment: scroll;
                background-position: center top;
            }
        }
    </style>
</head>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-<? echo $color; ?>">
                        <img src="./dist/img/logo2.png" width="55">
                        <? echo NOMBRE_SITE; ?>
                    </h1>
                </div><!-- /.col -->

            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Small Boxes (Tarjetas Superiores) -->
            <div class="row">
                <!-- Total Servicios (Principal Izquierda) -->
                <div class="col-lg-3 col-12">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $total_servicios; ?></h3>
                            <p>Total Servicios</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <a href="eventos_listado.php" class="small-box-footer">Ver Todos <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <!-- Estatus (Centro y Derecha) -->
                <div class="col-lg-9 col-12">
                    <div class="row">
                        <!-- Abierto -->
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3><?php echo $estatus_db['ABIERTO'] ?? 0; ?></h3>
                                    <p>Abierto</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <a href="eventos_listado.php" class="small-box-footer">Más Información <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                        <!-- En Proceso -->
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3><?php echo $estatus_db['EN PROCESO'] ?? 0; ?></h3>
                                    <p>En Proceso</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-spinner"></i>
                                </div>
                                <a href="eventos_listado.php" class="small-box-footer">Más Información <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                        <!-- Culminado -->
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3><?php echo $estatus_db['CULMINADO'] ?? 0; ?></h3>
                                    <p>Culminado</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <a href="eventos_listado.php" class="small-box-footer">Más Información <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                        <!-- Cerrado -->
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-secondary">
                                <div class="inner">
                                    <h3><?php echo $estatus_db['CERRADO'] ?? 0; ?></h3>
                                    <p>Cerrado</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-archive"></i>
                                </div>
                                <a href="eventos_listado.php" class="small-box-footer">Más Información <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Columna Izquierda: Tabla de Eventos de Hoy -->
                <div class="col-lg-9 col-12">
                    <div class="card card-<? echo $color; ?>">
                        <div class="card-header border-0">
                            <h3 class="card-title">Eventos de Hoy</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-striped table-valign-middle">
                                <thead>
                                <tr>
                                    <th>Nro</th>
                                    <th>Hora</th>
                                    <th>Tipo</th>
                                    <th>Estatus</th>
                                    <th>Ver</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if($eventos_hoy): ?>
                                    <?php foreach($eventos_hoy as $evt): ?>
                                    <tr>
                                        <td><?php echo $evt['nro_servicio']; ?></td>
                                        <td><?php echo $evt['hora_inicio'] ?? '--:--'; ?></td>
                                        <td><span class="badge bg-danger"><?php echo $evt['abreviacion'] ?? 'N/A'; ?></span> <?php echo $evt['tipo_nombre'] ?? ''; ?></td>
                                        <td>
                                            <?php 
                                            $est_nombre = strtoupper($evt['estatus_nombre'] ?? '');
                                            $est_color = 'bg-secondary';
                                            if($est_nombre == 'ABIERTO') $est_color = 'bg-danger';
                                            elseif($est_nombre == 'EN PROCESO') $est_color = 'bg-warning';
                                            elseif($est_nombre == 'CULMINADO') $est_color = 'bg-success';
                                            ?>
                                            <span class="badge <?php echo $est_color; ?>"><?php echo $evt['estatus_nombre'] ?? 'Activo'; ?></span>
                                        </td>
                                        <td>
                                            <a href="detalle.php?id=<?php echo $evt['id']; ?>" class="text-muted">
                                                <i class="fas fa-search"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center">No hay eventos registrados hoy.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Buscador y Gráfico -->
                <div class="col-lg-3 col-12">
                    <!-- Buscador -->
                    <div class="card card-<? echo $color; ?> mb-3">
                        <div class="card-body p-2">
                            <form action="ver_servicios.php" method="GET">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="buscar" class="form-control" placeholder="Buscar Evento...">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-<? echo $color; ?>"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Acciones Guardia -->
                    <?php if (in_array($_SESSION['usuario']['rol_id'], [1, 3, 5, 6])): ?>
                    <div class="card card-<? echo $color; ?> mb-3">
                        <div class="card-header">
                            <h5 class="card-title m-0">Acciones Rápidas</h5>
                        </div>
                        <div class="card-body">
                            <a href="evento_registrar.php" class="btn btn-danger btn-block mb-2">
                                <i class="fas fa-bullhorn mr-2"></i> Registrar Novedad
                            </a>
                            <?php if ($_SESSION['usuario']['rol_id'] != 6): // Ocultar a Paramédicos ?>
                            <a href="guardia_conductor.php" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-ambulance mr-2"></i> Entrega Conductor
                            </a>
                            <?php endif; ?>
                            <?php if ($_SESSION['usuario']['rol_id'] != 5): // Ocultar a Conductores ?>
                            <a href="guardia_paramedico.php" class="btn btn-success btn-block">
                                <i class="fas fa-user-md mr-2"></i> Entrega Paramédico
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
    </section>
</div>


<?php
include_once(realpath(dirname(__FILE__)) . "/include/footer.php");
?>
<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="./plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="./plugins/datatables/jquery.dataTables.min.js"></script>
<script src="./plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="./plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="./plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<!-- AdminLTE App -->
<script src="./dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="./dist/js/demo.js"></script>

<script>
    $(function () {
        $("#example1").DataTable({
            "responsive": true,
            "autoWidth": false,
            "ordering": true,
            "lengthMenu": [
                [12, 25, 50, -1],
                [12, 25, 50, "Todos"]
            ],
            "order": [
                [4, "desc"]
            ],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            }
        });

        $('#example2').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "lengthMenu": [
                [20, 30, 50, -1],
                [10, 25, 50, "Todos"]
            ]
        });
    });
</script>

<script>
function getLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(showPosition, showError);
  } else {
    alert("Geolocation is not supported by this browser.");
  }
}

function showPosition(position) {
  const latitude = position.coords.latitude;
  const longitude = position.coords.longitude;
  const googleMapsUrl = `https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}`;
  window.location.href = googleMapsUrl;
}

function showError(error) {
  switch(error.code) {
    case error.PERMISSION_DENIED:
      alert("User denied the request for Geolocation.");
      break;
  }
}
</script>

</body>

</html>