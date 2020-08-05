<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe;
	public $reCaptcha;
	public $oauth_provider;
	//public $verifyCode;

	public $ga_cod;

	private $_identity;



	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			// username and password are required
			array('username, password', 'required'),

			// password needs to be authenticated
			array('password', 'authenticate'),

			// username has to be a valid email address
			array('username', 'email', 'message'=>Yii::t('lang','Email hasn\'t right format.')),

			// verifyCode needs to be entered correctly
			//array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()),


		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'username'=>Yii::t('model','Email'),
			'password'=>Yii::t('model','Password'),
			'ga_cod'=>Yii::t('model','Google 2FA'),
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 * @param string $attribute the name of the attribute to be validated.
	 * @param array $params additional parameters passed with rule when being executed.
	 */
	public function authenticate($attribute,$params)
	{
		if(!$this->hasErrors())
		{
			$this->_identity=new UserIdentity($this->username,$this->password,$this->oauth_provider);
			// if(!$this->_identity->authenticate())
			//  	$this->addError('password','La password e/o l\'email non sono corrette.');
			$this->_identity->authenticate();
			$errorCode = $this->_identity->errorCode;

			switch ($errorCode){
				case UserIdentity::ERROR_PASSWORD_INVALID:
					$this->addError('password',Yii::t('lang','Password is incorrect.'));
					break;

				case UserIdentity::ERROR_USERNAME_INVALID:
					$this->addError('username',Yii::t('lang','Email is incorrect.'));
					break;

				case UserIdentity::ERROR_USERNAME_NOT_ACTIVE:
					$this->addError('password',Yii::t('lang','User is not enabled.'));
					break;

				case UserIdentity::ERROR_GOOGLE_NOT_AUTHENTICATE:
					$this->addError('username',Yii::t('lang','2fa is invalid.'));
					break;

			}
		}
	}

	/**
	 * Logs in the user using the given username and password in the model.
	 * @return boolean whether login is successful
	 */
	public function login()
	{
		if($this->_identity===null)
		{
			$this->_identity=new UserIdentity($this->username,$this->password,$this->oauth_provider);
			$this->_identity->authenticate();
		}
		if($this->_identity->errorCode===UserIdentity::ERROR_NONE)
		{
			$duration=3600*24*90; // 90 days
			Yii::app()->user->login($this->_identity,$duration);
			return true;
		}
		else
			return false;
	}
}
