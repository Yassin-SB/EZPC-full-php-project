-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 30 avr. 2025 à 19:15
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ezpc_db`
--
CREATE DATABASE IF NOT EXISTS `ezpc_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `ezpc_db`;

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id_cat` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(45) NOT NULL,
  `id_catparent` int DEFAULT NULL,
  PRIMARY KEY (`id_cat`),
  UNIQUE KEY `nom_UNIQUE` (`nom`),
  KEY `id_catparent_idx` (`id_catparent`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id_cat`, `nom`, `id_catparent`) VALUES
(1, 'Processors/CPUs', NULL),
(2, 'Motherboards', NULL),
(3, 'Computer Memory', NULL),
(4, 'Graphics Cards', NULL),
(5, 'Drives & Storage', NULL),
(11, 'AMD Processors', 1),
(12, 'INTEL Processors', 1),
(14, 'AMD Motherboards', 2),
(15, 'INTEL Motherboards', 2),
(17, 'Desktop Memory ', 3),
(18, 'Laptop Memory', 3),
(19, 'MAC Memory', 3),
(21, 'AMD Graphic Cards ', 4),
(22, 'INTEL Graphic Cards', 4),
(23, 'NVIDiA Graphic Cards', 4),
(25, 'SSDs', 5),
(26, 'HDDs', 5),
(27, 'CD/DVD', 5);

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

