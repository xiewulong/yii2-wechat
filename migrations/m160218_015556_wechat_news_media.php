<?php

use yii\db\Schema;

class m160218_015556_wechat_news_media extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat_news_media}}', [
			'id' => Schema::TYPE_PK . ' comment "id"',
			'appid' => Schema::TYPE_STRING . '(50) not null comment "应用id"',
			'news_id' => Schema::TYPE_INTEGER . ' not null comment "图文素材id"',
			'media_id' => Schema::TYPE_STRING . ' not null comment "媒体id"',
			'urls' => Schema::TYPE_TEXT . ' comment "图文页url(微信端)(json)"',
			'thumb_material_media_ids' => Schema::TYPE_STRING . ' not null comment "封面图片媒体id(必须是永久素材媒体)(json)"',
			'thumb_urls' => Schema::TYPE_TEXT . ' comment "封面图片url(微信端)(json)"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="公众号图文消息"');
	}

	public function down() {
		$this->dropTable('{{%wechat_news_media}}');
	}

}
