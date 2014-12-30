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
use yii\wechat\models\Wxapp;

class Wechat{

	//appid
	public $appid;

	//appsecret
	public $appsecret;

	//微信api地址
	private $api = 'https://api.weixin.qq.com/cgi-bin';

	//access token
	private $accesstoken = false;

	//access token过期时间
	private $expired_at = 0;

	//提示信息
	private $messages = false;

	/**
	 * 获取accesstoken
	 * @method getAccessToken
	 * @since 0.0.1
	 * @return {string}
	 * @example Yii::$app->sms->send();
	 */
	public function getAccessToken(){
		
	}

	/**
	 * 获取accesstoken
	 * @method getAccessToken
	 * @since 0.0.1
	 * @return {string}
	 * @example Yii::$app->sms->send();
	 */
	private function getAccessToken(){
		if(empty($this->appid) || empty($this->appsecret)){
			throw new ErrorException('Appid and appsecret must be required');
		}
		$time = time();
		if($this->accesstoken === false || $expired_at < $time){
			$wxapp = Wxapp::findOne($this->appid);
			if(!$wxapp){
				$wxapp = new Wxapp;
				$wxapp->appid = $this->appid;
			}
			if(empty($wxapp->updated_at) || $wxapp->updated_at + $wxapp->expires_in < $time){
				$result = Json::decode($this->curl($this->getUrl('token', [
					'grant_type' => 'client_credential',
					'appid' => $this->appid,
					'secret' => $this->appsecret,
				])));
				if(isset($result['errcode'])){
					throw new ErrorException($this->getMessage($result['errcode']));
				}
				$wxapp->access_token = $result['access_token'];
				$wxapp->expires_in = $result['expires_in'];
				$wxapp->save();
			}
			$this->accesstoken = $wxapp->access_token;
			$this->expired_at = $wxapp->updated_at + $wxapp->expires_in;
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
	 * @return {string} 返回获取的数据
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