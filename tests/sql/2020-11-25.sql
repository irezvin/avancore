CREATE TABLE `ac_items` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`summary` MEDIUMTEXT NULL,
	`description` LONGTEXT NULL,
	`published` INT(1) UNSIGNED NOT NULL DEFAULT 0,
	`created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`hits` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	INDEX `title` (`title`),
	INDEX `created` (`created`),
	INDEX `modified` (`modified`),
	INDEX `hits` (`hits`)
)
COMMENT='Demo table with simple structure for various purposes'
COLLATE='utf8_general_ci'

ALTER TABLE `ac_tree_nested_sets`
  CHANGE COLUMN `comment` `comment` VARCHAR(40) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci' AFTER `ordering`;
  
