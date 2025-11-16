-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 16, 2025 at 05:37 PM
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
-- Database: `travelgo`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `booking_reference` varchar(20) NOT NULL,
  `flight_type` enum('one_way','round_trip') NOT NULL,
  `flight_id` int(11) DEFAULT NULL,
  `flight_details` text DEFAULT NULL,
  `selected_seats` text DEFAULT NULL,
  `seat_charges` decimal(10,2) DEFAULT 0.00,
  `passenger_info` text DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `coupon_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `booking_reference`, `flight_type`, `flight_id`, `flight_details`, `selected_seats`, `seat_charges`, `passenger_info`, `billing_address`, `payment_method`, `total_amount`, `status`, `created_at`, `updated_at`, `coupon_code`, `discount_amount`) VALUES
(1, NULL, 'TG8C1EF0F6E8', 'round_trip', 2, '{\"flight_id\":\"2\",\"flight_type\":\"round_trip\",\"flight_data\":{\"id\":2,\"flight_number\":\"JL736\",\"airline_code\":\"JL\",\"airline_name\":\"Japan Airlines\",\"origin\":\"Hong Kong\",\"destination\":\"Tokyo\",\"departure_date\":\"2025-10-15\",\"return_date\":\"2025-10-22\",\"departure_time\":\"14:30:00\",\"arrival_time\":\"19:00:00\",\"return_departure_time\":\"09:00:00\",\"return_arrival_time\":\"13:30:00\",\"duration\":\"4h 30m\",\"return_duration\":\"4h 30m\",\"stops\":0,\"return_stops\":0,\"price\":\"3500.00\",\"class\":\"economy\",\"seats_available\":32,\"created_at\":\"2025-10-09 13:30:10\"},\"passengers\":\"1\",\"selected_seats\":\"[\\\"6E\\\"]\",\"seat_charges\":\"0\",\"base_amount\":3500,\"tax_amount\":105,\"duration\":\"4h 30m\",\"return_duration\":\"4h 30m\"}', NULL, 0.00, '{\"passenger_1\":{\"first_name\":\"Tin\",\"last_name\":\"Yan To\",\"email\":\"polo1st2023@gmail.com\",\"phone\":\"+852 55441153\",\"gender\":\"male\",\"dob\":\"2007-10-01\"}}', '{\"street\":\"kkek\",\"city\":\"Central\",\"state\":\"Yau Tsim Mong\",\"zip\":\"999077\",\"country\":\"Hong Kong SAR\"}', 'paypal', 3605.00, 'confirmed', '2025-11-09 19:49:36', '2025-11-09 19:49:36', NULL, 0.00),
(2, NULL, 'TGE665BB54AF', 'round_trip', 2, '{\"flight_id\":\"2\",\"flight_type\":\"round_trip\",\"flight_data\":{\"id\":2,\"flight_number\":\"JL736\",\"airline_code\":\"JL\",\"airline_name\":\"Japan Airlines\",\"origin\":\"Hong Kong\",\"destination\":\"Tokyo\",\"departure_date\":\"2025-10-15\",\"return_date\":\"2025-10-22\",\"departure_time\":\"14:30:00\",\"arrival_time\":\"19:00:00\",\"return_departure_time\":\"09:00:00\",\"return_arrival_time\":\"13:30:00\",\"duration\":\"4h 30m\",\"return_duration\":\"4h 30m\",\"stops\":0,\"return_stops\":0,\"price\":\"3500.00\",\"class\":\"economy\",\"seats_available\":31,\"created_at\":\"2025-10-09 13:30:10\"},\"passengers\":\"1\",\"selected_seats\":\"[\\\"2D\\\"]\",\"seat_charges\":\"50\",\"base_amount\":3500,\"tax_amount\":105,\"duration\":\"4h 30m\",\"return_duration\":\"4h 30m\"}', NULL, 0.00, '{\"passenger_1\":{\"first_name\":\"Tin\",\"last_name\":\"Yan To\",\"email\":\"polo1st2023@gmail.com\",\"phone\":\"+852 55441153\",\"gender\":\"male\",\"dob\":\"2007-10-01\"}}', '{\"street\":\"kkek\",\"city\":\"Central\",\"state\":\"Yau Tsim Mong\",\"zip\":\"999077\",\"country\":\"Hong Kong SAR\"}', 'paypal', 3655.00, 'confirmed', '2025-11-09 21:50:28', '2025-11-09 21:50:28', NULL, 0.00),
(3, NULL, 'TGF01BF4660A', 'round_trip', 4, '{\"flight_id\":\"4\",\"flight_type\":\"round_trip\",\"flight_data\":{\"id\":4,\"flight_number\":\"CX451\",\"airline_code\":\"CX\",\"airline_name\":\"Cathay Pacific\",\"origin\":\"Hong Kong\",\"destination\":\"Bangkok\",\"departure_date\":\"2025-10-15\",\"return_date\":\"2025-10-22\",\"departure_time\":\"09:30:00\",\"arrival_time\":\"11:45:00\",\"return_departure_time\":\"13:00:00\",\"return_arrival_time\":\"17:15:00\",\"duration\":\"2h 15m\",\"return_duration\":\"4h 15m\",\"stops\":0,\"return_stops\":1,\"price\":\"2800.00\",\"class\":\"economy\",\"seats_available\":50,\"created_at\":\"2025-10-09 13:30:10\"},\"passengers\":\"1\",\"selected_seats\":\"[\\\"1E\\\"]\",\"seat_charges\":\"50\",\"base_amount\":2800,\"tax_amount\":84,\"applied_coupon\":{\"id\":1,\"code\":\"WELCOME15\",\"description\":\"15% OFF for your first flight booking!\",\"discount_type\":\"percentage\",\"discount_value\":\"15.00\",\"min_amount\":\"100.00\",\"max_discount\":\"500.00\",\"valid_from\":\"2025-11-11 00:05:37\",\"valid_until\":\"2026-12-31 23:59:59\",\"usage_limit\":1,\"used_count\":0,\"for_new_users\":1,\"is_active\":1,\"created_at\":\"2025-11-11 00:05:37\"},\"discount_amount\":420,\"duration\":\"2h 15m\",\"return_duration\":\"4h 15m\"}', NULL, 0.00, '{\"passenger_1\":{\"first_name\":\"Tin\",\"last_name\":\"Yan  To\",\"email\":\"polo1st2023@gmail.com\",\"phone\":\"+852 55441153\",\"gender\":\"male\",\"dob\":\"2007-11-01\"}}', '{\"street\":\"1234567890\",\"city\":\"Causeway Bay\",\"state\":\"Sham Shui Po\",\"zip\":\"999077\",\"country\":\"Hong Kong SAR\"}', 'paypal', 2514.00, 'confirmed', '2025-11-10 17:26:20', '2025-11-10 17:26:20', NULL, 0.00),
(4, NULL, 'TG2C6B6B5725', 'round_trip', 2, '{\"flight_id\":\"2\",\"flight_type\":\"round_trip\",\"flight_data\":{\"id\":2,\"flight_number\":\"JL736\",\"airline_code\":\"JL\",\"airline_name\":\"Japan Airlines\",\"origin\":\"Hong Kong\",\"destination\":\"Tokyo\",\"departure_date\":\"2025-10-15\",\"return_date\":\"2025-10-22\",\"departure_time\":\"14:30:00\",\"arrival_time\":\"19:00:00\",\"return_departure_time\":\"09:00:00\",\"return_arrival_time\":\"13:30:00\",\"duration\":\"4h 30m\",\"return_duration\":\"4h 30m\",\"stops\":0,\"return_stops\":0,\"price\":\"3500.00\",\"class\":\"economy\",\"seats_available\":30,\"created_at\":\"2025-10-09 13:30:10\"},\"passengers\":\"1\",\"selected_seats\":\"[\\\"3F\\\"]\",\"seat_charges\":\"0\",\"base_amount\":3500,\"tax_amount\":105,\"applied_coupon\":{\"id\":1,\"code\":\"WELCOME15\",\"description\":\"15% OFF for your first flight booking!\",\"discount_type\":\"percentage\",\"discount_value\":\"15.00\",\"min_amount\":\"100.00\",\"max_discount\":\"500.00\",\"valid_from\":\"2025-11-11 00:05:37\",\"valid_until\":\"2026-12-31 23:59:59\",\"usage_limit\":1,\"used_count\":0,\"for_new_users\":1,\"is_active\":1,\"created_at\":\"2025-11-11 00:05:37\"},\"discount_amount\":\"500.00\",\"duration\":\"4h 30m\",\"return_duration\":\"4h 30m\"}', NULL, 0.00, '{\"passenger_1\":{\"first_name\":\"To\",\"last_name\":\"To\",\"email\":\"polo2nd2024@gmail.com\",\"phone\":\"+852 55441153\",\"gender\":\"male\",\"dob\":\"2007-11-01\"}}', '{\"street\":\"1234567890\",\"city\":\"Hong Kong\",\"state\":\"Wong Tai Sin\",\"zip\":\"999077\",\"country\":\"Hong Kong SAR\"}', 'paypal', 3105.00, 'confirmed', '2025-11-11 11:39:59', '2025-11-11 11:39:59', NULL, 0.00),
(5, 1, 'TGC040C1B4AD', 'round_trip', 2, '{\"flight_id\":\"2\",\"flight_type\":\"round_trip\",\"flight_data\":{\"id\":2,\"flight_number\":\"JL736\",\"airline_code\":\"JL\",\"airline_name\":\"Japan Airlines\",\"origin\":\"Hong Kong\",\"destination\":\"Tokyo\",\"departure_date\":\"2025-10-15\",\"return_date\":\"2025-10-22\",\"departure_time\":\"14:30:00\",\"arrival_time\":\"19:00:00\",\"return_departure_time\":\"09:00:00\",\"return_arrival_time\":\"13:30:00\",\"duration\":\"4h 30m\",\"return_duration\":\"4h 30m\",\"stops\":0,\"return_stops\":0,\"price\":\"3500.00\",\"class\":\"economy\",\"seats_available\":29,\"created_at\":\"2025-10-09 13:30:10\"},\"passengers\":\"1\",\"selected_seats\":\"[\\\"3E\\\"]\",\"seat_charges\":\"0\",\"base_amount\":3500,\"tax_amount\":105,\"applied_coupon\":{\"id\":1,\"code\":\"WELCOME15\",\"description\":\"15% OFF for your first flight booking!\",\"discount_type\":\"percentage\",\"discount_value\":\"15.00\",\"min_amount\":\"100.00\",\"max_discount\":\"500.00\",\"valid_from\":\"2025-11-11 00:05:37\",\"valid_until\":\"2026-12-31 23:59:59\",\"usage_limit\":1,\"used_count\":0,\"for_new_users\":1,\"is_active\":1,\"created_at\":\"2025-11-11 00:05:37\"},\"discount_amount\":\"500.00\",\"duration\":\"4h 30m\",\"return_duration\":\"4h 30m\"}', NULL, 0.00, '{\"passenger_1\":{\"first_name\":\"Tin\",\"last_name\":\"Yan To\",\"email\":\"polo1st2023@gmail.com\",\"phone\":\"+852 55441153\",\"gender\":\"male\",\"dob\":\"2007-10-01\"}}', '{\"street\":\"123456789990\",\"city\":\"Central\",\"state\":\"Sham Shui Po\",\"zip\":\"999077\",\"country\":\"Hong Kong SAR\"}', 'credit_card', 3105.00, 'confirmed', '2025-11-11 12:16:44', '2025-11-11 12:16:44', NULL, 0.00),
(6, 1, 'TG4C3D7F01BC', 'round_trip', 3, '{\"flight_id\":\"3\",\"flight_type\":\"round_trip\",\"flight_data\":{\"id\":3,\"flight_number\":\"NH812\",\"airline_code\":\"NH\",\"airline_name\":\"All Nippon Airways\",\"origin\":\"Hong Kong\",\"destination\":\"Tokyo\",\"departure_date\":\"2025-10-15\",\"return_date\":\"2025-10-22\",\"departure_time\":\"20:15:00\",\"arrival_time\":\"00:45:00\",\"return_departure_time\":\"16:30:00\",\"return_arrival_time\":\"21:00:00\",\"duration\":\"4h 30m\",\"return_duration\":\"4h 30m\",\"stops\":0,\"return_stops\":0,\"price\":\"3800.00\",\"class\":\"economy\",\"seats_available\":28,\"created_at\":\"2025-10-09 13:30:10\"},\"passengers\":\"2\",\"selected_seats\":\"[\\\"8D\\\",\\\"2D\\\"]\",\"seat_charges\":\"50\",\"base_amount\":7600,\"tax_amount\":228,\"applied_coupon\":null,\"discount_amount\":0,\"duration\":\"4h 30m\",\"return_duration\":\"4h 30m\"}', NULL, 0.00, '{\"passenger_1\":{\"first_name\":\"Tin\",\"last_name\":\"Yan To\",\"email\":\"polo1st2023@gmail.com\",\"phone\":\"+852 55441153\",\"gender\":\"male\",\"dob\":\"2007-10-09\"},\"passenger_2\":{\"first_name\":\"Tin Yan\",\"last_name\":\"To\",\"gender\":\"male\"}}', '{\"street\":\"123456789990\",\"city\":\"Central\",\"state\":\"Sham Shui Po\",\"zip\":\"999077\",\"country\":\"Hong Kong SAR\"}', 'paypal', 7878.00, 'confirmed', '2025-11-11 12:26:17', '2025-11-11 12:26:17', NULL, 0.00),
(7, 1, 'TGBE1DC7349B', 'round_trip', 5, '{\"flight_id\":\"5\",\"flight_type\":\"round_trip\",\"flight_data\":{\"id\":5,\"flight_number\":\"TG639\",\"airline_code\":\"TG\",\"airline_name\":\"Thai Airways\",\"origin\":\"Hong Kong\",\"destination\":\"Bangkok\",\"departure_date\":\"2025-10-15\",\"return_date\":\"2025-10-22\",\"departure_time\":\"16:45:00\",\"arrival_time\":\"19:00:00\",\"return_departure_time\":\"08:30:00\",\"return_arrival_time\":\"12:45:00\",\"duration\":\"2h 15m\",\"return_duration\":\"4h 15m\",\"stops\":0,\"return_stops\":1,\"price\":\"2600.00\",\"class\":\"economy\",\"seats_available\":38,\"created_at\":\"2025-10-09 13:30:10\"},\"passengers\":\"1\",\"selected_seats\":\"[\\\"1E\\\"]\",\"seat_charges\":\"50\",\"base_amount\":2600,\"tax_amount\":78,\"applied_coupon\":null,\"discount_amount\":0,\"duration\":\"2h 15m\",\"return_duration\":\"4h 15m\"}', NULL, 0.00, '{\"passenger_1\":{\"first_name\":\"Tin\",\"last_name\":\"Yan To\",\"email\":\"polo1st2023@gmail.com\",\"phone\":\"+852 55441153\",\"gender\":\"male\",\"dob\":\"2007-10-09\"}}', '{\"street\":\"123456789990\",\"city\":\"Central\",\"state\":\"Sham Shui Po\",\"zip\":\"999077\",\"country\":\"Hong Kong SAR\"}', 'paypal', 2728.00, 'confirmed', '2025-11-16 12:28:36', '2025-11-16 12:28:36', NULL, 0.00),
(8, 1, 'TGB2CDFC95C3', 'round_trip', 1, '{\"flight_id\":\"1\",\"flight_type\":\"round_trip\",\"flight_data\":{\"id\":1,\"flight_number\":\"CX888\",\"airline_code\":\"CX\",\"airline_name\":\"Cathay Pacific\",\"origin\":\"Hong Kong\",\"destination\":\"Tokyo\",\"departure_date\":\"2025-10-15\",\"return_date\":\"2025-10-22\",\"departure_time\":\"08:00:00\",\"arrival_time\":\"12:30:00\",\"return_departure_time\":\"14:00:00\",\"return_arrival_time\":\"18:30:00\",\"duration\":\"4h 30m\",\"return_duration\":\"4h 30m\",\"stops\":0,\"return_stops\":0,\"price\":\"3200.00\",\"class\":\"economy\",\"seats_available\":45,\"created_at\":\"2025-10-09 13:30:10\"},\"passengers\":\"1\",\"selected_seats\":\"[\\\"1E\\\"]\",\"seat_charges\":\"50\",\"base_amount\":3200,\"tax_amount\":96,\"applied_coupon\":null,\"discount_amount\":0,\"duration\":\"4h 30m\",\"return_duration\":\"4h 30m\"}', NULL, 0.00, '{\"passenger_1\":{\"first_name\":\"Tin\",\"last_name\":\"Yan To\",\"email\":\"polo1st2023@gmail.com\",\"phone\":\"+852 55441153\",\"gender\":\"male\",\"dob\":\"2007-10-09\"}}', '{\"street\":\"123456789990\",\"city\":\"Central\",\"state\":\"Sham Shui Po\",\"zip\":\"999077\",\"country\":\"Hong Kong SAR\"}', 'paypal', 3346.00, 'confirmed', '2025-11-16 13:19:59', '2025-11-16 13:19:59', NULL, 0.00),
(9, 1, 'TGD68AC8C098', 'round_trip', 4, '{\"flight_id\":\"4\",\"flight_type\":\"round_trip\",\"flight_data\":{\"id\":4,\"flight_number\":\"CX451\",\"airline_code\":\"CX\",\"airline_name\":\"Cathay Pacific\",\"origin\":\"Hong Kong\",\"destination\":\"Bangkok\",\"departure_date\":\"2025-10-15\",\"return_date\":\"2025-10-22\",\"departure_time\":\"09:30:00\",\"arrival_time\":\"11:45:00\",\"return_departure_time\":\"13:00:00\",\"return_arrival_time\":\"17:15:00\",\"duration\":\"2h 15m\",\"return_duration\":\"4h 15m\",\"stops\":0,\"return_stops\":1,\"price\":\"2800.00\",\"class\":\"economy\",\"seats_available\":49,\"created_at\":\"2025-10-09 13:30:10\"},\"passengers\":\"1\",\"selected_seats\":\"[\\\"1E\\\"]\",\"seat_charges\":\"50\",\"base_amount\":2800,\"tax_amount\":84,\"applied_coupon\":null,\"discount_amount\":0,\"duration\":\"2h 15m\",\"return_duration\":\"4h 15m\"}', NULL, 0.00, '{\"passenger_1\":{\"first_name\":\"Tin\",\"last_name\":\"Yan To\",\"email\":\"polo1st2023@gmail.com\",\"phone\":\"+852 55441153\",\"gender\":\"male\",\"dob\":\"2007-10-09\"}}', '{\"street\":\"123456789990\",\"city\":\"Central\",\"state\":\"Sham Shui Po\",\"zip\":\"999077\",\"country\":\"Hong Kong SAR\"}', 'paypal', 2934.00, 'confirmed', '2025-11-16 13:21:28', '2025-11-16 13:21:28', NULL, 0.00),
(10, 1, 'TG14D1643D6C', 'round_trip', 1, '{\"flight_id\":\"1\",\"flight_type\":\"round_trip\",\"flight_data\":{\"id\":1,\"flight_number\":\"CX888\",\"airline_code\":\"CX\",\"airline_name\":\"Cathay Pacific\",\"origin\":\"Hong Kong\",\"destination\":\"Tokyo\",\"departure_date\":\"2025-10-15\",\"return_date\":\"2025-10-22\",\"departure_time\":\"08:00:00\",\"arrival_time\":\"12:30:00\",\"return_departure_time\":\"14:00:00\",\"return_arrival_time\":\"18:30:00\",\"duration\":\"4h 30m\",\"return_duration\":\"4h 30m\",\"stops\":0,\"return_stops\":0,\"price\":\"3200.00\",\"class\":\"economy\",\"seats_available\":44,\"created_at\":\"2025-10-09 13:30:10\"},\"passengers\":\"1\",\"selected_seats\":\"[\\\"1E\\\"]\",\"seat_charges\":\"50\",\"base_amount\":3200,\"tax_amount\":96,\"applied_coupon\":null,\"discount_amount\":0,\"duration\":\"4h 30m\",\"return_duration\":\"4h 30m\"}', NULL, 0.00, '{\"passenger_1\":{\"first_name\":\"Tin\",\"last_name\":\"Yan To\",\"email\":\"polo1st2023@gmail.com\",\"phone\":\"+852 55441153\",\"gender\":\"male\",\"dob\":\"2007-10-09\"}}', '{\"street\":\"123456789990\",\"city\":\"Central\",\"state\":\"Sham Shui Po\",\"zip\":\"999077\",\"country\":\"Hong Kong SAR\"}', 'paypal', 3346.00, 'confirmed', '2025-11-16 13:28:45', '2025-11-16 13:28:45', NULL, 0.00),
(11, 1, 'TG68B3DD3E7B', 'round_trip', 5, '{\"flight_id\":\"5\",\"flight_type\":\"round_trip\",\"flight_data\":{\"id\":5,\"flight_number\":\"TG639\",\"airline_code\":\"TG\",\"airline_name\":\"Thai Airways\",\"origin\":\"Hong Kong\",\"destination\":\"Bangkok\",\"departure_date\":\"2025-10-15\",\"return_date\":\"2025-10-22\",\"departure_time\":\"16:45:00\",\"arrival_time\":\"19:00:00\",\"return_departure_time\":\"08:30:00\",\"return_arrival_time\":\"12:45:00\",\"duration\":\"2h 15m\",\"return_duration\":\"4h 15m\",\"stops\":0,\"return_stops\":1,\"price\":\"2600.00\",\"class\":\"economy\",\"seats_available\":37,\"created_at\":\"2025-10-09 13:30:10\"},\"passengers\":\"1\",\"selected_seats\":\"[\\\"3E\\\"]\",\"seat_charges\":\"0\",\"base_amount\":2600,\"tax_amount\":78,\"applied_coupon\":null,\"discount_amount\":0,\"duration\":\"2h 15m\",\"return_duration\":\"4h 15m\"}', NULL, 0.00, '{\"passenger_1\":{\"first_name\":\"Tin\",\"last_name\":\"Yan To\",\"email\":\"polo1st2023@gmail.com\",\"phone\":\"+852 55441153\",\"gender\":\"male\",\"dob\":\"2007-10-09\"}}', '{\"street\":\"123456789990\",\"city\":\"Central\",\"state\":\"Sham Shui Po\",\"zip\":\"999077\",\"country\":\"Hong Kong SAR\"}', 'paypal', 2678.00, 'confirmed', '2025-11-16 13:30:22', '2025-11-16 13:30:22', NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `country_id` int(11) NOT NULL,
  `timezone` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `name`, `country_id`, `timezone`, `created_at`) VALUES
(1, 'New York', 1, 'America/New_York', '2025-10-09 08:26:18'),
(2, 'Los Angeles', 1, 'America/Los_Angeles', '2025-10-09 08:26:18'),
(3, 'Chicago', 1, 'America/Chicago', '2025-10-09 08:26:18'),
(4, 'London', 2, 'Europe/London', '2025-10-09 08:26:18'),
(5, 'Manchester', 2, 'Europe/London', '2025-10-09 08:26:18'),
(6, 'Edinburgh', 2, 'Europe/London', '2025-10-09 08:26:18'),
(7, 'Toronto', 3, 'America/Toronto', '2025-10-09 08:26:18'),
(8, 'Vancouver', 3, 'America/Vancouver', '2025-10-09 08:26:18'),
(9, 'Montreal', 3, 'America/Montreal', '2025-10-09 08:26:18'),
(10, 'Sydney', 4, 'Australia/Sydney', '2025-10-09 08:26:18'),
(11, 'Melbourne', 4, 'Australia/Melbourne', '2025-10-09 08:26:18'),
(12, 'Brisbane', 4, 'Australia/Brisbane', '2025-10-09 08:26:18'),
(13, 'Tokyo', 5, 'Asia/Tokyo', '2025-10-09 08:26:18'),
(14, 'Osaka', 5, 'Asia/Tokyo', '2025-10-09 08:26:18'),
(15, 'Kyoto', 5, 'Asia/Tokyo', '2025-10-09 08:26:18'),
(16, 'Hong Kong', 7, 'Asia/Hong_Kong', '2025-10-09 08:26:18'),
(17, 'Bangkok', 8, 'Asia/Bangkok', '2025-10-09 08:26:18'),
(18, 'Taipei', 9, 'Asia/Taipei', '2025-10-09 08:26:18'),
(19, 'Paris', 10, 'Europe/Paris', '2025-10-09 08:26:18'),
(20, 'Berlin', 11, 'Europe/Berlin', '2025-10-09 08:26:18'),
(21, 'Rome', 12, 'Europe/Rome', '2025-10-09 08:26:18'),
(22, 'Amsterdam', 13, 'Europe/Amsterdam', '2025-10-09 08:26:18'),
(23, 'Barcelona', 14, 'Europe/Madrid', '2025-10-09 08:26:18'),
(24, 'Singapore', 15, 'Asia/Singapore', '2025-10-09 08:26:18'),
(25, 'Central', 7, 'Asia/Hong_Kong', '2025-10-09 09:24:21'),
(26, 'Tsim Sha Tsui', 7, 'Asia/Hong_Kong', '2025-10-09 09:24:21'),
(27, 'Causeway Bay', 7, 'Asia/Hong_Kong', '2025-10-09 09:24:21'),
(28, 'Mong Kok', 7, 'Asia/Hong_Kong', '2025-10-09 09:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(2) NOT NULL,
  `phone_code` varchar(5) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `code`, `phone_code`, `created_at`) VALUES
(1, 'United States', 'US', '+1', '2025-10-09 08:25:50'),
(2, 'United Kingdom', 'GB', '+44', '2025-10-09 08:25:50'),
(3, 'Canada', 'CA', '+1', '2025-10-09 08:25:50'),
(4, 'Australia', 'AU', '+61', '2025-10-09 08:25:50'),
(5, 'Japan', 'JP', '+81', '2025-10-09 08:25:50'),
(6, 'China', 'CN', '+86', '2025-10-09 08:25:50'),
(7, 'Hong Kong SAR', 'HK', '+852', '2025-10-09 08:25:50'),
(8, 'Thailand', 'TH', '+66', '2025-10-09 08:25:50'),
(9, 'Taiwan', 'TW', '+886', '2025-10-09 08:25:50'),
(10, 'France', 'FR', '+33', '2025-10-09 08:25:50'),
(11, 'Germany', 'DE', '+49', '2025-10-09 08:25:50'),
(12, 'Italy', 'IT', '+39', '2025-10-09 08:25:50'),
(13, 'Netherlands', 'NL', '+31', '2025-10-09 08:25:50'),
(14, 'Spain', 'ES', '+34', '2025-10-09 08:25:50'),
(15, 'Singapore', 'SG', '+65', '2025-10-09 08:25:50'),
(16, 'Macau SAR', 'MO', '+853', '2025-10-09 09:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `valid_from` datetime NOT NULL,
  `valid_until` datetime NOT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `for_new_users` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `description`, `discount_type`, `discount_value`, `min_amount`, `max_discount`, `valid_from`, `valid_until`, `usage_limit`, `used_count`, `for_new_users`, `is_active`, `created_at`) VALUES
(1, 'WELCOME15', '15% OFF for your first flight booking!', 'percentage', 15.00, 100.00, 500.00, '2025-11-11 00:05:37', '2026-12-31 23:59:59', 1, 0, 1, 1, '2025-11-10 16:05:37');

-- --------------------------------------------------------

--
-- Table structure for table `destinations`
--

CREATE TABLE `destinations` (
  `id` int(11) NOT NULL,
  `location_id` int(11) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `is_trending` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destinations`
