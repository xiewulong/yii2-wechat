<?php

use yii\db\Schema;

class m160107_105323_wechat_openid extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat_openid}}', [
			'id' => Schema::TYPE_PK . ' comment "id"',
			'uid' => Schema::TYPE_INTEGER . ' not null comment "用户id"',
			'appid' => Schema::TYPE_STRING . '(50) not null comment "应用id"',
			'openid' => Schema::TYPE_STRING . ' not null comment "用户对应应用的唯一id"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="公众号openid"');
	}

	public function down() {
		$this->dropTable('{{%wechat}}');
	}

}
