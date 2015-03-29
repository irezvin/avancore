alter table ac_shop_products drop column published;

CREATE TABLE `ac_publish` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`sharedObjectType` VARCHAR(50) NOT NULL,
	`published` INT(1) UNSIGNED NULL DEFAULT '1',
	`deleted` INT(1) UNSIGNED NULL DEFAULT '0',
	`publishUp` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
	`publishDown` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
	`authorId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`editorId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`pubChannelId` VARCHAR(255) NULL DEFAULT NULL,
	`dateCreated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`dateModified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`dateDeleted` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	INDEX `sharedObjectType` (`sharedObjectType`),
	PRIMARY KEY (`id`),
	CONSTRAINT `fkPubAuthor` FOREIGN KEY (`authorId`) REFERENCES `ac_people` (`personId`) ON UPDATE SET NULL ON DELETE SET NULL,
	CONSTRAINT `fkPubEditor` FOREIGN KEY (`editorId`) REFERENCES `ac_people` (`personId`) ON UPDATE SET NULL ON DELETE SET NULL
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `ac_shop_products`
	ADD COLUMN `pubId` INT(10) UNSIGNED NULL DEFAULT NULL,
	ADD CONSTRAINT `fkPersonPublish` FOREIGN KEY (`pubId`) REFERENCES `ac_publish` (`id`);
	
ALTER TABLE `ac_shop_categories`
	ADD COLUMN `pubId` INT(10) UNSIGNED NULL DEFAULT NULL,
	ADD CONSTRAINT `fkCategoryPublish` FOREIGN KEY (`pubId`) REFERENCES `ac_publish` (`id`);
	
ALTER TABLE `ac_person_posts`
	ADD COLUMN `pubId` INT(10) UNSIGNED NULL DEFAULT NULL,
	ADD CONSTRAINT `fkPostPublish` FOREIGN KEY (`pubId`) REFERENCES `ac_publish` (`id`);

ALTER TABLE `ac_shop_products`
    add unique index idxPubId (pubId);
    
ALTER TABLE `ac_shop_categories`
    add unique index idxPubId (pubId);    
    
ALTER TABLE `ac_person_posts`
    add unique index idxPubId (pubId);    
    
ALTER TABLE `ac_publish`
    add unique index idxPubChannelId (pubChannelId);    
    
INSERT INTO `ac_publish` (`id`, `sharedObjectType`, `authorId`, `editorId`, `pubChannelId`, `dateCreated`, `dateModified`) VALUES (1, 'product', 3, 6, '123456', '2014-11-20 01:22:31', '2014-12-20 01:22:31');

UPDATE `ac_shop_products` SET `pubId`=1 WHERE  `id`=1;


