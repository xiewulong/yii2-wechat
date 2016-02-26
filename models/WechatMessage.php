<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class WechatMessage extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat_message}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	 * 获取回复消息
	 * @method getReply
	 * @since 0.0.1
	 * @param {string} [$messageClass] 调用公众号消息类回复(优先级最高), 否则默认规则回复
	 * @return {array}
	 * @example $this->getReply($messageClass);
	 */
	public function getReply($messageClass = null) {
		if($messageClass) {
			return ($message = static::findOne($messageClass::autoReply($this->id))) && $message->pid == $this->id ? $message->replyFormat : null;
		}

		return $this->replyFormat;
	}

	/**
	 * 获取回复格式
	 * @method getReplyFormat
	 * @since 0.0.1
	 * @return {array}
	 * @example $this->getReplyFormat();
	 */
	public function getReplyFormat() {
		return [
			'ToUserName' => $this->from_user_name,
			'FromUserName' => $this->to_user_name,
			'CreateTime' => time(),
			'MsgType' => 'text',
			'Content' => 'FromUserName: ' . $this->from_user_name . ', ToUserName: ' . $this->to_user_name . ', MsgType: ' . $this->msg_type . ', Url: ' . \Yii::$app->request->url,
		];
	}

}
