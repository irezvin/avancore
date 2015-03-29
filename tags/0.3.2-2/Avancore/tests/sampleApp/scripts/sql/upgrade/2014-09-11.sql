CREATE TABLE `ac_perks` (
  `perkId` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NULL DEFAULT '',
  PRIMARY KEY (`perkId`),
  INDEX `uqName` (`name` ASC));

CREATE TABLE `ac_tag_perks` (
  `idOfTag` INT UNSIGNED NOT NULL,
  `idOfPerk` INT NULL,
  PRIMARY KEY (`idOfTag`, `idOfPerk`),
  INDEX `fkTagId_idx` (`idOfTag` ASC),
  INDEX `fkPerkId_idx` (`idOfPerk` ASC),
  CONSTRAINT `fkTagId`
    FOREIGN KEY (`idOfTag`)
    REFERENCES `avancore_test`.`ac_tags` (`tagId`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fkPerkId`
    FOREIGN KEY (`idOfPerk`)
    REFERENCES `avancore_test`.`ac_perks` (`perkId`)
    ON DELETE CASCADE
    ON UPDATE CASCADE);


