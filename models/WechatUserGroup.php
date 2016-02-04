<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class WechatUserGroup extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat_user_group}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

}
