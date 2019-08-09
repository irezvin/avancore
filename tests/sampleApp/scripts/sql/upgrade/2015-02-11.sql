CREATE TABLE `ac_shop_product_notes` (
	`productId` INT(11) UNSIGNED NOT NULL,
	`note` TEXT NOT NULL,
	`noteAuthorId` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`productId`),
	INDEX `fkProductNoteAuthor` (`noteAuthorId`),
	CONSTRAINT `fkProductNoteAuthor` FOREIGN KEY (`noteAuthorId`) REFERENCES `ac_people` (`personId`) ON UPDATE CASCADE ON DELETE SET NULL,
	CONSTRAINT `fkProductNoteProduct` FOREIGN KEY (`productId`) REFERENCES `ac_shop_products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

