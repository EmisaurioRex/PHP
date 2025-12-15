SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ************************************************************
-- ** ¡LÍNEAS AÑADIDAS PARA CREAR LA BASE DE DATOS! **
-- ************************************************************
CREATE DATABASE IF NOT EXISTS vetadmin_db;
USE vetadmin_db;
-- ************************************************************

--
-- Base de datos: `vetadmin_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

DROP TABLE IF EXISTS `citas`;
CREATE TABLE IF NOT EXISTS `citas` (
  `id_cita` int NOT NULL AUTO_INCREMENT,
  `id_paciente` int NOT NULL,
  `id_veterinario` int DEFAULT NULL,
  `fecha_hora` datetime NOT NULL,
  `motivo` text COLLATE utf8mb4_unicode_ci,
  `estado` enum('Confirmada','Pendiente','Cancelada') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_cita`),
  KEY `fk_cita_paciente` (`id_paciente`),
  KEY `fk_cita_veterinario` (`id_veterinario`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id_cita`, `id_paciente`, `id_veterinario`, `fecha_hora`, `motivo`, `estado`) VALUES
(1, 2, 2, '2025-12-17 09:00:00', 'Revisión dental y limpieza.', 'Pendiente'),
(2, 3, 3, '2025-12-17 14:00:00', 'Chequeo de seguimiento post-tratamiento de tos.', 'Pendiente'),
(3, 4, 2, '2025-12-18 10:30:00', 'Consulta por pérdida de apetito en Kira.', 'Pendiente'),
(4, 1, 3, '2025-12-18 15:00:00', 'Consulta dermatológica.', 'Cancelada'),
(5, 2, 2, '2025-12-19 11:00:00', 'Aplicación de microchip y registro.', 'Confirmada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE IF NOT EXISTS `clientes` (
  `id_cliente` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellido`, `telefono`, `email`, `direccion`) VALUES
(1, 'María', 'Fernández', '5565392878', 'maria.fer@gmail.com', 'Av. Insurgentes Sur #120'),
(2, 'Roberto', 'Sánchez', '5512345678', 'rsanchez@outlook.com', 'Calle de la Paz #45'),
(3, 'Laura', 'Martínez', '5598765432', 'laura.m@yahoo.com', 'Paseo de la Reforma #100');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

DROP TABLE IF EXISTS `pacientes`;
CREATE TABLE IF NOT EXISTS `pacientes` (
  `id_paciente` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `especie` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `raza` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `sexo` enum('Macho','Hembra') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_paciente`),
  KEY `fk_paciente_cliente` (`id_cliente`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id_paciente`, `id_cliente`, `nombre`, `especie`, `raza`, `fecha_nacimiento`, `sexo`) VALUES
(1, 1, 'Max', 'Perro', 'Labrador', '2020-05-15', 'Macho'),
(2, 1, 'Luna', 'Gato', 'Siamés', '2022-01-20', 'Hembra'),
(3, 2, 'Pipo', 'Perro', 'Chihuahua', '2019-11-01', 'Macho'),
(4, 3, 'Kira', 'Gato', 'Persa', '2021-08-10', 'Hembra');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tratamientos`
--

DROP TABLE IF EXISTS `tratamientos`;
CREATE TABLE IF NOT EXISTS `tratamientos` (
  `id_tratamiento` int NOT NULL AUTO_INCREMENT,
  `id_paciente` int NOT NULL,
  `fecha_inicio` date NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `dosis` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `costo` decimal(10,2) DEFAULT NULL,
  `fecha_fin_estimada` date DEFAULT NULL,
  PRIMARY KEY (`id_tratamiento`),
  KEY `fk_tratamiento_paciente` (`id_paciente`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tratamientos`
--

INSERT INTO `tratamientos` (`id_tratamiento`, `id_paciente`, `fecha_inicio`, `descripcion`, `dosis`, `costo`, `fecha_fin_estimada`) VALUES
(1, 3, '2025-12-10', 'Antibiótico Amoxicilina', '5ml cada 12 horas por 7 días', 350.50, '2025-12-17'),
(2, 2, '2025-11-28', 'Desparasitación Interna', 'Dosis única de Praziquantel', 150.00, '2025-11-28'),
(3, 1, '2025-10-01', 'Tratamiento articular preventivo', '1 tableta diaria', 780.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_completo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('Administrador','Veterinario','Asistente') COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `nombre_usuario` (`nombre_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre_usuario`, `password`, `nombre_completo`, `rol`, `fecha_creacion`) VALUES
(1, 'pepe', '$2y$10$w8T9WwN5q.p7qA2vG3e8M.u9X0L1c1.vO1k8R6y3g6w2z5g4e0e4s', 'Pepe Administrador', 'Administrador', '2025-12-13 21:18:59'),
(2, 'Diana_Lopez', '$2y$10$tJ8Gk1P0s.r6h5a4m3l2j.v7x6w5v4u3t2s1r0q9p8o7n6m5l4k3j', 'Dra. Diana López', 'Veterinario', '2025-12-13 21:22:20'),
(3, 'Ricardo_Perez', '$2y$10$qR7Xz0Y5a.s4d3f2g1h0j.k9l8m7n6b5v4c3x2z1y0w9u8t7r6q5p', 'Dr. Ricardo Pérez', 'Veterinario', '2025-12-13 21:22:33');

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `fk_cita_paciente` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cita_veterinario` FOREIGN KEY (`id_veterinario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD CONSTRAINT `fk_paciente_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tratamientos`
--
ALTER TABLE `tratamientos`
  ADD CONSTRAINT `fk_tratamiento_paciente` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;