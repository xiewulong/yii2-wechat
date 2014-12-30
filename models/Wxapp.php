<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Wxapp extends ActiveRecord{

	public static function tableName(){
		return '{{%wxapp}}';
	}

	public function behaviors(){
		return [
			TimestampBehavior::className(),
		];
	}

}
