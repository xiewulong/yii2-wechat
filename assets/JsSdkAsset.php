<?php
/*!
 * yii - wechat - js sdk
 * xiewulong <xiewulong@vip.qq.com>
 * https://github.com/xiewulong/yii2-wechat
 * https://raw.githubusercontent.com/xiewulong/yii2-wechat/master/LICENSE
 * create: 2017/01/22
 * update: 2017/02/20
 * since: 0.0.1
 */
namespace yii\wechat\assets;

use Yii;
use yii\web\AssetBundle;

class JsSdkAsset extends AssetBundle {

	public $baseUrl = '//res.wx.qq.com/open/js';

	public $js = [
		'jweixin-1.2.0.js',
	];

}
