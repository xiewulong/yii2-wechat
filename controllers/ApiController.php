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
		if($echostr = \Yii::$app->request->get('echostr')) {
			return $this->module->checkSignature($appid) ? $echostr : null;
		}

		/*
		if(!isset($GLOBALS["HTTP_RAW_POST_DATA"])){
			throw new NotFoundHttpException(\Yii::t('common', 'Page not found.'));
		}
		*/
/*
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		libxml_disable_entity_loader(true);
		$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

		$fromUsername = $postObj->FromUserName;
		$toUsername = $postObj->ToUserName;
		$keyword = trim($postObj->Content);
		$time = time();
		$textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					<FuncFlag>0</FuncFlag>
					</xml>";             
		if(!empty( $keyword ))
		{
			$msgType = "text";
			$contentStr = "Welcome to wechat world!";
			$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
			echo $resultStr;
		}else{
			echo "Input something...";
		}
		$response = [
			'ToUserName' => '<![CDATA[' . $fromUsername . ']]>',
			'FromUserName' => '<![CDATA[' . $toUsername . ']]>',
			'CreateTime' => $time,
			'msgType' => '<![CDATA[' . $msgType . ']]>',
			'Content' => '<![CDATA[' . $contentStr . ']]>',
			'FuncFlag' => 0,
		];
*/
		$aaa = 'sdfdsfsdf';
		$response = [
			'ToUserName' => (string)$aaa,
			'FromUserName' => 222,
			'CreateTime' => 333,
			'msgType' => 444,
			'Content' => 555,
			'FuncFlag' => 0,
		];
		\Yii::$app->response->formatters[Response::FORMAT_XML] = [
			'class' => 'yii\web\XmlResponseFormatter',
			'rootTag' => 'xml',
		];
		\Yii::$app->response->format = Response::FORMAT_XML;
		return $response;
	}

}
