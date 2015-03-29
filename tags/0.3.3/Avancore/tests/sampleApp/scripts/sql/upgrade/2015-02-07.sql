CREATE TABLE `ac_shop_product_categories` (
	`productId` INT(10) UNSIGNED NOT NULL,
	`categoryId` INT(10) UNSIGNED NOT NULL,
	PRIMARY KEY (`productId`, `categoryId`),
	CONSTRAINT `fkProductCategoryProduct` FOREIGN KEY (`productId`) REFERENCES `ac_shop_products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `fkProductCategoryCategory` FOREIGN KEY (`categoryId`) REFERENCES `ac_shop_categories` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
