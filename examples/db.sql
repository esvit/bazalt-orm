CREATE DATABASE `orm_examples` /*!40100 COLLATE 'utf8_unicode_ci' */;

CREATE TABLE `categories` (
	`category_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`created_at` DATETIME NOT NULL,
	`updated_at` DATETIME NOT NULL,
	PRIMARY KEY (`category_id`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB;

CREATE TABLE `news` (
	`article_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`category_id` INT(10) UNSIGNED NOT NULL DEFAULT NULL,
	`title` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`description` TEXT NOT NULL COLLATE 'utf8_unicode_ci',
	`created_at` DATETIME NOT NULL,
	`updated_at` DATETIME NOT NULL,
	PRIMARY KEY (`article_id`),
	INDEX `FK_news_ref_categories` (`category_id`),
	CONSTRAINT `FK_news_ref_categories` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON UPDATE NO ACTION ON DELETE CASCADE
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB;
