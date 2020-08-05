<?php

/**
 * This is the model class for table "np_users".
 *
 * The followings are the available columns in table 'np_users':
 * @property integer $id_user
 * @property integer $id_users_type
 * @property string $status
 * @property string $email
 * @property string $password
 * @property string $name
 * @property string $surname
 * @property string $activation_code
 * @property integer $status_activation_code
 */
class Users extends CActiveRecord
{
	public $ga_cod; //google 2fa code
	public $password_confirm;
	public $reCaptcha;


	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'bolt_users';
	}

	//hash della password dopo il validate!
	public function beforeSave() {
    	if ($this->isNewRecord){ // <---- the difference
        	$this->password=CPasswordHelper::hashPassword($this->password);
			$this->password_confirm=CPasswordHelper::hashPassword($this->password_confirm);
		}
		return true;
 	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('email, password', 'required'),
			// array('email', 'unique',  'message'=>Yii::t('lang','The inserted email is already present.'),'on'=>'insert'),

			array('status_activation_code', 'numerical', 'integerOnly'=>true),
			array('email, password ', 'length', 'max'=>255),

			array('password_confirm', 'compare', 'compareAttribute'=>'password', 'message'=>Yii::t('lang','Passwords do not match.'),'on'=>'insert'),

			array('activation_code', 'length', 'max'=>50),
			array('ga_secret_key', 'length', 'max'=>16),
			array('oauth_provider', 'length', 'max'=>8),
			array('oauth_uid', 'length', 'max'=>100),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_user, email, password, ga_secret_key, activation_code, status_activation_code, oauth_provider, oauth_uid', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id_user' => Yii::t('model','Id User'),
			'email' => Yii::t('model','Email'),
			'password' => Yii::t('model','Password'),
			'ga_secret_key' => Yii::t('model','Google Authentication Code'),
			'activation_code' => Yii::t('model','Activation Code'),
			'status_activation_code' => Yii::t('model','Stato Attivazione'),
			'oauth_provider' => Yii::t('model','oauth_provider'),
			'oauth_uid' => Yii::t('model','oauth_uid'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id_user',$this->id_user);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('ga_secret_key',$this->ga_secret_key,true);
		$criteria->compare('activation_code',$this->activation_code,true);
		$criteria->compare('status_activation_code',$this->status_activation_code);
		$criteria->compare('oauth_provider',$this->oauth_provider,true);
		$criteria->compare('oauth_uid',$this->oauth_uid,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Users the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