--

INSERT INTO `destinations` (`id`, `location_id`, `image_url`, `price`, `description`, `is_trending`, `created_at`) VALUES
(1, 13, 'https://images.unsplash.com/photo-1540959733332-4abdc58b52c1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', 1696.00, 'Experience the perfect blend of traditional culture and modern technology in Tokyo', 1, '2025-10-07 07:49:10'),
(2, 15, 'https://images.unsplash.com/photo-1537953773345-d172ccf13cf1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', 1159.00, 'Discover the vibrant street life and beautiful temples of Bangkok', 1, '2025-10-07 07:49:10'),
(3, 16, 'https://images.unsplash.com/photo-1598936062027-9f3d0e69f1cf?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', 1170.00, 'Explore the night markets and natural beauty of Taipei', 1, '2025-10-07 07:49:10'),
(4, 17, 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', 4705.00, 'Immerse yourself in the rich history and culture of London', 1, '2025-10-07 07:49:10'),
(5, 18, 'https://images.unsplash.com/photo-1502602898536-47ad22581b52?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', 2899.00, 'The city of love and lights awaits you', 0, '2025-10-07 07:49:10'),
(6, 19, 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', 3299.00, 'The city that never sleeps', 0, '2025-10-07 07:49:10'),
(7, 21, 'https://images.unsplash.com/photo-1545349786-53bcbdac48eb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', 1899.00, 'Lion City - A modern marvel', 0, '2025-10-07 07:49:10'),
(8, 23, 'https://images.unsplash.com/photo-1536098561742-ca998e48cbcc?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', 1299.00, 'Tropical paradise with stunning beaches', 0, '2025-10-07 07:49:10'),
(9, 25, 'https://images.unsplash.com/photo-1564501049412-61c2a3083791?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', 2199.00, 'Eternal City full of history and romance', 0, '2025-10-07 07:49:10'),
(10, 27, 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80', 1999.00, 'City of canals and rich history', 0, '2025-10-07 07:49:10');

-- --------------------------------------------------------

--
-- Table structure for table `email_verification_tokens`
--

CREATE TABLE `email_verification_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `type` enum('airport','city','country') NOT NULL,
  `code` varchar(10) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `city_name` varchar(255) DEFAULT NULL,
  `country_name` varchar(255) DEFAULT NULL,
  `distance_from_downtown` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `type`, `code`, `name`, `city_name`, `country_name`, `distance_from_downtown`, `created_at`) VALUES
(1, 'airport', 'HKG', 'Hong Kong International Airport', 'Hong Kong', 'China', '23 km from downtown', '2025-10-07 07:48:44'),
(2, 'airport', 'IHP', 'Shun Tak Heliport', 'Hong Kong', 'China', '1 km from downtown', '2025-10-07 07:48:44'),
(3, 'airport', 'NRT', 'Narita International Airport', 'Tokyo', 'Japan', '60 km from downtown', '2025-10-07 07:48:44'),
(4, 'airport', 'HND', 'Haneda Airport', 'Tokyo', 'Japan', '14 km from downtown', '2025-10-07 07:48:44'),
(5, 'airport', 'BKK', 'Suvarnabhumi Airport', 'Bangkok', 'Thailand', '25 km from downtown', '2025-10-07 07:48:44'),
(6, 'airport', 'TPE', 'Taoyuan International Airport', 'Taipei', 'Taiwan', '40 km from downtown', '2025-10-07 07:48:44'),
(7, 'airport', 'LHR', 'Heathrow Airport', 'London', 'United Kingdom', '23 km from downtown', '2025-10-07 07:48:44'),
(8, 'airport', 'CDG', 'Charles de Gaulle Airport', 'Paris', 'France', '25 km from downtown', '2025-10-07 07:48:44'),
(9, 'airport', 'JFK', 'John F. Kennedy Airport', 'New York', 'USA', '24 km from downtown', '2025-10-07 07:48:44'),
(10, 'airport', 'LAX', 'Los Angeles International Airport', 'Los Angeles', 'USA', '27 km from downtown', '2025-10-07 07:48:44'),
(11, 'airport', 'SYD', 'Sydney Airport', 'Sydney', 'Australia', '8 km from downtown', '2025-10-07 07:48:44'),
(12, 'airport', 'SIN', 'Changi Airport', 'Singapore', 'Singapore', '17 km from downtown', '2025-10-07 07:48:44'),
(13, 'city', NULL, 'Hong Kong', 'Hong Kong', 'China', NULL, '2025-10-07 07:48:44'),
(14, 'city', NULL, 'Tokyo', 'Tokyo', 'Japan', NULL, '2025-10-07 07:48:44'),
(15, 'city', NULL, 'Bangkok', 'Bangkok', 'Thailand', NULL, '2025-10-07 07:48:44'),
(16, 'city', NULL, 'Taipei', 'Taipei', 'Taiwan', NULL, '2025-10-07 07:48:44'),
(17, 'city', NULL, 'London', 'London', 'United Kingdom', NULL, '2025-10-07 07:48:44'),
(18, 'city', NULL, 'Paris', 'Paris', 'France', NULL, '2025-10-07 07:48:44'),
(19, 'city', NULL, 'New York', 'New York', 'USA', NULL, '2025-10-07 07:48:44'),
(20, 'city', NULL, 'Los Angeles', 'Los Angeles', 'USA', NULL, '2025-10-07 07:48:44'),
(21, 'city', NULL, 'Sydney', 'Sydney', 'Australia', NULL, '2025-10-07 07:48:44'),
(22, 'city', NULL, 'Singapore', 'Singapore', 'Singapore', NULL, '2025-10-07 07:48:44'),
(23, 'city', NULL, 'Dubai', 'Dubai', 'UAE', NULL, '2025-10-07 07:48:44'),
(24, 'city', NULL, 'Istanbul', 'Istanbul', 'Turkey', NULL, '2025-10-07 07:48:44'),
(25, 'city', NULL, 'Bali', 'Bali', 'Indonesia', NULL, '2025-10-07 07:48:44'),
(26, 'city', NULL, 'Seoul', 'Seoul', 'South Korea', NULL, '2025-10-07 07:48:44'),
(27, 'city', NULL, 'Berlin', 'Berlin', 'Germany', NULL, '2025-10-07 07:48:44'),
(28, 'city', NULL, 'Rome', 'Rome', 'Italy', NULL, '2025-10-07 07:48:44'),
(29, 'city', NULL, 'Barcelona', 'Barcelona', 'Spain', NULL, '2025-10-07 07:48:44'),
(30, 'city', NULL, 'Amsterdam', 'Amsterdam', 'Netherlands', NULL, '2025-10-07 07:48:44'),
(31, 'city', NULL, 'Vancouver', 'Vancouver', 'Canada', NULL, '2025-10-07 07:48:44'),
(32, 'city', NULL, 'Auckland', 'Auckland', 'New Zealand', NULL, '2025-10-07 07:48:44');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('booking','security','promotion','system','profile') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `action_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `is_read`, `action_url`, `created_at`) VALUES
(23, 1, 'profile', 'Profile Updated', 'Your profile information was updated.', 1, NULL, '2025-11-11 12:18:43'),
(24, 1, 'security', 'Password Changed', 'Your password was successfully changed.', 0, NULL, '2025-11-11 12:19:56'),
(25, 1, 'security', 'Password Reset', 'Your password was successfully reset.', 0, NULL, '2025-11-11 12:23:46');

-- --------------------------------------------------------

--
-- Table structure for table `one_way_flights`
--

CREATE TABLE `one_way_flights` (
  `id` int(11) NOT NULL,
  `flight_number` varchar(20) NOT NULL,
  `airline_code` varchar(10) NOT NULL,
  `airline_name` varchar(100) NOT NULL,
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `departure_date` date NOT NULL,
  `departure_time` time NOT NULL,
  `arrival_time` time NOT NULL,
  `duration` varchar(20) NOT NULL,
  `stops` int(11) DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `class` varchar(50) DEFAULT 'economy',
  `seats_available` int(11) DEFAULT 100,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `one_way_flights`
--

INSERT INTO `one_way_flights` (`id`, `flight_number`, `airline_code`, `airline_name`, `origin`, `destination`, `departure_date`, `departure_time`, `arrival_time`, `duration`, `stops`, `price`, `class`, `seats_available`, `created_at`) VALUES
(1, 'CX123', 'CX', 'Cathay Pacific', 'Hong Kong', 'Tokyo', '2025-10-15', '08:00:00', '12:30:00', '4h 30m', 0, 1600.00, 'economy', 50, '2025-11-16 10:00:50'),
(2, 'JL456', 'JL', 'Japan Airlines', 'Hong Kong', 'Tokyo', '2025-10-15', '14:30:00', '19:00:00', '4h 30m', 0, 1750.00, 'economy', 45, '2025-11-16 10:00:50'),
(3, 'NH789', 'NH', 'All Nippon Airways', 'Hong Kong', 'Tokyo', '2025-10-15', '20:15:00', '00:45:00', '4h 30m', 0, 1900.00, 'economy', 40, '2025-11-16 10:00:50'),
(4, 'CX321', 'CX', 'Cathay Pacific', 'Hong Kong', 'Bangkok', '2025-10-15', '09:30:00', '11:45:00', '2h 15m', 0, 1400.00, 'economy', 60, '2025-11-16 10:00:50'),
(5, 'TG654', 'TG', 'Thai Airways', 'Hong Kong', 'Bangkok', '2025-10-15', '16:45:00', '19:00:00', '2h 15m', 0, 1300.00, 'economy', 55, '2025-11-16 10:00:50');

-- --------------------------------------------------------

--
-- Table structure for table `round_trip_flights`
--

CREATE TABLE `round_trip_flights` (
  `id` int(11) NOT NULL,
  `flight_number` varchar(20) NOT NULL,
  `airline_code` varchar(10) NOT NULL,
  `airline_name` varchar(100) NOT NULL,
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `departure_date` date NOT NULL,
  `return_date` date NOT NULL,
  `departure_time` time NOT NULL,
  `arrival_time` time NOT NULL,
  `return_departure_time` time NOT NULL,
  `return_arrival_time` time NOT NULL,
  `duration` varchar(20) NOT NULL,
  `return_duration` varchar(20) NOT NULL,
  `stops` int(11) DEFAULT 0,
  `return_stops` int(11) DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `class` varchar(50) DEFAULT 'economy',
  `seats_available` int(11) DEFAULT 100,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `round_trip_flights`
--

INSERT INTO `round_trip_flights` (`id`, `flight_number`, `airline_code`, `airline_name`, `origin`, `destination`, `departure_date`, `return_date`, `departure_time`, `arrival_time`, `return_departure_time`, `return_arrival_time`, `duration`, `return_duration`, `stops`, `return_stops`, `price`, `class`, `seats_available`, `created_at`) VALUES
(1, 'CX888', 'CX', 'Cathay Pacific', 'Hong Kong', 'Tokyo', '2025-10-15', '2025-10-22', '08:00:00', '12:30:00', '14:00:00', '18:30:00', '4h 30m', '4h 30m', 0, 0, 3200.00, 'economy', 43, '2025-10-09 05:30:10'),
(2, 'JL736', 'JL', 'Japan Airlines', 'Hong Kong', 'Tokyo', '2025-10-15', '2025-10-22', '14:30:00', '19:00:00', '09:00:00', '13:30:00', '4h 30m', '4h 30m', 0, 0, 3500.00, 'economy', 28, '2025-10-09 05:30:10'),
(3, 'NH812', 'NH', 'All Nippon Airways', 'Hong Kong', 'Tokyo', '2025-10-15', '2025-10-22', '20:15:00', '00:45:00', '16:30:00', '21:00:00', '4h 30m', '4h 30m', 0, 0, 3800.00, 'economy', 26, '2025-10-09 05:30:10'),
(4, 'CX451', 'CX', 'Cathay Pacific', 'Hong Kong', 'Bangkok', '2025-10-15', '2025-10-22', '09:30:00', '11:45:00', '13:00:00', '17:15:00', '2h 15m', '4h 15m', 0, 1, 2800.00, 'economy', 48, '2025-10-09 05:30:10'),
(5, 'TG639', 'TG', 'Thai Airways', 'Hong Kong', 'Bangkok', '2025-10-15', '2025-10-22', '16:45:00', '19:00:00', '08:30:00', '12:45:00', '2h 15m', '4h 15m', 0, 1, 2600.00, 'economy', 36, '2025-10-09 05:30:10');

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `country_id` int(11) NOT NULL,
  `code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `name`, `country_id`, `code`, `created_at`) VALUES
(1, 'New York', 1, 'NY', '2025-10-09 12:41:58'),
(2, 'California', 1, 'CA', '2025-10-09 12:41:58'),
(3, 'Texas', 1, 'TX', '2025-10-09 12:41:58'),
(4, 'Florida', 1, 'FL', '2025-10-09 12:41:58'),
(5, 'Illinois', 1, 'IL', '2025-10-09 12:41:58'),
(6, 'Ontario', 3, 'ON', '2025-10-09 12:41:58'),
(7, 'Quebec', 3, 'QC', '2025-10-09 12:41:58'),
(8, 'British Columbia', 3, 'BC', '2025-10-09 12:41:58'),
(9, 'England', 2, 'ENG', '2025-10-09 12:41:58'),
(10, 'Scotland', 2, 'SCT', '2025-10-09 12:41:58'),
(11, 'Wales', 2, 'WLS', '2025-10-09 12:41:58'),
(12, 'New South Wales', 4, 'NSW', '2025-10-09 12:41:58'),
(13, 'Victoria', 4, 'VIC', '2025-10-09 12:41:58'),
(14, 'Tokyo', 5, '13', '2025-10-09 12:41:58'),
(15, 'Osaka', 5, '27', '2025-10-09 12:41:58'),
(16, 'Central and Western', 7, 'CW', '2025-10-09 12:41:58'),
(17, 'Wan Chai', 7, 'WC', '2025-10-09 12:41:58'),
(18, 'Eastern', 7, 'E', '2025-10-09 12:41:58'),
(19, 'Southern', 7, 'S', '2025-10-09 12:41:58'),
(20, 'Yau Tsim Mong', 7, 'YTM', '2025-10-09 12:41:58'),
(21, 'Sham Shui Po', 7, 'SSP', '2025-10-09 12:41:58'),
(22, 'Kowloon City', 7, 'KC', '2025-10-09 12:41:58'),
(23, 'Wong Tai Sin', 7, 'WTS', '2025-10-09 12:41:58'),
(24, 'Kwun Tong', 7, 'KT', '2025-10-09 12:41:58');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'HKD',
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `booking_id`, `transaction_id`, `amount`, `currency`, `payment_method`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'TXN508A69CED00697F0', 3605.00, 'HKD', 'paypal', 'success', '2025-11-09 19:49:36', '2025-11-09 19:49:36'),
(2, 2, 'TXN1A16E7271142A935', 3655.00, 'HKD', 'paypal', 'success', '2025-11-09 21:50:28', '2025-11-09 21:50:28'),
(3, 3, 'TXN4711B812983C50CF', 2514.00, 'HKD', 'paypal', 'success', '2025-11-10 17:26:20', '2025-11-10 17:26:20'),
(4, 4, 'TXN075E9E7B9FE2A49B', 3105.00, 'HKD', 'paypal', 'success', '2025-11-11 11:39:59', '2025-11-11 11:39:59'),
(5, 5, 'TXND092894782E05207', 3105.00, 'HKD', 'credit_card', 'success', '2025-11-11 12:16:44', '2025-11-11 12:16:44'),
(6, 6, 'TXNCA3EC508F948A390', 7878.00, 'HKD', 'paypal', 'success', '2025-11-11 12:26:17', '2025-11-11 12:26:17'),
(7, 7, 'TXNF21F98418E463C28', 2728.00, 'HKD', 'paypal', 'success', '2025-11-16 12:28:36', '2025-11-16 12:28:36'),
(8, 8, 'TXN7D3C61A93A826E97', 3346.00, 'HKD', 'paypal', 'success', '2025-11-16 13:19:59', '2025-11-16 13:19:59'),
(9, 9, 'TXNBC9B92BF9671B172', 2934.00, 'HKD', 'paypal', 'success', '2025-11-16 13:21:28', '2025-11-16 13:21:28'),
(10, 10, 'TXND3B92FCEDD550E38', 3346.00, 'HKD', 'paypal', 'success', '2025-11-16 13:28:45', '2025-11-16 13:28:45'),
(11, 11, 'TXNADC7A015ADEBD8ED', 2678.00, 'HKD', 'paypal', 'success', '2025-11-16 13:30:22', '2025-11-16 13:30:22');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `billing_street` varchar(255) DEFAULT NULL,
  `billing_city` varchar(100) DEFAULT NULL,
  `billing_state_id` int(11) DEFAULT NULL,
  `billing_zip` varchar(20) DEFAULT NULL,
  `billing_country` varchar(100) DEFAULT NULL,
  `city_id` int(11) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `secret_2fa` varchar(255) DEFAULT NULL,
  `is_2fa_enabled` tinyint(1) DEFAULT 0,
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `verification_token_expires` datetime DEFAULT NULL,
  `backup_codes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_picture` varchar(500) DEFAULT NULL,
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `name`, `email`, `password_hash`, `phone`, `address`, `state_id`, `country_id`, `billing_street`, `billing_city`, `billing_state_id`, `billing_zip`, `billing_country`, `city_id`, `postal_code`, `date_of_birth`, `gender`, `secret_2fa`, `is_2fa_enabled`, `email_verified`, `email_verification_token`, `verification_token_expires`, `backup_codes`, `created_at`, `updated_at`, `profile_picture`, `preferences`) VALUES
(1, 'Polo01', 'Tin Yan', 'To', 'Tin Yan To', 'polo1st2023@gmail.com', '$2y$10$.GbKC/QyfFW2gLjqCmVNfeK7.zfZZ4xjSCsxAgf8/2.b/HvMi8iy6', '+852 55441153', '123456789990', 21, NULL, NULL, NULL, NULL, NULL, NULL, 25, '999077', '2007-10-09', 'male', NULL, 0, 1, NULL, NULL, NULL, '2025-11-11 12:11:28', '2025-11-16 15:11:19', 'uploads/avatars/avatar_1_1763305879.jpg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_coupons`
--

CREATE TABLE `user_coupons` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_coupons`
--

INSERT INTO `user_coupons` (`id`, `user_id`, `coupon_id`, `booking_id`, `used_at`, `created_at`) VALUES
(3, 1, 1, 5, '2025-11-11 20:16:44', '2025-11-11 12:16:44');

-- --------------------------------------------------------

--
-- Table structure for table `user_offers`
--

CREATE TABLE `user_offers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `offer_code` varchar(50) NOT NULL,
  `offer_type` enum('first_flight_discount','seasonal','promotional') NOT NULL,
  `discount_percent` decimal(5,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `minimum_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `valid_from` datetime NOT NULL,
  `valid_until` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `is_claimed` tinyint(1) DEFAULT 0,
  `used_at` datetime DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_offers`
--

INSERT INTO `user_offers` (`id`, `user_id`, `offer_code`, `offer_type`, `discount_percent`, `discount_amount`, `minimum_amount`, `max_discount`, `valid_from`, `valid_until`, `is_used`, `is_claimed`, `used_at`, `booking_id`, `created_at`) VALUES
(5, 1, 'WELCOME15', 'first_flight_discount', 15.00, NULL, 100.00, 500.00, '2025-11-11 20:12:30', '2026-12-31 23:59:59', 1, 1, '2025-11-11 20:16:44', 5, '2025-11-11 12:12:30');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_token` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_wishlist`
--

CREATE TABLE `user_wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `destination` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `target_date` date DEFAULT NULL,
  `estimated_budget` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_wishlist`
--

INSERT INTO `user_wishlist` (`id`, `user_id`, `destination`, `notes`, `priority`, `target_date`, `estimated_budget`, `created_at`) VALUES
(2, 1, 'Tokyo', '1234567890', 'medium', '0000-00-00', 1000.00, '2025-11-11 12:17:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_reference` (`booking_reference`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `idx_booking_ref` (`booking_reference`),
  ADD KEY `idx_user_bookings` (`user_id`,`created_at`),
  ADD KEY `idx_bookings_created` (`created_at`),
  ADD KEY `idx_bookings_status` (`status`),
  ADD KEY `bookings_round_trip_flights_fk` (`flight_id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `country_id` (`country_id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `destinations`
--
ALTER TABLE `destinations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_notifications_created` (`created_at`);

--
-- Indexes for table `one_way_flights`
--
ALTER TABLE `one_way_flights`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `round_trip_flights`
--
ALTER TABLE `round_trip_flights`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`),
  ADD KEY `country_id` (`country_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `idx_booking_id` (`booking_id`),
  ADD KEY `idx_transaction_id` (`transaction_id`),
  ADD KEY `idx_transactions_created` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `username_unique` (`username`),
  ADD KEY `city_id` (`city_id`),
  ADD KEY `state_id` (`state_id`),
  ADD KEY `billing_state_id` (`billing_state_id`),
  ADD KEY `users_ibfk_country` (`country_id`),
  ADD KEY `idx_users_email_verified` (`email_verified`);

--
-- Indexes for table `user_coupons`
--
ALTER TABLE `user_coupons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `coupon_id` (`coupon_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `user_offers`
--
ALTER TABLE `user_offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `offer_code` (`offer_code`),
  ADD KEY `valid_until` (`valid_until`),
  ADD KEY `is_used` (`is_used`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wishlist_user_created` (`user_id`,`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `destinations`
--
ALTER TABLE `destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `one_way_flights`
--
ALTER TABLE `one_way_flights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `round_trip_flights`
--
ALTER TABLE `round_trip_flights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_coupons`
--
ALTER TABLE `user_coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_offers`
--
ALTER TABLE `user_offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_ibfk_flight` FOREIGN KEY (`flight_id`) REFERENCES `round_trip_flights` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_round_trip_flights_fk` FOREIGN KEY (`flight_id`) REFERENCES `round_trip_flights` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`);

--
-- Constraints for table `destinations`
--
ALTER TABLE `destinations`
  ADD CONSTRAINT `destinations_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`);

--
-- Constraints for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD CONSTRAINT `email_verification_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `states`
--
ALTER TABLE `states`
  ADD CONSTRAINT `states_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`),
  ADD CONSTRAINT `users_ibfk_3` FOREIGN KEY (`billing_state_id`) REFERENCES `states` (`id`),
  ADD CONSTRAINT `users_ibfk_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`);

--
-- Constraints for table `user_coupons`
--
ALTER TABLE `user_coupons`
  ADD CONSTRAINT `user_coupons_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_coupons_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_coupons_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_offers`
--
ALTER TABLE `user_offers`
  ADD CONSTRAINT `user_offers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  ADD CONSTRAINT `user_wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
