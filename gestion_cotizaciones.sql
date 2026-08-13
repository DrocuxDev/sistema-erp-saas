-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 13-08-2026 a las 00:02:55
-- Versión del servidor: 8.4.3
-- Versión de PHP: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gestion_cotizaciones`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id` bigint NOT NULL,
  `tabla` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registro_id` int NOT NULL,
  `usuario_id` bigint UNSIGNED NOT NULL,
  `accion` enum('creacion','edicion','anulacion') COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruc_dni` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacto` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int NOT NULL,
  `proveedor_id` int NOT NULL,
  `orden_compra_id` int DEFAULT NULL,
  `usuario_id` bigint UNSIGNED DEFAULT NULL,
  `fecha` date NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra_detalle`
--

CREATE TABLE `compra_detalle` (
  `id` int NOT NULL,
  `compra_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `presentacion_compra_id` int DEFAULT NULL,
  `cantidad_presentacion` decimal(10,2) DEFAULT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_compra_unit` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Disparadores `compra_detalle`
--
DELIMITER $$
CREATE TRIGGER `trg_compra_detalle_valida_producto` BEFORE INSERT ON `compra_detalle` FOR EACH ROW BEGIN
    DECLARE comprable BOOLEAN;

    SELECT puede_comprarse
    INTO comprable
    FROM productos
    WHERE id = NEW.producto_id;

    IF comprable = FALSE THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Este producto no está habilitado para compra';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobantes_venta`
--

CREATE TABLE `comprobantes_venta` (
  `id` int NOT NULL,
  `venta_id` int NOT NULL,
  `usuario_id` bigint UNSIGNED DEFAULT NULL,
  `tipo` enum('factura','boleta','nota_venta') COLLATE utf8mb4_unicode_ci NOT NULL,
  `serie` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` int NOT NULL,
  `fecha_emision` date NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `igv` decimal(12,2) NOT NULL,
  `total` decimal(12,2) GENERATED ALWAYS AS ((`subtotal` + `igv`)) STORED,
  `estado` enum('emitido','anulado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'emitido',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pct_igv` decimal(5,2) NOT NULL DEFAULT '18.00',
  `pct_retencion` decimal(5,2) NOT NULL DEFAULT '3.00',
  `pct_comision_venta` decimal(5,2) NOT NULL DEFAULT '5.00',
  `pct_comision_ganancia` decimal(5,2) NOT NULL DEFAULT '10.00',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nombre`, `pct_igv`, `pct_retencion`, `pct_comision_venta`, `pct_comision_ganancia`, `activo`, `created_at`) VALUES
(1, 'General', 18.00, 3.00, 5.00, 10.00, 1, '2026-08-12 23:46:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizaciones`
--

CREATE TABLE `cotizaciones` (
  `id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `usuario_id` bigint UNSIGNED DEFAULT NULL,
  `fecha` date NOT NULL,
  `estado` enum('pendiente','aprobada','rechazada','facturada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `observaciones` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizacion_detalle`
--

CREATE TABLE `cotizacion_detalle` (
  `id` int NOT NULL,
  `cotizacion_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `proveedor_id` int NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unidad_medida` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UND',
  `cantidad` decimal(10,2) NOT NULL,
  `precio_venta_unit` decimal(12,2) NOT NULL,
  `precio_venta_total` decimal(12,2) GENERATED ALWAYS AS ((`cantidad` * `precio_venta_unit`)) STORED,
  `precio_compra_unit` decimal(12,2) NOT NULL,
  `igv_compra` decimal(12,2) NOT NULL DEFAULT '0.00',
  `precio_compra_total` decimal(12,2) GENERATED ALWAYS AS (((`cantidad` * `precio_compra_unit`) + `igv_compra`)) STORED,
  `ganancia_linea` decimal(12,2) GENERATED ALWAYS AS (((`cantidad` * `precio_venta_unit`) - ((`cantidad` * `precio_compra_unit`) + `igv_compra`))) STORED
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizacion_resumen`
--

CREATE TABLE `cotizacion_resumen` (
  `id` int NOT NULL,
  `cotizacion_id` int NOT NULL,
  `configuracion_id` int NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `igv` decimal(12,2) NOT NULL,
  `retencion` decimal(12,2) NOT NULL,
  `total_facturar` decimal(12,2) GENERATED ALWAYS AS (((`subtotal` + `igv`) - `retencion`)) STORED,
  `compra_activa` decimal(12,2) NOT NULL,
  `compra_fija` decimal(12,2) NOT NULL,
  `no_utilizable` decimal(12,2) DEFAULT '0.00',
  `ganancia` decimal(12,2) GENERATED ALWAYS AS ((`subtotal` - `compra_fija`)) STORED,
  `comision_venta` decimal(12,2) NOT NULL,
  `comision_ganancia` decimal(12,2) NOT NULL,
  `comision_total` decimal(12,2) GENERATED ALWAYS AS ((`comision_venta` + `comision_ganancia`)) STORED,
  `saldo_a_favor` decimal(12,2) GENERATED ALWAYS AS ((((`subtotal` + `igv`) - `retencion`) - `compra_fija`)) STORED,
  `rentabilidad` decimal(12,2) GENERATED ALWAYS AS ((((((`subtotal` + `igv`) - `retencion`) - `compra_fija`) - `comision_venta`) - `comision_ganancia`)) STORED,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Disparadores `cotizacion_resumen`
--
DELIMITER $$
CREATE TRIGGER `trg_resumen_valida_subtotal` BEFORE INSERT ON `cotizacion_resumen` FOR EACH ROW BEGIN
    DECLARE suma_real DECIMAL(12,2);

    SELECT COALESCE(SUM(precio_venta_total), 0)
    INTO suma_real
    FROM cotizacion_detalle
    WHERE cotizacion_id = NEW.cotizacion_id;

    IF ABS(NEW.subtotal - suma_real) > 0.01 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'subtotal no coincide con la suma real de cotizacion_detalle';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `guias_remision`
--

CREATE TABLE `guias_remision` (
  `id` int NOT NULL,
  `venta_id` int DEFAULT NULL,
  `orden_compra_id` int DEFAULT NULL,
  `usuario_id` bigint UNSIGNED DEFAULT NULL,
  `serie` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` int NOT NULL,
  `fecha_traslado` date NOT NULL,
  `motivo_traslado` enum('venta','compra','traslado_interno','otro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `punto_partida` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `punto_llegada` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transportista` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `placa_vehiculo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('emitida','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'emitida',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `guia_remision_detalle`
--

CREATE TABLE `guia_remision_detalle` (
  `id` int NOT NULL,
  `guia_remision_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `unidad_medida` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UND'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` int NOT NULL,
  `producto_id` int NOT NULL,
  `tipo` enum('entrada','salida','ajuste') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `referencia_tipo` enum('compra','venta','ajuste','guia_remision','orden_compra') COLLATE utf8mb4_unicode_ci NOT NULL,
  `referencia_id` int DEFAULT NULL,
  `usuario_id` bigint UNSIGNED DEFAULT NULL,
  `fecha` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Disparadores `movimientos_inventario`
--
DELIMITER $$
CREATE TRIGGER `trg_movimiento_stock` AFTER INSERT ON `movimientos_inventario` FOR EACH ROW BEGIN
    IF NEW.tipo = 'entrada' THEN
        UPDATE productos
        SET stock_actual = stock_actual + NEW.cantidad
        WHERE id = NEW.producto_id;
    ELSEIF NEW.tipo = 'salida' THEN
        UPDATE productos
        SET stock_actual = stock_actual - NEW.cantidad
        WHERE id = NEW.producto_id;
    ELSE
        UPDATE productos
        SET stock_actual = NEW.cantidad
        WHERE id = NEW.producto_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_movimiento_valida` BEFORE INSERT ON `movimientos_inventario` FOR EACH ROW BEGIN
    DECLARE stock_disponible DECIMAL(12,2) DEFAULT 0;
    DECLARE existe INT DEFAULT 0;

    IF NEW.tipo = 'salida' THEN
        SELECT stock_actual
        INTO stock_disponible
        FROM productos
        WHERE id = NEW.producto_id;

        IF stock_disponible < NEW.cantidad THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Stock insuficiente para esta salida';
        END IF;
    END IF;

    IF NEW.referencia_tipo = 'compra' THEN
        SELECT COUNT(*)
        INTO existe
        FROM compras
        WHERE id = NEW.referencia_id;

    ELSEIF NEW.referencia_tipo = 'venta' THEN
        SELECT COUNT(*)
        INTO existe
        FROM ventas
        WHERE id = NEW.referencia_id;

    ELSEIF NEW.referencia_tipo = 'guia_remision' THEN
        SELECT COUNT(*)
        INTO existe
        FROM guias_remision
        WHERE id = NEW.referencia_id;

    ELSEIF NEW.referencia_tipo = 'orden_compra' THEN
        SELECT COUNT(*)
        INTO existe
        FROM ordenes_compra
        WHERE id = NEW.referencia_id;

    ELSE
        SET existe = 1;
    END IF;

    IF existe = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'referencia_id no existe para ese referencia_tipo';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes_compra`
--

CREATE TABLE `ordenes_compra` (
  `id` int NOT NULL,
  `proveedor_id` int NOT NULL,
  `usuario_id` bigint UNSIGNED DEFAULT NULL,
  `fecha` date NOT NULL,
  `estado` enum('pendiente','aprobada','recibida','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `observaciones` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes_compra_cliente`
--

CREATE TABLE `ordenes_compra_cliente` (
  `id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `cotizacion_id` int DEFAULT NULL,
  `venta_id` int DEFAULT NULL,
  `usuario_id` bigint UNSIGNED DEFAULT NULL,
  `numero_oc` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `archivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_recepcion` date NOT NULL,
  `estado` enum('pendiente','procesada','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `observaciones` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_compra_detalle`
--

CREATE TABLE `orden_compra_detalle` (
  `id` int NOT NULL,
  `orden_compra_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `presentacion_compra_id` int DEFAULT NULL,
  `cantidad_presentacion` decimal(10,2) DEFAULT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_compra_unit` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) GENERATED ALWAYS AS ((`cantidad` * `precio_compra_unit`)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int NOT NULL,
  `sku` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unidad_medida` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UND',
  `categoria` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock_actual` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stock_minimo` decimal(12,2) NOT NULL DEFAULT '0.00',
  `puede_venderse` tinyint(1) NOT NULL DEFAULT '1',
  `puede_comprarse` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_presentaciones_compra`
--

CREATE TABLE `producto_presentaciones_compra` (
  `id` int NOT NULL,
  `producto_id` int NOT NULL,
  `unidad_medida` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `equivalencia` decimal(12,4) NOT NULL,
  `preferida` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_proveedor`
--

CREATE TABLE `producto_proveedor` (
  `id` int NOT NULL,
  `producto_id` int NOT NULL,
  `proveedor_id` int NOT NULL,
  `precio_compra` decimal(12,2) NOT NULL,
  `fecha_actualizacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacto` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `series_documentos`
--

CREATE TABLE `series_documentos` (
  `id` int NOT NULL,
  `tipo_documento` enum('factura','boleta','nota_venta','guia_remision') COLLATE utf8mb4_unicode_ci NOT NULL,
  `serie` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correlativo_actual` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int NOT NULL,
  `cotizacion_id` int DEFAULT NULL,
  `cliente_id` int NOT NULL,
  `usuario_id` bigint UNSIGNED DEFAULT NULL,
  `fecha` date NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta_detalle`
--

CREATE TABLE `venta_detalle` (
  `id` int NOT NULL,
  `venta_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_venta_unit` decimal(12,2) NOT NULL,
  `precio_compra_unit` decimal(12,2) NOT NULL,
  `ganancia_linea` decimal(12,2) GENERATED ALWAYS AS ((`cantidad` * (`precio_venta_unit` - `precio_compra_unit`))) STORED
) ;

--
-- Disparadores `venta_detalle`
--
DELIMITER $$
CREATE TRIGGER `trg_venta_detalle_valida_producto` BEFORE INSERT ON `venta_detalle` FOR EACH ROW BEGIN
    DECLARE vendible BOOLEAN;

    SELECT puede_venderse
    INTO vendible
    FROM productos
    WHERE id = NEW.producto_id;

    IF vendible = FALSE THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Este producto no está habilitado para venta';
    END IF;
END
$$
DELIMITER ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_auditoria_tabla_registro` (`tabla`,`registro_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proveedor_id` (`proveedor_id`),
  ADD KEY `fk_compras_orden` (`orden_compra_id`),
  ADD KEY `fk_compras_usuario` (`usuario_id`);

--
-- Indices de la tabla `compra_detalle`
--
ALTER TABLE `compra_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_id` (`compra_id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `presentacion_compra_id` (`presentacion_compra_id`);

--
-- Indices de la tabla `comprobantes_venta`
--
ALTER TABLE `comprobantes_venta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_comprobante` (`tipo`,`serie`,`numero`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `fk_comprobantes_usuario` (`usuario_id`),
  ADD KEY `idx_comprobantes_venta_fecha` (`fecha_emision`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `fk_cotizaciones_usuario` (`usuario_id`),
  ADD KEY `idx_cotizaciones_fecha` (`fecha`);

--
-- Indices de la tabla `cotizacion_detalle`
--
ALTER TABLE `cotizacion_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cotizacion_id` (`cotizacion_id`),
  ADD KEY `proveedor_id` (`proveedor_id`),
  ADD KEY `idx_cotizacion_detalle_producto` (`producto_id`);

--
-- Indices de la tabla `cotizacion_resumen`
--
ALTER TABLE `cotizacion_resumen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cotizacion_id` (`cotizacion_id`),
  ADD KEY `configuracion_id` (`configuracion_id`);

--
-- Indices de la tabla `guias_remision`
--
ALTER TABLE `guias_remision`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_guia` (`serie`,`numero`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `orden_compra_id` (`orden_compra_id`),
  ADD KEY `fk_guias_usuario` (`usuario_id`),
  ADD KEY `idx_guias_remision_fecha` (`fecha_traslado`);

--
-- Indices de la tabla `guia_remision_detalle`
--
ALTER TABLE `guia_remision_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guia_remision_id` (`guia_remision_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_movimientos_usuario` (`usuario_id`),
  ADD KEY `idx_movimientos_producto_fecha` (`producto_id`,`fecha`);

--
-- Indices de la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proveedor_id` (`proveedor_id`),
  ADD KEY `fk_ordenes_compra_usuario` (`usuario_id`),
  ADD KEY `idx_ordenes_compra_fecha` (`fecha`);

--
-- Indices de la tabla `ordenes_compra_cliente`
--
ALTER TABLE `ordenes_compra_cliente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_oc_cliente` (`cliente_id`,`numero_oc`),
  ADD KEY `cotizacion_id` (`cotizacion_id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `fk_oc_cliente_usuario` (`usuario_id`);

--
-- Indices de la tabla `orden_compra_detalle`
--
ALTER TABLE `orden_compra_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orden_compra_id` (`orden_compra_id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `presentacion_compra_id` (`presentacion_compra_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`);

--
-- Indices de la tabla `producto_presentaciones_compra`
--
ALTER TABLE `producto_presentaciones_compra`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_producto_presentacion` (`producto_id`,`unidad_medida`);

--
-- Indices de la tabla `producto_proveedor`
--
ALTER TABLE `producto_proveedor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_producto_proveedor` (`producto_id`,`proveedor_id`),
  ADD KEY `proveedor_id` (`proveedor_id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `series_documentos`
--
ALTER TABLE `series_documentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_serie` (`tipo_documento`,`serie`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cotizacion_id` (`cotizacion_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `fk_ventas_usuario` (`usuario_id`),
  ADD KEY `idx_ventas_fecha` (`fecha`);

--
-- Indices de la tabla `venta_detalle`
--
ALTER TABLE `venta_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `idx_venta_detalle_producto` (`producto_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compra_detalle`
--
ALTER TABLE `compra_detalle`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comprobantes_venta`
--
ALTER TABLE `comprobantes_venta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cotizacion_detalle`
--
ALTER TABLE `cotizacion_detalle`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cotizacion_resumen`
--
ALTER TABLE `cotizacion_resumen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `guias_remision`
--
ALTER TABLE `guias_remision`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `guia_remision_detalle`
--
ALTER TABLE `guia_remision_detalle`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ordenes_compra_cliente`
--
ALTER TABLE `ordenes_compra_cliente`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `orden_compra_detalle`
--
ALTER TABLE `orden_compra_detalle`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto_presentaciones_compra`
--
ALTER TABLE `producto_presentaciones_compra`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto_proveedor`
--
ALTER TABLE `producto_proveedor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `series_documentos`
--
ALTER TABLE `series_documentos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `venta_detalle`
--
ALTER TABLE `venta_detalle`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD CONSTRAINT `auditoria_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  ADD CONSTRAINT `fk_compras_orden` FOREIGN KEY (`orden_compra_id`) REFERENCES `ordenes_compra` (`id`),
  ADD CONSTRAINT `fk_compras_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `compra_detalle`
--
ALTER TABLE `compra_detalle`
  ADD CONSTRAINT `compra_detalle_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `compra_detalle_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `compra_detalle_ibfk_3` FOREIGN KEY (`presentacion_compra_id`) REFERENCES `producto_presentaciones_compra` (`id`);

--
-- Filtros para la tabla `comprobantes_venta`
--
ALTER TABLE `comprobantes_venta`
  ADD CONSTRAINT `comprobantes_venta_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  ADD CONSTRAINT `fk_comprobantes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD CONSTRAINT `cotizaciones_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `fk_cotizaciones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `cotizacion_detalle`
--
ALTER TABLE `cotizacion_detalle`
  ADD CONSTRAINT `cotizacion_detalle_ibfk_1` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cotizacion_detalle_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `cotizacion_detalle_ibfk_3` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`);

--
-- Filtros para la tabla `cotizacion_resumen`
--
ALTER TABLE `cotizacion_resumen`
  ADD CONSTRAINT `cotizacion_resumen_ibfk_1` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cotizacion_resumen_ibfk_2` FOREIGN KEY (`configuracion_id`) REFERENCES `configuracion` (`id`);

--
-- Filtros para la tabla `guias_remision`
--
ALTER TABLE `guias_remision`
  ADD CONSTRAINT `fk_guias_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `guias_remision_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  ADD CONSTRAINT `guias_remision_ibfk_2` FOREIGN KEY (`orden_compra_id`) REFERENCES `ordenes_compra` (`id`);

--
-- Filtros para la tabla `guia_remision_detalle`
--
ALTER TABLE `guia_remision_detalle`
  ADD CONSTRAINT `guia_remision_detalle_ibfk_1` FOREIGN KEY (`guia_remision_id`) REFERENCES `guias_remision` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guia_remision_detalle_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `fk_movimientos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `movimientos_inventario_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  ADD CONSTRAINT `fk_ordenes_compra_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ordenes_compra_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`);

--
-- Filtros para la tabla `ordenes_compra_cliente`
--
ALTER TABLE `ordenes_compra_cliente`
  ADD CONSTRAINT `fk_oc_cliente_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ordenes_compra_cliente_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `ordenes_compra_cliente_ibfk_2` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`),
  ADD CONSTRAINT `ordenes_compra_cliente_ibfk_3` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`);

--
-- Filtros para la tabla `orden_compra_detalle`
--
ALTER TABLE `orden_compra_detalle`
  ADD CONSTRAINT `orden_compra_detalle_ibfk_1` FOREIGN KEY (`orden_compra_id`) REFERENCES `ordenes_compra` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orden_compra_detalle_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `orden_compra_detalle_ibfk_3` FOREIGN KEY (`presentacion_compra_id`) REFERENCES `producto_presentaciones_compra` (`id`);

--
-- Filtros para la tabla `producto_presentaciones_compra`
--
ALTER TABLE `producto_presentaciones_compra`
  ADD CONSTRAINT `producto_presentaciones_compra_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producto_proveedor`
--
ALTER TABLE `producto_proveedor`
  ADD CONSTRAINT `producto_proveedor_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `producto_proveedor_ibfk_2` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `fk_ventas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`),
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Filtros para la tabla `venta_detalle`
--
ALTER TABLE `venta_detalle`
  ADD CONSTRAINT `venta_detalle_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `venta_detalle_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
