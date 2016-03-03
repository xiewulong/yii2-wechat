<?php
/*!
 * yii2 extension - wechat
 * xiewulong <xiewulong@vip.qq.com>
 * https://github.com/xiewulong/yii2-wechat
 * https://raw.githubusercontent.com/xiewulong/yii2-wechat/master/LICENSE
 * create: 2014/12/30
 * update: 2016/3/3
 * version: 0.0.1
 */

namespace yii\wechat;

use Yii;
use yii\base\ErrorException;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\wechat\models\Wechat;
use yii\wechat\models\WechatUser;
use yii\wechat\models\WechatUserGroup;
use yii\wechat\models\WechatMenu;
use yii\wechat\models\WechatMaterial;
use yii\wechat\models\WechatMedia;
use yii\wechat\models\WechatNews;
use yii\wechat\models\WechatNewsMedia;
use yii\wechat\models\WechatNewsImage;

class Manager {

	//微信接口网关
	private $api = 'https://api.weixin.qq.com';

	//公众号
	public $wechat;

	//提示信息
	private $messages = false;

	//错误码
	public $errcode = 0;

	//错误信息
	public $errmsg = null;

	//fileupload扩展组件名
	public $fileupload = 'fileupload';

	//临时素材有效时长, 3天
	private $effectiveTimeOfTemporaryMaterial = 259200;

	/**
	 * 设置全局公众号
	 * @method setApp
	 * @since 0.0.1
	 * @param {string} $appid AppID
	 * @return {object}
	 * @example \Yii::$app->wechat->setApp($appid);
	 */
	public function setApp($appid) {
		if(!$this->wechat = Wechat::findOne($appid)) {
			throw new ErrorException('Without the wechat app');
		}

		return $this;
	}

	/**
	 * 上传图文消息内的图片
	 * @method addNewsImage
	 * @since 0.0.1
	 * @param {string} $url_source 源url(非微信端)
	 * @return {int}
	 * @example \Yii::$app->wechat->addNewsImage($url_source);
	 */
	public function addNewsImage($url_source) {
		if($image = WechatNewsImage::findOne(['appid' => $this->wechat->appid, 'url_source' => $url_source])) {
			return $image->id;
		}

		$image = new WechatNewsImage;
		$image->url_source = $url_source;

		$data = $this->getData('/cgi-bin/media/uploadimg', [
			'access_token' => $this->getAccessToken(),
		], ['media' => '@' . $image->localFile]);
		$image->cleanTmp();

		if($this->errcode == 0) {
			$image->appid = $this->wechat->appid;
			$image->url = $data['url'];
			if($image->save()) {
				return $image->id;
			}
		}

		return 0;
	}

	/**
	 * 删除图文消息
	 * @method deleteNews
	 * @since 0.0.1
	 * @param {int} $newsmediaid 图文消息id
	 * @return {boolean}
	 * @example \Yii::$app->wechat->deleteNews($newsmediaid);
	 */
	public function deleteNews($newsmediaid) {
		$media = WechatNewsMedia::findOne($newsmediaid);
		if(!$media) {
			throw new ErrorException('数据查询失败');
		}

		return $this->deleteMaterial($media->media_id) && $media->delete();
	}

