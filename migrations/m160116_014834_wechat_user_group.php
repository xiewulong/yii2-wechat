<?php

use yii\db\Schema;

class m160116_014834_wechat_user_group extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat_user_group}}', [
			'id' => Schema::TYPE_PK . ' comment "id"',
			'appid' => Schema::TYPE_STRING . '(50) not null comment "应用id"',
			'gid' => Schema::TYPE_INTEGER . ' not null comment "分组id, 由微信分配, 必须对应应用id"',
			'name' => Schema::TYPE_STRING . '(50) comment "分组名字, 30个字符以内"',
			'count' => Schema::TYPE_INTEGER . ' not null default 0 comment "分组内用户数量"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="公众号用户分组"');
	}

	public function down() {
		$this->dropTable('{{%wechat_user_group}}');
	}

}
