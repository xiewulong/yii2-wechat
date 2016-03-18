<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class WechatMessageKeyword extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat_message_keyword}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	 * 获取消息回复规则
	 * @method getRule
	 * @since 0.0.1
	 * @return {object}
	 * @example $this->getRule();
	 */
	public function getRule() {
		return $this->hasOne(WechatMessageRule::classname(), ['id' => 'rule_id']);
	}

}
