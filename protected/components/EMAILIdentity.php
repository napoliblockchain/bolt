<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class EMAILIdentity extends CUserIdentity
{
	private $_id;
	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticateEMAIL()
	{
		// echo '<pre>'.print_r($this->username,true).'</pre>';
		// echo '<pre>'.print_r($this->password,true).'</pre>';
		// exit;
		//Creo la query per verificare se esiste negli users questa Email
		$users = Users::model()->findByAttributes([
			'email'=>$this->username,
			'oauth_provider'=>$this->password,
		]);

		if($users===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else
			$this->errorCode=self::ERROR_NONE;

		return !$this->errorCode;
	}
}
