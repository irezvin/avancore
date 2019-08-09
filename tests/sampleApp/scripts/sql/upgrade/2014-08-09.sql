CREATE TABLE `ac_person_photos` (
  `photoId` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `personId` INT(10) UNSIGNED NOT NULL,
  `filename` VARCHAR(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`photoId`));
  
ALTER TABLE `ac_person_photos`
	ADD CONSTRAINT `FK_ac_person_photos_ac_people` FOREIGN KEY (`personId`) REFERENCES `ac_people` (`personId`)  ON UPDATE CASCADE ON DELETE CASCADE;
	
CREATE TABLE `ac_person_albums` (
	`albumId` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`personId` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`albumName` VARCHAR(255) NOT NULL DEFAULT '\'\'',
	PRIMARY KEY (`albumId`),
	CONSTRAINT `FK__ac_people` FOREIGN KEY (`personId`) REFERENCES `ac_people` (`personId`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `ac_album_photos` (
	`personId` INT(10) UNSIGNED NOT NULL,
	`albumId` INT(10) UNSIGNED NOT NULL,
	`photoId` INT(10) UNSIGNED NOT NULL,
	PRIMARY KEY (`photoId`, `albumId`, `personId`),
	CONSTRAINT `FK__ac_person_albums` FOREIGN KEY (`personId`, `albumId`) REFERENCES `ac_person_albums` (`personId`, `albumId`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK__ac_person_photos` FOREIGN KEY (`personId`, `photoId`) REFERENCES `ac_person_photos` (`personId`, `photoId`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `ac_people` 
   ADD COLUMN `portraitId` INT(10) UNSIGNED NULL DEFAULT NULL,
	ADD CONSTRAINT `FK_ac_person_photos_ac_people_protrait` 
		FOREIGN KEY (`personId`, `portraitId`) 
		REFERENCES `ac_person_photos` (`personId`, `photoId`) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE `ac_people` 
DROP FOREIGN KEY `FK_ac_people_1`;

ALTER TABLE `ac_orientation`
    RENAME TO `ac_religion`,
	CHANGE COLUMN `sexualOrientationId` `religionId` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
	DROP INDEX `Index_2`,
	ADD UNIQUE INDEX `Index_2` (`title`);
	
ALTER TABLE `ac_people`
	CHANGE COLUMN `sexualOrientationId` `religionId` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `createdTs`,
	ADD CONSTRAINT `FK_ac_person_religion` FOREIGN KEY (`religionId`) REFERENCES `ac_religion` (`religionId`) ON UPDATE CASCADE ON DELETE SET NULL;
	
	
delete from `ac_religion`;
alter table `ac_religion` auto_increment = 1;
insert into `ac_religion` (title) values ('Christian'), ('Muslim'), ('Atheist'), ('Agnostic');

UPDATE `ac_people` SET `religionId`=4 WHERE `personId`=3;
UPDATE `ac_people` SET `religionId`=1 WHERE `personId`=4;

