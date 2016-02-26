<?php

namespace yii\wechat\models;

interface WechatMessageInterface {

	/**
	 * 自动回复消息
	 * @method autoReply
	 * @since 0.0.1
	 * @param {int} $id 消息id
	 * @return {int}
	 * @example static::autoReply($id);
	 */
	public static function autoReply($id);

}
