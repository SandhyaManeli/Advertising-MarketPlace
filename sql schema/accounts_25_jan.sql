-- MySQL dump 10.13  Distrib 5.7.20, for Linux (x86_64)
--
-- Host: localhost    Database: accounts
-- ------------------------------------------------------
-- Server version	5.7.20-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `client_types`
--

DROP TABLE IF EXISTS `client_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_types_type_unique` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_types`
--

LOCK TABLES `client_types` WRITE;
/*!40000 ALTER TABLE `client_types` DISABLE KEYS */;
INSERT INTO `client_types` VALUES (1,'bbi','2018-06-18 05:34:18','2018-06-18 05:34:18'),(2,'owner','2018-06-18 05:34:18','2018-06-18 05:34:18'),(3,'agency','2018-06-18 05:34:18','2018-06-18 05:34:18');
/*!40000 ALTER TABLE `client_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` int(10) unsigned NOT NULL,
  `super_admin` int(10) unsigned DEFAULT NULL,
  `company_slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clients_company_slug_unique` (`company_slug`),
  KEY `clients_type_foreign` (`type`),
  KEY `clients_super_admin_foreign` (`super_admin`),
  CONSTRAINT `clients_super_admin_foreign` FOREIGN KEY (`super_admin`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `clients_type_foreign` FOREIGN KEY (`type`) REFERENCES `client_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,'BBI',1,1,'bbi',1,'2018-06-18 05:34:18','2018-06-18 05:34:20'),(2,'Prakash Arts',2,NULL,'prakash-arts',1,'2018-06-18 05:59:38','2018-06-18 05:59:38'),(3,'Ad Age Outdoor (P) Ltd',2,NULL,'ad-age-outdoor-(p)-ltd',1,'2018-06-18 06:01:44','2018-06-18 06:01:44'),(4,'Sudhakar Ad\'s Pvt Ltd',2,NULL,'sudhakar-ad\'s-pvt-ltd',1,'2018-06-18 06:02:55','2018-06-18 06:02:55'),(5,'MR.Publicities',2,NULL,'mr.publicities',1,'2018-06-18 06:03:53','2018-06-18 06:03:53'),(6,'Ad-space',2,NULL,'ad-space',1,'2018-06-18 06:05:00','2018-06-18 06:05:00'),(7,'RAINBOW ADVERTISING',2,NULL,'rainbow-advertising',1,'2018-06-18 06:06:24','2018-06-18 06:06:24'),(8,'Bhaskar Arts',2,NULL,'bhaskar-arts',1,'2018-06-18 06:07:51','2018-06-18 06:07:51'),(9,'Impressions Outdoor Advertising',2,NULL,'impressions-outdoor-advertising',1,'2018-06-18 06:09:26','2018-06-18 06:09:26'),(10,'Vision Outdoors',2,NULL,'vision-outdoors',1,'2018-06-18 06:10:16','2018-06-18 06:10:16'),(11,'SreeVarma',2,NULL,'sreevarma',1,'2018-06-18 06:11:08','2018-06-18 06:11:08'),(12,'Frontline Advertisers',2,NULL,'frontline-advertisers',1,'2018-06-18 06:12:12','2018-06-18 06:12:12'),(13,'SRIVEN ADS',2,NULL,'sriven-ads',1,'2018-06-18 06:13:12','2018-06-18 06:13:12'),(14,'LEADSPACE',2,NULL,'leadspace',1,'2018-06-18 06:13:59','2018-06-18 06:13:59'),(15,'Colors Outdoor advertising',2,NULL,'colors-outdoor-advertising',1,'2018-06-18 06:15:15','2018-06-18 06:15:15'),(16,'Roshan Adz',2,NULL,'roshan-adz',1,'2018-06-18 06:15:56','2018-06-18 06:15:56'),(17,'Fore-sites',2,NULL,'fore-sites',1,'2018-06-18 06:16:38','2018-06-18 06:16:38'),(18,'In and Out Advertising',2,NULL,'in-and-out-advertising',1,'2018-06-18 06:17:20','2018-06-18 06:17:20'),(19,'OUTSPACE ADVERTISING',2,NULL,'outspace-advertising',1,'2018-06-18 06:18:05','2018-06-18 06:18:05'),(20,'Narayana Cine Arts',2,NULL,'narayana-cine-arts',1,'2018-06-18 06:18:58','2018-06-18 06:18:58'),(21,'OUTDOOR MEDIA SOLUTIONS',2,NULL,'outdoor-media-solutions',1,'2018-06-18 06:19:48','2018-06-18 06:19:48'),(22,'Adlike Outdoor media',2,NULL,'adlike-outdoor-media',1,'2018-06-18 06:20:27','2018-06-18 06:20:27'),(23,'Brand vision Advertising',2,NULL,'brand-vision-advertising',1,'2018-06-18 06:21:05','2018-06-18 06:21:05'),(25,'IKAR ADVERTISING',2,19,'ikar-advertising',1,'2018-06-29 17:20:26','2018-08-27 14:26:42'),(26,'Ad-zone Advertising',2,NULL,'ad-zone-advertising',1,'2018-07-10 15:45:36','2018-07-10 15:45:36'),(27,'BBI Advertising pvt ltd',2,NULL,'bbi-advertising-pvt-ltd',1,'2018-07-26 13:29:08','2018-07-26 13:29:08'),(28,'BBI Advertising pvt ltd BA',2,16,'bbi-advertising-pvt-ltd-ba',1,'2018-07-26 13:29:37','2018-07-26 13:29:37'),(29,'Vantage Advertising Private Limited',2,NULL,'vantage-advertising-private-limited',1,'2018-07-30 16:45:26','2018-07-30 16:45:26'),(30,'Hima Sailaja Ads',2,NULL,'hima-sailaja-ads',1,'2018-08-16 19:04:18','2018-08-16 19:04:18'),(31,'Istaa Ads',2,20,'istaa-ads',1,'2018-08-28 13:52:31','2018-08-28 13:52:31'),(32,'billboardsindia',2,35,'billboardsindia',1,'2018-11-16 18:50:29','2018-11-16 18:50:29'),(33,'Landmark LLC',2,38,'landmark-llc',1,'2018-12-17 19:14:08','2018-12-17 19:14:09'),(34,'Crompton Elevator Pvt Ltd',2,42,'crompton-elevator-pvt-ltd',1,'2018-12-25 14:51:41','2018-12-25 14:51:41'),(35,'DakshaTiffinService',2,56,'dakshatiffinservice',1,'2019-01-15 15:34:47','2019-01-15 15:34:47'),(36,'Jugnoo Media private Limited',2,57,'jugnoo-media-private-limited',1,'2019-01-15 23:11:13','2019-01-15 23:11:13'),(37,'TMC Test',2,58,'tmc-test',1,'2019-01-16 08:34:24','2019-01-16 08:34:24');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8_unicode_ci NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_reserved_at_index` (`queue`,`reserved_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2017_09_05_083648_entrust_setup_tables',1),(2,'2018_03_19_120515_create_jobs_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_role`
--

DROP TABLE IF EXISTS `permission_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_role` (
  `role_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `permission_role_role_id_foreign` (`role_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_role`
--

LOCK TABLES `permission_role` WRITE;
/*!40000 ALTER TABLE `permission_role` DISABLE KEYS */;
INSERT INTO `permission_role` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,18),(1,19),(1,20),(1,21),(1,22),(1,23),(1,24),(1,25),(1,26),(1,27),(1,28),(1,29),(1,30),(1,31),(1,32),(1,33),(1,34),(1,35),(1,36),(1,37),(1,38),(1,39),(1,40),(1,41),(1,42),(1,43),(1,44),(1,45),(1,46),(1,47),(1,48),(1,49),(1,50),(1,51),(1,52),(1,53),(1,54),(1,55),(1,56),(1,57),(1,58),(1,59),(1,60),(1,61),(1,62),(1,63),(1,64),(1,65),(1,66),(1,67),(1,68),(1,69),(1,70),(1,71),(1,72),(1,73),(1,74),(1,75),(1,76),(1,77),(1,78),(1,79),(1,80),(1,81),(1,82),(1,83),(1,84),(1,85),(1,86),(1,87),(1,88),(1,89),(1,90),(1,91),(1,92),(1,93),(1,94),(1,95),(1,96),(1,97),(1,98),(1,99),(1,100),(1,101),(1,102),(1,103),(1,104),(1,105),(1,106),(1,107),(1,108),(1,109),(1,110),(1,111),(1,112),(1,113),(1,114),(1,116),(1,117),(1,118),(1,119),(1,120),(1,121),(1,122),(1,123),(1,124),(1,125),(1,126),(1,127),(1,128),(1,129),(1,130),(1,131),(1,132),(1,133),(1,134),(1,135),(1,136),(1,137),(1,138),(1,139),(1,140),(1,141),(2,1),(2,2),(2,3),(2,4),(2,5),(2,6),(2,15),(2,17),(2,18),(2,27),(2,31),(2,32),(2,33),(2,34),(2,35),(2,36),(2,38),(2,41),(2,42),(2,43),(2,44),(2,47),(2,50),(2,70),(2,71),(2,72),(2,73),(2,74),(2,75),(2,76),(2,77),(2,78),(2,90),(2,91),(2,95),(2,98),(2,100),(2,102),(2,123),(2,125),(2,127),(2,128),(2,131),(2,132),(2,133),(2,134),(2,135),(2,136),(2,137),(2,140),(2,141),(3,1),(3,2),(3,3),(3,4),(3,5),(3,6),(3,7),(3,8),(3,9),(3,10),(3,11),(3,12),(3,13),(3,14),(3,15),(3,16),(3,17),(3,18),(3,19),(3,20),(3,21),(3,22),(3,23),(3,24),(3,25),(3,26),(3,27),(3,28),(3,29),(3,30),(3,31),(3,32),(3,33),(3,34),(3,35),(3,36),(3,37),(3,38),(3,39),(3,40),(3,41),(3,42),(3,43),(3,44),(3,45),(3,46),(3,47),(3,48),(3,49),(3,50),(3,51),(3,52),(3,53),(3,54),(3,55),(3,56),(3,57),(3,58),(3,59),(3,60),(3,61),(3,62),(3,63),(3,64),(3,65),(3,66),(3,67),(3,68),(3,69),(3,70),(3,71),(3,72),(3,73),(3,74),(3,75),(3,76),(3,77),(3,78),(3,79),(3,80),(3,81),(3,82),(3,83),(3,84),(3,85),(3,86),(3,87),(3,88),(3,89),(3,90),(3,91),(3,92),(3,93),(3,94),(3,95),(3,96),(3,97),(3,98),(3,99),(3,100),(3,101),(3,102),(3,103),(3,104),(3,105),(3,106),(3,107),(3,108),(3,109),(3,110),(3,111),(3,112),(3,113),(3,114),(3,116),(3,117),(3,118),(3,119),(3,120),(4,1),(4,2),(4,3),(4,4),(4,5),(4,6),(4,7),(4,8),(4,9),(4,10),(4,11),(4,12),(4,13),(4,14),(4,17),(4,18),(4,19),(4,20),(4,22),(4,27),(4,28),(4,29),(4,30),(4,35),(4,56),(4,101),(5,1),(5,2),(5,3),(5,4),(5,5),(5,6),(5,7),(5,8),(5,9),(5,10),(5,11),(5,12),(5,13),(5,14),(5,15),(5,16),(5,17),(5,18),(5,19),(5,20),(5,21),(5,22),(5,23),(5,24),(5,25),(5,26),(5,27),(5,28),(5,29),(5,30),(5,31),(5,32),(5,33),(5,34),(5,35),(5,36),(5,37),(5,38),(5,39),(5,40),(5,41),(5,42),(5,43),(5,44),(5,45),(5,46),(5,47),(5,48),(5,49),(5,50),(5,51),(5,52),(5,53),(5,54),(5,55),(5,56),(5,57),(5,58),(5,59),(5,60),(5,61),(5,62),(5,63),(5,64),(5,65),(5,66),(5,67),(5,68),(5,69),(5,70),(5,71),(5,72),(5,73),(5,74),(5,75),(5,76),(5,77),(5,78),(5,79),(5,80),(5,81),(5,82),(5,83),(5,84),(5,85),(5,86),(5,87),(5,88),(5,89),(5,90),(5,91),(5,92),(5,93),(5,94),(5,95),(5,96),(5,97),(5,98),(5,99),(5,100),(5,101),(5,102),(5,103),(5,104),(5,105),(5,106),(5,107),(5,108),(5,109),(5,110),(5,111),(5,112),(5,113),(5,114),(5,116),(5,117),(5,118),(5,119),(5,120),(5,121),(5,122),(5,123),(5,124),(5,125),(5,126),(5,131),(5,132),(5,134),(5,135),(5,137),(5,138),(5,140),(5,141);
/*!40000 ALTER TABLE `permission_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'GET-countries',NULL,NULL,'2018-06-18 05:34:20','2018-06-18 05:34:20'),(2,'GET-states',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(3,'GET-cities',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(4,'GET-allCities',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(5,'GET-areas',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(6,'GET-allAreas',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(7,'POST-country',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(8,'DELETE-country',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(9,'POST-state',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(10,'DELETE-state',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(11,'POST-city',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(12,'DELETE-city',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(13,'POST-area',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(14,'DELETE-area',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(15,'GET-autocomplete-area',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(16,'GET-search-areas',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(17,'GET-products',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(18,'GET-map-products',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(19,'GET-product',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(20,'POST-product',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(21,'POST-products',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(22,'DELETE-product',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(23,'GET-approved-owner-products',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(24,'POST-request-owner-product-addition',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(25,'GET-requested-hoardings',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(26,'GET-requested-hoardings-for-owner',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(27,'GET-formats',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(28,'POST-format',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(29,'DELETE-format',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(30,'GET-search-products',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(31,'POST-filterProducts',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(32,'GET-shortlistedProducts',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(33,'POST-shortlistProduct',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(34,'DELETE-shortlistedProduct',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(35,'GET-searchBySiteNo',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(36,'POST-share-shortlisted',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(37,'POST-login',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(38,'GET-logout',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(39,'POST-userByAdmin',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(40,'POST-user',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(41,'GET-verify-email',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(42,'GET-user-profile',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(43,'POST-request-reset-password',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(44,'POST-reset-password',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(45,'GET-activate-user',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(46,'GET-user-permissions',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(47,'POST-change-password',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(48,'GET-switch-activation-user',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(49,'GET-delete-user',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(50,'POST-update-profile-pic',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(51,'POST-complete-registration',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(52,'GET-system-roles',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(53,'GET-system-permissions',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(54,'GET-users',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(55,'GET-role-details',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(56,'GET-all-clients',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(57,'POST-role',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(58,'GET-user-details-with-roles',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(59,'POST-set-su-for-client',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(60,'POST-set-permissions-for-role',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(61,'POST-set-roles-for-user',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(62,'GET-agencies',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(63,'POST-agencyByAdmin',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(64,'GET-companies',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(65,'GET-client-types',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(66,'POST-client',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(67,'GET-check-pwd-generation',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(68,'POST-resend-owner-invite',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(69,'GET-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(70,'GET-user-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(71,'GET-active-user-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(72,'POST-product-to-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(73,'POST-suggestion-request',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(74,'GET-export-all-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(75,'GET-request-proposal',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(76,'GET-request-campaign-launch',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(77,'POST-request-quote-change',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(78,'GET-get-all-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(79,'POST-propose-product-for-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(80,'GET-all-campaign-requests',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(81,'GET-campaign-suggestion-request-details',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(82,'GET-launch-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(83,'GET-close-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(84,'POST-floating-campaign-pdf',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(85,'GET-campaign-payments',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(86,'POST-campaign-payment',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(87,'GET-owner-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(88,'GET-user-campaigns-for-owner',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(89,'GET-campaign-for-owner',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(90,'GET-user-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(91,'POST-user-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(92,'POST-non-user-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(93,'GET-quote-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(94,'PUT-proposed-product-for-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(95,'DELETE-user-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(96,'DELETE-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(97,'GET-non-user-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(98,'POST-share-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(99,'GET-search-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(100,'GET-all-notifications',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(101,'GET-all-admin-notifications',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(102,'GET-update-notification-read',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(103,'POST-subscription',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(104,'POST-request-callback',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(105,'POST-user-query',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(106,'PUT-update-customer-data',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(107,'GET-customer-query',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(108,'POST-invite-bbi-user',NULL,NULL,'2018-06-26 21:17:02','2018-06-26 21:17:02'),(109,'GET-all-owner-notifications',NULL,NULL,'2018-06-26 21:17:02','2018-06-26 21:17:02'),(110,'GET-owner-products-report',NULL,NULL,'2018-08-28 14:42:53','2018-08-28 14:42:53'),(111,'GET-owner-product-details',NULL,NULL,'2018-08-28 14:42:53','2018-08-28 14:42:53'),(112,'GET-search-owner-products',NULL,NULL,'2018-08-28 14:42:53','2018-08-28 14:42:53'),(113,'POST-company',NULL,NULL,'2018-08-28 14:42:53','2018-08-28 14:42:53'),(114,'GET-quote-change-request-history',NULL,NULL,'2018-08-28 14:42:53','2018-08-28 14:42:53'),(116,'GET-campaigns-with-payments-owner',NULL,NULL,'2018-08-28 14:42:53','2018-08-28 14:42:53'),(117,'GET-campaign-payment-details-owner',NULL,NULL,'2018-08-28 14:42:53','2018-08-28 14:42:53'),(118,'POST-update-campaign-payment-owner',NULL,NULL,'2018-08-28 14:42:53','2018-08-28 14:42:53'),(119,'GET-owner-feeds',NULL,NULL,'2018-08-28 14:42:53','2018-08-28 14:42:53'),(120,'DELETE-non-user-campaign',NULL,NULL,'2018-08-28 14:42:53','2018-08-28 14:42:53'),(121,'GET-search-cities',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(122,'POST-metro-corridor',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(123,'GET-metro-corridors',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(124,'POST-metro-package',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(125,'GET-metro-packages',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(126,'GET-close-metro-campaigns',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(127,'DELETE-metro-campaign-product',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(128,'DELETE-metro-campaign',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(129,'DELETE-metro-corridor',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(130,'DELETE-metro-package',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(131,'POST-shortlist-metro-package',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(132,'GET-shortlisted-metro-packages',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(133,'DELETE-shortlisted-metro-package',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(134,'POST-metro-campaign',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(135,'GET-metro-campaigns',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(136,'GET-checkout-metro-campaign',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(137,'GET-metro-campaign',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(138,'POST-update-metro-campaigns-status',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(139,'GET-launch-metro-campaign',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(140,'POST-package-to-metro-campaign',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53'),(141,'POST-share-metro-campaign',NULL,NULL,'2018-09-10 17:24:53','2018-09-10 17:24:53');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_user`
--

DROP TABLE IF EXISTS `role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_user` (
  `role_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`user_id`),
  KEY `role_user_user_id_foreign` (`user_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_user`
--

LOCK TABLES `role_user` WRITE;
/*!40000 ALTER TABLE `role_user` DISABLE KEYS */;
INSERT INTO `role_user` VALUES (1,1),(2,2),(2,3),(4,4),(2,5),(4,5),(4,6),(2,7),(4,8),(5,8),(5,9),(2,10),(2,11),(2,12),(2,15),(2,16),(2,17),(2,18),(3,19),(3,20),(2,21),(2,22),(2,23),(2,24),(2,25),(2,26),(3,26),(2,27),(2,28),(2,29),(2,30),(2,31),(2,32),(2,33),(3,33),(2,34),(2,35),(3,35),(2,36),(2,37),(3,38),(2,39),(2,40),(2,41),(3,42),(2,43),(2,44),(5,45),(2,46),(2,47),(2,48),(2,49),(2,50),(2,51),(2,52),(2,53),(2,54),(2,55),(2,56),(2,57),(2,58),(2,59),(2,60),(2,61),(2,62),(2,63),(2,64),(2,65),(2,66),(2,67),(2,68);
/*!40000 ALTER TABLE `role_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`),
  KEY `roles_client_id_foreign` (`client_id`),
  CONSTRAINT `roles_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,1,'super_admin',NULL,'The ulitmate user of application. Has every permission that can exist in the application','2018-06-18 05:34:20','2018-06-18 05:34:20'),(2,1,'basic_user','Basic User','The most basic user of application. Has the permissions only related to viewing products, locations, profile etc.','2018-06-18 05:34:20','2018-06-18 05:34:20'),(3,1,'owner','Ad Space Owner','Owner of ad spaces. May have inventory shared with Billboards India.','2018-06-18 05:34:20','2018-06-18 05:34:20'),(4,1,'product_data_entry_operator','Product Data Entry Operator','Role that allows the user to add new product into the system. Also allows update of these products.','2018-06-26 12:04:40','2018-06-26 12:04:40'),(5,1,'bbi_admin','BBI Admin','All the permissions a Billboards India admin can have. Except the user management permissions.','2018-06-29 14:32:30','2018-06-29 14:32:30'),(6,1,'bbi_marketing','BBI Marketing','All the permissions a Billboards India admin can give access to marketing persons.','2019-01-03 18:21:46','2019-01-03 18:21:46');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned DEFAULT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_client_id_foreign` (`client_id`),
  CONSTRAINT `users_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'bbadmin','mridulkashyap57@gmail.com','b5dcf6f79f5394873787c4827da02281','bb94213',1,'2018-06-18 05:34:20','2018-06-19 14:39:40'),(2,NULL,NULL,'mahmoud.saeed@outsiteooh.com','42566ad158c018a0389c24bd8f672582','oZ1cqWX',1,'2018-06-18 19:59:24','2018-10-24 13:08:57'),(3,NULL,NULL,'nancy.jain@switchme.in','7ce42019d774b4b894a3dfdb5ce48f8f','7oJsBNP',0,'2018-06-21 13:56:52','2018-06-21 13:56:52'),(4,1,NULL,'deo@grr.la','1119d156314fe4ca365c0d9395947414','udZ0lL5',0,'2018-06-26 21:22:10','2018-06-26 21:53:51'),(5,1,NULL,'sudesh@billboardsindia.com','','U6OZQaS',1,'2018-06-28 13:53:02','2018-06-28 13:55:46'),(6,1,NULL,'naga.227sudi@gmail.com','0824f4d657f44a9951e6f5b3c0d0344c','0a8zQno',1,'2018-06-28 16:07:18','2018-06-29 13:42:54'),(7,1,NULL,'testuser14@grr.la','','KBAPQrS',0,'2018-06-28 16:13:16','2018-06-28 16:13:16'),(8,1,NULL,'banu.patan@ptgindia.com','f96e28e8b5aaa731f2abaeffcb818f12','fTJiOQz',1,'2018-06-29 13:38:25','2018-06-29 13:49:38'),(9,1,NULL,'chand375@gmail.com','d3bcb6c649bb96f3ed888404eb759abe','8GOMEEf',1,'2018-06-29 14:38:41','2018-12-20 18:47:00'),(10,NULL,NULL,'chanikya@billboardsindia.com','b66f49b4f6de2f73c02cbeba2e20e082','6Ob9Xn4',1,'2018-07-03 17:20:16','2018-11-12 14:50:40'),(11,NULL,NULL,'poorvabangad@gmail.com','0ebf6aa5ce5481f1d4408c463d10fbb0','d6lIlGo',0,'2018-07-19 17:58:28','2018-07-19 17:58:28'),(12,NULL,NULL,'sivasankar.web@gmail.com','a9a05ee1bc644aeab48b4007e557f50c','rHMZBnK',0,'2018-07-24 12:10:25','2018-09-10 12:28:14'),(15,NULL,NULL,'chanikya@nvipani.com','7c8502c56d14be67ac81accf0206dd74','Qyquo9r',1,'2018-07-26 13:12:08','2018-07-26 13:13:04'),(16,28,NULL,'chanikyavarma7@gmail.com','03553623ba7a1eec3e6074101c35a9d1','THLAUOy',1,'2018-07-26 13:29:37','2018-07-26 13:33:30'),(17,NULL,NULL,'dileep@billboardsindia.com','0b4b1aabe9a4346a574b1fb7578eb484','f5TOazx',0,'2018-08-03 14:05:50','2018-08-03 14:05:50'),(18,NULL,NULL,'barath.ecr@gmail.com','8579c017e9305c92da803b43e54946b8','sJL9mow',0,'2018-08-22 21:17:52','2018-08-22 21:17:52'),(19,25,NULL,'brandposters201@gmail.com','','cVOK00Z',1,'2018-08-27 14:26:38','2018-08-27 14:27:29'),(20,31,NULL,'istaaads@gmail.com','00144d6f3a8762d67c702442f1f56928','WEBN41O',1,'2018-08-28 13:52:31','2018-08-28 13:57:43'),(21,NULL,NULL,'dotgains@gmail.com','1cfcdc2bb70c78c39917e88882c97b3c','xVUJw3K',0,'2018-08-29 13:04:05','2018-08-29 13:04:05'),(22,NULL,NULL,'chand@billboardsindia.com','7e41257db4d8173f7610f1bd37aa3251','HYSJjKD',0,'2018-08-29 17:19:16','2018-08-29 17:19:16'),(23,NULL,NULL,'vrvrkrishnan@yahoo.com','276e51e2033e3b8df3796b2fd8b98906','UPNd3lz',1,'2018-09-10 16:30:06','2018-09-11 13:34:09'),(24,NULL,NULL,'kishore@laundrywaves.com','d2bed338ba7098a19c02546b8bfd8f58','fn0e1y0',0,'2018-09-12 18:46:26','2018-09-12 18:46:26'),(25,NULL,NULL,'mahe3030@gmail.com','c23dc3409feb3d121444b9d108095c60','7Fn6RDM',0,'2018-09-14 13:11:49','2018-09-14 13:11:49'),(26,NULL,NULL,'madhurigandla16@gmail.com','336e46c40ab5b9d6ab6046c6f6608f9f','MOsZk4Y',1,'2018-09-20 14:07:00','2018-09-20 14:09:40'),(27,NULL,NULL,'cavatsaldalmia@gmail.com','2ef3cf846d4a6fdbac0cb575ad39b83d','ZDtqTi3',0,'2018-09-21 16:47:01','2018-09-21 16:47:01'),(28,NULL,NULL,'rush2girish@gmail.com','354bc4b0d6ea8ff3ffe22263bf873c54','191TRgL',0,'2018-09-26 20:14:53','2018-09-26 20:14:53'),(29,NULL,NULL,'rajasekhar.tenneti@gmail.com','0b39e7aa40ae8d5b3b8f39771f129817','c3jLrvU',0,'2018-10-24 13:42:35','2018-10-24 13:42:35'),(30,NULL,NULL,'jakku.com@gmail.com','82ab92763e2dc65420dc8e37d996cd12','S2qJx64',1,'2018-10-27 14:14:26','2018-11-16 13:14:19'),(31,NULL,NULL,'pavani.sree09@gmail.com','1744af32f6903e3d3a3dd3e5b500e6df','OEhkhXq',0,'2018-10-29 12:34:06','2018-10-29 12:34:06'),(32,NULL,NULL,'sunny.dodeja305@gmail.com','ce308a208602e678e184edd8eadfa5b3','RwMLjc9',0,'2018-11-01 19:17:19','2018-11-01 19:17:19'),(33,NULL,NULL,'bharath.reddy1424@gmail.com','ecb2427e2f88308747776cc66d48306b','foctzaJ',1,'2018-11-12 11:49:25','2018-11-12 11:55:31'),(34,NULL,NULL,'sankaricore@gmail.com','e3f9da5f13ed58e11869d756cb117a3c','0BcroLd',1,'2018-11-16 17:22:12','2018-11-16 17:22:52'),(35,32,NULL,'sivam.963182@gmail.com','364ac190b8cf29508acf1811436e47d8','tiD826n',1,'2018-11-16 18:50:29','2018-11-16 18:55:44'),(36,NULL,NULL,'isabellakasollari@hotmail.com','0fbda93e378059aab019dc8a62056daa','EuMAC8C',0,'2018-11-21 14:05:10','2018-11-21 14:05:10'),(37,NULL,NULL,'p.soni@sdventures.com','d3d5e2c782c6f7216824bf16d300b5c2','q00f3j9',0,'2018-11-28 22:13:24','2018-11-28 22:13:24'),(38,33,NULL,'skyleigh.charla@cowaway.com','f73758074bbcaa09a8e6812b69b1f7d3','8EJLl4G',1,'2018-12-17 19:14:09','2018-12-26 22:19:42'),(39,NULL,NULL,'saikiranreddy09@gmail.com','e482357b18dc764a827fa808eea16cb6','Qle9fn5',1,'2018-12-19 18:58:08','2018-12-19 19:00:13'),(40,NULL,NULL,'bookurl.in@gmail.com','dfcb6e4ea0b9e6dc85b1d4d08ddd488f','9EzqqrS',1,'2018-12-20 04:37:49','2018-12-20 16:58:08'),(41,NULL,NULL,'mridul2010@gmail.com','44b00055e60a894e20ad2afdfbf47dd7','2g5vD8T',0,'2018-12-24 03:04:53','2018-12-24 03:04:53'),(42,34,NULL,'info@cromptonelevators.com','6bb7b82afb389d5934e3034bc57322d5','MA2I07i',1,'2018-12-25 14:51:41','2018-12-26 22:19:14'),(43,NULL,NULL,'sudhir.dharani@ptgindia.com','3bf02111a7ab935bcf4adcbecef19e63','c1NkRBR',1,'2018-12-26 21:57:54','2018-12-26 22:17:37'),(44,NULL,NULL,'miscalgames@gmail.com','9eab43cc204e49e77d05a4b78cf961fe','fkvAQ51',0,'2018-12-28 02:07:35','2018-12-28 02:07:35'),(45,1,NULL,'bhavan@billboardsindia.com','3362f90bb871f5ebd3e05e13d8325170','UUjE6h4',1,'2018-12-28 04:50:13','2019-01-04 19:43:09'),(46,NULL,NULL,'gm-softnet@telangana.gov.in','ba351671a3b0e84a348b8f3716e17db1','06NFAq5',0,'2018-12-29 01:16:25','2018-12-29 01:16:25'),(47,NULL,NULL,'qtramprasad@gmail.com','507d12605ae5760961963040a2a25854','Lo8HQcR',0,'2018-12-29 17:58:25','2018-12-29 17:58:25'),(48,NULL,NULL,'Karthikchengalpattu@gmail.com','5b5f74250c224a87b3f0bdf56a47303d','DU1735z',0,'2018-12-31 00:10:55','2018-12-31 00:10:55'),(49,NULL,NULL,'vinayasenareddy.k@gmail.com','f1957120e845fb867f4ca4e1a05a7f1e','PcwBz2Z',0,'2019-01-01 19:43:14','2019-01-01 19:43:14'),(50,NULL,NULL,'vijjikl@gmail.com','48779ec31fc2c7e031e3504a7f3a028b','Dsi3rKl',0,'2019-01-02 18:00:20','2019-01-02 18:00:20'),(51,NULL,NULL,'prasadababunadimpalli@gmail.com','e85a2e241d6568012294b3e821a82fb2','UDFlMlT',0,'2019-01-02 20:29:30','2019-01-02 20:29:30'),(52,NULL,NULL,'saiteja12bee1005@gmail.com','c6ff99353a2f7550af0203e4367e38e4','1YsxcM6',0,'2019-01-02 21:32:56','2019-01-02 21:32:56'),(53,NULL,NULL,'vamsikrishna010101@gmail.com','e076464926453f15c8fe8b66cc856272','30xeR0b',0,'2019-01-04 23:44:54','2019-01-04 23:44:54'),(54,NULL,NULL,'akhilponnada@gmail.com','700d960345cfd4f79e24b4ad026482d2','a7o8uWE',0,'2019-01-05 20:42:14','2019-01-05 20:42:14'),(55,NULL,NULL,'repalarohith@gmail.com','eda93bd317728d62837103cf46fabafa','23AmiUC',0,'2019-01-09 22:48:11','2019-01-09 22:48:11'),(56,35,NULL,'vijju028@gmail.com','f38194b9581c1b025f1f70a12a849f48','lpGykEG',0,'2019-01-15 15:34:47','2019-01-15 15:35:27'),(57,36,NULL,'business@myhoardings.com','2c7a3e4a33de5a214aae1c072b6fb79d','wCCxJ1D',0,'2019-01-15 23:11:13','2019-01-15 23:14:11'),(58,37,NULL,'jeremiah.nyman@rampgroup.com','f0302d4b4266b378077a7ce06eb9b1c7','MMOPbnq',0,'2019-01-16 08:34:24','2019-01-16 08:34:48'),(59,NULL,NULL,'modyauto01@gmail.com','ab52d7ddbf0127806d0762abdcfb576a','3msMeFd',0,'2019-01-16 19:12:58','2019-01-16 19:12:58'),(60,NULL,NULL,'mokkasiddhartha@gmail.com','61d218cd77dac87b3f955e7f8a7786d5','lV56SU0',0,'2019-01-18 02:26:36','2019-01-18 02:26:36'),(61,NULL,NULL,'printon6@gmail.com','bb448a800521c1db342d34cf0162d0c8','ajExjkS',0,'2019-01-19 20:26:49','2019-01-19 20:26:49'),(62,NULL,NULL,'krupagaju4@gmail.com','242846d0919894a1230e8ac00e6e99a6','T4dxxLh',0,'2019-01-22 00:46:23','2019-01-22 00:46:23'),(63,NULL,NULL,'mca.husain@gmail.com','d6909d7c8af1b87ea38b7a2ec500fb90','4xuXu0d',0,'2019-01-23 04:44:36','2019-01-23 04:44:36'),(64,NULL,NULL,'naninagarthi@amoghnya.com','0300aa5379777e10dd1a58165c9dc7a5','6zk7jOt',0,'2019-01-23 15:16:54','2019-01-23 15:16:54'),(65,NULL,NULL,'harimitrama@gmail.com','80fdc22a99fdd1372ee63b65ceb04f68','cmKqdUN',0,'2019-01-23 21:30:32','2019-01-23 21:30:32'),(66,NULL,NULL,'sushanthjonas2005@gmail.com','0ca02e26bc7570e49385327a18777267','rO6iKri',0,'2019-01-24 03:08:23','2019-01-24 03:08:23'),(67,NULL,NULL,'jayachandra.mothukuru@gmail.com','e63ab8596b6c38e0a658a24c9190097a','XNZQTPP',0,'2019-01-24 15:42:12','2019-01-24 15:42:12'),(68,NULL,NULL,'Pcc.karthik@gmail.com','79c968d41c17e41896023473c8bd9cab','ceo7iLj',0,'2019-01-24 20:02:53','2019-01-24 20:02:53');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-01-24 23:46:11
