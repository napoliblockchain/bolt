<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Settings');
Yii::import('libs.NaPacks.SaveModels');
Yii::import('libs.NaPacks.Save');
Yii::import('libs.ethereum.eth');
Yii::import('libs.NaPacks.WebApp');
Yii::import('libs.NaPacks.Logo');

class TokensController extends Controller
{
	public function init()
	{
		Yii::app()->language = ( isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'it' );
		Yii::app()->sourceLanguage = ( isset($_COOKIE['langSource']) ? $_COOKIE['langSource'] : 'it_it' );

		new JsTrans('js',Yii::app()->language); // javascript translation

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
				'actions'=>array('index','view','status'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionStatus()
	{
		$result['status'] = WebApp::walletStatus($_POST['status']);
		echo CJSON::encode($result);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		//carico il wallet selezionato nei settings
		$settings=Settings::loadUser(Yii::app()->user->objUser['id_user']);
		if (!(isset($settings->id_wallet))){
			//$wallet = new Wallets;
			$from_address = '0x0000000000000000000000000000000000000000';
			//$criteria->compare('from_address',0,false);
		}else{
			$wallet = Wallets::model()->findByPk($settings->id_wallet);
			$from_address = $wallet->wallet_address;
			//$criteria->compare('from_address',$wallet->from_address,false);
		}

		$this->render('view',array(
		'model'=>$this->loadModel(crypt::Decrypt($id)),
			'actualBlockNumberDec' => eth::latestBlockNumberDec(), // numero attuale del blocco sulla blockchain
				'from_address'=>$from_address, // indirizzo del wallet dell'utente
		));
	}


	/**
	 * Lists all models.
	 */
	// public function actionIndex()
	// {
	// 	$modelc=new Transactions('search');
	// 	$modelc->unsetAttributes();
	//
	// 	if(isset($_GET['Transactions']))
	// 		$modelc->attributes=$_GET['Transactions'];
	//
	// 	$this->render('index',array(
	// 		'modelc'=>$modelc,
	// 	));
	// }
	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$modelc=new Tokens('search');
		$modelc->unsetAttributes();

		if(isset($_GET['Tokens']))
			$modelc->attributes=$_GET['Tokens'];

		//carico il wallet selezionato nei settings
		$settings=Settings::loadUser(Yii::app()->user->objUser['id_user']);
		if (!(isset($settings->id_wallet))){
			//$wallet = new Wallets;
			$from_address = '0x0000000000000000000000000000000000000000';
			//$criteria->compare('from_address',0,false);
		}else{
			$wallet = Wallets::model()->findByPk($settings->id_wallet);
			$from_address = $wallet->wallet_address;
			//$criteria->compare('from_address',$wallet->from_address,false);
		}
		$modelc->from_address = $from_address;

		$this->render('index',array(
			'modelc'=>$modelc,
			//'wallet'=>$wallet, //il wallet selezionato
			'from_address'=>$from_address, // indirizzo del wallet dell'utente
			'actualBlockNumberDec' => eth::latestBlockNumberDec(), // numero attuale del blocco sulla blockchain
		));
	}




	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Tokens the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Tokens::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}




}
