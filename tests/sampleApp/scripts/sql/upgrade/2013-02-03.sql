ALTER TABLE `ac_relations` DROP FOREIGN KEY `FK_ac_relations_1` , DROP FOREIGN KEY `FK_ac_relations_2` ;
ALTER TABLE `ac_relations` 
  ADD CONSTRAINT `FK_ac_relations_outgoing`
  FOREIGN KEY (`personId` )
  REFERENCES `ac_people` (`personId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE, 
  ADD CONSTRAINT `FK_ac_relations_incoming`
  FOREIGN KEY (`otherPersonId` )
  REFERENCES `ac_people` (`personId` )
  ON DELETE CASCADE
  ON UPDATE CASCADE
, DROP INDEX `FK_ac_relations_1` 
, ADD INDEX `FK_ac_relations_outgoing` (`personId` ASC) 
, DROP INDEX `FK_ac_relations_2` 
, ADD INDEX `FK_ac_relations_incoming` (`otherPersonId` ASC) ;

