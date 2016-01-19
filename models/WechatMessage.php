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

}
