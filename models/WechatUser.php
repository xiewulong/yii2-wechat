<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class WechatUser extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat_user}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	 * 获取用户分组
	 * @method getGroup
	 * @since 0.0.1
	 * @return {object}
	 * @example $this->getGroup();
	 */
	public function getGroup() {
		return $this->hasOne(WechatUserGroup::classname(), ['appid' => 'appid', 'gid' => 'groupid']);
	}

	/**
	 * 获取用户昵称
	 * @method getName
	 * @since 0.0.1
	 * @return {string}
	 * @example $this->getName();
	 */
	public function getName() {
		return urldecode($this->nickname);
	}

}
