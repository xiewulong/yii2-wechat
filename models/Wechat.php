<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

class Wechat extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	 * 获取微信服务器IP地址数组
	 * @method getIpListArray
	 * @return {array}
	 * @example $this->getIpListArray();
	 */
	public function getIpListArray() {
		return empty($this->ip_list) ? null : Json::decode($this->ip_list);
	}

}
