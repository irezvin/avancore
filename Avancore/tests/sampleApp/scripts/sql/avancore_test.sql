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
  CONSTRAINT `FK_ac_person_photos_ac_people_protrait` FOREIGN KEY (`personId`, `portraitId`) REFERENCES `ac_person_photos` (`personId`, `photoId`),
  CONSTRAINT `FK_ac_person_religion` FOREIGN KEY (`religionId`) REFERENCES `ac_religion` (`religionId`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_people`
--

LOCK TABLES `ac_people` WRITE;
/*!40000 ALTER TABLE `ac_people` DISABLE KEYS */;
INSERT INTO `ac_people` VALUES (3,'Илья','M',0,'1982-04-11',NULL,'2014-08-10 19:24:57',4,1),(4,'Таня','F',0,'1981-12-23',NULL,'2014-08-10 19:25:10',1,3),(6,'Ян','M',1,'1981-09-21',NULL,'2014-08-31 13:03:07',4,NULL),(7,'Оля','F',1,'1981-09-08',NULL,'2014-08-31 13:04:00',1,NULL);
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
INSERT INTO `ac_people_tags` VALUES (4,1),(6,1),(4,2),(7,2),(6,3);
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
  PRIMARY KEY (`id`),
  KEY `personId` (`personId`),
  KEY `FK__ac_post_photo` (`personId`,`photoId`),
  CONSTRAINT `FK__ac_post_person` FOREIGN KEY (`personId`) REFERENCES `ac_people` (`personId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK__ac_post_photo` FOREIGN KEY (`personId`, `photoId`) REFERENCES `ac_person_photos` (`personId`, `photoId`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_person_posts`
--

LOCK TABLES `ac_person_posts` WRITE;
/*!40000 ALTER TABLE `ac_person_posts` DISABLE KEYS */;
INSERT INTO `ac_person_posts` VALUES (1,3,1,'Post 1 by Ilya','The text 1'),(2,3,NULL,'Post 2 by Ilya (no photo)','No photo this time'),(3,4,3,'Post by Tanya','Hello, world!');
/*!40000 ALTER TABLE `ac_person_posts` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_religion`
--

LOCK TABLES `ac_religion` WRITE;
/*!40000 ALTER TABLE `ac_religion` DISABLE KEYS */;
INSERT INTO `ac_religion` VALUES (4,'Agnostic'),(3,'Atheist'),(1,'Christian'),(2,'Muslim');
/*!40000 ALTER TABLE `ac_religion` ENABLE KEYS */;
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
  CONSTRAINT `fkTagId` FOREIGN KEY (`idOfTag`) REFERENCES `ac_tags` (`tagId`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fkPerkId` FOREIGN KEY (`idOfPerk`) REFERENCES `ac_perks` (`perkId`) ON DELETE CASCADE ON UPDATE CASCADE
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_tags`
--

LOCK TABLES `ac_tags` WRITE;
/*!40000 ALTER TABLE `ac_tags` DISABLE KEYS */;
INSERT INTO `ac_tags` VALUES (1,'Ум','Умный','Умница'),(2,'Красота','Красивый','Красавица'),(3,'Хитрость','Хитрюга','Хитрюга'),(4,'Богатство','Богач','Богачка');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_tree_adjacent`
--

LOCK TABLES `ac_tree_adjacent` WRITE;
/*!40000 ALTER TABLE `ac_tree_adjacent` DISABLE KEYS */;
INSERT INTO `ac_tree_adjacent` VALUES (1,NULL,1,'child1',1),(2,NULL,3,'child2',2),(3,NULL,2,'child3',3);
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_tree_combos`
--

LOCK TABLES `ac_tree_combos` WRITE;
/*!40000 ALTER TABLE `ac_tree_combos` DISABLE KEYS */;
INSERT INTO `ac_tree_combos` VALUES (1,0,3,NULL,1,'root',999,0,0),(2,1,2,1,1,'child1',1,0,1);
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
INSERT INTO `ac_tree_nested_sets` VALUES (0,1,0,7,NULL,1,'Sample_Tree_Record_Mapper',0,0),(1,1,1,2,0,1,'',0,1),(2,1,5,6,0,3,'',0,1),(3,1,3,4,0,2,'',0,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ac_tree_records`
--

LOCK TABLES `ac_tree_records` WRITE;
/*!40000 ALTER TABLE `ac_tree_records` DISABLE KEYS */;
INSERT INTO `ac_tree_records` VALUES (1,'child1',1),(2,'child2',2),(3,'child3',3);
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

-- Dump completed on 2014-09-12  0:42:00
