CREATE TABLE `feed_cache` (
	`cache_id` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`cache_timestamp` DATETIME NOT NULL,
	`ip_address` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`user_count` INT(10) NULL DEFAULT NULL,
	`maxmind_city` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`maxmind_cc` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`maxmind_subdivision` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`services` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`org` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`getipintel_score` DOUBLE NULL DEFAULT NULL,
	`raw_feed_result` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`do_not_purge` INT(1) NOT NULL DEFAULT '0',
	`hidden` INT(1) NULL DEFAULT '0',
	`expired` INT(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`cache_id`) USING BTREE,
	UNIQUE INDEX `cache_id` (`cache_id`) USING BTREE,
	UNIQUE INDEX `ip_address` (`ip_address`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
