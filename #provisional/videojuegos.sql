-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-02-2026 a las 10:42:47
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
-- Base de datos: `videojuegos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id_carrito` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_videojuego` int(11) NOT NULL,
  `cantidad` int(11) DEFAULT 1,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id_carrito`, `id_usuario`, `id_videojuego`, `cantidad`, `fecha_agregado`) VALUES
(50, 14, 2, 10, '2026-02-19 12:40:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `used`) VALUES
(1, 'andrea.garciagonzalez28@gmail.com', '51493d34f6d26a9426e02146fdf8d977c726d69518280411332c45e93dba5076', '2026-02-19 09:04:11', 1),
(2, 'andrea.garciagonzalez28@gmail.com', 'dfcd5d95032fc681af3dee48fbfd38d56546a96eb4fc37d7df198db7061092b9', '2026-02-19 09:04:51', 1),
(3, 'andreapracticascreartia@gmail.com', 'b387010c71da77d3a4dcf7d3db77fc11bfea3fb9e8ddb727684efdf7527bb40a', '2026-02-19 09:07:08', 1),
(4, 'andreapracticascreartia@gmail.com', 'cdea3c28be49385ca747e26ed883aa0c96fe6b4fbf98c96526d5ea2109c9685e', '2026-02-19 09:10:01', 1),
(5, 'andreapracticascreartia@gmail.com', '5fa3ad0500b333f15ce8305cf3ed878c26fff14baafe4da12825ced1b993d403', '2026-02-19 09:14:58', 1),
(6, 'andreapracticascreartia@gmail.com', '7c84abfe70212bf4e7a5815f67860552a0db4d6d219fa9bd553e4a5dc40f7533', '2026-02-19 09:17:40', 1),
(7, 'andreapracticascreartia@gmail.com', '236738956f75aa0bd42d787b17bea1b38c3dfece614db6d3ebf328bc41f69066', '2026-02-19 09:19:15', 1),
(8, 'andreapracticascreartia@gmail.com', 'ff0d3b26f64a17ba3eebdd548de0f4f2a431bfa9a51b10a0cda70782f6c96494', '2026-02-19 09:27:09', 1),
(9, 'andreapracticascreartia@gmail.com', 'dd73d126bc76f7ce46d435fa0c8cb93e887f5e95b3378964bcda6720f3a8276f', '2026-02-19 09:51:25', 1),
(10, 'andreapracticascreartia@gmail.com', '1dbc7aac7587069cf068de311f7d074b214837743fe0ecb055ba6c2ca2a5c397', '2026-02-19 09:51:55', 1),
(11, 'andreapracticascreartia@gmail.com', '5730150b6a0c574f7d6cf79003890fadf862c0532aab921b24f49591ad3314dd', '2026-02-19 09:52:07', 1),
(12, 'andreapracticascreartia@gmail.com', '56d38d8fb8e4aee97cc1ace6c2fdbb84dd0c217b444e53df1413a625f1c16c41', '2026-02-19 09:53:13', 1),
(13, 'andreapracticascreartia@gmail.com', '4a2cae62ba6cf66ac4214a69ff6918489cb4ffcc6a407aa06be14f5e03681cf1', '2026-02-19 09:55:06', 1),
(14, 'andreapracticascreartia@gmail.com', 'efce668527c4b391fffd081d4013b8b8ad1980afe5cedfc48c184b4040bae1c3', '2026-02-19 09:56:50', 1),
(15, 'andrea.garciagonzalez28@gmail.com', '6ca3f6b1dab47f70576784c4739cbfda65f57b7e994f4931354571c991315e9e', '2026-02-19 13:41:11', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` varchar(50) DEFAULT 'pendiente',
  `stripe_session_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_items`
--

CREATE TABLE `pedido_items` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `videojuego_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `screenshots`
--

CREATE TABLE `screenshots` (
  `id_screenshot` int(11) NOT NULL,
  `id_videojuego` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `orden` int(11) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `screenshots`
--

INSERT INTO `screenshots` (`id_screenshot`, `id_videojuego`, `nombre_archivo`, `orden`, `fecha_creacion`) VALUES
(1, 1, 'zelda-1.jpg', 1, '2026-02-16 08:43:04'),
(2, 1, 'zelda-2.jpg', 2, '2026-02-16 08:43:04'),
(3, 1, 'zelda-3.jpg', 3, '2026-02-16 08:43:04'),
(4, 1, 'zelda-4.jpg', 4, '2026-02-16 08:43:04'),
(5, 2, 'war-1.jpg', 1, '2026-02-16 08:43:04'),
(6, 2, 'war-2.jpg', 2, '2026-02-16 08:43:04'),
(7, 2, 'war-3.jpg', 3, '2026-02-16 08:43:04'),
(8, 2, 'war-4.jpg', 4, '2026-02-16 08:43:04'),
(9, 3, 'gta-1.jpg', 1, '2026-02-16 08:43:04'),
(10, 3, 'gta-2.jpg', 2, '2026-02-16 08:43:04'),
(11, 3, 'gta-3.jpg', 3, '2026-02-16 08:43:04'),
(12, 3, 'gta-4.jpg', 4, '2026-02-16 08:43:04'),
(13, 4, 'minecraft-1.jpg', 1, '2026-02-16 08:43:04'),
(14, 4, 'minecraft-2.jpg', 2, '2026-02-16 08:43:04'),
(15, 4, 'minecraft-3.jpg', 3, '2026-02-16 08:43:04'),
(16, 4, 'minecraft-4.jpg', 4, '2026-02-16 08:43:04'),
(17, 5, 'fifa23-1.jpg', 1, '2026-02-16 08:43:04'),
(18, 5, 'fifa23-2.jpg', 2, '2026-02-16 08:43:04'),
(19, 5, 'fifa23-3.jpg', 3, '2026-02-16 08:43:04'),
(20, 5, 'fifa23-4.jpg', 4, '2026-02-16 08:43:04'),
(21, 6, 'redII-1.jpg', 1, '2026-02-16 08:43:04'),
(22, 6, 'redII-2.jpg', 2, '2026-02-16 08:43:04'),
(23, 6, 'redII-3.jpg', 3, '2026-02-16 08:43:04'),
(24, 6, 'redII-4.jpg', 4, '2026-02-16 08:43:04'),
(25, 7, 'eldenRing-1.jpg', 1, '2026-02-16 08:43:04'),
(26, 7, 'eldenRing-2.jpg', 2, '2026-02-16 08:43:04'),
(27, 7, 'eldenRing-3.jpg', 3, '2026-02-16 08:43:04'),
(28, 7, 'eldenRing-4.jpg', 4, '2026-02-16 08:43:04'),
(29, 8, 'marioOdy-1.jpg', 1, '2026-02-16 08:43:04'),
(30, 8, 'marioOdy-2.jpg', 2, '2026-02-16 08:43:04'),
(31, 8, 'marioOdy-3.jpg', 3, '2026-02-16 08:43:04'),
(32, 8, 'marioOdy-4.jpg', 4, '2026-02-16 08:43:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` set('admin','cliente','invitado') DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `email`, `contrasena`, `rol`, `fecha_creacion`) VALUES
(2, 'user2', 'user2@dominio.es', '$2y$10$SAzwM7sZj.bImQcYu182TeUp8PP1ILO.vByevZfzhUs4KN9gSuHFm', 'cliente', '2026-02-13 09:41:30'),
(3, 'user3', 'user3@dom.com', '$2y$10$j7GFiW4YlIsPyUE6xJPRgutGf/P0bVY.2t9AN8jBwDWM5Tku019BK', 'cliente', '2026-02-13 09:43:46'),
(4, 'userr4', 'user4@dom.es', '$2y$10$PDIygqjouI/1.27aEkPzceNtRPPBSmjzbwg1xLImAhryJAZP/LF/G', 'cliente', '2026-02-13 09:52:09'),
(5, 'user5', 'user5@gmal.es', '$2y$10$BnoCs4J5gT7ZSkFK/Drgx.FxI7MQAAcTnjnEvzvobAu0xsNA3Pmtq', 'cliente', '2026-02-13 09:54:35'),
(6, 'user6', 'user6@gg.com', '$2y$10$fTJkZT6m.LEzY6/3Wf3NEOpXbfvaE9MVynLFKIxB4tlR11RxD.ldK', 'cliente', '2026-02-13 09:57:08'),
(7, 'user7', 'user7@gg.es', '$2y$10$6e9gXtBeeYmFf3AQ6zDpnOVBlD2umNYp8uF169vNBrm8bmxS/zXH6', 'cliente', '2026-02-13 10:00:18'),
(8, 'admin1', 'admin1@gmail.com', '$2y$10$rg0FRu//Bl8OkC0X5I4sMeoFqPejlDfrCwemwLF6yjZahxqtWuEPG', 'admin', '2026-02-13 11:51:24'),
(9, 'admin', 'admin@gg.es', '$2y$10$Wn0C6Q.QvhB9LwXM.XYR7e90wjbE/ZljlpmImAhFyPrK1tXN5zsfy', 'admin', '2026-02-13 12:12:28'),
(13, 'andy', 'prueba@gmail.com', '$2y$10$jJIYhcCc6MheWfHhFsdTTOzqPb8t3rOnYpo0A.cDVy78dN8M.G6oa', 'cliente', '2026-02-17 09:57:11'),
(14, 'andy', 'andrea.garciagonzalez28@gmail.com', '$2y$10$DyHoAGnbYOThtZ6BrIZsXep.9/D8zFhIop1gMWtc0.1dD2R3BCGdW', 'cliente', '2026-02-19 06:47:48'),
(16, 'user2', 'user2@gmail.com', '$2y$10$/Dda90xJ79nPjAx6WZJpb.clT3yn0NWoH7mfH2IjWYwKsexf5/VOu', 'cliente', '2026-02-19 08:42:05'),
(17, 'pepito', 'pepito@pp.es', '$2y$10$NuV6LU0DJC9.pNwl/OZJS.8laJy2YQk0Ptk0rUyIedcTieS4Wf1d.', 'cliente', '2026-02-19 09:06:20'),
(18, 'pepe', 'pepe@pp.com', '$2y$10$feA/N/pjxGHaTOYv.0SBfuO1VQKY2ARrP5I.totxIZ4oZAUf6165C', 'cliente', '2026-02-19 09:06:48'),
(19, 'pepita', 'pepita@pp.es', '$2y$10$ll4DVHMk.UVHcMEX1lDUpug0O4Zo6O1k4kdso2a2Lfyg0k3Cmdm2.', 'cliente', '2026-02-19 09:19:56'),
(21, 'admin3', 'admin3@gmail.com', '$2y$10$Kr6lRDI2uirQPy7qfORsJ.olKJBYSeOAkihcYQ2QxN0gYR/ydtmeq', 'admin', '2026-02-19 09:59:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `videojuego`
--

CREATE TABLE `videojuego` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `desarrollador` varchar(150) DEFAULT NULL,
  `genero` varchar(100) DEFAULT NULL,
  `plataforma` varchar(100) DEFAULT NULL,
  `fecha_lanzamiento` date DEFAULT NULL,
  `precio` decimal(6,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `feature` varchar(500) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `imagen_portada` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `videojuego`
--

INSERT INTO `videojuego` (`id`, `titulo`, `descripcion`, `desarrollador`, `genero`, `plataforma`, `fecha_lanzamiento`, `precio`, `stock`, `feature`, `fecha_creacion`, `imagen_portada`) VALUES
(1, 'The Legend of Zelda: Breath of the Wild', 'Embark on an epic adventure across Hyrule in this revolutionary open-world game. Explore vast landscapes, solve ingenious puzzles in ancient shrines, and face perilous challenges.', 'Nintendo', 'Adventure', 'Nintendo Switch', '2017-03-03', 59.99, 15, 'Single-player, Vast Open World, Physics and Chemistry Engine, Weapon Durability, Survival Mechanics, Expansion Pass Content', '2026-02-16 07:07:54', 'zelda.png'),
(2, 'God of War', 'Join Kratos and his son Atreus on an emotional journey through the Norse lands steeped in myth and legend. This reimagining of the saga combines brutal combat with a profound narrative about the past.', 'Santa Monica Studio', 'Action', 'PlayStation 4', '2018-04-20', 49.99, 0, 'Single-player, Steam Achievements, Steam Cloud, HDR available, Family Sharing, Accessibility Features', '2026-02-16 07:07:54', 'war.png'),
(3, 'Grand Theft Auto V', 'Experience three intertwined stories in the vibrant city of Los Santos and its surrounding areas. Alternate between Michael, Franklin, and Trevor, three unique criminals whose paths cross in daring he', 'Rockstar Games', 'Action', 'PC', '2013-09-17', 29.99, 14, 'Single-player, Online PvP, Online Co-op, Steam Achievements, In-App Purchases, Remote Play on Phone, Remote Play on Tablet', '2026-02-16 07:07:54', 'gta.png'),
(4, 'Minecraft', 'Unleash your creativity in an infinite world made of blocks. Build impossible structures, explore deep caves, survive perilous nights, and collaborate with friends in', 'Mojang Studios', 'Sandbox', 'Cross Platform', '2011-11-18', 19.99, 23, 'Multi-plyer, Mining Resources, Crafting Tools, Exploring Dimensions, Fighting Mobs ', '2026-02-16 07:07:54', 'minecraft.png'),
(5, 'FIFA 23', 'Experience the most realistic football with HyperMotion2 technology that captures every authentic movement of the game. Play with over 19,000 players, 700 teams, and 100 stadiums in this football simu', 'EA Sports', 'Sports', 'PlayStation 5', '2022-09-30', 69.99, 22, 'Multi-player, PS Plus required for online play, Optional in-game purchases, Supports up to 22 online players with PS Plus, Optional online play, Remote Play compatible', '2026-02-16 07:07:54', 'fifa23.png'),
(6, 'Red Dead Redemption 2', 'Experience the epic story of Arthur Morgan, outlaw of Dutch van der Linde\'s gang in the twilight of the Wild West. Explore a vast, vibrant open world, from frontier towns to wastelands.', 'Rockstar Games', 'Adventure', 'PlayStation 4', '2018-10-26', 39.99, 14, 'Single-player, Online PvP, Online Co-op, Steam Achievements, In-App Purchases, Remote Play on Phone, Remote Play on Tablet', '2026-02-16 07:07:54', 'redII.png'),
(7, 'Elden Ring', 'Explore the Middle Lands in this open-world action RPG created by FromSoftware and George R.R. Martin. Face epic bosses, discover hidden dungeons, and forge your own destiny in a world of adventure.', 'FromSoftware', 'RPG', 'PC', '2022-02-25', 59.99, 18, 'Single-player, Online PvP, Online Co-op, Steam Achievements, Steam Trading Cards, Steam Cloud, Family Sharing, Accessibility Features', '2026-02-16 07:07:54', 'eldenRing.png'),
(8, 'Super Mario Odyssey', 'Join Mario on an epic 3D adventure through unique and colorful kingdoms to stop Bowser and Peach\'s wedding. Use Cappy, your new hat companion, to capture enemies and objects, earning them rewards.', 'Nintendo', 'Cross Platform', 'Nintendo Switch', '2017-10-27', 49.99, 14, 'Multi-player, Cappy & Capture Mechanic, Sandbox Exploration, Movement & Controls, 2-Player Co-op, Costumes & Customization', '2026-02-16 07:07:54', 'marioOdy.png');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id_carrito`),
  ADD UNIQUE KEY `unique_cart_item` (`id_usuario`,`id_videojuego`),
  ADD KEY `id_videojuego` (`id_videojuego`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `screenshots`
--
ALTER TABLE `screenshots`
  ADD PRIMARY KEY (`id_screenshot`),
  ADD KEY `id_videojuego` (`id_videojuego`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`email`) USING BTREE;

--
-- Indices de la tabla `videojuego`
--
ALTER TABLE `videojuego`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `screenshots`
--
ALTER TABLE `screenshots`
  MODIFY `id_screenshot` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `videojuego`
--
ALTER TABLE `videojuego`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`id_videojuego`) REFERENCES `videojuego` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `screenshots`
--
ALTER TABLE `screenshots`
  ADD CONSTRAINT `screenshots_ibfk_1` FOREIGN KEY (`id_videojuego`) REFERENCES `videojuego` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
