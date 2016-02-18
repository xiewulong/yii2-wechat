<?php

use yii\db\Schema;

class m160218_012837_wechat_news_image extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat_news_image}}', [
			'id' => Schema::TYPE_PK . ' comment "id"',
			'appid' => Schema::TYPE_STRING . '(50) not null comment "应用id"',
			'url' => Schema::TYPE_TEXT . ' not null comment "url(微信端), <=1MB"',
			'url_source' => Schema::TYPE_TEXT . ' not null comment "源url(非微信端)"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="公众号图文素材内图片"');
	}

	public function down() {
		$this->dropTable('{{%wechat_news_image}}');
	}

}
