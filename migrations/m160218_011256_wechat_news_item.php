<?php

use yii\db\Schema;

class m160218_011256_wechat_news_item extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat_news_item}}', [
			'id' => Schema::TYPE_PK . ' comment "id"',
			'newsid' => Schema::TYPE_INTEGER . ' not null comment "图文素材id"',
			'title' => Schema::TYPE_STRING . '(50) not null comment "标题"',
			'author' => Schema::TYPE_STRING . '(50) not null comment "作者"',
			'thumb_materialid' => Schema::TYPE_INTEGER . ' not null comment "封面图片素材id(必须是永久素材媒体)"',
			'show_cover_pic' => Schema::TYPE_BOOLEAN . ' not null default 0 comment "封面显示: 0不显示, 1显示"',
			'digest' => Schema::TYPE_STRING . ' comment "单图文摘要, <=120个字, 如果不填写会默认抓取正文前54个字"',
			'content' => Schema::TYPE_TEXT . ' not null comment "内容, <=2万字符, <=1MB, 具备微信支付权限可以使用a标签"',
			'content_source_url' => Schema::TYPE_TEXT . ' comment "原文阅读url"',
			'list_order' => Schema::TYPE_INTEGER . ' not null default 0 comment "列表排序"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="图文素材项"');
	}

	public function down() {
		$this->dropTable('{{%wechat_news_item}}');
	}

}
