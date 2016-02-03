<?php

use yii\db\Schema;

class m160115_081300_wechat extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat}}', [
			'appid' => Schema::TYPE_STRING . '(50) primary key comment "AppID(应用ID)"',
			'name' => Schema::TYPE_STRING . '(50) not null comment "名称"',
			'secret' => Schema::TYPE_STRING . '(50) not null comment "AppSecret(应用密钥)"',
			'token' => Schema::TYPE_STRING . '(50) not null comment "Token(令牌)"',
			'aeskey' => Schema::TYPE_STRING . '(50) comment "EncodingAESKey(消息加解密密钥)"',
			'mode' => Schema::TYPE_BOOLEAN . ' not null default 1 comment "消息加解密模式: 0明文模式, 1兼容模式, 2安全模式"',
			'access_token' => Schema::TYPE_TEXT . ' comment "接口调用凭据"',
			'expired_at' => Schema::TYPE_INTEGER . ' not null default 0 comment "access_token过期时间"',
			'ip_list' => Schema::TYPE_TEXT . ' comment "微信服务器IP地址(json)"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="公众号"');
	}

	public function down() {
		$this->dropTable('{{%wechat}}');
	}

}
