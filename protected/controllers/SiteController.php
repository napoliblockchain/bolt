<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Settings');
Yii::import('libs.NaPacks.Logo');
Yii::import('libs.NaPacks.SaveModels');
Yii::import('libs.NaPacks.Save');

class SiteController extends Controller
{
	public function init()
	{
		// nulla da eseguire
		Yii::app()->language = ( isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'it' );
		Yii::app()->sourceLanguage = ( isset($_COOKIE['langSource']) ? $_COOKIE['langSource'] : 'it_it' );

		new JsTrans('js',Yii::app()->language); // javascript translation
	}

	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
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
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array(
					'index', //pagina iniziale: fa il redirect a login
					'signup', // registrazione utente
					'login', //pagina di login

					'contactForm', //form di contatto per bug, ecc. ecc.

					'oauth2callback', // google oauth

					'changelanguage', //cambia la lingua

					'error', //pagina di errore

					'logout', //si scollega dall'applicazione e redirect a login
					'activate', // attiva un utenza ??? FUNZIONA ANCORA??
					'recoverypassword',// PAGINA DI LOGIN PER IL RIPRISTINO DELLA PASSWORD
					'regenerate', // ACTIONI CHE RIGENERA LA PASSWORD
					'check2fa', // controlla l'esistenza del 2fa per un utente
					//'printPrivacy', // STAMPA LA PRIVACY ?? FUNZIONA??

				),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array(
					'dash', // pagina dashboard dopo aver effettuato il login
				),
				'users'=>array('@'),
			),
		);
	}

	/**
	 * Displays the contact page
	 */
	public function actionContactForm()
	{
		$this->layout='//layouts/column_login'; //NON ATTIVA IL BACKEND

		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			$model->reCaptcha=$_POST['reCaptcha'];

			if($model->validate())
			{
				if($_FILES['ContactForm']['error']['attach']==0){
					if($_FILES['ContactForm']['size']['attach'] < 3000000){ //< 3Mb

						$path = Yii::app()->basePath . '/../uploads/' . $_FILES['ContactForm']['name']['attach'];
						if (gethostname() == 'blockchain1'){
							$host = 'https://bolt-tts.tk';
						}elseif (gethostname()=='CGF6135T' || gethostname()=='NUNZIA'){ // SERVE PER LE PROVE IN UFFICIO
							$host = 'https://'.$_SERVER['HTTP_HOST'].'/bolt';
						}else{
							$host = 'https://bolt.napoliblockchain.it';
						}
						$wwwpath = $host.'/uploads/' . $_FILES['ContactForm']['name']['attach'];
						$model->attach = $wwwpath;

						move_uploaded_file($_FILES['ContactForm']['tmp_name']['attach'], $path);
					}else{
						$model->attach = '';
					}
		        }else{
					$model->attach = '';
				}
				$content = array(
					'name' => $model->name,
					'subject' => $model->subject,
					'email' => $model->email,
					'body' => $model->body,
					'attach' => $model->attach,
				);
				NMail::SendMail('contact','000abc',Yii::app()->params['adminEmail'],$content);

				Yii::app()->user->setFlash('contact',Yii::t('lang','Thank you for contacting us. We will respond to you as soon as possible.'));
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	public function actionChangelanguage() {
		$sourceLanguages = [
			'it' => 'it_it',
			'en' => 'en_us',
		];
    	setcookie('lang', $_GET['lang']);
		setcookie('langSource', $sourceLanguages[$_GET['lang']]);

    	$this->redirect(array('site/index'));
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		$this->redirect(array('site/login'));
	}

	/**
	 * controlla l'esistenza del 2fa per un utente
	 */
	public function actionCheck2fa(){
		$users = Users::model()->findByAttributes([
			'email'=>$_POST['username'],
			'oauth_provider'=>$_POST['oauth_provider'],
		]);

		if (null !== $users && null !== $users->ga_secret_key && $users->ga_secret_key <> '')
			echo CJSON::encode(["response"=>true]);
		else
			echo CJSON::encode(["response"=>false]);
	}


	public function actionRecoverypassword()
	{
		$this->layout='//layouts/column_login'; //NON ATTIVA IL BACKEND
		$model=new RecoverypasswordForm;

		if(isset($_POST['RecoverypasswordForm']))
		{
			$model->attributes=$_POST['RecoverypasswordForm'];
			$model->reCaptcha=$_POST['reCaptcha'];

			// validate user input and redirect to the previous page if valid
			if($model->validate()){
				// cerco user
				$users = Users::model()->findByAttributes([
					'email'=>$model->username,
					'oauth_provider'=>$model->password,
				]);
				$users->activation_code = md5(Utils::passwordGenerator()); //creo un nuovo activation_code
				//$users->status_activation_code = 0; // lo user adesso è inattivo
				// noN posso impostarlo a zero, altrimenti qualunque malintenzionato potrebbe inserire una
				//qualunque mail e disattivare l'account di chiunque....
				$users->save();
				NMail::SendMail('recovery',crypt::Encrypt($users->id_user),$model->username,'123456',$users->activation_code);
				$this->render('recovery/sent');
				exit;
			}
		}
		$this->render('recovery/_login',array('model'=>$model));
	}

	public function actionRegenerate($activation_code)
	{
		// echo '<pre>'.print_r($_POST,true).'</pre>';


		$this->layout='//layouts/column_login';
		$explode = explode(',',crypt::Decrypt($activation_code));

		// echo '<pre>'.print_r($explode,true).'</pre>';
		// exit;

		if (isset($explode[1])){
			$model=Users::model()->findByPk(crypt::Decrypt($explode[1]));
			if ($model !== null && $model->activation_code == $explode[0]){
				if(isset($_POST['Users']))
				{
					$flag = true;
					$model->password = $_POST['Users']['password'];
					if ($_POST['Users']['password'] != $_POST['Users']['password_confirm']){
						$model->addError('password', Yii::t('lang','Passwords do not match.'));
						$flag = false;
					}
					if (empty($_POST['Users']['password'])){
						$model->addError('password', Yii::t('lang','The Password field cannot be empty.'));
						$flag = false;
					}
					if (empty($_POST['Users']['password_confirm'])){
						$model->addError('password_confirm', Yii::t('lang','The Repeat Password field cannot be empty.'));
						$flag = false;
					}
					if ($flag){
						$model->password = CPasswordHelper::hashPassword($_POST['Users']['password']);
						$model->activation_code = '';
						if ($model->save())
							$this->render('recovery/ok');
						else
							$this->render('recovery/error');
						exit(1);
					}
				}
				$this->render('recovery/_password',array('model'=>$model));
				exit(1);
			}

		}
		$this->render('recovery/error');

	}


	public function actionDash()
	{
		// se sei guest vai a login
		if (Yii::app()->user->isGuest){
			Yii::app()->user->logout();
			$this->redirect(array('site/login'));
		}
		// se non è impostata la variabile objUser vai a login
		if (!(isset(Yii::app()->user->objUser))) {
			Yii::app()->user->logout();
			$this->redirect(array('site/login'));
		}

		$this->redirect(array('wallet/index'));
	}


	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}
	/**
	* Displays the register page
	*/
	public function actionSignup()
	{
		$this->layout='//layouts/column_login';
		$model=new UsersSignup;
		// echo "<pre>".print_r($_POST,true)."</pre>";
		// exit;
		if(isset($_POST['UsersSignup']))
		{
			$model->attributes=$_POST['UsersSignup'];
			$model->reCaptcha=$_POST['reCaptcha'];
			// echo "<pre>".print_r($model->attributes,true)."</pre>";
			// exit;

			if ($model->validate()){
				$savedModel = $_POST['UsersSignup'];
				#echo "<pre>".print_r($savedModel,true)."</pre>";
				#exit;
				$savedPassword = $model->password;
				$model->activation_code = md5($model->password);
				$model->status_activation_code = 0;
				$model->oauth_provider = 'email';
				$model->oauth_uid = Utils::passwordGenerator(18);

				if($model->save()){
					//DEVE ESSERE IDENTICO COME UserIdentity!!
					$explodemail = explode('@',$model->email);
					$explodename = explode('.',$explodemail[0]);

					$social = new Socialusers;
					$social->oauth_provider = $model->oauth_provider;
					$social->oauth_uid = $model->oauth_uid;
					$social->id_user = $model->id_user;
					$social->first_name = $explodename[0];
					$social->last_name = isset($explodename[1]) ? $explodename[1] : '';
					$social->username = $explodemail[0];
					$social->email = $model->email;
					$social->picture = 'css/images/anonymous.png';
					$social->save();

					// echo "<pre>".print_r($social->attributes,true)."</pre>";
					// exit;

					//Invio mail all'user
					NMail::SendMail('signup',crypt::Encrypt($model->id_user),$model->email,$savedPassword,$model->activation_code);

					$this->render('signup/signup',array(
						//'model'=>$model,
					));
					exit(1);
				}
				//reimposto le password in caso di errore
				$model->password = $savedPassword;
				//$model->password_confirm = $savedPassword;
			}

		}

		$this->render('signup/index',array(
			'model'=>$model,
		));
	}

	/**
	* Displays the login page
	*/
	public function actionLogin()
	{
		setcookie('G_AUTHUSER_LOGOUT','TRUE'); // FIX GOOGLE OAUTH
		//$this->layout = "/column2";
		$model=new LoginForm;
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			// echo "<pre>".print_r($_POST,true)."</pre>";
			// exit;
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(array('site/dash'));

		}
		#echo Yii::app()->user->objUser['facade'];
		#exit;

		if (!isset(Yii::app()->user->objUser)){
			$this->layout='//layouts/column_login';
			$this->render('login',array('model'=>$model)); // display the login form if not connected or validated user
		}else {
			$this->redirect(array('site/dash'));
		}
	}



	/**
	* Logs out the current user and redirect to homepage.
	*/
	public function actionLogout()
	{
		Yii::app()->user->logout();
		setcookie('PHPSESSID', time() - 93600);
		setcookie('tg_user', '');
		setcookie('stel_ssid', '');
		setcookie('stel_token', '');
		setcookie('G_AUTHUSER_LOGOUT','TRUE');
		// echo "<pre>".print_r($_COOKIE,true)."</pre>";
		// exit;
		$this->redirect(['site/index']);
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionActivate($activation_code)
	{
		$this->layout='//layouts/column_login';
		$explode = explode(',',crypt::Decrypt($activation_code));
		//$explode = explode(',',$activation_code);
		$model=Users::model()->findByPk(crypt::Decrypt($explode[1]));

		if (isset($model->activation_code)){
			switch ($model->status_activation_code){
				case 0:
					if ($model->activation_code == $explode[0]){
						$model->status_activation_code = 1;
						$model->activation_code = '0'; //Deve essere 0 e non (vuoto) altrimenti va in errore!
						if($model->update())
							$this->render('activate/ok');
						else
							$this->render('activate/error');
					}else{
						$this->render('activate/error');
					}
					break;
				case 1:
					$this->render('activate/ok');
					break;
			}
		}else{
			$this->render('activate/error');
		}
	}
}
