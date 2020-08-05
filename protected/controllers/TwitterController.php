<?php
class TwitterController extends Controller
{



	public function init()
	{
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
					'request_token', //richiede il token
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

	public function actionRequest_token()
	{
		$url = 'https://api.twitter.com/oauth/request_token';

		$request = webRequest::getUrl($url,$url,array('oauth_callback'=>'http://localhost'),'POST');
		echo "<pre>".print_r($request,true)."</pre>";
		exit;

	}


	/**
	 * This function check authorization
	 */
	public function actionCheckAuthorization()
	{
		// echo "<pre>".print_r($_POST,true)."</pre>";
		// exit;
		$auth_data = $_POST;

		$model = Users::model()->findByAttributes([
			'email'=>$auth_data['email'],
			'oauth_provider'=>'facebook',
		]);
		if (null === $model){
			$model=new Users;
			$model->email = $auth_data['email'];
			$model->password = CPasswordHelper::hashPassword($auth_data['id']);
			$model->ga_secret_key = null;
			$model->activation_code = 0;
			$model->status_activation_code = 1;
			$model->oauth_provider = 'facebook';
			$model->oauth_uid = $auth_data['id'];

			if ($model->save()){
				$social = new Socialusers;
				$social->oauth_provider = 'facebook';
				$social->oauth_uid = $auth_data['id'];
				$social->id_user = $model->id_user;
				$social->first_name = $auth_data['first_name'];
				$social->last_name = $auth_data['last_name'];
				$social->username = $auth_data['username'];
				$social->email = $auth_data['email'];
				$social->picture = $auth_data['picture'];
				$social->save();
			}
		}else{
			$social = Socialusers::model()->findByAttributes(['id_user'=>$model->id_user]);
			if (null === $social){
				$social = new Socialusers;
			}
			$social->oauth_provider = 'facebook';
			$social->oauth_uid = $auth_data['id'];
			$social->id_user = $model->id_user;
			$social->first_name = $auth_data['first_name'];
			$social->last_name = $auth_data['last_name'];
			$social->username = $auth_data['username'];
			$social->email = $auth_data['email'];
			$social->picture = $auth_data['picture'];
			$social->save();
		}

		$auth_data['oauth_provider'] = 'facebook';
		$auth_data['success'] = true;
		echo CJSON::encode($auth_data);
	}
}
