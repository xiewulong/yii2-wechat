<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

class WechatMessageRule extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat_message_rule}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	 * 获取
	 * @method keywords
	 * @since 0.0.1
	 * @param {string} $appid AppID
	 * @param {string} $content 文本消息内容
	 * @return {array}
	 * @example static::keywords($appid, $content);
	 */
	public static function keywords($appid, $content) {
		$rule = static::findOne(['appid' => $appid, 'type' => 'beadded']);

		return $rule ? $rule->messageFormat : [];
	}

	/**
	 * 获取被添加自动回复数据
	 * @method beAdded
	 * @since 0.0.1
	 * @param {string} $appid AppID
	 * @return {array}
	 * @example static::beAdded($appid);
	 */
	public static function beAdded($appid) {
		$rule = static::findOne(['appid' => $appid, 'type' => 'beadded']);

		return $rule ? $rule->messageFormat : [];
	}

	/**
	 * 获取回复消息格式
	 * @method getMessageFormat
	 * @since 0.0.1
	 * @return {array}
	 * @example $this->getMessageFormat();
	 */
	public function getMessageFormat() {
		$message = ['msg_type' => $this->msg_type];
		switch($this->msg_type) {
			case 'text':
				$message['content'] = $this->content;
				break;
			case 'video':
				$message['title'] = $this->title;
				$message['description'] = $this->description;
			case 'image':
			case 'voice':
				$message['media_id'] = $this->materialMedia->media_id;
				$message['media_url'] = $this->materialMedia->material->url;
				break;
			case 'music':
				$message['title'] = $this->title;
				$message['description'] = $this->description;
				$message['music_url'] = $this->music_url;
				$message['hq_music_url'] = $this->hq_music_url;
				$message['thumb_media_id'] = $this->thumbMaterialMedia->media_id;
				$message['thumb_media_url'] = $this->thumbMaterialMedia->material->url;
				break;
			case 'news':
				$pic_urls = $this->newsMedia->thumbUrlList;
				$urls = $this->newsMedia->urlList;
				$articles = [];
				foreach($this->newsMedia->news->items as $index => $item) {
					if(!isset($pic_urls[$index]) || !isset($urls[$index])) {
						break;
					}
					$articles[] = [
						'title' => $item->title,
						'description' => $item->digest,
						'pic_url' => $pic_urls[$index],
						'url' => $urls[$index],
					];
				}
				$message['articles'] = Json::encode($articles);
				break;
			default:
				return [];
				break;
		}

		return $message;
	}

	/**
	 * 获取缩略图的素材媒体
	 * @method getThumbMaterialMedia
	 * @since 0.0.1
	 * @return {object}
	 * @example $this->getThumbMaterialMedia();
	 */
	public function getThumbMaterialMedia() {
		return $this->hasOne(WechatMaterialMedia::classname(), ['id' => 'thumb_material_media_id']);
	}

	/**
	 * 获取素材媒体
	 * @method getMaterialMedia
	 * @since 0.0.1
	 * @return {object}
	 * @example $this->getMaterialMedia();
	 */
	public function getMaterialMedia() {
		return $this->hasOne(WechatMaterialMedia::classname(), ['id' => 'material_media_id']);
	}

	/**
	 * 获取图文消息
	 * @method getNewsMedia
	 * @since 0.0.1
	 * @return {object}
	 * @example $this->getNewsMedia();
	 */
	public function getNewsMedia() {
		return $this->hasOne(WechatNewsMedia::classname(), ['id' => 'news_media_id']);
	}

}
