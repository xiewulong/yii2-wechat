<?php

namespace yii\wechat\controllers;

use Yii;
use yii\web\Controller;

class DefaultController extends Controller{
	
	public function actionIndex($appid){
		if($echostr = Yii::$app->request->get('echostr')){
			return $this->module->checkSignature($appid) ? $echostr : false;
		}

	}

}