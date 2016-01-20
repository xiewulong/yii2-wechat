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

	public function behaviors(){
		return [
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'public' => ['get', 'post'],
				],
			],
		];
	}
	
	public function actionPublic($appid) {
		$this->module->manager->setAppid($appid);

		//服务器地址设置验证
		if($echostr = \Yii::$app->request->get('echostr')) {
			return $this->module->checkSignature() ? $echostr : null;
		}

		//过滤非消息请求
		if(!isset($GLOBALS["HTTP_RAW_POST_DATA"])){
			throw new NotFoundHttpException(\Yii::t('common', 'Page not found.'));
		}

		//获取数据
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		libxml_disable_entity_loader(true);
		$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

		//处理数据并获取返回结果
		//$response = $this->module->handleMessage($postObj);

		//debug
		$fromUsername = $postObj->FromUserName;
		$toUsername = $postObj->ToUserName;
		$response = [
			'ToUserName' => (string) $fromUsername,
			'FromUserName' => (string) $toUsername,
			'CreateTime' => time(),
			'MsgType' => 'text',
			'Content' => 'FromUserName: ' . $fromUsername . ', ToUserName: ' . $toUsername . ', Content: ' . $postObj->Content,
		];

		//设置xml格式
		\Yii::$app->response->formatters[Response::FORMAT_XML] = 'yii\wechat\components\XmlResponseFormatter';
		\Yii::$app->response->format = Response::FORMAT_XML;
		return $response;
	}

}
