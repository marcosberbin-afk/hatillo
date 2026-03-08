<?php
$modulo = 'personal';
$modulo_titulo = 'Gestión de Personal';

include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

$op = (isset($_GET['o']) && $_GET['o']) ? strval($_GET['o']) : '';
$id = (isset($_GET['id']) && $_GET['id']) ? strval($_GET['id']) : '';

$personal_obj = new Personal();

$personales[0]['nacionalidad'] = '';
$personales[0]['cedula'] = '';
$personales[0]['nombres'] = '';
$personales[0]['apellidos'] = '';
$personales[0]['correo'] = '';
$personales[0]['telefono'] = '';
$personales[0]['cargo'] = '';
$personales[0]['sexo'] = '';
$personales[0]['fecha_nacimiento'] = '';
$personales[0]['activo'] = '';
$personales[0]['hash'] = '';
$personales[0]['foto'] = '';

if ($op == '' and $id == '') {

	$op = "add";
	$titulo = "Crear Unidad";
	$modificar = 1;
} else {

	$personales = $personal_obj->Consultar($_DB_, 'hash', $id);

	if ($personales and $op == 'mod') {

		$op = "mod";
		$titulo = "Modificar Personal";
		$modificar = 1;
	} elseif ($personales and $op == 'del') {

		$op = "del";
		$titulo = "Eliminar Personal";
		$modificar = 1;
	} else {

		$titulo = "Consultar Personal";
		$modificar = 0;
	}
}

include_once(realpath(dirname(__FILE__)) . "/include/header.php");
?>


<head>
	<!-- DataTables -->
	<link rel="stylesheet" href="./plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
	<link rel="stylesheet" href="./plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
</head>


