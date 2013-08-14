-- MySQL dump 10.13  Distrib 5.5.30, for Linux (i686)
--
-- Host: localhost    Database: avancore_test
-- ------------------------------------------------------
-- Server version	5.5.30-log

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
-- Table structure for table `ac_orientation`
--

DROP TABLE IF EXISTS `ac_orientation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_orientation` (
  `sexualOrientationId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(45) NOT NULL,
  PRIMARY KEY (`sexualOrientationId`),
  KEY `Index_2` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_orientation`
--

LOCK TABLES `ac_orientation` WRITE;
/*!40000 ALTER TABLE `ac_orientation` DISABLE KEYS */;
INSERT INTO `ac_orientation` VALUES (2,'Бисексуальная'),(3,'Гомосексуальная'),(1,'Натуральная');
/*!40000 ALTER TABLE `ac_orientation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_people`
--

DROP TABLE IF EXISTS `ac_people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_people` (
  `personId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `gender` enum('F','M') NOT NULL DEFAULT 'F',
  `isSingle` int(1) unsigned NOT NULL DEFAULT '1',
  `birthDate` date NOT NULL,
  `lastUpdatedDatetime` datetime DEFAULT NULL,
  `createdTs` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sexualOrientationId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`personId`),
  KEY `FK_ac_people_1` (`sexualOrientationId`),
  CONSTRAINT `FK_ac_people_1` FOREIGN KEY (`sexualOrientationId`) REFERENCES `ac_orientation` (`sexualOrientationId`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_people`
--

LOCK TABLES `ac_people` WRITE;
/*!40000 ALTER TABLE `ac_people` DISABLE KEYS */;
INSERT INTO `ac_people` VALUES (3,'Илья','M',0,'1982-04-11',NULL,'2009-12-22 16:22:36',1),(4,'Таня','F',0,'1981-12-23',NULL,'2009-12-22 16:22:36',1);
/*!40000 ALTER TABLE `ac_people` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_people_tags`
--

DROP TABLE IF EXISTS `ac_people_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_people_tags` (
  `personId` int(10) unsigned NOT NULL,
  `tagId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`personId`,`tagId`),
  KEY `FK_ac_people_tags_2` (`tagId`),
  CONSTRAINT `FK_ac_people_tags_1` FOREIGN KEY (`personId`) REFERENCES `ac_people` (`personId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ac_people_tags_2` FOREIGN KEY (`tagId`) REFERENCES `ac_tags` (`tagId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_people_tags`
--

LOCK TABLES `ac_people_tags` WRITE;
/*!40000 ALTER TABLE `ac_people_tags` DISABLE KEYS */;
INSERT INTO `ac_people_tags` VALUES (4,1),(4,2);
/*!40000 ALTER TABLE `ac_people_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_relation_types`
--

DROP TABLE IF EXISTS `ac_relation_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_relation_types` (
  `relationTypeId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(45) NOT NULL,
  `isSymmetrical` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`relationTypeId`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_relation_types`
--

LOCK TABLES `ac_relation_types` WRITE;
/*!40000 ALTER TABLE `ac_relation_types` DISABLE KEYS */;
INSERT INTO `ac_relation_types` VALUES (1,'Супруги',1),(2,'Сексуальные партнеры',1);
/*!40000 ALTER TABLE `ac_relation_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_relations`
--

DROP TABLE IF EXISTS `ac_relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_relations` (
  `relationId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `personId` int(10) unsigned NOT NULL,
  `otherPersonId` int(10) unsigned NOT NULL,
  `relationTypeId` int(10) unsigned NOT NULL,
  `relationBegin` datetime DEFAULT NULL,
  `relationEnd` datetime DEFAULT NULL,
  `notes` text NOT NULL,
  PRIMARY KEY (`relationId`),
  KEY `FK_ac_relations_3` (`relationTypeId`),
  KEY `FK_ac_relations_outgoing` (`personId`),
  KEY `FK_ac_relations_incoming` (`otherPersonId`),
  CONSTRAINT `FK_ac_relations_3` FOREIGN KEY (`relationTypeId`) REFERENCES `ac_relation_types` (`relationTypeId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ac_relations_incoming` FOREIGN KEY (`otherPersonId`) REFERENCES `ac_people` (`personId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ac_relations_outgoing` FOREIGN KEY (`personId`) REFERENCES `ac_people` (`personId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_relations`
--

LOCK TABLES `ac_relations` WRITE;
/*!40000 ALTER TABLE `ac_relations` DISABLE KEYS */;
INSERT INTO `ac_relations` VALUES (1,3,4,1,'2004-04-15 00:00:00',NULL,'Счастливый (с переменным успехом) брак'),(2,4,3,2,'2001-01-13 00:00:00',NULL,'Счастливый (с переменным успехом) секс :))');
/*!40000 ALTER TABLE `ac_relations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_tags`
--

DROP TABLE IF EXISTS `ac_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_tags` (
  `tagId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(45) NOT NULL,
  `titleM` varchar(45) DEFAULT NULL,
  `titleF` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`tagId`),
  UNIQUE KEY `Index_2` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_tags`
--

LOCK TABLES `ac_tags` WRITE;
/*!40000 ALTER TABLE `ac_tags` DISABLE KEYS */;
INSERT INTO `ac_tags` VALUES (1,'Ум','Умный','Умница'),(2,'Красота','Красивый','Красавица');
/*!40000 ALTER TABLE `ac_tags` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-08-14  0:36:25
