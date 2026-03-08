<nav class="mt-2">
	<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
		<li class="nav-item">
			<a href="index.php" class="nav-link <? if ($modulo == 'index') {
				echo 'active';
			} ?>">
				<i class="nav-icon fas fa-home"></i>
				<p>Inicio</p>
			</a>
		</li>

		<li class="nav-item">
			<a href="evento_registrar.php" class="nav-link">
				<i class="nav-icon fas fa-bullhorn"></i>
				<p>Eventos</p>
			</a>
		</li>

		<li class="nav-item">
			<a href="eventos_listado.php" class="nav-link">
				<i class="nav-icon fas fa-history"></i>
				<p>Historial de Eventos</p>
			</a>
		</li>

		<li class="nav-item">
			<a href="guardia_listado.php" class="nav-link">
				<i class="nav-icon fas fa-exchange-alt"></i>
				<p>Cambio de Guardia</p>
			</a>
		</li>

		<li class="nav-item">
			<a href="personal_listado.php" class="nav-link">
				<i class="nav-icon fas fa-user-tie"></i>
				<p>Personal</p>
			</a>
		</li>

		<li class="nav-item">
			<a href="unidades_listado.php" class="nav-link">
				<i class="nav-icon fas fa-ambulance"></i>
				<p>Unidades</p>
			</a>
		</li>

		<li class="nav-item">
			<a href="usuarios_listado.php" class="nav-link">
				<i class="nav-icon fas fa-users-cog"></i>
				<p>Usuarios</p>
			</a>
		</li>
		<li class="nav-item">
    <a href="estadisticas.php" class="nav-link <?php if ($modulo == 'estadisticas') echo 'active'; ?>">
        <i class="nav-icon fas fa-chart-pie"></i>
        <p>Estadísticas</p>
    </a>
</li>

		<!-- Menu de Inventario -->
		<?php
			// Detectar página actual para marcar sub-menú y mantener abierto
			$pag_actual = basename($_SERVER['PHP_SELF']);
			$es_inventario = (isset($menu_abierto) && $menu_abierto == 'inventario');
		?>
		<li class="nav-item has-treeview <?php if ($es_inventario) echo 'menu-open'; ?>">
			<a href="#" class="nav-link">
				<i class="nav-icon fas fa-boxes"></i>
				<p>
					Inventario
					<i class="fas fa-angle-left right"></i>
				</p>
			</a>

			<ul class="nav nav-treeview">
				<li class="nav-item">
					<a href="inventario_listado.php" class="nav-link <?php if ($pag_actual == 'inventario_listado.php') echo 'active'; ?>">
						<i class="nav-icon fas fa-list ms-4"></i>
						<p>Ver Stock</p>
					</a>
				</li>
				<li class="nav-item">
					<a href="inventario_operacion.php" class="nav-link <?php if ($pag_actual == 'inventario_operacion.php') echo 'active'; ?>">
						<i class="nav-icon fas fa-exchange-alt ms-4"></i>
						<p>Operaciones</p>
					</a>
				</li>
				<li class="nav-item">
					<a href="inventario_alertas.php" class="nav-link <?php if ($pag_actual == 'inventario_alertas.php') echo 'active'; ?>">
						<i class="nav-icon fas fa-exclamation-triangle ms-4"></i>
						<p>Alertas</p>
					</a>
				</li>
			</ul>
		</li>
		<!-- Fin Menu de Inventario -->


	</ul>
</nav>