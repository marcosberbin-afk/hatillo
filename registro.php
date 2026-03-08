<?php 
session_start();
$modulo = null;

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }
include 'conexion.php'; 

include_once(realpath(dirname(__FILE__)) . "/include/header.php");
?>

<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-header bg-danger text-white"><h3>Registrar Novedad Relevante</h3></div>
        <div class="card-body">
            <form action="procesar.php" method="POST">
                <div class="row mb-3">
                    <div class="col-md-4"><label>Nro Servicio:</label><input type="text" name="nro_servicio" class="form-control" required></div>
                    <div class="col-md-4"><label>Fecha:</label><input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="col-md-4">
                        <label>Actividad:</label>
                        <select name="tipo_actividad_id" class="form-select">
                            <?php $stmt = $pdo->query("SELECT id, abreviacion, nombre FROM tipos_actividad");
                            while ($row = $stmt->fetch()) { echo "<option value='{$row['id']}'>{$row['abreviacion']} - {$row['nombre']}</option>"; } ?>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><label>Parroquia:</label><input type="text" name="parroquia" class="form-control" value="El Hatillo"></div>
                    <div class="col-md-6"><label>Cuadrante:</label><input type="text" name="cuadrante" class="form-control"></div>
                </div>
                <div class="mb-3"><label>Dirección:</label><textarea name="direccion" class="form-control" rows="2"></textarea></div>
                <div class="mb-3"><label>Resumen:</label><textarea name="resumen" class="form-control" rows="5"></textarea></div>

                <h4 class="mt-4">Actuantes</h4>
                <div id="contenedor-actuantes">
                    <div class="row mb-2 actuante-fila">
                        <div class="col-md-6">
                            <select name="personal_id[]" class="form-select">
                                <option value="">Seleccione Funcionario...</option>
                                <?php $pers = $pdo->query("SELECT id, nombre_completo FROM personal");
                                while ($p = $pers->fetch()) { echo "<option value='{$p['id']}'>{$p['nombre_completo']}</option>"; } ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="rol[]" class="form-select">
                                <option value="Conductor">Conductor</option>
                                <option value="Asistente">Asistente</option>
                                <option value="Supervisor">Supervisor</option>
                            </select>
                        </div>
                        <div class="col-md-2"><button type="button" class="btn btn-danger w-100" onclick="this.closest('.row').remove()">X</button></div>
                    </div>
                </div>
                <button type="button" class="btn btn-success btn-sm mt-2" onclick="agregarFila()">+ Añadir Personal</button>
                <hr>
                <button type="submit" class="btn btn-primary w-100 p-3">GUARDAR SERVICIO</button>
            </form>
        </div>
    </div>
</div>
<script>
function agregarFila() {
    const div = document.querySelector('.actuante-fila').cloneNode(true);
    div.querySelector('select').value = "";
    document.getElementById('contenedor-actuantes').appendChild(div);
}
</script>
<?
include_once(realpath(dirname(__FILE__)) . "/include/footer.php");