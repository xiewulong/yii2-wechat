<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\base\ErrorException;

class WechatNews extends ActiveRecord {

	public $manager;

	public static function tableName() {
		return '{{%wechat_news}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	 * 获取一条图文素材
	 * @method getArticle
	 * @since 0.0.1
	 * @param {object} &$manager wechat扩展对象
	 * @return {array}
	 * @example $this->getArticles($manager);
	 */
	public function getArticle(&$manager) {
		$this->manager = $manager;
		$article = $this->json;

		return [
			'index' => $this->pid ? static::find()->where("pid = $this->pid")->andWhere("list_order < $this->list_order or (list_order = $this->list_order and created_at < $this->created_at) or (list_order = $this->list_order and created_at = $this->created_at and id < $this->id)")->count() + 1 : 0,
			'thumb_mediaid' => array_pop($article),
			'articles' => $article,
		];

	}

	/**
	 * 获取整组图文素材
	 * @method getArticles
	 * @since 0.0.1
	 * @param {object} &$manager wechat扩展对象
	 * @return {array}
	 * @example $this->getArticles($manager);
	 */
	public function getArticles(&$manager) {
		$this->manager = $manager;
		if($this->pid) {
			throw new ErrorException('请选择根图文素材');
		}

		$articles = [$this->json];
		foreach($this->children as $child) {
			$child->manager = $manager;
			$articles[] = $child->json;
		}

		return $articles;
	}

	/**
	 * 转成接口调用json
	 * @method getJson
	 * @since 0.0.1
	 * @return {string}
	 */
	protected function getJson() {
		$media = $this->thumbMedia;
		
		return [
			'title' => $this->title,
			'author' => $this->author,
			'thumb_media_id' => $media->media_id,
			'show_cover_pic' => $this->show_cover_pic,
			'digest' => $this->digest,
			'content' => $this->content,
			'content_source_url' => $this->content_source_url,
			'thumb_mediaid' => $media->id,
		];
	}

	/**
	 * 获取缩略图媒体
	 * @method getThumbMedia
	 * @since 0.0.1
	 * @return {object}
	 * @example $this->getThumbMedia();
	 */
	protected function getThumbMedia() {
		$media = WechatMedia::findOne(['appid' => $this->manager->wechat->appid, 'materialid' => $this->thumb_materialid, 'expired_at' => 0]);
		if(!$media) {
			$media = WechatMedia::findOne($this->manager->addMaterial($this->thumb_materialid));
		}
		
		return $media;
	}

	/**
	 * 获取子级素材
	 * @method getChildren
	 * @since 0.0.1
	 * @return {array}
	 * @example $this->getChildren();
	 */
	public function getChildren() {
		return $this->hasMany(static::classname(), ['pid' => 'id'])->orderby('list_order, created_at, id');
	}

}
