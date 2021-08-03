CREATE TABLE `api_log` (
	`log_id` INT(255) NOT NULL AUTO_INCREMENT,
	`log_timestamp` DATETIME NOT NULL,
	`api_key` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`query` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`log_id`) USING BTREE,
	UNIQUE INDEX `log_id` (`log_id`) USING BTREE,
	INDEX `api_key` (`api_key`) USING BTREE,
	CONSTRAINT `api_key` FOREIGN KEY (`api_key`) REFERENCES `s54835__spur`.`api_users` (`api_key`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
