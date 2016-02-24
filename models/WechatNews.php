<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\base\ErrorException;

class WechatNews extends ActiveRecord {

	protected $manager;

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
	 * @param {object} [&$manager] wechat扩展对象
	 * @return {array}
	 * @example $this->getArticles($manager);
	 */
	public function getArticle(&$manager = null) {
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
	 * @param {object} [&$manager] wechat扩展对象
	 * @return {array}
	 * @example $this->getArticles($manager);
	 */
	public function getArticles(&$manager = null) {
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
		$json = [
			'title' => $this->title,
			'author' => $this->author,
			'show_cover_pic' => $this->show_cover_pic,
			'digest' => $this->digest,
			'content_source_url' => $this->content_source_url,
		];
		if($this->manager) {
			$media = $this->thumbMedia;
			$json['thumb_media_id'] = $media->media_id;
			$json['thumb_mediaid'] = $media->id;
			$json['content'] = $this->wechatContent;
		} else {
			$json['thumb_url'] = $this->thumbMaterial->url;
			$json['content'] = $this->content;
		}
		
		return $json;
	}

	/**
	 * 转换微信格式content
	 * @method getWechatContent
	 * @since 0.0.1
	 * @return {string}
	 * @example $this->getWechatContent();
	 */
	protected function getWechatContent() {
		$content = $this->content;
		if(preg_match_all('/<img.*?[\/]?>/i', $content, $imgs)) {
			$urls = [];
			foreach($imgs[0] as $img) {
				if(preg_match_all('/(?<=src|_src|data-src)(?:\s*=\s*")(.*?)(?=")/i', $img, $srcs) && isset($srcs[1]) && $srcs[1]) {
					$urls = array_merge($urls, $srcs[1]);
				}
			}
			if($urls) {
				$urls = array_unique($urls);
				foreach($urls as $url) {
					$image = WechatNewsImage::findOne($this->manager->addNewsImage($url));
					if($image) {
						$content = str_replace($image->url_source, $image->url, $content);
					}
				}
			}
		}

		echo $this->content;
		return $content;
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
	 * 获取封面图片素材
	 * @method getThumbMaterial
	 * @since 0.0.1
	 * @return {object}
	 * @example $this->getThumbMaterial();
	 */
	public function getThumbMaterial() {
		return $this->hasOne(WechatMaterial::classname(), ['id' => 'thumb_materialid']);
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
