ALTER TABLE `ac_people`
	DROP FOREIGN KEY `FK_ac_person_photos_ac_people_protrait`;
ALTER TABLE `ac_people`
	ADD CONSTRAINT `FK_ac_person_photos_ac_people_portrait` FOREIGN KEY (`personId`, `portraitId`) REFERENCES `ac_person_photos` (`personId`, `photoId`);
