<?php
$modulo = 'inventario';
$menu_abierto = 'inventario';
$modulo_titulo = 'Operaciones de Inventario';

include_once(realpath(dirname(__FILE__)) . "/include/header.php");

// Asegurar que la tabla permita NULL en origen para compras (Corrección automática de BD)
mysqli_query($_DB_->connection, "ALTER TABLE `inv_movimientos` MODIFY `id_almacen_origen` INT NULL");

$inventario_obj = new Inventario();
$almacenes = $inventario_obj->getAlmacenes($_DB_);
$productos_lista = $inventario_obj->getProductos($_DB_); // Cargar productos para el select
$mensaje = '';
$active_tab = 'transferencia'; // Pestaña activa por defecto

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'transferir') {
        $active_tab = 'transferencia';
        $id_lote = $_POST['id_lote'];
        $id_almacen_origen = $_POST['id_almacen_origen'];
        $id_almacen_destino = $_POST['id_almacen_destino'];
        $cantidad = $_POST['cantidad'];
        $id_usuario = $_SESSION['usuario']['id'] ?? 1;

        if ($inventario_obj->transferirStock($_DB_, $id_lote, $id_almacen_origen, $id_almacen_destino, $cantidad, $id_usuario)) {
            $mensaje = '<div class="alert alert-success">Transferencia realizada exitosamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger">Error en la transferencia.</div>';
        }
    } elseif ($_POST['action'] == 'entrada') {
        $active_tab = 'entrada';
        // Lógica para Nueva Entrada
        $id_producto = $_POST['id_producto'] ?? '';
        $id_almacen = $_POST['id_almacen_entrada'] ?? '';
        $numero_lote = $_POST['numero_lote'] ?? '';
        $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';
        $cantidad = $_POST['cantidad_entrada'] ?? '';
        $id_usuario = $_SESSION['usuario']['id'] ?? 1; // Corregido: id en lugar de id_usuario

        if (empty($id_producto) || empty($id_almacen) || empty($numero_lote) || empty($fecha_vencimiento) || empty($cantidad)) {
            $mensaje = '<div class="alert alert-warning">Por favor, complete todos los campos obligatorios.</div>';
        } elseif ($inventario_obj->registrarEntrada($_DB_, $id_producto, $id_almacen, $numero_lote, $fecha_vencimiento, $cantidad, $id_usuario)) {
             $mensaje = '<div class="alert alert-success">Entrada de stock registrada correctamente.</div>';
        } else {
             $mensaje = '<div class="alert alert-danger">Error al registrar la entrada. Verifique los datos e intente nuevamente.</div>';
        }
    }
}

$stock_origen = [];
if (isset($_GET['id_almacen_origen'])) {
    $id_almacen_origen = $_GET['id_almacen_origen'];
    $stock_origen = $inventario_obj->getStock($_DB_, $id_almacen_origen);
}

?>

