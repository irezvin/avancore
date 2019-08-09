CREATE TABLE `ac_person_posts` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`personId` INT(10) UNSIGNED NULL,
	`photoId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`title` VARCHAR(255) NULL DEFAULT '',
	`content` LONGTEXT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	INDEX `personId` (`personId`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE ac_person_posts
ADD CONSTRAINT `FK__ac_post_person` FOREIGN KEY (`personId`) REFERENCES `ac_people` (`personId`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `ac_person_posts`
	ADD CONSTRAINT `FK__ac_post_photo` FOREIGN KEY (`personId`, `photoId`) REFERENCES `ac_person_photos` (`personId`, `photoId`) ON UPDATE SET NULL ON DELETE SET NULL;

INSERT INTO `ac_person_posts` (`personId`, `photoId`, `title`, `content`) VALUES (3, 1, 'Post 1 by Ilya', 'The text 1');
INSERT INTO `ac_person_posts` (`personId`, `photoId`, `title`, `content`) VALUES (3, NULL, 'Post 2 by Ilya (no photo)', 'No photo this time');
INSERT INTO `ac_person_posts` (`personId`, `photoId`, `title`, `content`) VALUES (4, 3, 'Post by Tanya', 'Hello, world!');

