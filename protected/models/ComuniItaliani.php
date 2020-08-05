<?php

/**
 * This is the model class for table "np_comuni_italiani".
 *
 * The followings are the available columns in table 'np_comuni_italiani':
 * @property integer $id_comune
 * @property string $citta
 * @property string $provincia
 * @property string $sigla
 */
class ComuniItaliani extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'np_comuni_italiani';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('citta, provincia, sigla', 'required'),
			array('citta', 'length', 'max'=>200),
			array('provincia', 'length', 'max'=>50),
			array('sigla', 'length', 'max'=>2),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_comune, citta, provincia, sigla', 'safe', 'on'=>'search'),
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
	        'bycity' => array('order' => 'citta ASC'),
	    );
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id_comune' => 'Id Comune',
			'citta' => 'Citta',
			'provincia' => 'Provincia',
			'sigla' => 'Sigla',
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

		$criteria->compare('id_comune',$this->id_comune);
		$criteria->compare('citta',$this->citta,true);
		$criteria->compare('provincia',$this->provincia,true);
		$criteria->compare('sigla',$this->sigla,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ComuniItaliani the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
