DROP TABLE `ac_shop_product_extraCodes`;
CREATE TABLE `ac_shop_product_extraCodes` (
	`productId` INT(10) UNSIGNED NOT NULL,
	`ean` VARCHAR(255) NOT NULL DEFAULT '',
	`asin` VARCHAR(255) NOT NULL DEFAULT '',
	`gtin` VARCHAR(255) NOT NULL DEFAULT '',
	`responsiblePersonId` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`productId`),
	CONSTRAINT `fkExtraCodeProduct` FOREIGN KEY (`productId`) REFERENCES `ac_shop_products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `fkExtraCodeResponsiblePerson` FOREIGN KEY (`responsiblePersonId`) REFERENCES `ac_people` (`personId`) ON UPDATE CASCADE ON DELETE SET NULL
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB

INSERT INTO `avancore_test`.`ac_shop_product_extraCodes` (`productId`, `ean`, `asin`, `gtin`, `responsiblePersonId`) VALUES (2, '1', '2', '3', 3);

