<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class WechatMaterialMedia extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat_material_media}}';
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

}
