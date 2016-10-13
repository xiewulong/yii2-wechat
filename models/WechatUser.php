<?php

namespace yii\wechat\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\components\ActiveRecord;
use yii\helpers\Json;

/**
 * Wechat user model
 *
 * @since 0.0.1
 * @property {integer} $id
 * @property {integer} $user_id
 * @property {string} $appid
 * @property {string} $openid
 * @property {string} $unionid
 * @property {string} $subscribe
 * @property {string} $subscribed_at
 * @property {string} $nickname
 * @property {string} $remark
 * @property {integer} $groupid
 * @property {integer} $sex
 * @property {string} $country
 * @property {string} $province
 * @property {string} $city
 * @property {string} $language
 * @property {string} $headimgurl
 * @property {string} $access_token
 * @property {integer} $access_token_expired_at
 * @property {string} $refresh_token
 * @property {integer} $created_at
 * @property {integer} $updated_at
 */
class WechatUser extends ActiveRecord {

	private $_privilege;

	public static function tableName() {
		return '{{%wechat_user}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	 * Return group
	 *
	 * @since 0.0.1
	 * @return {object}
	 */
	public function getGroup() {
		return $this->hasOne(WechatUserGroup::classname(), [
			'appid' => 'appid',
			'gid' => 'groupid',
		]);
	}


	/**
	 * Return nickname
	 *
	 * @since 0.0.1
	 * @return {string}
	 */
	public function getName() {
		return urldecode($this->nickname);
	}


	/**
	 * Set nickname
	 *
	 * @since 0.0.1
	 * @return {string}
	 */
	public function setName($value) {
		$this->nickname = urlencode($value);
	}


	/**
	 * Return privilege array
	 *
	 * @since 0.0.1
	 * @return {string}
	 */
	public function getPrivilegeList() {
		if($this->_privilege === null) {
			$this->_privilege = Json::decode($this->privilege);
			if($this->_privilege === null) {
				$this->_privilege = [];
			}
		}

		return $this->_privilege;
	}


	/**
	 * Set privilege json
	 *
	 * @since 0.0.1
	 * @return {string}
	 */
	public function setPrivilegeList($value) {
		$this->privilege = Json::encode($value);
		$this->_privilege = $value;
	}

}
