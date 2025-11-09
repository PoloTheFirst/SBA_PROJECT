-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 09, 2025 at 03:00 PM
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
  `passenger_count` int(11) DEFAULT 1,
  `selected_seats` text DEFAULT NULL,
  `seat_charges` decimal(10,2) DEFAULT 0.00,
  `departure_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `passenger_info` text DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_status` enum('pending','completed','failed') DEFAULT 'pending',
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `traveler_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`traveler_details`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Dumping data for table `email_verification_tokens`
--

INSERT INTO `email_verification_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(2, 5, 'b7f78cfeacae82eb3f67c0ac4b8c5f3f5d5a423c9f02140c35f6c5859b8da15f', '2025-10-27 05:59:09', '2025-10-27 03:59:09');

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
  `type` enum('booking','security','promotion','system') NOT NULL,
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
(10, 5, '', 'Profile Updated', 'Your profile information was updated.', 0, NULL, '2025-10-26 13:40:30'),
(11, 5, '', 'Profile Updated', 'Your profile information was updated.', 0, NULL, '2025-10-26 13:45:07'),
(12, 5, '', 'Profile Updated', 'Your profile information was updated.', 0, NULL, '2025-10-26 13:45:23'),
(13, 5, '', 'Profile Updated', 'Your profile information was updated.', 0, NULL, '2025-10-26 13:46:08'),
(14, 5, '', 'Profile Updated', 'Your profile information was updated.', 0, NULL, '2025-10-26 13:46:16'),
(15, 5, '', 'Profile Updated', 'Your profile information was updated.', 0, NULL, '2025-10-26 13:46:22'),
(16, 5, '', 'Profile Updated', 'Your profile information was updated.', 0, NULL, '2025-10-26 13:46:29'),
(17, 5, '', 'Profile Updated', 'Your profile information was updated.', 0, NULL, '2025-10-26 13:46:37'),
(18, 5, '', 'Profile Updated', 'Your profile information was updated.', 0, NULL, '2025-10-26 13:55:24'),
(19, 7, '', 'Profile Updated', 'Your profile information was updated.', 0, NULL, '2025-10-28 01:34:38'),
(20, 7, '', 'Profile Updated', 'Your profile information was updated.', 0, NULL, '2025-10-28 01:35:01');

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
(1, 'CX888', 'CX', 'Cathay Pacific', 'Hong Kong', 'Tokyo', '2025-10-15', '2025-10-22', '08:00:00', '12:30:00', '14:00:00', '18:30:00', '4h 30m', '4h 30m', 0, 0, 3200.00, 'economy', 45, '2025-10-09 05:30:10'),
(2, 'JL736', 'JL', 'Japan Airlines', 'Hong Kong', 'Tokyo', '2025-10-15', '2025-10-22', '14:30:00', '19:00:00', '09:00:00', '13:30:00', '4h 30m', '4h 30m', 0, 0, 3500.00, 'economy', 32, '2025-10-09 05:30:10'),
(3, 'NH812', 'NH', 'All Nippon Airways', 'Hong Kong', 'Tokyo', '2025-10-15', '2025-10-22', '20:15:00', '00:45:00', '16:30:00', '21:00:00', '4h 30m', '4h 30m', 0, 0, 3800.00, 'economy', 28, '2025-10-09 05:30:10'),
(4, 'CX451', 'CX', 'Cathay Pacific', 'Hong Kong', 'Bangkok', '2025-10-15', '2025-10-22', '09:30:00', '11:45:00', '13:00:00', '17:15:00', '2h 15m', '4h 15m', 0, 1, 2800.00, 'economy', 50, '2025-10-09 05:30:10'),
(5, 'TG639', 'TG', 'Thai Airways', 'Hong Kong', 'Bangkok', '2025-10-15', '2025-10-22', '16:45:00', '19:00:00', '08:30:00', '12:45:00', '2h 15m', '4h 15m', 0, 1, 2600.00, 'economy', 38, '2025-10-09 05:30:10');

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

INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `name`, `email`, `password_hash`, `phone`, `address`, `state_id`, `billing_street`, `billing_city`, `billing_state_id`, `billing_zip`, `billing_country`, `city_id`, `postal_code`, `date_of_birth`, `gender`, `secret_2fa`, `is_2fa_enabled`, `email_verified`, `email_verification_token`, `verification_token_expires`, `backup_codes`, `created_at`, `updated_at`, `profile_picture`, `preferences`) VALUES
(5, 'Polo01', 'Tin Yan', 'To', 'Tin Yan To', 'polo1st2023@gmail.com', '$2y$10$.U98fBbTkErN33hMwfNmf.YAm7lbHbkmQLO5prhpGnM7L9beZqVSy', '+852 55441153', 'kkek', 20, NULL, NULL, NULL, NULL, NULL, 25, '999077', '2007-10-01', 'male', NULL, 0, 0, NULL, NULL, NULL, '2025-10-26 13:21:49', '2025-10-27 08:32:02', 'uploads/avatars/avatar_5_1761486921.jpg', NULL),
(7, 'Polo02', 'Tin Yan', 'To', 'Tin', 'polo2nd2024@gmail.com', '$2y$10$aJOoARskJ.8HnM2jIpCKZOAqKs5sbX.KXxcOZ7z0ePkXRMb9KxKUO', '+852 55441153', '38oiuytree4t5iop', 22, NULL, NULL, NULL, NULL, NULL, 27, '999077', '2007-09-30', 'male', NULL, 0, 0, NULL, NULL, NULL, '2025-10-28 00:52:39', '2025-10-28 01:35:33', 'uploads/avatars/avatar_7_1761615333.jpg', NULL);

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
  ADD KEY `idx_booking_reference` (`booking_reference`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_booking_date` (`booking_date`);

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
  ADD KEY `user_id` (`user_id`);

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
  ADD KEY `idx_transaction_id` (`transaction_id`);

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
  ADD KEY `billing_state_id` (`billing_state_id`);

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
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `destinations`
--
ALTER TABLE `destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `users_ibfk_3` FOREIGN KEY (`billing_state_id`) REFERENCES `states` (`id`);

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
