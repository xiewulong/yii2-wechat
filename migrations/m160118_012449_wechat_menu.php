<?php

use yii\db\Schema;

class m160118_012449_wechat_menu extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat_menu}}', [
			'id' => Schema::TYPE_PK . ' comment "id"',
			'appid' => Schema::TYPE_STRING . '(50) not null comment "应用id"',
			'conditional' => Schema::TYPE_BOOLEAN . ' not null default 0 comment "菜单类型, 0自定义菜单, 1个性化菜单"',
			'pid' => Schema::TYPE_INTEGER . ' not null default 0 comment "父菜单id, 0主菜单, >0子菜单"',
			'menuid' => Schema::TYPE_INTEGER . ' comment "菜单id"',
			'type' => Schema::TYPE_STRING . '(50) not null comment "菜单的响应动作类型"',
			'name' => Schema::TYPE_STRING . '(50) not null comment "菜单标题, 不超过16个字节, 子菜单不超过40个字节"',
			'key' => Schema::TYPE_STRING . ' comment "菜单KEY值, 用于消息接口推送, 不超过128字节, click等点击类型必须"',
			'url' => Schema::TYPE_TEXT . ' comment "网页链接, 用户点击菜单可打开链接, 不超过1024字节, view类型必须"',
			'media_id' => Schema::TYPE_STRING . ' comment "调用新增永久素材接口返回的合法media_id, media_id类型和view_limited类型必须"',
			'group_id' => Schema::TYPE_INTEGER . ' comment "用户分组id, 可通过用户分组管理接口获取"',
			'sex' => Schema::TYPE_BOOLEAN . ' comment "性别: 1男, 2女, 不填则不做匹配"',
			'client_platform_type' => Schema::TYPE_BOOLEAN . ' comment "客户端版本, 当前只具体到系统型号: IOS(1), Android(2), Others(3), 不填则不做匹配"',
			'country' => Schema::TYPE_STRING . '(50) comment "国家信息, 是用户在微信中设置的地区"',
			'province' => Schema::TYPE_STRING . '(50) comment "省份信息, 是用户在微信中设置的地区"',
			'city' => Schema::TYPE_STRING . '(50) comment "城市信息, 是用户在微信中设置的地区"',
			'language' => Schema::TYPE_STRING . '(50) comment "语言信息, 是用户在微信中设置的语言"',
			'list_order' => Schema::TYPE_INTEGER . ' not null default 0 comment "列表排序"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="公众号菜单"');
	}

	public function down() {
		$this->dropTable('{{%wechat_menu}}');
	}

}
