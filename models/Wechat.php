<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Wechat extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

}
