CREATE TABLE `ac_products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sku` VARCHAR(255) NOT NULL DEFAULT '',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `metaId` INT UNSIGNED NULL DEFAULT NULL,
  `published` INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`));

CREATE TABLE `ac_meta` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pageTitle` VARCHAR(255) NULL DEFAULT NULL,
  `metaDescription` VARCHAR(255) NULL DEFAULT NULL,
  `metaKeywords` VARCHAR(255) NULL DEFAULT NULL,
  `metaNoindex` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  `sharedObjectType` ENUM('product', 'category','other') NOT NULL DEFAULT 'other',
  PRIMARY KEY (`id`));

CREATE TABLE `ac_product_upc` (
  `productId` INT UNSIGNED NOT NULL,
  `upcCode` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`productId`),
  CONSTRAINT `fk_ac_product_upc_1`
    FOREIGN KEY (`productId`)
    REFERENCES `avancore_test`.`ac_products` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE);

