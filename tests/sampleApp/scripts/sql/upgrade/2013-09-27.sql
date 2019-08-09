ALTER TABLE `ac_people_tags` DROP FOREIGN KEY `FK_ac_people_tags_1` , DROP FOREIGN KEY `FK_ac_people_tags_2` ;
ALTER TABLE `ac_people_tags` CHANGE COLUMN `personId` `idOfPerson` INT(10) UNSIGNED NOT NULL  , CHANGE COLUMN `tagId` `idOfTag` INT(10) UNSIGNED NOT NULL  , 
  ADD CONSTRAINT `FK_ac_people_tags_1`
  FOREIGN KEY (`idOfPerson` )
  REFERENCES `ac_people` (`personId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE, 
  ADD CONSTRAINT `FK_ac_people_tags_2`
  FOREIGN KEY (`idOfTag` )
  REFERENCES `ac_tags` (`tagId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE;

