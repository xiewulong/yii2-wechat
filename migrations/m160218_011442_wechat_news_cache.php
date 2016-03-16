<?php

use yii\db\Schema;

class m160218_011442_wechat_news_cache extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat_news_cache}}', [
			'id' => Schema::TYPE_PK . ' comment "id"',
			'news_id' => Schema::TYPE_INTEGER . ' comment "图文素材id"',
			'items' => Schema::TYPE_TEXT . ' comment "图文素材项(json)"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="图文素材缓存"');
	}

	public function down() {
		$this->dropTable('{{%wechat_news_cache}}');
	}

}
