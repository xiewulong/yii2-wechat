<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class WechatMedia extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat_media}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	 * 获取素材
	 * @method getMaterial
	 * @since 0.0.1
	 * @return {object}
	 * @example $this->getMaterial();
	 */
	public function getMaterial() {
		return $this->hasOne(WechatMaterial::classname(), ['id' => 'material_id']);
	}

	/**
	 * 获取缩略图素材
	 * @method getThumb
	 * @since 0.0.1
	 * @return {object}
	 * @example $this->getThumb();
	 */
	public function getThumb() {
		return $this->hasOne(WechatMaterial::classname(), ['id' => 'thumb_material_id']);
	}

}
