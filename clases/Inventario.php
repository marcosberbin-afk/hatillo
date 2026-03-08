<?php
class Inventario extends BID_Master_Class
{
    /**
     * Obtiene el stock de un almacén específico, uniendo con la tabla de productos y lotes.
     *
     * @param object $sql Objeto de conexión a la base de datos.
     * @param int $id_almacen ID del almacén a consultar.
     * @return array Lista de productos en stock.
     */
    function getStock($sql, $id_almacen)
    {
        $result = array();
        $query = "SELECT s.*, p.nombre as producto_nombre, p.codigo_producto, p.marca, l.numero_lote, l.fecha_vencimiento
                  FROM inv_stock s
                  JOIN inv_lotes l ON s.id_lote = l.id_lote
                  JOIN inv_productos p ON l.id_producto = p.id_producto
                  WHERE s.id_almacen = '$id_almacen' AND s.eliminado = 0 AND p.eliminado = 0 AND l.eliminado = 0";

        $resul = $sql->ExecQuery($query);

        $i = 0;
        while ($row = $sql->FetchArray($resul)) {
            $campos = array_keys($row);
            foreach ($campos as $campo) {
                $result[$i][$campo] = $row[$campo];
            }
            $i++;
        }

        return $result;
    }

    /**
     * Obtiene la lista de todos los productos definidos.
     */
    function getProductos($sql)
    {
        $result = array();
        $query = "SELECT * FROM inv_productos WHERE eliminado = 0 ORDER BY nombre ASC";
        $resul = $sql->ExecQuery($query);
        $i = 0;
        while ($row = $sql->FetchArray($resul)) {
            $result[$i] = $row;
            $i++;
        }
        return $result;
    }

    /**
     * Transfiere stock de un almacén a otro.
     * Esta es la operación principal y más compleja.
     *
     * @param object $sql
     * @param int $id_lote
     * @param int $id_almacen_origen
     * @param int $id_almacen_destino
     * @param int $cantidad
     * @param int $id_usuario_responsable
     * @return bool True si la transferencia fue exitosa, false en caso contrario.
     */
    function transferirStock($sql, $id_lote, $id_almacen_origen, $id_almacen_destino, $cantidad, $id_usuario_responsable)
    {
        $sql->StartTransaction();

        try {
            // 1. Verificar stock disponible
            $query_stock = "SELECT cantidad FROM inv_stock WHERE id_almacen = $id_almacen_origen AND id_lote = $id_lote";
            $result_stock = $sql->ExecQuery($query_stock);
            if ($sql->NumerRows($result_stock) == 0) {
                throw new Exception("No hay stock disponible para este lote en el almacén origen.");
            }
            $row_stock = $sql->FetchArray($result_stock);
            if ($row_stock['cantidad'] < $cantidad) {
                throw new Exception("Stock insuficiente en el almacén origen.");
            }

            // 2. Restar del origen
            $query_restar = "UPDATE inv_stock SET cantidad = cantidad - $cantidad WHERE id_almacen = $id_almacen_origen AND id_lote = $id_lote";
            $sql->ExecQuery($query_restar);

            // 3. Sumar al destino (o insertar si no existe)
            $query_destino = "SELECT id_stock FROM inv_stock WHERE id_almacen = $id_almacen_destino AND id_lote = $id_lote";
            $result_destino = $sql->ExecQuery($query_destino);
            if ($sql->NumerRows($result_destino) > 0) {
                $query_sumar = "UPDATE inv_stock SET cantidad = cantidad + $cantidad WHERE id_almacen = $id_almacen_destino AND id_lote = $id_lote";
                $sql->ExecQuery($query_sumar);
            } else {
                $query_insert = "INSERT INTO inv_stock (id_almacen, id_lote, cantidad) VALUES ($id_almacen_destino, $id_lote, $cantidad)";
                $sql->ExecQuery($query_insert);
            }

            // 4. Registrar movimiento
            $query_movimiento = "INSERT INTO inv_movimientos (id_lote, cantidad, id_almacen_origen, id_almacen_destino, id_usuario_responsable, tipo_movimiento) 
                                 VALUES ($id_lote, $cantidad, $id_almacen_origen, $id_almacen_destino, $id_usuario_responsable, 'transferencia')";
            $sql->ExecQuery($query_movimiento);

            $sql->Commit();
            return true;
        } catch (Exception $e) {
            $sql->Rollback();
            return false;
        }
    }

