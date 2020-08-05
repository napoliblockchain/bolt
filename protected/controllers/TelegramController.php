<?php
require_once Yii::app()->params['libsPath'] . '/oauth/telegram/login.php';

class TelegramController extends Controller
{
	public function init()
	{
		define('BOT_TOKEN', Settings::load()->telegramToken); // place bot token of your bot here
		define('BOT_USERNAME', Settings::load()->telegramBotName); // place username of your bot here
	}

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', // allow user to perform actions
				'actions'=>array(
					'CheckAuthorization', // check telegram authorization
					//'getTelegramUserData',

				),
				'users'=>array('*'), // no login
				//'users'=>array('@'), //logged users
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


	/**
	 * This function check Telegram authorization
	 */

	public function actionCheckAuthorization()
	{
		$login = new \jambtc\telegram\Login(BOT_USERNAME,BOT_TOKEN);
		$auth_data = $login->checkTelegramAuthorization($_GET);

		// FIX CAMBIO USERNAME IN TELEGRAM
		$auth_data['email'] = $auth_data['id'] .'@telegram.com';
		$auth_data['oauth_provider'] = 'telegram';

		$this->saveUserData($auth_data);

		$model=new LoginForm;
		$model->username = $auth_data['email'];
		$model->password = $auth_data['id'];
		$model->oauth_provider = 'telegram';

		if($model->validate() && $model->login())
			$this->redirect(array('site/dash'));
		else
		  $this->redirect(array('site/login'));

	}


	private function saveUserData($auth_data)
	{
		$model = Users::model()->findByAttributes([
			'oauth_provider'=>'telegram',
			'oauth_uid'=>$auth_data['id'],
		]);

		if (null === $model){
			$model=new Users;
			$model->email = $auth_data['email'];
			$model->password = $auth_data['id'];
			$model->ga_secret_key = null;
			$model->activation_code = 0;
			$model->status_activation_code = 1;
			$model->oauth_provider = 'telegram';
			$model->oauth_uid = $auth_data['id'];

			if ($model->insert()){
				$social = new Socialusers;
				$social->oauth_provider = 'telegram';
				$social->oauth_uid = $auth_data['id'];
				$social->id_user = $model->id_user;
				$social->first_name = (isset($auth_data['first_name']) ? $auth_data['first_name'] : '');
				$social->last_name = (isset($auth_data['last_name']) ? $auth_data['last_name'] : '');
				$social->username = (isset($auth_data['username']) ? $auth_data['username'] : '');
				$social->email = $auth_data['email'];
				$social->picture = (isset($auth_data['photo_url']) ? $auth_data['photo_url'] : '');
				$social->insert();
			}
		}else{
			$social = Socialusers::model()->findByAttributes(['id_user'=>$model->id_user]);
			if (null === $social){
				$social = new Socialusers;
			}
			$social->oauth_provider = 'telegram';
			$social->oauth_uid = $auth_data['id'];
			$social->id_user = $model->id_user;
			$social->first_name = (isset($auth_data['first_name']) ? $auth_data['first_name'] : '');
			$social->last_name = (isset($auth_data['last_name']) ? $auth_data['last_name'] : '');
			$social->username = (isset($auth_data['username']) ? $auth_data['username'] : '');
			$social->email = $auth_data['email'];
			$social->picture = (isset($auth_data['photo_url']) ? $auth_data['photo_url'] : '');
			$social->save();
		}

		$auth_data_json = CJSON::encode($auth_data);
	  	setcookie('tg_user', $auth_data_json);
	}
}