<!-- Select2 -->
<link rel="stylesheet" href="./plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="./plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-12">
					<h1 class="m-0 text-<? echo $color; ?>"><img src="./dist/img/logo2.png" width="55">
						<? echo $modulo_titulo; ?> </h1>
				</div>
			</div>
		</div>
	</div>

	<section class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-12">
					<?php echo $mensaje; ?>
					
                    <div class="card card-primary card-tabs">
						<div class="card-header">
                            <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_tab == 'transferencia') ? 'active' : ''; ?>" id="tab-transferencia" data-toggle="pill" href="#content-transferencia" role="tab">Transferencia entre Almacenes</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_tab == 'entrada') ? 'active' : ''; ?>" id="tab-entrada" data-toggle="pill" href="#content-entrada" role="tab">Registrar Entrada (Compra/Dotación)</a>
                                </li>
                            </ul>
						</div>
						<div class="card-body">
                            <div class="tab-content">
                                <!-- TAB TRANSFERENCIA -->
                                <div class="tab-pane fade <?php echo ($active_tab == 'transferencia') ? 'show active' : ''; ?>" id="content-transferencia" role="tabpanel">
                                    <form method="POST" action="">
                                        <input type="hidden" name="action" value="transferir">
                                        <div class="form-group">
                                            <label for="id_almacen_origen">Almacén Origen:</label>
                                            <select name="id_almacen_origen" id="id_almacen_origen" class="form-control select2" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($almacenes as $almacen): ?>
                                                    <option value="<?php echo $almacen['id_almacen']; ?>" <?php echo (isset($id_almacen_origen) && $id_almacen_origen == $almacen['id_almacen']) ? 'selected' : ''; ?>>
                                                        <?php echo $almacen['nombre'] . ' (' . $almacen['tipo'] . ')'; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="id_lote">Producto/Lote Disponible:</label>
                                            <select name="id_lote" id="id_lote" class="form-control select2" required>
                                                <option value="">
                                                    <?php 
                                                    if (empty($id_almacen_origen)) {
                                                        echo "Seleccione almacén origen primero";
                                                    } elseif (empty($stock_origen)) {
                                                        echo "No hay stock disponible en este almacén";
                                                    } else {
                                                        echo "Seleccione un producto...";
                                                    }
                                                    ?>
                                                </option>
                                                <?php if (!empty($stock_origen)): ?>
                                                    <?php foreach ($stock_origen as $item): ?>
                                                        <option value="<?php echo $item['id_lote']; ?>">
                                                            <?php echo $item['producto_nombre'] . ' - Lote: ' . $item['numero_lote'] . ' (Cant: ' . $item['cantidad'] . ')'; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="id_almacen_destino">Almacén Destino:</label>
                                            <select name="id_almacen_destino" id="id_almacen_destino" class="form-control select2" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($almacenes as $almacen): ?>
                                                    <option value="<?php echo $almacen['id_almacen']; ?>">
                                                        <?php echo $almacen['nombre'] . ' (' . $almacen['tipo'] . ')'; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="cantidad">Cantidad a Transferir:</label>
                                            <input type="number" name="cantidad" id="cantidad" class="form-control" required min="1">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Realizar Transferencia</button>
                                    </form>
                                </div>

                                <!-- TAB ENTRADA (NUEVO) -->
                                <div class="tab-pane fade <?php echo ($active_tab == 'entrada') ? 'show active' : ''; ?>" id="content-entrada" role="tabpanel">
                                    <form method="POST" action="" novalidate>
                                        <input type="hidden" name="action" value="entrada">
                                        <div class="form-group">
                                            <label>Producto:</label>
                                            <select name="id_producto" class="form-control select2" style="width: 100%;">
                                                <option value="">Buscar producto...</option>
                                                <?php foreach ($productos_lista as $prod): ?>
                                                    <option value="<?php echo $prod['id_producto']; ?>">
                                                        <?php echo $prod['nombre'] . ' (' . $prod['unidad_medida'] . ')'; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Número de Lote:</label>
                                                    <input type="text" name="numero_lote" class="form-control" required placeholder="Ej: L-2023-001">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Fecha de Vencimiento:</label>
                                                    <input type="date" name="fecha_vencimiento" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Almacén de Recepción:</label>
                                                    <select name="id_almacen_entrada" class="form-control select2">
                                                        <?php foreach ($almacenes as $almacen): ?>
                                                            <option value="<?php echo $almacen['id_almacen']; ?>">
                                                                <?php echo $almacen['nombre']; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Cantidad Recibida:</label>
                                                    <input type="number" name="cantidad_entrada" class="form-control" required min="1">
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success">Registrar Entrada</button>
                                    </form>
                                </div>
                            </div>
						</div>
					</div>
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
<!-- Select2 -->
<script src="./plugins/select2/js/select2.full.min.js"></script>

<script>
$(function () {
    // Inicializar Select2
    $('.select2').select2({ theme: 'bootstrap4' });

    // Detectar cambio en almacén origen (Compatible con Select2)
    $('#id_almacen_origen').on('change', function() {
        var id_almacen = $(this).val();
        if (id_almacen) {
            window.location.href = 'inventario_operacion.php?id_almacen_origen=' + id_almacen;
        }
    });
});
</script>