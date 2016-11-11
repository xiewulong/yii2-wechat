<?php
/*!
 * yii2 extension - wechat - module
 * xiewulong <xiewulong@vip.qq.com>
 * https://github.com/xiewulong/yii2-wechat
 * https://raw.githubusercontent.com/xiewulong/yii2-wechat/master/LICENSE
 * create: 2016/1/21
 * update: 2016/3/17
 * version: 0.0.1
 */

namespace yii\wechat;

use Yii;
use yii\base\ErrorException;
use yii\wechat\components\XmlResponseFormatter;
use yii\wechat\models\WechatMessage;

class Module extends \yii\base\Module {

	public $defaultRoute = 'api';

	public $defaultComponent = 'wechat';

	public $manager;

	//公众号消息调用类
	public $messageClass;

	private $key;

	public function init() {
		parent::init();

		$this->manager = \Yii::createObject(\Yii::$app->components[$this->defaultComponent]);
	}

	/**
	 * 签名
	 * @method sign
	 * @since 0.0.1
	 * @param {array} $arr 数据数组
	 * @return {string}
	 */
	private function sign($arr) {
		sort($arr, SORT_STRING);

		return sha1(implode($arr));
	}

	/**
	 * 验证签名
	 * @method checkSignature
	 * @since 0.0.1
	 * @param {string} $signature 加密签名
	 * @param {string} $timestamp 时间戳
	 * @param {string} $nonce 随机数
	 * @return {boolean}
	 * @example $this->checkSignature($signature, $timestamp, $nonce);
	 */
	public function checkSignature($signature, $timestamp, $nonce) {
		return \Yii::$app->security->compareString($this->sign([$this->manager->app->token, $timestamp, $nonce]), $signature);
	}

	/**
	 * 验证消息体签名
	 * @method checkMsgSignature
	 * @since 0.0.1
	 * @param {string} $msg_signature 消息体加密签名
	 * @param {string} $timestamp 时间戳
	 * @param {string} $nonce 随机数
	 * @param {string} $encrypt 密文消息体
	 * @return {boolean}
	 */
	private function checkMsgSignature($msg_signature, $timestamp, $nonce, $encrypt) {
		return \Yii::$app->security->compareString($this->sign([$this->manager->app->token, $timestamp, $nonce, $encrypt]), $msg_signature);
	}

	/**
	 * 对解密后的明文进行补位删除
	 * @method decode
	 * @since 0.0.1
	 * @param {string} $text 解密后的明文
	 * @return {string}
	 */
	private function decode($text) {
		$pad = ord(substr($text, -1));
		if ($pad < 1 || $pad > 32) {
			$pad = 0;
		}

		return substr($text, 0, (strlen($text) - $pad));
	}

	/**
	 * 对密文进行解密
	 * @method decrypt
	 * @since 0.0.1
	 * @param {string} $encrypted 需要解密的密文
	 * @return {string|boolean}
	 */
	private function decrypt($encrypted) {
		$this->key = base64_decode($this->manager->app->aeskey . '=');

		//使用BASE64对需要解密的字符串进行解码
		$ciphertext_dec = base64_decode($encrypted);
		$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		$iv = substr($this->key, 0, 16);
		mcrypt_generic_init($module, $this->key, $iv);

		//解密
		$decrypted = mdecrypt_generic($module, $ciphertext_dec);
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);

		//去除补位字符
		$result = $this->decode($decrypted);

		//去除16位随机字符串,网络字节序和AppId
		if(strlen($result) < 16) {
			return false;
		}

		$content = substr($result, 16, strlen($result));
		$len_list = unpack('N', substr($content, 0, 4));
		$xml_len = $len_list[1];
		$xml_content = substr($content, 4, $xml_len);
		$from_appid = substr($content, $xml_len + 4);

