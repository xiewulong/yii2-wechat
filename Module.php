<?php

namespace yii\wechat;

use Yii;
use yii\base\ErrorException;
use yii\wechat\models\Wechat;

class Module extends \yii\base\Module {

	public $defaultRoute = 'api';

	public $defaultComponent = 'wechat';

	public $manager;

	public function init() {
		parent::init();

		$this->manager = \Yii::createObject(Yii::$app->components[$this->defaultComponent]);
	}

	public function checkSignature($appid) {
		$this->manager->setAppid($appid);
		
		$tmpArr = [$this->manager->wechat->token, \Yii::$app->request->get('timestamp'), \Yii::$app->request->get('nonce')];
		sort($tmpArr, SORT_STRING);

		return \Yii::$app->security->compareString(sha1(implode($tmpArr)), \Yii::$app->request->get('signature'));
	}
	
}
