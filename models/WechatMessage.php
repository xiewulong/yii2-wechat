<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

class WechatMessage extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat_message}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	 * 获取自动回复消息
	 * @method autoReply
	 * @since 0.0.1
	 * @param {string} [$messageClass] 调用公众号消息类回复(优先级最高), 否则默认规则回复
	 * @return {array}
	 * @example $this->autoReply($messageClass);
	 */
	public function autoReply($messageClass = null) {
		if($messageClass) {
			return ($message = static::findOne($messageClass::reply($this->id))) && $message->pid == $this->id ? $message->replyFormat : null;
		}

		$reply = [];
		switch($this->msg_type) {
			case 'text':
				$reply = WechatMessageRule::keywords($this->appid, $this->content);
				break;
			case 'event':
				switch($this->event) {
					case 'unsubscribe':
						break;
					case 'subscribe':
						$reply = WechatMessageRule::beAdded($this->appid);
						break;
				}
				break;
		}

		if($reply) {
			$message = new static;
			foreach($reply as $k => $v) {
				$message[$k] = $v;
			}
			$message->appid = $this->appid;
			$message->type = 2;
			$message->pid = $this->id;
			$message->to_user_name = $this->from_user_name;
			$message->from_user_name = $this->to_user_name;
			if($message->save()) {
				return $message->replyFormat;
			}
		}

		return null;
	}

	/**
	 * 获取回复格式
	 * @method getReplyFormat
	 * @since 0.0.1
	 * @return {array}
	 * @example $this->getReplyFormat();
	 */
	public function getReplyFormat() {
		if($this->type != 2) {
			return null;
		}

		$reply = [
			'ToUserName' => $this->to_user_name,
			'FromUserName' => $this->from_user_name,
			'CreateTime' => $this->created_at,
			'MsgType' => $this->msg_type,
		];
		switch($this->msg_type) {
			case 'text':
				$reply['Content'] = $this->content;
				break;
			case 'image':
				$reply['Image']['MediaId'] = $this->media_id;
				break;
			case 'voice':
				$reply['Voice']['MediaId'] = $this->media_id;
				break;
			case 'video':
				$reply['Video'] = [
					'MediaId' => $this->media_id,
					'Title' => $this->title,
					'Description' => $this->description,
				];
				break;
			case 'music':
				$reply['Music'] = [
					'Title' => $this->title,
					'Description' => $this->description,
					'MusicUrl' => $this->music_url,
					'HQMusicUrl' => $this->hq_music_url,
					'ThumbMediaId' => $this->thumb_media_id,
				];
				break;
			case 'news':
				$articles = $this->articleList;
				$reply['ArticleCount'] = count($articles);
				$reply['Articles'] = [];
				foreach($articles as $article) {
					$reply['Articles'][] = [
						'Title' => $article['title'],
						'Description' => $article['description'],
						'PicUrl' => $article['pic_url'],
						'Url' => $article['url'],
					];
				}
				break;
			default:
				return null;
				break;
		}

		return $reply;
	}

	/**
	 * 获取多图文消息列表
	 * @method getArticleList
	 * @since 0.0.1
	 * @return {array}
	 * @example $this->getArticleList();
	 */
	public function getArticleList() {
		return $this->articles ? Json::decode($this->articles) : [];
	}

}
