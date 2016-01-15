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
			'appid' => Schema::TYPE_STRING . '(50) not null comment "应用id"',
			'openid' => Schema::TYPE_STRING . ' not null comment "绑定微信用户id, 对应appid唯一"',
			'userid' => Schema::TYPE_INTEGER . ' not null comment "用户id"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="公众号用户绑定"');
	}

	public function down() {
		$this->dropTable('{{%wechat_user}}');
	}

}
