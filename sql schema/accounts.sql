-- MySQL dump 10.13  Distrib 5.7.30, for Linux (x86_64)
--
-- Host: localhost    Database: accounts
-- ------------------------------------------------------
-- Server version	5.7.30-0ubuntu0.16.04.1

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
-- Current Database: `accounts`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `accounts` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `accounts`;

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_types`
--

LOCK TABLES `client_types` WRITE;
/*!40000 ALTER TABLE `client_types` DISABLE KEYS */;
INSERT INTO `client_types` VALUES (1,'bbi','2018-09-12 13:28:21','2018-09-12 13:28:21'),(2,'owner','2018-09-12 13:28:21','2018-09-12 13:28:21'),(3,'agency','2018-09-12 13:28:21','2018-09-12 13:28:21'),(4,'sub-seller','2020-05-07 12:14:57','2020-05-07 12:14:57');
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
  CONSTRAINT `clients_type_foreign` FOREIGN KEY (`type`) REFERENCES `client_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,'BBI',1,1,'bbi',1,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(2,'TMC Media',2,14,'tmc-media',1,'2018-09-14 11:01:21','2019-11-28 09:17:16'),(3,'Landmark OOH',2,4,'landmark-ooh',1,'2018-09-14 11:01:53','2018-10-02 10:32:52'),(4,'Test Pvt',2,5,'test-pvt',1,'2018-10-23 07:21:44','2018-10-23 07:24:19'),(5,'Demo TMC',2,13,'demo-tmc',1,'2018-10-23 08:17:23','2019-11-28 09:20:12'),(6,'TMC Demo',2,3,'tmc-demo',1,'2018-10-23 08:25:22','2018-10-23 08:25:59'),(7,'Demo - Landmark OOH',2,6,'demo---landmark-ooh',1,'2018-10-23 08:37:12','2018-10-23 08:37:46'),(8,'Landmark',2,8,'landmark',1,'2018-12-17 06:36:32','2018-12-17 06:36:32'),(9,'Landmark LLC - 1',2,9,'landmark-llc---1',1,'2018-12-17 06:42:41','2018-12-17 06:42:41'),(10,'TMC Holdings',2,11,'tmc-holdings',1,'2018-12-19 02:48:32','2018-12-19 02:48:32'),(11,'Billboards Inc',2,12,'billboards-inc',1,'2018-12-24 06:22:11','2018-12-24 06:22:11'),(12,'Shiva',2,30,'shiva5e74d546c254b',1,'2020-03-20 14:37:58','2020-03-20 14:37:58'),(13,'Sand',2,33,'sand5e95569d496e1',1,'2020-04-14 06:22:21','2020-04-14 06:22:21'),(14,'Anu',2,34,'anu5e985610e42e3',1,'2020-04-16 12:56:48','2020-04-16 12:56:49'),(15,'Rama',2,35,'rama5e98579df3bed',1,'2020-04-16 13:03:26','2020-04-16 13:03:26'),(16,'Sand',2,38,'sand5eb27be41dfb6',1,'2020-05-06 08:57:08','2020-05-06 08:57:08'),(17,'Sand',2,39,'sand5eb2857a2bf3f',1,'2020-05-06 09:38:02','2020-05-06 09:38:02'),(18,'Anu',2,40,'anu5eb2865beaaa0',1,'2020-05-06 09:41:47','2020-05-06 09:41:48'),(19,'Vasudev Baggi',2,41,'vasudev-baggi5eb29a17768a5',1,'2020-05-06 11:05:59','2020-05-06 11:05:59'),(20,'Vasudev',2,42,'vasudev5eb29be902e77',1,'2020-05-06 11:13:45','2020-05-06 11:13:45'),(21,'dev',2,43,'dev5eb29c693ddb4',1,'2020-05-06 11:15:53','2020-05-06 11:15:53'),(22,'Vasudev Baggi',2,44,'vasudev-baggi5eb29d71cb6b7',1,'2020-05-06 11:20:17','2020-05-06 11:20:17'),(23,'Rama',2,45,'rama5eb29da3c3152',1,'2020-05-06 11:21:07','2020-05-06 11:21:07'),(24,'Siri',2,47,'siri5eb2cdab9c659',1,'2020-05-06 14:46:03','2020-05-06 14:46:03'),(25,'Test Name',2,NULL,'test-name',1,'2020-05-12 05:35:45','2020-05-12 05:35:45'),(28,'Test Name1',2,NULL,'test-name1',1,'2020-05-12 05:36:30','2020-05-12 05:36:30'),(29,'Sandhya',2,NULL,'sandhya',1,'2020-05-21 09:48:06','2020-05-21 09:48:06'),(31,'Seller',2,49,'seller5ec655d0c85b3',1,'2020-05-21 10:20:00','2020-05-21 10:20:00'),(32,'AmpSeller',2,50,'ampseller5ec676ad2f544',1,'2020-05-21 12:40:13','2020-05-21 12:40:13'),(33,'SellerAmo',2,51,'selleramo5ec67be40f2ac',1,'2020-05-21 13:02:28','2020-05-21 13:02:28'),(34,'Vasudev Baggi',2,NULL,'vasudev-baggi',1,'2020-06-22 14:31:36','2020-06-22 14:31:36'),(35,'Sand',4,NULL,'sand5ef1b1b4d392b',1,'2020-06-23 07:39:32','2020-06-23 07:39:32'),(36,'Sand',4,NULL,'sand5ef1b1e642fd3',1,'2020-06-23 07:40:22','2020-06-23 07:40:22'),(37,'Sand',4,58,'sand5ef1b4dab0d9c',1,'2020-06-23 07:52:58','2020-06-23 07:52:58'),(38,'Sand',4,59,'sand5ef1b9aad6d9f',1,'2020-06-23 08:13:30','2020-06-23 08:13:30'),(39,'Sand',4,NULL,'sand5ef31651cc5f8',1,'2020-06-24 09:01:05','2020-06-24 09:01:05'),(40,'Sand',4,60,'sand5ef319f210fcd',1,'2020-06-24 09:16:34','2020-06-24 09:16:34'),(41,'Sand',4,61,'sand5ef32641bf017',1,'2020-06-24 10:09:05','2020-06-24 10:09:05'),(42,'Sand',4,62,'sand5ef326aecb0d5',1,'2020-06-24 10:10:54','2020-06-24 10:10:54'),(43,'Siri',4,63,'siri5ef32b7060380',1,'2020-06-24 10:31:12','2020-06-24 10:31:12'),(44,'Siri',4,64,'siri5ef32bd6ba2e3',1,'2020-06-24 10:32:54','2020-06-24 10:32:54'),(45,'Siri',4,65,'siri5ef32e7331f95',1,'2020-06-24 10:44:03','2020-06-24 10:44:03'),(46,'Siri',4,66,'siri5ef3351d6b672',1,'2020-06-24 11:12:29','2020-06-24 11:12:29'),(47,'Siri',4,67,'siri5ef34b30e890e',1,'2020-06-24 12:46:40','2020-06-24 12:46:40'),(48,'Siri',4,68,'siri5ef34bd4b3a59',1,'2020-06-24 12:49:24','2020-06-24 12:49:24'),(49,'Siri',4,69,'siri5ef34db19a34b',1,'2020-06-24 12:57:21','2020-06-24 12:57:21'),(50,'Anu',4,70,'anu5ef34e10ebd42',1,'2020-06-24 12:58:56','2020-06-24 12:58:57'),(51,'Anu',4,71,'anu5ef34e65dbcc2',1,'2020-06-24 13:00:21','2020-06-24 13:00:21'),(52,'Anu',4,72,'anu5ef34fb9b5a0d',1,'2020-06-24 13:06:01','2020-06-24 13:06:01'),(53,'Siri',4,73,'siri5ef35061ba1fe',1,'2020-06-24 13:08:49','2020-06-24 13:08:53'),(54,'Siri',4,74,'siri5ef350dfcb6de',1,'2020-06-24 13:10:55','2020-06-24 13:10:57'),(55,'Anu',4,75,'anu5ef35524b9b2b',1,'2020-06-24 13:29:08','2020-06-24 13:29:08'),(56,'Sravani',4,76,'sravani5ef42dc943c84',1,'2020-06-25 04:53:29','2020-06-25 04:53:29'),(57,'Sravani',4,NULL,'sravani5ef43a001cdcc',1,'2020-06-25 05:45:36','2020-06-25 05:45:36'),(58,'Sravani',4,NULL,'sravani5ef43a5dddbda',1,'2020-06-25 05:47:09','2020-06-25 05:47:09'),(59,'Sravani',4,79,'sravani5ef440bd92c97',1,'2020-06-25 06:14:21','2020-06-25 06:14:21'),(60,'Sravani',4,80,'sravani5ef442004bac8',1,'2020-06-25 06:19:44','2020-06-25 06:19:44'),(61,'Sravani',4,NULL,'sravani5ef444a12b59c',1,'2020-06-25 06:30:57','2020-06-25 06:30:57'),(62,'Sravani',4,82,'sravani5ef4452bef7a8',1,'2020-06-25 06:33:15','2020-06-25 06:33:16'),(63,'Sravani',4,83,'sravani5ef44c64a7ed6',1,'2020-06-25 07:04:04','2020-06-25 07:04:04'),(64,'Sravani',4,84,'sravani5ef44c964eef1',1,'2020-06-25 07:04:54','2020-06-25 07:04:54'),(65,'Sravani',2,NULL,'sravani',1,'2020-06-25 07:55:34','2020-06-25 07:55:34'),(69,'SravaniY',2,NULL,'sravaniy',1,'2020-06-25 08:00:28','2020-06-25 08:00:28'),(70,'SravaniYelesam',2,NULL,'sravaniyelesam',1,'2020-06-25 08:00:53','2020-06-25 08:00:53');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2017_09_05_083648_entrust_setup_tables',1),(3,'2018_03_19_120515_create_jobs_table',2),(4,'2018_03_19_120515_create_subsellusers_table',3);
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
  KEY `permission_role_role_id_foreign` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_role`
--

LOCK TABLES `permission_role` WRITE;
/*!40000 ALTER TABLE `permission_role` DISABLE KEYS */;
INSERT INTO `permission_role` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,19),(1,20),(1,21),(1,22),(1,23),(1,25),(1,26),(1,27),(1,28),(1,29),(1,30),(1,31),(1,32),(1,33),(1,34),(1,35),(1,38),(1,39),(1,40),(1,41),(1,42),(1,43),(1,44),(1,45),(1,46),(1,47),(1,48),(1,49),(1,50),(1,51),(1,52),(1,53),(1,54),(1,55),(1,56),(1,57),(1,58),(1,59),(1,60),(1,61),(1,62),(1,63),(1,64),(1,65),(1,66),(1,67),(1,68),(1,69),(1,70),(1,71),(1,72),(1,73),(1,74),(1,75),(1,76),(1,77),(1,78),(1,79),(1,80),(1,81),(1,82),(1,83),(1,84),(1,86),(1,87),(1,88),(1,89),(1,90),(1,91),(1,92),(1,93),(1,94),(1,95),(1,97),(1,98),(1,99),(1,100),(1,101),(1,102),(1,103),(1,104),(1,105),(1,106),(1,107),(1,108),(1,109),(1,110),(1,111),(1,112),(1,113),(1,114),(1,115),(1,116),(1,117),(1,118),(1,119),(1,120),(1,121),(1,123),(1,126),(1,127),(1,128),(1,129),(1,130),(1,131),(1,132),(1,133),(1,134),(1,135),(1,136),(1,137),(1,138),(1,139),(1,140),(1,141),(1,142),(1,143),(1,144),(1,145),(1,146),(1,147),(1,148),(1,149),(1,150),(1,151),(1,152),(1,153),(1,154),(1,155),(1,156),(1,157),(1,158),(1,159),(1,160),(1,161),(1,162),(1,163),(1,164),(1,165),(1,166),(1,167),(1,168),(1,169),(1,170),(1,171),(1,172),(1,173),(1,174),(1,175),(1,176),(1,177),(1,178),(1,179),(1,180),(1,181),(1,182),(1,183),(1,184),(1,185),(1,186),(1,187),(1,188),(1,189),(1,190),(2,1),(2,2),(2,3),(2,4),(2,5),(2,6),(2,7),(2,8),(2,9),(2,11),(2,12),(2,13),(2,14),(2,15),(2,16),(2,17),(2,19),(2,20),(2,21),(2,22),(2,23),(2,25),(2,26),(2,27),(2,28),(2,29),(2,30),(2,31),(2,32),(2,33),(2,34),(2,35),(2,38),(2,39),(2,40),(2,41),(2,42),(2,43),(2,44),(2,45),(2,46),(2,47),(2,48),(2,49),(2,50),(2,52),(2,53),(2,54),(2,55),(2,56),(2,57),(2,58),(2,59),(2,60),(2,61),(2,62),(2,63),(2,64),(2,65),(2,66),(2,67),(2,68),(2,69),(2,70),(2,71),(2,72),(2,73),(2,74),(2,75),(2,76),(2,77),(2,78),(2,79),(2,80),(2,81),(2,82),(2,83),(2,84),(2,86),(2,87),(2,88),(2,89),(2,90),(2,91),(2,92),(2,93),(2,94),(2,95),(2,97),(2,98),(2,99),(2,100),(2,101),(2,102),(2,103),(2,104),(2,105),(2,106),(2,107),(2,108),(2,109),(2,110),(2,111),(2,112),(2,113),(2,114),(2,115),(2,116),(2,117),(2,118),(2,119),(2,120),(2,121),(2,123),(2,126),(2,127),(2,128),(2,129),(2,130),(2,131),(2,132),(2,133),(2,134),(2,135),(2,136),(2,137),(2,138),(2,139),(2,140),(2,141),(2,142),(2,143),(2,144),(2,145),(2,146),(2,147),(2,148),(2,149),(2,150),(2,151),(2,152),(2,153),(2,154),(2,155),(2,156),(2,157),(2,158),(2,159),(2,160),(2,162),(2,163),(2,164),(2,165),(2,166),(2,167),(2,168),(2,169),(2,171),(2,173),(2,174),(2,175),(2,176),(2,177),(2,178),(2,182),(2,183),(2,184),(2,185),(2,187),(2,188),(2,190),(3,1),(3,2),(3,3),(3,4),(3,5),(3,6),(3,7),(3,8),(3,9),(3,10),(3,11),(3,12),(3,13),(3,14),(3,15),(3,16),(3,17),(3,19),(3,20),(3,21),(3,22),(3,23),(3,25),(3,26),(3,27),(3,28),(3,29),(3,30),(3,31),(3,32),(3,33),(3,34),(3,35),(3,38),(3,39),(3,40),(3,41),(3,42),(3,43),(3,44),(3,45),(3,46),(3,47),(3,48),(3,49),(3,50),(3,51),(3,52),(3,53),(3,54),(3,55),(3,56),(3,57),(3,58),(3,59),(3,60),(3,61),(3,62),(3,63),(3,64),(3,65),(3,66),(3,67),(3,68),(3,69),(3,70),(3,71),(3,72),(3,73),(3,74),(3,75),(3,76),(3,77),(3,78),(3,79),(3,80),(3,81),(3,82),(3,83),(3,84),(3,86),(3,87),(3,88),(3,89),(3,90),(3,91),(3,92),(3,93),(3,94),(3,95),(3,97),(3,98),(3,99),(3,100),(3,101),(3,102),(3,103),(3,104),(3,105),(3,106),(3,107),(3,108),(3,109),(3,110),(3,111),(3,112),(3,113),(3,114),(3,115),(3,116),(3,117),(3,118),(3,119),(3,120),(3,121),(3,123),(3,126),(3,127),(3,128),(3,129),(3,130),(3,131),(3,132),(3,133),(3,134),(3,135),(3,136),(3,137),(3,138),(3,139),(3,140),(3,141),(3,142),(3,143),(3,144),(3,145),(3,146),(3,147),(3,148),(3,149),(3,150),(3,151),(3,152),(3,153),(3,154),(3,155),(3,156),(3,157),(3,158),(3,159),(3,160),(3,161),(3,162),(3,163),(3,164),(3,165),(3,166),(3,167),(3,168),(3,169),(3,171),(3,173),(3,174),(3,175),(3,176),(3,177),(3,178),(3,182),(3,183),(3,184),(3,185),(3,187),(3,188),(3,190);
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
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'GET-countries',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(2,'GET-states',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(3,'GET-cities',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(4,'GET-allCities',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(5,'GET-areas',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(6,'GET-allAreas',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(7,'POST-country',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(8,'DELETE-country',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(9,'POST-state',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(10,'DELETE-state',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(11,'POST-city',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(12,'DELETE-city',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(13,'POST-area',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(14,'DELETE-area',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(15,'GET-autocomplete-area',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(16,'GET-search-areas',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(17,'GET-products',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(19,'GET-map-products',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(20,'GET-product',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(21,'POST-product',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(22,'POST-products',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(23,'DELETE-product',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(25,'POST-request-owner-product-addition',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(26,'GET-requested-hoardings',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(27,'GET-requested-hoardings-for-owner',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(28,'GET-owner-products-report',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(29,'GET-owner-product-details',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(30,'GET-formats',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(31,'POST-format',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(32,'DELETE-format',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(33,'GET-search-products',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(34,'GET-search-owner-products',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(35,'POST-filterProducts',NULL,NULL,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(38,'GET-shortlistedProducts',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(39,'POST-shortlistProduct',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(40,'DELETE-shortlistedProduct',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(41,'GET-searchBySiteNo',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(42,'POST-share-shortlisted',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(43,'POST-login',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(44,'GET-logout',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(45,'POST-userByAdmin',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(46,'POST-user',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(47,'GET-verify-email',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(48,'GET-user-profile',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(49,'POST-request-reset-password',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(50,'POST-reset-password',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(51,'GET-activate-user',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(52,'GET-user-permissions',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(53,'POST-change-password',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(54,'GET-switch-activation-user',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(55,'GET-delete-user',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(56,'POST-update-profile-pic',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(57,'POST-complete-registration',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(58,'GET-system-roles',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(59,'GET-system-permissions',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(60,'GET-users',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(61,'GET-role-details',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(62,'GET-all-clients',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(63,'POST-role',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(64,'GET-user-details-with-roles',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(65,'POST-set-su-for-client',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(66,'POST-set-permissions-for-role',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(67,'POST-set-roles-for-user',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(68,'POST-invite-bbi-user',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(69,'GET-agencies',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(70,'POST-agencyByAdmin',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(71,'GET-companies',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(72,'GET-client-types',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(73,'POST-client',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(74,'POST-company',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(75,'GET-check-pwd-generation',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(76,'POST-resend-owner-invite',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(77,'GET-campaigns',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(78,'GET-user-campaigns',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(79,'GET-active-user-campaigns',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(80,'POST-user-campaign',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(81,'POST-product-to-campaign',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(82,'POST-suggestion-request',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(83,'GET-export-all-campaigns',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(84,'GET-request-proposal',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(86,'POST-request-quote-change',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(87,'DELETE-user-campaign',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(88,'GET-get-all-campaigns',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(89,'GET-all-campaign-requests',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(90,'GET-campaign-suggestion-request-details',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(91,'GET-close-campaign',NULL,NULL,'2018-09-12 13:28:22','2018-09-12 13:28:22'),(92,'POST-floating-campaign-pdf',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(93,'GET-campaign-payments',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(94,'POST-campaign-payment',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(95,'GET-quote-change-request-history',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(97,'GET-owner-campaigns',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(98,'GET-user-campaigns-for-owner',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(99,'GET-campaign-for-owner',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(100,'GET-campaigns-with-payments-owner',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(101,'GET-campaign-payment-details-owner',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(102,'POST-update-campaign-payment-owner',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(103,'GET-owner-feeds',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(104,'GET-user-campaign',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(105,'POST-propose-product-for-campaign',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(106,'DELETE-campaign',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(107,'GET-quote-campaign',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(108,'GET-launch-campaign',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(109,'POST-non-user-campaign',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(110,'PUT-proposed-product-for-campaign',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(111,'DELETE-non-user-campaign',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(112,'GET-non-user-campaign',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(113,'POST-share-campaign',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(114,'GET-search-campaigns',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(115,'GET-all-notifications',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(116,'GET-all-admin-notifications',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(117,'GET-all-owner-notifications',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(118,'GET-update-notification-read',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(119,'POST-subscription',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(120,'POST-request-callback',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(121,'POST-user-query',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(123,'GET-customer-query',NULL,NULL,'2018-09-12 13:28:23','2018-09-12 13:28:23'),(126,'GET-search-cities',NULL,NULL,'2019-06-04 07:30:01','2019-06-04 07:30:01'),(127,'POST-approved-owner-products',NULL,NULL,'2019-06-04 07:30:01','2019-06-04 07:30:01'),(128,'POST-metro-corridor',NULL,NULL,'2019-06-04 07:30:01','2019-06-04 07:30:01'),(129,'GET-metro-corridors',NULL,NULL,'2019-06-04 07:30:01','2019-06-04 07:30:01'),(130,'POST-metro-package',NULL,NULL,'2019-06-04 07:30:01','2019-06-04 07:30:01'),(131,'POST-change-product-price',NULL,NULL,'2019-06-04 07:30:01','2019-06-04 07:30:01'),(132,'POST-change-campaign-product-price',NULL,NULL,'2019-06-04 07:30:01','2019-06-04 07:30:01'),(133,'PUT-product-visibility',NULL,NULL,'2019-06-04 07:30:01','2019-06-04 07:30:01'),(134,'GET-metro-packages',NULL,NULL,'2019-06-04 07:30:01','2019-06-04 07:30:01'),(135,'GET-close-metro-campaigns',NULL,NULL,'2019-06-04 07:30:01','2019-06-04 07:30:01'),(136,'DELETE-metro-campaign-product',NULL,NULL,'2019-06-04 07:30:01','2019-06-04 07:30:01'),(137,'DELETE-metro-campaign',NULL,NULL,'2019-06-04 07:30:01','2019-06-04 07:30:01'),(138,'DELETE-metro-corridor',NULL,NULL,'2019-06-04 07:30:01','2019-06-04 07:30:01'),(139,'DELETE-metro-package',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(140,'GET-product-unavailable-dates',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(141,'GET-campaigns-from-products',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(142,'POST-filterProductsByDate',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(143,'POST-shortlist-metro-package',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(144,'GET-shortlisted-metro-packages',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(145,'DELETE-shortlisted-metro-package',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(146,'GET-request-campaign-booking',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(147,'POST-metro-campaign',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(148,'GET-metro-campaigns',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(149,'GET-checkout-metro-campaign',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(150,'GET-metro-campaign',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(151,'POST-update-metro-campaigns-status',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(152,'GET-launch-metro-campaign',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(153,'POST-package-to-metro-campaign',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(154,'POST-post-campaign-comment',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(155,'POST-get-campaign-comment',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(156,'POST-share-metro-campaign',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(157,'GET-confirm-campaign-booking',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(158,'GET-book-non-user-campaign',NULL,NULL,'2019-06-04 07:30:02','2019-06-04 07:30:02'),(159,'PUT-update-customer-data',NULL,NULL,'2019-06-04 07:30:03','2019-06-04 07:30:03'),(160,'GET-get-notifications',NULL,NULL,'2019-06-04 07:30:03','2019-06-04 07:30:03'),(161,'GET-update-notification-status',NULL,NULL,'2019-06-04 07:30:03','2019-06-04 07:30:03'),(162,'GET-cancel-campaign-product',NULL,NULL,'2019-06-04 07:30:03','2019-06-04 07:30:03'),(163,'GET-download-quote',NULL,NULL,'2019-06-04 07:30:03','2019-06-04 07:30:03'),(164,'GET-download-metro-quote',NULL,NULL,'2019-06-04 07:30:03','2019-06-04 07:30:03'),(165,'GET-test-noti',NULL,NULL,'2019-06-04 07:30:03','2019-06-04 07:30:03'),(166,'POST-save-product-details',NULL,NULL,'2019-06-17 12:05:25','2019-06-17 12:05:25'),(167,'POST-pay-launch-campaign',NULL,NULL,'2019-06-17 12:05:25','2019-06-17 12:05:25'),(168,'POST-digital-product-unavailable-dates',NULL,NULL,'2019-06-17 12:05:25','2019-06-17 12:05:25'),(169,'POST-save-bulk-product-details',NULL,NULL,'2020-03-12 09:59:57','2020-03-12 09:59:57'),(170,'GET-generate-pop',NULL,NULL,'2020-03-12 09:59:57','2020-03-12 09:59:57'),(171,'GET-payments-info-download',NULL,NULL,'2020-03-12 09:59:57','2020-03-12 09:59:57'),(173,'GET-bulk-upload-products',NULL,NULL,'2020-03-16 14:23:39','2020-03-16 14:23:39'),(174,'POST-shareCampaigndownloadQuote',NULL,NULL,'2020-03-19 06:08:33','2020-03-19 06:08:33'),(175,'POST-save-subseller-details',NULL,NULL,'2020-03-31 10:31:24','2020-03-31 10:31:24'),(176,'GET-get-subseller-details',NULL,NULL,'2020-03-31 10:31:24','2020-03-31 10:31:24'),(177,'DELETE-delete-subseller',NULL,NULL,'2020-03-31 10:31:24','2020-03-31 10:31:24'),(178,'POST-subseller-generate-password',NULL,NULL,'2020-05-06 10:26:59','2020-05-06 10:26:59'),(182,'POST-stripePost',NULL,NULL,'2020-05-17 14:54:44','2020-05-17 14:54:44'),(183,'GET-searchByCpm',NULL,NULL,'2020-06-04 11:43:33','2020-06-04 11:43:33'),(184,'GET-searchBySecondImpression',NULL,NULL,'2020-06-04 11:43:33','2020-06-04 11:43:33'),(185,'POST-notifyUsershortlistedProduct',NULL,NULL,'2020-06-11 09:51:33','2020-06-11 09:51:33'),(187,'POST-deleteshortlistedProducts',NULL,NULL,'2020-06-19 11:15:23','2020-06-19 11:15:23'),(188,'POST-subseller',NULL,NULL,'2020-06-22 15:12:57','2020-06-22 15:12:57'),(190,'GET-map-products-filter-shortlist',NULL,NULL,'2020-06-25 08:41:04','2020-06-25 08:41:04');
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
  KEY `role_user_user_id_foreign` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_user`
