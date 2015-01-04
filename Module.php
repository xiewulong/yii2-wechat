<?php

namespace yii\wechat;

use Yii;
use yii\base\ErrorException;
use yii\wechat\models\Wechat;

class Module extends yii\base\Module{

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
