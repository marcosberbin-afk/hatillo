<?php
$modulo = 'evento';
$modulo_titulo = 'Módulo de Eventos';

include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

// Obtener listas para los selectores desde la base de datos
$tipos_actividad = [];
$estatus_list = [];
$unidades = [];
$personal_list = [];
$nro_sugerido = 1;

try {
    // Tipos de Actividad
    $stmt = $pdo->query("SELECT id, nombre FROM tipos_actividad ORDER BY nombre ASC");
    $tipos_actividad = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Estatus
    $stmt = $pdo->query("SELECT id, nombre FROM estatus ORDER BY nombre ASC");
    $estatus_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Unidades
    $stmt = $pdo->query("SELECT id, codigo, descripcion FROM unidades WHERE activo = 1 ORDER BY codigo ASC");
    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Personal (Funcionarios)
    $stmt = $pdo->query("SELECT id, nombres, apellidos, cedula FROM personal WHERE activo = 1 ORDER BY nombres ASC");
    $personal_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener último Nro de Servicio para mostrar sugerencia
    $stmt = $pdo->query("SELECT nro_servicio FROM servicios ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($last) {
        $nro_sugerido = (int)preg_replace('/[^0-9]/', '', $last['nro_servicio']) + 1;
    }

    // Lista de Cuadrantes (Misma que en estadísticas)
    $cuadrantes_lista = [
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
} catch (Exception $e) {
    // Manejo silencioso si las tablas aun no tienen datos
}

// --- Lógica para cargar datos si es EDICIÓN ---
$id = $_GET['id'] ?? '';
$evento = [];
$actuantes_evento = [];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM servicios WHERE id = ?");
    $stmt->execute([$id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt_act = $pdo->prepare("SELECT * FROM servicio_actuantes WHERE servicio_id = ?");
    $stmt_act->execute([$id]);
    $actuantes_evento = $stmt_act->fetchAll(PDO::FETCH_ASSOC);
}

// Incluimos el Header para que cargue el menú y estilos
include_once(realpath(dirname(__FILE__)) . "/include/header.php");
?>

<!-- Estilos específicos para esta página (Select2) -->
<link rel="stylesheet" href="./plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="./plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

<style>
    /* Estilo para separar visualmente cada fila de actuantes en móviles */
    .actuante-fila {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        margin-bottom: 15px; /* Separación entre bloques */
    }
</style>

<!-- Content Wrapper. Contiene el contenido principal -->
<div class="content-wrapper">
    <!-- Encabezado de contenido (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-<? echo $color; ?>">
                        <img src="./dist/img/logo2.png" width="55" class="mr-2"> Eventos 
                        <small class="text-muted">| Registro de la Novedad Relevante</small>
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    
                    <!-- Tarjeta Principal -->
                    <div class="card card-<? echo $color; ?>">
                        <div class="card-header">
                            <h3 class="card-title">Datos del Evento</h3>
                        </div>
                        
                        <div class="card-body">
                            <form action="evento_guardar.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                
                                <!-- Fila 1: Ubicación General -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Estado</label>
                                            <input type="text" class="form-control" name="estado" value="Miranda" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Municipio</label>
                                            <input type="text" class="form-control" name="municipio" value="El Hatillo" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Parroquia</label>
                                            <input type="text" class="form-control" name="parroquia" value="Santa Rosalía de Palermo" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fila 2: Fecha y Dirección -->
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Nro Servicio</label>
                                            <input type="text" class="form-control" name="nro_servicio" value="<?php echo $evento['nro_servicio'] ?? $nro_sugerido; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Fecha</label>
                                            <input type="date" class="form-control" name="fecha" value="<?php echo $evento['fecha_registro'] ?? date('Y-m-d'); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Hora</label>
                                            <input type="time" class="form-control" name="hora" value="<?php echo $evento['hora_inicio'] ?? date('H:i'); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Cuadrante</label>
                                            <select class="form-control select2" name="cuadrante" style="width: 100%;">
                                                <option value="">Seleccione...</option>
                                                <?php foreach($cuadrantes_lista as $cod => $desc): ?>
                                                    <option value="<?= $cod ?>" <?php if(($evento['cuadrante'] ?? '') == $cod) echo 'selected'; ?>><?= $cod ?>: <?= $desc ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Dirección Exacta</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo $evento['direccion_detallada'] ?? ''; ?>" placeholder="Ej: La Boyera, calle 20 este...">
                                                <div class="input-group-append">
                                                    <button class="btn btn-default" type="button" id="btn-google-maps" title="Buscar en Google Maps"><i class="fas fa-map-marker-alt"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fila 3: Detalles -->
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Tipo de Actividad</label>
                                            <select class="form-control select2" name="categoria" required>
                                                <option value="">Seleccione...</option>
                                                <?php 
                                                $cats = ['Soporte Operacional', 'Emergencias Prehospitalarias', 'Gestión de Riesgos', 'Capacitaciones', '0-800- Miranda'];
                                                foreach($cats as $c): ?>
                                                    <option value="<?= $c ?>" <?php if(($evento['categoria'] ?? '') == $c) echo 'selected'; ?>><?= $c ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Tipo de Operación</label>
                                            <select class="form-control select2" name="tipo_actividad" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach($tipos_actividad as $tipo): ?>
                                                    <option value="<?= $tipo['id'] ?>" <?php if(($evento['tipo_actividad_id'] ?? '') == $tipo['id']) echo 'selected'; ?>><?= $tipo['nombre'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Unidad</label>
                                            <select class="form-control select2" name="unidad">
                                                <option value="">Seleccione...</option>
                                                <?php foreach($unidades as $u): ?>
                                                    <option value="<?= $u['id'] ?>" <?php if(($evento['unidad_id'] ?? '') == $u['id']) echo 'selected'; ?>><?= $u['codigo'] ?> - <?= $u['descripcion'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Estatus</label>
                                            <select class="form-control" name="estatus">
                                                <?php foreach($estatus_list as $est): ?>
                                                    <option value="<?= $est['id'] ?>" <?php if(($evento['estatus_id'] ?? '') == $est['id']) echo 'selected'; ?>><?= $est['nombre'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Resumen -->
                                <div class="form-group">
                                    <label>Resumen (Operador) 
                                        <button type="button" class="btn btn-sm btn-outline-danger ml-2" onclick="startDictation('resumen', true)" title="Dictar por voz">
                                            <i class="fas fa-microphone"></i>
                                        </button>
                                    </label>
                                    <textarea id="resumen" name="resumen" class="form-control"><?php echo $evento['resumen_novedad'] ?? ''; ?></textarea>
                                </div>

                                <!-- Nuevo Campo: Reporte de Escena -->
                                <div class="form-group">
                                    <label>Reporte de Escena / Novedad en Sitio (Funcionario)
                                        <button type="button" class="btn btn-sm btn-outline-danger ml-2" onclick="startDictation('reporte_sitio', false)" title="Dictar por voz">
                                            <i class="fas fa-microphone"></i>
                                        </button>
                                    </label>
                                    <textarea id="reporte_sitio" name="reporte_sitio" class="form-control" rows="4" placeholder="Describa lo encontrado en el sitio..."><?php echo $evento['reporte_sitio'] ?? ''; ?></textarea>
                                    <small class="text-muted">Utilice el micrófono para transcribir su voz a texto automáticamente.</small>
                                </div>

                                <!-- Foto del Evento -->
                                <div class="form-group">
                                    <label>Foto del Evento</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="foto" name="foto" accept="image/*">
                                        <label class="custom-file-label" for="foto">Seleccionar archivo</label>
                                    </div>
                                </div>

                                <hr>
                                <h5>Funcionarios Actuantes Proteccion Civil</h5>
                                <div id="contenedor-actuantes">
                                    <?php 
                                    // Si hay actuantes (edición), los mostramos. Si no, mostramos una fila vacía.
                                    $lista_actuantes = (!empty($actuantes_evento)) ? $actuantes_evento : [['personal_id'=>'','rol'=>'']];
                                    foreach($lista_actuantes as $act): 
                                    ?>
                                    <div class="row mb-2 actuante-fila">
                                        <div class="col-md-6">
                                            <select name="personal_id[]" class="form-control select2-func">
                                                <option value="">Seleccione Funcionario...</option>
                                                <?php foreach($personal_list as $p): ?>
                                                    <option value="<?= $p['id'] ?>" <?php if($act['personal_id'] == $p['id']) echo 'selected'; ?>><?= $p['nombres'] . ' ' . $p['apellidos'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <select name="personal_id_acomp[]" class="form-control select2-func">
                                                <option value="">Seleccione Acompañante (Opcional)...</option>
                                                <?php foreach($personal_list as $p): ?>
                                                    <option value="<?= $p['id'] ?>" ><?= $p['nombres'] . ' ' . $p['apellidos'] ?></option>
                                                <?php endforeach; ?>
                                            </select>

                                        </div>

                                        <div class="col-md-6">
                                            <select name="rol[]" class="form-control">
                                                <option value="Conductor" <?php if($act['rol'] == 'Conductor') echo 'selected'; ?>>Conductor</option>
                                                <option value="Paramédico" <?php if($act['rol'] == 'Paramédico') echo 'selected'; ?>>Paramédico</option>
                                                <option value="Supervisor" <?php if($act['rol'] == 'Supervisor') echo 'selected'; ?>>Supervisor</option>
                                                <option value="Auxiliar" <?php if($act['rol'] == 'Auxiliar') echo 'selected'; ?>>Asistente / Auxiliar</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <select name="rol_acomp[]" class="form-control">
                                                <option value="Conductor" <?php if(($act['rol_acomp'] ?? '') == 'Conductor') echo 'selected'; ?>>Conductor</option>
                                                <option value="Paramédico" <?php if(($act['rol_acomp'] ?? '') == 'Paramédico') echo 'selected'; ?>>Paramédico</option>
                                                <option value="Supervisor" <?php if(($act['rol_acomp'] ?? '') == 'Supervisor') echo 'selected'; ?>>Supervisor</option>
                                                <option value="Auxiliar" <?php if(($act['rol_acomp'] ?? '') == 'Auxiliar') echo 'selected'; ?>>Asistente / Auxiliar</option>
                                            </select>
                                        </div>

                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <a href="personal_gestion.php" target="_blank" class="btn btn-info btn-sm mt-2"><i class="fas fa-user-plus"></i> Registrar Nuevo Personal</a>

                                <!-- Campo para Otros Actuantes (Policía, Bomberos, etc.) -->
                                <div class="form-group mt-3">
                                    <label>Funcionarios Actuantes de otros Organismos / Organismos de Apoyo</label>
                                    <input type="text" class="form-control" name="organismo" value="<?php echo $evento['organismo'] ?? ''; ?>" placeholder="Ej: Policía Municipal, Bomberos, Protección Civil Nacional...">
                                </div>

                                <!-- Botón para Borrar todos los datos del formulario -->
                                <div class="form-group mt-3">
                                    <button type="button" class="btn btn-danger" onclick="borrarFormulario()">
                                        <i class="fas fa-eraser"></i> Borrar Todos los Datos
                                    </button>
                                </div>


                                <div class="row mt-4">

                                    <div class="col-12 text-right">

                                        <a href="eventos_listado.php" class="btn btn-default">Cancelar</a>
                                        <button type="submit" class="btn btn-primary">Guardar Evento</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /.card -->
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

<!-- Scripts adicionales necesarios para esta página -->
<!-- Select2 -->
<script src="./plugins/select2/js/select2.full.min.js"></script>
<!-- Summernote -->
<script src="./plugins/summernote/summernote-bs4.min.js"></script>

<script>
    $(function () {
        // Inicializar Select2 general
        $('.select2').select2({ theme: 'bootstrap4' });
        $('.select2-func').select2({ theme: 'bootstrap4' });

        // Inicializar Summernote
        $('#resumen').summernote({
            placeholder: 'Escriba aquí el resumen del evento...',
            tabsize: 2,
            height: 150,
            lang: 'es-ES',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['view', ['fullscreen']]
            ]
        });

        // Botón Google Maps
        $('#btn-google-maps').click(function() {
            var direccion = $('#direccion').val();
            var busqueda = direccion ? direccion + ", El Hatillo, Miranda" : "El Hatillo, Miranda";
            window.open('https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(busqueda), '_blank');
        });

        // Mostrar nombre del archivo seleccionado
        $('.custom-file-input').on('change', function () {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });
    });

    function agregarFila() {
        var fila = $('.actuante-fila').first().clone();
        
        // Limpiar valores
        fila.find('select').val(''); 
        
        // Destruir select2 clonado para reinicializarlo correctamente
        fila.find('.select2-container').remove(); 
        fila.find('select').removeClass('select2-hidden-accessible').removeAttr('data-select2-id');


        
        $('#contenedor-actuantes').append(fila);
        
        // Reinicializar select2 en la nueva fila
        fila.find('.select2-func').select2({ theme: 'bootstrap4' });
    }

    function eliminarFila(btn) {
        if ($('.actuante-fila').length > 1) {
            $(btn).closest('.actuante-fila').remove();
        } else {
            alert("Debe haber al menos un funcionario.");
        }
    }

    function borrarFormulario() {
        if (confirm("¿Estás seguro de que quieres borrar todos los datos del formulario?")) {
            document.querySelector('form').reset();
            // Adicionalmente, puedes limpiar los select2 si es necesario
            $('.select2').val(null).trigger('change');
        }
    }

    // --- Función de Reconocimiento de Voz ---
    function startDictation(fieldId, isSummernote) {
        if (window.hasOwnProperty('webkitSpeechRecognition')) {
            var recognition = new webkitSpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = "es-ES";
            
            recognition.start();

            recognition.onresult = function(e) {
                var transcript = e.results[0][0].transcript;
                if (isSummernote) {
                    $('#' + fieldId).summernote('insertText', transcript + ' ');
                } else {
                    document.getElementById(fieldId).value += transcript + ' ';
                }
                recognition.stop();
            };

            recognition.onerror = function(e) {
                recognition.stop();
                alert('No se pudo reconocer la voz. Verifique permisos de micrófono.');
            }
        } else {
            alert('Tu navegador no soporta la entrada de voz (Prueba Chrome o Safari).');
        }
    }
</script>
