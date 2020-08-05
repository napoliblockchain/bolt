<?php

/**
 * This is the model class for table "np_tokens".
 *
 * The followings are the available columns in table 'np_tokens':
 * @property integer $id_token
 * @property integer $id_user
 * @property string $type
 * @property string $status
 * @property double $token_price
 * @property double $token_ricevuti
 * @property double $fiat_price
 * @property string $currency
 * @property string $item_desc
 * @property string $item_code
 * @property integer $invoice_timestamp
 * @property integer $expiration_timestamp
 * @property double $rate
 * @property string $from_address
 * @property string $to_address
 * @property double $blocknumber
 * @property string $txhash
 */
class Tokens extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'bolt_tokens';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id_user', 'required'),
			array('id_user, invoice_timestamp, expiration_timestamp', 'numerical', 'integerOnly'=>true),
			array('token_price, token_ricevuti, fiat_price, rate, blocknumber', 'numerical'),
			array('type, status, from_address, to_address', 'length', 'max'=>250),
			array('currency', 'length', 'max'=>10),
			array('item_desc, item_code', 'length', 'max'=>60),
			array('txhash', 'length', 'max'=>80),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_token, id_user, type, status, token_price, token_ricevuti, fiat_price, currency, item_desc, item_code, invoice_timestamp, expiration_timestamp, rate, to_address, from_address, blocknumber, txhash', 'safe', 'on'=>'search'),
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
			'id_token' => Yii::t('model','Id Token'),
			'id_user' => Yii::t('model','Id User'),
			'type' => Yii::t('model','Type'),
			'status' => Yii::t('model','Status'),
			'token_price' => Yii::t('model','Price'),
			'token_ricevuti' => Yii::t('model','Token Ricevuti'),
			'fiat_price' => Yii::t('model','Fiat Price'),
			'currency' => Yii::t('model','Currency'),
			'item_desc' => Yii::t('model','Item Desc'),
			'item_code' => Yii::t('model','Item Code'),
			'invoice_timestamp' => Yii::t('model','Date'),
			'expiration_timestamp' => Yii::t('model','Expiration Timestamp'),
			'rate' => Yii::t('model','Rate'),
			'from_address' => Yii::t('model','from Address'),
			'to_address' => Yii::t('model','to Address'),
			'blocknumber' => Yii::t('model','Blocknumber'),
			'txhash' => Yii::t('model','Tx Hash'),
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

		// echo '<pre>'.print_r($this,true).'</pre>';
		// exit;

		$criteria->compare('id_token',$this->id_token);
		$criteria->compare('id_user',$this->id_user);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('token_price',$this->token_price);
		$criteria->compare('token_ricevuti',$this->token_ricevuti);
		$criteria->compare('fiat_price',$this->fiat_price);
		$criteria->compare('currency',$this->currency,true);
		$criteria->compare('item_desc',$this->item_desc,true);
		$criteria->compare('item_code',$this->item_code,true);
		$criteria->compare('invoice_timestamp',$this->invoice_timestamp);
		$criteria->compare('expiration_timestamp',$this->expiration_timestamp);
		$criteria->compare('rate',$this->rate);
		$criteria->compare('blocknumber',$this->blocknumber);
		$criteria->compare('txhash',$this->txhash,true);

		$criteria->addInCondition('from_address',[$this->from_address]);
		$criteria->addInCondition('to_address',[$this->from_address],'OR');

		 // echo '<pre>'.print_r($criteria,true).'</pre>';
		 // exit;
		// $criteria->compare('from_address',$this->from_address,true);
		// $criteria->compare('to_address',$this->to_address,true);


		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>array(
					'invoice_timestamp'=>true,
				)
			),
			'pagination' => array(
			  'pageSize' => 10,
		  	),
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Tokens the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