	/**
	 * 修改图文消息
	 * @method updateNews
	 * @since 0.0.1
	 * @param {int} $newsmediaid 图文消息id
	 * @return {int}
	 * @example \Yii::$app->wechat->updateNews($newsmediaid);
	 */
	public function updateNews($newsmediaid) {
		$media = WechatNewsMedia::findOne($newsmediaid);
		if(!$media) {
			throw new ErrorException('数据查询失败');
		}

		$articles = $media->news->getArticles($this);
		if(count($articles) == count($media->thumbMediaidList)) {
			$thumb_mediaids = [];
			$success = true;
			foreach($articles as $index => $article) {
				$thumb_mediaids[] = $article['thumb_mediaid'];
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
				$media->thumb_mediaids = Json::encode($thumb_mediaids);
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
	 * @method addNews
	 * @since 0.0.1
	 * @param {int} $newsid 图文素材id
	 * @return {int}
	 * @example \Yii::$app->wechat->addNews($newsid);
	 */
	public function addNews($newsid) {
		$news = WechatNews::findOne($newsid);
		if(!$news) {
			throw new ErrorException('数据查询失败');
		} else if($media = WechatNewsMedia::findOne(['appid' => $this->wechat->appid, 'newsid' => $news->id])) {
			return $media->id;
		}

		$articles = $news->getArticles($this);
		$data = $this->getData('/cgi-bin/material/add_news', [
			'access_token' => $this->getAccessToken(),
		], Json::encode(['articles' => $articles]));

		if($this->errcode == 0) {
			$media = new WechatNewsMedia;
			$media->appid = $this->wechat->appid;
			$media->newsid = $news->id;
			$media->thumb_mediaids = Json::encode(ArrayHelper::getColumn($articles, 'thumb_mediaid'));
			$media->media_id = $data['media_id'];
			if($details = $this->getNews($media->media_id)) {
				$media->urls = Json::encode(ArrayHelper::getColumn($details['news_item'], 'url'));
				$media->thumb_urls = Json::encode(ArrayHelper::getColumn($details['news_item'], 'thumb_url'));
			}
			if($media->save()) {
				return $media->id;
			}
		}

		return 0;
	}

	/**
	 * 获取图文消息
	 * @method getNews
	 * @since 0.0.1
	 * @param {string} [$media_id] 媒体文件ID
	 * @param {boolean} [$all=true] 是否返回所有
	 * @param {int} [$page=1] 页码
	 * @param {int} [$count=20] 每页数量
	 * @return {array}
	 * @example \Yii::$app->wechat->getNews($media_id, $all, $page, $count);
	 */
	public function getNews($media_id = null, $all = true, $page = 1, $count = 20) {
		return $media_id ? $this->getMaterial($media_id) : $this->getMaterials('news', $all, $page, $count);
	}

	/**
	 * 获取素材总数
	 * @method getMaterialCount
	 * @since 0.0.1
	 * @return {boolean}
	 * @example \Yii::$app->wechat->getMaterialCount();
	 */
	public function getMaterialCount() {
		$data = $this->getData('/cgi-bin/material/get_materialcount', [
			'access_token' => $this->getAccessToken(),
		]);

		if($this->errcode == 0) {
			$this->wechat->count_image = $data['image_count'];
			$this->wechat->count_voice = $data['voice_count'];
			$this->wechat->count_video = $data['video_count'];
			$this->wechat->count_news = $data['news_count'];
			return $this->wechat->save();
		}

		return false;
	}

	/**
	 * 获取素材列表
	 * @method getMaterials
	 * @since 0.0.1
	 * @param {string} $type 素材的类型
	 * @param {boolean} [$all=true] 是否返回所有
	 * @param {int} [$page=1] 页码
	 * @param {int} [$count=20] 每页数量
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
	 * @method deleteMaterial
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
	 * @method getMaterial
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
	 * @method addMaterial
	 * @since 0.0.1
	 * @param {int} $materialid 素材数据id
	 * @param {int} [$mediaid] 媒体数据id, 如果设置此值则为上传缩略图
	 * @return {int}
	 * @example \Yii::$app->wechat->addMaterial($materialid, $mediaid);
	 */
	public function addMaterial($materialid, $mediaid = null) {
		$material = WechatMaterial::findOne($materialid);
		if(!$material) {
			throw new ErrorException('数据查询失败');
		}

		if($mediaid) {
			$media = WechatMedia::findOne($mediaid);
			if(!$media) {
				throw new ErrorException('数据查询失败');
			}
			if($media->expired_at > 0) {
				throw new ErrorException('不能为临时素材添加永久缩略图素材');
			}
			if($material->type != 'thumb') {
				throw new ErrorException('素材类型错误');
			}
		} else if($media = WechatMedia::findOne(['appid' => $this->wechat->appid, 'materialid' => $materialid, 'expired_at' => 0])) {
			return $media->id;
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
			if($mediaid) {
				$media->thumb_media_id = $data['media_id'];
				$media->thumb_materialid = $material->id;
				if(isset($data['url'])) {
					$media->thumb_url = $data['url'];
				}
			} else {
				$media = new WechatMedia;
				$media->appid = $this->wechat->appid;
				$media->media_id = $data['media_id'];
				$media->materialid = $material->id;
				if(isset($data['url'])) {
					$media->url = $data['url'];
				}
			}
			if($media->save()) {
				return $media->id;
			}
		}

		return 0;
	}

	/**
	 * 获取临时素材
	 * @method getMedia
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
	 * @method addMedia
	 * @since 0.0.1
	 * @param {int} $materialid 素材数据id
	 * @param {int} [$mediaid] 媒体数据id, 如果设置此值则为上传缩略图
	 * @return {int}
	 * @example \Yii::$app->wechat->addMedia($materialid, $mediaid);
	 */
	public function addMedia($materialid, $mediaid = null) {
		$material = WechatMaterial::findOne($materialid);
		if(!$material) {
			throw new ErrorException('数据查询失败');
		}

		if($mediaid) {
			$media = WechatMedia::findOne($mediaid);
			if(!$media) {
				throw new ErrorException('数据查询失败');
			}
			if($media->expired_at == 0) {
				throw new ErrorException('不能为永久素材添加临时缩略图素材');
			}
			if($material->type != 'thumb') {
				throw new ErrorException('素材类型错误');
			}
		}

		$data = $this->getData('/cgi-bin/media/upload', [
			'access_token' => $this->getAccessToken(),
			'type' => $material->type,
		], ['media' => '@' . $material->localFile]);
		$material->cleanTmp();

		if($this->errcode == 0) {
			if($mediaid) {
				$media->thumb_media_id = $data['thumb_media_id'];
				$media->thumb_materialid = $material->id;
				$media->thumb_expired_at = $data['created_at'] + $this->effectiveTimeOfTemporaryMaterial;
			} else {
				$media = new WechatMedia;
				$media->appid = $this->wechat->appid;
				$media->media_id = $data['media_id'];
				$media->materialid = $material->id;
				$media->expired_at = $data['created_at'] + $this->effectiveTimeOfTemporaryMaterial;
			}
			if($media->save()) {
				return $media->id;
			}
		}

		return 0;
	}

	/**
	 * 获取公众号的菜单配置
	 * @method getCurrentMenu
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
	 * @method tryMatchMenu
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
	 * @method deleteConditionalMenu
	 * @since 0.0.1
	 * @param {int} $menuid 菜单id
	 * @return {boolean}
	 * @example \Yii::$app->wechat->deleteConditionalMenu($menuid);
	 */
	public function deleteConditionalMenu($menuid) {
		$data = $this->getData('/cgi-bin/menu/delconditional', [
			'access_token' => $this->getAccessToken(),
		], Json::encode(['menuid' => $menuid]));

		return $this->errcode == 0 && WechatMenu::deleteAll(['appid' => $this->wechat->appid, 'conditional' => 1, 'menuid' => $menuid]);
	}

	/**
	 * 删除自定义(个性化)菜单
	 * @method deleteMenu
	 * @since 0.0.1
	 * @return {boolean}
	 * @example \Yii::$app->wechat->deleteMenu();
	 */
	public function deleteMenu() {
		$data = $this->getData('/cgi-bin/menu/delete', [
			'access_token' => $this->getAccessToken(),
		]);

		return $this->errcode == 0 && WechatMenu::deleteAll(['appid' => $this->wechat->appid]);
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
		], Json::encode(['button' => WechatMenu::getMenu($this->wechat->appid)]));

		return $this->errcode == 0;
	}

	/**
	 * 创建自定义(个性化)菜单
	 * @method createMenu
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

		return $this->errcode == 0 && WechatMenu::createMenu($this->wechat->appid, $postData);
	}

	/**
	 * 刷新自定义(个性化)菜单
	 * @method refreshMenu
	 * @since 0.0.1
	 * @return {boolean}
	 * @example \Yii::$app->wechat->refreshMenu();
	 */
	public function refreshMenu() {
		$data = $this->getMenu();
		if($data && isset($data['menu']) && isset($data['menu']['button'])) {
			WechatMenu::deleteAll(['appid' => $this->wechat->appid]);
			WechatMenu::addMenu($this->wechat->appid, $data['menu']['button'], isset($data['menu']['menuid']) ? $data['menu']['menuid'] : null);
			if(isset($data['conditionalmenu'])) {
				foreach($data['conditionalmenu'] as $conditionalmenu) {
					WechatMenu::addMenu($this->wechat->appid, $conditionalmenu['button'], $conditionalmenu['menuid'], $conditionalmenu['matchrule']);
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * 查询自定义(个性化)菜单
	 * @method getMenu
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
	 * @method updateGroupUsers
	 * @since 0.0.1
	 * @param {string|array} $uids 用户id
	 * @param {int} $gid 用户分组gid
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
	 * @method updateGroupUser
	 * @since 0.0.1
	 * @param {int} $uid 用户id
	 * @param {int} $gid 用户分组gid
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
	 * @method deleteGroup
	 * @since 0.0.1
	 * @param {int} $gid 用户分组id
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
	 * @method updateGroup
	 * @since 0.0.1
	 * @param {int} $gid 用户分组id
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
	 * @method createGroup
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

		$group = null;
		if(isset($data['group'])) {
			$group = new WechatUserGroup;
			$group->appid = $this->wechat->appid;
			$group->gid = $data['group']['id'];
			$group->name = $data['group']['name'];
			$group->save();
		}

		return $group;
	}

	/**
	 * 查询所有用户分组
	 * @method getGroups
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
				$group = WechatUserGroup::findOne(['appid' => $this->wechat->appid, 'gid' => $_group['id']]);
				if(!$group) {
					$group = new WechatUserGroup;
					$group->appid = $this->wechat->appid;
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
	 * @method getUserGroup
	 * @since 0.0.1
	 * @param {string} $openid OpenID
	 * @return {int}
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
	 * @method updateUserRemark
	 * @since 0.0.1
	 * @param {int} $uid 用户id
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
	 * @method refreshUser
	 * @since 0.0.1
	 * @param {int} $uid 用户id
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
				$user->subscribe_time = $data['subscribe_time'];
				$user->nickname = $data['nickname'];
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
	 * @method refreshUsers
	 * @since 0.0.1
	 * @param {int} [$page=1] 页码
	 * @return {boolean}
	 * @example \Yii::$app->wechat->refreshUsers($page);
	 */
	public function refreshUsers($page = 1) {
		$query = WechatUser::find()->where(['appid' => $this->wechat->appid])->select('openid');

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
					$user = WechatUser::findOne(['appid' => $this->wechat->appid, 'openid' => $_user['openid']]);
					if(!$user) {
						$user = new WechatUser;
						$user->appid = $this->wechat->appid;
						$user->openid = $_user['openid'];
					}
					$user->subscribe = $_user['subscribe'];
					if($user->subscribe == 1) {
						$user->subscribe_time = $_user['subscribe_time'];
						$user->nickname = urlencode($_user['nickname']);
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
	 * @method getUsers
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
				if($user = WechatUser::findOne(['appid' => $this->wechat->appid, 'openid' => $openid])) {
					if($user->subscribe == 0) {
						$user->subscribe = 1;
						$user->save();
					}
				} else {
					$user = new WechatUser;
					$user->appid = $this->wechat->appid;
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
	 * @method getCallbackIp
	 * @since 0.0.1
	 * @return {array}
	 * @example \Yii::$app->wechat->getCallbackIp();
	 */
	public function getCallbackIp() {
		if(empty($this->wechat->ip_list)) {
			$this->refreshIpList();
		}

		return $this->wechat->ipListArray;
	}

	/**
	 * 刷新微信服务器IP地址
	 * @method refreshIpList
	 * @since 0.0.1
	 * @return {boolean}
	 * @example \Yii::$app->wechat->refreshIpList();
	 */
	public function refreshIpList() {
		$data = $this->getData('/cgi-bin/getcallbackip', [
			'access_token' => $this->getAccessToken(),
		]);

		if(isset($data['ip_list'])) {
			$this->wechat->ip_list = Json::encode($data['ip_list']);
			return $this->wechat->save();
		}
		
		return $this->errcode == 0;
	}

	/**
	 * 获取js接口调用配置
	 * @method getJsapiConfig
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
			'appId' => $this->wechat->appid,
			'verifyAppId' => $this->wechat->appid,
			'verifySignType' => 'sha1',
			'verifyTimestamp' => $params['timestamp'],
			'verifyNonceStr' => $params['noncestr'],
			'verifySignature' => $this->sign($params),
		];
	}

	/**
	 * 获取js接口调用票据
	 * @method getJsapiTicket
	 * @since 0.0.1
	 * @return {string}
	 * @example \Yii::$app->wechat->getJsapiTicket();
	 */
	public function getJsapiTicket() {
		$time = time();
		if(empty($this->wechat->jsapi_ticket) || $this->wechat->jsapi_ticket_expired_at < $time) {
			$data = $this->getData('/cgi-bin/ticket/getticket', [
				'access_token' => $this->getAccessToken(),
				'type' => 'jsapi',
			]);
			if(isset($data['ticket']) && isset($data['expires_in'])) {
				$this->wechat->jsapi_ticket = $data['ticket'];
				$this->wechat->jsapi_ticket_expired_at = $time + $data['expires_in'];
				$this->wechat->save();
			}
		}

		return $this->wechat->jsapi_ticket;
	}

	/**
	 * 获取接口调用凭据
	 * @method getAccessToken
	 * @since 0.0.1
	 * @return {string}
	 * @example \Yii::$app->wechat->getAccessToken();
	 */
	public function getAccessToken() {
		$time = time();
		if(empty($this->wechat->access_token) || $this->wechat->access_token_expired_at < $time) {
			$data = $this->getData('/cgi-bin/token', [
				'grant_type' => 'client_credential',
				'appid' => $this->wechat->appid,
				'secret' => $this->wechat->secret,
			]);
			if(isset($data['access_token']) && isset($data['expires_in'])) {
				$this->wechat->access_token = $data['access_token'];
				$this->wechat->access_token_expired_at = $time + $data['expires_in'];
				$this->wechat->save();
			}
		}

		return $this->wechat->access_token;
	}

	/**
	 * 刷新用户网页授权接口调用凭据
	 * @method refreshUserAccessToken
	 * @since 0.0.1
	 * @param {string} $refresh_token access_token刷新token
	 * @return {array}
	 * @example \Yii::$app->wechat->refreshUserAccessToken($refresh_token);
	 */
	public function refreshUserAccessToken($refresh_token) {
		$data = $this->getData('/sns/oauth2/refresh_token', [
			'appid' => $this->wechat->appid,
			'grant_type' => 'refresh_token',
			'refresh_token' => $refresh_token,
		]);
		
		return $this->errcode == 0 ? $data : [];
	}

	/**
	 * 获取用户网页授权信息
	 * @method getUserInfo
	 * @since 0.0.1
	 * @param {string} $code 通过用户在网页授权后获取的code参数
	 * @return {array}
	 * @example \Yii::$app->wechat->getUserInfo($code);
	 */
	public function getUserInfo($code) {
		$data = $this->getData('/sns/oauth2/access_token', [
			'appid' => $this->wechat->appid,
			'secret' => $this->wechat->secret,
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
	 * @method getUserAuthorizeCodeUrl
	 * @since 0.0.1
	 * @param {string} [$state] 重定向后会带上state参数, 开发者可以填写a-zA-Z0-9的参数值, 最多128字节
	 * @param {string} [$scope=snsapi_base] 应用授权作用域: snsapi_base(默认), snsapi_userinfo
	 * @param {string} [$url] 调用js接口页面url
	 * @return {string}
	 * @example \Yii::$app->wechat->getUserAuthorizeCodeUrl($state, $scope, $url);
	 */
	public function getUserAuthorizeCodeUrl($state = null, $scope = 'snsapi_base', $url = null) {
		return 'https://open.weixin.qq.com/connect/oauth2/authorize?' . http_build_query([
			'appid' => $this->wechat->appid,
			'redirect_uri' => $url ? : \Yii::$app->request->absoluteUrl,
			'response_type' => 'code',
			'scope' => $scope,
			'state' => $state,
		]) . '#wechat_redirect';
	}

	/**
	 * 生成随机令牌
	 * @method generateToken
	 * @since 0.0.1
	 * @return {string}
	 * @example \Yii::$app->wechat->generateToken();
	 */
	public function generateToken() {
		return $this->generateRandomString(mt_rand(3, 32));
	}

	/**
	 * 生成随机字符串
	 * @method generateRandomString
	 * @since 0.0.1
	 * @param {int} [$len=32] 长度
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
	 * @method sign
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
	 * @method saveFile
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
	 * @method getData
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
	 * @method getExtension
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
	 * @method getExtension
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
	 * @method getApiUrl
	 * @since 0.0.1
	 * @param {string} $action 接口名称
	 * @param {array} [$query=[]] 参数
	 * @return {string}
	 */
	private function getApiUrl($action, $query = []) {
		return $this->api . $action . (empty($query) ? '' : '?' . http_build_query($query));
	}

	/**
	 * 获取信息
	 * @method getMessage
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
	 * @method curl
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
