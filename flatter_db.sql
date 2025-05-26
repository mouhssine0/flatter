-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 25 mai 2025 à 21:51
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `flatter_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'Home Office Storage', '2025-05-24 22:17:04'),
(2, 'Suits & Sets', '2025-05-24 22:17:04'),
(3, 'Paint Care', '2025-05-24 22:17:04'),
(5, 'Print', '2025-05-24 22:17:04'),
(6, 'Cycling Gloves', '2025-05-24 22:17:04'),
(7, 'Men\'s Shirts', '2025-05-24 22:17:04'),
(8, 'Totes', '2025-05-24 22:17:04'),
(9, 'Woman Boots', '2025-05-24 22:17:04'),
(10, 'Leather Cases', '2025-05-24 22:17:04'),
(12, 'Women\'s Crossbody Bags', '2025-05-24 22:17:04'),
(13, 'Lady Dresses', '2025-05-24 22:17:04'),
(15, 'Earrings', '2025-05-24 22:17:04'),
(16, 'Bracelets & Bangles', '2025-05-24 22:17:04'),
(17, 'Hand Tools', '2025-05-24 22:17:04'),
(18, 'Cooking Tools', '2025-05-24 22:17:04'),
(23, 'Baby Rompers', '2025-05-24 22:17:04'),
(24, 'Women\'s Camis', '2025-05-24 22:17:04'),
(26, 'Necklace & Pendants', '2025-05-24 22:17:04'),
(28, 'Blazers', '2025-05-24 22:17:04'),
(29, 'Nappy Changing', '2025-05-24 22:17:04'),
(30, 'Wide Leg Pants', '2025-05-24 22:17:04'),
(32, 'Woman Hoodies & Sweatshirts', '2025-05-24 22:17:04'),
(34, 'Girl Clothing Sets', '2025-05-24 22:17:04'),
(35, 'Interior Parts', '2025-05-24 22:17:04'),
(36, 'Children\'s Shoes', '2025-05-24 22:17:04'),
(37, 'Woman Sandals', '2025-05-24 22:17:04'),
(43, 'Flats', '2025-05-24 22:17:04'),
(44, 'Casual Pants', '2025-05-24 22:17:04'),
(48, 'Men Sports Watches', '2025-05-24 22:17:04'),
(49, 'Fine Earrings', '2025-05-24 22:17:04'),
(50, 'Furniture', '2025-05-24 22:17:04');

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE `products` (
  `id` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `category` varchar(100) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `categoryId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `image`, `price`, `category`, `stock`, `created_at`, `updated_at`, `categoryId`) VALUES
