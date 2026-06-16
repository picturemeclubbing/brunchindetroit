-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: brunchindetroit
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `galleries`
--

LOCK TABLES `galleries` WRITE;
/*!40000 ALTER TABLE `galleries` DISABLE KEYS */;
REPLACE INTO `galleries` (`id`, `slug`, `title`, `venue_id`, `event_date`, `location_label`, `description`, `cover_image_path`, `gallery_url`, `is_published`, `is_featured`, `created_at`, `updated_at`) VALUES (1,'sunday-jazz-brunch-2026','Sunday Jazz Brunch at The Lowell',NULL,'2026-06-14','Midtown Detroit','Live jazz, bottomless mimosas, and a curated brunch spread at one of Detroit\'s most elegant Sunday traditions.','https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=1200&h=800&fit=crop','https://brunchindetroit.smugmug.com/Events/Sunday-Jazz-Brunch-2026',1,1,'2026-06-15 10:00:00','2026-06-16 13:02:42'),(2,'bottomless-mimosa-pop-up-2026','Bottomless Mimosa Pop-Up',NULL,'2026-05-31','Downtown Detroit','A roaming mimosa pop-up featuring local DJs, floral installations, and a chef\'s-table brunch experience.','https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=1200&h=800&fit=crop','https://brunchindetroit.smugmug.com/Events/Bottomless-Mimosa-Pop-Up',1,1,'2026-06-01 12:00:00','2026-06-16 13:02:42'),(3,'rooftop-brunch-series-corktown','Rooftop Brunch Series — Corktown',NULL,'2026-05-17','Corktown, Detroit','Sunset rooftop brunch overlooking the Detroit skyline with small plates from five local kitchens.','https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=1200&h=800&fit=crop','https://brunchindetroit.smugmug.com/Events/Rooftop-Brunch-Corktown',1,0,'2026-05-18 09:30:00','2026-06-16 13:02:42'),(4,'brunch-and-art-bazaar-2026','Brunch & Art Bazaar',NULL,'2026-04-26','Eastern Market, Detroit','A collaborative brunch bazaar pairing Detroit artists with local pastry chefs and coffee roasters.','https://images.unsplash.com/photo-1551218808-94e220e084d2?w=1200&h=800&fit=crop','https://brunchindetroit.smugmug.com/Events/Brunch-Art-Bazaar-2026',1,0,'2026-04-27 11:00:00','2026-06-16 13:02:42'),(5,'womens-history-brunch-tea','Women\'s History Brunch & Tea',NULL,'2026-03-08','Midtown Detroit','A celebratory brunch honoring Detroit women in hospitality, featuring a paired tea service and panel discussion.','https://images.unsplash.com/photo-1559521783-1d1599583485?w=1200&h=800&fit=crop','https://brunchindetroit.smugmug.com/Events/Womens-History-Brunch-2026',1,0,'2026-03-09 14:00:00','2026-06-16 13:02:42'),(6,'new-year-day-recovery-brunch','New Year\'s Day Recovery Brunch',NULL,'2026-01-01','Downtown Detroit','Detroit\'s biggest New Year\'s Day brunch: hair-of-the-dog cocktails, comfort plates, and a live soul band.','https://images.unsplash.com/photo-1600891964599-f61ba0e24092?w=1200&h=800&fit=crop','https://brunchindetroit.smugmug.com/Events/NYE-Recovery-Brunch-2026',1,0,'2026-01-02 08:00:00','2026-06-16 13:02:42');
/*!40000 ALTER TABLE `galleries` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-16 13:40:31
