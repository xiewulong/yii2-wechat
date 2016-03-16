<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class WechatMessageRule extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat_message_rule}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

}
