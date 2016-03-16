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

}
