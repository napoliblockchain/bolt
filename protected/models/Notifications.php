<?php

/**
 * This is the model class for table "np_notifications".
 *
 * The followings are the available columns in table 'np_notifications':
 * @property integer id_association
 * @property string $denomination
 * @property string $vat
 * @property string $address
 * @property string $city
 * @property string $county
 * @property string $cap
 */
class Notifications extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'np_notifications';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type_notification, id_tocheck, status, description, url, timestamp, price', 'required'),
			array('id_notification, id_tocheck, timestamp, deleted', 'numerical', 'integerOnly'=>true),

			array('type_notification, status, url, price', 'length', 'max'=>250),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_notification, type_notification, id_tocheck, status, description, url, timestamp, price, deleted', 'safe', 'on'=>'search'),
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
			'id_notification' => Yii::t('model','id_notification'),
			'type_notification' => Yii::t('model','type_notification'),
			'id_tocheck' => Yii::t('model','id_tocheck'),
			'status' => Yii::t('model','status'),
			'description' => Yii::t('model','description'),
			'url' => Yii::t('model','url'),
			'timestamp' => Yii::t('model','timestamp'),
			'price' => Yii::t('model','price'),
			'deleted' => Yii::t('model','deleted'),
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

		$criteria->compare('id_notification',$this->id_notification);
		$criteria->compare('type_notification',$this->type_notification);
		$criteria->compare('id_tocheck',$this->id_tocheck,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('timestamp',$this->timestamp,true);
		$criteria->compare('price',$this->price,true);
		$criteria->compare('deleted',$this->deleted,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Notifications the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
