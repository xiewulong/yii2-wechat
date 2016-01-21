<?php
/*!
 * yii2 extension - wechat
 * xiewulong <xiewulong@vip.qq.com>
 * https://github.com/xiewulong/yii2-wechat
 * https://raw.githubusercontent.com/xiewulong/yii2-wechat/master/LICENSE
 * create: 2014/12/30
 * update: 2016/1/21
 * version: 0.0.1
 */

namespace yii\wechat;

use yii\base\ErrorException;
use yii\helpers\Json;
use yii\wechat\models\Wechat;

class Manager {

	//微信api地址
	private $api = 'https://api.weixin.qq.com/cgi-bin/';

	//公众号
	public $wechat;

	//提示信息
	private $messages = false;

	//返回码
	public $errcode = 0;

	//返回码说明
	public $errmsg = null;

	/**
	 * 获取公众号配置信息
	 * @method setAppid
	 * @since 0.0.1
	 * @return {object}
	 * @example \Yii::$app->wechat->setAppid();
	 */
	public function setAppid($appid) {
		if(!$this->wechat = Wechat::findOne($appid)) {
			throw new ErrorException('Without the wechat app');
		}

		return $this;
	}

	/**
	 * 获取微信服务器IP地址
	 * @method getCallbackIp
	 * @since 0.0.1
	 * @return {array}
	 * @example \Yii::$app->wechat->getCallbackIp();
	 */
	public function getCallbackIp() {
		if(empty($this->wechat->ip_list)){
			$this->refreshIpList();
		}

		return $this->wechat->ipListArray;
	}

	/**
	 * 刷新微信服务器IP地址
	 * @method refreshIpList
	 * @since 0.0.1
	 * @return {none}
	 * @example \Yii::$app->wechat->refreshIpList();
	 */
	public function refreshIpList() {
		$data = Json::decode($this->curl($this->getApiUrl('getcallbackip', [
			'access_token' => $this->getAccessToken(),
		])));
		if(isset($data['errcode']) && isset($data['errmsg'])) {
			$this->errcode = $data['errcode'];
			$this->errmsg = $this->getMessage($data['errmsg']);
		}
		if(isset($data['ip_list'])){
			$this->wechat->ip_list = Json::encode($data['ip_list']);
			return $this->wechat->save();
		}
		
		return false;
	}

	/**
	 * 获取接口调用凭据
	 * @method getAccessToken
	 * @since 0.0.1
	 * @return {boolean}
	 * @example \Yii::$app->wechat->getAccessToken();
	 */
	public function getAccessToken() {
		$time = time();
		if(empty($this->wechat->access_token) || $this->wechat->expired_at < $time) {
			$data = Json::decode($this->curl($this->getApiUrl('token', [
				'grant_type' => 'client_credential',
				'appid' => $this->wechat->appid,
				'secret' => $this->wechat->secret,
			])));
			if(isset($data['access_token']) && isset($data['expires_in'])) {
				$this->wechat->access_token = $data['access_token'];
				$this->wechat->expired_at = $time + $data['expires_in'];
				$this->wechat->save();
			} else if(isset($data['errcode']) && isset($data['errmsg'])) {
				$this->errcode = $data['errcode'];
				$this->errmsg = $this->getMessage($data['errmsg']);
			}
		}

		return $this->wechat->access_token;
	}

	/**
	 * 生成随机令牌
	 * @method generateToken
	 * @since 0.0.1
	 * @return {string}
	 * @example \Yii::$app->wechat->generateToken();
	 */
	public function generateToken() {
		return $this->generateRandomString(mt_rand(3, 32));
	}

	/**
	 * 生成随机字符串
	 * @method generateRandomString
	 * @since 0.0.1
	 * @param {int} $len 长度
	 * @return {string}
	 * @example \Yii::$app->wechat->generateRandomString($len);
	 */
	public function generateRandomString($len = 32) {
		$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$max = strlen($chars) - 1;
		
		$strArr = [];
		for($i = 0; $i < $len; $i++){
			$strArr[] = $chars[mt_rand(0, $max)];
		}

		return implode($strArr);
	}

	/**
	 * 获取接口完整访问地址
	 * @method getApiUrl
	 * @since 0.0.1
	 * @param {string} $action 接口动作
	 * @param {array} $query 参数
	 * @return {string}
	 */
	private function getApiUrl($action, $query) {
		return $this->api . $action . (empty($query) ? '' : '?' . http_build_query($query));
	}

	/**
	 * 获取信息
	 * @method getMessage
	 * @since 0.0.1
	 * @return {string}
	 */
	private function getMessage($status) {
		if($this->messages === false) {
			$this->messages = require(__DIR__ . '/messages.php');
		}

		return isset($this->messages[$status]) ? $this->messages[$status] : null;
	}

	/**
	 * curl远程获取数据方法
	 * @method curl
	 * @since 0.0.1
	 * @param {string} $url 请求地址
	 * @param {array|string} [$data=null] post数据
	 * @param {string} [$useragent=null] 模拟浏览器用户代理信息
	 * @return {string}
	 */
	private function curl($url, $data = null, $useragent = null) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if(!empty($data)) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		if(!empty($useragent)) {
			curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		}
		$data = curl_exec($curl);
		curl_close($curl);
		return $data;
	}

}
