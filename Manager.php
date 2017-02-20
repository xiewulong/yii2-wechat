<?php
/*!
 * yii2 extension - wechat
 * xiewulong <xiewulong@vip.qq.com>
 * https://github.com/xiewulong/yii2-wechat
 * https://raw.githubusercontent.com/xiewulong/yii2-wechat/master/LICENSE
 * create: 2014/12/30
 * update: 2017/01/22
 * version: 0.0.1
 */
namespace yii\wechat;

use Yii;
use yii\base\ErrorException;
use yii\base\Object;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

use yii\wechat\models\Wechat;
use yii\wechat\models\WechatMaterial;
use yii\wechat\models\WechatMaterialMedia;
use yii\wechat\models\WechatMenu;
use yii\wechat\models\WechatNews;
use yii\wechat\models\WechatNewsImage;
use yii\wechat\models\WechatNewsMedia;
use yii\wechat\models\WechatUser;
use yii\wechat\models\WechatUserGroup;

class Manager extends Object {

	//微信接口网关
	private $api = 'https://api.weixin.qq.com';

	//公众号
	private $_app;

	//提示信息
	private $messages = false;

	//错误信息
	public $templates = [];

	//错误码
	public $errcode = 0;

	//错误信息
	public $errmsg = null;

	//fileupload扩展组件名
	public $fileupload = 'fileupload';

	//临时素材有效时长, 3天
	private $effectiveTimeOfTemporaryMaterial = 259200;

	/**
	 * Returns app model
	 *
	 * @since 0.0.1
	 * @return {object}
	 */
	public function getApp() {
		if(!$this->_app) {
			throw new ErrorException('Please set app first');
		}

		return $this->_app;
	}

	/**
	 * Set app model
	 *
	 * @since 0.0.1
	 * @param {string} $value app id
	 */
	public function setApp($value) {
		if(!$this->_app = Wechat::findOne($value)) {
			throw new ErrorException('Without the wechat app: ' . $value);
		}
	}

	/**
	 * Send template by type
	 *
	 * @since 0.0.1
	 * @param {string} $touser openid
	 * @param {string} $type
	 * @param {array} $data
	 * @param {string} [$url]
	 * @return {boolean}
	 * @example \Yii::$app->wechat->sendTemplateMessageByType($touser, $type, $data, $url);
	 */
	public function sendTemplateMessageByType($touser, $type, $data, $url = null) {
		return isset($this->templates[$type]) && $this->sendTemplateMessage($touser, $this->templates[$type], $data, $url);
	}

	/**
	 * Send template
	 *
	 * @since 0.0.1
	 * @param {string} $touser openid
	 * @param {string} $template_id
	 * @param {array} $data
	 * @param {string} [$url]
	 * @return {boolean}
	 * @example \Yii::$app->wechat->sendTemplateMessage($touser, $template_id, $data, $url);
	 */
	public function sendTemplateMessage($touser, $template_id, $data, $url = null) {
		$this->getData('/cgi-bin/message/template/send', [
			'access_token' => $this->getAccessToken(),
		], Json::encode([
			'touser' => $touser,
			'template_id' => $template_id,
			'url' => $url,
			'data' => $data,
		]));

		return $this->errcode == 0;
	}

	/**
	 * Delete template
	 *
	 * @since 0.0.1
	 * @param {string} $template_id
	 * @return {boolean}
	 * @example \Yii::$app->wechat->deleteTemplate($template_id);
	 */
	public function deleteTemplate($template_id) {
		$data = $this->getData('/cgi-bin/template/del_private_template', [
			'access_token' => $this->getAccessToken(),
		], Json::encode([
			'template_id' => $template_id,
		]));

		return $this->errcode == 0;
	}

	/**
	 * Add template
	 *
	 * @since 0.0.1
	 * @param {string} $template_id_short
	 * @return {string}
	 * @example \Yii::$app->wechat->addTemplate($template_id_short);
	 */
	public function addTemplate($template_id_short) {
		$data = $this->getData('/cgi-bin/template/api_add_template', [
			'access_token' => $this->getAccessToken(),
		], Json::encode([
			'template_id_short' => $template_id_short,
		]));

		if($this->errcode == 0) {
			return $data['template_id'];
		}

		return null;
	}

	/**
	 * Get all template
	 *
	 * @since 0.0.1
	 * @return {array}
	 * @example \Yii::$app->wechat->getAllTemplate();
	 */
	public function getAllTemplate() {
		$data = $this->getData('/cgi-bin/template/get_all_private_template', [
			'access_token' => $this->getAccessToken(),
		]);

		if($this->errcode == 0) {
			return $data['template_list'];
		}

		return [];
	}

	/**
	 * Get template industry
	 *
	 * @since 0.0.1
	 * @return {array}
	 * @example \Yii::$app->wechat->getTemplateIndustry();
	 */
	public function getTemplateIndustry() {
		$data = $this->getData('/cgi-bin/template/get_industry', [
			'access_token' => $this->getAccessToken(),
		]);

		if($this->errcode == 0) {
			return $data;
		}

		return [];
	}

	/**
	 * Set template industry
	 *
	 * @since 0.0.1
	 * @param {integer} $industry_id1 primary
	 * @param {integer} $industry_id2 secondary
	 * @return {boolean}
	 * @example \Yii::$app->wechat->setTemplateIndustry($industry_id1, $industry_id2);
	 */
	public function setTemplateIndustry($industry_id1, $industry_id2) {
		$data = $this->getData('/cgi-bin/template/api_set_industry', [
			'access_token' => $this->getAccessToken(),
		], Json::encode([
			'industry_id1' => $industry_id1,
			'industry_id2' => $industry_id2,
		]));

		return $this->errcode == 0;
	}

