<?php

/**
 * This is the model class for table "np_log".
 *
 * The followings are the available columns in table 'np_log':
 * @property integer $id_log
 * @property integer $timestamp
 * @property integer $id_user
 * @property string $remote_address
 * @property string $browser
 * @property string $app
 * @property string $controller
 * @property string $action
 * @property string $description
 * @property integer $die
 */
class Log extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'np_log';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('timestamp, remote_address, browser, app, controller, action, description, die', 'required'),
			array('timestamp, id_user, die', 'numerical', 'integerOnly'=>true),
			array('remote_address', 'length', 'max'=>60),
			array('browser', 'length', 'max'=>500),
			array('app, controller, action', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_log, timestamp, id_user, remote_address, browser, app, controller, action, description, die', 'safe', 'on'=>'search'),
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
			'id_log' => 'Id Log',
			'timestamp' => 'Timestamp',
			'id_user' => 'Id User',
			'remote_address' => 'Remote Address',
			'browser' => 'Browser',
			'app' => 'App',
			'controller' => 'Controller',
			'action' => 'Action',
			'description' => 'Description',
			'die' => 'Die',
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

		$criteria->compare('id_log',$this->id_log);
		$criteria->compare('timestamp',$this->timestamp);
		$criteria->compare('id_user',$this->id_user);
		$criteria->compare('remote_address',$this->remote_address,true);
		$criteria->compare('browser',$this->browser,true);
		$criteria->compare('app',$this->app,true);
		$criteria->compare('controller',$this->controller,true);
		$criteria->compare('action',$this->action,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('die',$this->die);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Log the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
