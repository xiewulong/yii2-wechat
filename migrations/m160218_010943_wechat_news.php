<?php

use yii\db\Schema;

class m160218_010943_wechat_news extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat_news}}', [
			'id' => Schema::TYPE_PK . ' comment "id"',
			'count_item' => Schema::TYPE_INTEGER . ' not null default 1 comment "素材项数量, 1单图文, >1多图文"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="图文素材"');
	}

	public function down() {
		$this->dropTable('{{%wechat_news}}');
	}

}
