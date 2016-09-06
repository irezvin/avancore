-- MySQL dump 10.16  Distrib 10.1.16-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: avancore_test
-- ------------------------------------------------------
-- Server version	10.1.16-MariaDB

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
INSERT INTO `ac_album_photos` VALUES (3,1,1),(3,2,1),(3,1,2);
/*!40000 ALTER TABLE `ac_album_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_cpk`
--

DROP TABLE IF EXISTS `ac_cpk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_cpk` (
  `foo` int(11) NOT NULL,
  `bar` int(11) NOT NULL,
  `baz` int(11) DEFAULT NULL,
  PRIMARY KEY (`foo`,`bar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_cpk`
--

LOCK TABLES `ac_cpk` WRITE;
/*!40000 ALTER TABLE `ac_cpk` DISABLE KEYS */;
/*!40000 ALTER TABLE `ac_cpk` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_people`
--

LOCK TABLES `ac_people` WRITE;
/*!40000 ALTER TABLE `ac_people` DISABLE KEYS */;
INSERT INTO `ac_people` VALUES (3,'Илья','M',0,'1982-04-11',NULL,'2014-08-10 19:24:57',4,1),(4,'Таня','F',0,'1981-12-23',NULL,'2014-08-10 19:25:10',1,3),(6,'Ян','M',1,'1981-09-21',NULL,'2014-08-31 13:03:07',4,NULL),(7,'Оля','F',1,'1981-09-08',NULL,'2014-08-31 13:04:00',1,NULL),(8,'test author','F',0,'1990-01-01',NULL,'0000-00-00 00:00:00',NULL,NULL),(9,'test editor','F',0,'1990-02-02',NULL,'0000-00-00 00:00:00',NULL,NULL),(10,'test prod author','M',0,'2015-02-05',NULL,'0000-00-00 00:00:00',NULL,NULL),(11,'test prod author 2','F',0,'2015-02-05',NULL,'0000-00-00 00:00:00',NULL,NULL),(12,'Test author of a note','M',0,'2014-02-14',NULL,'0000-00-00 00:00:00',NULL,NULL),(13,'testPerson','M',0,'2014-11-07',NULL,'0000-00-00 00:00:00',5,NULL);
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
INSERT INTO `ac_people_tags` VALUES (4,1),(4,2),(6,1),(6,3),(7,2),(13,5),(13,6);
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
  CONSTRAINT `FK__ac_post_person` FOREIGN KEY (`personId`) REFERENCES `ac_people` (`personId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK__ac_post_photo` FOREIGN KEY (`personId`, `photoId`) REFERENCES `ac_person_photos` (`personId`, `photoId`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `fkPostPublish` FOREIGN KEY (`pubId`) REFERENCES `ac_publish` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_person_posts`
--

LOCK TABLES `ac_person_posts` WRITE;
/*!40000 ALTER TABLE `ac_person_posts` DISABLE KEYS */;
INSERT INTO `ac_person_posts` VALUES (1,3,1,'Post 1 by Ilya','The text 1',NULL),(2,3,NULL,'Post 2 by Ilya (no photo)','No photo this time',118),(3,4,3,'Post by Tanya','Hello, world!',117);
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
) ENGINE=InnoDB AUTO_INCREMENT=380 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_publish`
--

LOCK TABLES `ac_publish` WRITE;
/*!40000 ALTER TABLE `ac_publish` DISABLE KEYS */;
INSERT INTO `ac_publish` VALUES (1,'Sample_Shop_Product_Mapper',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',3,6,'123456','2014-11-20 01:22:31','2014-12-20 01:22:31','0000-00-00 00:00:00'),(117,'Sample_Person_Post_Mapper',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',3,7,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(118,'Sample_Person_Post_Mapper',0,1,'2012-01-01 00:00:00','2013-01-01 00:00:00',3,3,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(190,'Sample_Shop_Product_Mapper',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(191,'Sample_Shop_Product_Mapper',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(192,'Sample_Shop_Product_Mapper',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(193,'Sample_Shop_Product_Mapper',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(376,'Sample_Shop_Product_Mapper',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',8,9,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(377,'Sample_Shop_Product_Mapper',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(378,'Sample_Shop_Product_Mapper',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(379,'Sample_Shop_Product_Mapper',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00');
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
-- Table structure for table `ac_shop_classifier`
--

DROP TABLE IF EXISTS `ac_shop_classifier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_shop_classifier` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(16) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_title` (`type`,`title`),
  CONSTRAINT `fkClassifierType` FOREIGN KEY (`type`) REFERENCES `ac_shop_classifier_type` (`type`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_classifier`
--

LOCK TABLES `ac_shop_classifier` WRITE;
/*!40000 ALTER TABLE `ac_shop_classifier` DISABLE KEYS */;
INSERT INTO `ac_shop_classifier` VALUES (3,'ELD','Matrix'),(1,'LCD','Matrix'),(2,'LED','Matrix'),(6,'OLED','Matrix'),(4,'PDP','Matrix'),(5,'QLED','Matrix');
/*!40000 ALTER TABLE `ac_shop_classifier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_shop_classifier_type`
--

DROP TABLE IF EXISTS `ac_shop_classifier_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_shop_classifier_type` (
  `type` varchar(16) NOT NULL,
  PRIMARY KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_classifier_type`
--

LOCK TABLES `ac_shop_classifier_type` WRITE;
/*!40000 ALTER TABLE `ac_shop_classifier_type` DISABLE KEYS */;
INSERT INTO `ac_shop_classifier_type` VALUES ('Matrix');
/*!40000 ALTER TABLE `ac_shop_classifier_type` ENABLE KEYS */;
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
  `sharedObjectType` varchar(255) NOT NULL DEFAULT 'other',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=420 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_meta`
--

LOCK TABLES `ac_shop_meta` WRITE;
/*!40000 ALTER TABLE `ac_shop_meta` DISABLE KEYS */;
INSERT INTO `ac_shop_meta` VALUES (1,'Купить Товар 1 он-лайн','Страница товара 1','Товар 1, купить',0,'product'),(43,'','','',0,'product'),(230,'','','',0,'product'),(231,'','','',0,'product'),(232,'','','',0,'product'),(233,'','','',0,'product'),(416,'','','',0,'product'),(417,'','','',0,'product'),(418,'','','',0,'product'),(419,'','','',0,'product');
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
  CONSTRAINT `fkProductCategoryCategory` FOREIGN KEY (`categoryId`) REFERENCES `ac_shop_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fkProductCategoryProduct` FOREIGN KEY (`productId`) REFERENCES `ac_shop_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
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
INSERT INTO `ac_shop_product_extraCodes` VALUES (2,'1','2','3',3),(158,'','','',NULL),(159,'A','B','C',10),(160,'A1','B1','C1',11),(161,'','','',NULL);
/*!40000 ALTER TABLE `ac_shop_product_extraCodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_shop_product_notes`
--

DROP TABLE IF EXISTS `ac_shop_product_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_shop_product_notes` (
  `productId` int(11) unsigned NOT NULL,
  `note` text NOT NULL,
  `noteAuthorId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`productId`),
  KEY `fkProductNoteAuthor` (`noteAuthorId`),
  CONSTRAINT `fkProductNoteAuthor` FOREIGN KEY (`noteAuthorId`) REFERENCES `ac_people` (`personId`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fkProductNoteProduct` FOREIGN KEY (`productId`) REFERENCES `ac_shop_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_product_notes`
--

LOCK TABLES `ac_shop_product_notes` WRITE;
/*!40000 ALTER TABLE `ac_shop_product_notes` DISABLE KEYS */;
INSERT INTO `ac_shop_product_notes` VALUES (1,'xxx',3),(158,'',NULL),(159,'',NULL),(160,'',NULL),(161,'foobar',12);
/*!40000 ALTER TABLE `ac_shop_product_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_shop_product_related`
--

DROP TABLE IF EXISTS `ac_shop_product_related`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_shop_product_related` (
  `productId` int(10) unsigned NOT NULL,
  `relatedProductId` int(10) unsigned NOT NULL,
  `ignore` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`productId`,`relatedProductId`),
  KEY `ignore` (`ignore`),
  KEY `relatedProductReferencing` (`relatedProductId`),
  CONSTRAINT `relatedProductReferenced` FOREIGN KEY (`productId`) REFERENCES `ac_shop_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `relatedProductReferencing` FOREIGN KEY (`relatedProductId`) REFERENCES `ac_shop_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_product_related`
--

LOCK TABLES `ac_shop_product_related` WRITE;
/*!40000 ALTER TABLE `ac_shop_product_related` DISABLE KEYS */;
INSERT INTO `ac_shop_product_related` VALUES (1,2,0),(1,3,0),(2,4,0),(1,4,1),(2,1,1),(2,3,1),(4,1,1),(4,2,1),(4,3,1);
/*!40000 ALTER TABLE `ac_shop_product_related` ENABLE KEYS */;
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
INSERT INTO `ac_shop_product_upc` VALUES (1,'1234'),(158,''),(159,''),(160,''),(161,'');
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
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_products`
--

LOCK TABLES `ac_shop_products` WRITE;
/*!40000 ALTER TABLE `ac_shop_products` DISABLE KEYS */;
INSERT INTO `ac_shop_products` VALUES (1,'PROD01','Товар 1',1,1),(2,'PROD02','Товар 2',NULL,NULL),(3,'PROD03','Товар 3',NULL,NULL),(4,'PROD04','Товар 4',NULL,NULL),(9,'xxx','yyy',NULL,NULL),(10,'mc-food1','Food 1',NULL,NULL),(11,'mc-computer1','Computer 1',NULL,NULL),(12,'mc-laptop1','Laptop 1',NULL,NULL),(13,'mc-laptop2','Laptop 2',NULL,NULL),(158,'1337','test product',416,376),(159,'f00','test prod 2',417,377),(160,'f01','test prod 3',418,378),(161,'PROD_NOTE','product with a note',419,379);
/*!40000 ALTER TABLE `ac_shop_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_shop_spec_computer`
--

DROP TABLE IF EXISTS `ac_shop_spec_computer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_shop_spec_computer` (
  `productId` int(10) unsigned NOT NULL,
  `hdd` int(10) unsigned NOT NULL,
  `ram` int(10) unsigned NOT NULL,
  `os` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`productId`),
  CONSTRAINT `fkSpecsComputer` FOREIGN KEY (`productId`) REFERENCES `ac_shop_specs` (`productId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_spec_computer`
--

LOCK TABLES `ac_shop_spec_computer` WRITE;
/*!40000 ALTER TABLE `ac_shop_spec_computer` DISABLE KEYS */;
INSERT INTO `ac_shop_spec_computer` VALUES (11,1024,16,'Ubuntu Linux'),(12,512,8,'Windows 10'),(13,256,8,'Arch Linux');
/*!40000 ALTER TABLE `ac_shop_spec_computer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_shop_spec_food`
--

DROP TABLE IF EXISTS `ac_shop_spec_food`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_shop_spec_food` (
  `productId` int(10) unsigned NOT NULL,
  `storageType` enum('shelfStable','frozen','refrigerated') DEFAULT 'shelfStable',
  `storageTerm` int(3) unsigned NOT NULL DEFAULT '0',
  `storageTermUnit` enum('days','months','years') NOT NULL DEFAULT 'days',
  PRIMARY KEY (`productId`),
  CONSTRAINT `fkSpecsFood` FOREIGN KEY (`productId`) REFERENCES `ac_shop_specs` (`productId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_spec_food`
--

LOCK TABLES `ac_shop_spec_food` WRITE;
/*!40000 ALTER TABLE `ac_shop_spec_food` DISABLE KEYS */;
INSERT INTO `ac_shop_spec_food` VALUES (10,'frozen',6,'months');
/*!40000 ALTER TABLE `ac_shop_spec_food` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_shop_spec_laptop`
--

DROP TABLE IF EXISTS `ac_shop_spec_laptop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_shop_spec_laptop` (
  `productId` int(10) unsigned NOT NULL,
  `weight` decimal(3,1) unsigned NOT NULL,
  `battery` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`productId`),
  CONSTRAINT `fkSpecLaptopProduct` FOREIGN KEY (`productId`) REFERENCES `ac_shop_specs` (`productId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_spec_laptop`
--

LOCK TABLES `ac_shop_spec_laptop` WRITE;
/*!40000 ALTER TABLE `ac_shop_spec_laptop` DISABLE KEYS */;
INSERT INTO `ac_shop_spec_laptop` VALUES (12,2.0,'6'),(13,3.0,'4');
/*!40000 ALTER TABLE `ac_shop_spec_laptop` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_shop_spec_monitor`
--

DROP TABLE IF EXISTS `ac_shop_spec_monitor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_shop_spec_monitor` (
  `productId` int(10) unsigned NOT NULL,
  `diagonal` decimal(4,1) unsigned NOT NULL,
  `hRes` int(5) unsigned NOT NULL,
  `vRes` int(5) unsigned NOT NULL,
  `matrixTypeId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`productId`),
  KEY `fkMonitorMatrixType` (`matrixTypeId`),
  CONSTRAINT `fkMonitorMatrixType` FOREIGN KEY (`matrixTypeId`) REFERENCES `ac_shop_classifier` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fkMonitorSpec` FOREIGN KEY (`productId`) REFERENCES `ac_shop_specs` (`productId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_spec_monitor`
--

LOCK TABLES `ac_shop_spec_monitor` WRITE;
/*!40000 ALTER TABLE `ac_shop_spec_monitor` DISABLE KEYS */;
INSERT INTO `ac_shop_spec_monitor` VALUES (12,15.0,1280,768,6),(13,17.0,1680,1050,5);
/*!40000 ALTER TABLE `ac_shop_spec_monitor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ac_shop_specs`
--

DROP TABLE IF EXISTS `ac_shop_specs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_shop_specs` (
  `productId` int(10) unsigned NOT NULL,
  `detailsUrl` varchar(255) NOT NULL DEFAULT '',
  `specsType` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`productId`),
  KEY `specsType` (`specsType`),
  CONSTRAINT `specsProduct` FOREIGN KEY (`productId`) REFERENCES `ac_shop_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_shop_specs`
--

LOCK TABLES `ac_shop_specs` WRITE;
/*!40000 ALTER TABLE `ac_shop_specs` DISABLE KEYS */;
INSERT INTO `ac_shop_specs` VALUES (10,'http://www.example.com/food1','Sample_Shop_Spec_Mapper_Food'),(11,'http://www.example.com/computer1','Sample_Shop_Spec_Mapper_Computer'),(12,'http://www.example.com/laptop1','Sample_Shop_Spec_Mapper_Laptop'),(13,'http://www.example.com/laptop2','Sample_Shop_Spec_Mapper_Laptop');
/*!40000 ALTER TABLE `ac_shop_specs` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_tree_combos`
--

LOCK TABLES `ac_tree_combos` WRITE;
/*!40000 ALTER TABLE `ac_tree_combos` DISABLE KEYS */;
INSERT INTO `ac_tree_combos` VALUES (1,0,15,NULL,1,'root',999,0,0),(2,1,10,1,1,'A',NULL,0,1),(3,2,7,2,1,'A.1',NULL,0,2),(4,10,3,2,3,'A.2',NULL,0,2);
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
INSERT INTO `ac_tree_nested_sets` VALUES (0,1,0,9,NULL,1,'Sample_Tree_Record_Mapper',0,0),(1,1,1,8,0,1,'',0,1),(2,1,2,7,2,1,'',0,2),(3,1,3,4,3,2,'',0,3),(4,1,5,6,4,2,'',0,3);
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_tree_records`
--

LOCK TABLES `ac_tree_records` WRITE;
/*!40000 ALTER TABLE `ac_tree_records` DISABLE KEYS */;
INSERT INTO `ac_tree_records` VALUES (1,'-= top =-',NULL),(2,'A',NULL),(3,'A.1',NULL),(4,'A.2',NULL);
/*!40000 ALTER TABLE `ac_tree_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `im_template`
--

DROP TABLE IF EXISTS `im_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `importId` int(10) unsigned NOT NULL,
  `lineNo` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `im_template`
--

LOCK TABLES `im_template` WRITE;
/*!40000 ALTER TABLE `im_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `im_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `im_test_aipk`
--

DROP TABLE IF EXISTS `im_test_aipk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_test_aipk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` varchar(45) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `im_test_aipk`
--

LOCK TABLES `im_test_aipk` WRITE;
/*!40000 ALTER TABLE `im_test_aipk` DISABLE KEYS */;
/*!40000 ALTER TABLE `im_test_aipk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `im_test_aipk_import`
--

DROP TABLE IF EXISTS `im_test_aipk_import`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_test_aipk_import` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `importId` int(11) NOT NULL DEFAULT '1',
  `itemId` int(11) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `content` varchar(45) DEFAULT NULL,
  `otherContent` varchar(45) DEFAULT NULL,
  `isDraft` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `im_test_aipk_import`
--

LOCK TABLES `im_test_aipk_import` WRITE;
/*!40000 ALTER TABLE `im_test_aipk_import` DISABLE KEYS */;
/*!40000 ALTER TABLE `im_test_aipk_import` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `im_test_aipk_linked`
--

DROP TABLE IF EXISTS `im_test_aipk_linked`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_test_aipk_linked` (
  `masterId` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `otherContent` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`masterId`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `im_test_aipk_linked`
--

LOCK TABLES `im_test_aipk_linked` WRITE;
/*!40000 ALTER TABLE `im_test_aipk_linked` DISABLE KEYS */;
/*!40000 ALTER TABLE `im_test_aipk_linked` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `im_test_categories`
--

DROP TABLE IF EXISTS `im_test_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_test_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `parentId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxTitle` (`name`),
  KEY `fk_im_test_categories_1_idx` (`parentId`),
  CONSTRAINT `fk_im_test_categories_1` FOREIGN KEY (`parentId`) REFERENCES `im_test_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `im_test_categories`
--

LOCK TABLES `im_test_categories` WRITE;
/*!40000 ALTER TABLE `im_test_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `im_test_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `im_test_categories_import`
--

DROP TABLE IF EXISTS `im_test_categories_import`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_test_categories_import` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `categoryId` int(10) DEFAULT NULL,
  `categoryName` varchar(45) DEFAULT NULL,
  `description` mediumtext,
  `parentId` int(10) DEFAULT NULL,
  `parentName` varchar(45) DEFAULT NULL,
  `importStatus` varchar(45) NOT NULL DEFAULT 'unprocessed',
  `problems` varchar(45) DEFAULT NULL,
  `importId` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idxStatus` (`importStatus`),
  KEY `idxName` (`categoryName`),
  KEY `idxItemId` (`categoryId`),
  KEY `idxTypeId` (`parentId`),
  KEY `idxType` (`parentName`),
  KEY `idxImportId` (`importId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `im_test_categories_import`
--

LOCK TABLES `im_test_categories_import` WRITE;
/*!40000 ALTER TABLE `im_test_categories_import` DISABLE KEYS */;
/*!40000 ALTER TABLE `im_test_categories_import` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `im_test_classifiers_import`
--

DROP TABLE IF EXISTS `im_test_classifiers_import`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_test_classifiers_import` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `classifierId` int(10) DEFAULT NULL,
  `title` varchar(45) DEFAULT NULL,
  `description` varchar(45) DEFAULT NULL,
  `classifierType` varchar(45) NOT NULL DEFAULT '',
  `importStatus` varchar(45) NOT NULL DEFAULT 'unprocessed',
  `problems` varchar(45) DEFAULT NULL,
  `importId` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idxStatus` (`importStatus`),
  KEY `idxName` (`title`),
  KEY `idxItemId` (`classifierId`),
  KEY `idxType` (`classifierType`),
  KEY `idxImportId` (`importId`)
) ENGINE=InnoDB AUTO_INCREMENT=614 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `im_test_classifiers_import`
--

LOCK TABLES `im_test_classifiers_import` WRITE;
/*!40000 ALTER TABLE `im_test_classifiers_import` DISABLE KEYS */;
INSERT INTO `im_test_classifiers_import` VALUES (612,1,'typeC',NULL,'type','unchanged',NULL,1),(613,2,'typeD',NULL,'type','unchanged',NULL,1);
/*!40000 ALTER TABLE `im_test_classifiers_import` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `im_test_item_categories`
--

DROP TABLE IF EXISTS `im_test_item_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_test_item_categories` (
  `itemId` int(10) unsigned NOT NULL,
  `categoryId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`itemId`,`categoryId`),
  KEY `fk_im_test_item_categories_1_idx` (`categoryId`),
  KEY `fk_im_test_item_categories_1_idx1` (`itemId`),
  CONSTRAINT `categoryId` FOREIGN KEY (`categoryId`) REFERENCES `im_test_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `itemId` FOREIGN KEY (`itemId`) REFERENCES `im_test_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `im_test_item_categories`
--

LOCK TABLES `im_test_item_categories` WRITE;
/*!40000 ALTER TABLE `im_test_item_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `im_test_item_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `im_test_item_categories_import`
--

DROP TABLE IF EXISTS `im_test_item_categories_import`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_test_item_categories_import` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `categoryId` int(10) DEFAULT NULL,
  `categoryName` mediumtext,
  `importStatus` varchar(45) NOT NULL DEFAULT 'unprocessed',
  `problems` varchar(45) DEFAULT NULL,
  `importId` int(10) NOT NULL,
  `lineNo` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idxStatus` (`importStatus`),
  KEY `idxName` (`categoryId`),
  KEY `idxLineNo` (`lineNo`),
  KEY `idxImportId` (`importId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `im_test_item_categories_import`
--

LOCK TABLES `im_test_item_categories_import` WRITE;
/*!40000 ALTER TABLE `im_test_item_categories_import` DISABLE KEYS */;
/*!40000 ALTER TABLE `im_test_item_categories_import` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `im_test_items`
--

DROP TABLE IF EXISTS `im_test_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_test_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `description` mediumtext,
  `imageFileName` varchar(255) DEFAULT NULL,
  `thumbFileName` varchar(255) DEFAULT NULL,
  `typeId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxName` (`name`),
  KEY `fk_im_test_items_1_idx` (`typeId`),
  CONSTRAINT `fk_im_test_items_1` FOREIGN KEY (`typeId`) REFERENCES `im_test_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `im_test_items`
--

LOCK TABLES `im_test_items` WRITE;
/*!40000 ALTER TABLE `im_test_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `im_test_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `im_test_items_import`
--

DROP TABLE IF EXISTS `im_test_items_import`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_test_items_import` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemId` int(10) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `description` mediumtext,
  `typeId` int(10) DEFAULT NULL,
  `type` varchar(45) DEFAULT NULL,
  `importStatus` varchar(45) NOT NULL DEFAULT 'unprocessed',
  `problems` varchar(45) DEFAULT NULL,
  `relatedText1` mediumtext,
  `pictureUrl` varchar(255) DEFAULT NULL,
  `importId` int(10) NOT NULL,
  `lineNo` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idxStatus` (`importStatus`),
  KEY `idxName` (`name`),
  KEY `idxItemId` (`itemId`),
  KEY `idxTypeId` (`typeId`),
  KEY `idxType` (`type`),
  KEY `idxLineNo` (`lineNo`),
  KEY `idxImportId` (`importId`),
  KEY `idxPictureUrl` (`pictureUrl`)
) ENGINE=InnoDB AUTO_INCREMENT=401 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `im_test_items_import`
--

LOCK TABLES `im_test_items_import` WRITE;
/*!40000 ALTER TABLE `im_test_items_import` DISABLE KEYS */;
INSERT INTO `im_test_items_import` VALUES (399,NULL,'item1',NULL,1,'typeC','unprocessed',NULL,NULL,NULL,1,8),(400,NULL,'item2',NULL,2,'typeD','unprocessed',NULL,NULL,NULL,1,9);
/*!40000 ALTER TABLE `im_test_items_import` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `im_test_pictures_import`
--

DROP TABLE IF EXISTS `im_test_pictures_import`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_test_pictures_import` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `importId` int(10) NOT NULL,
  `importStatus` varchar(45) NOT NULL DEFAULT 'unprocessed',
  `problems` varchar(45) DEFAULT NULL,
  `pictureUrl` varchar(255) DEFAULT NULL,
  `pictureFileName` varchar(255) DEFAULT NULL,
  `thumbFileName` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idxStatus` (`importStatus`),
  KEY `idxImportId` (`importId`),
  KEY `idxPictureUrl` (`pictureUrl`),
  KEY `idxPictureFileName` (`pictureFileName`),
  KEY `idxThumbFileName` (`thumbFileName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `im_test_pictures_import`
--

LOCK TABLES `im_test_pictures_import` WRITE;
/*!40000 ALTER TABLE `im_test_pictures_import` DISABLE KEYS */;
/*!40000 ALTER TABLE `im_test_pictures_import` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `im_test_related`
--

DROP TABLE IF EXISTS `im_test_related`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_test_related` (
  `itemId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `relatedText1` mediumtext NOT NULL,
  PRIMARY KEY (`itemId`),
  KEY `fk_im_test_related_1_idx` (`itemId`),
  CONSTRAINT `fk_im_test_related_1` FOREIGN KEY (`itemId`) REFERENCES `im_test_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `im_test_related`
--

LOCK TABLES `im_test_related` WRITE;
/*!40000 ALTER TABLE `im_test_related` DISABLE KEYS */;
/*!40000 ALTER TABLE `im_test_related` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `im_test_source_of_copy`
--

DROP TABLE IF EXISTS `im_test_source_of_copy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_test_source_of_copy` (
  `id` varchar(45) NOT NULL,
  `name` varchar(45) NOT NULL DEFAULT '',
  `description` varchar(45) NOT NULL DEFAULT '',
  `thisWillBeRelatedText` varchar(45) NOT NULL DEFAULT '',
  `somethingElse` varchar(45) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `im_test_source_of_copy`
--

LOCK TABLES `im_test_source_of_copy` WRITE;
/*!40000 ALTER TABLE `im_test_source_of_copy` DISABLE KEYS */;
INSERT INTO `im_test_source_of_copy` VALUES ('a','aaa','aaa description','aaa related','aaa else'),('b','bbb','bbb description','bbb related','bbb else');
/*!40000 ALTER TABLE `im_test_source_of_copy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `im_test_types`
--

DROP TABLE IF EXISTS `im_test_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_test_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxTitle` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `im_test_types`
--

LOCK TABLES `im_test_types` WRITE;
/*!40000 ALTER TABLE `im_test_types` DISABLE KEYS */;
INSERT INTO `im_test_types` VALUES (1,'typeC'),(2,'typeD');
/*!40000 ALTER TABLE `im_test_types` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-09-07  0:33:14