<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-12">
					<h1 class="m-0 text-<? echo $color; ?>"><img src="./dist/img/logo2.png" width="55">
						<? echo $modulo_titulo; ?></h1>
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.container-fluid -->
	</div>

	<? /** /
echo "<pre>";
print_r($id);
echo "</pre>";
/**/
	?>
	<!-- Main content -->
	<section class=" content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-12">

					<form action="personal_operacion.php" method="POST" enctype="multipart/form-data">
						<input type="hidden" name="op" value="<? echo $op; ?>">
						<input type="hidden" name="id" value="<? echo $personales[0]['hash']; ?>">

						<div class="card card-<? echo $color; ?>">
							<div class="card-header">
								<h3 class="card-title">Datos del Personal</h3>
							</div>
							<div class="card-body">

								<div class="row">

									<div class="col-sm-4">
										<!-- text input -->
										<div class="form-group">
											<b>Cedula</b>
											<div class="input-group mb-3">
																<select name="nacionalidad" name="" class="form-control"
																	<? if ($modificar) {
																		echo 'required';
																	} else {
																		echo 'readonly';
																	} ?>>
																	<option value="V" <?php if ($personales[0]['nacionalidad'] == 'V') {
																		echo " selected";
																	} ?>>V</option>
																	<option value="E" <?php if ($personales[0]['nacionalidad'] == 'E') {
																		echo " selected";
																	} ?>>E</option>
																	<option value="P" <?php if ($personales[0]['nacionalidad'] == 'P') {
																		echo " selected";
																	} ?>>P</option>

																</select>

																<input type="text" name="cedula"
																	value="<? echo $personales[0]['cedula']; ?>"
																	class="form-control"
																	placeholder="Nº Cédula" <? if ($modificar) {
																		echo 'required';
																	} else {
																		echo 'readonly';
																	} ?>>
																<div class="input-group">

																</div>
															</div>
										</div>
									</div>

									<div class="col-sm-4">
										<!-- text input -->
										<div class="form-group">
											<b>Cargo</b>
											<select name="cargo" class="form-control" <? if ($modificar) {
											} else {
												echo 'readonly';
											} ?>>
												<option value="">Seleccione...</option>
												<option value="Supervisor" <? if (($personales[0]['cargo'] ?? '') == 'Supervisor') { echo " selected"; } ?>>Supervisor</option>
												<option value="Conductor" <? if (($personales[0]['cargo'] ?? '') == 'Conductor') { echo " selected"; } ?>>Conductor</option>
												<option value="Paramedico" <? if (($personales[0]['cargo'] ?? '') == 'Paramedico') { echo " selected"; } ?>>Paramédico</option>
											</select>
										</div>
									</div>

									<div class="col-sm-4">
										<!-- text input -->
										<div class="form-group">
											<b>Nombres</b>
											<input type="text" name="nombres"
												value="<? echo $personales[0]['nombres']; ?>" class="form-control"
												placeholder="" <? if ($modificar) {
												} else {
													echo 'readonly';
												} ?>>
										</div>
									</div>

									<div class="col-sm-4">
										<!-- text input -->
										<div class="form-group">
											<b>Apellidos</b>
											<input type="text" name="apellidos"
												value="<? echo $personales[0]['apellidos']; ?>" class="form-control"
												placeholder="" <? if ($modificar) {
												} else {
													echo 'readonly';
												} ?>>
										</div>
									</div>

									<div class="col-sm-4">
										<!-- text input -->
										<div class="form-group">
											<b>Teléfono</b>
											<input type="text" name="telefono"
												value="<? echo $personales[0]['telefono']; ?>" class="form-control"
												placeholder="" <? if ($modificar) {
												} else {
													echo 'readonly';
												} ?>>
										</div>
									</div>

									<div class="col-sm-4">
										<!-- text input -->
										<div class="form-group">
											<b>Correo Electrónico</b>
											<input type="text" name="correo"
												value="<? echo $personales[0]['correo']; ?>" class="form-control"
												placeholder="" <? if ($modificar) {
												} else {
													echo 'readonly';
												} ?>>
										</div>
									</div>

									<div class="col-sm-4">
										<!-- text input -->
										<div class="form-group">
											<b>Fecha Nacimiento</b>
											<div class="input-group">
															<div class="input-group-prepend">
																<span class="input-group-text"><i
																		class="far fa-calendar-alt"></i></span>
															</div>
															<input type="date" name="fecha_nacimiento"
																value="<?php echo ($personales[0]['fecha_nacimiento'] != '0000-00-00') ? $personales[0]['fecha_nacimiento'] : ''; ?>" 
																class="form-control"
																<?php if (!$modificar) echo 'readonly'; ?>>
														</div>
										</div>
									</div>

									<div class="col-sm-4">
										<!-- text input -->
										<div class="form-group">
											<b>Sexo</b>


											<select name="sexo" class="form-control" <? if ($modificar) {
											} else {
												echo 'readonly';
											} ?>>

												<option value="M" <? if ($personales[0]['sexo'] == 'M') {
													echo " selected";
												} ?>>Masculino</option>
												<option value="F" <? if ($personales[0]['sexo'] == 'F') {
													echo " selected";
												} ?>>Femenino</option>
											</select>

										</div>
									</div>

									<div class="col-sm-4">
										<!-- text input -->
										<div class="form-group">
											<b>Activa</b>


											<select name="activo" class="form-control" <? if ($modificar) {
											} else {
												echo 'readonly';
											} ?>>

												<option value="1" <? if ($personales[0]['activo'] == 1) {
													echo " selected";
												} ?>>Si</option>
												<option value="0" <? if ($personales[0]['activo'] == 0) {
													echo " selected";
												} ?>>No</option>
											</select>

										</div>
									</div>

									<?php if (!empty($personales[0]['foto'])): ?>
									<div class="col-sm-4">
										<b>Foto Actual</b><br>
										<img src="dist/img/personal/<?php echo $personales[0]['foto']; ?>" alt="Foto de perfil" class="img-thumbnail" width="100">
									</div>
									<?php endif; ?>

									<div class="col-sm-4">
										<div class="form-group">
											<b><?php echo !empty($personales[0]['foto']) ? 'Cambiar' : 'Subir'; ?> Foto de Perfil</b>
											<div class="custom-file">
												<input type="file" class="custom-file-input" id="foto" name="foto" accept="image/*" <? if (!$modificar) { echo 'disabled'; } ?>>
												<label class="custom-file-label" for="foto">Seleccionar archivo</label>
											</div>
										</div>
									</div>

								</div>
							</div>
						</div>





						<div class="row">
							<div class="col-sm-6">
								<div class="text-left">
									<a onclick="history.back();" class="btn btn-default">
										Volver</a>
								</div>
							</div>
							<div class="col-sm-6">
								<?
								if ($modificar) { ?>
									<div class="text-right">
										<?php if ($op == "add") { ?>
											<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Personal</button>
										<?php } elseif ($op == "mod") { ?>
											<button type="submit" class="btn btn-warning"><i class="fas fa-pen"></i> Modificar Personal</button>
										<?php } elseif ($op == "del") { ?>
											<button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> Eliminar Personal</button>
										<?php } ?>
									</div>
								<?
								} 
								/*
								else {
									if (
										(($_SESSION['usuario']['tipo_usuario'] >= 10) and ($_SESSION['usuario']['tipo_usuario'] < 20)) or
										($_SESSION['usuario']['tipo_usuario'] >= 90)
									) {
										?>

										<div class="text-right">
											<a href="./horarios_gestion.php?o=mod&id=<? echo $id; ?>" type="submit"
												class="btn btn-primary"><i class="fas fa-pen"></i> Modificar Horario</a>
										</div>
									<? }
								}*/
								?>
							</div>
							<br>
						</div>
					</form>
					<br>
				</div>

			</div>
		</div>
	</section>
