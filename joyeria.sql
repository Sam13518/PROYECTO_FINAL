-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: mysql-joyeria:3306
-- Tiempo de generación: 01-12-2025 a las 21:06:35
-- Versión del servidor: 8.0.44
-- Versión de PHP: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `joyeria`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admins`
--

CREATE TABLE `admins` (
  `id_admin` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `admins`
--

INSERT INTO `admins` (`id_admin`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Admin Principal', 'admin@ecle.com', '000', '2025-11-24 22:15:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `buy_details`
--

CREATE TABLE `buy_details` (
  `id_detail` int NOT NULL,
  `id_buy` int NOT NULL,
  `id_product` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `buy_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `buy_details`
--

INSERT INTO `buy_details` (`id_detail`, `id_buy`, `id_product`, `quantity`, `price`, `buy_date`) VALUES
(1, 10, 13, 1, 399.00, '2025-11-27 00:29:00'),
(2, 11, 5, 1, 349.00, '2025-11-27 00:29:19'),
(3, 11, 8, 1, 399.00, '2025-11-27 00:29:19'),
(4, 12, 13, 1, 399.00, '2025-11-27 01:02:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `buy_history`
--

CREATE TABLE `buy_history` (
  `id_buy` int NOT NULL,
  `id_user` int NOT NULL,
  `buy_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `buy_history`
--

INSERT INTO `buy_history` (`id_buy`, `id_user`, `buy_date`, `total_amount`) VALUES
(10, 1, '2025-11-27 00:29:00', 399.00),
(11, 1, '2025-11-27 00:29:19', 748.00),
(12, 1, '2025-11-27 01:02:30', 399.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edit_history`
--

CREATE TABLE `edit_history` (
  `id_edit` int NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `action_type` tinyint NOT NULL COMMENT '1=Edit, 2=Delete, 3=Create',
  `edited_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `edit_history`
--

INSERT INTO `edit_history` (`id_edit`, `product_name`, `action_type`, `edited_at`) VALUES
(1, 'ÉCLÉ Timeless Watch', 1, '2025-11-27 04:05:17'),
(2, 'La Fleur', 3, '2025-11-27 04:17:55'),
(3, 'The Amber Cuff', 1, '2025-11-27 04:25:36'),
(4, 'L\'Ensemble Riviera', 3, '2025-11-27 04:37:26'),
(5, 'La Fleur', 1, '2025-11-27 04:47:18'),
(6, 'Le Lien', 1, '2025-11-27 04:48:14'),
(7, 'Le Lien', 1, '2025-11-27 04:48:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id_product` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `photo` longblob,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL,
  `material` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `manufacturer` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `origin` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



--
-- Estructura de tabla para la tabla `shopping_cart`
--

CREATE TABLE `shopping_cart` (
  `id_cart` int NOT NULL,
  `id_user` int NOT NULL,
  `id_product` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `birth_date` date DEFAULT NULL,
  `card_number` varchar(16) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `postal_address` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id_user`, `name`, `email`, `password`, `birth_date`, `card_number`, `postal_address`, `created_at`) VALUES
(1, 'Ana Pérez', 'ana.perez@gmail.com', '1234', '1999-05-14', '1234567812345678', 'CDMX, Méx', '2025-11-26 01:59:10'),
(2, 'Lara Hdz', 'larah@gmail.com', '9876', '2001-06-21', '6785342732873276', 'Anahuac 45', '2025-11-27 02:12:58'),
(3, 'Renata Perez', 'reperez@gmail.com', '0000', '2004-07-06', '6789546734567825', 'Ciudad de Mexico, Mexico', '2025-11-27 02:15:56');
