-- MySQL dump 10.13  Distrib 5.5.35, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: ac3_test
-- ------------------------------------------------------
-- Server version	5.5.35-2

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ac_tree_adjacent` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`parentId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`ordering` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`tag` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `index_4` (`parentId`),
	INDEX `index_5` (`ordering`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-04-15 12:42:10

