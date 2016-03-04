<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\base\ErrorException;

class WechatNews extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat_news}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	 * 获取整组图文素材
	 * @method getArticles
	 * @since 0.0.1
	 * @param {object} [&$manager] wechat扩展对象
	 * @return {array}
	 * @example $this->getArticles($manager);
	 */
	public function getArticles(&$manager = null) {
		$articles = [];
		foreach($this->items as $item) {
			$item->manager = $manager;
			$articles[] = $item->json;
		}

		return $articles;
	}

	/**
	 * 获取素材项
	 * @method getItems
	 * @since 0.0.1
	 * @return {array}
	 * @example $this->getItems();
	 */
	public function getItems() {
		return $this->hasMany(WechatNewsItem::classname(), ['newsid' => 'id'])->orderby('list_order, created_at, id');
	}

}
