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
			'access_token' => Schema::TYPE_TEXT . ' comment "接口调用凭据"',
			'access_token_expired_at' => Schema::TYPE_INTEGER . ' not null default 0 comment "access_token过期时间"',
			'jsapi_ticket' => Schema::TYPE_TEXT . ' comment "JS接口调用票据"',
			'jsapi_ticket_expired_at' => Schema::TYPE_INTEGER . ' not null default 0 comment "jsapi_ticket过期时间"',
			'ip_list' => Schema::TYPE_TEXT . ' comment "微信服务器IP地址(json)"',
			'count_image' => Schema::TYPE_INTEGER . ' not null default 0 comment "图片总数量, <=1000"',
			'count_voice' => Schema::TYPE_INTEGER . ' not null default 0 comment "语音总数量, <=1000"',
			'count_video' => Schema::TYPE_INTEGER . ' not null default 0 comment "视频总数量, <=1000"',
			'count_news' => Schema::TYPE_INTEGER . ' not null default 0 comment "图文总数量, <=5000"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="公众号"');
	}

	public function down() {
		$this->dropTable('{{%wechat}}');
	}

}
