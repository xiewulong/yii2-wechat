<?php

namespace yii\wechat\models;

interface WechatMessageInterface {

	/**
	 * 回复消息
	 * @method reply
	 * @since 0.0.1
	 * @param {int} $id 消息id
	 * @return {int}
	 * @example static::reply($id);
	 */
	public static function reply($id);

}
