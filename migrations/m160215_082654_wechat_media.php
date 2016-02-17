<?php

use yii\db\Schema;

class m160215_082654_wechat_media extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat_media}}', [
			'id' => Schema::TYPE_PK . ' comment "id"',
			'appid' => Schema::TYPE_STRING . '(50) not null comment "应用id"',
			'media_id' => Schema::TYPE_STRING . ' not null comment "媒体id"',
			'material_id' => Schema::TYPE_INTEGER . ' not null comment "素材id"',
			'url' => Schema::TYPE_TEXT . ' comment "url(微信端)"',
			'expired_at' => Schema::TYPE_INTEGER . ' not null default 0 comment "过期时间"',
			'thumb_media_id' => Schema::TYPE_STRING . ' comment "缩略图媒体id"',
			'thumb_material_id' => Schema::TYPE_INTEGER . ' comment "缩略图素材id"',
			'thumb_url' => Schema::TYPE_TEXT . ' comment "缩略图url(微信端)"',
			'thumb_expired_at' => Schema::TYPE_INTEGER . ' not null default 0 comment "缩略图过期时间"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="公众号媒体库"');
	}

	public function down() {
		$this->dropTable('{{%wechat_media}}');
	}

}
