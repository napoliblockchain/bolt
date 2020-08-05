<?php

/**
 * ContactForm class.
 * ContactForm is the data structure for keeping
 * contact form data. It is used by the 'contact' action of 'SiteController'.
 */
class ContactForm extends CFormModel
{
	public $name;
	public $email;
	public $subject;
	public $body;
	public $attach;
	public $reCaptcha;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		if (gethostname()=='CGF6135T'){
			return array(
				// name, email, subject and body are required
				array('name, email, subject, body', 'required'),

				// image attach
				array('attach', 'file', 'types'=>'jpg, jpeg, png, gif, tiff, tif','allowEmpty'=>true),

				// email has to be a valid email address
				array('email', 'email'),
				array('reCaptcha ', 'required'),
			);
		}else{
			return array(
				// name, email, subject and body are required
				array('name, email, subject, body', 'required'),

				// image attach
				array('attach', 'file', 'types'=>'jpg, jpeg, png, gif, tiff, tif','allowEmpty'=>true),

				// email has to be a valid email address
				array('email', 'email'),

				array('reCaptcha ', 'required'),
				array('reCaptcha', 'application.extensions.reCaptcha2.SReCaptchaValidator', 'secret' => Settings::load()->reCaptcha2PrivateKey,'message' => 'The verification code is incorrect.'),
			);
		}
	}

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'name' => Yii::t('model','Name'),
			'email' => Yii::t('model','Email where we can contact you'),
			'subject' => Yii::t('model','Subject'),
			'body' => Yii::t('model','Body'),
			'attach' => Yii::t('model','Image'),
			'reCaptcha'=> Yii::t('model','reCaptcha'),
		);
	}
}
