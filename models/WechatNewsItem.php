<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class WechatNewsItem extends ActiveRecord {

	//wechat扩展对象
	public $manager;

	public static function tableName() {
		return '{{%wechat_news_item}}';
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
			'index' => $this->index,
			'thumb_material_media_id' => $article['thumb_material_media_id'],
			'articles' => $article,
		];

	}

	/**
	 * 转成接口调用json
	 * @method getJson
	 * @since 0.0.1
	 * @return {string}
	 */
	public function getJson() {
		$json = [
			'title' => $this->title,
			'author' => $this->author,
			'show_cover_pic' => $this->show_cover_pic,
			'digest' => $this->digest,
			'content_source_url' => $this->content_source_url,
		];
		if($this->manager) {
			$materialMedia = $this->thumbMaterialMedia;
			$json['thumb_media_id'] = $materialMedia->media_id;
			$json['thumb_material_media_id'] = $materialMedia->id;
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

		return $content;
	}

	/**
	 * 获取缩略图媒体
	 * @method getThumbMaterialMedia
	 * @since 0.0.1
	 * @return {object}
	 * @example $this->getThumbMaterialMedia();
	 */
	protected function getThumbMaterialMedia() {
		$media = WechatMaterialMedia::findOne(['appid' => $this->manager->app->appid, 'material_id' => $this->thumb_material_id, 'expired_at' => 0]);
		if(!$media) {
			$media = WechatMaterialMedia::findOne($this->manager->addMaterial($this->thumb_material_id));
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
		return $this->hasOne(WechatMaterial::classname(), ['id' => 'thumb_material_id']);
	}

	/**
	 * 获取素材项索引, 从0开始计
	 * @method getIndex
	 * @since 0.0.1
	 * @return {object}
	 * @example $this->getIndex();
	 */
	public function getIndex() {
		return static::find()->where("news_id = $this->news_id")->andWhere("list_order < $this->list_order or (list_order = $this->list_order and created_at < $this->created_at) or (list_order = $this->list_order and created_at = $this->created_at and id < $this->id)")->count();
	}

}
