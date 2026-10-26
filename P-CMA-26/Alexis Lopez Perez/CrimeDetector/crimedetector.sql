-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-07-2026 a las 13:48:20
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `crimedetector`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colonias`
--

CREATE TABLE `colonias` (
  `CODIGO_POSTAL` int(11) NOT NULL,
  `NOMBRE` varchar(100) DEFAULT NULL,
  `LATITUD` float DEFAULT NULL,
  `LONGITUD` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `colonias`
--

INSERT INTO `colonias` (`CODIGO_POSTAL`, `NOMBRE`, `LATITUD`, `LONGITUD`) VALUES
(86000, 'Centro', 17.9884, -92.9194),
(86029, 'Fraccionamiento Olmeca', 18.035, -92.8961),
(86030, 'Tamulté de las Barrancas', 17.9786, -92.9461),
(86035, 'Fraccionamiento Brisas del Usumacinta', 17.9911, -92.9485),
(86039, 'Fraccionamiento Bonanza', 18.0042, -92.9395),
(86040, 'Atasta de Serra', 17.9868, -92.9392),
(86050, 'Gil y Sáenz (El Águila)', 17.9815, -92.9258),
(86068, 'Colonia Gaviotas Norte Sector Explanada', 17.9826, -92.9105),
(86069, 'Colonia La Manga II', 17.9995, -92.909),
(86070, 'Colonia Nueva Villahermosa', 17.99, -92.9321),
(86090, 'Colonia Gaviotas Norte', 17.9811, -92.9195),
(86100, 'Tabasco 2000', 17.9982, -92.9372),
(86120, 'Fraccionamiento Islas del Mundo', 17.9765, -92.9815),
(86150, 'Fraccionamiento Jardines del Sur', 17.9553, -92.9497),
(86180, 'Fraccionamiento Guadalupe', 17.9768, -92.944),
(86190, 'Colonia Primero de Mayo', 17.9747, -92.9322),
(86246, 'La Selva', 18.0239, -92.9622);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `crimenes`
--

CREATE TABLE `crimenes` (
  `CVE_TIPO_CRIMEN` smallint(6) DEFAULT NULL,
  `FOLIO` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `crimenes`
--

INSERT INTO `crimenes` (`CVE_TIPO_CRIMEN`, `FOLIO`) VALUES
(8, 1),
(5, 1),
(23, 2),
(11, 2),
(5, 3),
(9, 3),
(15, 4),
(6, 4),
(23, 7),
(4, 10),
(13, 13),
(12, 17),
(16, 18);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `FOLIO` int(11) NOT NULL,
  `USUARIO` varchar(60) DEFAULT NULL,
  `COLONIA` int(11) DEFAULT NULL,
  `FECHA_CREACION` datetime DEFAULT NULL,
  `DIRECCION` text DEFAULT NULL,
  `LATITUD` float DEFAULT NULL,
  `LONGITUD` float DEFAULT NULL,
  `DESCRIPCION` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reportes`
--

INSERT INTO `reportes` (`FOLIO`, `USUARIO`, `COLONIA`, `FECHA_CREACION`, `DIRECCION`, `LATITUD`, `LONGITUD`, `DESCRIPCION`) VALUES
(1, 'arturosanchez@email.com', 86000, '2026-07-10 14:30:00', 'Calle Juarez #215, entre Hidalgo y Lerdo', 17.9892, -92.9181, 'Sujetos en motocicleta despojaron de sus pertenencias a un peatón.'),
(2, 'joseandrestorresvidal1@gmail.com', 86100, '2026-07-11 09:15:00', 'Av. Paseo Tabasco, cerca de la plaza comercial', 17.9982, -92.9372, 'Cristalazo a vehículo estacionado fuera del establecimiento.'),
(3, 'noc@email.com', 86030, '2026-07-12 21:45:00', 'Av. Revolución s/n, esquina con Calle Independencia', 17.9786, -92.9461, 'Robo a mano armada en tienda de conveniencia.'),
(4, 'arturosanchez@email.com', 86040, '2026-07-14 18:20:00', 'Calle Manuel Doblado #104', 17.9868, -92.9392, 'Sujeto sospechoso intentando abrir cerraduras de casas habitacionales.'),
(7, 'arturosanchez@email.com', 86050, '2026-07-18 16:05:00', 'Calle Gil y Sáenz #512', 17.9815, -92.9258, 'Robo de autopartes (batería y espejos) durante la madrugada.'),
(10, 'joseandrestorresvidal1@gmail.com', 86100, '2026-07-21 08:30:00', 'Prolongación de Zaragoza #801', 17.999, -92.936, 'Sujetos a bordo de vehículo sospechoso observando negocios de la zona.'),
(13, 'noc@email.com', 86190, '2026-07-25 14:45:51', 'Calle Independencia, Colonia Primero de Mayo, Villahermosa, Centro, Tabasco, 86190, México', 17.9745, -92.9323, ''),
(17, 'noc@email.com', 86246, '2026-07-27 18:29:54', 'Andador El Armadillo, La Selva, Pomoca, Nacajuca, Tabasco, 86246, México', 18.0246, -92.8995, 'Venta ilegal de marihuana, me han contado'),
(18, 'noc@email.com', 86070, '2026-07-28 10:33:33', 'Privada Tlaxcala, Colonia Nueva Villahermosa, Villahermosa, Centro, Tabasco, 86070, México', 17.9921, -92.9276, 'Me robaron el corazon');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_crimen`
--

CREATE TABLE `tipos_crimen` (
  `CVE_TIPO_CRIMEN` smallint(6) NOT NULL,
  `NOMBRE` varchar(50) DEFAULT NULL,
  `GRAVEDAD` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_crimen`
