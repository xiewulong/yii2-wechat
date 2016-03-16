<?php

use yii\db\Schema;

class m160119_076441_wechat_message_keyword extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat_message_keyword}}', [
			'id' => Schema::TYPE_PK . ' comment "id"',
			'appid' => Schema::TYPE_STRING . '(50) not null comment "应用id"',
			'rule_id' => Schema::TYPE_INTEGER . ' not null comment "消息回复规则id"',
			'keyword' => Schema::TYPE_STRING . '(50) not null comment "关键词, <=30"',
			'mode' => Schema::TYPE_BOOLEAN . ' not null default 0 comment "匹配模式: 0模糊匹配, 1全匹配"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="公众号消息关键词"');
	}

	public function down() {
		$this->dropTable('{{%wechat_message_keyword}}');
	}

}
