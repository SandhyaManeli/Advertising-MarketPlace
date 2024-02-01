-- MySQL dump 10.13  Distrib 5.7.31, for Linux (x86_64)
--
-- Host: localhost    Database: accounts
-- ------------------------------------------------------
-- Server version	5.7.31-0ubuntu0.16.04.1

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
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,'BBI',1,1,'bbi',1,'2018-09-12 13:28:21','2018-09-12 13:28:21'),(2,'TMC Media',2,14,'tmc-media',1,'2018-09-14 11:01:21','2019-11-28 09:17:16'),(3,'Landmark OOH',2,4,'landmark-ooh',1,'2018-09-14 11:01:53','2018-10-02 10:32:52'),(4,'Test Pvt',2,5,'test-pvt',1,'2018-10-23 07:21:44','2018-10-23 07:24:19'),(5,'Demo TMC',2,13,'demo-tmc',1,'2018-10-23 08:17:23','2019-11-28 09:20:12'),(6,'TMC Demo',2,3,'tmc-demo',1,'2018-10-23 08:25:22','2018-10-23 08:25:59'),(7,'Demo - Landmark OOH',2,6,'demo---landmark-ooh',1,'2018-10-23 08:37:12','2018-10-23 08:37:46'),(8,'Landmark',2,8,'landmark',1,'2018-12-17 06:36:32','2018-12-17 06:36:32'),(9,'Landmark LLC - 1',2,9,'landmark-llc---1',1,'2018-12-17 06:42:41','2018-12-17 06:42:41'),(10,'TMC Holdings',2,11,'tmc-holdings',1,'2018-12-19 02:48:32','2018-12-19 02:48:32'),(11,'Billboards Inc',2,12,'billboards-inc',1,'2018-12-24 06:22:11','2018-12-24 06:22:11'),(31,'Seller',2,49,'seller5ec655d0c85b3',1,'2020-05-21 10:20:00','2020-05-21 10:20:00'),(32,'AmpSeller',2,50,'ampseller5ec676ad2f544',1,'2020-05-21 10:20:00','2020-05-21 10:20:00'),(33,'SellerAmo',2,51,'selleramo5ec67be40f2ac',1,'2020-05-21 10:20:00','2020-05-21 10:20:00'),(89,'Abe Noe',2,108,'abe-noe5f107f93c5df3',1,'2020-07-16 16:25:55','2020-07-16 16:25:55'),(90,'TG Shaw',2,109,'tg-shaw5f109df0854bb',1,'2020-07-16 18:35:28','2020-07-16 18:35:28'),(91,'Tina Turner',2,110,'tina-turner5f10bf5e038a5',1,'2020-07-16 20:58:06','2020-07-16 20:58:06'),(92,'Ben Hartling',2,111,'ben-hartling5f119aab34a01',1,'2020-07-17 12:33:47','2020-07-17 12:33:47'),(93,'Carson Luna',2,112,'carson-luna5f120b3e6673b',1,'2020-07-17 20:34:06','2020-07-17 20:34:06'),(94,'Steve DiTolla',2,113,'steve-ditolla5f16137352b12',1,'2020-07-20 21:58:11','2020-07-20 21:58:11'),(95,'John Mulholland',2,115,'john-mulholland5f172aac124fa',1,'2020-07-21 17:49:32','2020-07-21 17:49:32'),(96,'Joe Frisch',2,116,'joe-frisch5f172f8e129af',1,'2020-07-21 18:10:22','2020-07-21 18:10:22'),(97,'Joseph Grippo',2,122,'joseph-grippo5f19ee83409f8',1,'2020-07-23 20:09:39','2020-07-23 20:09:39'),(98,'Peter Bellas',2,125,'peter-bellas5f1f6b8b02495',1,'2020-07-28 00:04:27','2020-07-28 00:04:27'),(99,'Dave Westburg',2,127,'dave-westburg5f2093df0d451',1,'2020-07-28 21:08:47','2020-07-28 21:08:47'),(100,'ERIC WOODS',2,129,'eric-woods5f21b98ccfc56',1,'2020-07-29 18:01:48','2020-07-29 18:01:48'),(101,'Lawrence Smallacombe',2,130,'lawrence-smallacombe5f21c3eb5d0ea',1,'2020-07-29 18:46:03','2020-07-29 18:46:03'),(102,'Kevin Kalua',2,134,'kevin-kalua5f234e480846f',1,'2020-07-30 22:48:40','2020-07-30 22:48:40'),(103,'Peter Bellas',2,139,'peter-bellas5f28178e183c8',1,'2020-08-03 13:56:30','2020-08-03 13:56:30'),(104,'Christopher Stanley',2,144,'christopher-stanley5f298cb41e4e1',1,'2020-08-04 16:28:36','2020-08-04 16:28:36'),(105,'Jason Wilson',2,145,'jason-wilson5f29bf8950855',1,'2020-08-04 20:05:29','2020-08-04 20:05:29'),(106,'Courtney Preiss',2,146,'courtney-preiss5f29d512bdb70',1,'2020-08-04 21:37:22','2020-08-04 21:37:22'),(107,'Ryan Miyaki',2,147,'ryan-miyaki5f29d8beac9de',1,'2020-08-04 21:53:02','2020-08-04 21:53:02'),(108,'Andrew Kleist',2,152,'andrew-kleist5f2d951244703',1,'2020-08-07 17:53:22','2020-08-07 17:53:22'),(109,'David Curry',2,153,'david-curry5f2d98c329247',1,'2020-08-07 18:09:07','2020-08-07 18:09:07'),(110,'Larry Smallacombe',2,155,'larry-smallacombe5f2df37875903',1,'2020-08-08 00:36:08','2020-08-08 00:36:08');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2017_09_05_083648_entrust_setup_tables',1);
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
INSERT INTO `role_user` VALUES (1,1),(2,2),(2,3),(3,3),(3,4),(2,5),(3,5),(3,6),(3,49),(3,50),(3,51),(2,52),(3,52),(2,53),(2,54),(3,54),(2,55),(3,55),(2,100),(2,105),(2,106),(2,107),(3,108),(2,109),(2,110),(2,111),(2,112),(2,113),(2,114),(3,114),(2,115),(2,116),(3,117),(2,118),(3,119),(2,120),(2,121),(2,122),(3,123),(2,124),(3,125),(2,126),(2,127),(2,128),(2,129),(2,130),(2,131),(2,132),(2,133),(2,134),(2,135),(2,136),(2,137),(2,138),(2,139),(2,140),(2,141),(2,142),(2,143),(2,144),(2,145),(2,146),(2,147),(2,148),(2,149),(2,150),(2,151),(2,152),(2,153),(2,154),(2,155);
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
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'bbadmin','chand@billboardsindia.com','921aab40aa6f5cb672786a4767672cb1','bb94213',1,'2018-09-12 13:28:21','2019-11-28 08:04:01'),(2,NULL,NULL,'siri.vulupala@gmail.com','e98ff34eb2b95bbd384e3bc6d0ae8d10','1qXM3Ly',0,'2018-09-21 07:34:33','2018-09-21 07:34:33'),(3,NULL,NULL,'pavan.arutla@ptgindia.com','90e9edb70b74552dc03aad3c13728503','2dg5CUM',1,'2018-09-24 12:42:49','2019-11-27 11:41:25'),(4,3,NULL,'mcclemmy.richard@gmail.com','2f57e356af9b3d7b6e056173c0452c1d','bb94213',1,'2018-10-02 10:32:49','2020-07-16 05:01:35'),(5,4,NULL,'chand375@gmail.com','da6c2924d2f0ab1185756ff52103ad48','wpKmVPQ',0,'2018-10-23 07:24:15','2019-11-28 09:37:45'),(6,7,NULL,'srikanthbijjala1990@gmail.com','15e67345612a99499da6e21061ec52b0','abjVkjL',1,'2018-10-23 08:37:42','2018-10-23 08:45:27'),(49,31,NULL,'ampseller1@gmail.com','851ecf13c0fd0cf20563e81e2f7749d5','MzjtMHP',1,'2020-05-21 10:20:00','2020-05-21 12:23:29'),(50,32,NULL,'ampseller2@gmail.com','29c0e330380c9bfffdf34aafdb263b06','k6PZ5mH',1,'2020-05-21 12:40:13','2020-05-21 12:47:40'),(51,33,NULL,'ampseller3@gmail.com','e636a090805cfb95bb5c60634a651ccd','b8RYCif',1,'2020-05-21 13:02:28','2020-05-21 13:09:55'),(52,NULL,NULL,'buyer1amp@gmail.com','55c657397fb69d459b48bc87c7f9e6b9','XBDeUr2',1,'2020-05-21 16:35:36','2020-05-21 16:36:38'),(53,NULL,NULL,'buyer2@gmail.com','2d9187cd303602cce404958778597f4d','5o5o3GC',0,'2020-05-21 17:35:26','2020-05-22 03:06:59'),(54,NULL,NULL,'buyer2amp@gmail.com','b2ecd01dc2dd6ea10fc35b2f494a920b','fN2wH8j',1,'2020-05-22 01:47:30','2020-05-22 01:50:44'),(55,NULL,NULL,'buyer3amp@gmail.com','9c197b1586a4b2be770ccf5f6fd1c2e9','lsIRy5l',1,'2020-05-22 01:54:57','2020-05-22 01:57:12'),(100,86,NULL,'patrick.mcclemmy@gmail.com','','MlfnPnR',1,'2020-07-08 23:54:16','2020-07-21 18:10:24'),(105,NULL,NULL,'tourismads01@gmail.com','c08d5e67491164f5a54577ccd47c274c','tZ1H7J2',0,'2020-07-13 14:09:08','2020-07-13 14:09:08'),(106,NULL,NULL,'tom@thomasprinters.com','2e9b2cea8edb24dbb9dab50f82af9f79','B8shOy2',0,'2020-07-13 16:42:48','2020-07-13 16:42:48'),(107,NULL,NULL,'tmiller417@gmail.com','77276b75ca93421e900199868dba0679','jXaMhtC',1,'2020-07-15 21:31:23','2020-07-15 21:35:10'),(108,89,NULL,'Abe@bigcityoutdoor.com','9dc6df8ad847684d8276175d948f0544','U78VXln',1,'2020-07-16 16:25:55','2020-07-16 16:29:14'),(109,90,NULL,'tshaw@reevesmedia.com','4e1771b0b6a57fd09f1e49998652c5ef','c4SixHE',1,'2020-07-16 18:35:28','2020-07-16 18:37:10'),(110,91,NULL,'tina@tntoutdoor.com','6ff201960c4648707cbb463ff69493f4','Is5IONd',1,'2020-07-16 20:58:06','2020-07-16 20:59:53'),(111,92,NULL,'hartlingbenjamin@gmail.com','6afa01bc8a2aa0645d4eb02b37dfce6c','KUENSma',1,'2020-07-17 12:33:47','2020-07-17 18:27:04'),(112,93,NULL,'carson.luna@foxsports.net','71c15d35438a950e0d2aed2799b75ac7','JcY4DU9',1,'2020-07-17 20:34:06','2020-07-17 20:34:41'),(113,94,NULL,'sditolla@fullerton.edu','3df753dc078d6ba706473c895f781981','GKEGqUq',1,'2020-07-20 21:58:11','2020-07-20 21:59:47'),(114,NULL,NULL,'khaledabdelwahed@yahoo.com','c0deec54380a82895b0276ecd75d0bd5','PBWcAHd',1,'2020-07-20 23:23:01','2020-07-20 23:34:18'),(115,95,NULL,'john@filowifi.com','5a48f71d9011689588e6123fd2e9357e','T0MoY4p',1,'2020-07-21 17:49:32','2020-07-21 18:10:16'),(116,96,NULL,'jfrisch@picturemarketing.com','88871c45840fa3e9ad526354e642e23d','3EGfzP7',1,'2020-07-21 18:10:22','2020-07-21 18:17:00'),(117,NULL,NULL,'ttonini@picturemarketing.com','310fad0d32ec4d7b19fc246e395baa64','GNUE1Iz',1,'2020-07-21 18:13:10','2020-07-21 18:14:51'),(118,NULL,NULL,'Daniela.meola88@gmail.com','21c8bf457bbe2f0c89eeaf0af55b5971','ujn3TNs',1,'2020-07-21 19:10:45','2020-07-21 19:11:38'),(119,NULL,NULL,'info@adwalls.com','c943bbf1296b0b4a75f17b612bef635b','5ftc9na',1,'2020-07-21 22:55:26','2020-07-21 22:57:32'),(120,NULL,NULL,'aimeebk@gmail.com','fd6f505a348965db73fbd0787d91f1d5','NKXfV8j',1,'2020-07-22 21:49:06','2020-07-22 21:49:52'),(121,NULL,NULL,'tturner@tntoutdoor.com','38bc3859d8cf66aa36fc8a12c51f6e1b','4K9yngp',1,'2020-07-23 00:09:30','2020-07-23 20:09:37'),(122,97,NULL,'jgrippo10@verizon.net','50ba5d223e138076054aff6d9b9d7fd7','TTc5nJK',1,'2020-07-23 20:09:39','2020-07-23 20:11:53'),(123,NULL,NULL,'mitch.huberman@gmail.com','b7cadd7b4b7ef5a85388abf85d042d8c','6XkZrhX',1,'2020-07-24 20:08:35','2020-07-24 20:08:52'),(124,NULL,NULL,'stacyo@omegamarketing.us','c257b2d0995e2e04c65d0d6306a0d005','7R2FcxE',1,'2020-07-24 22:06:18','2020-07-24 22:06:37'),(125,98,NULL,'bellas.pete@gmail.com','bf4471d3153fca32c6ba221e8f6cbac9','sxAMULT',1,'2020-07-28 00:04:27','2020-07-28 02:20:24'),(126,NULL,NULL,'dasma.012@gmail.com','a6a18885ea6b18821f50ceaa4a4a4365','ViKKqtT',1,'2020-07-28 20:21:58','2020-07-28 20:22:40'),(127,99,NULL,'circlecitybillboards@gmail.com','7d186eed8244b0841be6f13096c0f1f8','Uw2myCX',1,'2020-07-28 21:08:47','2020-07-28 21:10:05'),(128,NULL,NULL,'darleneipuente@gmail.com','583a47d8536ba2ef8d51e4264d4bcb19','pk1S5S9',1,'2020-07-29 02:03:00','2020-07-29 15:43:12'),(129,100,NULL,'eric@calgolfnews.com','85bdde9adb07e6e383205e29603f3f4a','HS2Da0F',1,'2020-07-29 18:01:48','2020-07-29 18:03:35'),(130,101,NULL,'lawrence.smallacombe@gmail.com','6de5dbf4da1d839e6e5da414dac310fd','ZqpTCTQ',1,'2020-07-29 18:46:03','2020-07-29 18:53:44'),(131,NULL,NULL,'partnerships@eventbizusa.com','33adbf52bf861942621e344948cf9a0d','SeC6yp9',1,'2020-07-29 21:12:20','2020-07-29 23:11:35'),(132,NULL,NULL,'eddie.vasquez@food4less.com','d250930ef53e69468a201abfc26c0195','Lj4RDrZ',1,'2020-07-29 23:43:21','2020-07-29 23:43:44'),(133,NULL,NULL,'lino@ikahanmedia.com','bf186f1a7dee3eab7ce73252ba03c56e','RUMGkLs',1,'2020-07-30 22:05:19','2020-07-30 22:12:31'),(134,102,NULL,'kevin@greatmediawall.com','93ee8f1e78d0ea1fef702773a4256991','gYCXhVI',1,'2020-07-30 22:48:40','2020-07-31 16:34:11'),(135,NULL,NULL,'lauren@skyebrookebrands.com','9741ef78341db552ae8a3b148ec38390','MpCceGC',1,'2020-07-31 16:31:06','2020-07-31 16:34:06'),(136,NULL,NULL,'dennisdo@advertisingmarketplace.com','2e6f61b7b596376e7148451060d5bd9d','HU4M69H',1,'2020-07-31 17:16:22','2020-07-31 17:16:49'),(137,NULL,NULL,'denise.bogan@gmail.com','51aae00de750c70bc1c00cf089dcd77d','DAhC20K',1,'2020-07-31 17:39:09','2020-07-31 17:39:35'),(138,NULL,NULL,'kmacc68@msn.com','8a839082aed0cd985e23252e3fda7e92','ajAswWN',1,'2020-08-02 18:07:24','2020-08-02 18:07:59'),(139,103,NULL,'mrbellas@gmail.com','94f77c6cda7c99c634c9e7cf29579e20','UIeXCdv',1,'2020-08-03 13:56:30','2020-08-03 16:45:03'),(140,NULL,NULL,'ray@orangecountysoccer.com','4a7ca08bfc8afe80324ae0da1a0c23dd','XdrVwTM',1,'2020-08-03 16:44:37','2020-08-03 16:44:52'),(141,NULL,NULL,'rick.robinson@billups.com','bae63466aa349079cd656880eefb01ef','Xb2ezak',1,'2020-08-03 19:08:40','2020-08-03 19:09:15'),(142,NULL,NULL,'etrevino@proticketent.com','1ccadba08c6f3906fa5d5fbf82655aa7','OCYsMWk',1,'2020-08-03 23:30:46','2020-08-04 01:23:17'),(143,NULL,NULL,'chris@teqyla.com','6f860d3db03010fd4f3a89ac19e18243','1p3ZyDH',1,'2020-08-04 16:27:09','2020-08-04 17:13:06'),(144,104,NULL,'chris@alcancemg.com','884adccd3f71ad77df67c93ead91fe85','Bt4v4ym',1,'2020-08-04 16:28:36','2020-08-04 17:13:02'),(145,105,NULL,'jasonwilson@trioutdoor.com','72074fd498fb93e4ca6c4e07ccab88fe','ZMvZE8Q',1,'2020-08-04 20:05:29','2020-08-04 20:07:02'),(146,106,NULL,'cpreiss@adwalls.com','7dc51da2785355570446aceceb23950f','6Nu2Wz0',1,'2020-08-04 21:37:22','2020-08-04 21:38:10'),(147,107,NULL,'miyaki@rushhourmedia.us','dca1635e7429cea175626e23968eca11','6lXLmZC',1,'2020-08-04 21:53:02','2020-08-04 22:30:23'),(148,NULL,NULL,'brian@quanmediagroup.com','ac4f7a13e47ae1ea30db0017d7bd8798','5q31f7g',1,'2020-08-05 19:07:27','2020-08-05 19:07:53'),(149,NULL,NULL,'robin.hall@daktronics.com','513ede2d7c78557464fbb00811886e75','RLaVDLN',1,'2020-08-05 21:07:42','2020-08-05 21:08:16'),(150,NULL,NULL,'marcysmith68@gmail.com','b9aec87bb2ab00565f5a79354987cb91','C6GGZDG',1,'2020-08-06 20:52:33','2020-08-06 20:52:55'),(151,NULL,NULL,'mnitti1941@gmail.com','753551e9332db2021ff23b961a34a29f','d18mtGj',1,'2020-08-06 23:11:35','2020-08-06 23:11:55'),(152,108,NULL,'akleist@beckerboards.com','f50cf14c145567ccebf4b997eecf4076','G9l4aEK',1,'2020-08-07 17:53:22','2020-08-07 18:02:49'),(153,109,NULL,'curr123955@aol.com','0097efb0f3b56907859bdad690bbaf1d','zQZmm6t',1,'2020-08-07 18:09:07','2020-08-07 18:11:11'),(154,NULL,NULL,'DDCONSULTANTS@GMAIL.COM','74302f33fb9530ab62cc6ed99d6866af','TMBdCgz',1,'2020-08-07 21:19:37','2020-08-07 21:20:15'),(155,110,NULL,'Larry.smallacombe@mgmoutdoor.com','30b058ba5133c22a5e4d0d1eb28f810f','xU0omuG',1,'2020-08-08 00:36:08','2020-08-08 00:36:58');
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

-- Dump completed on 2020-08-08 19:00:04