</div>


<?php
include_once(realpath(dirname(__FILE__)) . "/include/footer.php");
?>

<!-- jQuery -->
<script src="./plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="./plugins/select2/js/select2.full.min.js"></script>
<!-- Bootstrap4 Duallistbox -->
<script src="./plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>
<!-- InputMask -->
<script src="./plugins/moment/moment.min.js"></script>
<script src="./plugins/inputmask/min/jquery.inputmask.bundle.min.js"></script>
<!-- date-range-picker -->
<script src="./plugins/daterangepicker/daterangepicker.js"></script>
<!-- bootstrap color picker -->
<script src="./plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="./plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Bootstrap Switch -->
<script src="./plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<!-- AdminLTE App -->
<script src="./dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="./dist/js/demo.js"></script>
<!-- Page script -->
<script>
	$(function () {
		//Initialize Select2 Elements
		$('.select2').select2()
		
		// Para mostrar el nombre del archivo en el input de foto
		$('.custom-file-input').on('change', function () {
			var fileName = $(this).val().split('\\').pop();
			$(this).next('.custom-file-label').html(fileName);
		});

		//Initialize Select2 Elements
		$('.select2bs4').select2({
			theme: 'bootstrap4'
		})

		//Datemask dd/mm/yyyy
		$('#datemask').inputmask('dd-mm-yyyy', {
			'placeholder': 'dd-mm-yyyy'
		})
		//Datemask2 mm/dd/yyyy
		$('#datemask2').inputmask('mm-dd-yyyy', {
			'placeholder': 'mm-dd-yyyy'
		})
		//Money Euro
		$('[data-mask]').inputmask()

		//Date range picker
		$('#reservationdate').datetimepicker({
			format: 'L'
		});
		//Date range picker
		$('#reservation').daterangepicker()
		//Date range picker with time picker
		$('#reservationtime').daterangepicker({
			timePicker: true,
			timePickerIncrement: 30,
			locale: {
				format: 'MM/DD/YYYY hh:mm A'
			}
		})
		//Date range as a button
		$('#daterange-btn').daterangepicker({
			ranges: {
				'Today': [moment(), moment()],
				'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
				'Last 7 Days': [moment().subtract(6, 'days'), moment()],
				'Last 30 Days': [moment().subtract(29, 'days'), moment()],
				'This Month': [moment().startOf('month'), moment().endOf('month')],
				'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
			},
			startDate: moment().subtract(29, 'days'),
			endDate: moment()
		},
			function (start, end) {
				$('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
			}
		)

		//Timepicker
		$('#timepicker').datetimepicker({
			format: 'LT'
		})

		//Bootstrap Duallistbox
		$('.duallistbox').bootstrapDualListbox()

		//Colorpicker
		$('.my-colorpicker1').colorpicker()
		//color picker with addon
		$('.my-colorpicker2').colorpicker()

		$('.my-colorpicker2').on('colorpickerChange', function (event) {
			$('.my-colorpicker2 .fa-square').css('color', event.color.toString());
		});

		$("input[data-bootstrap-switch]").each(function () {
			$(this).bootstrapSwitch('state', $(this).prop('checked'));
		});

	})
</script>

<!-- SweetAlert2: Alertas de Evolución Tecnológica -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('error') === 'duplicate') {
            Swal.fire({
                icon: 'error',
                title: '¡Registro Duplicado!',
                html: 'El número de cédula ingresado <b>ya se encuentra registrado</b> en nuestra base de datos inteligente.',
                footer: '<span style="color:#6c757d">Verifique el listado de personal activo</span>',
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