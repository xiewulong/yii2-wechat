<?php

namespace yii\wechat\controllers;

use Yii;
use yii\web\Controller;

class DefaultController extends Controller{
	
	public function actionIndex(){
		echo 1;
		echo $this->module->param;
	}

}