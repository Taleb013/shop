-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2025 at 06:51 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `3_cart`
--

CREATE TABLE `3_cart` (
  `product_code` varchar(100) NOT NULL,
  `quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `5_cart`
--

CREATE TABLE `5_cart` (
  `product_code` varchar(100) NOT NULL,
  `quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `user_id` int(11) NOT NULL,
  `product_code` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`user_id`, `product_code`, `quantity`) VALUES
(1, '123', 1),
(1, '3', 2);

-- --------------------------------------------------------

--
-- Table structure for table `cart_backup`
--

CREATE TABLE `cart_backup` (
  `cart_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('percentage','fixed') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `min_purchase` decimal(10,2) DEFAULT NULL,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `status` enum('active','inactive','expired') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` int(11) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('subscribed','unsubscribed') DEFAULT 'subscribed',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `order_status` varchar(50) NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders_backup`
--

CREATE TABLE `orders_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `order_id` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `vat` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `code`, `name`, `category`, `price`, `discount`, `image`) VALUES
(1, '1', 'gjhk', 'Software', 676545.00, 4, 'img_68f9212b9da7f.png'),
(2, '123', 'fghj', 'Book', 689.00, 2, 'img_68f9211297b12.jpg'),
(3, '3', 'dj', 'Cloth', 234.00, 23, 'img_68f89fffd6a9a.jpg'),
(4, '5', '5n', 'Book', 6767.00, 6, 'img_68f9216257d4c.jpg'),
(5, '7', '7hjjhk', 'Course', 890.00, 7, 'img_68f921491898a.jpg'),
(6, '24', 'masum', 'Cloth', 100.00, 1, 'img_68f9bc945ba9d.webp');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `discount_start` datetime DEFAULT NULL,
  `discount_end` datetime DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 5,
  `image` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `dimensions` varchar(50) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','out_of_stock') NOT NULL DEFAULT 'active',
  `featured` tinyint(1) DEFAULT 0,
  `trending` tinyint(1) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_attributes`
--

CREATE TABLE `product_attributes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `attribute_name` varchar(50) NOT NULL,
  `attribute_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `review_text` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `title` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `helpful_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'text',
  `group` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `group`, `description`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'My Online Shop', 'text', 'general', 'Name of the website', '2025-08-16 16:17:06', '2025-08-16 16:17:06'),
