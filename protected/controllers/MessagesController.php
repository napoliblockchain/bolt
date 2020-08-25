<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Settings');
Yii::import('libs.NaPacks.WebApp');
Yii::import('libs.NaPacks.Notifi');
Yii::import('libs.NaPacks.Logo');


class MessagesController extends Controller
{
	public function init()
	{
		Yii::app()->language = ( isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'it' );
		Yii::app()->sourceLanguage = ( isset($_COOKIE['langSource']) ? $_COOKIE['langSource'] : 'it_it' );

		new JsTrans('js',Yii::app()->language); // javascript translation

		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['facade'] <> 'dashboard'){
			Yii::app()->user->logout();
			$this->redirect(Yii::app()->homeUrl);
		}
	}
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column1';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(

			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

		/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		#echo "<pre>".print_r($_POST,true)."</pre>";
		#exit;
		if(isset($_POST['selectedNotifications'])){
			foreach ($_POST['selectedNotifications'] as $x => $id_notification){
				#echo "<br>".$id_notification;
				$criteriaReaders=new CDbCriteria();
				$criteriaReaders->compare('id_notification',$id_notification,false);

				$allReaders=new CActiveDataProvider('Notifications_readers', array(
				    'criteria'=>$criteriaReaders,
				));

				if ($allReaders){
					$iterator = new CDataProviderIterator($allReaders);
					foreach($iterator as $item) {
						$singleReader=Notifications_readers::model()->findByPk($item->id_notifications_reader);
						if($singleReader!==null){
							$singleReader->delete();
						}
					}
				}
			}
		}

		$criteria=new CDbCriteria();
	 	$criteria->compare('id_user',Yii::app()->user->objUser['id_user'],false);


		//Preparo i criteri di visualizzazione
		$rawData=Yii::app()->db->createCommand('SELECT * FROM np_notifications_readers WHERE id_user = "'.Yii::app()->user->objUser['id_user'].'"')->queryAll();
		 #echo "<pre>".print_r($rawData,true)."</pre>";
		 #exit;
		$dataReader=new CArrayDataProvider($rawData, array(
		    'id'=>'id_notification',
		    'pagination'=>false
		));
		// echo "<pre>".print_r($dataReader,true)."</pre>";
		// exit;
		//$criteria=new CDbCriteria;
		$arrayCondition = array();
		foreach ($dataReader->getData() as $value)
			$arrayCondition[] = $value['id_notification'];

		#echo "<pre>".print_r($arrayCondition,true)."</pre>";
		#exit;

		$criteria->addInCondition('id_notification', $arrayCondition);
		#echo "<pre>".print_r($criteria,true)."</pre>";
		#exit;


		$dataProvider=new CActiveDataProvider('Notifications', array(
			'sort'=>array(
	    		'defaultOrder'=>array(
	      			'timestamp'=>true // viene prima la più recente
	    		)
	  		),
		    'criteria'=>$criteria,
		));

		//carico il wallet selezionato nei settings
		$settings=Settings::loadUser(Yii::app()->user->objUser['id_user']);
		if (!(isset($settings->id_wallet))){
			$from_address = '0x0000000000000000000000000000000000000000';
		}else{
			$wallet = Wallets::model()->findByPk($settings->id_wallet);
			$from_address = $wallet->wallet_address;
		}
		#echo "<pre>".print_r($dataProvider,true)."</pre>";
		#exit;
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
				'from_address'=>$from_address, // indirizzo del wallet dell'utente
		));
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Transactions the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Transactions::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Transactions $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='transactions-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}


}
