<?php

namespace yii\wechat\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class WechatMenu extends ActiveRecord {

	public static function tableName() {
		return '{{%wechat_menu}}';
	}

	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	 * 获取自定义菜单
	 * @method getMenu
	 * @since 0.0.1
	 * @param {string} $appid AppID
	 * @param {int} [$pid=0] 父菜单id
	 * @return {array}
	 * @example static::getMenu($appid, $pid);
	 */
	public static function getMenu($appid, $pid = 0) {
		$button = static::find()->where(['appid' => $appid, 'conditional' => 0, 'pid' => $pid])->select('id, type, name, key, url, media_id')->orderby('list_order, created_at')->asArray()->all();

		foreach($button as $index => $_button) {
			$button[$index]['sub_button'] = static::getMenu($appid, $_button['id']);
			unset($_button['id']);
		}

		return $button;
	}

	/**
	 * 创建自定义(个性化)菜单
	 * @method createMenu
	 * @since 0.0.1
	 * @param {string} $appid AppID
	 * @param {array} $postData 菜单数据
	 * @return {boolean}
	 * @example static::createMenu($appid, $postData);
	 */
	public static function createMenu($appid, $postData) {
		$conditional = isset($postData['matchrule']);
		if(!$conditional) {
			static::deleteAll(['appid' => $appid, 'conditional' => 0]);
		}

		return static::addMenu($appid, $postData['button'], isset($postData['menuid']) ? $postData['menuid'] : null, $conditional ? $postData['matchrule'] : []);
	}

	/**
	 * 添加自定义(个性化)菜单
	 * @method addMenu
	 * @since 0.0.1
	 * @param {string} $appid AppID
	 * @param {array} $button 菜单数据
	 * @param {int} [$menuid] 菜单id
	 * @param {array} [$matchrule=[]] 菜单匹配规则
	 * @param {int} [$pid=0] 父菜单id
	 * @return {boolean}
	 * @example static::addMenu($appid, $button, $menuid, $matchrule, $pid);
	 */
	public static function addMenu($appid, $button, $menuid = null, $matchrule = [], $pid = 0) {
		foreach($button as $index => $_menu) {
			$menu = new static;
			$menu->appid = $appid;
			$menu->conditional = $matchrule ? 1 : 0;
			$menu->pid = $pid;
			$menu->menuid = $menuid;
			$menu->type = $_menu['type'];
			$menu->name = $_menu['name'];
			if(isset($_menu['key'])) {
				$menu->key = $_menu['key'];
			}
			if(isset($_menu['url'])) {
				$menu->url = $_menu['url'];
			}
			if(isset($_menu['media_id'])) {
				$menu->media_id = $_menu['media_id'];
			}
			foreach($matchrule as $k => $v) {
				$menu[$k] = $v;
			}
			$menu->list_order = $index;
			if($menu->save() && isset($_menu['sub_button']) && $_menu['sub_button']) {
				static::createMenu($appid, $_menu['sub_button'], $menuid, $matchrule, $menu->id);
			}
		}

		return true;
	}

}