(2, 'site_description', 'Your one-stop shop for all products', 'textarea', 'general', 'Website description', '2025-08-16 16:17:06', '2025-08-16 16:17:06'),
(3, 'contact_email', 'contact@example.com', 'email', 'contact', 'Contact email address', '2025-08-16 16:17:06', '2025-08-16 16:17:06'),
(4, 'currency', 'USD', 'text', 'payment', 'Default currency', '2025-08-16 16:17:06', '2025-08-16 16:17:06'),
(5, 'currency_symbol', '$', 'text', 'payment', 'Currency symbol', '2025-08-16 16:17:06', '2025-08-16 16:17:06'),
(6, 'tax_rate', '15', 'number', 'payment', 'Default tax rate (%)', '2025-08-16 16:17:06', '2025-08-16 16:17:06'),
(7, 'shipping_cost', '100', 'number', 'shipping', 'Default shipping cost', '2025-08-16 16:17:06', '2025-08-16 16:17:06'),
(8, 'free_shipping_threshold', '1000', 'number', 'shipping', 'Minimum order amount for free shipping', '2025-08-16 16:17:06', '2025-08-16 16:17:06'),
(9, 'order_prefix', 'ORD-', 'text', 'order', 'Prefix for order numbers', '2025-08-16 16:17:06', '2025-08-16 16:17:06'),
(10, 'items_per_page', '12', 'number', 'display', 'Number of items to show per page', '2025-08-16 16:17:06', '2025-08-16 16:17:06'),
(11, 'maintenance_mode', '0', 'boolean', 'system', 'Enable maintenance mode', '2025-08-16 16:17:06', '2025-08-16 16:17:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `verification_token` varchar(64) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `phone`, `location`, `profile_image`, `is_admin`, `status`, `verification_token`, `reset_token`, `reset_token_expires`, `last_login`, `created_at`, `updated_at`, `avatar`) VALUES
(1, 'Md Abu Taleb', '2002mdabutaleb@gmail.com', '$2y$10$yKNSvkG9Q.HaUEsnNE9O..JPEcvpEobGgVx06lgyRNDvbRbUuiRBe', NULL, NULL, NULL, 0, 'active', NULL, NULL, NULL, NULL, '2025-10-22 10:13:56', '2025-10-22 10:13:56', NULL),
(2, 'masum', 'masum@gmail.com', '$2y$10$d1BB253lWWZdGno8sJ81b.aWA4v.Wgsk7Y.wb1tFNxdmjjhtHEqni', NULL, NULL, NULL, 0, 'active', NULL, NULL, NULL, NULL, '2025-10-23 05:02:34', '2025-10-23 05:02:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `type` enum('billing','shipping') DEFAULT 'shipping',
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `name`, `address`, `address_line2`, `city`, `state`, `postal_code`, `country`, `phone`, `type`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-22 10:14:14', '2025-10-22 10:14:14'),
(2, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-22 10:14:23', '2025-10-22 10:14:23'),
(3, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-22 10:31:28', '2025-10-22 10:31:28'),
(4, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-22 18:02:37', '2025-10-22 18:02:37'),
(5, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-22 18:15:50', '2025-10-22 18:15:50'),
(6, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-22 18:16:01', '2025-10-22 18:16:01'),
(7, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-22 18:22:37', '2025-10-22 18:22:37'),
(8, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-22 19:09:20', '2025-10-22 19:09:20'),
(9, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 03:27:36', '2025-10-23 03:27:36'),
(10, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 04:49:34', '2025-10-23 04:49:34'),
(11, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 04:56:43', '2025-10-23 04:56:43'),
(12, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 04:58:43', '2025-10-23 04:58:43'),
(13, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 04:59:33', '2025-10-23 04:59:33'),
(14, 2, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 05:15:57', '2025-10-23 05:15:57'),
(15, 2, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 05:16:14', '2025-10-23 05:16:14'),
(16, 2, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 05:16:41', '2025-10-23 05:16:41'),
(17, 2, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 05:16:59', '2025-10-23 05:16:59'),
(18, 2, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 05:17:12', '2025-10-23 05:17:12'),
(19, 2, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 05:17:16', '2025-10-23 05:17:16'),
(20, 2, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 05:18:03', '2025-10-23 05:18:03'),
(21, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 05:58:31', '2025-10-23 05:58:31'),
(22, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 06:04:05', '2025-10-23 06:04:05'),
(23, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 06:04:26', '2025-10-23 06:04:26'),
(24, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 06:58:00', '2025-10-23 06:58:00'),
(25, 1, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shipping', 0, '2025-10-23 16:46:02', '2025-10-23 16:46:02');

-- --------------------------------------------------------

--
-- Table structure for table `user_orders`
--

CREATE TABLE `user_orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `order_status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_orders`
--

INSERT INTO `user_orders` (`order_id`, `user_id`, `full_name`, `email`, `phone`, `shipping_address`, `total_amount`, `payment_method`, `order_status`, `created_at`) VALUES
(1, 1, 'Md Abu Taleb', '2002mdabutaleb@gmail.com', NULL, 'comilla ', 1683.10, 'Cash on Delivery', 'pending', '2025-10-23 04:48:01'),
(2, 1, 'Md Abu Taleb', '2002mdabutaleb@gmail.com', NULL, 'comilla', 1035.58, 'bKash', 'pending', '2025-10-23 04:49:22'),
(3, 1, 'Md Abu Taleb', '2002mdabutaleb@gmail.com', NULL, 'comilla ', 180.18, 'Cash on Delivery', 'pending', '2025-10-23 04:50:24'),
(4, 1, 'Md Abu Taleb', '2002mdabutaleb@gmail.com', NULL, 'h', 360.36, 'bKash', 'pending', '2025-10-23 04:59:23'),
(5, 2, 'masum', 'masum@gmail.com', NULL, 'jhj', 540.54, 'Cash on Delivery', 'pending', '2025-10-23 05:04:00'),
(6, 2, 'masum', 'masum@gmail.com', NULL, 'masum', 360.36, 'bKash', 'pending', '2025-10-23 05:16:10'),
(7, 1, 'Md Abu Taleb', '2002mdabutaleb@gmail.com', NULL, 'hi', 180.18, 'Nagad', 'pending', '2025-10-23 05:58:27'),
(8, 1, 'Md Abu Taleb', '2002mdabutaleb@gmail.com', NULL, 'comilla', 360.36, 'Nagad', 'pending', '2025-10-23 06:04:01');

-- --------------------------------------------------------

--
-- Table structure for table `user_order_items`
--

CREATE TABLE `user_order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_code` varchar(100) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_order_items`
--

INSERT INTO `user_order_items` (`id`, `order_id`, `product_code`, `product_name`, `quantity`, `price`, `created_at`) VALUES
(1, 1, '123', 'fghj', 1, 675.22, '2025-10-23 04:48:01'),
(2, 1, '3', 'dj', 1, 180.18, '2025-10-23 04:48:01'),
(3, 1, '7', '7hjjhk', 1, 827.70, '2025-10-23 04:48:01'),
(4, 2, '123', 'fghj', 1, 675.22, '2025-10-23 04:49:22'),
(5, 2, '3', 'dj', 2, 180.18, '2025-10-23 04:49:22'),
(6, 3, '3', 'dj', 1, 180.18, '2025-10-23 04:50:24'),
(7, 4, '3', 'dj', 2, 180.18, '2025-10-23 04:59:23'),
(8, 5, '3', 'dj', 3, 180.18, '2025-10-23 05:04:00'),
(9, 6, '3', 'dj', 2, 180.18, '2025-10-23 05:16:10'),
(10, 7, '3', 'dj', 1, 180.18, '2025-10-23 05:58:27'),
(11, 8, '3', 'dj', 2, 180.18, '2025-10-23 06:04:01');

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  `sms_notifications` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`id`, `user_id`, `email_notifications`, `sms_notifications`, `created_at`) VALUES
(1, 1, 1, 0, '2025-10-22 10:14:14'),
(2, 1, 1, 0, '2025-10-22 10:14:23'),
(3, 1, 1, 0, '2025-10-22 10:31:28'),
(4, 1, 1, 0, '2025-10-22 18:02:37'),
(5, 1, 1, 0, '2025-10-22 18:15:50'),
(6, 1, 1, 0, '2025-10-22 18:16:01'),
(7, 1, 1, 0, '2025-10-22 18:22:37'),
(8, 1, 1, 0, '2025-10-22 19:09:20'),
(9, 1, 1, 0, '2025-10-23 03:27:36'),
(10, 1, 1, 0, '2025-10-23 04:49:34'),
(11, 1, 1, 0, '2025-10-23 04:56:43'),
(12, 1, 1, 0, '2025-10-23 04:58:43'),
(13, 1, 1, 0, '2025-10-23 04:59:33'),
(14, 2, 1, 0, '2025-10-23 05:15:57'),
(15, 2, 1, 0, '2025-10-23 05:16:14'),
(16, 2, 1, 0, '2025-10-23 05:16:41'),
(17, 2, 1, 0, '2025-10-23 05:16:59'),
(18, 2, 1, 0, '2025-10-23 05:17:12'),
(19, 2, 1, 0, '2025-10-23 05:17:16'),
(20, 2, 1, 0, '2025-10-23 05:18:03'),
(21, 1, 1, 0, '2025-10-23 05:58:31'),
(22, 1, 1, 0, '2025-10-23 06:04:05'),
(23, 1, 1, 0, '2025-10-23 06:04:26'),
(24, 1, 1, 0, '2025-10-23 06:58:00'),
(25, 1, 1, 0, '2025-10-23 16:46:02');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `3_cart`
--
ALTER TABLE `3_cart`
  ADD PRIMARY KEY (`product_code`);

--
-- Indexes for table `5_cart`
--
ALTER TABLE `5_cart`
  ADD PRIMARY KEY (`product_code`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_user_id_index` (`user_id`),
  ADD KEY `activity_entity_index` (`entity_type`,`entity_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`user_id`,`product_code`),
  ADD KEY `product_code` (`product_code`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_product_code` (`code`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `product_status` (`status`),
  ADD KEY `product_featured` (`featured`),
  ADD KEY `product_trending` (`trending`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_orders`
--
ALTER TABLE `user_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_order_items`
--
ALTER TABLE `user_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_wishlist_product` (`product_id`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_attributes`
--
ALTER TABLE `product_attributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `user_orders`
--
ALTER TABLE `user_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_order_items`
--
ALTER TABLE `user_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_code`) REFERENCES `product` (`code`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD CONSTRAINT `product_attributes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_prodimg_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_order_items`
--
ALTER TABLE `user_order_items`
  ADD CONSTRAINT `fk_user_order_items` FOREIGN KEY (`order_id`) REFERENCES `user_orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
