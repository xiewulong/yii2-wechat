<?php

use yii\db\Schema;

class m160119_070234_wechat_message extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%wechat_message}}', [
			'id' => Schema::TYPE_BIGINT . ' not null primary key comment "id"',
			'appid' => Schema::TYPE_STRING . '(50) not null comment "ToUserName, 开发者微信号"',
			'openid' => Schema::TYPE_STRING . ' not null comment "FromUserName, 发送方帐号"',
			'type' => Schema::TYPE_BOOLEAN . ' not null default 1 comment "类型: 1接收, 2发送"',
			'msgid' => Schema::TYPE_STRING . ' comment "MsgId, 消息id"',
			'mediaid' => Schema::TYPE_STRING . ' comment "MediaId, 媒体id"',
			'msgtype' => Schema::TYPE_STRING . '(50) not null comment "MsgType, 消息类型: text(文本消息), image(图片消息), voice(语音消息), video(视频消息), shortvideo(小视频消息), location(地理位置消息), link(链接消息), link(链接消息), event(事件)"',
			'create_time' => Schema::TYPE_INTEGER . ' not null default 0 comment "CreateTime, 消息创建时间"',
			'content' => Schema::TYPE_TEXT . ' comment "Content, 文本消息内容"',
			'picurl' => Schema::TYPE_TEXT . ' comment "PicUrl, 图片链接"',
			'format' => Schema::TYPE_STRING . '(50) comment "Format, 语音格式"',
			'recognition' => Schema::TYPE_STRING . ' comment "Recognition, 语音识别结果"',
			'thumbmediaid' => Schema::TYPE_STRING . ' comment "ThumbMediaId, 缩略图的媒体id"',
			'loc_x' => Schema::TYPE_STRING . ' comment "Location_X, 地理位置纬度"',
			'loc_y' => Schema::TYPE_STRING . ' comment "Location_Y, 地理位置经度"',
			'scale' => Schema::TYPE_STRING . ' comment "Scale, 地图缩放大小"',
			'label' => Schema::TYPE_STRING . ' comment "Label, 地理位置信息"',
			'title' => Schema::TYPE_STRING . ' comment "Title, 消息标题"',
			'description' => Schema::TYPE_STRING . ' comment "Description, 消息描述"',
			'url' => Schema::TYPE_STRING . ' comment "Url, 消息链接"',
			'event' => Schema::TYPE_STRING . '(50) comment "Event, 事件类型: subscribe(订阅或关注同时扫描带参数二维码), unsubscribe(取消订阅), scan(已关注时扫描带参数二维码), location(上报地理位置), click(自定义菜单), view(点击菜单跳转链接)"',
			'eventkey' => Schema::TYPE_STRING . ' comment "EventKey, 事件KEY值: 1. qrscene_为前缀, 后面为二维码的参数值; 2. 是一个32位无符号整数, 即创建二维码时的二维码scene_id; 3. 与自定义菜单接口中KEY值对应; 4. 设置的跳转URL"',
			'ticket' => Schema::TYPE_STRING . ' comment "Ticket, 二维码的ticket, 可用来换取二维码图片"',
			'latitude' => Schema::TYPE_STRING . ' comment "Latitude, 地理位置纬度"',
			'longitude' => Schema::TYPE_STRING . ' comment "Longitude, 地理位置经度"',
			'precision' => Schema::TYPE_STRING . ' comment "Precision, 地理位置精度"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
			'updated_at' => Schema::TYPE_INTEGER . ' not null comment "更新时间"',
		], $tableOptions . ' comment="消息"');
	}

	public function down() {
		$this->dropTable('{{%wechat_message}}');
	}

}