	/**
	 * 上传图文消息内的图片
	 *
	 * @since 0.0.1
	 * @param {string} $url_source 源url(非微信端)
	 * @return {integer}
	 * @example \Yii::$app->wechat->addNewsImage($url_source);
	 */
	public function addNewsImage($url_source) {
		if($image = WechatNewsImage::findOne(['appid' => $this->app->appid, 'url_source' => $url_source])) {
			return $image->id;
		}

		$image = new WechatNewsImage;
		$image->url_source = $url_source;

		$data = $this->getData('/cgi-bin/media/uploadimg', [
			'access_token' => $this->getAccessToken(),
		], ['media' => '@' . $image->localFile]);
		$image->cleanTmp();

		if($this->errcode == 0) {
			$image->appid = $this->app->appid;
			$image->url = $data['url'];
			if($image->save()) {
				return $image->id;
			}
		}

		return 0;
	}

	/**
	 * 删除图文消息
	 *
	 * @since 0.0.1
	 * @param {integer} $news_media_id 图文消息id
	 * @return {boolean}
	 * @example \Yii::$app->wechat->deleteNews($news_media_id);
	 */
	public function deleteNews($news_media_id) {
		$media = WechatNewsMedia::findOne($news_media_id);
		if(!$media) {
			throw new ErrorException('数据查询失败');
		}

		return $this->deleteMaterial($media->media_id) && $media->delete();
	}

	/**
	 * 修改图文消息
	 *
	 * @since 0.0.1
	 * @param {integer} $news_media_id 图文消息id
	 * @return {integer}
	 * @example \Yii::$app->wechat->updateNews($news_media_id);
	 */
	public function updateNews($news_media_id) {
		$newsMedia = WechatNewsMedia::findOne($news_media_id);
		if(!$newsMedia) {
			throw new ErrorException('数据查询失败');
		}

		if($newsMedia->news->count_item == count($newsMedia->thumbMaterialMediaIdList)) {
			$articles = $media->news->getArticles($this);
			$thumb_material_media_ids = [];
			$success = true;
			foreach($articles as $index => $article) {
				$thumb_material_media_ids[] = $article['thumb_material_media_id'];
				$data = $this->getData('/cgi-bin/material/update_news', [
					'access_token' => $this->getAccessToken(),
				], Json::encode([
					'media_id' => $media->media_id,
					'index' => $index,
					'articles' => $article,
				]));
				if($this->errcode != 0) {
					$success = false;
					break;
				}
			}
			if($success) {
				$media->thumb_material_media_ids = Json::encode($thumb_material_media_ids);
				if($details = $this->getNews($media->media_id)) {
					$media->urls = Json::encode(ArrayHelper::getColumn($details['news_item'], 'url'));
					$media->thumb_urls = Json::encode(ArrayHelper::getColumn($details['news_item'], 'thumb_url'));
				}
				if($media->save()) {
					return $media->id;
				}
			}
		} else {
			$newsid = $media->newsid;
			if($this->deleteNews($media->id)) {
				return $this->addNews($newsid);
			}
		}

		return 0;
	}

	/**
	 * 新增图文消息
	 *
	 * @since 0.0.1
	 * @param {integer} $news_id 图文素材id
	 * @return {integer}
	 * @example \Yii::$app->wechat->addNews($news_id);
	 */
	public function addNews($news_id) {
		$news = WechatNews::findOne($news_id);
		if(!$news) {
			throw new ErrorException('数据查询失败');
		} else if($newsMedia = WechatNewsMedia::findOne(['appid' => $this->app->appid, 'news_id' => $news->id])) {
			return $newsMedia->id;
		}

		$articles = $news->getArticles($this);
		$data = $this->getData('/cgi-bin/material/add_news', [
			'access_token' => $this->getAccessToken(),
		], Json::encode(['articles' => $articles]));

		if($this->errcode == 0) {
			$newsMedia = new WechatNewsMedia;
			$newsMedia->appid = $this->app->appid;
			$newsMedia->news_id = $news->id;
			$newsMedia->media_id = $data['media_id'];
			$newsMedia->thumb_material_media_ids = Json::encode(ArrayHelper::getColumn($articles, 'thumb_material_media_id'));
			if($details = $this->getNews($newsMedia->media_id)) {
				$newsMedia->urls = Json::encode(ArrayHelper::getColumn($details['news_item'], 'url'));
				$newsMedia->thumb_urls = Json::encode(ArrayHelper::getColumn($details['news_item'], 'thumb_url'));
			}
			if($newsMedia->save()) {
				return $newsMedia->id;
			}
		}

		return 0;
	}

	/**
	 * 获取图文消息
	 *
	 * @since 0.0.1
	 * @param {string} [$media_id] 媒体文件ID
	 * @param {boolean} [$all=true] 是否返回所有
	 * @param {integer} [$page=1] 页码
	 * @param {integer} [$count=20] 每页数量
	 * @return {array}
	 * @example \Yii::$app->wechat->getNews($media_id, $all, $page, $count);
	 */
	public function getNews($media_id = null, $all = true, $page = 1, $count = 20) {
		return $media_id ? $this->getMaterial($media_id) : $this->getMaterials('news', $all, $page, $count);
	}

