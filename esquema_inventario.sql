-- --------------------------------------------------------
-- Estructura de tabla para la tabla `inv_productos`
-- Catálogo maestro de todos los productos disponibles.
-- --------------------------------------------------------
CREATE TABLE `inv_productos` (
  `id_producto` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `nombre` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `descripcion` text COLLATE utf8_spanish_ci,
  `marca` varchar(150) COLLATE utf8_spanish_ci DEFAULT NULL,
  `eliminado` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_producto`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `inv_almacenes`
-- Define los diferentes inventarios: principal, secundarios y ambulancias.
-- --------------------------------------------------------
CREATE TABLE `inv_almacenes` (
  `id_almacen` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `tipo` enum('principal','secundario','ambulancia') COLLATE utf8_spanish_ci NOT NULL,
  `id_almacen_padre` int(11) DEFAULT NULL,
  `ubicacion` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `eliminado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_almacen`),
  KEY `id_almacen_padre` (`id_almacen_padre`),
  CONSTRAINT `fk_almacen_padre` FOREIGN KEY (`id_almacen_padre`) REFERENCES `inv_almacenes` (`id_almacen`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `inv_stock`
-- Stock real de productos en cada almacén.
-- --------------------------------------------------------
CREATE TABLE `inv_stock` (
  `id_stock` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) NOT NULL,
  `id_almacen` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `lote` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `fecha_ingreso` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `eliminado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_stock`),
  KEY `id_producto` (`id_producto`),
  KEY `id_almacen` (`id_almacen`),
  CONSTRAINT `fk_stock_producto` FOREIGN KEY (`id_producto`) REFERENCES `inv_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_stock_almacen` FOREIGN KEY (`id_almacen`) REFERENCES `inv_almacenes` (`id_almacen`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `inv_movimientos`
-- Historial de todas las transferencias de stock.
-- --------------------------------------------------------
CREATE TABLE `inv_movimientos` (
  `id_movimiento` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) NOT NULL,
  `id_almacen_origen` int(11) DEFAULT NULL,
  `id_almacen_destino` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_usuario_responsable` int(11) DEFAULT NULL,
  `lote` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `observaciones` text COLLATE utf8_spanish_ci,
  PRIMARY KEY (`id_movimiento`),
  KEY `id_producto_mov` (`id_producto`),
  KEY `id_almacen_origen` (`id_almacen_origen`),
  KEY `id_almacen_destino` (`id_almacen_destino`),
  CONSTRAINT `fk_movimiento_producto` FOREIGN KEY (`id_producto`) REFERENCES `inv_productos` (`id_producto`),
  CONSTRAINT `fk_movimiento_origen` FOREIGN KEY (`id_almacen_origen`) REFERENCES `inv_almacenes` (`id_almacen`),
  CONSTRAINT `fk_movimiento_destino` FOREIGN KEY (`id_almacen_destino`) REFERENCES `inv_almacenes` (`id_almacen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
