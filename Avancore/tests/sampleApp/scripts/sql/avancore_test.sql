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
-- Table structure for table `ac_album_photos`
--

DROP TABLE IF EXISTS `ac_album_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_album_photos` (
  `personId` int(10) unsigned NOT NULL,
  `albumId` int(10) unsigned NOT NULL,
  `photoId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`photoId`,`albumId`,`personId`),
  KEY `FK__ac_person_albums` (`personId`,`albumId`),
  KEY `FK__ac_person_photos` (`personId`,`photoId`),
  CONSTRAINT `FK__ac_person_albums` FOREIGN KEY (`personId`, `albumId`) REFERENCES `ac_person_albums` (`personId`, `albumId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK__ac_person_photos` FOREIGN KEY (`personId`, `photoId`) REFERENCES `ac_person_photos` (`personId`, `photoId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_album_photos`
--

LOCK TABLES `ac_album_photos` WRITE;
/*!40000 ALTER TABLE `ac_album_photos` DISABLE KEYS */;
INSERT INTO `ac_album_photos` VALUES (3,1,1),(3,1,2),(3,2,1);
/*!40000 ALTER TABLE `ac_album_photos` ENABLE KEYS */;
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
  `religionId` int(10) unsigned DEFAULT NULL,
  `portraitId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`personId`),
  KEY `FK_ac_people_1` (`religionId`),
  KEY `FK_ac_person_photos_ac_people_protrait` (`personId`,`portraitId`),
  CONSTRAINT `FK_ac_person_photos_ac_people_portrait` FOREIGN KEY (`personId`, `portraitId`) REFERENCES `ac_person_photos` (`personId`, `photoId`),
  CONSTRAINT `FK_ac_person_religion` FOREIGN KEY (`religionId`) REFERENCES `ac_religion` (`religionId`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_people`
--

LOCK TABLES `ac_people` WRITE;
/*!40000 ALTER TABLE `ac_people` DISABLE KEYS */;
INSERT INTO `ac_people` VALUES (3,'Илья','M',0,'1982-04-11',NULL,'2014-08-10 19:24:57',4,1),(4,'Таня','F',0,'1981-12-23',NULL,'2014-08-10 19:25:10',1,3),(6,'Ян','M',1,'1981-09-21',NULL,'2014-08-31 13:03:07',4,NULL),(7,'Оля','F',1,'1981-09-08',NULL,'2014-08-31 13:04:00',1,NULL),(8,'test author','F',0,'1990-01-01',NULL,'0000-00-00 00:00:00',NULL,NULL),(9,'test editor','F',0,'1990-02-02',NULL,'0000-00-00 00:00:00',NULL,NULL),(10,'test prod author','M',0,'2015-02-05',NULL,'0000-00-00 00:00:00',NULL,NULL),(11,'test prod author 2','F',0,'2015-02-05',NULL,'0000-00-00 00:00:00',NULL,NULL),(12,'testPerson','M',0,'2014-11-07',NULL,'0000-00-00 00:00:00',5,NULL);
/*!40000 ALTER TABLE `ac_people` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_people_tags`
--

DROP TABLE IF EXISTS `ac_people_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_people_tags` (
  `idOfPerson` int(10) unsigned NOT NULL,
  `idOfTag` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idOfPerson`,`idOfTag`),
  KEY `FK_ac_people_tags_2` (`idOfTag`),
  CONSTRAINT `FK_ac_people_tags_1` FOREIGN KEY (`idOfPerson`) REFERENCES `ac_people` (`personId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ac_people_tags_2` FOREIGN KEY (`idOfTag`) REFERENCES `ac_tags` (`tagId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_people_tags`
--

LOCK TABLES `ac_people_tags` WRITE;
/*!40000 ALTER TABLE `ac_people_tags` DISABLE KEYS */;
INSERT INTO `ac_people_tags` VALUES (4,1),(6,1),(4,2),(7,2),(6,3),(12,5),(12,6);
/*!40000 ALTER TABLE `ac_people_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_perks`
--

DROP TABLE IF EXISTS `ac_perks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_perks` (
  `perkId` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8 DEFAULT '',
  PRIMARY KEY (`perkId`),
  KEY `uqName` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_perks`
--

LOCK TABLES `ac_perks` WRITE;
/*!40000 ALTER TABLE `ac_perks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ac_perks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_person_albums`
--

DROP TABLE IF EXISTS `ac_person_albums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_person_albums` (
  `albumId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `personId` int(10) unsigned NOT NULL DEFAULT '0',
  `albumName` varchar(255) NOT NULL DEFAULT '''''',
  PRIMARY KEY (`albumId`),
  KEY `FK__ac_people` (`personId`),
  CONSTRAINT `FK__ac_people` FOREIGN KEY (`personId`) REFERENCES `ac_people` (`personId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_person_albums`
--

LOCK TABLES `ac_person_albums` WRITE;
/*!40000 ALTER TABLE `ac_person_albums` DISABLE KEYS */;
INSERT INTO `ac_person_albums` VALUES (1,3,'personal'),(2,3,'all');
/*!40000 ALTER TABLE `ac_person_albums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_person_photos`
--

DROP TABLE IF EXISTS `ac_person_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_person_photos` (
  `photoId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `personId` int(10) unsigned NOT NULL,
  `filename` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`photoId`),
  KEY `FK_ac_person_photos_ac_people` (`personId`),
  CONSTRAINT `FK_ac_person_photos_ac_people` FOREIGN KEY (`personId`) REFERENCES `ac_people` (`personId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_person_photos`
--

LOCK TABLES `ac_person_photos` WRITE;
/*!40000 ALTER TABLE `ac_person_photos` DISABLE KEYS */;
INSERT INTO `ac_person_photos` VALUES (1,3,'ilya1.jpg'),(2,3,'ilya2.jpg'),(3,4,'tanya1.jpg');
/*!40000 ALTER TABLE `ac_person_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_person_posts`
--

DROP TABLE IF EXISTS `ac_person_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_person_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `personId` int(10) unsigned DEFAULT NULL,
  `photoId` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) DEFAULT '',
  `content` longtext,
  `pubId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxPubId` (`pubId`),
  KEY `personId` (`personId`),
  KEY `FK__ac_post_photo` (`personId`,`photoId`),
  CONSTRAINT `fkPostPublish` FOREIGN KEY (`pubId`) REFERENCES `ac_publish` (`id`),
  CONSTRAINT `FK__ac_post_person` FOREIGN KEY (`personId`) REFERENCES `ac_people` (`personId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK__ac_post_photo` FOREIGN KEY (`personId`, `photoId`) REFERENCES `ac_person_photos` (`personId`, `photoId`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_person_posts`
--

LOCK TABLES `ac_person_posts` WRITE;
/*!40000 ALTER TABLE `ac_person_posts` DISABLE KEYS */;
INSERT INTO `ac_person_posts` VALUES (1,3,1,'Post 1 by Ilya','The text 1',NULL),(2,3,NULL,'Post 2 by Ilya (no photo)','No photo this time',NULL),(3,4,3,'Post by Tanya','Hello, world!',NULL);
/*!40000 ALTER TABLE `ac_person_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_publish`
--

DROP TABLE IF EXISTS `ac_publish`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_publish` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sharedObjectType` varchar(50) NOT NULL,
  `published` int(1) unsigned DEFAULT '1',
  `deleted` int(1) unsigned DEFAULT '0',
  `publishUp` datetime DEFAULT '0000-00-00 00:00:00',
  `publishDown` datetime DEFAULT '0000-00-00 00:00:00',
  `authorId` int(10) unsigned DEFAULT NULL,
  `editorId` int(10) unsigned DEFAULT NULL,
  `pubChannelId` varchar(255) DEFAULT NULL,
  `dateCreated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dateModified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dateDeleted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxPubChannelId` (`pubChannelId`),
  KEY `sharedObjectType` (`sharedObjectType`),
  KEY `fkPubAuthor` (`authorId`),
  KEY `fkPubEditor` (`editorId`),
  CONSTRAINT `fkPubAuthor` FOREIGN KEY (`authorId`) REFERENCES `ac_people` (`personId`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `fkPubEditor` FOREIGN KEY (`editorId`) REFERENCES `ac_people` (`personId`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_publish`
--

LOCK TABLES `ac_publish` WRITE;
/*!40000 ALTER TABLE `ac_publish` DISABLE KEYS */;
INSERT INTO `ac_publish` VALUES (1,'product',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',3,6,'123456','2014-11-20 01:22:31','2014-12-20 01:22:31','0000-00-00 00:00:00'),(28,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(29,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(30,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(31,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(32,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(33,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(34,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(35,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(36,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(37,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(38,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(39,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(40,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(41,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(42,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(43,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(44,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(45,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(46,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(47,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(48,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(49,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(51,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(52,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(53,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(55,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(56,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(57,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(59,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(60,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(61,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(63,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(64,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(65,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(67,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',8,9,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(68,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(69,'',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `ac_publish` ENABLE KEYS */;
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
-- Table structure for table `ac_religion`
--

DROP TABLE IF EXISTS `ac_religion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_religion` (
  `religionId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(45) NOT NULL,
  PRIMARY KEY (`religionId`),
  UNIQUE KEY `Index_2` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_religion`
--

LOCK TABLES `ac_religion` WRITE;
/*!40000 ALTER TABLE `ac_religion` DISABLE KEYS */;
INSERT INTO `ac_religion` VALUES (4,'Agnostic'),(3,'Atheist'),(1,'Christian'),(2,'Muslim'),(5,'Pastafarian');
/*!40000 ALTER TABLE `ac_religion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_shop_categories`
--

DROP TABLE IF EXISTS `ac_shop_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_shop_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `leftCol` int(10) unsigned NOT NULL,
  `rightCol` int(10) unsigned NOT NULL,
  `ignore` int(10) unsigned NOT NULL,
  `parentId` int(10) unsigned DEFAULT NULL,
  `ordering` int(10) unsigned NOT NULL,
  `depth` int(10) unsigned NOT NULL,
  `metaId` int(10) unsigned DEFAULT NULL,
  `pubId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxPubId` (`pubId`),
  CONSTRAINT `fkCategoryPublish` FOREIGN KEY (`pubId`) REFERENCES `ac_publish` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_categories`
--

LOCK TABLES `ac_shop_categories` WRITE;
/*!40000 ALTER TABLE `ac_shop_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `ac_shop_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_shop_meta`
--

DROP TABLE IF EXISTS `ac_shop_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_shop_meta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pageTitle` varchar(255) DEFAULT NULL,
  `metaDescription` varchar(255) DEFAULT NULL,
  `metaKeywords` varchar(255) DEFAULT NULL,
  `metaNoindex` int(1) unsigned NOT NULL DEFAULT '0',
  `sharedObjectType` enum('product','category','other') NOT NULL DEFAULT 'other',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_meta`
--

LOCK TABLES `ac_shop_meta` WRITE;
/*!40000 ALTER TABLE `ac_shop_meta` DISABLE KEYS */;
INSERT INTO `ac_shop_meta` VALUES (1,'Купить Товар 1 он-лайн','Страница товара 1','Товар 1, купить',0,'product'),(3,'','','',0,'product'),(4,'','','',0,'product'),(5,'','','',0,'product'),(6,'','','',0,'product'),(7,'','','',0,'product'),(8,'','','',0,'product'),(10,'','','',0,'product'),(11,'','','',0,'product'),(12,'','','',0,'product'),(13,'','','',0,'product'),(14,'','','',0,'product'),(15,'','','',0,'product'),(16,'','','',0,'product'),(17,'','','',0,'product'),(18,'','','',0,'product'),(19,'','','',0,'product'),(20,'','','',0,'product'),(21,'','','',0,'product'),(22,'','','',0,'product'),(23,'','','',0,'product'),(24,'','','',0,'product'),(25,'','','',0,'product'),(27,'','','',0,'product'),(28,'','','',0,'product'),(29,'','','',0,'product'),(31,'','','',0,'product'),(32,'','','',0,'product'),(33,'','','',0,'product'),(35,'','','',0,'product'),(36,'','','',0,'product'),(37,'','','',0,'product'),(39,'','','',0,'product'),(40,'','','',0,'product'),(41,'','','',0,'product'),(43,'','','',0,'product'),(44,'','','',0,'product'),(45,'','','',0,'product');
/*!40000 ALTER TABLE `ac_shop_meta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_shop_product_categories`
--

DROP TABLE IF EXISTS `ac_shop_product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_shop_product_categories` (
  `productId` int(10) unsigned NOT NULL,
  `categoryId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`productId`,`categoryId`),
  KEY `fkProductCategoryCategory` (`categoryId`),
  CONSTRAINT `fkProductCategoryProduct` FOREIGN KEY (`productId`) REFERENCES `ac_shop_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fkProductCategoryCategory` FOREIGN KEY (`categoryId`) REFERENCES `ac_shop_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_product_categories`
--

LOCK TABLES `ac_shop_product_categories` WRITE;
/*!40000 ALTER TABLE `ac_shop_product_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `ac_shop_product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_shop_product_extraCodes`
--

DROP TABLE IF EXISTS `ac_shop_product_extraCodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_shop_product_extraCodes` (
  `productId` int(10) unsigned NOT NULL,
  `ean` varchar(255) NOT NULL DEFAULT '',
  `asin` varchar(255) NOT NULL DEFAULT '',
  `gtin` varchar(255) NOT NULL DEFAULT '',
  `responsiblePersonId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`productId`),
  KEY `fkExtraCodeResponsiblePerson` (`responsiblePersonId`),
  CONSTRAINT `fkExtraCodeProduct` FOREIGN KEY (`productId`) REFERENCES `ac_shop_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fkExtraCodeResponsiblePerson` FOREIGN KEY (`responsiblePersonId`) REFERENCES `ac_people` (`personId`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_product_extraCodes`
--

LOCK TABLES `ac_shop_product_extraCodes` WRITE;
/*!40000 ALTER TABLE `ac_shop_product_extraCodes` DISABLE KEYS */;
INSERT INTO `ac_shop_product_extraCodes` VALUES (2,'1','2','3',3),(37,'','','',NULL),(38,'A','B','C',10),(39,'A1','B1','C1',11);
/*!40000 ALTER TABLE `ac_shop_product_extraCodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_shop_product_upc`
--

DROP TABLE IF EXISTS `ac_shop_product_upc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_shop_product_upc` (
  `productId` int(10) unsigned NOT NULL,
  `upcCode` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`productId`),
  CONSTRAINT `fk_ac_product_upc_1` FOREIGN KEY (`productId`) REFERENCES `ac_shop_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_product_upc`
--

LOCK TABLES `ac_shop_product_upc` WRITE;
/*!40000 ALTER TABLE `ac_shop_product_upc` DISABLE KEYS */;
INSERT INTO `ac_shop_product_upc` VALUES (1,'1234'),(37,''),(38,''),(39,'');
/*!40000 ALTER TABLE `ac_shop_product_upc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_shop_products`
--

DROP TABLE IF EXISTS `ac_shop_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_shop_products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sku` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `metaId` int(10) unsigned DEFAULT NULL,
  `pubId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxPubId` (`pubId`),
  CONSTRAINT `fkPersonPublish` FOREIGN KEY (`pubId`) REFERENCES `ac_publish` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_products`
--

LOCK TABLES `ac_shop_products` WRITE;
/*!40000 ALTER TABLE `ac_shop_products` DISABLE KEYS */;
INSERT INTO `ac_shop_products` VALUES (1,'PROD01','Товар 1',1,1),(2,'PROD02','Товар 2',NULL,NULL),(37,'1337','test product',43,67),(38,'f00','test prod 2',44,68),(39,'f01','test prod 3',45,69);
/*!40000 ALTER TABLE `ac_shop_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_tag_perks`
--

DROP TABLE IF EXISTS `ac_tag_perks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_tag_perks` (
  `idOfTag` int(10) unsigned NOT NULL,
  `idOfPerk` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idOfTag`,`idOfPerk`),
  KEY `fkTagId_idx` (`idOfTag`),
  KEY `fkPerkId_idx` (`idOfPerk`),
  CONSTRAINT `fkPerkId` FOREIGN KEY (`idOfPerk`) REFERENCES `ac_perks` (`perkId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fkTagId` FOREIGN KEY (`idOfTag`) REFERENCES `ac_tags` (`tagId`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_tag_perks`
--

LOCK TABLES `ac_tag_perks` WRITE;
/*!40000 ALTER TABLE `ac_tag_perks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ac_tag_perks` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_tags`
--

LOCK TABLES `ac_tags` WRITE;
/*!40000 ALTER TABLE `ac_tags` DISABLE KEYS */;
INSERT INTO `ac_tags` VALUES (1,'Ум','Умный','Умница'),(2,'Красота','Красивый','Красавица'),(3,'Хитрость','Хитрюга','Хитрюга'),(4,'Богатство','Богач','Богачка'),(5,'The','The Guy','The Girl'),(6,'A','A Guy','A Girl');
/*!40000 ALTER TABLE `ac_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_tree_adjacent`
--

DROP TABLE IF EXISTS `ac_tree_adjacent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_tree_adjacent` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parentId` int(10) unsigned DEFAULT NULL,
  `ordering` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `tag` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_4` (`parentId`),
  KEY `index_5` (`ordering`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_tree_adjacent`
--

LOCK TABLES `ac_tree_adjacent` WRITE;
/*!40000 ALTER TABLE `ac_tree_adjacent` DISABLE KEYS */;
INSERT INTO `ac_tree_adjacent` VALUES (1,NULL,1,'-= top =-',NULL),(2,1,1,'A',NULL),(3,2,1,'A.1',NULL),(4,2,2,'A.2',NULL);
/*!40000 ALTER TABLE `ac_tree_adjacent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_tree_combos`
--

DROP TABLE IF EXISTS `ac_tree_combos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_tree_combos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `leftCol` int(10) unsigned NOT NULL DEFAULT '0',
  `rightCol` int(10) unsigned NOT NULL DEFAULT '1',
  `parentId` int(10) unsigned DEFAULT NULL,
  `ordering` int(10) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `tag` int(11) DEFAULT NULL,
  `ignore` int(1) unsigned NOT NULL DEFAULT '0',
  `depth` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `index_2` (`leftCol`),
  KEY `index_3` (`rightCol`),
  KEY `index_4` (`parentId`),
  KEY `index_5` (`ordering`),
  KEY `index_6` (`ignore`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_tree_combos`
--

LOCK TABLES `ac_tree_combos` WRITE;
/*!40000 ALTER TABLE `ac_tree_combos` DISABLE KEYS */;
INSERT INTO `ac_tree_combos` VALUES (1,0,17,NULL,1,'root',999,0,0),(2,1,12,1,1,'A',NULL,0,1),(3,2,7,2,1,'A.1',NULL,0,2),(4,10,11,2,3,'A.2',NULL,0,2);
/*!40000 ALTER TABLE `ac_tree_combos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_tree_nested_sets`
--

DROP TABLE IF EXISTS `ac_tree_nested_sets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_tree_nested_sets` (
  `id` int(10) unsigned NOT NULL,
  `treeId` int(10) unsigned NOT NULL,
  `leftCol` int(10) unsigned NOT NULL DEFAULT '0',
  `rightCol` int(10) unsigned NOT NULL DEFAULT '1',
  `parentId` int(10) unsigned DEFAULT NULL,
  `ordering` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(40) NOT NULL,
  `ignore` int(1) unsigned NOT NULL DEFAULT '0',
  `depth` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`treeId`),
  KEY `index_2` (`leftCol`),
  KEY `index_3` (`rightCol`),
  KEY `index_4` (`parentId`),
  KEY `index_5` (`ordering`),
  KEY `index_6` (`ignore`),
  KEY `index_7` (`id`),
  KEY `index_8` (`treeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_tree_nested_sets`
--

LOCK TABLES `ac_tree_nested_sets` WRITE;
/*!40000 ALTER TABLE `ac_tree_nested_sets` DISABLE KEYS */;
INSERT INTO `ac_tree_nested_sets` VALUES (0,1,0,9,NULL,1,'Sample_Tree_Record_Mapper',0,0),(1,1,1,8,0,1,'',0,1),(2,1,2,7,1,1,'',0,2),(3,1,3,4,2,1,'',0,3),(4,1,5,6,2,2,'',0,3);
/*!40000 ALTER TABLE `ac_tree_nested_sets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_tree_records`
--

DROP TABLE IF EXISTS `ac_tree_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_tree_records` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `tag` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_tree_records`
--

LOCK TABLES `ac_tree_records` WRITE;
/*!40000 ALTER TABLE `ac_tree_records` DISABLE KEYS */;
INSERT INTO `ac_tree_records` VALUES (1,'-= top =-',NULL),(2,'A',NULL),(3,'A.1',NULL),(4,'A.2',NULL);
/*!40000 ALTER TABLE `ac_tree_records` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-02-09  1:58:34
