<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
            <a href="index.php" class="nav-link <?= ($modulo == 'index') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-home"></i>
                <p>Inicio</p>
            </a>
        </li>
        <li class="nav-item has-treeview">
            <a href="disciplinas_deportivas_gestion.php" class="nav-link">
                <!-- <i class="nav-icon fa fa-book"></i> -->
                <i class="nav-icon fas fa-layer-group"></i>
                <p>Disciplinas Deportivas</p>
            </a>
        </li>
        <li class="nav-item has-treeview">
            <a href="evento_gestion.php" class="nav-link">
                <i class="nav-icon fa fa-book"></i>
                <p>Eventos</p>
            </a>
        </li>

        <!-- Menu de Atletas -->
        <li class="nav-item has-treeview">
            <a href="alumnos_listado.php" class="nav-link">
                <!-- <i class="nav-icon fa fa-book"></i> -->
                <i class="nav-icon fas fa-running"></i>
                <p>Atletas</p>
            </a>
        </li>

        <li class="nav-item has-treeview">
            <a href="alumnos_listado.php" class="nav-link">
                <i class="nav-icon fa fa-book"></i>
                <p>Alumnos</p>
            </a>
        </li>

        <li class="nav-header">
            CONFIGURACIONES
        </li>

        <!-- Menu de Usuarios -->
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link <?= ($menu_abierto == 'usuarios') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-users-cog"></i>
                <p>
                    Usuarios
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>

            <ul class="nav nav-treeview">
                <?php
                $opciones_usuario = [
                    ['permiso' => $permisos->validarPermiso(Permisos::CREAR_USUARIO),       'icono' => 'fas fa-plus',  'texto' => 'Crear',         'link' => 'usuarios_gestion.php'],
                    ['permiso' => $permisos->validarPermiso(Permisos::MODIFICAR_USUARIO),   'icono' => 'fas fa-edit',  'texto' => 'Modificar',     'link' => 'usuarios_gestion.php'],
                    ['permiso' => $permisos->validarPermiso(Permisos::ELIMINAR_USUARIO),    'icono' => 'fas fa-trash', 'texto' => 'Eliminar',      'link' => 'usuarios_gestion.php'],
                    ['permiso' => $permisos->validarPermiso(Permisos::CAMBIAR_CLAVE_USUARIO), 'icono' => 'fas fa-key',   'texto' => 'Cambiar Clave', 'link' => 'usuarios_gestion.php'],
                ];

                foreach ($opciones_usuario as $opcion) {
                    if ($opcion['permiso']) { ?>
                        <li class="nav-item text-white">
                            <a href="<?= $opcion['link'] ?>" class="nav-link">
                                <i class="nav-icon <?= $opcion['icono'] ?> ms-4"></i>
                                <?= $opcion['texto'] ?>
                            </a>
                        </li>
                <?php }
                } ?>
            </ul>
        </li>
        <!-- Fin Menu de Usuarios -->

        <!-- Menu de Inventario -->
        <?php
            // Lógica para determinar la página actual y si pertenece al módulo de inventario
            $current_page_filename = basename($_SERVER['PHP_SELF']);
            $inventory_pages = ['inventario_listado.php', 'inventario_operacion.php', 'inventario_alertas.php'];
            $is_on_inventory_page = in_array($current_page_filename, $inventory_pages);
        ?>
        <li class="nav-item has-treeview <?= $is_on_inventory_page ? 'menu-open' : '' ?>">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-boxes"></i>
                <p>
                    Inventario
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>

            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="inventario_listado.php" class="nav-link <?= ($current_page_filename == 'inventario_listado.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-list ms-4"></i>
                        <p>Ver Stock</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="inventario_operacion.php" class="nav-link <?= ($current_page_filename == 'inventario_operacion.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-exchange-alt ms-4"></i>
                        <p>Operaciones</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="inventario_alertas.php" class="nav-link <?= ($current_page_filename == 'inventario_alertas.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-exclamation-triangle ms-4"></i>
                        <p>Alertas</p>
                    </a>
                </li>
            </ul>
        </li>
        <!-- Fin Menu de Inventario -->

    </ul>
</nav>