    /**
     * Registra una entrada de stock nuevo (Compra o Dotación Inicial).
     */
    function registrarEntrada($sql, $id_producto, $id_almacen, $numero_lote, $fecha_vencimiento, $cantidad, $id_usuario)
    {
        $sql->StartTransaction();
        try {
            // 1. Crear el Lote
            $query_lote = "INSERT INTO inv_lotes (id_producto, numero_lote, fecha_vencimiento, cantidad_inicial) 
                           VALUES ($id_producto, '$numero_lote', '$fecha_vencimiento', $cantidad)";
            $sql->ExecQuery($query_lote);
            $id_lote = $sql->LastInsertId();

            // 2. Insertar en Stock del Almacén seleccionado
            $query_stock = "INSERT INTO inv_stock (id_almacen, id_lote, cantidad) VALUES ($id_almacen, $id_lote, $cantidad)";
            $sql->ExecQuery($query_stock);

            // 3. Registrar Movimiento (Origen NULL indica entrada externa/compra)
            $query_mov = "INSERT INTO inv_movimientos (id_lote, cantidad, id_almacen_origen, id_almacen_destino, id_usuario_responsable, tipo_movimiento, observaciones) 
                          VALUES ($id_lote, $cantidad, NULL, $id_almacen, $id_usuario, 'ajuste_entrada', 'Carga Inicial / Compra')";
            $sql->ExecQuery($query_mov);

            $sql->Commit();
            return true;
        } catch (Exception $e) {
            $sql->Rollback();
            return false;
        }
    }

    /**
     * Obtiene la lista de almacenes, opcionalmente filtrada por tipo.
     *
     * @param object $sql Objeto de conexión a la base de datos.
     * @param string $tipo (opcional) 'principal', 'secundario', o 'ambulancia'.
     * @return array Lista de almacenes.
     */
    function getAlmacenes($sql, $tipo = '')
    {
        $this->_tabla = 'inv_almacenes'; // Establecemos la tabla para usar el método heredado
        
        $condicion = "eliminado = 0";
        if (!empty($tipo)) {
            $condicion .= " AND tipo = '$tipo'";
        }

        // Reutilizamos el método de la clase padre
        return $this->ListadoCondicional($sql, $condicion);
    }

    /**
     * Obtiene alertas activas para un almacén.
     *
     * @param object $sql
     * @param int $id_almacen
     * @return array Lista de alertas.
     */
    function getAlertas($sql, $id_almacen = null)
    {
        $query = "SELECT a.*, p.nombre as producto_nombre, l.numero_lote 
                  FROM inv_alertas a 
                  LEFT JOIN inv_lotes l ON a.id_lote = l.id_lote 
                  LEFT JOIN inv_productos p ON l.id_producto = p.id_producto 
                  WHERE a.resuelta = FALSE";
        if ($id_almacen) {
            $query .= " AND a.id_almacen = $id_almacen";
        }
        $query .= " ORDER BY a.fecha_alerta DESC";

        $result = array();
        $resul = $sql->ExecQuery($query);
        $i = 0;
        while ($row = $sql->FetchArray($resul)) {
            $result[$i] = $row;
            $i++;
        }
        return $result;
    }

