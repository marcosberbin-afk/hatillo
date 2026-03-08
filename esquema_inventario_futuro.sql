
-- Base de datos: `hatillo`

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_productos`
-- Catálogo maestro de todos los productos que pueden existir en el inventario.
--
CREATE TABLE `inv_productos` (
  `id_producto` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo_producto` VARCHAR(50) UNIQUE NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `descripcion` TEXT,
  `marca` VARCHAR(100),
  `categoria` VARCHAR(100),
  `unidad_medida` VARCHAR(50) COMMENT 'Ej: Caja, Unidad, Kit',
  `eliminado` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_lotes`
-- Un lote representa una compra o batch específico de un producto, 
-- con su propia fecha de vencimiento y cantidad inicial.
--
CREATE TABLE `inv_lotes` (
  `id_lote` INT AUTO_INCREMENT PRIMARY KEY,
  `id_producto` INT NOT NULL,
  `numero_lote` VARCHAR(100) NOT NULL,
  `fecha_vencimiento` DATE NOT NULL,
  `cantidad_inicial` INT NOT NULL,
  `eliminado` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_producto`) REFERENCES `inv_productos`(`id_producto`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_almacenes`
-- Define los diferentes almacenes o inventarios que existen en el sistema.
-- `tipo` puede ser 'principal', 'secundario', o 'ambulancia'.
--
CREATE TABLE `inv_almacenes` (
  `id_almacen` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(255) NOT NULL,
  `ubicacion` VARCHAR(255),
  `tipo` ENUM('principal', 'secundario', 'ambulancia') NOT NULL,
  `id_padre` INT NULL COMMENT 'Para vincular un almacén secundario al principal, o una ambulancia a un secundario.',
  `responsable_id` INT NULL COMMENT 'ID del usuario o personal a cargo',
  `eliminado` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_padre`) REFERENCES `inv_almacenes`(`id_almacen`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_stock`
-- Representa la cantidad de un lote de producto específico en un almacén determinado.
-- Esta es la tabla central para saber "cuánto hay de qué y dónde".
--
CREATE TABLE `inv_stock` (
  `id_stock` INT AUTO_INCREMENT PRIMARY KEY,
  `id_almacen` INT NOT NULL,
  `id_lote` INT NOT NULL,
  `cantidad` INT NOT NULL,
  `stock_minimo` INT DEFAULT 10,
  `eliminado` TINYINT(1) NOT NULL DEFAULT '0',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `almacen_lote` (`id_almacen`, `id_lote`),
  FOREIGN KEY (`id_almacen`) REFERENCES `inv_almacenes`(`id_almacen`) ON DELETE CASCADE,
  FOREIGN KEY (`id_lote`) REFERENCES `inv_lotes`(`id_lote`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_movimientos`
-- Registra cada transferencia de stock entre almacenes.
-- Vital para la trazabilidad y auditoría del inventario.
--
CREATE TABLE `inv_movimientos` (
  `id_movimiento` INT AUTO_INCREMENT PRIMARY KEY,
  `id_lote` INT NOT NULL,
  `cantidad` INT NOT NULL,
  `id_almacen_origen` INT NOT NULL,
  `id_almacen_destino` INT NOT NULL,
  `fecha_movimiento` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `id_usuario_responsable` INT NOT NULL COMMENT 'Usuario que realiza la operación',
  `tipo_movimiento` ENUM('transferencia', 'ajuste_entrada', 'ajuste_salida', 'consumo') NOT NULL,
  `observaciones` TEXT,
  FOREIGN KEY (`id_lote`) REFERENCES `inv_lotes`(`id_lote`),
  FOREIGN KEY (`id_almacen_origen`) REFERENCES `inv_almacenes`(`id_almacen`),
  FOREIGN KEY (`id_almacen_destino`) REFERENCES `inv_almacenes`(`id_almacen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tablas adicionales para innovación y futuro
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_proveedores`
-- Información de proveedores para reabastecimiento automático.
--
CREATE TABLE `inv_proveedores` (
  `id_proveedor` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(255) NOT NULL,
  `contacto` VARCHAR(255),
  `email` VARCHAR(255),
  `telefono` VARCHAR(50),
  `direccion` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `inv_producto_proveedor`
-- Relación muchos a muchos entre productos y proveedores.
--
CREATE TABLE `inv_producto_proveedor` (
  `id_producto` INT NOT NULL,
  `id_proveedor` INT NOT NULL,
  `precio_unitario` DECIMAL(10,2),
  `tiempo_entrega_dias` INT,
  PRIMARY KEY (`id_producto`, `id_proveedor`),
  FOREIGN KEY (`id_producto`) REFERENCES `inv_productos`(`id_producto`) ON DELETE CASCADE,
  FOREIGN KEY (`id_proveedor`) REFERENCES `inv_proveedores`(`id_proveedor`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `inv_alertas`
-- Sistema de alertas para vencimientos, stock bajo, etc.
--
CREATE TABLE `inv_alertas` (
  `id_alerta` INT AUTO_INCREMENT PRIMARY KEY,
  `id_almacen` INT NOT NULL,
  `id_lote` INT NULL,
  `tipo_alerta` ENUM('vencimiento_proximo', 'stock_bajo', 'sobre_stock', 'transferencia_pendiente') NOT NULL,
  `mensaje` TEXT NOT NULL,
  `fecha_alerta` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `resuelta` BOOLEAN DEFAULT FALSE,
  `id_usuario_notificado` INT NULL,
  FOREIGN KEY (`id_almacen`) REFERENCES `inv_almacenes`(`id_almacen`) ON DELETE CASCADE,
  FOREIGN KEY (`id_lote`) REFERENCES `inv_lotes`(`id_lote`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `inv_pedidos`
-- Pedidos automáticos o manuales para reabastecimiento.
--
CREATE TABLE `inv_pedidos` (
  `id_pedido` INT AUTO_INCREMENT PRIMARY KEY,
  `id_almacen` INT NOT NULL,
  `id_proveedor` INT NOT NULL,
  `fecha_pedido` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_entrega_estimada` DATE,
  `estado` ENUM('pendiente', 'en_transito', 'recibido', 'cancelado') DEFAULT 'pendiente',
  `id_usuario_solicitante` INT NOT NULL,
  FOREIGN KEY (`id_almacen`) REFERENCES `inv_almacenes`(`id_almacen`) ON DELETE CASCADE,
  FOREIGN KEY (`id_proveedor`) REFERENCES `inv_proveedores`(`id_proveedor`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `inv_pedido_detalles`
-- Detalles de productos en un pedido.
--
CREATE TABLE `inv_pedido_detalles` (
  `id_pedido_detalle` INT AUTO_INCREMENT PRIMARY KEY,
  `id_pedido` INT NOT NULL,
  `id_producto` INT NOT NULL,
  `cantidad_solicitada` INT NOT NULL,
  `cantidad_recibida` INT DEFAULT 0,
  `precio_unitario` DECIMAL(10,2),
  FOREIGN KEY (`id_pedido`) REFERENCES `inv_pedidos`(`id_pedido`) ON DELETE CASCADE,
  FOREIGN KEY (`id_producto`) REFERENCES `inv_productos`(`id_producto`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `inv_consumos_ambulancia`
-- Registro específico de consumos en ambulancias para trazabilidad.
--
CREATE TABLE `inv_consumos_ambulancia` (
  `id_consumo` INT AUTO_INCREMENT PRIMARY KEY,
  `id_almacen_ambulancia` INT NOT NULL,
  `id_lote` INT NOT NULL,
  `cantidad_consumida` INT NOT NULL,
  `fecha_consumo` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `id_paciente` VARCHAR(100) NULL COMMENT 'ID o referencia del paciente si aplica',
  `id_usuario_responsable` INT NOT NULL,
  `observaciones` TEXT,
  FOREIGN KEY (`id_almacen_ambulancia`) REFERENCES `inv_almacenes`(`id_almacen`) ON DELETE CASCADE,
  FOREIGN KEY (`id_lote`) REFERENCES `inv_lotes`(`id_lote`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Vistas para reportes innovadores
-- --------------------------------------------------------

--
-- Vista para stock jerárquico
--
CREATE VIEW `v_stock_jerarquico` AS
SELECT 
  a.id_almacen,
  a.nombre AS almacen_nombre,
  a.tipo,
  a.id_padre,
  p.nombre AS producto_nombre,
  l.numero_lote,
  l.fecha_vencimiento,
  s.cantidad,
  s.stock_minimo,
  CASE 
    WHEN l.fecha_vencimiento < CURDATE() + INTERVAL 30 DAY THEN 'Próximo a vencer'
    WHEN s.cantidad <= s.stock_minimo THEN 'Stock bajo'
    ELSE 'Normal'
  END AS estado
FROM inv_almacenes a
JOIN inv_stock s ON a.id_almacen = s.id_almacen
JOIN inv_lotes l ON s.id_lote = l.id_lote
JOIN inv_productos p ON l.id_producto = p.id_producto
WHERE a.eliminado = 0;

--
-- Vista para alertas activas
--
CREATE VIEW `v_alertas_activas` AS
SELECT * FROM inv_alertas WHERE resuelta = FALSE;

-- --------------------------------------------------------
-- Triggers para automatización
-- --------------------------------------------------------

--
-- Trigger para generar alertas de stock bajo
--
DELIMITER ;;
CREATE TRIGGER `trg_stock_bajo` AFTER UPDATE ON `inv_stock`
FOR EACH ROW
BEGIN
  IF NEW.cantidad <= NEW.stock_minimo AND OLD.cantidad > OLD.stock_minimo THEN
    INSERT INTO inv_alertas (id_almacen, id_lote, tipo_alerta, mensaje)
    VALUES (NEW.id_almacen, NEW.id_lote, 'stock_bajo', CONCAT('Stock bajo para lote ', (SELECT numero_lote FROM inv_lotes WHERE id_lote = NEW.id_lote)));
  END IF;
END;;
DELIMITER ;

--
-- Trigger para alertas de vencimiento próximo
--
DELIMITER ;;
CREATE TRIGGER `trg_vencimiento_proximo` AFTER INSERT ON `inv_lotes`
FOR EACH ROW
BEGIN
  IF NEW.fecha_vencimiento < CURDATE() + INTERVAL 30 DAY THEN
    INSERT INTO inv_alertas (id_almacen, id_lote, tipo_alerta, mensaje)
    SELECT id_almacen, NEW.id_lote, 'vencimiento_proximo', CONCAT('Lote ', NEW.numero_lote, ' próximo a vencer')
    FROM inv_stock WHERE id_lote = NEW.id_lote;
  END IF;
END;;
DELIMITER ;
