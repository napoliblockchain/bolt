<?php

/**
 * This is the model class for table "np_merchants".
 *
 * The followings are the available columns in table 'np_merchants':
 * @property integer $id_merchant
 * @property string $denomination
 * @property string $vat
 * @property string $address
 * @property string $city
 * @property string $county
 * @property string $cap
 * @property integer deleted
 */
class Merchants extends CActiveRecord
{
	//private $send_mail;
	public $provincia;


	public function init() { $this->setTableAlias( '_merchants_' ); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'np_merchants';
	}

	public function scopes() {
	    return array(
	        'orderByDenomination' => array('order' => 'denomination ASC'),
	    );
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id_user, denomination, vat, address, city, county, cap', 'required'),
			array('id_user', 'numerical', 'integerOnly'=>true),
			array('denomination, vat, address, city, county, cap', 'length', 'max'=>250),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_merchant, id_user, denomination, vat, address, city, county, cap, deleted', 'safe', 'on'=>'search'),
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
			'id_merchant' => 'Id Merchant',
			'id_user' => 'Utente/Socio',
			'denomination' => 'Denominazione',
			'vat' => 'Partita iva',
			'address' => 'Indirizzo',
			'city' => 'Città',
			'county' => 'Stato',
			'cap' => 'Cap',
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

		$criteria->compare('id_merchant',$this->id_merchant);
		$criteria->compare('id_user',$this->id_user);
		$criteria->compare('denomination',$this->denomination,true);
		$criteria->compare('vat',$this->vat,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('city',$this->city,true);
		$criteria->compare('county',$this->county,true);
		$criteria->compare('cap',$this->cap,true);
		$criteria->compare('deleted',0,true);
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>array(
					'denomination'=>false,
				)
			),
			'pagination' => array(
			  'pageSize' => 20,
		  	),
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Merchants the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
