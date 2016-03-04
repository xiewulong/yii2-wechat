<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

class WechatNewsCache extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat_news_cache}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	 * 获取图文素材项列表
	 * @method getItemList
	 * @since 0.0.1
	 * @return {array}
	 * @example $this->getItemList($manager);
	 */
	public function getItemList(&$manager = null) {
		return $this->items ? Json::decode($this->items) : [];
	}

}