--

INSERT INTO `tipos_crimen` (`CVE_TIPO_CRIMEN`, `NOMBRE`, `GRAVEDAD`) VALUES
(1, 'Homicidio', 5),
(2, 'Feminicidio', 5),
(3, 'Secuestro', 5),
(4, 'Extorsión', 4),
(5, 'Robo a mano armada', 4),
(6, 'Robo a casa habitación', 3),
(7, 'Robo de vehículo', 3),
(8, 'Robo a transeúnte', 2),
(9, 'Robo a negocio', 3),
(10, 'Fraude', 2),
(11, 'Vandalismo / Daño a propiedad', 1),
(12, 'Narcomenudeo', 3),
(13, 'Asalto en transporte público', 3),
(14, 'Usurpación de identidad', 2),
(15, 'Violación de domicilio', 2),
(16, 'Acoso callejero', 1),
(18, 'Portación ilegal de arma', 4),
(19, 'Amenazas', 2),
(20, 'Despojo de inmueble', 3),
(21, 'Tráfico de personas', 5),
(23, 'Robo de autopartes', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `CORREO` varchar(60) NOT NULL,
  `NOMBRE` varchar(60) NOT NULL,
  `PASSWORD` varchar(80) DEFAULT NULL,
  `TOKEN` varchar(80) DEFAULT NULL,
  `FOTO_PERFIL` text DEFAULT 'resources/img/default.png',
  `rol` varchar(20) NOT NULL DEFAULT 'cliente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`CORREO`, `NOMBRE`, `PASSWORD`, `TOKEN`, `FOTO_PERFIL`, `rol`) VALUES
('arturosanchez@email.com', 'Arturo Sanchez Perez', '$2y$10$AvX1khgX1iTfalH0kjV8y.c51MOcb6DVWXBhogcAidmbUzbP2YBT.', NULL, 'resources/img/default.png', 'cliente'),
('desechablealexis@gmail.com', 'Alexis', '$2y$10$6asT.HUKLAA4xJ5tp5OCCudg0TRfUFniRTzPbqvM8vrzJB6qI53u.', NULL, 'resources/img/default.png', 'administrador'),
('joseandrestorresvidal1@gmail.com', 'José Andrés', '$2y$10$umRy5QqD3YG.heflwD7H7eeG13kV7Ex6OgN5R6KHvj8AnSw9v3vqu', NULL, 'uploads/fotosPerfilfoto_joseandrestorresvidal1_gmail_com_1785127746.jpg', 'cliente'),
('noc@email.com', 'Administrador', '$2y$10$GPztBI/2GM.PjmY6MCGg3.j.DEMI5DG0uNB2BFq277fBKj0a5RqwW', NULL, 'uploads/fotosPerfilfoto_noc_email_com_1785013099.webp', 'administrador');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `colonias`
--
ALTER TABLE `colonias`
  ADD PRIMARY KEY (`CODIGO_POSTAL`);

--
-- Indices de la tabla `crimenes`
--
ALTER TABLE `crimenes`
  ADD KEY `FK_REFERENCE_2` (`CVE_TIPO_CRIMEN`),
  ADD KEY `FK_REFERENCE_3` (`FOLIO`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`FOLIO`),
  ADD KEY `FK_REFERENCE_1` (`USUARIO`),
  ADD KEY `FK_REFERENCE_4` (`COLONIA`);

--
-- Indices de la tabla `tipos_crimen`
--
ALTER TABLE `tipos_crimen`
  ADD PRIMARY KEY (`CVE_TIPO_CRIMEN`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`CORREO`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `FOLIO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `tipos_crimen`
--
ALTER TABLE `tipos_crimen`
  MODIFY `CVE_TIPO_CRIMEN` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `crimenes`
--
ALTER TABLE `crimenes`
  ADD CONSTRAINT `FK_REFERENCE_2` FOREIGN KEY (`CVE_TIPO_CRIMEN`) REFERENCES `tipos_crimen` (`CVE_TIPO_CRIMEN`),
  ADD CONSTRAINT `FK_REFERENCE_3` FOREIGN KEY (`FOLIO`) REFERENCES `reportes` (`FOLIO`);

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `FK_REFERENCE_1` FOREIGN KEY (`USUARIO`) REFERENCES `usuarios` (`CORREO`),
  ADD CONSTRAINT `FK_REFERENCE_4` FOREIGN KEY (`COLONIA`) REFERENCES `colonias` (`CODIGO_POSTAL`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
