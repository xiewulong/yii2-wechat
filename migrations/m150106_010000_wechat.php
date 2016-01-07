<?php

use yii\db\Schema;

class m150106_010000_wechat extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat}}', [
			'appid' => Schema::TYPE_STRING . '(50) primary key comment "AppID(应用ID)"',
			'name' => Schema::TYPE_STRING . '(50) not null comment "名称"',
			'appsecret' => Schema::TYPE_STRING . '(50) not null comment "AppSecret(应用密钥)"',
			'token' => Schema::TYPE_STRING . '(50) not null comment "Token(令牌)"',
			'aeskey' => Schema::TYPE_STRING . '(50) comment "EncodingAESKey(消息加解密密钥)"',
			'mode' => Schema::TYPE_BOOLEAN . ' not null default 1 comment "消息加解密模式: 0明文模式, 1兼容模式, 2安全模式"',
			'access_token' => Schema::TYPE_TEXT. ' comment "access_token"',
			'expires_in' => Schema::TYPE_INTEGER . ' not null default 0 comment "access_token有效时长(秒)"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="公众号"');
	}

	public function down() {
		$this->dropTable('{{%wechat}}');
	}

}
