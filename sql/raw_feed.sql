CREATE TABLE `raw_feed` (
	`feed_id` INT(255) NOT NULL AUTO_INCREMENT,
	`feed_timestamp` DATETIME NOT NULL,
	`feed_data` LONGTEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`status` INT(1) NOT NULL DEFAULT '1' COMMENT '1=new,2=processing,3=processed',
	`total_records` INT(20) NULL DEFAULT NULL,
	`status_timestamp` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`feed_id`) USING BTREE,
	UNIQUE INDEX `feed_id` (`feed_id`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
