-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 05, 2025 at 02:22 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `savesa`
--

-- --------------------------------------------------------

--
-- Table structure for table `asistencias`
--

CREATE TABLE `asistencias` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('Presente','Ausente','Justificado') NOT NULL,
  `justificacion` varchar(255) DEFAULT NULL,
  `archivo_justificacion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asistencias`
--

INSERT INTO `asistencias` (`id`, `estudiante_id`, `grupo_id`, `fecha`, `estado`, `justificacion`, `archivo_justificacion`) VALUES
(8, 27, 9, '2025-05-31', 'Presente', NULL, NULL),
(9, 28, 9, '2025-05-31', 'Presente', NULL, NULL),
(10, 29, 9, '2025-05-31', 'Ausente', NULL, NULL),
(11, 30, 9, '2025-05-31', 'Presente', NULL, NULL),
(12, 27, 15, '2025-05-31', 'Ausente', 'Buenas Noches profesor, no pude ir debido a un cierre en el pindo.', NULL),
(13, 27, 11, '2025-05-31', 'Presente', NULL, NULL),
(14, 33, 15, '2025-05-31', 'Presente', NULL, NULL),
(15, 28, 15, '2025-05-31', 'Presente', NULL, NULL),
(16, 29, 15, '2025-05-31', 'Presente', NULL, NULL),
(17, 30, 15, '2025-05-31', 'Presente', NULL, NULL),
(18, 31, 15, '2025-05-31', 'Presente', NULL, NULL),
(19, 34, 15, '2025-05-31', 'Ausente', NULL, NULL),
(20, 39, 15, '2025-05-31', 'Justificado', 'Tuve un dolor de cabeza', 'just_39_1748704115.jpg'),
(21, 27, 11, '2025-06-03', 'Presente', NULL, NULL),
(22, 28, 11, '2025-06-03', 'Ausente', NULL, NULL),
(23, 29, 11, '2025-06-03', 'Presente', NULL, NULL),
(24, 30, 11, '2025-06-03', 'Presente', NULL, NULL),
(25, 39, 15, '2025-06-03', 'Justificado', 'El motivo de mi ausencia es por motivos mayores de salud.', 'just_39_1748933910.docx'),
(26, 31, 15, '2025-06-03', 'Presente', NULL, NULL),
(27, 30, 15, '2025-06-03', 'Presente', NULL, NULL),
(28, 29, 15, '2025-06-03', 'Presente', NULL, NULL),
(29, 28, 15, '2025-06-03', 'Presente', NULL, NULL),
(30, 27, 15, '2025-06-03', 'Presente', NULL, NULL),
(31, 33, 15, '2025-06-03', 'Presente', NULL, NULL),
(32, 34, 15, '2025-06-03', 'Presente', NULL, NULL),
(33, 38, 15, '2025-06-03', 'Presente', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `grupos`
--

CREATE TABLE `grupos` (
  `id` int(11) NOT NULL,
  `materia_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grupos`
--

INSERT INTO `grupos` (`id`, `materia_id`, `nombre`) VALUES
(9, 9, '20'),
(10, 9, '21'),
(11, 10, '41'),
(12, 10, '42'),
(13, 11, '34'),
(14, 11, '35'),
(15, 12, '1'),
(16, 15, '1');

-- --------------------------------------------------------

--
-- Table structure for table `grupo_estudiante`
--

CREATE TABLE `grupo_estudiante` (
  `grupo_id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grupo_estudiante`
--

INSERT INTO `grupo_estudiante` (`grupo_id`, `estudiante_id`) VALUES
(9, 27),
(9, 28),
(9, 29),
(9, 30),
(10, 31),
(10, 33),
(10, 34),
(10, 38),
(11, 27),
(11, 28),
(11, 29),
(11, 30),
(12, 31),
(12, 33),
(12, 34),
(12, 38),
(13, 27),
(13, 28),
(14, 30),
(14, 31),
(14, 33),
(14, 34),
(14, 38),
(15, 27),
(15, 28),
(15, 29),
(15, 30),
(15, 31),
(15, 33),
(15, 34),
(15, 38),
(15, 39),
(16, 38);

-- --------------------------------------------------------

--
-- Table structure for table `materias`
--

CREATE TABLE `materias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materias`
--

INSERT INTO `materias` (`id`, `nombre`, `fecha_creacion`) VALUES
(9, 'Estructura de Datos', '2025-05-31 06:29:08'),
(10, 'Base de Datos', '2025-05-31 06:29:22'),
(11, 'Sistemas Operativos', '2025-05-31 06:29:30'),
(12, 'Ecuaciones Diferenciales', '2025-05-31 06:29:38'),
(13, 'Ingeniería de Software III', '2025-05-31 06:29:46'),
(15, 'Matematicas Generales', '2025-05-31 15:05:50');

-- --------------------------------------------------------

--
-- Table structure for table `materia_profesor`
--

CREATE TABLE `materia_profesor` (
  `grupo_id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materia_profesor`
--

INSERT INTO `materia_profesor` (`grupo_id`, `profesor_id`, `fecha_asignacion`) VALUES
(9, 32, '2025-05-31 06:31:02'),
(10, 32, '2025-05-31 06:37:08'),
(11, 32, '2025-05-31 06:37:20'),
(11, 37, '2025-05-31 06:36:30'),
(12, 37, '2025-05-31 06:36:40'),
(13, 32, '2025-05-31 06:36:35'),
(14, 32, '2025-05-31 06:36:38'),
(15, 37, '2025-05-31 06:46:13'),
(16, 32, '2025-05-31 15:06:06');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `rol` enum('Estudiante','Profesor','Administrador') NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `correo_electronico` varchar(100) NOT NULL,
  `id_carnet` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `zona_horaria` varchar(32) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `rol`, `nombre_completo`, `correo_electronico`, `id_carnet`, `contrasena`, `fecha_registro`, `zona_horaria`, `foto_perfil`) VALUES
(27, 'Estudiante', 'Andres Gomez Martinez', 'andres.gomez@udenar.edu.co', '223151040', '$2y$10$rRt17/ScXFDV9P.0CQPkK.TqYDrQfLRaY7CnBFrdJ8wo7gvyPFyvq', '2025-05-31 06:22:36', NULL, NULL),
(28, 'Estudiante', 'Catalina Rojas Herrera', 'catalina.rojas@udenar.edu.co', '223151042', '$2y$10$DGR957sFnERHUdr/JZ5x0Ol2x66Xgw0LXGmZUpWb/9BYT2N8LGGSa', '2025-05-31 06:23:19', NULL, NULL),
(29, 'Estudiante', 'Diego Palacios López', 'diego.palacios@udenar.edu.co', '223151043', '$2y$10$WGcpZjVxnkmI7/nOpUNVAOsSXGqFNeDwmGL2I.KI3JuG0TMOSovXO', '2025-05-31 06:23:50', NULL, NULL),
(30, 'Estudiante', 'María Fernanda Muñoz', 'maria.munoz@unal.edu.co', '223151044', '$2y$10$uuTR55W01.uU.J.OAxIXFuekhgLNjDK94MJX01IvjKS9wE3zrAvvq', '2025-05-31 06:24:36', NULL, NULL),
(31, 'Estudiante', 'Santiago Ramírez Pérez', 'santiago.ramirez@udenar.edu.co', '223151045', '$2y$10$VCMPy90.xcH9m.2mYXrLPuz8NLulnc8bB2hzbv2i89VyeUwOl6RNa', '2025-05-31 06:25:08', NULL, NULL),
(32, 'Profesor', 'Patricia Delgado López', 'patricia.delgado@udenar.edu.co', 'P1001', '$2y$10$wz3mtTRKG2IYKnVv20CfUeedSMgDYoVAKQWO6yusUWpz58178ofGe', '2025-05-31 06:26:01', NULL, NULL),
(33, 'Estudiante', 'Carlos Aurelio Rodriguez', 'cr7@udenar.edu.co', 'P1002', '$2y$10$Va0E5VC4vS4fGY9JIvl3gOIClkVkMMdW.jMPa74BRQTHJPXwaKVTy', '2025-05-31 06:27:08', NULL, NULL),
(34, 'Estudiante', 'Laura Fernanda Rodríguez', 'laura.rodriguez@udenar.edu.co', 'P1003', '$2y$10$442HXbvZaStEZR/BmsEg2OEqEIzWDRA1PLrBuJoYr.zMUBft1lZKO', '2025-05-31 06:27:39', NULL, NULL),
(35, 'Administrador', 'James David Solarte Estacio', 'jdsolarte23a@udenar.edu.co', '223151041', '$2y$10$8FlON.g3PFHKqMyxG8yBMu6HoEfryzLsic.N/AdGamf12fBhfOiAa', '2025-05-31 06:28:15', NULL, NULL),
(37, 'Profesor', 'Ana María Quintero', 'ana.quintero@udenar.edu.co', 'P1009', '$2y$10$Q6LpVaJER.TJIQgjUPpfeOfHZzxazlIi.ezLDXIYjk0dWnuPBHPYa', '2025-05-31 06:33:08', NULL, NULL),
(38, 'Estudiante', 'Ariel Santiago Pineda', 'Pariel@udenar.edu.co', 'P1010', '$2y$10$Am7A8cQINdHDmGLlQaoUHuz.OU6IraXdPCyRlaaNZDJSlnaczz.WC', '2025-05-31 06:33:45', NULL, NULL),
(39, 'Estudiante', 'Sergio Ruano', 'Saruano@gmil.com', '223151053', '$2y$10$T6zGBQnULw00Uxq.19yUR.gqTlacxCXGvr2Z7a74Pg8f.qWeui3jy', '2025-05-31 15:07:22', NULL, NULL),
(40, 'Estudiante', 'Juan David', 'jd@gmail.com', '123', '$2y$10$WlLZK93gleym944YaHgF5.1AXqBVMR.PNWxlIIwfAoIXH//0NpRle', '2025-06-03 01:43:25', NULL, NULL),
(41, 'Estudiante', 'michael', 'm@gmail.com', '11234553', '$2y$10$BPk8zbrgAIHox3z8cbqNsOu4j.0gX9ggjtfKM7K6hyfN8mdxK1Ft6', '2025-06-04 01:11:50', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `estudiante_grupo_fecha` (`estudiante_id`,`grupo_id`,`fecha`);

--
-- Indexes for table `grupos`
--
ALTER TABLE `grupos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `materia_id` (`materia_id`);

--
-- Indexes for table `grupo_estudiante`
--
ALTER TABLE `grupo_estudiante`
  ADD PRIMARY KEY (`grupo_id`,`estudiante_id`),
  ADD KEY `estudiante_id` (`estudiante_id`);

--
-- Indexes for table `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `materia_profesor`
--
ALTER TABLE `materia_profesor`
  ADD PRIMARY KEY (`grupo_id`,`profesor_id`),
  ADD KEY `profesor_id` (`profesor_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo_electronico` (`correo_electronico`),
  ADD UNIQUE KEY `id_carnet` (`id_carnet`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `grupos`
--
ALTER TABLE `grupos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `materias`
--
ALTER TABLE `materias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `grupos`
--
ALTER TABLE `grupos`
  ADD CONSTRAINT `grupos_ibfk_1` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grupo_estudiante`
--
ALTER TABLE `grupo_estudiante`
  ADD CONSTRAINT `grupo_estudiante_ibfk_1` FOREIGN KEY (`grupo_id`) REFERENCES `grupos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grupo_estudiante_ibfk_2` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `materia_profesor`
--
ALTER TABLE `materia_profesor`
  ADD CONSTRAINT `materia_profesor_ibfk_1` FOREIGN KEY (`grupo_id`) REFERENCES `grupos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `materia_profesor_ibfk_2` FOREIGN KEY (`profesor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
