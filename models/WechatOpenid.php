<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class WechatOpenid extends ActiveRecord{

	public static function tableName(){
		return '{{%wechat_openid}}';
	}

	public function behaviors(){
		return [
			TimestampBehavior::className(),
		];
	}

}
