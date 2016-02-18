<?php

use yii\db\Schema;

class m160215_011538_wechat_material extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat_material}}', [
			'id' => Schema::TYPE_PK . ' comment "id"',
			'type' => Schema::TYPE_STRING . '(50) not null comment "类型, 图片(image: <=2MB, bmp/png/jpeg/jpg/gif), 语音(voice: <=2MB, <=60s, amr\mp3), 视频(video: <=10MB, mp4), 缩略图(thumb: <=64KB, jpg)"',
			'title' => Schema::TYPE_STRING . '(50) comment "标题"',
			'description' => Schema::TYPE_STRING . ' comment "描述"',
			'url' => Schema::TYPE_TEXT . ' not null comment "url(非微信端)"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="素材库"');
	}

	public function down() {
		$this->dropTable('{{%wechat_material}}');
	}

}
