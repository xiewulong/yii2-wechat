<?php

use yii\db\Schema;

class m160119_075249_wechat_message_rule extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat_message_rule}}', [
			'id' => Schema::TYPE_PK . ' comment "id"',
			'appid' => Schema::TYPE_STRING . '(50) not null comment "应用id"',
			'type' => Schema::TYPE_STRING . '(50) not null comment "规则类型, 按优先级筛选, beadded(被添加) > keywords(关键词) > autoreply(自动)"',
			'msg_type' => Schema::TYPE_STRING . '(50) not null comment "MsgType, text(文本), image(图片), voice(语音), video(视频), music(音乐), news(图文)"',
			'content' => Schema::TYPE_TEXT . ' comment "Content, 文本消息内容"',
			'news_media_id' => Schema::TYPE_INTEGER . ' comment "图文消息id"',
			'material_media_id' => Schema::TYPE_INTEGER . ' comment "素材媒体id"',
			'thumb_material_media_id' => Schema::TYPE_INTEGER . ' comment "缩略图的素材媒体id"',
			'title' => Schema::TYPE_STRING . ' comment "Title, 音乐标题"',
			'description' => Schema::TYPE_STRING . ' comment "Description, 音乐描述"',
			'music_url' => Schema::TYPE_TEXT . ' comment "MusicURL, 音乐链接(非微信端)"',
			'hq_music_url' => Schema::TYPE_TEXT . ' comment "HQMusicUrl, 高质量音乐链接(非微信端), wifi环境优先使用该链接播放音乐"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="公众号消息回复规则"');
	}

	public function down() {
		$this->dropTable('{{%wechat_message_rule}}');
	}

}
