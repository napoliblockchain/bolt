
<?php

/**
 * This is the model class for table "np_settings".
 *
 * The followings are the available columns in table 'np_settings_user':
 * @property integer $id_user
 * @property integer $id_wallet
 *
 */
class SettingsUserForm extends CFormModel
{
	public $id_user;
	public $id_wallet;

	public $scadenzaPin;
	public $language;

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id_user, id_wallet, scadenzaPin', 'numerical', 'integerOnly'=>true),
		);
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id_setting' => Yii::t('model','Id Settings'),
			'id_user' => Yii::t('model','Id User'),
			'id_wallet' => Yii::t('model','Id Wallet'),
			'scadenzaPin' => Yii::t('model','Pin expiring'),
			'language' => Yii::t('model','Language'),
		);
	}
}
