<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\base\ErrorException;

class WechatNews extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat_news_cache}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

}