	/**
	 * 获取素材总数
	 *
	 * @since 0.0.1
	 * @return {boolean}
	 * @example \Yii::$app->wechat->getMaterialCount();
	 */
	public function getMaterialCount() {
		$data = $this->getData('/cgi-bin/material/get_materialcount', [
			'access_token' => $this->getAccessToken(),
		]);

		if($this->errcode == 0) {
			$this->app->count_image = $data['image_count'];
			$this->app->count_voice = $data['voice_count'];
			$this->app->count_video = $data['video_count'];
			$this->app->count_news = $data['news_count'];
			return $this->app->save();
		}

		return false;
	}

	/**
	 * 获取素材列表
	 *
	 * @since 0.0.1
	 * @param {string} $type 素材的类型
	 * @param {boolean} [$all=true] 是否返回所有
	 * @param {integer} [$page=1] 页码
	 * @param {integer} [$count=20] 每页数量
	 * @return {array}
	 * @example \Yii::$app->wechat->getMaterials($type, $all, $page, $count);
	 */
	public function getMaterials($type, $all = true, $page = 1, $count = 20) {
		$offset = ($page - 1) * $count;
		$data = $this->getData('/cgi-bin/material/batchget_material', [
			'access_token' => $this->getAccessToken(),
		], Json::encode([
			'type' => $type,
			'offset' => $offset,
			'count' => $count,
		]));

		if($this->errcode == 0) {
			if($all && $offset + $data['item_count'] < $data['total_count']) {
				if($_data = $this->getMaterials($type, $all, $page + 1, $count)) {
					$data['item_count'] += $_data['item_count'];
					$data['item'] = array_merge($data['item'], $_data['item']);
				}
			}

			return $data;
		}

		return [];
	}

	/**
	 * 删除永久素材
	 *
	 * @since 0.0.1
	 * @param {string} $media_id 媒体文件ID
	 * @return {boolean}
	 * @example \Yii::$app->wechat->deleteMaterial($media_id);
	 */
	public function deleteMaterial($media_id) {
		$data = $this->getData('/cgi-bin/material/del_material', [
			'access_token' => $this->getAccessToken(),
		], Json::encode(['media_id' => $media_id]));

		return $this->errcode == 0;
	}

	/**
	 * 获取永久素材
	 *
	 * @since 0.0.1
	 * @param {string} $media_id 媒体文件ID
	 * @return {string|array|null}
	 * @example \Yii::$app->wechat->getMaterial($media_id);
	 */
	public function getMaterial($media_id) {
		$data = $this->getData('/cgi-bin/material/get_material', [
			'access_token' => $this->getAccessToken(),
		], Json::encode(['media_id' => $media_id]));

		return $this->errcode == 0 ? (isset($data['content']) && isset($data['extension']) ? $this->saveFile($data) : $data) : null;
	}

	/**
	 * 新增永久素材
	 *
	 * @since 0.0.1
	 * @param {integer} $material_id 素材id
	 * @return {integer}
	 * @example \Yii::$app->wechat->addMaterial($material_id);
	 */
	public function addMaterial($material_id) {
		$material = WechatMaterial::findOne($material_id);
		if(!$material) {
			throw new ErrorException('数据查询失败');
		}else if($materialMedia = WechatMaterialMedia::findOne(['appid' => $this->app->appid, 'material_id' => $material->id, 'expired_at' => 0])) {
			return $materialMedia->id;
		}

		$postData = ['media' => '@' . $material->localFile];
		if($material->type == 'video') {
			if($material->title == '') {
				throw new ErrorException('视频标题不能为空');
			}
			$postData['description'] = Json::encode([
				'title' => $material->title,
				'introduction' => $material->description,
			]);
		}

		$data = $this->getData('/cgi-bin/material/add_material', [
			'access_token' => $this->getAccessToken(),
			'type' => $material->type,
		], $postData);
		$material->cleanTmp();

		if($this->errcode == 0) {
			$materialMedia = new WechatMaterialMedia;
			$materialMedia->appid = $this->app->appid;
			$materialMedia->material_id = $material->id;
			$materialMedia->media_id = $data['media_id'];
			if(isset($data['url'])) {
				$materialMedia->url = $data['url'];
			}
			if($materialMedia->save()) {
				return $materialMedia->id;
			}
		}

		return 0;
	}

	/**
	 * 获取临时素材
	 *
	 * @since 0.0.1
	 * @param {string} $media_id 媒体文件ID
	 * @return {string|null}
	 * @example \Yii::$app->wechat->getMedia($media_id);
	 */
	public function getMedia($media_id) {
		$data = $this->getData('/cgi-bin/media/get', [
			'access_token' => $this->getAccessToken(),
			'media_id' => $media_id,
		]);

		return $this->errcode == 0 ? $this->saveFile($data) : null;
	}

	/**
	 * 新增临时素材
	 *
	 * @since 0.0.1
	 * @param {integer} $material_id 素材id
	 * @return {integer}
	 * @example \Yii::$app->wechat->addMedia($material_id);
	 */
	public function addMedia($material_id) {
		$material = WechatMaterial::findOne($material_id);
		if(!$material) {
			throw new ErrorException('数据查询失败');
		}

		$data = $this->getData('/cgi-bin/media/upload', [
			'access_token' => $this->getAccessToken(),
			'type' => $material->type,
		], ['media' => '@' . $material->localFile]);
		$material->cleanTmp();

		if($this->errcode == 0) {
			$materialMedia = new WechatMaterialMedia;
			$materialMedia->appid = $this->app->appid;
			$materialMedia->material_id = $material->id;
			$materialMedia->media_id = $data['media_id'];
			$materialMedia->expired_at = $data['created_at'] + $this->effectiveTimeOfTemporaryMaterial;
			if($materialMedia->save()) {
				return $materialMedia->id;
			}
		}

		return 0;
	}

