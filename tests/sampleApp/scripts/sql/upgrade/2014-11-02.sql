CREATE TABLE `ac_shop_product_extraCodes` (
	`productId` INT(10) UNSIGNED NOT NULL,
	`ean` VARCHAR(255) NOT NULL DEFAULT '',
	`asin` VARCHAR(255) NOT NULL DEFAULT '',
	`gtin` VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`productId`),
	CONSTRAINT `fk_ac_product_ean_1` FOREIGN KEY (`productId`) REFERENCES `ac_shop_products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

