<?php
use yii\components\Migration;

class m161010_051207_wechat_init extends Migration {

	public $messageCategory ='wechat';

	public function init() {
		$this->messagesPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'messages';

		parent::init();
	}

	public function safeUp() {
		$tableOptions = null;
		if($this->db->driverName === 'mysql') {
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%wechat}}', [
			'appid' => $this->string(68)->comment(\Yii::t($this->messageCategory, 'app id')),
			'name' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'name')),
			'secret' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'app secret')),
			'token' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'token')),
			'aeskey' => $this->string(68)->comment(\Yii::t($this->messageCategory, 'encoding AES key')),
			'access_token' => $this->text()->comment(\Yii::t($this->messageCategory, 'access token')),
			'access_token_expired_at' => $this->integer()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'access token expired time')),
			'jsapi_ticket' => $this->text()->comment(\Yii::t($this->messageCategory, 'js api ticket')),
			'jsapi_ticket_expired_at' => $this->integer()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'js api ticket expired time')),
			'ip_list' => $this->text()->comment(\Yii::t($this->messageCategory, 'wechat server ip list')),
			'count_image' => $this->integer()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'image quantity')),
			'count_voice' => $this->integer()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'voice quantity')),
			'count_video' => $this->integer()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'video quantity')),
			'count_news' => $this->integer()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'news quantity')),
			'created_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'created time')),
			'updated_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'updated time')),
		], $tableOptions);
		$this->addPrimaryKey('appid', '{{%wechat}}', 'appid');
		$this->addCommentOnTable('{{%wechat}}', \Yii::t($this->messageCategory, 'wechat app'));

		$this->createTable('{{%wechat_user}}', [
			'id' => $this->primaryKey()->comment(\Yii::t($this->messageCategory, 'id')),
			'user_id' => $this->string(68)->comment(\Yii::t($this->messageCategory, 'user id')),
			'appid' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'app id')),
			'openid' => $this->string()->notNull()->comment(\Yii::t($this->messageCategory, 'wechat user id')),
			'unionid' => $this->string()->comment(\Yii::t($this->messageCategory, 'union id')),
			'subscribe' => $this->smallInteger()->notNull()->defaultValue(1)->comment(\Yii::t($this->messageCategory, 'subscribe status')),
			'subscribed_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'subscribe time')),
			'nickname' => $this->string()->comment(\Yii::t($this->messageCategory, 'nickname')),
			'remark' => $this->string(68)->comment(\Yii::t($this->messageCategory, 'remark')),
			'groupid' => $this->integer()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'user group id')),
			'sex' => $this->smallInteger()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'sex')),
			'country' => $this->string(68)->comment(\Yii::t($this->messageCategory, 'country')),
			'province' => $this->string(68)->comment(\Yii::t($this->messageCategory, 'province')),
			'city' => $this->string(68)->comment(\Yii::t($this->messageCategory, 'city')),
			'language' => $this->string(68)->comment(\Yii::t($this->messageCategory, 'language')),
			'headimgurl' => $this->text()->comment(\Yii::t($this->messageCategory, 'head image url')),
			'access_token' => $this->text()->comment(\Yii::t($this->messageCategory, 'user page authorization interface access token')),
			'access_token_expired_at' => $this->integer()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'user page authorization interface access token expired time')),
			'refresh_token' => $this->text()->comment(\Yii::t($this->messageCategory, 'user page authorization interface access token\'s refresh token')),
			'created_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'created time')),
			'updated_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'updated time')),
		], $tableOptions);
		$this->createIndex('appid', '{{%wechat_user}}', 'appid');
		$this->createIndex('openid', '{{%wechat_user}}', 'openid');
		$this->addCommentOnTable('{{%wechat_user}}', \Yii::t($this->messageCategory, 'wechat user'));

		$this->createTable('{{%wechat_user_group}}', [
			'id' => $this->primaryKey()->comment(\Yii::t($this->messageCategory, 'id')),
			'appid' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'app id')),
			'gid' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'group id')),
			'name' => $this->string(68)->comment(\Yii::t($this->messageCategory, 'name')),
			'count' => $this->integer()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'user quantity')),
			'created_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'created time')),
			'updated_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'updated time')),
		], $tableOptions);
		$this->createIndex('appid', '{{%wechat_user_group}}', 'appid');
		$this->createIndex('gid', '{{%wechat_user_group}}', 'gid');
		$this->addCommentOnTable('{{%wechat_user_group}}', \Yii::t($this->messageCategory, 'wechat user group'));

		$this->createTable('{{%wechat_menu}}', [
			'id' => $this->primaryKey()->comment(\Yii::t($this->messageCategory, 'id')),
			'appid' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'app id')),
			'conditional' => $this->smallInteger()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'personalized menu')),
			'pid' => $this->integer()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'parent id')),
			'menuid' => $this->integer()->comment(\Yii::t($this->messageCategory, 'menu id')),
			'type' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'response action type')),
			'name' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'name')),
			'key' => $this->string()->comment(\Yii::t($this->messageCategory, 'key')),
			'url' => $this->text()->comment(\Yii::t($this->messageCategory, 'url')),
			'media_id' => $this->string()->comment(\Yii::t($this->messageCategory, 'media id')),
			'group_id' => $this->integer()->comment(\Yii::t($this->messageCategory, 'user group id')),
			'sex' => $this->smallInteger()->comment(\Yii::t($this->messageCategory, 'sex')),
			'client_platform_type' => $this->smallInteger()->comment(\Yii::t($this->messageCategory, 'client platform type')),
			'country' => $this->string(68)->comment(\Yii::t($this->messageCategory, 'country')),
			'province' => $this->string(68)->comment(\Yii::t($this->messageCategory, 'province')),
			'city' => $this->string(68)->comment(\Yii::t($this->messageCategory, 'city')),
			'language' => $this->string(68)->comment(\Yii::t($this->messageCategory, 'language')),
			'list_order' => $this->integer()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'list order')),
			'created_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'created time')),
			'updated_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'updated time')),
		], $tableOptions);
		$this->createIndex('appid', '{{%wechat_menu}}', 'appid');
		$this->createIndex('conditional', '{{%wechat_menu}}', 'conditional');
		$this->createIndex('pid', '{{%wechat_menu}}', 'pid');
		$this->createIndex('menuid', '{{%wechat_menu}}', 'menuid');
		$this->createIndex('type', '{{%wechat_menu}}', 'type');
		$this->addCommentOnTable('{{%wechat_menu}}', \Yii::t($this->messageCategory, 'wechat menu'));

		$this->createTable('{{%wechat_message}}', [
			'id' => $this->bigInteger()->comment(\Yii::t($this->messageCategory, 'id')),
			'appid' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'app id')),
			'type' => $this->smallInteger()->notNull()->defaultValue(1)->comment(\Yii::t($this->messageCategory, 'type')),
			'pid' => $this->bigInteger()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'parent id')),
			'to_user_name' => $this->string()->notNull()->comment(\Yii::t($this->messageCategory, 'receiver id')),
			'from_user_name' => $this->string()->notNull()->comment(\Yii::t($this->messageCategory, 'sender id')),
			'create_time' => $this->integer()->comment(\Yii::t($this->messageCategory, 'created time')),
			'msg_type' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'message type')),
			'msg_id' => $this->string()->comment(\Yii::t($this->messageCategory, 'message id')),
			'media_id' => $this->string()->comment(\Yii::t($this->messageCategory, 'media id')),
			'media_url' => $this->text()->comment(\Yii::t($this->messageCategory, 'media url')),
			'thumb_media_id' => $this->string()->comment(\Yii::t($this->messageCategory, 'thumbnail media id')),
			'thumb_media_url' => $this->text()->comment(\Yii::t($this->messageCategory, 'thumbnail media url')),
			'event' => $this->string(68)->comment(\Yii::t($this->messageCategory, 'event type')),
			'event' => $this->string()->comment(\Yii::t($this->messageCategory, 'event key')),
			'content' => $this->text()->comment(\Yii::t($this->messageCategory, 'content')),
			'pic_url' => $this->text()->comment(\Yii::t($this->messageCategory, 'picture url')),
			'format' => $this->string(68)->comment(\Yii::t($this->messageCategory, 'voice format')),
			'recognition' => $this->string()->comment(\Yii::t($this->messageCategory, 'voice recognition result')),
			'location_x' => $this->string()->comment(\Yii::t($this->messageCategory, 'location latitude')),
			'location_y' => $this->string()->comment(\Yii::t($this->messageCategory, 'location longitude')),
			'scale' => $this->string()->comment(\Yii::t($this->messageCategory, 'map zoom size')),
			'label' => $this->string()->comment(\Yii::t($this->messageCategory, 'location information')),
			'title' => $this->string()->comment(\Yii::t($this->messageCategory, 'title')),
			'description' => $this->string()->comment(\Yii::t($this->messageCategory, 'description')),
			'url' => $this->text()->comment(\Yii::t($this->messageCategory, 'url')),
			'ticket' => $this->string()->comment(\Yii::t($this->messageCategory, 'QR code tiket')),
			'latitude' => $this->string()->comment(\Yii::t($this->messageCategory, 'latitude')),
			'longitude' => $this->string()->comment(\Yii::t($this->messageCategory, 'longitude')),
			'precision' => $this->string()->comment(\Yii::t($this->messageCategory, 'location precision')),
			'menu_id' => $this->integer()->comment(\Yii::t($this->messageCategory, 'menu id')),
			'scan_type' => $this->string()->comment(\Yii::t($this->messageCategory, 'scan type')),
			'scan_result' => $this->string()->comment(\Yii::t($this->messageCategory, 'scan result')),
			'count' => $this->integer()->comment(\Yii::t($this->messageCategory, 'quantity of pictures sent')),
			'pic_list' => $this->text()->comment(\Yii::t($this->messageCategory, 'picture list')),
			'poiname' => $this->string()->comment(\Yii::t($this->messageCategory, 'POI name')),
			'music_url' => $this->text()->comment(\Yii::t($this->messageCategory, 'music url')),
			'hq_music_url' => $this->text()->comment(\Yii::t($this->messageCategory, 'HQ music url')),
			'articles' => $this->text()->comment(\Yii::t($this->messageCategory, 'multiple news')),
			'created_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'created time')),
			'updated_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'updated time')),
		], $tableOptions);
		$this->addPrimaryKey('id', '{{%wechat_message}}', 'id');
		$this->createIndex('appid', '{{%wechat_message}}', 'appid');
		$this->createIndex('type', '{{%wechat_message}}', 'type');
		$this->createIndex('pid', '{{%wechat_message}}', 'pid');
		$this->createIndex('to_user_name', '{{%wechat_message}}', 'to_user_name');
		$this->createIndex('from_user_name', '{{%wechat_message}}', 'from_user_name');
		$this->createIndex('msg_type', '{{%wechat_message}}', 'msg_type');
		$this->addCommentOnTable('{{%wechat_message}}', \Yii::t($this->messageCategory, 'wechat message'));

		$this->createTable('{{%wechat_message_rule}}', [
			'id' => $this->primaryKey()->comment(\Yii::t($this->messageCategory, 'id')),
			'appid' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'app id')),
			'type' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'type')),
			'msg_type' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'message type')),
			'content' => $this->text()->comment(\Yii::t($this->messageCategory, 'content')),
			'news_media_id' => $this->integer()->comment(\Yii::t($this->messageCategory, 'news media id')),
			'material_media_id' => $this->integer()->comment(\Yii::t($this->messageCategory, 'material media id')),
			'thumb_material_media_id' => $this->integer()->comment(\Yii::t($this->messageCategory, 'thumbnail material media id')),
			'title' => $this->string()->comment(\Yii::t($this->messageCategory, 'title')),
			'description' => $this->string()->comment(\Yii::t($this->messageCategory, 'description')),
			'music_url' => $this->text()->comment(\Yii::t($this->messageCategory, 'music url')),
			'hq_music_url' => $this->text()->comment(\Yii::t($this->messageCategory, 'HQ music url')),
			'created_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'created time')),
			'updated_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'updated time')),
		], $tableOptions);
		$this->createIndex('appid', '{{%wechat_message_rule}}', 'appid');
		$this->createIndex('type', '{{%wechat_message_rule}}', 'type');
		$this->createIndex('msg_type', '{{%wechat_message_rule}}', 'msg_type');
		$this->addCommentOnTable('{{%wechat_message_rule}}', \Yii::t($this->messageCategory, 'wechat message reply rule'));

		$this->createTable('{{%wechat_message_keyword}}', [
			'id' => $this->primaryKey()->comment(\Yii::t($this->messageCategory, 'id')),
			'appid' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'app id')),
			'rule_id' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'rule id')),
			'mode' => $this->smallInteger()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'matching pattern')),
			'keyword' => $this->text()->notNull()->comment(\Yii::t($this->messageCategory, 'keyword')),
			'created_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'created time')),
			'updated_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'updated time')),
		], $tableOptions);
		$this->createIndex('appid', '{{%wechat_message_keyword}}', 'appid');
		$this->addCommentOnTable('{{%wechat_message_keyword}}', \Yii::t($this->messageCategory, 'wechat message keyword'));

		$this->createTable('{{%wechat_material}}', [
			'id' => $this->primaryKey()->comment(\Yii::t($this->messageCategory, 'id')),
			'type' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'type')),
			'title' => $this->string(68)->comment(\Yii::t($this->messageCategory, 'title')),
			'description' => $this->string()->comment(\Yii::t($this->messageCategory, 'description')),
			'url' => $this->text()->notNull()->comment(\Yii::t($this->messageCategory, 'url')),
			'created_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'created time')),
			'updated_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'updated time')),
		], $tableOptions);
		$this->createIndex('type', '{{%wechat_material}}', 'type');
		$this->addCommentOnTable('{{%wechat_material}}', \Yii::t($this->messageCategory, 'wechat material'));

		$this->createTable('{{%wechat_material_media}}', [
			'id' => $this->primaryKey()->comment(\Yii::t($this->messageCategory, 'id')),
			'appid' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'app id')),
			'material_id' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'material id')),
			'media_id' => $this->string()->comment(\Yii::t($this->messageCategory, 'media id')),
			'url' => $this->text()->notNull()->comment(\Yii::t($this->messageCategory, 'url')),
			'expired_at' => $this->integer()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'expired time')),
			'created_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'created time')),
			'updated_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'updated time')),
		], $tableOptions);
		$this->createIndex('appid', '{{%wechat_material_media}}', 'appid');
		$this->createIndex('material_id', '{{%wechat_material_media}}', 'material_id');
		$this->addCommentOnTable('{{%wechat_material_media}}', \Yii::t($this->messageCategory, 'wechat material media'));

		$this->createTable('{{%wechat_news}}', [
			'id' => $this->primaryKey()->comment(\Yii::t($this->messageCategory, 'id')),
			'count_item' => $this->integer()->notNull()->defaultValue(1)->comment(\Yii::t($this->messageCategory, 'news item quantity')),
			'created_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'created time')),
			'updated_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'updated time')),
		], $tableOptions);
		$this->createIndex('count_item', '{{%wechat_news}}', 'count_item');
		$this->addCommentOnTable('{{%wechat_news}}', \Yii::t($this->messageCategory, 'wechat news'));

		$this->createTable('{{%wechat_news_item}}', [
			'id' => $this->primaryKey()->comment(\Yii::t($this->messageCategory, 'id')),
			'news_id' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'news id')),
			'title' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'title')),
			'author' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'author')),
			'thumb_material_id' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'thumbnail material id')),
			'show_cover_pic' => $this->smallInteger()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'show cover picture')),
			'digest' => $this->string()->comment(\Yii::t($this->messageCategory, 'digest')),
			'content' => $this->text()->notNull()->comment(\Yii::t($this->messageCategory, 'content')),
			'content_source_url' => $this->text()->comment(\Yii::t($this->messageCategory, 'original article url')),
			'list_order' => $this->integer()->notNull()->defaultValue(0)->comment(\Yii::t($this->messageCategory, 'list order')),
			'created_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'created time')),
			'updated_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'updated time')),
		], $tableOptions);
		$this->createIndex('news_id', '{{%wechat_news_item}}', 'news_id');
		$this->createIndex('thumb_material_id', '{{%wechat_news_item}}', 'thumb_material_id');
		$this->createIndex('list_order', '{{%wechat_news_item}}', 'list_order');
		$this->addCommentOnTable('{{%wechat_news_item}}', \Yii::t($this->messageCategory, 'wechat news item'));

		$this->createTable('{{%wechat_news_cache}}', [
			'id' => $this->primaryKey()->comment(\Yii::t($this->messageCategory, 'id')),
			'news_id' => $this->integer()->comment(\Yii::t($this->messageCategory, 'news id')),
			'items' => $this->text()->comment(\Yii::t($this->messageCategory, 'news item')),
			'created_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'created time')),
			'updated_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'updated time')),
		], $tableOptions);
		$this->addCommentOnTable('{{%wechat_news_cache}}', \Yii::t($this->messageCategory, 'wechat news cache'));

		$this->createTable('{{%wechat_news_image}}', [
			'id' => $this->primaryKey()->comment(\Yii::t($this->messageCategory, 'id')),
			'appid' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'app id')),
			'url' => $this->text()->notNull()->comment(\Yii::t($this->messageCategory, 'url')),
			'url_source' => $this->text()->notNull()->comment(\Yii::t($this->messageCategory, 'source url')),
			'created_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'created time')),
			'updated_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'updated time')),
		], $tableOptions);
		$this->createIndex('appid', '{{%wechat_news_image}}', 'appid');
		$this->addCommentOnTable('{{%wechat_news_image}}', \Yii::t($this->messageCategory, 'wechat nwes image'));

		$this->createTable('{{%wechat_news_media}}', [
			'id' => $this->primaryKey()->comment(\Yii::t($this->messageCategory, 'id')),
			'appid' => $this->string(68)->notNull()->comment(\Yii::t($this->messageCategory, 'app id')),
			'news_id' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'news id')),
			'media_id' => $this->string()->notNull()->comment(\Yii::t($this->messageCategory, 'media id')),
			'urls' => $this->text()->comment(\Yii::t($this->messageCategory, 'news item url')),
			'thumb_material_media_ids' => $this->text()->notNull()->comment(\Yii::t($this->messageCategory, 'thumbnail material media id')),
			'thumb_urls' => $this->text()->comment(\Yii::t($this->messageCategory, 'news item thumbnail url')),
			'url' => $this->text()->notNull()->comment(\Yii::t($this->messageCategory, 'url')),
			'url_source' => $this->text()->notNull()->comment(\Yii::t($this->messageCategory, 'source url')),
			'created_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'created time')),
			'updated_at' => $this->integer()->notNull()->comment(\Yii::t($this->messageCategory, 'updated time')),
		], $tableOptions);
		$this->createIndex('appid', '{{%wechat_news_media}}', 'appid');
		$this->createIndex('news_id', '{{%wechat_news_media}}', 'news_id');
		$this->addCommentOnTable('{{%wechat_news_media}}', \Yii::t($this->messageCategory, 'wechat nwes media'));
	}

	public function safeDown() {
		$this->dropTable('{{%wechat_news_media}}');
		$this->dropTable('{{%wechat_news_image}}');
		$this->dropTable('{{%wechat_news_cache}}');
		$this->dropTable('{{%wechat_news_item}}');
		$this->dropTable('{{%wechat_news}}');
		$this->dropTable('{{%wechat_material_media}}');
		$this->dropTable('{{%wechat_material}}');
		$this->dropTable('{{%wechat_message_keyword}}');
		$this->dropTable('{{%wechat_message_rule}}');
		$this->dropTable('{{%wechat_message}}');
		$this->dropTable('{{%wechat_menu}}');
		$this->dropTable('{{%wechat_user_group}}');
		$this->dropTable('{{%wechat_user}}');
		$this->dropTable('{{%wechat}}');
	}

}