	/**
	 * 获取公众号的菜单配置
	 *
	 * @since 0.0.1
	 * @return {array}
	 * @example \Yii::$app->wechat->getCurrentMenu();
	 */
	public function getCurrentMenu() {
		$data = $this->getData('/cgi-bin/get_current_selfmenu_info', [
			'access_token' => $this->getAccessToken(),
		]);

		return $this->errcode == 0 ? $data : [];
	}

	/**
	 * 测试个性化菜单匹配结果
	 *
	 * @since 0.0.1
	 * @param {string} $openid OpenID或微信号
	 * @return {array}
	 * @example \Yii::$app->wechat->tryMatchMenu($openid);
	 */
	public function tryMatchMenu($openid) {
		$data = $this->getData('/cgi-bin/menu/trymatch', [
			'access_token' => $this->getAccessToken(),
		], Json::encode(['user_id' => $openid]));

		return $this->errcode == 0 ? $data : [];
	}

	/**
	 * 删除个性化菜单
	 *
	 * @since 0.0.1
	 * @param {integer} $menuid 菜单id
	 * @return {boolean}
	 * @example \Yii::$app->wechat->deleteConditionalMenu($menuid);
	 */
	public function deleteConditionalMenu($menuid) {
		$data = $this->getData('/cgi-bin/menu/delconditional', [
			'access_token' => $this->getAccessToken(),
		], Json::encode(['menuid' => $menuid]));

		return $this->errcode == 0 && WechatMenu::deleteAll(['appid' => $this->app->appid, 'conditional' => 1, 'menuid' => $menuid]);
	}

	/**
	 * 删除自定义(个性化)菜单
	 *
	 * @since 0.0.1
	 * @return {boolean}
	 * @example \Yii::$app->wechat->deleteMenu();
	 */
	public function deleteMenu() {
		$data = $this->getData('/cgi-bin/menu/delete', [
			'access_token' => $this->getAccessToken(),
		]);

		return $this->errcode == 0 && WechatMenu::deleteAll(['appid' => $this->app->appid]);
	}

	/**
	 * 更新自定义菜单
	 * @method updateMenu
	 * @since 0.0.1
	 * @return {boolean}
	 * @example \Yii::$app->wechat->updateMenu();
	 */
	public function updateMenu() {
		$data = $this->getData('/cgi-bin/menu/create', [
			'access_token' => $this->getAccessToken(),
		], Json::encode(['button' => WechatMenu::getMenu($this->app->appid)]));

		return $this->errcode == 0;
	}

	/**
	 * 创建自定义(个性化)菜单
	 *
	 * @since 0.0.1
	 * @param {array} $button 菜单数据
	 * @param {array} [$matchrule] 个性化菜单匹配规则
	 * @return {boolean}
	 * @example \Yii::$app->wechat->createMenu($button, $matchrule);
	 */
	public function createMenu($button, $matchrule = null) {
		$postData = ['button' => $button];
		if($matchrule) {
			$postData['matchrule'] = $matchrule;
		}

		$data = $this->getData('/cgi-bin/menu/' . ($matchrule ? 'addconditional' : 'create'), [
			'access_token' => $this->getAccessToken(),
		], Json::encode($postData));

		if(isset($data['menuid'])) {
			$postData['menuid'] = $data['menuid'];
		}

		return $this->errcode == 0 && WechatMenu::createMenu($this->app->appid, $postData);
	}

