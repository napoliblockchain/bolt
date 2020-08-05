<?php

/**
 * This is the model class for table "bolt_socialusers".
 *
 * The followings are the available columns in table 'bolt_socialusers':
 * @property integer $id_social
 * @property string $oauth_provider
 * @property string $oauth_uid
 * @property integer $id_user
 * @property string $first_name
 * @property string $last_name
 * @property string $username
 * @property string $email
 * @property string $picture
 */
class Socialusers extends CActiveRecord
{
	public $id;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'bolt_socialusers';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('oauth_provider, oauth_uid, id_user, username, email, picture ', 'required'),
			//array('oauth_provider', 'length', 'max'=>8),
			array('oauth_uid, email', 'length', 'max'=>100),
			array('id_user, ', 'numerical'),
			array('first_name, last_name, username', 'length', 'max'=>50),
			array('picture', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_social, oauth_provider, oauth_uid, id_user, first_name, last_name, username, email, picture', 'safe', 'on'=>'search'),
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

	public function scopes() {
	    return array(
	        'orderedBy' => array('order' => 'last_name ASC, first_name ASC, username ASC'),
	    );
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id_social' => Yii::t('model','ID'),
			'oauth_provider' => Yii::t('model','Oauth Provider'),
			'oauth_uid' => Yii::t('model','Oauth Uid'),
			'id_user' => Yii::t('model','id user'),
			'first_name' => Yii::t('model','First Name'),
			'last_name' => Yii::t('model','Last Name'),
			'username' => Yii::t('model','Username'),
			'email' => Yii::t('model','Email'),
			'picture' => Yii::t('model','Picture'),
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
	public function search(){
		$criteria = new CDbCriteria();
		 // echo '<pre>'.print_r($_GET,true).'</pre>';
		 // echo '<pre>'.print_r($this->attributes,true).'</pre>';
		 // exit;
		$ricerca = 'sdjkhsh uihsuihguihgukshjk h euwhuihjkh  h89fh';
		if (isset($_GET['Socialusers']['username'])){
			$ricerca = $_GET['Socialusers']['username'];

			$criteria->addSearchCondition("username", $ricerca,true,'OR');
			$criteria->addSearchCondition("first_name", $ricerca,true,'OR');
			$criteria->addSearchCondition("last_name", $ricerca,true,'OR');
			$criteria->addSearchCondition("email", $ricerca,true,'OR');

			$criteria->addSearchCondition("username", Yii::app()->user->objUser['username'],true,'AND','NOT LIKE');

		}else if (isset($_GET['id'])){
			$criteria->compare('id_social',$this->id_social);
		}else{
			$criteria->compare('username',$ricerca);
		}
		 // echo '<pre>'.print_r($this->id_social,true).'</pre>';
		 // exit;

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination' => array(
			  'pageSize' => 10,
			),
		));

	}
	// public function searchOLD()
	// {
	// 	// @todo Please modify the following code to remove attributes that should not be searched.
	//
	// 	$criteria=new CDbCriteria;
	//
	// 	$criteria->compare('id_social',$this->id_social);
	// 	$criteria->compare('oauth_provider',$this->oauth_provider,true);
	// 	$criteria->compare('oauth_uid',$this->oauth_uid,true);
	// 	$criteria->compare('id_user',$this->id_user,true);
	// 	$criteria->compare('first_name',$this->first_name,true);
	// 	$criteria->compare('last_name',$this->last_name,true);
	// 	$criteria->compare('username',$this->username,true);
	// 	$criteria->compare('email',$this->email,true);
	// 	$criteria->compare('picture',$this->picture,true);
	//
	// 	return new CActiveDataProvider($this, array(
	// 		'criteria'=>$criteria,
	// 	));
	// }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Socialusers the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
