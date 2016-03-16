<?php

use yii\db\Schema;

class m160119_075249_wechat_message_rule extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat_message_rule}}', [
			'id' => Schema::TYPE_BIGINT . ' not null primary key auto_increment comment "id"',
			'appid' => Schema::TYPE_STRING . '(50) not null comment "应用id"',
			'type' => Schema::TYPE_STRING . '(50) not null comment "规则类型, 按优先级筛选, subscribe(被添加) > keyword(关键词) > common(普通)"',
			'msg_type' => Schema::TYPE_STRING . '(50) not null comment "MsgType, text(文本), image(图片), voice(语音), video(视频), music(音乐), news(图文)"',
			'content' => Schema::TYPE_TEXT . ' comment "Content, 文本消息内容"',
			'mediaid' => Schema::TYPE_INTEGER . ' not null comment "媒体id"',
			'mediaid' => Schema::TYPE_INTEGER . ' not null comment "媒体id"',



			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="公众号消息回复规则"');
	}

	public function down() {
		$this->dropTable('{{%wechat_message_rule}}');
	}

}