--

LOCK TABLES `role_user` WRITE;
/*!40000 ALTER TABLE `role_user` DISABLE KEYS */;
INSERT INTO `role_user` VALUES (1,1),(2,2),(2,3),(3,3),(3,4),(2,5),(3,5),(3,6),(2,7),(3,8),(3,9),(2,10),(3,11),(2,12),(2,13),(2,14),(2,15),(2,16),(2,17),(2,18),(2,19),(2,20),(2,21),(2,22),(2,23),(2,24),(2,25),(3,25),(2,26),(3,26),(2,27),(3,27),(2,28),(3,28),(2,29),(3,30),(2,31),(2,32),(2,33),(2,34),(2,35),(2,36),(2,37),(2,38),(2,39),(2,40),(2,41),(2,42),(2,43),(2,44),(2,45),(2,46),(2,47),(2,48),(3,49),(3,50),(3,51),(2,52),(3,52),(2,53),(2,54),(3,54),(2,55),(3,55),(2,56),(2,58),(2,59),(2,60),(2,61),(2,62),(2,63),(2,64),(2,65),(2,66),(2,67),(2,68),(2,69),(2,70),(2,71),(2,72),(2,73),(2,74),(2,75),(2,76),(2,79),(2,80),(2,82),(2,83),(3,84);
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
INSERT INTO `roles` VALUES (1,1,'super_admin',NULL,'The ulitmate user of application. Has every permission that can exist in the application','2018-09-12 13:28:21','2018-09-12 13:28:21'),(2,1,'basic_user','Basic User','The most basic user of application. Has the permissions only related to viewing products, locations, profile etc.','2018-09-12 13:28:21','2018-09-12 13:28:21'),(3,1,'owner','Ad Space Owner','Owner of ad spaces. May have inventory shared with Billboards India.','2018-09-12 13:28:21','2018-09-12 13:28:21');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subsellusers`
--

DROP TABLE IF EXISTS `subsellusers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subsellusers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subseller_username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subseller_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subseller_password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `activated` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subsellusers`
--

LOCK TABLES `subsellusers` WRITE;
/*!40000 ALTER TABLE `subsellusers` DISABLE KEYS */;
INSERT INTO `subsellusers` VALUES (1,'Krizi','krizi2020@gmail.com','96d401c6f09d53972778b14c35353682','3mUesyI','0','2020-05-05 12:02:13','2020-05-05 12:02:13'),(2,'Krizi2','krizi20220@gmail.com','9a3b6d1c1257f7fdc9d245ce5dc5cc50','dDNHuMA','0','2020-05-05 12:11:16','2020-05-05 12:11:16'),(3,'Krizi','krizimanelli2020@gmail.com','7e5cfe37b6a81ee0899f3078b820770e','SqYAOuw','0','2020-05-05 15:36:23','2020-05-05 15:36:23'),(4,'Krizi','krizimanelli2020@gmail.com','5f4e2244be1e8382553b59b0ed2e0812','71b4JYn','0','2020-05-05 15:43:05','2020-05-05 15:43:05'),(5,'Krizi','krizimanelli2020@gmail.com','9c1f7e9d3432dd0217426304cfb3dbb9','pEpdd0U','0','2020-05-05 15:51:22','2020-05-05 15:51:22'),(6,'Krizi','krizimanelli2020@gmail.com','89d22deb419d0cc6e23fad8e8700a837','yobFxBJ','0','2020-05-05 15:52:41','2020-05-05 15:52:41'),(7,'Sravani','sravaniyelesam@gmail.com','ee3357eef1623143e1c8a7f6d323b6c8','VihiCZ5','0','2020-05-05 15:55:41','2020-05-05 15:55:41'),(8,'Sand','sandmanelli1717@gmail.com','ab2af12301655f979184a81e87932377','Yvtbb8P','0','2020-05-05 16:12:45','2020-05-05 16:12:45'),(9,'Richard S McClemmy','mcclemmy.richard@gmail.com','815a5d150d3287cdef9d0b0ed68cfee6','pk5PgmN','0','2020-05-05 22:23:54','2020-05-05 22:23:54'),(10,'Vasudev','vasudev.baggi@ptgindia.com','4f080c1678eabd90c0adacd26a8dbca1','ySl7VnW','0','2020-05-06 10:53:32','2020-05-06 10:53:32'),(11,'testing_new','vasubaggi2020@gmail.com','9634fab56d1c7417ff1cf73190b171fe','sNfv0Up','0','2020-05-06 11:40:12','2020-05-06 11:40:12'),(12,'Abc','abc@gmail.com','faa45672488ef50705577676c0c7cbef','ambcKMq','0','2020-05-06 12:18:14','2020-05-06 12:18:14'),(13,'Siri','sirimanelli2020@gmail.com','d6807f270e5b68e657714c30eeaff979','DyPoocA','0','2020-05-06 12:19:07','2020-05-06 12:19:07'),(14,'Siri','sirimanelli2020@gmail.com','340764049ba9631974faee2e5b1bdd14','qo3sN9k','0','2020-05-06 12:31:40','2020-05-06 12:31:40'),(15,'Siri','sirimanelli2020@gmail.com','','76giy6r','0','2020-05-06 12:39:31','2020-05-06 12:39:31'),(16,'Siri','sirimanelli2020@gmail.com','','kdUXpee','0','2020-05-06 13:29:03','2020-05-06 13:29:03'),(17,'Siri','sirimanelli2020@gmail.com','32878ae7feeb6e2cb4b4e96fafd643f9','sAjTFC7','0','2020-05-06 13:30:46','2020-05-06 13:30:46'),(18,'Siri','sirimanelli2020@gmail.com','29f9d250ce11c195ff7dd00477274053','eZ78Dlw','0','2020-05-06 13:33:03','2020-05-06 13:33:03'),(19,'Siri','sirimanelli2020@gmail.com','97a66837c8e8a2525e6019a3e865def4','a2Iv5dQ','0','2020-05-06 13:34:02','2020-05-06 13:34:02'),(20,'Sravani','sravani.yelesam@ptgindia.com','1289dfb7375b5af9d1d19989837e00fd','oVyyaZX','0','2020-05-06 13:36:30','2020-05-06 13:36:30'),(21,'Siri','sirimanelli2020@gmail.com','b144ae31bd43c8ac2b08d5b765df2921','VRzAIH1','0','2020-05-06 13:45:40','2020-05-06 13:45:40'),(22,'Siri','sirimanelli2020@gmail.com','bbae0275e92d31ce7072c4c14b7666b9','VJ5NTWq','0','2020-05-06 13:49:02','2020-05-06 13:49:02'),(23,'Siri','sirimanelli2020@gmail.com','bd985861ca750e342e05e0cd409773a1','SWHMyRn','0','2020-05-06 13:51:49','2020-05-06 13:51:49'),(24,'Siri','sirimanelli2020@gmail.com','f10c5c262015621afae2c2e953bbe095','XlSRIiS','0','2020-05-06 14:06:21','2020-05-06 14:06:21'),(25,'Siri','sirimanelli2020@gmail.com','231cd6c20f77d5d2bf605465b0dff20a','CiZijvf','0','2020-05-06 15:41:57','2020-05-06 15:41:57'),(26,'Sravani','sravani.yelesam@ptgindia.com','a2a47ed418aedb02028016c9fc2e3b2a','011dTyu','0','2020-05-07 04:52:12','2020-05-07 04:52:12'),(27,'gdvsd','dfvvfdvc@gmail.com','dbb2b5ff4b51de09956fa9e69559ac79','w8fb0SN','0','2020-05-13 04:30:42','2020-05-13 04:30:42'),(28,'Sandhya','sandhyarani.manelli@ptgindia.com','4ebed79903f9d6943d89dd1f809bd571','NbjhRFm','0','2020-05-14 07:17:24','2020-05-14 07:17:24'),(29,'Sandhya','sandhyarani.manelli@ptgindia.com','99331bbad5074fe7d80034496d9d19ae','A3d0ndS','0','2020-05-14 07:59:38','2020-05-14 07:59:38'),(30,'Sandhya','sandhyarani.manelli@ptgindia.com','22a0ee2c951940dc76e454c8d3ac0c3e','Y0PgDql','0','2020-05-14 08:01:35','2020-05-14 08:01:35'),(31,'Sandhya','sandhyarani.manelli@ptgindia.com','87e31ad1a34a2ad79f7b48646141edf2','gbshwJN','0','2020-05-14 08:03:11','2020-05-14 08:03:11'),(32,'Sandhya','sandhyarani.manelli@ptgindia.com','334cae9ab23443a78b26cf7a9237a09f','80H3VcJ','0','2020-05-14 08:03:29','2020-05-14 08:03:29'),(33,'Sandhya','sandhyarani.manelli@ptgindia.com','55f69b0d2cea5db978d04f8743e21e74','gZFbCXZ','0','2020-05-14 08:03:54','2020-05-14 08:03:54'),(34,'Sand','sandhyarani.manelli@ptgindia.com','985b3f959869bb238484ca6657941310','CK6HF6X','0','2020-05-19 03:08:56','2020-05-19 03:08:56'),(35,'Sandhya','sandhyarani.manelli@ptgindia.com','fae052c25eff0745b019ba0b9db1a1b5','klnqb7N','0','2020-05-21 03:59:25','2020-05-21 03:59:25'),(36,'Sandhya','sandhyarani.manelli@ptgindia.com','87b9b03cd76925bd17ccaefde817e74a','pyzZiYN','0','2020-05-21 04:08:17','2020-05-21 04:08:17'),(37,'Sandhya','sandhyarani.manelli@ptgindia.com','66827d41593ff5b88abd614408823a06','wV6aPWe','0','2020-05-21 04:09:54','2020-05-21 04:09:54'),(38,'Sandhya','sandhyarani.manelli@ptgindia.com','3b94ab5713dcf8bc1b8f9c512cf4957f','nQv1Fo4','0','2020-05-21 04:10:11','2020-05-21 04:10:11'),(39,'Sandhya','sandhyarani.manelli@ptgindia.com','3a9d9e3741f96ea53fedd2265f006b84','ngr1FTD','0','2020-05-21 04:10:38','2020-05-21 04:10:38'),(40,'Sandhya','sandhyarani.manelli1@ptgindia.com','fed0a8c6467c2f89b63cd642aa0110da','IwJ7s7g','0','2020-05-21 04:11:03','2020-05-21 04:11:03'),(41,'Sandhya','sandhyarani.manelli11213@ptgindia.com','9243b49128b0a65d5c37498a1b4a9ffc','LO8NdGY','0','2020-05-21 04:12:33','2020-05-21 04:12:33'),(42,'Sandhya','san@gmail.com','9190311bd868c1db3265b97f2b662f9e','XZoSwOz','0','2020-05-21 04:13:11','2020-05-21 04:13:11'),(43,'Sandhya','san21414@gmail.com','516977d9e51c18df59f12694f0f41942','0i7sQP7','0','2020-05-21 04:14:42','2020-05-21 04:14:42'),(44,'Sandhya','san21414123@gmail.com','fd4b04133a5fe0cf9316dbcd493e1852','GfbfjW2','0','2020-05-21 04:17:08','2020-05-21 04:17:08'),(45,'Sandhya','san2141432123@gmail.com','dd5ba823ea23b8f82a150bce259f4dd3','PcKQmFJ','0','2020-05-21 04:52:41','2020-05-21 04:52:41'),(46,'Sandhya','san21414321233@gmail.com','5a51bb23dbf6f7e65601f51e519f683c','8YiQvpC','0','2020-05-21 05:00:18','2020-05-21 05:00:18'),(47,'Sandhya','sandhyarani.manelli@ptgindia.com','be28121b45b9827e239cc4232e3a02a6','D32rIld','0','2020-05-21 05:01:47','2020-05-21 05:01:47'),(48,'Sandhya','sandhyarani.manelli@ptgindia.com','2e5e0fa9bf7a7dcdf182ab30179117f2','OaHEEw4','0','2020-05-21 05:04:31','2020-05-21 05:04:31'),(49,'Sandhya','sandhyarani.manelli@ptgindia.com','768fcc0d44e89d13ba3749722afb0f29','bsYxky2','0','2020-05-21 05:09:23','2020-05-21 05:09:23'),(50,'Sandhya','sandhyarani.manelli@ptgindia.com','6559e7b4f83c53718d2fe08ff0abdb9b','zXQx5hV','0','2020-05-21 05:10:48','2020-05-21 05:10:48'),(51,'Sandhya','sandhyasandym.17@gmail.com','9e6f9ff5bc650ba7bbb8ea417125e40c','t1Sf2Bj','0','2020-05-21 08:22:41','2020-05-21 08:22:41'),(52,'Sandhya','sandhyasandym.17@gmail.com','631e1caf743de25f41cff56980c49c97','0vW50kK','0','2020-05-21 10:48:02','2020-05-21 10:48:02'),(53,'Sandhya','sandhyasandym.17@gmail.com','fe95c67e07a82a3856ed596fddb11e73','72vqQdE','0','2020-05-21 11:04:20','2020-05-21 11:04:20'),(54,'Sandhya','sandhyasandym.17@gmail.com','d361fabcbb82d3407a4984cdaddd3348','RZfpwvU','0','2020-05-21 11:07:53','2020-05-21 11:07:53'),(55,'Sandhya','sandhyasandym.17@gmail.com','dc1938591af51dd00b9b25505d9b6db9','A7HleZx','0','2020-05-21 11:10:00','2020-05-21 11:10:00'),(56,'Sandhya','sandhyasandym.17@gmail.com','174287b4846877b1f5b5511296d2e388','6asqBhh','0','2020-05-21 11:17:10','2020-05-21 11:17:10'),(57,'Bob','bob.duffy@peopletech.com','50881f5bf8d6a537ab9ea49559a80166','C62xlqk','0','2020-05-24 03:26:44','2020-05-24 03:26:44'),(58,'Mitchey','mitch.hubermann@gmail.com','24f06f953360455bfb7a59cf7dffb119','VbFw9Bh','0','2020-05-28 00:10:17','2020-05-28 00:10:17'),(59,'Sand','sandhyarani.manelli@ptgindia.com','495f92c0ef7be4c02b6b84072137e311','BVeRF6z','0','2020-05-29 11:07:51','2020-05-29 11:07:51'),(60,'Sand','sandhyarani.manelli@ptgindia.com','24a1944d1fefc9309138472ef37d15c4','EKIggZo','0','2020-05-29 11:25:48','2020-05-29 11:25:48'),(61,'','sandhyarani.manelli@gmail.com','4bfba28654475e47e08a7cc45e1ef36f','wCPwYxX','0','2020-05-29 15:18:12','2020-05-29 15:18:12'),(62,'','sandhyarani.manelli@ptgindia.com','','QFLR5WN','0','2020-05-29 15:30:11','2020-05-29 15:30:11'),(63,'','sandhyarani.manelli@ptgindia.com','88cacf9403b806bdd40f7ec318037c49','cYvylQn','0','2020-06-04 16:15:47','2020-06-04 16:15:47'),(64,'Vasudev Baggi','vasudevbaggi07@gmail.com','a87072d92c8a6403ac5eb0b5ff970831','qX7uMEc','0','2020-06-22 14:29:43','2020-06-22 14:29:43');
/*!40000 ALTER TABLE `subsellusers` ENABLE KEYS */;
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
  KEY `users_client_id_foreign` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'bbadmin','chand@billboardsindia.com','921aab40aa6f5cb672786a4767672cb1','bb94213',1,'2018-09-12 13:28:21','2019-11-28 08:04:01'),(2,NULL,NULL,'siri.vulupala@gmail.com','e98ff34eb2b95bbd384e3bc6d0ae8d10','1qXM3Ly',0,'2018-09-21 07:34:33','2018-09-21 07:34:33'),(3,NULL,NULL,'pavan.arutla@ptgindia.com','90e9edb70b74552dc03aad3c13728503','2dg5CUM',1,'2018-09-24 12:42:49','2019-11-27 11:41:25'),(4,3,NULL,'mcclemmy.richard@gmail.com','fa670db977abd11edc70e04204f40fe4','bb94213',1,'2018-10-02 10:32:49','2020-02-05 17:40:22'),(5,4,NULL,'chand375@gmail.com','da6c2924d2f0ab1185756ff52103ad48','wpKmVPQ',0,'2018-10-23 07:24:15','2019-11-28 09:37:45'),(6,7,NULL,'srikanthbijjala1990@gmail.com','15e67345612a99499da6e21061ec52b0','abjVkjL',1,'2018-10-23 08:37:42','2018-10-23 08:45:27'),(7,NULL,NULL,'jeremiah.nyman@peopletechgroup.com','0817b82f8a15f036adf564d2e8cf90dd','wzst7rM',0,'2018-12-17 06:31:34','2018-12-17 06:31:34'),(8,8,NULL,'o4082583@nwytg.net','29d5f9974154bafac94523560960e96b','jknqACI',1,'2018-12-17 06:36:32','2018-12-19 02:43:45'),(9,9,NULL,'skyleigh.charla@cowaway.com','16424c34644ef5e5d71897832691325f','aTwTx5q',1,'2018-12-17 06:42:41','2018-12-17 09:21:17'),(10,NULL,NULL,'o4443663@nwytg.net','29e244198a5304cf8e2c88223bb1ceed','KyrMMQK',1,'2018-12-19 02:45:06','2018-12-19 02:46:11'),(11,10,NULL,'alyna.rinka@cowaway.com','a077725725d0808a649b9964feb53298','id577ot',0,'2018-12-19 02:48:32','2019-11-28 10:12:57'),(12,11,NULL,'heyimfamous@gmail.com','6085f110b8e9bdbf8800fd56f38c489f','sDUgrrY',1,'2018-12-24 06:22:11','2018-12-24 06:24:34'),(13,NULL,NULL,'sandhya@gmail.com','3f4ff06e01e208e8eba47e6496103e94','PmlUIOq',0,'2019-11-27 07:02:55','2019-11-27 07:02:55'),(14,2,NULL,'san@gmail.com','','5brtMQJ',1,'2019-11-28 09:17:02','2020-06-28 02:15:01'),(15,NULL,NULL,'anroop@gmail.com','e14dcf2a861db7c4d4d9fa837a876c8e','KQVTOTl',0,'2020-01-28 04:10:58','2020-01-28 04:10:58'),(16,NULL,NULL,'anroop111111@gmail.com','48e24aec7d15145b5dd856dca9ff97de','2z9RfA2',0,'2020-01-28 04:11:31','2020-01-28 04:11:31'),(17,NULL,NULL,'sandhyarani.manelli@ptgindia.com','5d8fc0cf834e5fd105f14a86a8622ba7','HdifFxu',1,'2020-01-30 04:54:22','2020-02-05 04:25:10'),(18,NULL,NULL,'sravani.yelesam@ptgindia.com','40dcccaf6fc4a330787fd07f48fe8a32','GkNu0ud',0,'2020-01-30 09:17:37','2020-01-30 09:17:37'),(19,NULL,NULL,'test@test.com','5a815b6909de1f37fea62d1422805bac','42C4Nbx',0,'2020-01-30 09:21:25','2020-01-30 09:21:25'),(20,NULL,NULL,'sravani.yelesam@gmail.com','d22debee6d3b98b9e5d90c7f62845f7f','WqQ9dNr',0,'2020-01-30 09:39:10','2020-01-30 09:39:10'),(21,NULL,NULL,'sravani.yelesam@hotmail.com','964f6a84f06f782df73ca7368a608c53','KIxvjbi',0,'2020-01-30 09:39:47','2020-01-30 09:39:47'),(22,NULL,NULL,'vasubaggi7@gmail.com','b4c0af0a05f6aa23791385edb2afff03','2HiJYwm',0,'2020-01-30 09:40:58','2020-01-30 09:40:58'),(23,NULL,NULL,'vasudevbaggi7@gmail.com','0777e5aa26f6a6f7276e6e9e0d3eeed2','7bd2CIx',0,'2020-01-30 09:42:45','2020-01-30 09:42:45'),(24,NULL,NULL,'vasudev@gmail.com','1a73f9521f14ed24af857d40925edc57','1H0wdle',0,'2020-01-30 09:53:16','2020-01-30 09:53:16'),(25,NULL,NULL,'vasudev.baggi@ptgindia.com','2eeacf4e410ee53a334db89a9092a348','iYyakPx',1,'2020-02-18 11:46:23','2020-03-20 14:26:44'),(26,NULL,NULL,'shiva.karunakar@ptgindia.com','7d9049286855616380319a4dd8f9e7d4','AGBSNYS',1,'2020-03-20 14:02:51','2020-03-20 14:03:37'),(27,NULL,NULL,'shivakumar.karunakar@gmail.com','cfcdacca8787df21fc001e71b12fc1f1','TpkPzDe',1,'2020-03-20 14:10:18','2020-03-23 04:56:14'),(28,NULL,NULL,'shivakumar.karunakar@yahoo.co.in','2568a596fadf2ad7ab2b8240fc6af5b7','NAS0mCV',1,'2020-03-20 14:11:22','2020-03-23 04:56:17'),(29,NULL,NULL,'goutham.rudroju@ptgindia.com','8b735c9dddc152cf4cbbee93fd5692df','r1Va9lF',1,'2020-03-20 14:20:25','2020-03-20 14:24:27'),(30,12,NULL,'ptg.bba@gmail.com','43b8929f0f6d37bb3c2af8a73b9844d4','JPx8sih',0,'2020-03-20 14:37:58','2020-04-13 22:46:01'),(31,NULL,NULL,'robert.mk@codesmartinc.com','815706c0d9bf4ec5efd8c7fdbc2b5a14','ecflKNa',1,'2020-03-20 22:12:14','2020-03-23 04:10:28'),(32,NULL,NULL,'kmacc68@msn.com','301fcbb46e71b7d36a69747c13c3558e','il1f0Nb',1,'2020-04-13 23:04:46','2020-04-15 05:12:51'),(33,13,NULL,'sandhyamanelli@gmail.com','f84c11c7a9353e6a7b4ff5e9d1857db2','TvxL24X',0,'2020-04-14 06:22:21','2020-04-14 06:22:21'),(34,14,NULL,'manellianupama91@gmail.com','7345bbf2583ff0b58f9e50e1d922d596','R8d9RH5',0,'2020-04-16 12:56:49','2020-04-16 12:56:49'),(35,15,NULL,'ramamanelli98@gmail.com','ef40c499c720e38d4ac240406bb0ba5a','pbhK4c1',1,'2020-04-16 13:03:26','2020-04-16 13:08:57'),(36,NULL,NULL,'vasudevbaggi07@gmail.com','e3faf1f52c4be9d5196c6bdb8afd8ead','XDnUQtJ',1,'2020-04-27 05:34:02','2020-04-27 05:35:29'),(37,NULL,NULL,'sand@gmail.com','7b48f2282321eff302db53377ea63226','x5GZx1G',1,'2020-04-27 10:48:25','2020-05-06 06:18:27'),(38,16,NULL,'sandmanelli1717@gmail.com','819c1cde63b087300c891b304788ebcc','Qe2QxXt',0,'2020-05-06 08:57:08','2020-05-06 08:57:08'),(39,17,NULL,'anumanelli1717@gmail.com','3c8b6229aa86e35a20d4bdffe4ddd2ae','OZRK1bf',0,'2020-05-06 09:38:02','2020-05-06 09:38:02'),(40,18,NULL,'anumanelli71717@gmail.com','384e4138cb62bd9111baee7f4f6f72d9','sNbnrfQ',0,'2020-05-06 09:41:48','2020-05-06 09:42:59'),(41,19,NULL,'baggikeyur10@gmail.com','31bbf9531fcc68a6e8d64441ce95e68f','O0k7ZiV',0,'2020-05-06 11:05:59','2020-05-06 11:05:59'),(42,20,NULL,'vasudevbaggi@gmail.com','a544cf5bfb965e77f9da3740915c524b','LXW6eDB',0,'2020-05-06 11:13:45','2020-05-06 11:13:45'),(43,21,NULL,'vasudevkbaggi@gmail.com','1c12f1eebe4c63df7191c3f10d25bd1e','QXg5l3p',0,'2020-05-06 11:15:53','2020-05-06 11:15:53'),(44,22,NULL,'vasudevbaggi70@gmail.com','f68eb38eede5f5bcfd91b8739219205c','UxIoKxv',0,'2020-05-06 11:20:17','2020-05-06 11:20:17'),(45,23,NULL,'ramamanelli2020@gmail.com','e4543d5325729443eeb49fabedc48ad4','JNZwyA4',0,'2020-05-06 11:21:07','2020-05-06 11:21:07'),(46,NULL,NULL,'nishakd16@gmail.com','c66d6d6dd41f8aa89f40073dca094e9d','tabqs8U',0,'2020-05-06 11:48:13','2020-05-06 11:48:13'),(47,24,NULL,'sirimanelli2020@gmail.com','7324e89d398c8e3b328b7364573ebcc1','UeS1zRL',0,'2020-05-06 14:46:03','2020-05-06 14:46:03'),(48,NULL,NULL,'sandhyasandym.17@gmail.com','b6185318ca53b198ddc22e27ea58ff7c','uPGlRed',1,'2020-05-21 09:55:37','2020-05-21 09:57:02'),(49,31,NULL,'ampseller1@gmail.com','851ecf13c0fd0cf20563e81e2f7749d5','MzjtMHP',1,'2020-05-21 10:20:00','2020-05-21 12:23:29'),(50,32,NULL,'ampseller2@gmail.com','29c0e330380c9bfffdf34aafdb263b06','k6PZ5mH',1,'2020-05-21 12:40:13','2020-05-21 12:47:40'),(51,33,NULL,'ampseller3@gmail.com','e636a090805cfb95bb5c60634a651ccd','b8RYCif',1,'2020-05-21 13:02:28','2020-05-21 13:09:55'),(52,NULL,NULL,'buyer1amp@gmail.com','55c657397fb69d459b48bc87c7f9e6b9','XBDeUr2',1,'2020-05-21 16:35:36','2020-05-21 16:36:38'),(53,NULL,NULL,'buyer2@gmail.com','2d9187cd303602cce404958778597f4d','5o5o3GC',0,'2020-05-21 17:35:26','2020-05-22 03:06:59'),(54,NULL,NULL,'buyer2amp@gmail.com','b2ecd01dc2dd6ea10fc35b2f494a920b','fN2wH8j',1,'2020-05-22 01:47:30','2020-05-22 01:50:44'),(55,NULL,NULL,'buyer3amp@gmail.com','9c197b1586a4b2be770ccf5f6fd1c2e9','lsIRy5l',1,'2020-05-22 01:54:57','2020-05-22 01:57:12'),(56,NULL,NULL,'testp@gmail.com','352e9968de8c7babebfd03e38a8c6006','BVrNzzn',0,'2020-06-10 11:23:13','2020-06-10 11:23:13'),(58,37,NULL,'sandsandhya777@gmail.com','7269865f8c497b761385de62dbd85902','pOlEnyL',1,'2020-06-23 07:52:58','2020-06-23 07:56:35'),(59,38,NULL,'sandsandhya7777@gmail.com','e20c4dbd97532efaebbc171c46477853','iDattxT',0,'2020-06-23 08:13:30','2020-06-23 08:17:26'),(60,40,NULL,'sandsandhya111@gmail.com','3044288fee3cde80bd09ba15d522e053','Qfv3lVB',0,'2020-06-24 09:16:34','2020-06-24 09:16:34'),(61,41,NULL,'sandsandhya1111@gmail.com','e76dfefa7c6e185c8b5c4f847cefa241','bQEgUKn',0,'2020-06-24 10:09:05','2020-06-24 10:09:05'),(62,42,NULL,'sandsandhya11111@gmail.com','af6c85e75bec62934bb84ecc6adee7a9','g65NxuS',0,'2020-06-24 10:10:54','2020-06-24 10:10:54'),(63,43,NULL,'sirisha@gmail.com','6d76b446ffcbe7d127cf69634038a52d','sOstCmj',0,'2020-06-24 10:31:12','2020-06-24 10:31:12'),(64,44,NULL,'sirisirisha9666@gmail.com','11a45c62db42089fe28d6267966ffce4','K0y0NQn',0,'2020-06-24 10:32:54','2020-06-24 10:32:54'),(65,45,NULL,'sirisirisha966666@gmail.com','c5d1320af168211e1084e69a4d4dfd8c','TORC2hW',0,'2020-06-24 10:44:03','2020-06-24 10:44:03'),(66,46,NULL,'sirisirisha12345@gmail.com','47b6c78284f22ef4d517f3c078703b00','FgHO7yC',0,'2020-06-24 11:12:29','2020-06-24 11:12:29'),(67,47,NULL,'sirisirisha000@gmail.com','e1e69209838b582299ff5015cbe3ef21','OWwHfpu',0,'2020-06-24 12:46:40','2020-06-24 12:46:40'),(68,48,NULL,'siri12345@gmail.com','d78fa2d5ab450675ccadb773b45ad899','rOE8K3q',0,'2020-06-24 12:49:24','2020-06-24 12:49:24'),(69,49,NULL,'siri12345678@gmail.com','56d49e7418f52da06f101359f9253b2b','ruIVfHl',0,'2020-06-24 12:57:21','2020-06-24 12:57:21'),(70,50,NULL,'anu@gmail.com','9015a29e3c2d402d16b083e622f1394c','qZH1gZL',0,'2020-06-24 12:58:57','2020-06-24 12:58:57'),(71,51,NULL,'anu123@gmail.com','6eab86a8114acff949a7f257138adccb','wfaDlFt',0,'2020-06-24 13:00:21','2020-06-24 13:00:21'),(72,52,NULL,'anu12345@gmail.com','e1a3652a8a9aef0fa9dccbaed161bddb','iuLm2xj',0,'2020-06-24 13:06:01','2020-06-24 13:06:01'),(73,53,NULL,'sirisirsha9666@gml.com','df96ce17e17d072f1a58dede6c72c2b6','wj23dOv',0,'2020-06-24 13:08:52','2020-06-24 13:08:52'),(74,54,NULL,'sirisirisha9666@gml.com','d82ef0321453282fa6190d400b6eaaee','JvQRpNU',0,'2020-06-24 13:10:57','2020-06-24 13:10:57'),(75,55,NULL,'anupama95423@gmail.com','0272e32787a564c22074cb584745e631','xi2GQfw',1,'2020-06-24 13:29:08','2020-06-25 00:10:27'),(76,56,NULL,'sravaniyelesam94@gmail.com','210d8737f184065b68f7555d702d3302','lp8XdCh',1,'2020-06-25 04:53:29','2020-06-26 05:03:30'),(77,57,NULL,'sravaniyelesam19940@gmail.com','','87m69As',0,'2020-06-25 05:45:36','2020-06-25 05:45:36'),(78,58,NULL,'sravaniyelesam199400@gmail.com','','odRRfrE',0,'2020-06-25 05:47:09','2020-06-25 05:47:09'),(79,59,NULL,'sravaniyelesam2020@gmail.com','','8URaGCL',0,'2020-06-25 06:14:21','2020-06-25 06:14:21'),(80,60,NULL,'sravaniyelesam1994@gmail.com','da6628666f15c75924a199928f39e5f1','KijCpHW',1,'2020-06-25 06:19:44','2020-06-26 06:45:08'),(81,4,NULL,'sravaniyelesam@gmail.com','','qoTzxvS',0,'2020-06-25 06:30:57','2020-06-25 06:30:57'),(82,62,NULL,'sravaniyelesam123@gmail.com','','3ZzVztD',0,'2020-06-25 06:33:16','2020-06-25 06:33:16'),(83,63,NULL,'sravaniyelesam12345@gmail.com','','zJ4YlBT',0,'2020-06-25 07:04:04','2020-06-25 07:04:04'),(84,64,NULL,'sravaniyelesam9999@gmail.com','','yQx0tgo',0,'2020-06-25 07:04:54','2020-06-26 09:10:48');
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

-- Dump completed on 2020-06-30  6:38:39