    /**
     * Crea un pedido automático basado en stock bajo.
     *
     * @param object $sql
     * @param int $id_almacen
     * @param int $id_usuario
     * @return bool
     */
    function crearPedidoAutomatico($sql, $id_almacen, $id_usuario)
    {
        // Obtener productos con stock bajo
        $query_stock_bajo = "SELECT s.id_lote, l.id_producto, p.nombre, (s.stock_minimo - s.cantidad) as cantidad_necesaria, pp.id_proveedor, pp.precio_unitario
                             FROM inv_stock s
                             JOIN inv_lotes l ON s.id_lote = l.id_lote
                             JOIN inv_productos p ON l.id_producto = p.id_producto
                             LEFT JOIN inv_producto_proveedor pp ON p.id_producto = pp.id_producto
                             WHERE s.id_almacen = $id_almacen AND s.cantidad <= s.stock_minimo
                             ORDER BY pp.id_proveedor";

        $resul = $sql->ExecQuery($query_stock_bajo);
        if ($sql->NumerRows($resul) == 0) return false;

        $pedidos_por_proveedor = array();
        while ($row = $sql->FetchArray($resul)) {
            $prov = $row['id_proveedor'] ?: 1; // Default proveedor si no hay
            if (!isset($pedidos_por_proveedor[$prov])) {
                $pedidos_por_proveedor[$prov] = array();
            }
            $pedidos_por_proveedor[$prov][] = $row;
        }

        foreach ($pedidos_por_proveedor as $id_proveedor => $productos) {
            // Crear pedido
            $query_pedido = "INSERT INTO inv_pedidos (id_almacen, id_proveedor, id_usuario_solicitante) VALUES ($id_almacen, $id_proveedor, $id_usuario)";
            $sql->ExecQuery($query_pedido);
            $id_pedido = $sql->LastInsertId();

            foreach ($productos as $prod) {
                $query_detalle = "INSERT INTO inv_pedido_detalles (id_pedido, id_producto, cantidad_solicitada, precio_unitario) 
                                  VALUES ($id_pedido, {$prod['id_producto']}, {$prod['cantidad_necesaria']}, {$prod['precio_unitario']})";
                $sql->ExecQuery($query_detalle);
            }
        }
        return true;
    }

    /**
     * Registra consumo en ambulancia.
     *
     * @param object $sql
     * @param int $id_almacen_ambulancia
     * @param int $id_lote
     * @param int $cantidad
     * @param int $id_usuario
     * @param string $id_paciente
     * @return bool
     */
    function registrarConsumoAmbulancia($sql, $id_almacen_ambulancia, $id_lote, $cantidad, $id_usuario, $id_paciente = null)
    {
        // Verificar que es ambulancia
        $query_tipo = "SELECT tipo FROM inv_almacenes WHERE id_almacen = $id_almacen_ambulancia";
        $result_tipo = $sql->ExecQuery($query_tipo);
        $row_tipo = $sql->FetchArray($result_tipo);
        if ($row_tipo['tipo'] != 'ambulancia') {
            return false;
        }

        // Insertar consumo
        $query = "INSERT INTO inv_consumos_ambulancia (id_almacen_ambulancia, id_lote, cantidad_consumida, id_usuario_responsable, id_paciente) 
                  VALUES ($id_almacen_ambulancia, $id_lote, $cantidad, $id_usuario, " . ($id_paciente ? "'$id_paciente'" : "NULL") . ")";
        $sql->ExecQuery($query);

        // Actualizar stock
        $query_stock = "UPDATE inv_stock SET cantidad = cantidad - $cantidad WHERE id_almacen = $id_almacen_ambulancia AND id_lote = $id_lote AND cantidad >= $cantidad";
        $sql->ExecQuery($query_stock);

        return true;
    }

    /**
     * Marca una alerta como resuelta.
     */
    function resolverAlerta($sql, $id_alerta, $id_usuario)
    {
        // Actualiza el estado a resuelta (1) y registra quién lo hizo
        $query = "UPDATE inv_alertas SET resuelta = 1, id_usuario_notificado = $id_usuario WHERE id_alerta = $id_alerta";
        return $sql->ExecQuery($query);
    }
}
