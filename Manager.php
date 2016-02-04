<?php
/*!
 * yii2 extension - wechat
 * xiewulong <xiewulong@vip.qq.com>
 * https://github.com/xiewulong/yii2-wechat
 * https://raw.githubusercontent.com/xiewulong/yii2-wechat/master/LICENSE
 * create: 2014/12/30
 * update: 2016/2/4
 * version: 0.0.1
 */

namespace yii\wechat;

use yii\base\ErrorException;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\wechat\models\Wechat;
use yii\wechat\models\WechatUser;
use yii\wechat\models\WechatUserGroup;

class Manager {

	//微信api地址
	private $api = 'https://api.weixin.qq.com/cgi-bin';

	//公众号
	public $wechat;

	//提示信息
	private $messages = false;

	//返回码
	public $errcode = 0;

	//返回码说明
	public $errmsg = null;

	/**
	 * 获取公众号配置信息
	 * @method setAppid
	 * @since 0.0.1
	 * @return {object}
	 * @example \Yii::$app->wechat->setAppid();
	 */
	public function setAppid($appid) {
		if(!$this->wechat = Wechat::findOne($appid)) {
			throw new ErrorException('Without the wechat app');
		}

		return $this;
	}

	/**
	 * 批量移动用户分组
	 * @method updateGroupUsers
	 * @since 0.0.1
	 * @param {string|array} $ids 用户id
	 * @param {int} $gid 用户分组gid
	 * @return {boolean}
	 * @example \Yii::$app->wechat->updateGroupUsers($ids, $gid);
	 */
	public function updateGroupUsers($ids, $gid) {
		if(is_array($ids)) {
			$ids = implode(',', $ids);
		}

		$query = WechatUser::find()->where("id in ($ids) and groupid <> $gid");
		$users = $query->all();
		$openids = ArrayHelper::getColumn($users, 'openid');

		if(!$openids) {
			return true;
		}

		$data = $this->getData('/groups/members/batchupdate', [
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
	 * @param {int} $id 用户id
	 * @param {int} $gid 用户分组gid
	 * @return {boolean}
	 * @example \Yii::$app->wechat->updateGroupUser($id, $gid);
	 */
	public function updateGroupUser($id, $gid) {
		$user = WechatUser::findOne($id);
		if(!$user) {
			throw new ErrorException('数据查询失败');
		}

		if($user->groupid == $gid) {
			return true;
		}

		$data = $this->getData('/groups/members/update', [
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
	 * @param {int} $id 用户分组id
	 * @param {string} [$name] 用户分组名字, 30个字符以内, 默认直接取数据库中的值
	 * @return {boolean}
	 * @example \Yii::$app->wechat->deleteGroup($id, $name);
	 */
	public function deleteGroup($id) {
		$group = WechatUserGroup::findOne($id);
		if(!$group) {
			throw new ErrorException('数据查询失败');
		}

		$data = $this->getData('/groups/delete', [
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
	 * @param {int} $id 用户分组id
	 * @param {string} [$name] 分组名字, 30个字符以内, 默认直接取数据库中的值
	 * @return {boolean}
	 * @example \Yii::$app->wechat->updateGroup($id, $name);
	 */
	public function updateGroup($id, $name = null) {
		$group = WechatUserGroup::findOne($id);
		if(!$group) {
			throw new ErrorException('数据查询失败');
		}

		$data = $this->getData('/groups/update', [
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
		$data = $this->getData('/groups/create', [
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
		$data = $this->getData('/groups/get', [
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
	 * @param {string} $openid openid
	 * @return {int}
	 * @example \Yii::$app->wechat->getUserGroup($openid);
	 */
	public function getUserGroup($openid) {
		$data = $this->getData('/groups/getid', [
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
	 * @param {int} $id 用户id
	 * @param {string} [$remark] 备注名, 长度必须小于30字符, 默认直接取数据库中的值
	 * @return {boolean}
	 * @example \Yii::$app->wechat->updateUserRemark($id, $remark);
	 */
	public function updateUserRemark($id, $remark = null) {
		$user = WechatUser::findOne($id);
		if(!$user) {
			throw new ErrorException('数据查询失败');
		}

		$data = $this->getData('/user/info/updateremark', [
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
	 * @param {int} $id 用户id
	 * @return {boolean}
	 * @example \Yii::$app->wechat->refreshUser($id);
	 */
	public function refreshUser($id) {
		$user = WechatUser::findOne($id);
		if(!$user) {
			throw new ErrorException('数据查询失败');
		}

		$data = $this->getData('/user/info', [
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
			$data = $this->getData('/user/info/batchget', [
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
						$user->nickname = $_user['nickname'];
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
		$data = $this->getData('/user/get', [
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
		$data = $this->getData('/getcallbackip', [
			'access_token' => $this->getAccessToken(),
		]);

		if(isset($data['ip_list'])) {
			$this->wechat->ip_list = Json::encode($data['ip_list']);
			return $this->wechat->save();
		}
		
		return $this->errcode == 0;
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
		if(empty($this->wechat->access_token) || $this->wechat->expired_at < $time) {
			$data = $this->getData('/token', [
				'grant_type' => 'client_credential',
				'appid' => $this->wechat->appid,
				'secret' => $this->wechat->secret,
			]);
			if(isset($data['access_token']) && isset($data['expires_in'])) {
				$this->wechat->access_token = $data['access_token'];
				$this->wechat->expired_at = $time + $data['expires_in'];
				$this->wechat->save();
			}
		}

		return $this->wechat->access_token;
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
	 * 获取数据
	 * @method getData
	 * @since 0.0.1
	 * @param {string} $action 接口名称
	 * @param {array} $query 参数
	 * @param {array} [$data] 数据
	 * @return {array}
	 */
	private function getData($action, $query, $data = null) {
		$result = Json::decode($this->curl($this->getApiUrl($action, $query), $data));

		if(!$result) {
			$this->errcode = '503';
			$this->errmsg = '接口服务不可用';
		}

		if(isset($result['errcode']) && isset($result['errmsg'])) {
			$this->errcode = $result['errcode'];
			$this->errmsg = $this->getMessage($result['errmsg']);
		}

		return $result;
	}

	/**
	 * 获取接口完整访问地址
	 * @method getApiUrl
	 * @since 0.0.1
	 * @param {string} $action 接口名称
	 * @param {array} $query 参数
	 * @return {string}
	 */
	private function getApiUrl($action, $query) {
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

		return isset($this->messages[$status]) ? $this->messages[$status] : null;
	}

	/**
	 * curl远程获取数据方法
	 * @method curl
	 * @since 0.0.1
	 * @param {string} $url 请求地址
	 * @param {array|string} [$data] post数据
	 * @param {string} [$useragent] 模拟浏览器用户代理信息
	 * @return {string}
	 */
	private function curl($url, $data = null, $useragent = null) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		if(!empty($data)) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}

		if(!empty($useragent)) {
			curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		}

		$data = curl_exec($curl);
		curl_close($curl);

		return $data;
	}

}
