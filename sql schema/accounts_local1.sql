-- MySQL dump 10.13  Distrib 5.7.22, for Linux (x86_64)
--
-- Host: localhost    Database: accounts
-- ------------------------------------------------------
-- Server version	5.7.22-0ubuntu0.16.04.1

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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,'BBI',1,1,'bbi',1,'2018-06-18 05:34:18','2018-06-18 05:34:20'),(2,'Prakash Arts',2,NULL,'prakash-arts',1,'2018-06-18 05:59:38','2018-06-18 05:59:38'),(3,'Ad Age Outdoor (P) Ltd',2,NULL,'ad-age-outdoor-(p)-ltd',1,'2018-06-18 06:01:44','2018-06-18 06:01:44'),(4,'Sudhakar Ad\'s Pvt Ltd',2,NULL,'sudhakar-ad\'s-pvt-ltd',1,'2018-06-18 06:02:55','2018-06-18 06:02:55'),(5,'MR.Publicities',2,NULL,'mr.publicities',1,'2018-06-18 06:03:53','2018-06-18 06:03:53'),(6,'Ad-space',2,NULL,'ad-space',1,'2018-06-18 06:05:00','2018-06-18 06:05:00'),(7,'RAINBOW ADVERTISING',2,NULL,'rainbow-advertising',1,'2018-06-18 06:06:24','2018-06-18 06:06:24'),(8,'Bhaskar Arts',2,NULL,'bhaskar-arts',1,'2018-06-18 06:07:51','2018-06-18 06:07:51'),(9,'Impressions Outdoor Advertising',2,NULL,'impressions-outdoor-advertising',1,'2018-06-18 06:09:26','2018-06-18 06:09:26'),(10,'Vision Outdoors',2,NULL,'vision-outdoors',1,'2018-06-18 06:10:16','2018-06-18 06:10:16'),(11,'SreeVarma',2,NULL,'sreevarma',1,'2018-06-18 06:11:08','2018-06-18 06:11:08'),(12,'Frontline Advertisers',2,NULL,'frontline-advertisers',1,'2018-06-18 06:12:12','2018-06-18 06:12:12'),(13,'SRIVEN ADS',2,NULL,'sriven-ads',1,'2018-06-18 06:13:12','2018-06-18 06:13:12'),(14,'LEADSPACE',2,NULL,'leadspace',1,'2018-06-18 06:13:59','2018-06-18 06:13:59'),(15,'Colors Outdoor advertising',2,NULL,'colors-outdoor-advertising',1,'2018-06-18 06:15:15','2018-06-18 06:15:15'),(16,'Roshan Adz',2,NULL,'roshan-adz',1,'2018-06-18 06:15:56','2018-06-18 06:15:56'),(17,'Fore-sites',2,NULL,'fore-sites',1,'2018-06-18 06:16:38','2018-06-18 06:16:38'),(18,'In and Out Advertising',2,NULL,'in-and-out-advertising',1,'2018-06-18 06:17:20','2018-06-18 06:17:20'),(19,'OUTSPACE ADVERTISING',2,NULL,'outspace-advertising',1,'2018-06-18 06:18:05','2018-06-18 06:18:05'),(20,'Narayana Cine Arts',2,NULL,'narayana-cine-arts',1,'2018-06-18 06:18:58','2018-06-18 06:18:58'),(21,'OUTDOOR MEDIA SOLUTIONS',2,NULL,'outdoor-media-solutions',1,'2018-06-18 06:19:48','2018-06-18 06:19:48'),(22,'Adlike Outdoor media',2,NULL,'adlike-outdoor-media',1,'2018-06-18 06:20:27','2018-06-18 06:20:27'),(23,'Brand vision Advertising',2,NULL,'brand-vision-advertising',1,'2018-06-18 06:21:05','2018-06-18 06:21:05');
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
INSERT INTO `permission_role` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,18),(1,19),(1,20),(1,21),(1,22),(1,23),(1,24),(1,25),(1,26),(1,27),(1,28),(1,29),(1,30),(1,31),(1,32),(1,33),(1,34),(1,35),(1,36),(1,37),(1,38),(1,39),(1,40),(1,41),(1,42),(1,43),(1,44),(1,45),(1,46),(1,47),(1,48),(1,49),(1,50),(1,51),(1,52),(1,53),(1,54),(1,55),(1,56),(1,57),(1,58),(1,59),(1,60),(1,61),(1,62),(1,63),(1,64),(1,65),(1,66),(1,67),(1,68),(1,69),(1,70),(1,71),(1,72),(1,73),(1,74),(1,75),(1,76),(1,77),(1,78),(1,79),(1,80),(1,81),(1,82),(1,83),(1,84),(1,85),(1,86),(1,87),(1,88),(1,89),(1,90),(1,91),(1,92),(1,93),(1,94),(1,95),(1,96),(1,97),(1,98),(1,99),(1,100),(1,101),(1,102),(1,103),(1,104),(1,105),(1,106),(1,107),(2,1),(2,2),(2,3),(2,4),(2,5),(2,6),(2,15),(2,17),(2,18),(2,27),(2,32),(2,33),(2,34),(2,35),(2,36),(2,38),(2,41),(2,42),(2,43),(2,44),(2,70),(2,71),(2,72),(2,73),(2,74),(2,75),(2,76),(2,77),(2,78),(2,90),(2,91),(2,95),(2,98),(2,100),(2,102);
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
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'GET-countries',NULL,NULL,'2018-06-18 05:34:20','2018-06-18 05:34:20'),(2,'GET-states',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(3,'GET-cities',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(4,'GET-allCities',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(5,'GET-areas',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(6,'GET-allAreas',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(7,'POST-country',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(8,'DELETE-country',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(9,'POST-state',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(10,'DELETE-state',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(11,'POST-city',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(12,'DELETE-city',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(13,'POST-area',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(14,'DELETE-area',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(15,'GET-autocomplete-area',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(16,'GET-search-areas',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(17,'GET-products',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(18,'GET-map-products',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(19,'GET-product',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(20,'POST-product',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(21,'POST-products',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(22,'DELETE-product',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(23,'GET-approved-owner-products',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(24,'POST-request-owner-product-addition',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(25,'GET-requested-hoardings',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(26,'GET-requested-hoardings-for-owner',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(27,'GET-formats',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(28,'POST-format',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(29,'DELETE-format',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(30,'GET-search-products',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(31,'POST-filterProducts',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(32,'GET-shortlistedProducts',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(33,'POST-shortlistProduct',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(34,'DELETE-shortlistedProduct',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(35,'GET-searchBySiteNo',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(36,'POST-share-shortlisted',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(37,'POST-login',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(38,'GET-logout',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(39,'POST-userByAdmin',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(40,'POST-user',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(41,'GET-verify-email',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(42,'GET-user-profile',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(43,'POST-request-reset-password',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(44,'POST-reset-password',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(45,'GET-activate-user',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(46,'GET-user-permissions',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(47,'POST-change-password',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(48,'GET-switch-activation-user',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(49,'GET-delete-user',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(50,'POST-update-profile-pic',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(51,'POST-complete-registration',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(52,'GET-system-roles',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(53,'GET-system-permissions',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(54,'GET-users',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(55,'GET-role-details',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(56,'GET-all-clients',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(57,'POST-role',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(58,'GET-user-details-with-roles',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(59,'POST-set-su-for-client',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(60,'POST-set-permissions-for-role',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(61,'POST-set-roles-for-user',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(62,'GET-agencies',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(63,'POST-agencyByAdmin',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(64,'GET-companies',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(65,'GET-client-types',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(66,'POST-client',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(67,'GET-check-pwd-generation',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(68,'POST-resend-owner-invite',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(69,'GET-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(70,'GET-user-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(71,'GET-active-user-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(72,'POST-product-to-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(73,'POST-suggestion-request',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(74,'GET-export-all-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(75,'GET-request-proposal',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(76,'GET-request-campaign-launch',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(77,'POST-request-quote-change',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(78,'GET-get-all-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(79,'POST-propose-product-for-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(80,'GET-all-campaign-requests',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(81,'GET-campaign-suggestion-request-details',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(82,'GET-launch-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(83,'GET-close-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(84,'POST-floating-campaign-pdf',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(85,'GET-campaign-payments',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(86,'POST-campaign-payment',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(87,'GET-owner-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(88,'GET-user-campaigns-for-owner',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(89,'GET-campaign-for-owner',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(90,'GET-user-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(91,'POST-user-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(92,'POST-non-user-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(93,'GET-quote-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(94,'PUT-proposed-product-for-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(95,'DELETE-user-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(96,'DELETE-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(97,'GET-non-user-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(98,'POST-share-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(99,'GET-search-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(100,'GET-all-notifications',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(101,'GET-all-admin-notifications',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(102,'GET-update-notification-read',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(103,'POST-subscription',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(104,'POST-request-callback',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(105,'POST-user-query',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(106,'PUT-update-customer-data',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(107,'GET-customer-query',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21');
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
INSERT INTO `role_user` VALUES (1,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,1,'super_admin',NULL,'The ulitmate user of application. Has every permission that can exist in the application','2018-06-18 05:34:20','2018-06-18 05:34:20'),(2,1,'basic_user','Basic User','The most basic user of application. Has the permissions only related to viewing products, locations, profile etc.','2018-06-18 05:34:20','2018-06-18 05:34:20'),(3,1,'owner','Ad Space Owner','Owner of ad spaces. May have inventory shared with Billboards India.','2018-06-18 05:34:20','2018-06-18 05:34:20');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'bbadmin','mridulkashyap57@gmail.com','921aab40aa6f5cb672786a4767672cb1','bb94213',1,'2018-06-18 05:34:20','2018-06-18 05:34:20');
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

-- Dump completed on 2018-06-18  7:16:36
