<?php

/**
 * Clase que maneja los permisos de los usuarios 
 * 
 * @file: ./sistema/clases/Permisos.php
 * @author: gfrias
 * @version: 1.0 22/01/2026
 * 
 */

class Permisos extends BID_Master_Class
{
    var $_tabla = 'permisos';

    // Jugadores
    public const CREAR_JUGADORES  = 'crear_jugadores';
    public const EDITAR_JUGADORES = 'editar_jugadores';
    public const BORRAR_JUGADORES = 'borrar_jugadores';

    // Artículos
    public const CREAR_ARTICULO   = 'crear_articulo';
    public const PUBLICAR_ARTICULO = 'publicar_articulo';

    // Administración
    public const GESTIONAR_USUARIOS = 'usuario_gestionar';
    public const CREAR_USUARIO = 'usuario_crear';
    public const MODIFICAR_USUARIO = 'usuario_modificar';
    public const ELIMINAR_USUARIO = 'usuario_eliminar';
    public const CAMBIAR_CLAVE_USUARIO = 'usuario_cambiar_clave';

    /**
     * Obtiene los permisos de un rol
     * 
     * @param $sql
     * @param $id_rol
     * @return array
     */
    public function obtenerPermisosRol($sql, $id_rol): array
    {
        $result = array();
        // Obtenemos solo la abreviacion para tener un array simple de strings
        $query = "SELECT p.abreviacion
                FROM permisos p
                INNER JOIN rol_permisos rp ON p.id = rp.permiso_id
                WHERE rp.rol_id = $id_rol";
        $resul = $sql->ExecQuery($query);

        while ($row = $sql->FetchArray($resul)) {
            $result[] = $row['abreviacion'];
        }

        return $result;
    }

    /**
     * Valida si el usuario tiene el permiso necesario
     * 
     * @param string $permiso
     * @return bool
     */
    public function validarPermiso(string $permiso): bool
    {
        // Si no hay sesión o no hay permisos cargados, denegar
        if (!isset($_SESSION['permisos']) || !is_array($_SESSION['permisos'])) {
            return false;
        }

        // El SuperAdmin suele saltarse todas las restricciones
        if (isset($_SESSION['usuario']['correo']) && $_SESSION['usuario']['correo'] === 'superadmin') {
            return true;
        }

        return in_array($permiso, $_SESSION['permisos']);
    }
}
