<?php

/**
 * This is the model class for table "np_institutes".
 *
 * The followings are the available columns in table 'np_institutes':
 * @property integer $id_institute
 * @property string $description
 * @property string $wallet_address
 * @property integer $max_wait_time
 * @property string $max_wait_message
 * @property double $default_sending_quantity
 * @property double $min_fund_alert
 * @property string $email_fund_alert
 */
class Institutes extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'np_institutes';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('description, max_wait_time, max_wait_message, default_sending_quantity, min_fund_alert, email_fund_alert', 'required'),

			array('email_fund_alert', 'email'), // email_fund_alert has to be a valid email address

			array('max_wait_time', 'numerical', 'integerOnly'=>true),
			array('default_sending_quantity, min_fund_alert', 'numerical'),
			array('description, max_wait_message', 'length', 'max'=>200),
			array('wallet_address', 'length', 'max'=>50),
			array('email_fund_alert', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_institute, description, wallet_address, max_wait_time, max_wait_message, default_sending_quantity, min_fund_alert, email_fund_alert', 'safe', 'on'=>'search'),
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
			'id_institute' => 'Email',
			'description' => 'Descrizione',
			'wallet_address' => 'Indirizzo Wallet',
			'max_wait_time' => 'Tempo massimo di permanenza nell’istituto (min.)',
			'max_wait_message' => 'Messaggio',
			'default_sending_quantity' => 'Quantità prestabilita Token',
			'min_fund_alert' => 'Soglia di allarme wallet Istituto',
			'email_fund_alert' => 'Email di contatto',
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

		$criteria->compare('id_institute',$this->id_institute);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('wallet_address',$this->wallet_address,true);
		$criteria->compare('max_wait_time',$this->max_wait_time);
		$criteria->compare('max_wait_message',$this->max_wait_message,true);
		$criteria->compare('default_sending_quantity',$this->default_sending_quantity);
		$criteria->compare('min_fund_alert',$this->min_fund_alert);
		$criteria->compare('email_fund_alert',$this->email_fund_alert,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Institutes the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
