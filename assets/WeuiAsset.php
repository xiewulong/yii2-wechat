<?php
/*!
 * weui asset
 * xiewulong <xiewulong@vip.qq.com>
 * https://github.com/xiewulong/yii2-wechat
 * https://raw.githubusercontent.com/xiewulong/yii2-wechat/master/LICENSE
 * create: 2016/2/23
 * update: 2016/2/23
 * version: 0.0.1
 */

namespace wechat\assets;

use Yii;
use yii\web\AssetBundle;

class WeuiAsset extends AssetBundle {

	public $sourcePath = '@bower/weui/dist/style/';

	public $css = [
		'weui.min.css',
	];

}
