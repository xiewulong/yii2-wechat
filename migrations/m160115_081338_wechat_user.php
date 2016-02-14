<?php

use yii\db\Schema;

class m160115_081338_wechat_user extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat_user}}', [
			'id' => Schema::TYPE_PK . ' comment "id"',
			'userid' => Schema::TYPE_STRING . '(50) comment "用户id"',
			'appid' => Schema::TYPE_STRING . '(50) not null comment "应用id"',
			'openid' => Schema::TYPE_STRING . ' not null comment "绑定微信用户id, 对应appid唯一"',
			'unionid' => Schema::TYPE_STRING . ' comment "UnionID"',
			'subscribe' => Schema::TYPE_BOOLEAN . ' not null default 0 comment "订阅状态: 0未订阅, 1已订阅"',
			'subscribe_time' => Schema::TYPE_INTEGER . ' not null default 0 comment "关注时间"',
			'nickname' => Schema::TYPE_STRING . ' comment "昵称(urlencode)"',
			'remark' => Schema::TYPE_STRING . '(50) comment "备注名, 长度必须小于30字符"',
			'groupid' => Schema::TYPE_INTEGER . ' not null default 0 comment "所在分组ID"',
			'sex' => Schema::TYPE_BOOLEAN . ' not null default 0 comment "性别: 0未知, 1男性, 2女性"',
			'country' => Schema::TYPE_STRING . '(50) comment "所在国家"',
			'province' => Schema::TYPE_STRING . '(50) comment "所在省份"',
			'city' => Schema::TYPE_STRING . '(50) comment "所在城市"',
			'language' => Schema::TYPE_STRING . '(50) comment "语言"',
			'headimgurl' => Schema::TYPE_TEXT . ' comment "头像, 尺寸: 0(640*640), 46, 64, 96, 132"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="公众号用户"');
	}

	public function down() {
		$this->dropTable('{{%wechat_user}}');
	}

}
