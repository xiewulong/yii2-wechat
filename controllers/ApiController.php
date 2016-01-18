<?php

namespace yii\wechat\controllers;

use Yii;
use yii\web\Controller;

class ApiController extends Controller {

	public $defaultAction = 'public';
	
	public function actionPublic($appid) {
		if($echostr = \Yii::$app->request->get('echostr')) {
			return $this->module->checkSignature($appid) ? $echostr : false;
		}
	}

}
