CREATE TABLE `tbpre_wechat` (
	`appid` VARCHAR(50) NOT NULL COMMENT 'AppID(应用ID)' COLLATE 'utf8_unicode_ci',
	`appsecret` VARCHAR(50) NOT NULL COMMENT 'AppSecret(应用密钥)' COLLATE 'utf8_unicode_ci',
	`token` VARCHAR(50) NOT NULL COMMENT 'Token(令牌)' COLLATE 'utf8_unicode_ci',
	`aeskey` VARCHAR(50) NULL DEFAULT NULL COMMENT 'EncodingAESKey(消息加解密密钥)' COLLATE 'utf8_unicode_ci',
	`mode` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '消息加解密模式: 0明文模式, 1兼容模式, 2安全模式',
	`access_token` TEXT NULL COMMENT 'access_token' COLLATE 'utf8_unicode_ci',
	`expires_in` INT(11) NOT NULL DEFAULT '0' COMMENT 'access_token有效时长(秒)',
	`created_at` INT(11) NOT NULL COMMENT '创建时间',
	`updated_at` INT(11) NOT NULL COMMENT '更新时间',
	PRIMARY KEY (`appid`)
)
COMMENT='微信app'
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB;

INSERT INTO `tbpre_wechat` (`appid`, `appsecret`, `token`, `aeskey`, `mode`, `created_at`, `updated_at`) VALUES
	('appid1', 'appsecret', 'Token', 'encodingAESKey', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
	('appid2', 'appsecret', 'Token', 'encodingAESKey', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
	('appid3', 'appsecret', 'Token', 'encodingAESKey', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