DROP TABLE IF EXISTS `commande`;
CREATE TABLE IF NOT EXISTS `commande` (
  `id_commande` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `prix_total` double NOT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_commande`),
  KEY `fk3_idx` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`id_commande`, `id_user`, `prix_total`, `date`) VALUES
(37, 19, 49.99, '2025-01-03 15:24:13'),
(38, 21, 2291.94, '2025-01-03 17:22:50');

-- --------------------------------------------------------

--
-- Structure de la table `commande_items`
--

DROP TABLE IF EXISTS `commande_items`;
CREATE TABLE IF NOT EXISTS `commande_items` (
  `id_ci` int NOT NULL AUTO_INCREMENT,
  `id_commande` int NOT NULL,
  `id_produit` int NOT NULL,
  `quantite` int DEFAULT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_ci`),
  KEY `fk4_idx` (`id_commande`),
  KEY `fk5_idx` (`id_produit`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `commande_items`
--

INSERT INTO `commande_items` (`id_ci`, `id_commande`, `id_produit`, `quantite`, `date`) VALUES
(45, 37, 40, 1, '2025-01-03 15:24:13'),
(46, 38, 41, 4, '2025-01-03 17:22:50'),
(47, 38, 33, 2, '2025-01-03 17:22:50');

--
-- Déclencheurs `commande_items`
--
DROP TRIGGER IF EXISTS `update_total_after_delete`;
DELIMITER $$
CREATE TRIGGER `update_total_after_delete` AFTER DELETE ON `commande_items` FOR EACH ROW BEGIN
    UPDATE commande
    SET prix_total = (
        SELECT SUM(ci.quantite * p.prix)
        FROM commande_items ci
        JOIN produits p ON ci.id_produit = p.id_produit
        WHERE ci.id_commande = OLD.id_commande
    )
    WHERE id_commande = OLD.id_commande;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `update_total_after_insert`;
DELIMITER $$
CREATE TRIGGER `update_total_after_insert` AFTER INSERT ON `commande_items` FOR EACH ROW BEGIN
    UPDATE commande
    SET prix_total = (
        SELECT SUM(ci.quantite * p.prix)
        FROM commande_items ci
        JOIN produits p ON ci.id_produit = p.id_produit
        WHERE ci.id_commande = NEW.id_commande
    )
    WHERE id_commande = NEW.id_commande;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `update_total_after_update`;
DELIMITER $$
CREATE TRIGGER `update_total_after_update` AFTER UPDATE ON `commande_items` FOR EACH ROW BEGIN
    UPDATE commande
    SET prix_total = (
        SELECT SUM(ci.quantite * p.prix)
        FROM commande_items ci
        JOIN produits p ON ci.id_produit = p.id_produit
        WHERE ci.id_commande = NEW.id_commande
    )
    WHERE id_commande = NEW.id_commande;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `panier_items`
--

DROP TABLE IF EXISTS `panier_items`;
CREATE TABLE IF NOT EXISTS `panier_items` (
  `id_panier` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_produit` int NOT NULL,
  `quantite` int DEFAULT NULL,
  `prix` double DEFAULT NULL,
  PRIMARY KEY (`id_panier`),
  KEY `fk6_idx` (`id_user`),
  KEY `fk7_idx` (`id_produit`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `panier_items`
--

INSERT INTO `panier_items` (`id_panier`, `id_user`, `id_produit`, `quantite`, `prix`) VALUES
(54, 19, 39, 1, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

DROP TABLE IF EXISTS `produits`;
CREATE TABLE IF NOT EXISTS `produits` (
  `id_produit` int NOT NULL AUTO_INCREMENT,
  `id_cat` int DEFAULT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `description` longtext,
  `prix` double NOT NULL DEFAULT '0',
  `stock` int DEFAULT '0',
  `datecreation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `promotion` double DEFAULT '0',
  `nbvendues` int DEFAULT '0',
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_produit`),
  UNIQUE KEY `nom_UNIQUE` (`nom`),
  KEY `fk1_idx` (`id_cat`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id_produit`, `id_cat`, `nom`, `description`, `prix`, `stock`, `datecreation`, `promotion`, `nbvendues`, `image`) VALUES
(5, 11, 'AMD Ryzen 7 5800XT Vermeer AM4 3.80GHz 8-Core Boxed Processor - Wraith Prism Cooler', 'Designed for socket AM4 motherboards using the powerful Zen 3 architecture, the 7nm 5th generation Ryzen processor offers significantly improved performance compared to its predecessor. With a base clock speed of 3.8 GHz and a max boost clock speed of 4.8 GHz in addition to 32MB of L3 Cache, the Ryzen 7 5800XT is built to deliver the power needed to smoothly handle tasks ranging from content creation to immersive gaming experiences. You can boost performance further by overclocking this unlocked processor. Other features include support for PCIe Gen 4 technology and 3200 MHz DDR4 RAM with compatible motherboards.', 250, 49, '2024-12-14 15:47:01', 5, 0, '682196_721464_01_front_zoom.jpg'),
(6, 11, 'Ryzen 5 7600X Raphael AM5 4.7GHz 6-Core Boxed Processor - Heatsink Not Included', 'Welcome to the new era of performance. AMD Ryzen 7000 Series ushers in the speed of Zen 4 for gamers and creators with pure power to tackle any game or workflow on the digital playground.', 299.99, 29, '2024-12-14 16:16:33', 0, 0, '674522_643908_01_front_zoom'),
(7, 11, 'AMD Ryzen 7 9700X Granite Ridge AM5 3.80GHz 8-Core Boxed Processor - Heatsink Not Included', 'Shatter any obstacle that stands between you and victory with the raw power and performance of AMD Ryzen 9000 Series processors. Our \"Zen 5\" core technology evolves with your needs, so you can go big today and tomorrow. Rebel against underwhelming performance. AMD Ryzen 9000 Series processors give you the strength to level up and provide an insane experience for gaming, streaming, creating, and all the worlds you want to master. With features like AMD EXPO technology one-touch memory overclocking, you can game with the ultra-high performance available on a PC and go for more glory.', 359.99, 600, '2024-12-14 16:27:04', 0, 0, '682199_721217_01_front_zoom'),
(8, 12, 'Intel Core i7-12700K Alder Lake 3.6GHz Twelve-Core LGA 1700 Boxed Processor - Heatsink Not Included', 'The 12th generation of Intel processors provides gamers with an ideal setup for streaming or media production, boasting industry-first PCIe 5.0 compatibility and notable DDR5 memory advancements. With speeds up to 4800 MT/s, DDR5 advancements also prove to be beneficial to memory bandwidth, as it too sees an increase in speed. The processor also features an integrated UHD Graphics 770 chip with 8K HDR support and the ability to view four simultaneous 4K displays. Using Gaussian & Neural Accelerator 3.0 (GNA) technology, noise suppression and background blurring are achieved more efficiently and effectively. A standout feature of this processor is the 12 cores unlocked for overclocking, which share 25MB of the L3 cache. The highest clock speed available for these processors is 5.0 GHz, while the base model sits at 3.6 GHz. Managed by the Intel Thread Director, the cores support the operating system to more intelligently channel workloads to the right core at the right time.', 449.99, 255, '2024-12-14 16:31:16', 0, 0, '641917_326652_01_front_zoom'),
(9, 12, 'Intel Core i5-12600KF Alder Lake 3.7GHz Ten-Core LGA 1700 Boxed Processor - Heatsink Not Included', '12th Gen Intel Core i5-12600KF unlocked desktop processor, without processor graphics. Featuring PCIe Gen 5.0 & 4.0 support, DDR5 & DDR4 support, unlocked 12th Gen Intel Core­ desktop processors are optimized for productivity, gaming, and overclocking. Discrete graphics required. Thermal solution NOT included in the box. Compatible with 600 series chipset based motherboards.', 299, 16, '2024-12-14 16:33:59', 0, 0, '677153_669424_01_front_zoom'),
(10, 12, 'Intel Core i5-14400 Raptor Lake Ten-Core LGA 1700 Boxed Processor - Intel Laminar RM1 Cooler Included', 'Featuring PCIe 5.0 & 4.0 support, DDR5 and DDR4 support, Intel Core i5 desktop processors (14th gen) are optimized for gamers and productivity and help deliver high performance. Compatible with Intel 700 Series and Intel 600 Series Chipset based motherboards. 65W Processor Base Power. Intel Laminar RM1 included in the box.', 239.99, 55, '2024-12-14 16:35:24', 15, 3, '676177_660654_01_front_zoom'),
(11, 14, 'Gigabyte B650 Gaming X AX V2 AMD AM5 ATX Motherboard', 'GIGABYTE Ultra Durable motherboards built with optimal components inside out provide the prime performance and timeless platform.', 199, 12, '2024-12-14 16:58:36', 20, 6, '676262_660753_01_front_zoom'),
(12, 14, 'ASUS B650-E TUF Gaming WiFi AMD AM5 ATX Motherboard', 'ASUS TUF GAMING B650-E WIFI takes all the essential elements of the latest AM5 Socket for AMD Ryzen Desktop Processors and combines them with game-ready features and proven durability. Engineered with military-grade components, an upgraded power solution and a comprehensive cooling system, this motherboard goes beyond expectations with rock-solid and stable performance for marathon gaming. TUF GAMING motherboards also undergo rigorous endurance testing to ensure that they can handle conditions where others may fail. Aesthetically, this model incorporates rugged off-black and geometric design elements to reflect the dependability and stability that defines the TUF GAMING series.', 199.99, 60, '2024-12-14 17:02:35', 0, 0, '679451_693788_01_front_zoom'),
(13, 14, 'ASUS B650-A ROG Strix Gaming WiFi AMD AM5 ATX Motherboard', 'ASUS ROG STRIX B650-A GAMING WIFI 6E white and silver scheme motherboard is designed with boosted power delivery and optimized cooling to cope with the demands of powerful Ryzen 7000 series processors. Along with Wi-Fi 6E and PCI Express 5.0 for superfast transfer speeds and storage, ROG STRIX B650-A GAMING WIFI 6E is ready to take the helm of your clean and neat white gaming gear.', 259, 9, '2024-12-14 17:04:08', 30, 2, '659715_513747_01_front_zoom'),
(14, 15, 'ASUS Z790-F ROG Strix Gaming WiFi II Intel LGA 1700 ATX Motherboard', 'Beneath its dark heatsinks and brooding aesthetic, the ROG Strix Z790-F packs a power solution and overclocking features that push Intel Core processors to new heights. Flush with advanced thermal management for quieter builds, along with support for DDR5, PCIe 5.0, and WiFi 6E, this well-equipped powerhouse delivers massive bandwidth for everything.', 359.99, 65, '2024-12-14 17:06:00', 0, 0, '673257_633602_01_front_zoom'),
(15, 15, 'ASUS Z790-Plus TUF Gaming WiFi D5 Intel LGA 1700 ATX Motherboard', 'TUF GAMING Z790-PLUS WIFI D4 takes all the essential elements of the latest Intel processors and combines them with game-ready features and proven durability. Engineered with military-grade components, an upgraded power solution and a comprehensive cooling system, this motherboard goes beyond expectations with rock-solid performance for marathon gaming. TUF GAMING motherboards also undergo rigorous endurance testing to ensure that they can handle conditions where others may fail. Aesthetically, this model incorporates an embossed nameplate and honeycomb design elements to reflect the dependability and stability that defines the TUF GAMING series.', 249.99, 79, '2024-12-14 17:08:24', 0, 0, '662599_536334_01_front_zoom'),
(16, 15, 'Gigabyte Z890 AORUS ELITE WIFI7 Intel LGA 1851 ATX Motherboard', 'Designed for the Intel Core Ultra Series 2 processors, the Gigabyte Z890 AORUS ELITE WIFI7 LGA 1851 ATX Motherboard features the LGA 1851 socket and Intel Z890 chipset for optimal performance. The motherboard has a 16+1+2 twin digital VRM design. With four DDR5-5600 slots, one PCIe 5.0 x16 slot, two PCIe 4.0 x4 slots, four M.2 slots, and four SATA slots, you\'ll be able to build a powerful gaming PC. With EZ-Latch, installing a graphics card and M.2 SSDs with heat sinks is user-friendly and doesn\'t require tools. For online connectivity, the motherboard provides fast 2.5GbE LAN and Wi-Fi 7 (802.11be).', 289, 54, '2024-12-14 17:09:22', 12, 1, '687163_768895_01_front_zoom'),
(17, 17, 'G.Skill Flare X5 Series 32GB (2 x 16GB) DDR5-6000 PC5-48000 CL36 Dual Channel Desktop Memory Kit F5-6000J3636F16GX2-FX5 - Black', 'Flare X5 series DDR5 memory is designed and optimized for the latest DDR5-enabled AMD Ryzen platforms, and supports AMD EXPO overclocking profiles to enable easy memory overclocking by simply enabling the EXPO profile in the BIOS with a compatible motherboard and processor. Featuring a 33mm low-profile height and built with hand-screened IC chips tested for quality and performance, the Flare X5 is an excellent choice for compact performance PC builds.', 149.99, 89, '2024-12-14 17:12:22', 0, 0, '653727_440792_01_front_zoom'),
(18, 17, 'Corsair VENGEANCE RGB 32GB (2 x 16GB) DDR5-6000 PC5-48000 CL36 Dual Channel Desktop Memory Kit CMH32GX5M2M6000Z36 - Black', 'CORSAIR VENGEANCE RGB DDR5 memory for AMD delivers DDR5 performance, higher frequencies, and greater capacities optimized for AMD motherboards while lighting up your PC with dynamic, individually addressable ten-zone RGB lighting. Tightly screened high-frequency memory chips enable faster processing, rendering, and buffering than ever, with onboard voltage regulation providing reliable power at high frequencies for easy, finely controlled overclocking.', 99.99, 90, '2024-12-14 17:14:34', 0, 0, '688526_781021_01_front_zoom'),
(19, 17, 'G.Skill Trident Z5 Neo RGB 64GB (2 x 32GB) DDR5-6000 PC5-48000 CL30 Dual Channel Desktop Memory Kit F5-6000J3040G32GX2-TZ5NR - Black', 'Trident Z5 Neo RGB DDR5 memory is designed for ultra-high overclocked performance on DDR5-enabled AMD platforms. Featuring AMD EXPO overclocking technology for easy memory overclocking on supported AMD platforms, the Trident Z5 Neo RGB series is the ideal choice for building high-performance systems', 319.99, 97, '2024-12-14 17:14:34', 10, 3, '664697_555466_01_front_zoom'),
(20, 18, 'Crucial 64GB (2 x 32GB) DDR5-5600 PC5-44800 CL46 Dual Channel Laptop Memory Kit CT2K32G56C46S5 - Black', 'Crucial DDR5 Memory has the blazing speed and massive bandwidth needed for the next generation of multi-core CPUs. This innovative technology empowers your system to multitask better, load, analyze, edit, and render faster, game with higher frame rates, uncover data insights faster, enhance productivity to save time and money, significantly reduce lag for heavy workloads and optimize power efficiency over the previous generation.', 217, 50, '2024-12-14 17:22:14', 12, 0, '661345_524876_01_front_zoom'),
(21, 18, 'Patriot 32GB DDR4-3200 PC4-25600 CL-18 SO-DIMM Memory PVS432G320C8S', 'Patriot\'s Viper Steel SODIMM deliver cutting-edge performance for notebooks and small form-factor PC\'s without sacrificing reliability. Built for the latest Intel and AMD platforms, Viper Steel SODIMM provides the best performance and stability for the most demanding computer environments.', 74.99, 23, '2024-12-14 17:22:14', 5, 0, '666070_575472_01_front_zoom'),
(22, 18, 'Corsair VENGEANCE Performance 16GB DDR4-3200 PC4-25600 CL-22 SO-DIMM Memory Module CMSX16GX4M1A3200C22', 'CORSAIR high performance VENGEANCE SODIMM memory kit, 3200MHz CL22 1.2V, allows you to automatically boost performance of your without BIOS reconfiguration. Slim and attractive design to ensure physical compatibility with all 11th Generation Intel Core Processors equipped DDR4 notebooks and NUCs. Each module is built using carefully selected DRAM to allow excellent stability and is backed by Corsairs limited lifetime warranty.', 44.99, 13, '2024-12-14 17:22:14', 0, 0, '665503_566109_01_front_zoom'),
(25, 19, 'Crucial 32GB DDR4-2666 SODIMM for Mac', 'Designed to help your system run faster and smoother, Crucial Laptop Memory is one of the easiest and most affordable ways to improve your system’s performance', 58.99, 15, '2024-12-14 17:26:43', 0, 0, 'CT32G4S266M-crucial-memory-for-mac-compatibility-image'),
(26, 21, 'AMD Radeon RX 7900 XT Triple Fan 20GB GDDR6 PCIe 4.0 Graphics Card', 'Built on the groundbreaking AMD RDNA 3 architecture with chiplet technology, AMD Radeon RX 7900 XT graphics deliver next-generation performance, visuals, and efficiency at 4K and beyond', 859.99, 63, '2024-12-14 17:34:02', 15, 0, '681803_717322_01_front_zoom'),
(27, 21, 'Sapphire Technology AMD Radeon RX 7900 XTX Nitro Plus Vapor-X RGB Overclocked Triple Fan 24GB GDDR6 PCIe 4.0 Graphics Card', 'Experience unprecedented performance, visuals, and efficiency at 4K and beyond with AMD Radeon RX 7900 XTX graphics card, the worlds first gaming GPUs powered by AMD RDNA 3 chiplet technology. Immerse yourself in breathtaking visuals with the pinpoint color accuracy of AMD Radiance Display Engine and boost frame rates with AMD FidelityFX Super Resolution and Radeon Super Resolution upscaling technologies. To unlock even more performance, combine AMD Radeon RX 7000 Series graphics and compatible AMD Ryzen processors to activate AMD smart technologies.', 1000, 25, '2024-12-14 17:34:02', 10, 0, '662381_533786_01_front_zoom'),
(28, 21, 'PowerColor Radeon RX 7800 XT Hellhound Spectral White RGB Overclocked Triple Fan 16GB GDDR6 PCIe 4.0 Graphics Card', 'For enthusiasts who crave a pristine and elegant look without compromising on performance, the Spectral White Edition of the RX 7800 XT Hellhound is the epitome of design excellence. Unlike other brands that merely switch the cooler\'s color, PowerColor has gone the extra mile. The Spectral White Edition boasts an all-white PCB, a first-of-its-kind all-white heatsink, and a white cooler, setting a new benchmark in graphics card design.', 569.99, 90, '2024-12-14 17:34:02', 0, 0, '674026_641167_01_front_zoom'),
(29, 22, 'Sparkle Intel Arc A770 ROC Luna Overclocked Dual Fan 16GB GDDR6 PCIe 4.0 Graphics Card', 'The SPARKLE Intel Arc A770 ROC Luna OC Edition is the brand-new BEST choice of the series, comes with Intel Arc A770 and 16GB GDDR6. This card is designed for ultimate gaming experience and maximum productivity. ROC series equipped dual 100mm double-ball bearing fan with 2.5-slot heatsink design, reduce the length of card effetely and ready to fit into the compact system. Furthermore, 100mm DBB fans have brought the higher efficiency of cooling, lead to an ultra-silence experience without compromising performance', 259.99, 52, '2024-12-14 17:38:44', 0, 0, '680438_723916_01_front_zoom'),
(30, 22, 'ASRock Intel Arc B580 Challenger Overclocked Dual Fan 12GB GDDR6 PCIe 4.0 Graphics Card', NULL, 259.99, 3, '2024-12-14 17:38:44', 0, 0, '689223_787648_01_front_zoom'),
(31, 22, 'Sparkle Intel Arc A750 ORC RGB Overclocked Dual Fan 8GB GDDR6 PCIe 4.0 Graphics Card', 'The SPARKLE Intel Arc comes with the TORN cooling solution featuring 0dB mode and customized AXL Fan. The fan blades are designed with stripe structures and polished surfaces on the bottom side, enhancing its cooling performance even further.', 199.99, 15, '2024-12-14 17:38:44', 5, 0, '668259_599126_01_front_zoom'),
(32, 23, 'ASUS NVIDIA GeForce RTX 4080 Super TUF Gaming RGB Overclocked Triple Fan 16GB GDDR6X PCIe 4.0 Graphics Card', 'ASUS TUF Gaming NVIDIA GeForce RTX 4080 SUPER OC has been redesigned to house the all-new Ada Lovelace architecture, from NVIDIA. With up to 2x the performance of the previous generation and an all-new design, the ASUS TUF Gaming GeForce RTX 4080 SUPER OC will provide your next unparalleled gaming experience.', 1099.99, 65, '2024-12-14 17:45:10', 0, 0, '676132_659532_01_front_zoom'),
(33, 23, 'Gigabyte NVIDIA GeForce RTX 4080 Super Windforce V2 Triple Fan 16GB GDDR6X PCIe 4.0 Graphics Card', 'Ahead of its time, ahead of the game is the GIGABYTE GeForce RTX 4080 SUPER WINDFORCE V2 16G Graphics Cards. Powered by NVIDIA\'s new RTX architecture, the GeForce RTX 4080 SUPER WINDFORCE V2 16G brings stunning visuals, amazingly fast frame rates, and AI acceleration to games and creative applications with its enhanced RT Cores and Tensor Cores, along with a staggering 16 GB of GDDR6X memory.', 999.99, 68, '2024-12-14 17:45:10', 12, 2, '676365_661918_01_front_zoom'),
(34, 23, 'ASUS NVIDIA GeForce RTX 4080 Super ProArt Overclocked Triple Fan 16GB GDDR6X PCIe 4.0 Graphics Card', 'The brand new ASUS ProArt GeForce RTX 4080 SUPER OC has arrived. With an all-new slim 2.5 slot small form factor design, the 4080 SUPER OC is now ready for any case and any environment you find yourself in. From 3D rendering to video and photo editing, you\'ll be sure to have all the power in the smallest package possible', 1149, 17, '2024-12-14 17:45:10', 5, 1, '676921_666446_01_front_zoom'),
(35, 25, 'Samsung 990 PRO 2TB Samsung V NAND 3-bit MLC PCIe Gen 4 x4 NVMe M.2 Internal SSD', 'Reach maximum performance of PCIe 4.0. Experience longer-lasting performance at amazing speed. The in-house controller\'s smart heat control delivers outstanding power efficiency while maintaining speed and performance to always keep you at the top of your game.', 279.99, 90, '2024-12-14 17:49:52', 0, 0, '660429_516120_01_front_zoom'),
(36, 25, 'SK Hynix Platinum P41 2TB 176L 3D TLC NAND Flash PCIe Gen 4 x4 NVMe M.2 Internal SSD', 'Time Tested TBW The Platinum P41 has been fine tuned to lead the industry in sustained performance. Enduring rigorous 1,000-hour stress tests the Platinum P41 boasts an outstanding 1,200-TBW (Terabytes Written, 2TB standard) rating. Simple Data Migration We provide our customized SK hynix edition Macrium cloning software for data transfers and OS migration along with our Easy Kit software so you can check the performance and status of your SSD at a glance. Platinum 5-Year Protection SK hynix promises quality with confidence. Our products have long lasting performance so our loyal customers can purchase with peace of mind. Massive Capacities With the sleek 22X80mm stick-type M.2 form factor, the Platinum P41 SSD is not only compatible with desktops, but also easily installs into laptops.', 250.99, 78, '2024-12-14 17:49:52', 0, 0, '671857_618058_01_front_zoom'),
(37, 25, 'WD Black 2TB D50 Game Dock NVMe SSD, RGB with Thunderbolt 3 Connectivity, Up to 3,000 MB/s - WDBA3U0020BBK-NESN', 'Transform your laptop into a fully integrated gaming station, clearing the clutter through a single Thunderbolt 3 cord with multiple ports to seamlessly connect your peripherals, giving you fast speeds with NVMe technology and more capacity for your games.', 199.99, 12, '2024-12-14 17:49:52', 0, 0, '628273_174706_01_front_zoom'),
(38, 26, 'Seagate Ironwolf 8TB 7200 RPM SATA III 6Gb/s 3.5\" Internal NAS CMR Hard Drive', 'Designed for use in 1-8 bay NAS environments within home, SOHO, and SMB environments, the 8TB IronWolf 7200 rpm SATA III 3.5\" Internal NAS HDD from Seagate is built to withstand 24x7 operations, or 8760 hours per year. This 8TB drive has been equipped with a SATA III 6 Gb/s interface, a rotational speed of 7200 rpm, a 3.5\" form factor, and a 256MB cache, providing users with an ample storage capacity and data transfer speeds of up to 210 MB/s. This IronWolf hard drive also has an MTBF rating of 1 million hours, 600,000 load/unload cycles, 1 in 1015 non-recoverable read errors per bits read, and a workload rate of up to 180TB per year. Protection is provided by a limited 3-year warranty.', 194.99, 56, '2024-12-14 18:00:47', 0, 0, '611674_993089_01_front_zoom'),
(39, 26, 'Toshiba N300 4TB 7200 RPM SATA III 6Gb/s 3.5\" Internal CMR NAS Hard Drive', 'Toshibas N300 3.5-inch NAS internal hard drive is designed to meet the reliability, performance, endurance, and scalability requirements of 24/7 network attached storage application for personal, home office and small business use.', 124, 148, '2024-12-14 18:00:47', 3, 9, '679595_694950_01_front_zoom'),
(40, 26, 'Toshiba Canvio Ready 1TB USB 3.1 (Gen 1 Type-A) 2.5\" Portable External Hard Drive - Black', 'Toshibas Canvio Ready external hard drive uses USB 3.1 Gen 1 technology for transfer speeds of up to 5 Gbit/s and with plug-and-play technology you are able to quickly save and secure up to 4 TB of media to a compact, portable device. The Canvio Ready includes ramp load design that prevents damage to the disk while mobile, and a shock sensor that will cut the power to protect data in the event of physical stress so you can rest assured that your files are safe.', 49.99, 15, '2024-12-14 18:00:47', 2, 8, '629585_202424_01_front_zoom'),
(41, 26, 'WD My Passport 1TB USB 3.2 (Gen 1 Type-A) 2.5\" Portable External Hard Drive - Black', 'The My Passport drive is trusted, portable storage that gives you the confidence and freedom to drive forward in life. With a new, stylish design that fits in the palm of your hand, theres space to store, organize, and share your photos, videos, music, and documents. Perfectly paired with WD Backup software and password protection,the My Passport drive helps keep your digital lifes contents safe.', 72.99, 103, '2024-12-14 18:00:47', 6, 8, '611097_993527_01_front_zoom'),
(42, 27, 'LG Ultra Slim Portable DVD Writer with M-DISC Support (Refurbished)', 'The M-DISC uses a patent rock-like recording surface instead of organic dye to etch your data onto a disc. The M-DISC has been tested and proven to outlast standard DVDs currently on the market. Burn more discs in less time with 8x DVD-Max writing speed. LG Super Multi Compatibility optical drives can read and write different types of disc formats in one convenient package. Enjoy complete freedom in use with compatible support for Windows 10 and MAC OS. The overall slim design of only 0.55 inches high optimizes space for the slim OPU installation. It allows for a sleek companion to thin Ultrabooks and enhanced portability.', 16.99, 10, '2024-12-14 18:00:47', 0, 0, '675463_653360_01_front_zoom'),
(43, 27, 'ASUS SDRW-08D2S-U/BK USB 2.0 External Slim DVD Burner', 'SDRW-08D2S-U Optical Drive - Harmony of Technology and Aesthetics Burn disc with enhanced data protection in just 3 quick steps and intuitive Disc Encryption lets you do this easily Elegant appearance embody the harmonious marriage of technology and aesthetics Disc Encryption doubles the security with password-controlled and hidden-file functionality Drag-and-burn interface accomplishes entire jobs in three easy steps', 28.99, 17, '2024-12-14 18:00:47', 0, 0, '381285_211847_01_front_zoom'),
(44, 27, 'Vantec NexStar DX USB 3.0 External Enclosure for SATA Blu-Ray/CD/DVD Drive', 'NexStar DX converts 5.25\" desktop optical drive to an external enclosure. The internal old optical drive became a portable USB 3.0 enclosure that fits with desktops or laptops. This enclosure supports read/write optical drive like Blu-Ray drive, CD and DVD. The aluminum case keeps the device cool and operating at optimum conditions. The NexStar DX external 5.25\" enclosure is the smart way to optimize and share your optical drive with all your system.', 53.99, 12, '2024-12-14 18:00:47', 0, 0, '474636_239780_01_front_zoom');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `login` varchar(45) NOT NULL,
  `password` varchar(250) NOT NULL,
  `role` varchar(45) NOT NULL DEFAULT 'user',
  `username` varchar(45) NOT NULL,
  `phone_number` int NOT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `login_UNIQUE` (`login`),
  UNIQUE KEY `phone_number_UNIQUE` (`phone_number`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id_user`, `login`, `password`, `role`, `username`, `phone_number`) VALUES
(18, 'admin', '$2y$10$tbnCN8MDOioFh8UEhAeGduUOeE5fCUQxMz8UUUB2b31gGxsbLA8d.', 'admin', 'admin', 56566566),
(19, 'yassinsb', '$2y$10$RP8CZVL9Fi43.rsro1bXEu7RKFMKRaj6HvIXfu358/.s/i/ILTPba', 'user', 'yassin sassi bzeouich', 54561568),
(20, 'hey', '$2y$10$kVcXOW7WrVoTUho60qNZ6.83JVyu2z2tYdVU5Ta5A7vPRXvaA/B06', 'user', 'hey', 56665666),
(21, '92605702', '$2y$10$qJmmIYJzWgzIxlGceX36OuoC/l/h/V.SitOxNMKIW5HyKqRnnvmye', 'user', 'amjed ba', 92605702);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk1` FOREIGN KEY (`id_catparent`) REFERENCES `categories` (`id_cat`);

--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `fk3` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `commande_items`
--
ALTER TABLE `commande_items`
  ADD CONSTRAINT `fk4` FOREIGN KEY (`id_commande`) REFERENCES `commande` (`id_commande`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk5` FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id_produit`);

--
-- Contraintes pour la table `panier_items`
--
ALTER TABLE `panier_items`
  ADD CONSTRAINT `fk6` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `fk7` FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id_produit`);

--
-- Contraintes pour la table `produits`
--
ALTER TABLE `produits`
  ADD CONSTRAINT `fk2` FOREIGN KEY (`id_cat`) REFERENCES `categories` (`id_cat`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
