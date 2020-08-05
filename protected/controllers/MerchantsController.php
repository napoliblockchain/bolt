<?php
class MerchantsController extends Controller
{
	public function init()
	{
		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['facade'] != 'dashboard'){
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
			//'postOnly + delete', // we only allow deletion via POST request
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
				'actions'=>array('index','view',
				// 'config' // configurazione del wallet bitcoin
			),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}



	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$model = $this->loadModel(crypt::Decrypt($id));

		//carico il wallet selezionato nei settings
		$settings=Settings::loadUser(Yii::app()->user->objUser['id_user']);
		if (!(isset($settings->id_wallet))){
			$from_address = '0x0000000000000000000000000000000000000000';
		}else{
			$wallet = Wallets::model()->findByPk($settings->id_wallet);
			$from_address = $wallet->wallet_address;
		}

		$this->render('view',array(
			'model'=>$model,
			'from_address'=>$from_address, // indirizzo del wallet dell'utente
			'merchantAddress'=>$this->getMerchantAddress($model->id_user),

		));
	}

	private function getMerchantAddress($id)
	{
		$wallets = WalletsNapay::model()->findByAttributes(['id_user'=>$id]);
		if (null === $wallets)
			$address = '0x0';
		else
			$address = $wallets->wallet_address;

		return $address;
	}



	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		// $criteria=new CDbCriteria();
		// $criteria->compare('deleted',0,false);
		//
		// $dataProvider=new CActiveDataProvider('Merchants',array(
		// 	'criteria'=>$criteria,
		// 	'sort'=>array(
	  //   		'defaultOrder'=>array(
	  //     			'denomination'=>false
	  //   		)
	  // 		),
		// 	'pagination'=>array('pageSize'=>20)
		// ));
		// $this->render('index',array(
		// 	'dataProvider'=>$dataProvider,
		// ));
		$modelc=new Merchants('search');
		$modelc->unsetAttributes();

		if(isset($_GET['Merchants']))
			$modelc->attributes=$_GET['Merchants'];

		//carico il wallet selezionato nei settings
		$settings=Settings::loadUser(Yii::app()->user->objUser['id_user']);
		if (!(isset($settings->id_wallet))){
			$from_address = '0x0000000000000000000000000000000000000000';
		}else{
			$wallet = Wallets::model()->findByPk($settings->id_wallet);
			$from_address = $wallet->wallet_address;
		}

		$this->render('index',array(
			'modelc'=>$modelc,
			'from_address'=>$from_address, // indirizzo del wallet dell'utente
			'actualBlockNumberDec' => eth::latestBlockNumberDec(), // numero attuale del blocco sulla blockchain
		));
	}

		/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Merchants the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Merchants::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Merchants $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='merchants-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

}
