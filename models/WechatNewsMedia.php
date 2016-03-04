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
		return $this->hasOne(WechatNews::classname(), ['id' => 'newsid']);
	}

	/**
	 * 获取封面图片媒体列表
	 * @method getThumbMediaList
	 * @since 0.0.1
	 * @return {array}
	 * @example $this->getThumbMediaList();
	 */
	public function getThumbMediaList() {
		$medias = [];
		foreach($this->thumbMediaidList as $thumb_mediaid) {
			$medias[$thumb_mediaid] = WechatMedia::findOne($thumb_mediaid);
		}

		return $medias;
	}

	/**
	 * 获取封面图片媒体id列表
	 * @method getThumbMediaidList
	 * @since 0.0.1
	 * @return {array}
	 * @example $this->getThumbMediaidList();
	 */
	public function getThumbMediaidList() {
		return Json::decode($this->thumb_mediaids);
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