		return $from_appid == $this->manager->app->appid ? $xml_content : false;
	}

	/**
	 * 验证消息体签名, 并获取解密后的消息
	 * @method decryptMessage
	 * @since 0.0.1
	 * @param {string} $msg_signature 消息体加密签名
	 * @param {string} $timestamp 时间戳
	 * @param {string} $nonce 随机数
	 * @param {string} $encrypt 密文消息体
	 * @return {string|boolean}
	 * @example $this->decryptMessage($msg_signature, $timestamp, $nonce, $encrypt);
	 */
	public function decryptMessage($msg_signature, $timestamp, $nonce, $encrypt) {
		if(!$this->checkMsgSignature($msg_signature, $timestamp, $nonce, $encrypt)) {
			return false;
		}

		$result = $this->decrypt($encrypt);

		return $result ? (array) simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA) : false;
	}

	/**
	 * 处理消息
	 * @method handleMessage
	 * @since 0.0.1
	 * @param {string} $postObj 消息
	 * @return {array}
	 * @example $this->handleMessage($postObj);
	 */
	public function handleMessage($postObj) {
		if((isset($postObj['MsgId']) && WechatMessage::findOne(['msg_id' => $postObj['MsgId']])) || WechatMessage::findOne(['appid' => $this->manager->app->appid, 'to_user_name' => $postObj['ToUserName'], 'from_user_name' => $postObj['FromUserName'], 'create_time' => $postObj['CreateTime']])) {
			return null;
		}

		$message = new WechatMessage;
		$message->appid = $this->manager->app->appid;
		$message->to_user_name = $postObj['ToUserName'];
		$message->from_user_name = $postObj['FromUserName'];
		$message->create_time = $postObj['CreateTime'];
		$message->msg_type = $postObj['MsgType'];

		if(isset($postObj['MsgId'])) {
			$message->msg_id = $postObj['MsgId'];
		}

		switch($message->msg_type) {
			case 'text':
				$message->content = $postObj['Content'];
				break;
			case 'image':
				$message->media_id = $postObj['MediaId'];
				$message->pic_url = $postObj['PicUrl'];
				break;
			case 'voice':
				$message->media_id = $postObj['MediaId'];
				$message->format = $postObj['Format'];
				if(isset($postObj['Recognition'])) {
					$message->recognition = $postObj['Recognition'];
				}
				break;
			case 'video':
			case 'shortvideo':
				$message->media_id = $postObj['MediaId'];
				$message->thumb_media_id = $postObj['ThumbMediaId'];
				break;
			case 'location':
				$message->location_x = $postObj['Location_X'];
				$message->location_y = $postObj['Location_Y'];
				$message->scale = $postObj['Scale'];
				$message->label = $postObj['Label'];
				break;
			case 'link':
				$message->title = $postObj['Title'];
				$message->description = $postObj['Description'];
				$message->url = $postObj['Url'];
				break;
			case 'event':
				$message->event = $postObj['Event'];
				switch($message->event) {
					case 'unsubscribe':
						break;
					case 'subscribe':
						if(isset($postObj['EventKey']) && isset($postObj['Ticket'])) {
							$message->event_key = $postObj['EventKey'];
							$message->ticket = $postObj['Ticket'];
						}
						break;
					case 'SCAN':
						$message->event_key = $postObj['EventKey'];
						$message->ticket = $postObj['Ticket'];
						break;
					case 'LOCATION':
						$message->latitude = $postObj['Latitude'];
						$message->longitude = $postObj['Longitude'];
						$message->precision = $postObj['Precision'];
						break;
					case 'CLICK':
						$message->event_key = $postObj['EventKey'];
						break;
					case 'VIEW':
						$message->event_key = $postObj['EventKey'];
						if(isset($postObj['MenuID'])) {
							$message->menu_id = $postObj['MenuID'];
						}
						break;
					case 'scancode_push':
					case 'scancode_waitmsg':
						$message->event_key = $postObj['EventKey'];
						if(isset($postObj['ScanCodeInfo'])) {
							$message->scan_type = $postObj['ScanCodeInfo']['ScanType'];
							$message->scan_result = $postObj['ScanCodeInfo']['ScanResult'];
						}
						break;
					case 'pic_sysphoto':
					case 'pic_photo_or_album':
					case 'pic_weixin':
						$message->event_key = $postObj['EventKey'];
						if(isset($postObj['SendPicsInfo'])) {
							$message->count = $postObj['SendPicsInfo']['Count'];
							$message->pic_list = $postObj['SendPicsInfo']['PicList'];
						}
						break;
					case 'location_select':
						$message->event_key = $postObj['EventKey'];
						if(isset($postObj['SendLocationInfo'])) {
							$message->location_x = $postObj['SendLocationInfo']['Location_X'];
							$message->location_y = $postObj['SendLocationInfo']['Location_Y'];
							$message->scale = $postObj['SendLocationInfo']['Scale'];
							$message->label = $postObj['SendLocationInfo']['Label'];
							$message->poiname = $postObj['SendLocationInfo']['Poiname'];
						}
						break;
				}
				break;
		}

		return $message->save() ? $message->autoReply($this->messageClass) : null;
	}

	/**
	 * 对需要加密的明文进行填充补位
	 * @method encode
	 * @since 0.0.1
	 * @param {string} $text 需要进行填充补位操作的明文
	 * @return {string}
	 */
	private function encode($text) {
		$block_size = 32;
		$text_length = strlen($text);

		//计算需要填充的位数
		$amount_to_pad = $block_size - ($text_length % $block_size);
		if ($amount_to_pad == 0) {
			$amount_to_pad = $block_size;
		}

		//获得补位所用的字符
		$pad_chr = chr($amount_to_pad);
		$tmp = "";
		for ($index = 0; $index < $amount_to_pad; $index++) {
			$tmp .= $pad_chr;
		}

		return $text . $tmp;
	}

	/**
	 * 对密文进行加密
	 * @method encrypt
	 * @since 0.0.1
	 * @param {string} $text 需要加密的明文
	 * @return {string}
	 */
	private function encrypt($text) {
		if(!$this->key) {
			$this->key = base64_decode($this->manager->app->aeskey . '=');
		}

		//获得16位随机字符串，填充到明文之前
		$random = $this->manager->generateRandomString(16);
		$text = $random . pack("N", strlen($text)) . $text . $this->manager->app->appid;

		//网络字节序
		$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		$iv = substr($this->key, 0, 16);

		//使用自定义的填充方式对明文进行补位填充
		$text = $this->encode($text);
		mcrypt_generic_init($module, $this->key, $iv);

		//加密
		$encrypted = mcrypt_generic($module, $text);
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);

		//使用BASE64对加密后的字符串进行编码
		return base64_encode($encrypted);
	}

	/**
	 * 加密回复消息
	 * @method encryptMessage
	 * @since 0.0.1
	 * @param {string} $message 回复消息
	 * @param {string} $timestamp 时间戳
	 * @param {string} $nonce 随机数
	 * @return {array}
	 * @example $this->encryptMessage($message, $timestamp, $nonce);
	 */
	public function encryptMessage($message, $timestamp, $nonce) {
		$encrypt = $this->encrypt(XmlResponseFormatter::formatData($message));

		return [
			'Encrypt' => $encrypt,
			'MsgSignature' => $this->sign([$this->manager->app->token, $timestamp, $nonce, $encrypt]),
			'TimeStamp' => $timestamp,
			'Nonce' => $nonce,
		];
	}

}
