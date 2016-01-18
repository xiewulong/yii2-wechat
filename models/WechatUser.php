<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class WechatUser extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat_user}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

}
