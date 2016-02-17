<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class WechatMaterial extends ActiveRecord {

	private $tmp;

	public static function tableName() {
		return '{{%wechat_material}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	 * 获取本地文件
	 * @method getLocalFile
	 * @since 0.0.1
	 * @return {string}
	 * @example $this->getLocalFile();
	 */
	public function getLocalFile() {
		return preg_match('/^(http|https):\/\//', $this->url) ? $this->downloadFile() : \Yii::getAlias('@webroot' . $this->url);
	}

	/**
	 * 清除缓存文件
	 * @method cleanTmp
	 * @since 0.0.1
	 * @return {string}
	 * @example $this->cleanTmp();
	 */
	public function cleanTmp() {
		$this->tmp and unlink($this->tmp);
	}

	/**
	 * 下载文件
	 * @method downloadFile
	 * @since 0.0.1
	 * @return {string}
	 */
	private function downloadFile() {
		$content = @file_get_contents($this->url);
		if($content) {
			$this->tmp = \Yii::getAlias('@runtime' . DIRECTORY_SEPARATOR . md5($this->url) . '.' . pathinfo($this->url, PATHINFO_EXTENSION));
			file_put_contents($this->tmp, $content);
		}

		return $this->tmp;
	}

}
