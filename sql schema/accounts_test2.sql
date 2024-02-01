-- MySQL dump 10.13  Distrib 5.7.23, for Linux (x86_64)
--
-- Host: localhost    Database: accounts
-- ------------------------------------------------------
-- Server version	5.7.23-0ubuntu0.16.04.1

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
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,'BBI',1,1,'bbi',1,'2018-06-18 05:34:18','2018-06-18 05:34:20'),(2,'Prakash Arts',2,NULL,'prakash-arts',1,'2018-06-18 05:59:38','2018-06-18 05:59:38'),(3,'Ad Age Outdoor (P) Ltd',2,NULL,'ad-age-outdoor-(p)-ltd',1,'2018-06-18 06:01:44','2018-06-18 06:01:44'),(4,'Sudhakar Ad\'s Pvt Ltd',2,NULL,'sudhakar-ad\'s-pvt-ltd',1,'2018-06-18 06:02:55','2018-06-18 06:02:55'),(5,'MR.Publicities',2,NULL,'mr.publicities',1,'2018-06-18 06:03:53','2018-06-18 06:03:53'),(6,'Ad-space',2,NULL,'ad-space',1,'2018-06-18 06:05:00','2018-06-18 06:05:00'),(7,'RAINBOW ADVERTISING',2,NULL,'rainbow-advertising',1,'2018-06-18 06:06:24','2018-06-18 06:06:24'),(8,'Bhaskar Arts',2,NULL,'bhaskar-arts',1,'2018-06-18 06:07:51','2018-06-18 06:07:51'),(9,'Impressions Outdoor Advertising',2,NULL,'impressions-outdoor-advertising',1,'2018-06-18 06:09:26','2018-06-18 06:09:26'),(10,'Vision Outdoors',2,NULL,'vision-outdoors',1,'2018-06-18 06:10:16','2018-06-18 06:10:16'),(11,'SreeVarma',2,NULL,'sreevarma',1,'2018-06-18 06:11:08','2018-06-18 06:11:08'),(12,'Frontline Advertisers',2,NULL,'frontline-advertisers',1,'2018-06-18 06:12:12','2018-06-18 06:12:12'),(13,'SRIVEN ADS',2,NULL,'sriven-ads',1,'2018-06-18 06:13:12','2018-06-18 06:13:12'),(14,'LEADSPACE',2,NULL,'leadspace',1,'2018-06-18 06:13:59','2018-06-18 06:13:59'),(15,'Colors Outdoor advertising',2,NULL,'colors-outdoor-advertising',1,'2018-06-18 06:15:15','2018-06-18 06:15:15'),(16,'Roshan Adz',2,NULL,'roshan-adz',1,'2018-06-18 06:15:56','2018-06-18 06:15:56'),(17,'Fore-sites',2,NULL,'fore-sites',1,'2018-06-18 06:16:38','2018-06-18 06:16:38'),(18,'In and Out Advertising',2,NULL,'in-and-out-advertising',1,'2018-06-18 06:17:20','2018-06-18 06:17:20'),(19,'OUTSPACE ADVERTISING',2,NULL,'outspace-advertising',1,'2018-06-18 06:18:05','2018-06-18 06:18:05'),(20,'Narayana Cine Arts',2,NULL,'narayana-cine-arts',1,'2018-06-18 06:18:58','2018-06-18 06:18:58'),(21,'OUTDOOR MEDIA SOLUTIONS',2,NULL,'outdoor-media-solutions',1,'2018-06-18 06:19:48','2018-06-18 06:19:48'),(22,'Adlike Outdoor media',2,NULL,'adlike-outdoor-media',1,'2018-06-18 06:20:27','2018-06-18 06:20:27'),(23,'Brand vision Advertising',2,NULL,'brand-vision-advertising',1,'2018-06-18 06:21:05','2018-06-18 06:21:05'),(24,'billboards',2,2,'billboards',1,'2018-06-20 04:15:02','2018-06-20 04:15:02'),(25,'testowner-01',2,7,'testowner-01',1,'2018-06-25 00:43:23','2018-06-25 00:43:23'),(29,'bills',2,12,'bills',1,'2018-06-26 00:04:42','2018-06-26 00:04:42'),(37,'addspace',2,14,'addspace',1,'2018-06-26 00:23:44','2018-06-26 00:23:44'),(42,'adspaceowners',2,15,'adspaceowners',1,'2018-06-26 02:54:22','2018-06-26 02:54:22'),(44,'mahendra',2,16,'mahendra',1,'2018-06-27 05:13:08','2018-06-27 05:13:08'),(47,'Tech mahendra',2,63,'tech-mahendra',1,'2018-06-27 05:19:28','2018-08-24 03:05:21'),(50,'Delloitt',2,18,'delloitt',1,'2018-06-27 12:01:19','2018-06-27 12:01:19'),(51,'RGMCET',2,19,'rgmcet',1,'2018-06-28 01:10:40','2018-06-28 01:10:40'),(52,'shanthiram',2,NULL,'shanthiram',1,'2018-06-28 01:42:48','2018-06-28 01:42:48'),(55,'TNPS',2,20,'tnps',1,'2018-06-28 01:44:37','2018-06-28 01:44:37'),(56,'TNJS',2,21,'tnjs',1,'2018-06-28 01:50:05','2018-06-28 01:50:05'),(57,'Raos',2,22,'raos',1,'2018-06-28 02:05:51','2018-06-28 02:05:51'),(58,'tata',2,23,'tata',1,'2018-06-28 02:27:50','2018-06-28 02:27:50'),(59,'Chand',2,24,'chand',1,'2018-06-28 06:02:57','2018-06-28 06:02:58'),(60,'google',2,25,'google',1,'2018-06-29 01:29:16','2018-06-29 01:29:16'),(61,'YAHOO',3,26,'yahoo',1,'2018-06-29 01:30:09','2018-06-29 01:30:09'),(62,'xuv',2,27,'xuv',1,'2018-06-29 04:36:16','2018-06-29 04:36:16'),(63,'anjali',2,28,'anjali',1,'2018-06-29 04:46:15','2018-06-29 04:47:45'),(64,'fhg',2,29,'fhg',1,'2018-06-29 05:34:16','2018-06-29 05:34:16'),(65,'baskar ads',3,NULL,'baskar-ads',1,'2018-07-10 02:02:44','2018-07-10 02:02:44'),(66,'djahsgdasd',2,NULL,'djahsgdasd',1,'2018-07-11 01:47:59','2018-07-11 01:47:59'),(67,'broadband',2,37,'broadband',1,'2018-07-11 01:49:05','2018-07-11 01:49:05'),(68,'baskaradds',2,40,'baskaradds',1,'2018-07-12 06:40:55','2018-07-12 06:40:55'),(69,'hemanth',3,NULL,'hemanth',1,'2018-08-06 00:24:39','2018-08-06 00:24:39'),(70,'srilakshmi',3,NULL,'srilakshmi',1,'2018-08-08 23:29:05','2018-08-08 23:29:05'),(71,'bhavan',2,NULL,'bhavan',1,'2018-08-08 23:36:00','2018-08-08 23:36:00'),(72,'Adarsh Pasagadugula',2,NULL,'adarsh-pasagadugula',1,'2018-08-08 23:37:19','2018-08-08 23:37:19'),(73,'NeoN Software Solutions',2,NULL,'neon-software-solutions',1,'2018-08-09 01:01:36','2018-08-09 01:01:36'),(74,'EYE Treat',2,53,'eye-treat',1,'2018-08-09 01:03:35','2018-08-09 01:03:35'),(75,'people tech',2,NULL,'people-tech',1,'2018-08-09 08:45:55','2018-08-09 08:45:55'),(76,'siriadds',2,NULL,'siriadds',1,'2018-08-09 23:30:32','2018-08-09 23:30:32'),(77,'vasuadds',2,54,'vasuadds',1,'2018-08-09 23:37:44','2018-08-09 23:37:44'),(78,'vaibhav adds',2,55,'vaibhav-adds',1,'2018-08-09 23:44:44','2018-08-09 23:44:44'),(79,'billssss',2,NULL,'billssss',1,'2018-08-10 00:10:14','2018-08-10 00:10:14'),(80,'uniqone',2,56,'uniqone',1,'2018-08-10 00:10:49','2018-08-10 00:10:49'),(81,'billboardsasasa',2,NULL,'billboardsasasa',1,'2018-08-10 00:13:56','2018-08-10 00:13:56'),(82,'itsuniq',2,58,'itsuniq',1,'2018-08-10 00:14:14','2018-08-10 00:14:14'),(83,'billssasas',2,NULL,'billssasas',1,'2018-08-10 00:17:19','2018-08-10 00:17:19'),(84,'klkskjnkjn',2,59,'klkskjnkjn',1,'2018-08-10 00:17:40','2018-08-10 00:17:40'),(85,'techmahendra',2,NULL,'techmahendra',1,'2018-08-10 00:22:06','2018-08-10 00:22:06'),(86,'newclient',2,60,'newclient',1,'2018-08-10 00:22:46','2018-08-10 00:22:46'),(87,'uniads',3,NULL,'uniads',1,'2018-08-20 03:50:57','2018-08-20 03:50:57');
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
INSERT INTO `permission_role` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,18),(1,19),(1,20),(1,21),(1,22),(1,23),(1,24),(1,25),(1,26),(1,27),(1,28),(1,29),(1,30),(1,31),(1,32),(1,33),(1,34),(1,35),(1,36),(1,37),(1,38),(1,39),(1,40),(1,41),(1,42),(1,43),(1,44),(1,45),(1,46),(1,47),(1,48),(1,49),(1,50),(1,51),(1,52),(1,53),(1,54),(1,55),(1,56),(1,57),(1,58),(1,59),(1,60),(1,61),(1,62),(1,63),(1,64),(1,65),(1,66),(1,67),(1,68),(1,69),(1,70),(1,71),(1,72),(1,73),(1,74),(1,75),(1,76),(1,77),(1,78),(1,79),(1,80),(1,81),(1,82),(1,83),(1,84),(1,85),(1,86),(1,87),(1,88),(1,89),(1,90),(1,91),(1,92),(1,93),(1,94),(1,95),(1,96),(1,97),(1,98),(1,99),(1,100),(1,101),(1,102),(1,103),(1,104),(1,105),(1,106),(1,107),(1,108),(1,109),(1,110),(1,111),(1,112),(1,113),(1,114),(1,115),(1,116),(1,117),(1,118),(1,119),(1,120),(1,121),(1,122),(1,123),(1,124),(1,125),(1,126),(1,127),(1,128),(1,129),(1,130),(1,131),(1,132),(1,133),(1,134),(1,135),(1,136),(1,137),(2,1),(2,2),(2,3),(2,4),(2,5),(2,6),(2,7),(2,11),(2,13),(2,14),(2,15),(2,17),(2,18),(2,22),(2,23),(2,27),(2,28),(2,31),(2,32),(2,33),(2,34),(2,35),(2,36),(2,37),(2,38),(2,39),(2,40),(2,41),(2,42),(2,43),(2,44),(2,45),(2,46),(2,47),(2,50),(2,51),(2,54),(2,56),(2,58),(2,62),(2,64),(2,65),(2,66),(2,67),(2,69),(2,70),(2,71),(2,72),(2,73),(2,74),(2,75),(2,76),(2,77),(2,78),(2,80),(2,81),(2,83),(2,84),(2,85),(2,86),(2,88),(2,89),(2,90),(2,91),(2,94),(2,95),(2,96),(2,98),(2,100),(2,101),(2,102),(2,122),(2,124),(2,126),(2,127),(2,128),(2,129),(2,131),(2,132),(2,133),(2,134),(3,3),(3,4),(3,5),(3,6),(3,11),(3,23),(3,24),(3,25),(3,26),(3,56),(3,62),(3,65),(3,66),(3,69),(3,80),(3,83),(3,87),(3,88),(3,92),(3,97),(3,100),(3,101),(3,117),(3,119),(4,1),(4,2),(4,3),(4,4),(4,5),(4,6),(4,7),(4,8),(4,9),(4,10),(4,11),(4,12),(4,13),(4,14),(4,15),(4,16),(4,17),(4,18),(4,19),(4,20),(4,21),(4,22),(4,23),(4,24),(4,25),(4,26),(4,27),(4,28),(4,29),(4,30),(4,31),(4,32),(4,33),(4,34),(4,35),(4,36),(4,37),(4,38),(4,39),(4,40),(4,41),(4,42),(4,43),(4,44),(4,45),(4,46),(4,47),(4,48),(4,49),(4,50),(4,51),(4,52),(4,53),(4,54),(4,55),(4,56),(4,57),(4,58),(4,59),(4,60),(4,61),(4,62),(4,63),(4,64),(4,65),(4,66),(4,67),(4,68),(4,69),(4,70),(4,71),(4,72),(4,73),(4,74),(4,75),(4,76),(4,77),(4,78),(4,79),(4,80),(4,81),(4,82),(4,83),(4,84),(4,85),(4,86),(4,87),(4,88),(4,89),(4,90),(4,91),(4,92),(4,93),(4,94),(4,96),(4,97),(4,98),(4,102),(4,103),(4,104),(5,1),(5,2),(5,3),(5,4),(5,5),(5,6),(5,7),(5,8),(5,9),(5,10),(5,11),(5,12),(5,13),(5,14),(5,15),(5,16),(5,17),(5,18),(5,19),(5,20),(5,21),(5,22),(5,23),(5,24),(5,25),(5,26),(5,27),(5,28),(5,29),(5,30),(5,31),(5,32),(5,33),(5,34),(5,35),(5,36),(5,37),(5,38),(5,39),(5,40),(5,41),(5,42),(5,43),(5,44),(5,45),(5,46),(5,47),(5,48),(5,49),(5,50),(5,51),(5,52),(5,53),(5,54),(5,55),(5,56),(5,57),(5,58),(5,59),(5,60),(5,61),(5,62),(5,63),(5,64),(5,65),(5,66),(5,67),(5,68),(5,69),(5,70),(5,71),(5,72),(5,73),(5,74),(5,75),(5,76),(5,77),(5,78),(5,79),(5,80),(5,81),(5,82),(5,83),(5,84),(5,85),(5,86),(5,87),(5,88),(5,89),(5,90),(5,91),(5,92),(5,93),(5,94),(5,95),(5,96),(5,97),(5,98),(5,99),(5,100),(5,101),(5,102),(5,103),(5,104),(5,105),(5,106),(5,107),(5,108),(5,109),(5,110),(5,111),(5,112),(5,113),(5,114),(5,115),(5,116),(5,117),(5,118),(5,119),(6,1),(6,2),(6,3),(6,4),(6,5),(6,6),(6,7),(6,8),(6,9),(6,10),(6,11),(6,12),(6,13),(6,14),(6,15),(6,16),(6,17),(6,18),(6,19),(6,20),(6,21),(6,22),(6,23),(6,24),(6,25),(6,26),(6,27),(6,28),(6,29),(6,30),(6,31),(6,32),(6,33),(6,34),(6,35),(6,36),(6,37),(6,38),(6,39),(6,40),(6,41),(6,42),(6,43),(6,44),(6,45),(6,46),(6,47),(6,48),(6,49),(6,50),(6,51),(6,52),(6,53),(6,54),(6,55),(6,56),(6,57),(6,58),(6,59),(6,60),(6,61),(6,62),(6,63),(6,64),(6,65),(6,66),(6,67),(6,68),(6,69),(6,70),(6,71),(6,72),(6,73),(6,74),(6,75),(6,76),(6,77),(6,78),(6,79),(6,80),(6,81),(6,82),(6,83),(6,84),(6,85),(6,86),(6,87),(6,88),(6,89),(6,90),(6,91),(6,92),(6,93),(6,94),(6,95),(6,96),(6,97),(6,98),(6,99),(6,100),(6,101),(6,102),(6,103),(6,104),(6,105),(6,106),(6,107),(6,108),(6,109),(6,110),(6,111),(6,112),(6,113),(6,114),(6,115),(6,116),(6,117),(6,118),(6,119);
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
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'GET-countries',NULL,NULL,'2018-06-18 05:34:20','2018-06-18 05:34:20'),(2,'GET-states',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(3,'GET-cities',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(4,'GET-allCities',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(5,'GET-areas',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(6,'GET-allAreas',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(7,'POST-country',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(8,'DELETE-country',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(9,'POST-state',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(10,'DELETE-state',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(11,'POST-city',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(12,'DELETE-city',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(13,'POST-area',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(14,'DELETE-area',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(15,'GET-autocomplete-area',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(16,'GET-search-areas',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(17,'GET-products',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(18,'GET-map-products',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(19,'GET-product',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(20,'POST-product',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(21,'POST-products',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(22,'DELETE-product',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(23,'GET-approved-owner-products',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(24,'POST-request-owner-product-addition',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(25,'GET-requested-hoardings',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(26,'GET-requested-hoardings-for-owner',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(27,'GET-formats',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(28,'POST-format',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(29,'DELETE-format',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(30,'GET-search-products',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(31,'POST-filterProducts',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(32,'GET-shortlistedProducts',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(33,'POST-shortlistProduct',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(34,'DELETE-shortlistedProduct',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(35,'GET-searchBySiteNo',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(36,'POST-share-shortlisted',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(37,'POST-login',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(38,'GET-logout',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(39,'POST-userByAdmin',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(40,'POST-user',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(41,'GET-verify-email',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(42,'GET-user-profile',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(43,'POST-request-reset-password',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(44,'POST-reset-password',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(45,'GET-activate-user',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(46,'GET-user-permissions',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(47,'POST-change-password',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(48,'GET-switch-activation-user',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(49,'GET-delete-user',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(50,'POST-update-profile-pic',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(51,'POST-complete-registration',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(52,'GET-system-roles',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(53,'GET-system-permissions',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(54,'GET-users',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(55,'GET-role-details',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(56,'GET-all-clients',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(57,'POST-role',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(58,'GET-user-details-with-roles',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(59,'POST-set-su-for-client',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(60,'POST-set-permissions-for-role',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(61,'POST-set-roles-for-user',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(62,'GET-agencies',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(63,'POST-agencyByAdmin',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(64,'GET-companies',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(65,'GET-client-types',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(66,'POST-client',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(67,'GET-check-pwd-generation',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(68,'POST-resend-owner-invite',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(69,'GET-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(70,'GET-user-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(71,'GET-active-user-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(72,'POST-product-to-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(73,'POST-suggestion-request',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(74,'GET-export-all-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(75,'GET-request-proposal',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(76,'GET-request-campaign-launch',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(77,'POST-request-quote-change',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(78,'GET-get-all-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(79,'POST-propose-product-for-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(80,'GET-all-campaign-requests',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(81,'GET-campaign-suggestion-request-details',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(82,'GET-launch-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(83,'GET-close-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(84,'POST-floating-campaign-pdf',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(85,'GET-campaign-payments',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(86,'POST-campaign-payment',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(87,'GET-owner-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(88,'GET-user-campaigns-for-owner',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(89,'GET-campaign-for-owner',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(90,'GET-user-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(91,'POST-user-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(92,'POST-non-user-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(93,'GET-quote-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(94,'PUT-proposed-product-for-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(95,'DELETE-user-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(96,'DELETE-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(97,'GET-non-user-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(98,'POST-share-campaign',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(99,'GET-search-campaigns',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(100,'GET-all-notifications',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(101,'GET-all-admin-notifications',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(102,'GET-update-notification-read',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(103,'POST-subscription',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(104,'POST-request-callback',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(105,'POST-user-query',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(106,'PUT-update-customer-data',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(107,'GET-customer-query',NULL,NULL,'2018-06-18 05:34:21','2018-06-18 05:34:21'),(108,'GET-owner-products-report',NULL,NULL,'2018-07-11 00:24:18','2018-07-11 00:24:18'),(109,'GET-owner-product-details',NULL,NULL,'2018-07-11 00:24:18','2018-07-11 00:24:18'),(110,'GET-search-owner-products',NULL,NULL,'2018-07-11 00:24:18','2018-07-11 00:24:18'),(111,'POST-invite-bbi-user',NULL,NULL,'2018-07-11 00:24:18','2018-07-11 00:24:18'),(112,'POST-company',NULL,NULL,'2018-07-11 00:24:18','2018-07-11 00:24:18'),(113,'GET-quote-change-request-history',NULL,NULL,'2018-07-11 00:24:18','2018-07-11 00:24:18'),(114,'GET-campaigns-with-payments-owner',NULL,NULL,'2018-07-11 00:24:18','2018-07-11 00:24:18'),(115,'GET-campaign-payment-details-owner',NULL,NULL,'2018-07-11 00:24:18','2018-07-11 00:24:18'),(116,'POST-update-campaign-payment-owner',NULL,NULL,'2018-07-11 00:24:18','2018-07-11 00:24:18'),(117,'GET-owner-feeds',NULL,NULL,'2018-07-11 00:24:18','2018-07-11 00:24:18'),(118,'DELETE-non-user-campaign',NULL,NULL,'2018-07-11 00:24:18','2018-07-11 00:24:18'),(119,'GET-all-owner-notifications',NULL,NULL,'2018-07-11 00:24:18','2018-07-11 00:24:18'),(120,'GET-search-cities',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(121,'POST-metro-corridor',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(122,'GET-metro-corridors',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(123,'POST-metro-package',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(124,'GET-metro-packages',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(125,'GET-close-metro-campaigns',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(126,'DELETE-metro-campaign-product',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(127,'DELETE-metro-campaign',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(128,'POST-shortlist-metro-package',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(129,'GET-shortlisted-metro-packages',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(130,'DELETE-shortlisted-metro-package',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(131,'POST-metro-campaign',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(132,'GET-metro-campaigns',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(133,'GET-checkout-metro-campaign',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(134,'GET-metro-campaign',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(135,'POST-update-metro-campaigns-status',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(136,'GET-launch-metro-campaign',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35'),(137,'POST-package-to-metro-campaign',NULL,NULL,'2018-08-26 13:49:35','2018-08-26 13:49:35');
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
INSERT INTO `role_user` VALUES (1,1),(2,2),(3,2),(2,3),(2,4),(3,4),(4,4),(5,4),(2,5),(2,6),(2,7),(5,7),(2,8),(2,9),(3,9),(4,9),(5,9),(2,10),(2,11),(2,12),(2,13),(2,14),(2,15),(2,16),(2,17),(2,18),(2,19),(4,19),(2,20),(2,21),(2,22),(2,23),(2,24),(2,25),(2,26),(4,26),(2,27),(3,27),(2,28),(2,29),(2,30),(3,30),(2,31),(2,32),(2,33),(2,34),(2,35),(2,36),(2,37),(2,38),(2,39),(2,40),(2,42),(2,43),(2,44),(2,45),(2,46),(2,47),(2,48),(2,49),(2,50),(2,51),(2,52),(2,53),(3,53),(4,53),(5,53),(2,54),(2,55),(3,55),(4,55),(2,56),(2,57),(2,58),(2,59),(2,60),(2,61),(2,62),(2,63);
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,1,'super_admin',NULL,'The ulitmate user of application. Has every permission that can exist in the application','2018-06-18 05:34:20','2018-06-18 05:34:20'),(2,1,'basic_user','Basic User','The most basic user of application. Has the permissions only related to viewing products, locations, profile etc.','2018-06-18 05:34:20','2018-06-18 05:34:20'),(3,1,'owner','Ad Space Owner','Owner of ad spaces. May have inventory shared with Billboards India.','2018-06-18 05:34:20','2018-06-18 05:34:20'),(4,1,'testowner','testowner','Owner for testing','2018-06-25 01:34:13','2018-06-25 01:34:13'),(5,1,'srilakshmi','srilakshmi','testing owner','2018-07-02 03:56:44','2018-07-02 03:56:44'),(6,1,'sudesh_swaroop','sudesh swaroop','marketing executive.','2018-08-13 04:18:50','2018-08-13 04:18:50'),(7,1,'anroop','anroop','software eng','2018-08-13 04:53:53','2018-08-13 04:53:53');
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
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'bbadmin','mridulkashyap57@gmail.com','921aab40aa6f5cb672786a4767672cb1','bb94213',1,'2018-06-18 05:34:20','2018-06-18 05:34:20'),(2,24,NULL,'siri.vulupala@gmail.com','875140dc9cea857ed118876c5402e1b4','E9jMWfl',1,'2018-06-20 04:15:02','2018-06-20 23:41:03'),(3,NULL,NULL,'bhavan@billboardsindia.com','6f220fa987c51fb0932fb030efe4bce6','Wv1EYCe',0,'2018-06-20 05:42:55','2018-06-20 05:42:55'),(4,NULL,NULL,'anjalikhurana11@gmail.com','45771179554a88b4198d6d824d443aa4','07Vd3cA',1,'2018-06-21 05:59:47','2018-06-21 06:02:36'),(5,NULL,NULL,'srikanthbijjala1990@gmail.com','98fc12d01b2306e8a21ea8d83d7925cc','cMu7mP9',1,'2018-06-21 23:16:26','2018-06-21 23:18:49'),(6,NULL,NULL,'bhavanch007@gmail.com','4af5c7c721f28a671a67423f534ca803','Wiibblh',0,'2018-06-22 00:53:50','2018-06-22 00:53:50'),(7,25,NULL,'srikanthbijjala0709@gmail.com','ac700671966d6046f866d112938af040','iUh21Rh',1,'2018-06-25 00:43:23','2018-07-30 04:11:37'),(8,NULL,NULL,'testuser1@grr.la','134472178d9dd29433ff488426fe10d9','tkUwPpY',1,'2018-06-25 00:54:10','2018-06-25 00:54:42'),(9,NULL,NULL,'hemanth.ednith@gmail.com','7aa495fc2e90f88a4ad557c3770da5e9','ijXb1MW',1,'2018-06-25 23:41:40','2018-06-27 06:40:58'),(10,NULL,NULL,'hemanthrai47@gmail.com','0fd320190f1a19d8dcf0fe5c1da7b22e','CkFD5Vl',1,'2018-06-25 23:55:20','2018-06-27 06:28:33'),(11,NULL,NULL,'hemanth@gmail.com','d65f3c01befc73d241ead91a884dd798','7efZ5ZM',1,'2018-06-26 00:02:14','2018-06-27 06:41:02'),(12,29,NULL,'bills@gmail.com','8805965ecf101d0e4ba521198fd3ed97','Ilrw7vr',0,'2018-06-26 00:04:42','2018-06-26 00:04:42'),(13,NULL,NULL,'HEMANTH47@GMAIL.COM','2ac10951036fb89fd013f37087ed7890','2Zc7XGm',0,'2018-06-26 00:21:14','2018-06-26 00:21:14'),(14,37,NULL,'tetingclient@gmail.com','fed4827dd1cbbf30ec2cb4f5c1c84617','bvkFU3B',1,'2018-06-26 00:23:44','2018-06-29 04:32:10'),(15,42,NULL,'hem@gmail.com','d00b7b0cf9e4a76be02c5ada44cad1e1','b9DoOf6',0,'2018-06-26 02:54:22','2018-06-26 02:54:22'),(16,44,NULL,'testOwner@gmail.com','cc4378daf375e97f46bf6f36d1f6c578','O3cMaIX',0,'2018-06-27 05:13:08','2018-06-27 05:13:08'),(17,NULL,NULL,'hemanth57@gmail.com','cb2d2a8be2ca5d89d4337b0d3049fff3','sK8HTpX',0,'2018-06-27 06:38:40','2018-06-27 06:38:40'),(18,50,NULL,'sukumar587@gmail.com','681651e98a9873dcdf35dae92475ae80','rdx2cQN',0,'2018-06-27 12:01:19','2018-06-27 12:01:19'),(19,51,NULL,'raghuram@gmail.com','3221db00c75b8974adf4fabd162f67e3','W8IgNwO',1,'2018-06-28 01:10:40','2018-07-01 23:48:36'),(20,55,NULL,'shanthiram@gmail.com','b1a858c6918d9c984d6e5d88e07a9aaf','KFIiuIa',0,'2018-06-28 01:44:37','2018-06-28 01:44:37'),(21,56,NULL,'yaswanth@gmail.com','97ba7d414d5c87cf4740cd5d7338b4b7','xomj8rQ',0,'2018-06-28 01:50:05','2018-06-28 01:50:05'),(22,57,NULL,'srinivas@gmail.com','e55dee8e794dfe2f95ec994e8db9d498','EgbmCN9',0,'2018-06-28 02:05:51','2018-06-28 02:05:51'),(23,58,NULL,'tata@gmail.com','37428aa0b5b2d220b92cd8b425776555','v74fiBh',0,'2018-06-28 02:27:50','2018-06-28 02:27:50'),(24,59,NULL,'chand375@gmail.com','8992b7ed08f590b081a3efac084538fd','GtGnaGr',1,'2018-06-28 06:02:58','2018-07-02 00:27:46'),(25,60,NULL,'google@gmail.com','5aada49b1a552feebef0261892504de3','mZS3KKn',1,'2018-06-29 01:29:16','2018-06-29 04:28:28'),(26,61,NULL,'YAHOO@gmail.com','7948660ca2317458d9ac295821a0d6a7','NECK8fv',1,'2018-06-29 01:30:09','2018-06-29 04:27:51'),(27,62,NULL,'xuv@gmail.com','b6ba029938eab70941aabfc84f3679ff','FMNlZQm',1,'2018-06-29 04:36:16','2018-06-29 04:43:43'),(28,63,NULL,'anjali@gmail.com','','Haxkuri',0,'2018-06-29 04:47:37','2018-06-29 04:47:37'),(29,64,NULL,'fdg@ff.hh','dd629da950e92ef55bdff8e5e6a442a7','ifOVamT',1,'2018-06-29 05:34:16','2018-07-02 03:56:23'),(30,NULL,NULL,'sayalis13@gmail.com','d954b8eb5e6dbcf1121e9fe1ef1d0069','4AmekG5',1,'2018-07-02 01:50:10','2018-07-02 01:53:06'),(31,NULL,NULL,'vishnupriya1994@gmail.com','e8a2dd84c54180a1defaa05fe0b8dc52','Qq8XAhf',0,'2018-07-03 06:07:28','2018-07-03 06:07:28'),(32,NULL,NULL,'srilakshmi.vulupala@ptgindia.com','b2d43275661d092b8fdb913cc1852d78','0ZMmMA0',0,'2018-07-11 01:09:28','2018-07-16 01:45:29'),(33,NULL,NULL,'deepthijagadeesh.dummu@gmail.com','f9e93284261252160d063fda67242675','Jl61biQ',1,'2018-07-11 01:12:49','2018-07-13 04:11:43'),(34,NULL,NULL,'hghgv@gmail.com','219c2f3656a67463a500f793f78b8a50','1pPYCLm',0,'2018-07-11 01:24:31','2018-07-11 01:24:31'),(35,NULL,NULL,'lakshmii@gmail.com','3903fbac4af14eb146b1ff4716e7898c','iYr27jc',0,'2018-07-11 01:28:12','2018-07-11 01:28:12'),(36,NULL,NULL,'asdadad@gmail.com','b233706b9fcdec16d156a19008eccbdb','2u2usFc',0,'2018-07-11 01:41:56','2018-07-11 01:41:56'),(37,67,NULL,'hemanthrai57@gmail.com','355a525b80b59b3ef3edff1bbcb030e9','N6HZJaD',0,'2018-07-11 01:49:05','2018-07-11 01:49:05'),(38,NULL,NULL,'sivasankar.web@gmail.com','9590e840934ff05c5f929e1e2a76928c','IKNfLXC',0,'2018-07-11 02:00:14','2018-08-13 00:51:50'),(39,NULL,NULL,'deepthi1394.dummu@gmail.com','f87bbc2bf77a3686890a73da3c3100ea','FROqYSS',0,'2018-07-12 06:38:41','2018-07-12 06:38:41'),(40,68,NULL,'saritha.vulupala@gmail.com','8142bf0cdbeed7cfb1f8832d78cdd329','r0o1jj3',0,'2018-07-12 06:40:55','2018-07-12 06:40:55'),(42,NULL,NULL,'gollapudivishnupriya@gmail.com','83e5713201af58b4e14fa3c6040fcbc6','wMD2xru',0,'2018-07-16 04:59:47','2018-07-16 04:59:47'),(43,NULL,NULL,'vaishnavikeerthi0817@gmail.com','38bc40857476f11d1ffdf5d7f261cd6d','dRhaXBE',1,'2018-07-16 05:03:17','2018-07-16 05:04:38'),(44,NULL,NULL,'banu.patan@ptgindia.com','34aac7393b663915d5dd2e2449df0f23','RhHcw3W',1,'2018-07-16 05:13:38','2018-07-16 05:14:21'),(45,NULL,NULL,'chandudilshan@gmail.com','08879adc09f651580eae1532171892a2','7ACyF1P',1,'2018-07-17 00:42:01','2018-07-17 00:44:49'),(46,NULL,NULL,'uthejvemula7@gmail.com','aecfd4f91142739961c4935fbe0f901c','BKkfNSF',1,'2018-07-20 03:55:36','2018-07-20 03:57:36'),(47,NULL,NULL,'gopi.paidepally@ptgindia.com','7f39fc210bd52833d2eccdd4e0084d08','u90f8v9',1,'2018-07-30 00:36:31','2018-07-30 00:37:30'),(48,NULL,NULL,'adarsh.2498@gmail.com','ae96aacba047ce1126f85786466aedd8','pAUpXp4',1,'2018-07-30 01:01:11','2018-07-30 01:03:20'),(49,NULL,NULL,'mysoorareddy@billboardsindia.com','43b232cb099d8825029f127890f7ce5a','nDKr6EJ',1,'2018-07-30 01:56:25','2018-07-31 00:27:39'),(50,NULL,NULL,'dileep@billboardsindia.com','8f939ab03b5d7a582e843ab865604210','nckbWS5',1,'2018-08-03 01:43:03','2018-08-03 01:44:23'),(51,NULL,NULL,'sudesh@ptgindia.com','ee1a45f431aca4f8792ad1a0d8b99a88','o3pzSK0',0,'2018-08-03 01:46:02','2018-08-03 01:46:02'),(52,NULL,NULL,'akashchittari1816@gmail.com','d815548aa45465e9aa424d416cef0fd1','hxEcDiL',1,'2018-08-06 01:41:46','2018-08-06 01:43:02'),(53,74,NULL,'chanikyavarma7@gmail.com','4dc0c3d8b0626964eb714f637c1fae3c','snrOzFb',1,'2018-08-09 01:03:35','2018-08-09 01:37:40'),(54,77,NULL,'vasudev.baggi@ptgindia.com','9a6132d50873a85160037d591cb744eb','EGFNOro',0,'2018-08-09 23:37:44','2018-08-09 23:51:38'),(55,78,NULL,'srikanth.bijjala@ptgindia.com','e3dabebad3a371437083a4a8956ad391','BGJhHns',1,'2018-08-09 23:44:44','2018-08-10 00:37:33'),(56,80,NULL,'hemanth.edni@gmail.com','0d3e6da44526649aa974b3c3238b4c47','AZMWmWv',0,'2018-08-10 00:10:49','2018-08-10 00:10:49'),(57,NULL,NULL,'mridulkashyap572@gmail.com','57ed111697dce3a5a3c586c0f378b4f1','KnfZFNg',0,'2018-08-10 00:13:33','2018-08-10 00:13:33'),(58,82,NULL,'hemanth.asasa@gmail.com','2c4d21bbcfbf4cab89f3e52a50f9d140','LadfEax',0,'2018-08-10 00:14:14','2018-08-10 00:14:14'),(59,84,NULL,'sasas.sasasa@gmail.com','5802a0df559f480903e1ef9049a9ba87','eKcfD68',0,'2018-08-10 00:17:40','2018-08-10 00:17:40'),(60,86,NULL,'uniqqfortest@gmail.com','ba5fa09a4f60444b086ba06e5e2de3b6','k0bzxT6',0,'2018-08-10 00:22:46','2018-08-10 00:22:46'),(61,NULL,NULL,'sudesh@billboardsindia.com','fd6cd33a5a86ff3983ce65857561afdd','eCnzF4m',1,'2018-08-13 04:14:11','2018-08-13 04:16:58'),(62,NULL,NULL,'anroopvullampathi@gmail.com','f54191bdbc50df45ebdb398383dc6936','YCJ0axP',1,'2018-08-13 04:50:50','2018-08-13 04:53:34'),(63,47,NULL,'user1@grr.la','','bF8l1Aa',0,'2018-08-24 03:05:18','2018-08-24 03:05:18');
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

-- Dump completed on 2018-08-29 13:01:44
