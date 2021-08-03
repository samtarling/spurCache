CREATE TABLE `api_users` (
	`api_key` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`valid_from` DATETIME NOT NULL,
	`valid_until` DATETIME NOT NULL,
	`assigned_to` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`api_key`) USING BTREE,
	UNIQUE INDEX `api_key` (`api_key`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
