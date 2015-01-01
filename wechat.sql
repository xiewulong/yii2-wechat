CREATE TABLE `tbpre_wechat` (
	`appid` VARCHAR(50) NOT NULL,
	`access_token` TEXT NOT NULL,
	`expires_in` BIGINT(20) UNSIGNED NOT NULL,
	`created_at` BIGINT(20) UNSIGNED NOT NULL,
	`updated_at` BIGINT(20) UNSIGNED NOT NULL,
	PRIMARY KEY (`appid`),
	UNIQUE INDEX `appid` (`appid`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
