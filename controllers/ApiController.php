<?php

namespace yii\wechat\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ApiController extends Controller {

	public $enableCsrfValidation = false;

	public $defaultAction = 'public';

	public function behaviors() {
		return [
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'public' => ['get', 'post'],
				],
			],
		];
	}

	public function actionPublic($appid, $echostr = null, $signature = null, $timestamp = null, $nonce = null, $encrypt_type = null, $msg_signature = null) {
		$this->module->manager->setApp($appid);

		//验证消息
		if(!$this->module->checkSignature($signature, $timestamp, $nonce)) {
			throw new NotFoundHttpException(\Yii::t('common', 'Page not found.'));
		}

		//返回服务器地址设置随机字符串
		if($echostr) {
			return $echostr;
		}

		//过滤非消息请求
		if(!$postStr = file_get_contents('php://input')) {
			throw new NotFoundHttpException(\Yii::t('common', 'Page not found.'));
		}

		//获取数据
		libxml_disable_entity_loader(true);
		$postObj = (array) simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

		//确定是否开启安全模式
		$safeMode = $encrypt_type && $msg_signature;

		//安全模式下验证并解密消息
		if($safeMode && (!isset($postObj['Encrypt']) || !($postObj = $this->module->decryptMessage($msg_signature, $timestamp, $nonce, $postObj['Encrypt'])))) {
			throw new NotFoundHttpException(\Yii::t('common', 'Page not found.'));
		}

		//处理数据并获取回复结果
		$response = $this->module->handleMessage($postObj);

		//加密回复消息
		if($safeMode && $response){
			$response = $this->module->encryptMessage($response, $timestamp, $nonce);
		}

		//设置xml格式
		\Yii::$app->response->formatters[Response::FORMAT_XML] = 'yii\wechat\components\XmlResponseFormatter';
		\Yii::$app->response->format = Response::FORMAT_XML;
		return $response;
	}

}
