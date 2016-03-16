<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

class WechatNewsMedia extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat_news_media}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	 * 获取图文素材
	 * @method getNews
	 * @since 0.0.1
	 * @return {object}
	 * @example $this->getNews();
	 */
	public function getNews() {
		return $this->hasOne(WechatNews::classname(), ['id' => 'news_id']);
	}

	/**
	 * 获取封面图片素材媒体列表
	 * @method getThumbMaterialMediaList
	 * @since 0.0.1
	 * @return {array}
	 * @example $this->getThumbMaterialMediaList();
	 */
	public function getThumbMaterialMediaList() {
		$materialMedias = [];
		foreach($this->thumbMaterialMediaIdList as $thumb_material_media_id) {
			$materialMedias[$thumb_material_media_id] = WechatMaterialMedia::findOne($thumb_material_media_id);
		}

		return $materialMedias;
	}

	/**
	 * 获取封面图片素材媒体id列表
	 * @method getThumbMaterialMediaIdList
	 * @since 0.0.1
	 * @return {array}
	 * @example $this->getThumbMaterialMediaIdList();
	 */
	public function getThumbMaterialMediaIdList() {
		return Json::decode($this->thumb_material_media_ids);
	}

	/**
	 * 获取图文页url列表
	 * @method getUrlList
	 * @since 0.0.1
	 * @return {array}
	 * @example $this->getUrlList();
	 */
	public function getUrlList() {
		return $this->urls ? Json::decode($this->urls) : [];
	}

	/**
	 * 获取封面图片url列表
	 * @method getThumbUrlList
	 * @since 0.0.1
	 * @return {array}
	 * @example $this->getThumbUrlList();
	 */
	public function getThumbUrlList() {
		return $this->thumb_urls ? Json::decode($this->thumb_urls) : [];
	}

}
