<?php

/**
 * This is the model class for table "np_settings_webapp".
 *
 * The followings are the available columns in table 'np_settings_webapp':
 * @property integer $id_setting
 * @property string $setting_name
 * @property string $setting_value
 */
class SettingsWebapp extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'np_settings_webapp';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('setting_name, setting_value', 'required'),
			array('setting_name', 'length', 'max'=>50),
			array('setting_value', 'length', 'max'=>1000),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_setting, setting_name, setting_value', 'safe', 'on'=>'search'),
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
			'id_setting' => Yii::t('model','Id Setting'),
			'setting_name' => Yii::t('model','Setting Name'),
			'setting_value' => Yii::t('model','Setting Value'),
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

		$criteria->compare('id_setting',$this->id_setting);
		$criteria->compare('setting_name',$this->setting_name,true);
		$criteria->compare('setting_value',$this->setting_value,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SettingsWebapp the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
