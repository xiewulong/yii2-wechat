<?php
/*!
 * yii2 extension - wechat
 * xiewulong <xiewulong@vip.qq.com>
 * https://github.com/xiewulong/yii2-wechat
 * https://raw.githubusercontent.com/xiewulong/yii2-wechat/master/LICENSE
 * create: 2014/12/30
 * update: 2014/12/30
 * version: 0.0.1
 */

namespace yii\wechat;

use yii\base\ErrorException;
use yii\helpers\Json;
use yii\wechat\models\Wechat;

class WechatSdk{

	//微信api地址
	private $api = 'https://api.weixin.qq.com/cgi-bin';

	//wechat app
	private $wechat;

	//access_token
	private $accesstoken = false;

	//access_token过期时间
	private $expired_at = 0;

	//提示信息
	private $messages = false;

	/**
	 * 验证
	 * @method getAccessToken
	 * @since 0.0.1
	 * @return {object}
	 * @example Yii::$app->wechat->setAppid();
	 */
	public function setAppid($appid){
		if(!$this->wechat = Wechat::findOne($appid)){
			throw new ErrorException('Without the wechat app');
		}

		return $this;
	}

	/**
	 * 获取accesstoken
	 * @method getAccessToken
	 * @since 0.0.1
	 * @return {string}
	 */
	private function getAccessToken(){
		$time = time();
		if($this->accesstoken === false || $expired_at < $time){
			if(empty($this->wechat->updated_at) || $this->wechat->updated_at + $this->wechat->expires_in < $time){
				$result = Json::decode($this->curl($this->getUrl('token', [
					'grant_type' => 'client_credential',
					'appid' => $this->wechat->appid,
					'secret' => $this->wechat->appsecret,
				])));
				if(isset($result['errcode'])){
					throw new ErrorException($this->getMessage($result['errcode']));
				}
				$this->wechat->access_token = $result['access_token'];
				$this->wechat->expires_in = $result['expires_in'];
				$this->wechat->save();
			}
			$this->accesstoken = $this->wechat->access_token;
			$this->expired_at = $this->wechat->updated_at + $this->wechat->expires_in;
		}

		return $this->accesstoken;
	}

	/**
	 * 获取接口完整访问地址
	 * @method getUrl
	 * @since 0.0.1
	 * @param {string} $action 接口动作
	 * @param {array} $query 参数
	 * @return {string}
	 */
	private function getUrl($action, $query){
		return $this->api . '/' . $action . (empty($query) ? '' : '?' . http_build_query($query));
	}

	/**
	 * 获取信息
	 * @method getMessage
	 * @since 0.0.1
	 * @return {string}
	 */
	private function getMessage($status){
		if($this->messages === false){
			$this->messages = require(__DIR__ . '/messages.php');
		}

		return isset($this->messages[$status]) ? $this->messages[$status] : '';
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
	private function curl($url, $data = null, $useragent = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if(!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		if(!empty($useragent)){
			curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		}
		$data = curl_exec($curl);
		curl_close($curl);
		return $data;
	}

}