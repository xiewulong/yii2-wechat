<?php
/*!
 * ueditor action
 * xiewulong <xiewulong@vip.qq.com>
 * https://github.com/xiewulong/yii2-wechat
 * https://raw.githubusercontent.com/xiewulong/yii2-wechat/master/LICENSE
 * create: 2016/2/23
 * update: 2016/2/23
 * version: 0.0.1
 */

namespace yii\wechat\actions;

use Yii;

class UeditorAction extends \yii\xui\UeditorAction {

	public function init() {
		parent::init();

	}

	protected function saveFile() {
		$request = \Yii::$app->request;
		$name = 'upfile';
		$min = 0;
		$max = $this->config['imageMaxSize'];
		$type = 'image';
		$sizes = null;
		$oss = 'images';
		$response = ['state' => '没有文件被上传'];

		if(!empty($name) && !empty($_FILES)) {
			$_file = $_FILES[$name];
			if(!empty($min) && $_file['size'] < $min) {
				$response['state'] = \Yii::t('common', 'File size too small');
			}else if(!empty($max) && $_file['size'] > $max) {
				$response['state'] = \Yii::t('common', 'File size too large');
			}else if(!empty($type) && !in_array($_file['type'], $this->types[$type])) {
				$response['state'] = \Yii::t('common', 'Please upload the right file type');
			}else{
				$manager = \Yii::createObject(\Yii::$app->components[$this->fileupload]);
				$file = $manager->createFile(array_pop(explode('.', $_file['name'])));
				if(move_uploaded_file($_file['tmp_name'], $file['tmp'])) {
					$response['state'] = 'SUCCESS';
					$response['title'] = $_file['name'];
					$response['type'] = $_file['type'];
					$response['size'] = $_file['size'];
					$response['url'] = $response['original'] = $manager->finalFile($file, $oss);
				}
			}
		}

		return $response;
	}
	
}