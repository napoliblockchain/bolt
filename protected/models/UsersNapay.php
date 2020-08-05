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
class UsersNapay extends CActiveRecord
{
	public $send_mail;
	public $new_password;
	public $new_password_confirm;
	public $ga_cod;

	public $telefono;


	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'np_users';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id_users_type, id_carica, email, password, name, surname, vat, address, cap, city, country, activation_code, status_activation_code', 'required'),
			array('email', 'unique',  'message'=>'La mail inserita è già presente in archivio.'),
			array('id_users_type, status_activation_code, id_carica, corporate', 'numerical', 'integerOnly'=>true),
			array('email, password, name, surname', 'length', 'max'=>255),
			array('denomination, vat, address, cap, city, country', 'length', 'max'=>250),
			array('activation_code', 'length', 'max'=>50),
			array('ga_secret_key', 'length', 'max'=>16),
			array('telefono', 'length', 'max'=>100),
			//array('password', 'allowEmpty'=>false, 'on' => 'update'),

			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_user, id_users_type, id_carica, email, name, surname, corporate, denomination, city, status_activation_code', 'safe', 'on'=>'search'),
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
			'id_user' => 'Id User',
			'id_users_type' => 'Tipo utenza',
			'id_carica' => 'Carica',
			'email' => 'Email',
			'password' => 'Password',
			'ga_secret_key' => 'Google Authentication Code',
			'name' => 'Nome',
			'surname' => 'Cognome',
			'corporate' => 'Persona Giuridica',
			'denomination' => 'Denominazione',
			'vat' => 'Codice Fiscale/P.Iva',
			'address' => 'Indirizzo',
			'city' => 'Città',
			'country' => 'Stato',
			'cap' => 'Cap',
			'activation_code' => 'Activation Code',
			'status_activation_code' => 'Stato Attivazione',
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

		$criteria->compare('status_activation_code',1,false);

		$criteria->compare('id_user',$this->id_user);
		$criteria->compare('id_users_type',$this->id_users_type);
		$criteria->compare('id_carica',$this->id_carica,true);
		$criteria->compare('email',$this->email,true);
		//$criteria->compare('password',$this->password,true);
		//$criteria->compare('ga_secret_key',$this->ga_secret_key,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('surname',$this->surname,true);
		$criteria->compare('corporate',$this->corporate,true);
		$criteria->compare('denomination',$this->denomination,true);
		//$criteria->compare('vat',$this->vat,true);
		$criteria->compare('city',$this->city,true);
		//$criteria->compare('country',$this->country,true);
		//$criteria->compare('cap',$this->cap,true);
		//$criteria->compare('activation_code',$this->activation_code,true);

		//$criteria->compare('status_activation_code',$this->status_activation_code);

		//creo la lista degli utenti abilitati
		//$users = Users::model()->findAll($criteria);
		$reminders = array();

		#echo "<pre>".print_r($_GET,true)."</pre>";
		#exit;

		//valore di default: ATTIVI
		$limit_up = 0; // 0 perche gli attivi sono tutti, anche quelli in scadenza...
		$limit_down = -366; //366 perchè è l'anno di validità dell'iscrizione

		if (isset($_GET['typelist'])){
			switch ($_GET['typelist']){
				case 0:
				default:
					$limit_up 	=  10000000; //
					$limit_down = -10000000; //negativo
					break;
				case 1:
					$limit_up = 0; // 0 perche gli attivi sono tutti, anche quelli in scadenza...
					$limit_down = -366; //366 perchè è l'anno di validità dell'iscrizione
					break;
				case 2:
					$limit_up = 0; //1 mese e mezzo prima ti avviso che stai per scadere !!!
					$limit_down = -46;
					break;
				case 3:
					$limit_up = 366; //1 mese e mezzo prima ti avviso che stai per scadere !!!
					$limit_down = 0;
					break;
			}
		}




		// return new CActiveDataProvider($this, array(
		// 	'data' => $reminders,
		// 	'sort'=>array(
	    // 		'defaultOrder'=>array(
	    //   			'surname'=>true
	    // 		)
	  	// 	),
		// 	'pagination' => [
        // 		'pageSize' => 5,
    	// 	],
		// ));

		$dataProvider = new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));

		$iterator = new CDataProviderIterator($dataProvider);
		foreach($iterator as $item) {
		   // per ciascun utente verifico i pagamenti
			$timelapse = Yii::app()->controller->StatoPagamenti($item->id_user,true);
			if ($timelapse > $limit_down && $timelapse < $limit_up){
				$reminders[] = $item;
			}
		}

		$newDataProvider = new CArrayDataProvider($reminders, array(
		    'keyField' => 'id_user',
		    'keys'=>array('id_user','surname', 'name'),
		    // 'sort'=>array(
		    //     'attributes'=>array(
		    //         'surname', 'name',
		    //     ),
		    // ),
				'sort'=>array(
		    		'defaultOrder'=>array(
		      			'surname'=>true
		    		)
		  		),
		    'pagination'=>array(
		        'pageSize'=>10,
		    ),
		));

		#echo "<pre>".print_r($newDataProvider,true)."</pre>";
		#exit;

		return $newDataProvider;

		// $newDataProvider = new CActiveDataProvider($this, array(
		// 	'data' => $reminders,
		// 	// 'sort'=>array(
	    // 	// 	'defaultOrder'=>array(
	    //   	// 		'surname'=>true
	    // 	// 	)
	  	// 	// ),
		// 	'pagination' => [
        // 		'pageSize' => 5,
    	// 	],
		// ));

		//return $newDataProvider;

		#echo "<pre>".print_r($newDataProvider,true)."</pre>";
		//echo "<pre>".print_r($reminders,true)."</pre>";
		#exit;




		//
		// return new CActiveDataProvider($this, array(
		// 	'criteria'=>$criteria,
		// 	'pagination' => [
		// 		'pageSize' => 5,
		// 	],
		// ));
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
