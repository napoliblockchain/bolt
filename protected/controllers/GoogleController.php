<?php
Yii::import('libs.NaPacks.SaveModels');
Yii::import('libs.NaPacks.Save');

require_once Yii::app()->params['libsPath'] . '/OAuth/oauth-google/login.php';

class GoogleController extends Controller
{

	public function init()
	{
		if (!isset($_POST) ){
			Yii::app()->user->logout();
			$this->redirect(Yii::app()->homeUrl);
		}
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
					'CheckAuthorization', // check authorization
					'resetCookies', // reset cookies for fresh google authentication
				),
				'users'=>array('*'), // no login
				//'users'=>array('@'), //logged users
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


	public function actionResetCookies()
	{
		setcookie('G_AUTHUSER_LOGOUT','');
	}



	/**
	 * This function check Telegram authorization
	 */
	public function actionCheckAuthorization()
	{
		if (isset($_COOKIE['G_AUTHUSER_LOGOUT']) && $_COOKIE['G_AUTHUSER_LOGOUT'] == 'TRUE'){
			setcookie('G_AUTHUSER_LOGOUT','');
			$auth_data['success'] = false;
			echo CJSON::encode($auth_data);
			exit(1);
		}

		$login = new \jambtc\google\Login(false);
		$auth_data = $_GET;

		$auth_data['oauth_provider'] = 'google';

		$this->saveUserData($auth_data);

		$model=new LoginForm;
		$model->username = $auth_data['email'];
		$model->password = $auth_data['id'];
		$model->oauth_provider = 'google';

		if($model->validate() && $model->login())
		 	$auth_data['success'] = true;
			//$this->redirect(array('site/dash'));
		else
			$auth_data['success'] = false;
		  //$this->redirect(array('site/login'));

		 echo CJSON::encode($auth_data);
	}

	private function saveUserData($auth_data)
	{
		$model = Users::model()->findByAttributes([
			'oauth_provider'=>'google',
			'oauth_uid'=>$auth_data['id'],
		]);

		if (null === $model){
			$model=new Users;
			$model->email = $auth_data['email'];
			$model->password = $auth_data['id'];
			$model->ga_secret_key = null;
			$model->activation_code = 0;
			$model->status_activation_code = 1;
			$model->oauth_provider = 'google';
			$model->oauth_uid = $auth_data['id'];

			if ($model->insert()){
				$social = new Socialusers;
				$social->oauth_provider = 'google';
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
			$social->oauth_provider = 'google';
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
	}


	// public function actionCheckAuthorization2()
	// {
	// 	if (isset($_COOKIE['G_AUTHUSER_LOGOUT']) && $_COOKIE['G_AUTHUSER_LOGOUT'] == 'TRUE'){
	// 		setcookie('G_AUTHUSER_LOGOUT','');
	// 		$auth_data['success'] = false;
	// 		echo CJSON::encode($auth_data);
	// 		exit(1);
	// 	}
	// 	// echo "<pre>cookie".print_r($_COOKIE,true)."</pre>";
	// 	// echo "<pre>POST".print_r($_POST,true)."</pre>";
	// 	// exit;
	// 	$auth_data = $_POST;
	//
	// 	$model = Users::model()->findByAttributes([
	// 		'email'=>$auth_data['email'],
	// 		'oauth_provider'=>'google',
	// 	]);
	// 	// echo "<pre>".print_r($model,true)."</pre>";
	// 	// exit;
	//
	// 	if (null === $model){
	// 		// echo "<pre>null model".print_r($model,true)."</pre>";
	// 		// exit;
	// 		$model=new Users;
	// 		$model->email = $auth_data['email'];
	// 		$model->password = $auth_data['id'];
	// 		$model->ga_secret_key = null;
	// 		$model->activation_code = 0;
	// 		$model->status_activation_code = 1;
	// 		$model->oauth_provider = 'google';
	// 		$model->oauth_uid = $auth_data['id'];
	// 		// echo "<pre>prima di salvare".print_r($model,true)."</pre>";
	//
	// 		if ($model->insert()){
	// 			// echo "<pre>dopo salvato".print_r($model,true)."</pre>";
	// 			// exit;
	// 			$social = new Socialusers;
	// 			$social->oauth_provider = 'google';
	// 			$social->oauth_uid = $auth_data['id'];
	// 			$social->id_user = $model->id_user;
	// 			$social->first_name = $auth_data['first_name'];
	// 			$social->last_name = $auth_data['last_name'];
	// 			$social->username = $auth_data['username'];
	// 			$social->email = $auth_data['email'];
	// 			$social->picture = $auth_data['picture'];
	// 			$social->insert();
	// 		}
	// 	}else{
	// 		$social = Socialusers::model()->findByAttributes(['id_user'=>$model->id_user]);
	// 		if (null === $social){
	// 			$social = new Socialusers;
	// 		}
	// 		$social->oauth_provider = 'google';
	// 		$social->oauth_uid = $auth_data['id'];
	// 		$social->id_user = $model->id_user;
	// 		$social->first_name = $auth_data['first_name'];
	// 		$social->last_name = $auth_data['last_name'];
	// 		$social->username = $auth_data['username'];
	// 		$social->email = $auth_data['email'];
	// 		$social->picture = $auth_data['picture'];
	// 		$social->save();
	// 	}
	//
	// 	$auth_data['oauth_provider'] = 'google';
	// 	$auth_data['success'] = true;
	// 	echo CJSON::encode($auth_data);
	// }
}
