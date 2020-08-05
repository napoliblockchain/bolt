<?php

/**
 * This is the model class for table "np_vapid_subscription".
 *
 * The followings are the available columns in table 'np_vapid_subscription':
 * @property integer $id
 * @property string $browser
 * @property string $endpoint
 * @property string $auth
 * @property string $p256dh
 * @property string $type
 */
class PushSubscriptions extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'bolt_vapid_subscription';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id_user, type, browser, endpoint, auth, p256dh', 'required'),
			array('browser, endpoint, auth, p256dh', 'length', 'max'=>1000),
			array('type', 'length', 'max'=>20),
			array('id_user', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_subscription, id_user, type, browser, endpoint, auth, p256dh', 'safe', 'on'=>'search'),
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
			'id_subscription' => Yii::t('model','ID'),
			'id_user' => Yii::t('model','id_user'),
			'type' => Yii::t('model','type'),
			'browser' => Yii::t('model','Browser'),
			'endpoint' => Yii::t('model','Endpoint'),
			'auth' => Yii::t('model','Auth'),
			'p256dh' => Yii::t('model','P256dh'),
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

		$criteria->compare('id_subscription',$this->id_subscription);
		$criteria->compare('id_user',$this->id_user,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('browser',$this->browser,true);
		$criteria->compare('endpoint',$this->endpoint,true);
		$criteria->compare('auth',$this->auth,true);
		$criteria->compare('p256dh',$this->p256dh,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PushSubscriptions the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
