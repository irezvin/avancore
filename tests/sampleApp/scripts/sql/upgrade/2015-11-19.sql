-- MySQL dump 10.15  Distrib 10.0.22-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: avancore_test
-- ------------------------------------------------------
-- Server version	10.0.22-MariaDB-log

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


REPLACE INTO `ac_shop_products` (`sku`, `title`, `metaId`, `pubId`) VALUES ('PROD03', 'Товар 3', NULL, NULL);
REPLACE INTO `ac_shop_products` (`sku`, `title`, `metaId`, `pubId`) VALUES ('PROD04', 'Товар 4', NULL, NULL);


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

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-11-19  1:18:04
