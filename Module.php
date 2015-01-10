<?php

namespace yii\wechat;

use Yii;
use yii\base\ErrorException;
use yii\wechat\models\Wechat;

class Module extends \yii\base\Module{

	public $defaultRoute = 'api';

	public $defaultComponent = 'wechat';

	public $manager;

	public function init(){
		parent::init();

		$this->manager = Yii::createObject(Yii::$app->components[$this->defaultComponent]);
	}

	public function checkSignature($appid){
		if($wechat = Wechat::findOne($appid)){
			$token = $wechat->token;
		}else{
			return false;
		}

		$tmpArr = [$token, Yii::$app->request->get('timestamp'), Yii::$app->request->get('nonce')];
		sort($tmpArr, SORT_STRING);

		return sha1(implode($tmpArr)) == Yii::$app->request->get('signature');
	}
	
}