('2505240604391609600', 'Multifunctional Electric Cleaning Brush Wireless Charging', 'Aucune description disponible', '0', 12.11, 'Furniture', 0, '2025-05-24 22:17:04', '2025-05-24 22:29:04', 50),
('2505240605381625200', '925 Silver Needle Ballet Shoes Stud Earrings Women\'s Exquisite Sweet Ribbon Bow', 'Aucune description disponible', '0', 1.74, 'Fine Earrings', 0, '2025-05-24 22:17:04', '2025-05-24 22:29:02', 49),
('2505240606081627000', 'Male Student Multi-functional Youth Leisure Sports Fashion Electronic Watch', 'Aucune description disponible', '0', 3.86, 'Men Sports Watches', 0, '2025-05-24 22:17:04', '2025-05-24 22:29:01', 48),
('2505240606271600600', 'Waist Up Camisole Dress Plus Size Dress', 'Aucune description disponible', '0', 28.03, 'Lady Dresses', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:59', 13),
('2505240606441628900', 'Refrigerator Cooling Type Dustproof Cover Cloth', 'Aucune description disponible', '0', 3.81, 'Home Office Storage', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:58', 1),
('2505240608581608000', 'ECG Heart-shaped Exquisite Necklace For Women', 'Aucune description disponible', '0', 0.60, 'Necklace & Pendants', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:56', 26),
('2505240609381610000', 'Black Tencel Cotton Single Pleated Cut Adjustable Waist Straight Trousers', 'Aucune description disponible', '0', 57.71, 'Casual Pants', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:55', 44),
('2505240610411626900', 'Low-cut Internet Hot Korean Style Round Head Pumps Women', 'Aucune description disponible', '0', 4.94, 'Flats', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:53', 43),
('2505240611331600000', 'Fashion Portable TWS Waterproof Bluetooth Speaker', 'Aucune description disponible', '0', 14.09, 'Home Office Storage', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:52', 1),
('2505240614351606000', 'Niche Design Hand Woven Hand Rope', 'Aucune description disponible', '0', 0.96, 'Bracelets & Bangles', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:51', 16),
('2505240617201606200', 'Wide Terylene Curtain Window Screen Fabric Solid Color', 'Aucune description disponible', '0', 1.86, 'Home Office Storage', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:50', 1),
('2505240623031621600', 'Fashion Creative Sofa Handrail Tray Storage Rack', 'Aucune description disponible', '0', 4.44, 'Home Office Storage', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:48', 1),
('2505240628301626500', 'Nitrile Rubber O-ring Seal', 'Aucune description disponible', '0', 0.00, 'Home Office Storage', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:47', 1),
('2505240630071604500', 'Summer Casual And Lightweight Beach Sandals For Women', 'Aucune description disponible', '0', 3.88, 'Woman Sandals', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:45', 37),
('2505240635101616800', 'Children\'s Small Leather Shoes Non-slip Soft Bottom Girl Cute Princess Cartoon Shoes', 'Aucune description disponible', '0', 9.60, 'Children\'s Shoes', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:41', 36),
('2505240638251623600', 'Car Leather Waterproof Garbage Bag', 'Aucune description disponible', '0', 3.58, 'Interior Parts', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:40', 35),
('2505240640011604800', 'Flying Sleeve Length Bowknot Dress Shorts Two-piece Set', 'Aucune description disponible', '0', 3.94, 'Girl Clothing Sets', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:38', 34),
('2505240647111613000', 'Black Zipper Satin Body Waist-controlled Top', 'Aucune description disponible', '0', 7.13, 'Blazers', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:37', 28),
('2505240649061624700', 'American Street Solid Color Fleece Hooded Sweatshirt For Men And Women', 'Aucune description disponible', '0', 16.42, 'Woman Hoodies & Sweatshirts', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:36', 32),
('2505240654311603100', 'Stainless Steel Corner Corner Pulling Artifact Wall Scraping Tool', 'Aucune description disponible', '0', 0.17, 'Home Office Storage', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:34', 1),
('2505240656151617500', 'Fleece-lined Thick Hooded Solid Color Hoodie Suit', 'Aucune description disponible', '0', 16.42, 'Wide Leg Pants', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:32', 30),
('2505240658001620600', 'Gauze Cotton Baby\'s Ring Urine Training Pants Pure Diaper Pants', 'Aucune description disponible', '0', 1.74, 'Nappy Changing', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:30', 29),
('2505240659171601200', 'Women\'s Elegant French Style Puff Sleeve Floral Shirt', 'Aucune description disponible', '0', 2.61, 'Blazers', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:24', 28),
('2505240715491612100', 'Women\'s Personality Solid Color Dress', 'Aucune description disponible', '0', 4.02, 'Lady Dresses', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:17', 13),
('2505240718211612800', 'Two-tone Heart Buckle Stainless Steel Necklace', 'Aucune description disponible', '0', 1.32, 'Necklace & Pendants', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:16', 26),
('2505240719571611800', 'Geometric Chain Earrings For Women', 'Aucune description disponible', '0', 0.62, 'Earrings', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:15', 15),
('2505240722461613500', 'Outdoor Workout Yoga Vest Top', 'Aucune description disponible', '0', 7.13, 'Women\'s Camis', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:14', 24),
('2505240724581615600', 'Baby Romper Spring And Autumn Jumpsuit', 'Aucune description disponible', '0', 4.19, 'Baby Rompers', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:12', 23),
('2505240728191601400', 'Women\'s Hooded Long Sleeve Plush Dress', 'Aucune description disponible', '0', 5.80, 'Lady Dresses', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:11', 13),
('2505240730201600400', 'Women\'s Printed Short-sleeved Shirt Dress', 'Aucune description disponible', '0', 7.96, 'Lady Dresses', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:09', 13),
('2505240735321617900', 'Dress Middle-aged And Elderly Two-piece Suit', 'Aucune description disponible', '0', 1.76, 'Lady Dresses', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:08', 13),
('2505240735541607400', 'Printed AB Stitching Short Sleeve Lace-up A- Line Skirt', 'Aucune description disponible', '0', 7.46, 'Lady Dresses', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:06', 13),
('2505240737161616800', 'Intelligent Waterproof Low Temperature Slow-boiling Machine Shu Fat Machine Cooking Stick', 'Aucune description disponible', '0', 74.46, 'Cooking Tools', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:04', 18),
('2505240742171623500', 'Refrigerator Copper Tube Charging Valve Pliers', 'Aucune description disponible', '0', 8.29, 'Hand Tools', 0, '2025-05-24 22:17:04', '2025-05-24 22:28:03', 17),
('2505240742361621900', 'New Stainless Steel Peach Heart Cuban Link Chain Bracelet Suit', 'Aucune description disponible', '0', 4.49, 'Bracelets & Bangles', 0, '2025-05-24 22:17:04', '2025-05-24 22:27:57', 16),
('2505240743111626700', 'Simple Hoop Earrings Women', 'Aucune description disponible', '0', 0.53, 'Earrings', 0, '2025-05-24 22:17:04', '2025-05-24 22:27:56', 15),
('2505240743371610800', 'GearS3 S2 Universal Genuine Leather Strap', 'Aucune description disponible', '0', 1.67, 'Home Office Storage', 0, '2025-05-24 22:17:04', '2025-05-24 22:27:50', 1),
('2505240745201627500', 'Women\'s Fashion Personality Vintage Bow Dress', 'Aucune description disponible', '0', 8.46, 'Lady Dresses', 0, '2025-05-24 22:17:04', '2025-05-24 22:27:44', 13),
('2505240746141615200', 'Fashion Handbag Mom Large Capacity Shoulder Bag', 'Aucune description disponible', '0', 4.21, 'Women\'s Crossbody Bags', 0, '2025-05-24 22:17:04', '2025-05-24 22:27:43', 12),
('2505240746451615700', 'Creative Simple Jianshan Flower Pot Ornaments', 'Aucune description disponible', '0', 1.21, 'Home Office Storage', 0, '2025-05-24 22:17:04', '2025-05-24 22:27:41', 1),
('2505240751101629900', 'Litchi Patterned Phone Case With A Niche And High-end Feel', 'Aucune description disponible', '0', 0.90, 'Leather Cases', 0, '2025-05-24 22:17:04', '2025-05-24 22:27:39', 10),
('2505240753191619600', 'Fashion Rain Boots Women\'s Middle Tube Non-slip Warm', 'Aucune description disponible', '0', 2.70, 'Woman Boots', 0, '2025-05-24 22:17:04', '2025-05-24 22:27:38', 9),
('2505240806371609800', 'Handbag Crossbody Shoulder All-matching Elegant', 'Aucune description disponible', '0', 6.14, 'Totes', 0, '2025-05-24 22:17:04', '2025-05-24 22:27:36', 8),
('2505240809221620100', 'Men\'s Metal Bronzing Printed Lapel Shirt', 'Aucune description disponible', '0', 4.30, 'Men\'s Shirts', 0, '2025-05-24 22:17:04', '2025-05-24 22:27:29', 7),
('2505240809371611700', 'Motorbike Gloves Motorcycle Equipment Warm Breathable Gloves', 'Aucune description disponible', '0', 18.24, 'Cycling Gloves', 0, '2025-05-24 22:17:04', '2025-05-24 22:27:27', 6),
('2505240814331618600', 'H Cute Cat Sushi Cartoon Style Mens Cotton Short Sleeve', 'Aucune description disponible', '0', 3.02, 'Print', 0, '2025-05-24 22:17:04', '2025-05-24 22:27:25', 5),
('2505240817191629000', 'Coconut Mint Whitening Tooth Patch', 'Aucune description disponible', '0', 4.53, 'Home Office Storage', 0, '2025-05-24 22:17:04', '2025-05-24 22:27:22', 1),
('2505240858461609700', 'Car Scratch Removal Pen Quick Repair', 'Aucune description disponible', '0', 1.36, 'Paint Care', 0, '2025-05-24 22:17:04', '2025-05-24 22:27:21', 3),
('2505240946501615500', 'Lace-up Sleeveless Backless Top High Waist Shorts Suit', 'Aucune description disponible', '0', 7.96, 'Suits & Sets', 0, '2025-05-24 22:17:04', '2025-05-24 22:27:16', 2),
('2505241005561600600', 'Cute Healing Capibala Wobbly Ornament', 'Aucune description disponible', '0', 2.99, 'Home Office Storage', 0, '2025-05-24 22:17:04', '2025-05-24 22:27:13', 1);

-- --------------------------------------------------------

--
-- Structure de la table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` varchar(50) DEFAULT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_main` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `is_main`, `created_at`) VALUES
(8, '2505241005561600600', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/10/5a23558e-20dc-499b-b105-f38e75677480_fine.jpeg', 1, '2025-05-24 22:27:31'),
(9, '2505240946501615500', 'https://cf.cjdropshipping.com/quick/product/801b250b-f352-4270-a9ba-334928c4d350.jpg', 1, '2025-05-24 22:27:32'),
(10, '2505240858461609700', 'https://cf.cjdropshipping.com/quick/product/9d943207-0674-43a0-be8a-a78bfa28bf87.jpg', 1, '2025-05-24 22:27:33'),
(11, '2505240817191629000', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/08/46e8b4b3-e6ee-4fa0-bf5d-c2ee206bcc2d.jpg', 1, '2025-05-24 22:27:35'),
(16, '2505240814331618600', 'https://cf.cjdropshipping.com/quick/product/15f4eef3-69bc-4173-9322-bf5b9af0f7b1.jpg', 1, '2025-05-24 22:27:42'),
(19, '2505240809371611700', 'https://cbu01.alicdn.com/img/ibank/O1CN01t7gUie2JcwqVHlWtz_!!3987189443-0-cib.jpg', 1, '2025-05-24 22:27:45'),
(20, '2505240809221620100', 'https://cbu01.alicdn.com/img/ibank/O1CN01R1R4jf1PBMitXH3Jl_!!2217767591802-0-cib.jpg', 1, '2025-05-24 22:27:47'),
(21, '2505240806371609800', 'https://cf.cjdropshipping.com/quick/product/254c20c7-f60f-46f5-bf85-e395ad537a70.jpg', 1, '2025-05-24 22:27:48'),
(22, '2505240753191619600', 'https://cbu01.alicdn.com/img/ibank/O1CN01gJC8Ua1NT6iRgfsad_!!2216746871570-0-cib.jpg', 1, '2025-05-24 22:27:50'),
(24, '2505240751101629900', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/07/50a27511-e516-4d38-8e81-11f30d8cc16c_trans.jpeg', 1, '2025-05-24 22:27:51'),
(25, '2505240746451615700', 'https://cf.cjdropshipping.com/quick/product/be86b39e-91db-4df3-aeec-c16c1b70ff5e.jpg', 1, '2025-05-24 22:27:52'),
(26, '2505240746141615200', 'https://cbu01.alicdn.com/img/ibank/O1CN0122aGXe21edN6XAPwd_!!2821027010-0-cib.jpg', 1, '2025-05-24 22:27:53'),
(27, '2505240745201627500', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/07/3940d464-dd3c-49dc-aeba-95d7b988fa42.jpg', 1, '2025-05-24 22:27:55'),
(30, '2505240743371610800', 'https://cbu01.alicdn.com/img/ibank/10745452730_1254121087.jpg', 1, '2025-05-24 22:27:59'),
(31, '2505240743111626700', 'https://cbu01.alicdn.com/img/ibank/O1CN01cqhHVW1K33GNxiZCh_!!2215472041107-0-cib.jpg', 1, '2025-05-24 22:28:00'),
(32, '2505240742361621900', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/07/9599d23b-36d5-4b57-82d6-c2fbc214876c.jpg', 1, '2025-05-24 22:28:01'),
(35, '2505240742171623500', 'https://cbu01.alicdn.com/img/ibank/O1CN01YEj7n21wfwVWtu7jW_!!3960876336-0-cib.jpg', 1, '2025-05-24 22:28:04'),
(36, '2505240737161616800', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/09/c797653a-5bb6-47d2-8b55-f4c58ea54735_fine.jpeg', 1, '2025-05-24 22:28:05'),
(40, '2505240735541607400', 'https://cbu01.alicdn.com/img/ibank/O1CN0105rSTg1w1BFWQGQzR_!!2200559976247-0-cib.jpg', 1, '2025-05-24 22:28:10'),
(46, '2505240735321617900', 'https://cbu01.alicdn.com/img/ibank/O1CN018RzpIU1pTqh2INctu_!!2218422455362-0-cib.jpg', 1, '2025-05-24 22:28:17'),
(48, '2505240730201600400', 'https://cbu01.alicdn.com/img/ibank/O1CN01N2f43X1o1chm0ZFxP_!!2218658925165-0-cib.jpg', 1, '2025-05-24 22:28:19'),
(49, '2505240728191601400', 'https://cbu01.alicdn.com/img/ibank/O1CN01BgnD3D1xiWJvAq0az_!!2206581336477-0-cib.jpg', 1, '2025-05-24 22:28:21'),
(50, '2505240724581615600', 'https://cbu01.alicdn.com/img/ibank/O1CN01vYVVfE2NPRlJZDgt8_!!3916929955-0-cib.jpg', 1, '2025-05-24 22:28:22'),
(51, '2505240722461613500', 'https://cbu01.alicdn.com/img/ibank/O1CN01B8hPk11KebqwKSZ31_!!2887621189-0-cib.jpg', 1, '2025-05-24 22:28:23'),
(53, '2505240719571611800', 'https://cf.cjdropshipping.com/quick/product/380329e4-826a-4be6-aa49-d68096c544a3.jpg', 1, '2025-05-24 22:28:24'),
(54, '2505240718211612800', 'https://cf.cjdropshipping.com/quick/product/d489bcc4-2e28-4612-98d2-554e801f1086.jpg', 1, '2025-05-24 22:28:25'),
(55, '2505240715491612100', 'https://cbu01.alicdn.com/img/ibank/O1CN01HbPHr41eqmiwR9RyI_!!2215071393923-0-cib.jpg', 1, '2025-05-24 22:28:27'),
(56, '2505240659171601200', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/06/f353b4d8-2a3c-4a33-aea1-c68a297324a2.jpg', 1, '2025-05-24 22:28:28'),
(58, '2505240658001620600', 'https://cf.cjdropshipping.com/quick/product/7eb19897-7a52-4d59-bb61-59b3564e09e7.jpg', 1, '2025-05-24 22:28:30'),
(62, '2505240656151617500', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/06/1418213e-742e-43f2-bc21-1fe67754a151.jpg', 1, '2025-05-24 22:28:37'),
(67, '2505240654311603100', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/06/fdfa57e9-2c03-44b5-b97b-01bebf16b5de.jpg', 1, '2025-05-24 22:28:42'),
(68, '2505240649061624700', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/06/2bfab538-8ccd-45d7-96cf-261195b0695d.jpg', 1, '2025-05-24 22:28:44'),
(73, '2505240647111613000', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/06/ed396773-8c48-4de1-a2ea-87f6930c76e3_trans.jpeg', 1, '2025-05-24 22:28:50'),
(79, '2505240640011604800', 'https://cf.cjdropshipping.com/quick/product/58e0a779-2983-4dbc-8258-5e312925fc6b.jpg', 1, '2025-05-24 22:28:56'),
(83, '2505240638251623600', 'https://cbu01.alicdn.com/img/ibank/O1CN019UlmcH20hYMigwGvJ_!!2219368566881-0-cib.jpg', 1, '2025-05-24 22:29:02'),
(84, '2505240605381625200', 'https://cbu01.alicdn.com/img/ibank/O1CN01EMVd3I1lq3O2JplXH_!!2200716164869-0-cib.jpg', 1, '2025-05-24 22:29:02'),
(85, '2505240604391609600', 'https://cbu01.alicdn.com/img/ibank/O1CN01Fnl1O31RGypr4LtWV_!!2216164282085-0-cib.jpg', 1, '2025-05-24 22:29:04'),
(86, '2505240635101616800', 'https://cbu01.alicdn.com/img/ibank/O1CN017eLxy01VaS1Ht2gh9_!!2903272669-0-cib.jpg', 1, '2025-05-24 22:29:05'),
(87, '2505240630071604500', 'https://cf.cjdropshipping.com/quick/product/0e499316-70a6-4a74-be09-56653ba33451.jpg', 1, '2025-05-24 22:29:07'),
(88, '2505240628301626500', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/06/b288ec25-7fe1-452c-80ce-54cb3b5d6953_trans.jpeg', 1, '2025-05-24 22:29:08'),
(89, '2505240623031621600', 'https://cbu01.alicdn.com/img/ibank/O1CN01YoFZqC2KWMslWCZ9c_!!2216642829564-0-cib.jpg', 1, '2025-05-24 22:29:09'),
(90, '2505240617201606200', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/06/cd3f7b7f-b816-4be8-8e97-48ffe371f3c9_trans.jpeg', 1, '2025-05-24 22:29:10'),
(91, '2505240614351606000', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/06/c0798fce-fac8-4132-a7ea-3e36bf748f66_trans.jpeg', 1, '2025-05-24 22:29:11'),
(92, '2505240611331600000', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/06/809f54b0-e308-444b-b584-395fded30fef_trans.jpeg', 1, '2025-05-24 22:29:14'),
(93, '2505240610411626900', 'https://cf.cjdropshipping.com/quick/product/d05165cf-0670-45d5-8567-8ec5e83e08d4.jpg', 1, '2025-05-24 22:29:16'),
(94, '2505240609381610000', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/06/09dde034-089b-4093-a3e5-06790a9307c1.jpg', 1, '2025-05-24 22:29:17'),
(95, '2505240608581608000', 'https://cbu01.alicdn.com/img/ibank/O1CN01qtLVDM1U98oaKRVQH_!!2211166642474-0-cib.jpg', 1, '2025-05-24 22:29:20'),
(96, '2505240606441628900', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/05/051fa8ff-7274-4f53-912e-276cd6055c19_fine.jpeg', 1, '2025-05-24 22:29:23'),
(97, '2505240606271600600', 'https://oss-cf.cjdropshipping.com/product/2025/05/24/06/e324c6f8-89e0-486e-ac9c-91f3a48eb3b3.jpg', 1, '2025-05-24 22:29:25'),
(98, '2505240606081627000', 'https://cf.cjdropshipping.com/quick/product/53f43802-3df7-4b9b-8400-cfcbb3b3d2d6.jpg', 1, '2025-05-24 22:29:26');

-- --------------------------------------------------------

--
-- Structure de la table `sync_logs`
--

CREATE TABLE `sync_logs` (
  `id` int(11) NOT NULL,
  `sync_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `products_added` int(11) NOT NULL DEFAULT 0,
  `products_updated` int(11) NOT NULL DEFAULT 0,
  `products_failed` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL,
  `error_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sync_logs`
--

INSERT INTO `sync_logs` (`id`, `sync_date`, `products_added`, `products_updated`, `products_failed`, `status`, `error_message`) VALUES
(1, '2025-05-24 22:17:04', 0, 50, 0, 'success', ''),
(2, '2025-05-24 22:29:04', 0, 50, 0, 'success', '');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `firstname`, `lastname`, `role`, `created_at`, `last_login`) VALUES
(1, 'banpremier003@gmail.com', '$2y$10$R3VnDGM7aPtQrTyUt7lnA.1zr/2l9HxT1i4tauCySbl/hrI8J1tRm', 'Admin', 'tiraoui', 'admin', '2025-05-25 16:00:09', '2025-05-25 19:50:25'),
(4, 'mouhssinetiraoui00@gmail.com', '$2y$10$8OhmNn622nozAXM.yLAAIOOC6AfjYRn4IMY9EfVLcpTpFZqUmXoTi', 'user', 'tiraoui', 'user', '2025-05-25 16:21:25', '2025-05-25 16:59:33');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoryId` (`categoryId`);

--
-- Index pour la table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `sync_logs`
--
ALTER TABLE `sync_logs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT pour la table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT pour la table `sync_logs`
--
ALTER TABLE `sync_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`categoryId`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
