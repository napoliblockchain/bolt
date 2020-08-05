<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
	const ERROR_USERNAME_NOT_ACTIVE = 3;
	const ERROR_GOOGLE_NOT_AUTHENTICATE = 5;

	private $_id;
	public $oauth_provider;
	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		$save = new Save;
		// echo '<pre>'.print_r($_POST,true).'</pre>';
		// exit;

		if (isset($_GET['r']) && $_GET['r'] == 'telegram/CheckAuthorization')
			$this->oauth_provider = 'telegram';

		if (isset($_GET['r']) && $_GET['r'] == 'google/CheckAuthorization')
			$this->oauth_provider = 'google';

		if (isset($_POST['LoginForm']))
			$this->oauth_provider = $_POST['LoginForm']['oauth_provider'];

		// echo '<pre>'.print_r($this->oauth_provider,true).'</pre>';
		// echo '<pre>'.print_r($this->username,true).'</pre>';
		// exit;

		//Creo la query
		$record = Users::model()->findByAttributes([
			'email'=>$this->username,
			'oauth_provider'=>$this->oauth_provider,
		]);

		if($record===null){
			$this->errorCode=self::ERROR_USERNAME_INVALID;
			$save->WriteLog('bolt','useridentity','authenticate','Incorrect username: '.$this->username);
		}
		else if(!CPasswordHelper::verifyPassword($this->password,$record->password) && !isset($_COOKIE['tg_user'])){
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
			$save->WriteLog('bolt','useridentity','authenticate','Incorrect password for user: '.$this->username);
		}
		else if($record->status_activation_code == 0){
		 	$this->errorCode=self::ERROR_USERNAME_NOT_ACTIVE;
			$save->WriteLog('bolt','useridentity','authenticate','User not active: '.$this->username);
		}
		else
		{
			$valid = true;
			// Per rendere facoltativo il 2fa, verifico prima che il campo sia riempito
			// In caso positivo attivo il 2fa, altrimenti proseguo...
			// if (null !== $record->ga_secret_key){
			// 	//verifico che OTP di google authenticator sia passato correttamente
			// 	$key = CHtml::encode($_POST['LoginForm']['ga_cod']);
			// 	$ga = new GoogleAuthenticator();
			//
			// 	$checkResult = $ga->verifyCode($record->ga_secret_key, $key, 2);    // 2 = 2*30sec clock tolerance
			// 	if (!$checkResult){
			// 		$save->WriteLog('bolt','useridentity','authenticate','2fa not valid: '.$this->username);
			// 		$this->errorCode=self::ERROR_GOOGLE_NOT_AUTHENTICATE;
			// 		$valid = false;
			// 	}
			// }

			if ($valid){
				//altrimenti, prosegue...
				$this->_id=$record->id_user;
				$this->errorCode=self::ERROR_NONE;

				// social user parameters
				$social = Socialusers::model()->findByAttributes([
					'oauth_uid'=>$record->oauth_uid,
					'oauth_provider'=>$this->oauth_provider,
				]);

				// fix per salvare un social user nel caso non fosse stato salvato
				if (null === $social){
					//DEVE ESSERE IDENTICO COME site/signup!!
					$social = new Socialusers;
					$explodemail = explode('@',$this->username);
					$explodename = explode('.',$explodemail[0]);

					$social = new Socialusers;
					$social->oauth_provider = $this->oauth_provider;
					$social->oauth_uid = $record->oauth_uid;
					$social->id_user = $record->id_user;
					$social->first_name = $explodename[0];
					$social->last_name = isset($explodename[1]) ? $explodename[1] : '';
					$social->username = $explodemail[0];
					$social->email = $this->username;
					$social->picture = 'css/images/anonymous.png';

					$social->insert();
				}


				// $userSettings = Settings::loadUser($record->id_user);
				// echo "<pre>".print_r($social->attributes,true)."</pre>";
				// exit;
				$array = array(
					'id_user' => $record->id_user,
					'name' => $social->first_name,
					'surname' => $social->last_name,
					'email' => $this->username,
					'username' => $social->username,
					'picture' => $social->picture,
					'provider'=> $social->oauth_provider,
					'oauth_uid'=> $record->oauth_uid,
					'facade' => 'dashboard',
				);
				// echo "<pre>".print_r($array,true)."</pre>";
				// exit;
				$save->WriteLog('bolt','useridentity','authenticate','User '.$this->username. ' logged in.');

				$this->setState('objUser', $array);
			}
		}
		return !$this->errorCode;
	}
}