	/**
	 * 刷新自定义(个性化)菜单
	 *
	 * @since 0.0.1
	 * @return {boolean}
	 * @example \Yii::$app->wechat->refreshMenu();
	 */
	public function refreshMenu() {
		$data = $this->getMenu();
		if($data && isset($data['menu']) && isset($data['menu']['button'])) {
			WechatMenu::deleteAll(['appid' => $this->app->appid]);
			WechatMenu::addMenu($this->app->appid, $data['menu']['button'], isset($data['menu']['menuid']) ? $data['menu']['menuid'] : null);
			if(isset($data['conditionalmenu'])) {
				foreach($data['conditionalmenu'] as $conditionalmenu) {
					WechatMenu::addMenu($this->app->appid, $conditionalmenu['button'], $conditionalmenu['menuid'], $conditionalmenu['matchrule']);
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * 查询自定义(个性化)菜单
	 *
	 * @since 0.0.1
	 * @return {array}
	 * @example \Yii::$app->wechat->getMenu();
	 */
	public function getMenu() {
		$data = $this->getData('/cgi-bin/menu/get', [
			'access_token' => $this->getAccessToken(),
		]);

		return $this->errcode == 0 ? $data : [];
	}

	/**
	 * 批量移动用户分组
	 *
	 * @since 0.0.1
	 * @param {string|array} $uids 用户id
	 * @param {integer} $gid 用户分组gid
	 * @return {boolean}
	 * @example \Yii::$app->wechat->updateGroupUsers($uids, $gid);
	 */
	public function updateGroupUsers($uids, $gid) {
		if(is_array($uids)) {
			$uids = implode(',', $uids);
		}

		$query = WechatUser::find()->where("id in ($uids) and groupid <> $gid");
		$users = $query->all();
		$openids = ArrayHelper::getColumn($users, 'openid');

		if(!$openids) {
			return true;
		}

		$data = $this->getData('/cgi-bin/groups/members/batchupdate', [
			'access_token' => $this->getAccessToken(),
		], Json::encode([
			'openid_list' => $openids,
			'to_groupid' => $gid,
		]));

		$result = $this->errcode == 0;
		if($result) {
			foreach($users as $user) {
				if($user->group->updateCounters(['count' => -1])) {
					$user->groupid = $gid;
					if($user->save()) {
						$user->refresh();
						$user->group->updateCounters(['count' => 1]);
					}
				}
			}
		}

		return $result;
	}

	/**
	 * 移动用户分组
	 *
	 * @since 0.0.1
	 * @param {integer} $uid 用户id
	 * @param {integer} $gid 用户分组gid
	 * @return {boolean}
	 * @example \Yii::$app->wechat->updateGroupUser($uid, $gid);
	 */
	public function updateGroupUser($uid, $gid) {
		$user = WechatUser::findOne($uid);
		if(!$user) {
			throw new ErrorException('数据查询失败');
		}

		if($user->groupid == $gid) {
			return true;
		}

		$data = $this->getData('/cgi-bin/groups/members/update', [
			'access_token' => $this->getAccessToken(),
		], Json::encode([
			'openid' => $user->openid,
			'to_groupid' => $gid,
		]));

		$result = $this->errcode == 0;
		if($result && $user->group->updateCounters(['count' => -1])) {
			$user->groupid = $gid;
			if($user->save()) {
				$user->refresh();
				return $user->group->updateCounters(['count' => 1]);
			}
		}

		return $result;
	}

	/**
	 * 删除用户分组
	 *
	 * @since 0.0.1
	 * @param {integer} $gid 用户分组id
	 * @param {string} [$name] 用户分组名字, 30个字符以内, 默认直接取数据库中的值
	 * @return {boolean}
	 * @example \Yii::$app->wechat->deleteGroup($gid, $name);
	 */
	public function deleteGroup($gid) {
		$group = WechatUserGroup::findOne($gid);
		if(!$group) {
			throw new ErrorException('数据查询失败');
		}

		$data = $this->getData('/cgi-bin/groups/delete', [
			'access_token' => $this->getAccessToken(),
		], Json::encode([
			'group' => ['id' => $group->gid],
		]));

		if(empty($data)) {
			return $group->delete();
		}

		return false;
	}

	/**
	 * 修改用户分组名
	 *
	 * @since 0.0.1
	 * @param {integer} $gid 用户分组id
	 * @param {string} [$name] 分组名字, 30个字符以内, 默认直接取数据库中的值
	 * @return {boolean}
	 * @example \Yii::$app->wechat->updateGroup($gid, $name);
	 */
	public function updateGroup($gid, $name = null) {
		$group = WechatUserGroup::findOne($gid);
		if(!$group) {
			throw new ErrorException('数据查询失败');
		}

		$data = $this->getData('/cgi-bin/groups/update', [
			'access_token' => $this->getAccessToken(),
		], Json::encode([
			'group' => ['id' => $group->gid, 'name' => $name ? : $group->name],
		]));

		$result = $this->errcode == 0;
		if($result && $name) {
			$group->name = $name;
			$group->save();
		}

		return $result;
	}

	/**
	 * 创建用户分组
	 *
	 * @since 0.0.1
	 * @param {string} $name 用户分组名字, 30个字符以内
	 * @return {object}
	 * @example \Yii::$app->wechat->createGroup($name);
	 */
	public function createGroup($name) {
		$data = $this->getData('/cgi-bin/groups/create', [
			'access_token' => $this->getAccessToken(),
		], Json::encode([
			'group' => ['name' => $name],
		]));

		if(isset($data['group'])) {
			$group = new WechatUserGroup;
			$group->appid = $this->app->appid;
			$group->gid = $data['group']['id'];
			$group->name = $data['group']['name'];
			if($group->save()) {
				return $group->id;
			}
		}

		return 0;
	}

	/**
	 * 查询所有用户分组
	 *
	 * @since 0.0.1
	 * @return {boolean}
	 * @example \Yii::$app->wechat->getGroups();
	 */
	public function getGroups() {
		$data = $this->getData('/cgi-bin/groups/get', [
			'access_token' => $this->getAccessToken(),
		]);

		if(isset($data['groups'])) {
			foreach($data['groups'] as $_group) {
				$group = WechatUserGroup::findOne(['appid' => $this->app->appid, 'gid' => $_group['id']]);
				if(!$group) {
					$group = new WechatUserGroup;
					$group->appid = $this->app->appid;
					$group->gid = $_group['id'];
				}
				$group->name = $_group['name'];
				$group->count = $_group['count'];
				$group->save();
			}
		}

		return $this->errcode == 0;
	}

	/**
	 * 查询用户所在分组
	 *
	 * @since 0.0.1
	 * @param {string} $openid OpenID
	 * @return {integer}
	 * @example \Yii::$app->wechat->getUserGroup($openid);
	 */
	public function getUserGroup($openid) {
		$data = $this->getData('/cgi-bin/groups/getid', [
			'access_token' => $this->getAccessToken(),
		], Json::encode([
			'openid' => $openid,
		]));

		return isset($data['groupid']) ? $data['groupid'] : -1;
	}

	/**
	 * 设置用户备注名
	 *
	 * @since 0.0.1
	 * @param {integer} $uid 用户id
	 * @param {string} [$remark] 备注名, 长度必须小于30字符, 默认直接取数据库中的值
	 * @return {boolean}
	 * @example \Yii::$app->wechat->updateUserRemark($uid, $remark);
	 */
	public function updateUserRemark($uid, $remark = null) {
		$user = WechatUser::findOne($uid);
		if(!$user) {
			throw new ErrorException('数据查询失败');
		}

		$data = $this->getData('/cgi-bin/user/info/updateremark', [
			'access_token' => $this->getAccessToken(),
		], Json::encode([
			'openid' => $user->openid,
			'remark' => $remark ? : $user->remark,
		]));

		$result = $this->errcode == 0;
		if($result && $remark) {
			$user->remark = $remark;
			$user->save();
		}

		return $result;
	}

	/**
	 * 刷新用户基本信息
	 *
	 * @since 0.0.1
	 * @param {integer} $uid 用户id
	 * @return {boolean}
	 * @example \Yii::$app->wechat->refreshUser($uid);
	 */
	public function refreshUser($uid) {
		$user = WechatUser::findOne($uid);
		if(!$user) {
			throw new ErrorException('数据查询失败');
		}

		$data = $this->getData('/cgi-bin/user/info', [
			'access_token' => $this->getAccessToken(),
			'openid' => $user->openid,
			'lang' => \Yii::$app->language,
		]);

		$result = $this->errcode == 0;
		if($result) {
			$user->subscribe = $data['subscribe'];
			if($user->subscribe == 1) {
				$user->subscribed_at = $data['subscribe_time'];
				$user->name = $data['nickname'];
				$user->sex = $data['sex'];
				$user->country = $data['country'];
				$user->city = $data['city'];
				$user->province = $data['province'];
				$user->language = $data['language'];
				$user->headimgurl = $data['headimgurl'];
				$user->remark = $data['remark'];
				$user->groupid = $data['groupid'];
			}
			if(isset($data['unionid'])) {
				$user->unionid = $data['unionid'];
			}
			$user->save();
		}

		return $result;
	}

	/**
	 * 刷新所有用户基本信息
	 *
	 * @since 0.0.1
	 * @param {integer} [$page=1] 页码
	 * @return {boolean}
	 * @example \Yii::$app->wechat->refreshUsers($page);
	 */
	public function refreshUsers($page = 1) {
		$query = WechatUser::find()->where(['appid' => $this->app->appid])->select('openid');

		$pageSize = 100;
		$pagination = new Pagination([
			'totalCount' => $query->count(),
			'defaultPageSize' => $pageSize,
			'pageSizeLimit' => [0, $pageSize],
		]);
		$pagination->setPage($page - 1, true);

		$users = $query->offset($pagination->offset)
			->limit($pagination->limit)
			->asArray()
			->all();

		$user_list = [];
		foreach($users as $user) {
			$user['lang'] = \Yii::$app->language;
			$user_list['user_list'][] = $user;
		}

		if($user_list) {
			$data = $this->getData('/cgi-bin/user/info/batchget', [
				'access_token' => $this->getAccessToken(),
			], Json::encode($user_list));
			if(isset($data['user_info_list'])) {
				foreach($data['user_info_list'] as $_user) {
					$user = WechatUser::findOne(['appid' => $this->app->appid, 'openid' => $_user['openid']]);
					if(!$user) {
						$user = new WechatUser;
						$user->appid = $this->app->appid;
						$user->openid = $_user['openid'];
					}
					$user->subscribe = $_user['subscribe'];
					if($user->subscribe == 1) {
						$user->subscribed_at = $_user['subscribed_at'];
						$user->name = $_user['nickname'];
						$user->sex = $_user['sex'];
						$user->country = $_user['country'];
						$user->city = $_user['city'];
						$user->province = $_user['province'];
						$user->language = $_user['language'];
						$user->headimgurl = $_user['headimgurl'];
						$user->remark = $_user['remark'];
						$user->groupid = $_user['groupid'];
					}
					if(isset($_user['unionid'])) {
						$user->unionid = $_user['unionid'];
					}
					$user->save();
				}
			}
		}

		return $page < $pagination->pageCount ? $this->refreshUsers($page + 1) : $this->errcode == 0;
	}

	/**
	 * 获取用户列表
	 *
	 * @since 0.0.1
	 * @param {string} [$next_openid] 第一个拉取的OPENID, 不填默认从头开始拉取
	 * @return {boolean}
	 * @example \Yii::$app->wechat->getUsers($next_openid);
	 */
	public function getUsers($next_openid = null) {
		$data = $this->getData('/cgi-bin/user/get', [
			'access_token' => $this->getAccessToken(),
			'next_openid' => $next_openid,
		]);

		if(isset($data['count']) && $data['count'] && isset($data['data']) && isset($data['data']['openid'])) {
			foreach($data['data']['openid'] as $openid) {
				if($user = WechatUser::findOne(['appid' => $this->app->appid, 'openid' => $openid])) {
					if($user->subscribe == 0) {
						$user->subscribe = 1;
						$user->save();
					}
				} else {
					$user = new WechatUser;
					$user->appid = $this->app->appid;
					$user->openid = $openid;
					$user->subscribe = 1;
					$user->save();
				}
			}
		}

		return isset($data['next_openid']) && $data['next_openid'] ? $this->getUsers($data['next_openid']) : $this->errcode == 0;
	}

	/**
	 * 获取微信服务器IP地址
	 *
	 * @since 0.0.1
	 * @return {array}
	 * @example \Yii::$app->wechat->getCallbackIp();
	 */
	public function getCallbackIp() {
		if(empty($this->app->ip_list)) {
			$this->refreshIpList();
		}

		return $this->app->ipListArray;
	}

	/**
	 * 刷新微信服务器IP地址
	 *
	 * @since 0.0.1
	 * @return {boolean}
	 * @example \Yii::$app->wechat->refreshIpList();
	 */
	public function refreshIpList() {
		$data = $this->getData('/cgi-bin/getcallbackip', [
			'access_token' => $this->getAccessToken(),
		]);

		if(isset($data['ip_list'])) {
			$this->app->ip_list = Json::encode($data['ip_list']);
			return $this->app->save();
		}

		return $this->errcode == 0;
	}

	/**
	 * 获取js接口调用配置
	 *
	 * @since 0.0.1
	 * @param {string} [$url] 调用js接口页面url
	 * @return {array}
	 * @example \Yii::$app->wechat->getJsapiConfig($url);
	 */
	public function getJsapiConfig($url = null) {
		$params = [
			'jsapi_ticket' => $this->getJsapiTicket(),
			'noncestr' => md5(mt_rand()),
			'timestamp' => strval(time()),
			'url' => $url ? : \Yii::$app->request->absoluteUrl,
		];

		return [
			'appId' => $this->app->appid,
			'timestamp' => $params['timestamp'],
			'nonceStr' => $params['noncestr'],
			'signature' => $this->sign($params),
			'signType' => 'sha1',
		];
	}

	/**
	 * 获取js接口调用票据
	 *
	 * @since 0.0.1
	 * @return {string}
	 * @example \Yii::$app->wechat->getJsapiTicket();
	 */
	public function getJsapiTicket() {
		$time = time();
		if(empty($this->app->jsapi_ticket) || $this->app->jsapi_ticket_expired_at < $time) {
			$data = $this->getData('/cgi-bin/ticket/getticket', [
				'access_token' => $this->getAccessToken(),
				'type' => 'jsapi',
			]);
			if(isset($data['ticket']) && isset($data['expires_in'])) {
				$this->app->jsapi_ticket = $data['ticket'];
				$this->app->jsapi_ticket_expired_at = $time + $data['expires_in'];
				$this->app->save();
			}
		}

		return $this->app->jsapi_ticket;
	}

	/**
	 * 获取接口调用凭据
	 *
	 * @since 0.0.1
	 * @return {string}
	 * @example \Yii::$app->wechat->getAccessToken();
	 */
	public function getAccessToken() {
		$time = time();
		if(empty($this->app->access_token) || $this->app->access_token_expired_at < $time) {
			$data = $this->getData('/cgi-bin/token', [
				'grant_type' => 'client_credential',
				'appid' => $this->app->appid,
				'secret' => $this->app->secret,
			]);
			if(isset($data['access_token']) && isset($data['expires_in'])) {
				$this->app->access_token = $data['access_token'];
				$this->app->access_token_expired_at = $time + $data['expires_in'];
				$this->app->save();
			}
		}

		return $this->app->access_token;
	}

	/**
	 * 刷新用户网页授权接口调用凭据
	 *
	 * @since 0.0.1
	 * @param {string} $refresh_token access_token刷新token
	 * @return {array}
	 * @example \Yii::$app->wechat->refreshUserAccessToken($refresh_token);
	 */
	public function refreshUserAccessToken($refresh_token) {
		$data = $this->getData('/sns/oauth2/refresh_token', [
			'appid' => $this->app->appid,
			'grant_type' => 'refresh_token',
			'refresh_token' => $refresh_token,
		]);

		return $this->errcode == 0 ? $data : [];
	}

	/**
	 * 保存用户
	 *
	 * @since 0.0.1
	 * @param {string} $openid OpenID
	 * @return {object}
	 * @example \Yii::$app->wechat->findUser($openid);
	 */
	public function saveUser($userinfo) {
		if(!isset($userinfo['openid'])) {
			return null;
		}

		if($user = $this->findUser($userinfo['openid'])) {
			return $user;
		}

		$user = new WechatUser;
		$user->appid = $this->app->appid;
		$user->openid = $userinfo['openid'];
		$user->name = $userinfo['nickname'];
		$user->sex = $userinfo['sex'];
		$user->language = $userinfo['language'];
		$user->city = $userinfo['city'];
		$user->province = $userinfo['province'];
		$user->country = $userinfo['country'];
		$user->headimgurl = $userinfo['headimgurl'];
		$user->privilegeList = $userinfo['privilege'];

		if(!$user->save()) {
			return null;
		}

		$this->refreshUser($user->id);

		return $user;
	}

	/**
	 * 获取用户
	 *
	 * @since 0.0.1
	 * @param {string} $openid OpenID
	 * @return {object}
	 * @example \Yii::$app->wechat->findUser($openid);
	 */
	public function findUser($openid) {
		return WechatUser::findOne([
			'appid' => $this->app->appid,
			'openid' => $openid,
		]);
	}

	/**
	 * 获取用户网页授权信息
	 *
	 * @since 0.0.1
	 * @param {string} $code 通过用户在网页授权后获取的code参数
	 * @return {array}
	 * @example \Yii::$app->wechat->getUserInfo($code);
	 */
	public function getUserInfoByCode($code) {
		$data = $this->getData('/sns/oauth2/access_token', [
			'appid' => $this->app->appid,
			'secret' => $this->app->secret,
			'grant_type' => 'authorization_code',
			'code' => $code,
		]);

		$user = [];
		if($this->errcode == 0) {
			$user['openid'] = $data['openid'];
			if($data['scope'] == 'snsapi_userinfo') {
				$data = $this->getData('/sns/userinfo', [
					'access_token' => $data['access_token'],
					'openid' => $user['openid'],
					'lang' => \Yii::$app->language,
				]);
				if($this->errcode == 0) {
					$user = $data;
				}
			}
		}

		return $user;
	}

	/**
	 * 获取用户网页授权code跳转url
	 *
	 * @since 0.0.1
	 * @param {string} [$state] 重定向后会带上state参数, 开发者可以填写a-zA-Z0-9的参数值, 最多128字节
	 * @param {string} [$scope=snsapi_base] 应用授权作用域: snsapi_base(默认), snsapi_userinfo
	 * @param {string} [$url] 调用js接口页面url
	 * @return {string}
	 * @example \Yii::$app->wechat->getUserAuthorizeCodeUrl($state, $scope, $url);
	 */
	public function getUserAuthorizeCodeUrl($state = null, $scope = 'snsapi_base', $url = null) {
		return 'https://open.weixin.qq.com/connect/oauth2/authorize?' . http_build_query([
			'appid' => $this->app->appid,
			'redirect_uri' => $url ? : \Yii::$app->request->absoluteUrl,
			'response_type' => 'code',
			'scope' => $scope,
			'state' => $state,
		]) . '#wechat_redirect';
	}

	/**
	 * 生成随机令牌
	 *
	 * @since 0.0.1
	 * @return {string}
	 * @example \Yii::$app->wechat->generateToken();
	 */
	public function generateToken() {
		return $this->generateRandomString(mt_rand(3, 32));
	}

	/**
	 * 生成随机字符串
	 *
	 * @since 0.0.1
	 * @param {integer} [$len=32] 长度
	 * @return {string}
	 * @example \Yii::$app->wechat->generateRandomString($len);
	 */
	public function generateRandomString($len = 32) {
		$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$max = strlen($chars) - 1;

		$strArr = [];
		for($i = 0; $i < $len; $i++) {
			$strArr[] = $chars[mt_rand(0, $max)];
		}

		return implode($strArr);
	}

	/**
	 * 签名
	 *
	 * @since 0.0.1
	 * @param {array} $arr 数据数组
	 * @return {string}
	 */
	private function sign($arr) {
		ksort($arr);

		return sha1(urldecode(http_build_query($arr)));
	}

	/**
	 * 保存文件
	 *
	 * @since 0.0.1
	 * @param {array} $data 数据
	 * @return {string}
	 */
	private function saveFile($data) {
		$fileupload = \Yii::createObject(\Yii::$app->components[$this->fileupload]);
		$fileinfo = $fileupload->createFile($data['extension']);
		$file = fopen($fileinfo['tmp'], 'wb');
		fwrite($file, $data['content']);
		fclose($file);

		return $fileupload->finalFile($fileinfo);
	}

	/**
	 * 获取数据
	 *
	 * @since 0.0.1
	 * @param {string} $action 接口名称
	 * @param {array} $query 参数
	 * @param {string|array} [$data] 数据
	 * @return {array}
	 */
	private function getData($action, $query, $data = null) {
		$_result = $this->curl($this->getApiUrl($action, $query), $data);

		if(!$_result) {
			$this->errcode = '503';
			$this->errmsg = '接口服务不可用';
		}

		$result = json_decode($_result, true);
		if(json_last_error()) {
			if($extension = $this->getExtension($this->getMimeType($_result, true))) {
				$result = ['content' => $_result, 'extension' => $extension];
			} else {
				$this->errcode = '503';
				$this->errmsg = '数据不合法';
			}
		} else if(isset($result['errcode']) && isset($result['errmsg'])) {
			$this->errcode = $result['errcode'];
			$this->errmsg = $this->getMessage($result['errmsg']);
		}

		return $result;
	}

	/**
	 * 获取文件扩展名
	 *
	 * @since 0.0.1
	 * @param {string} $mimetype 文件MIME type
	 * @return {string}
	 */
	private function getExtension($mimetype) {
		if(preg_match('/^(image|audio|video)\/(.+)$/', $mimetype, $matches)) {
			$extension = $matches[2];
			if(in_array($extension, ['jpeg', 'pjpeg'])) {
				$extension = 'jpg';
			} else if(in_array($extension, ['mpeg4'])) {
				$extension = 'mp4';
			}

			return $extension;
		}

		return null;
	}

	/**
	 * 获取文件MIME type
	 *
	 * @since 0.0.1
	 * @param {string} $data 数据
	 * @param {boolean} $stream 数据流形式
	 * @return {string}
	 */
	private function getMimeType($data, $stream = false) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mimetype = $stream ? finfo_buffer($finfo, $data) : finfo_file($finfo, $data);
		finfo_close($finfo);

		return $mimetype;
	}

	/**
	 * 获取接口完整访问地址
	 *
	 * @since 0.0.1
	 * @param {string} $action 接口名称
	 * @param {array} [$query=[]] 参数
	 * @return {string}
	 */
	private function getApiUrl($action, $query = []) {
		return $this->api . $action . (empty($query) ? '' : '?' . http_build_query($query));
	}

	/**
	 * 是否通过内置浏览器中访问
	 *
	 * @since 0.0.1
	 * @return {boolean}
	 * @example \Yii::$app->wechat->getIsBuildInBrowser();
	 */
	public function getIsBuildInBrowser() {
		return isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'micromessenger') !== false;
	}

	/**
	 * 获取信息
	 *
	 * @since 0.0.1
	 * @param {string} $status 状态码
	 * @return {string}
	 */
	private function getMessage($status) {
		if($this->messages === false) {
			$this->messages = require(__DIR__ . '/messages.php');
		}

		return isset($this->messages[$status]) ? $this->messages[$status] : "Error: $status";
	}

	/**
	 * curl远程获取数据方法
	 *
	 * @since 0.0.1
	 * @param {string} $url 请求地址
	 * @param {string|array} [$data] post数据
	 * @param {string} [$useragent] 模拟浏览器用户代理信息
	 * @return {array}
	 */
	private function curl($url, $data = null, $useragent = null) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		if(!empty($data)) {
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		if(!empty($useragent)) {
			curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		}
		$result = curl_exec($curl);
		curl_close($curl);

		return $result;
	}

}
