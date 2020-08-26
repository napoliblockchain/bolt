<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Settings');
Yii::import('libs.NaPacks.WebApp');
Yii::import('libs.NaPacks.Logo');
Yii::import('libs.NaPacks.SaveModels');
Yii::import('libs.NaPacks.Save');
Yii::import('libs.ethereum.eth');

require_once Yii::app()->params['libsPath'] . '/ethereum/web3/vendor/autoload.php';
use Web3\Web3;

class WalletController extends Controller
{
	public function init()
	{
		Yii::app()->language = ( isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'it' );
		Yii::app()->sourceLanguage = ( isset($_COOKIE['langSource']) ? $_COOKIE['langSource'] : 'it_it' );

		new JsTrans('js',Yii::app()->language); // javascript translation

		if (session_id() && isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['facade'] <> 'dashboard'){
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
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('saveSubscription'), //salva lo sottoscrizinoe dell'user per le notifiche push
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array(
					'index',
					'loadUserContacts', //carica i contatti (ajax)
					'error', //pagina di errore
					'saveAddress', // salva l'indirizzo creato da eth-lightwallet
					'estimateGas',
					'checkAddress',
					'changeQrCode', //cambia il qrcode RICEVI quando si cambia indirizzo
					'crypt', //cripta codice da js
					'decrypt', //decripta codice da js
					'rescan', // azzera il blocknumber del wallet, causando una rescansione della blockchain
					),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionCrypt()
	{
		echo CJSON::encode(array(
			'cryptedpass'=>isset($_POST['pass']) ? crypt::Encrypt($_POST['pass']) : '',
			'cryptedseed'=>isset($_POST['seed']) ? crypt::Encrypt($_POST['seed']) : '',
			'cryptediduser'=>crypt::Encrypt(Yii::app()->user->objUser['id_user']),
		));
	}

	public function actionDecrypt()
	{
		echo CJSON::encode(array(
			'decrypted'=>isset($_POST['pass']) ? crypt::Decrypt($_POST['pass']) : '',
			'decryptedseed'=>isset($_POST['cryptedseed']) ? crypt::Decrypt($_POST['cryptedseed']) : '',
			'decryptediduser'=>isset($_POST['cryptediduser']) ? crypt::Decrypt($_POST['cryptediduser']) : '',
		));
	}


	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$modelc=new Tokens('search');
		$modelc->unsetAttributes();

		$walletForm = new WalletTokenForm; //form di input dei dati

		// carico il wallet selezionato nei settings
		$settings=Settings::loadUser(Yii::app()->user->objUser['id_user']);
		if (empty($settings->id_wallet)){
			$from_address = '0x0000000000000000000000000000000000000000';
		}else{
			$wallet = Wallets::model()->findByPk($settings->id_wallet);
			$from_address = $wallet->wallet_address;
		}

		$modelc->from_address = $from_address;

		// carico i contatti dell'utente
		$criteria = new CDbCriteria();
		$criteria->compare('id_user',Yii::app()->user->objUser['id_user'],false);
		$dataProvider=new CActiveDataProvider('Contacts',array(
			'criteria' => $criteria,
			'pagination' => array(
				'pageSize' => 10,
			),
		));

		// visualizzo la schermata
		$this->render('index',array(
			'modelc'=>$modelc, //lista transazioni tokens
			'walletForm'=>$walletForm, //form per invio dati
			'from_address'=>$from_address, // indirizzo del wallet dell'utente
			'actualBlockNumberDec' => eth::latestBlockNumberDec(), // numero attuale del blocco sulla blockchain
			'dataProvider' => $dataProvider, // lista contatti
		));
	}

	public function actionLoadUserContacts()
	{
		//carico i contatti dell'utente
		$criteria = new CDbCriteria();
		$criteria->compare('id_user',Yii::app()->user->objUser['id_user'],false);
		$dataProvider=new CActiveDataProvider('Contacts',array(
			'criteria' => $criteria,
			'pagination' => array(
				'pageSize' => 10,
			),
		));

		$this->renderPartial('contacts', array('dataProvider' => $dataProvider));
	}


	/**
	 * Funziona che salva esclusivamente l'indirizzo generato da eth-lightwallet
	 */
	public function actionSaveAddress()
	{
		$settings=Settings::load();

		if ($settings === null){
			echo CJSON::encode(array("error"=>'Errore: I parametri di configurazione per la connessione al nodo POA non sono stati trovati'));
			exit;
		}
		$block = 0;
		//if( webRequest::checkUrl( $settings->poa_url ) ) {
			// mi connetto al nodo poa
			// $web3 = new Web3($settings->poa_url);
			// $web3 = new Web3(WebApp::getPoaNode());
		$poaNode = WebApp::getPoaNode();
		if (!$poaNode){
			$save = new Save;
			$save->WriteLog('bolt','wallet','saveAddress',"All Nodes are down.");
		}else{
			$web3 = new Web3($poaNode);
			$eth = $web3->eth;
			//recupero l'ultimo block number

			$eth->getBlockByNumber('latest',false, function ($err, $response) use (&$block){
				if ($err !== null) {
					echo CJSON::encode(array("error"=>'Error: ' . $err->getMessage()));
					exit;
				}
				$block = $response->number;
			});
		}
		//}

		// se esiste aggiorno, altrimneti aggiungo
		// in questa ricerca devo trovare l'id_user e non il wallet address
		// se un user è già inserito aggiorno l0indirizzo e non il contrario
		$wallets=Wallets::model()->findByAttributes(array(
			//'wallet_address'=>$_POST['address'],
			'id_user' => Yii::app()->user->objUser['id_user']
		));
		if ($wallets === null){
			$wallets = new Wallets;
		}
		// salvo il nuovo indirizzo
		$wallets->id_user = Yii::app()->user->objUser['id_user'];
		$wallets->wallet_address = $_POST['address'];

		// metto il blocco a 0, in modo che se è un ripristino wallet, carica di nuovo tutte le transazioni
		// si potrebbe migliorare
		// TODO!!
		// cercare l'ultima transazione token in db con quel wallet e recuperare il numero blocco
		// in modo da non dover cercare nella blockchain dall'inizio
		$wallets->blocknumber = $block;

		if ($wallets->save()){
			//assegno il nuovo indirizzo all'utente
			Settings::saveUser($wallets->id_user,$wallets->attributes,array('id_wallet'));
			$result = array(
				'success'=>true,
				'wallet'=>$wallets->wallet_address
			);
		}else{
			$result = array(
				'success'=>false,
				'wallet'=>$_POST['address']
			);
		}

		echo CJSON::encode($result);
	}





	/**
	 * @param POST string address the Ethereum Address to be rescanned
	 */
	public function actionRescan(){
        //azzero il nuomero dei blocchi dell'indirizzo
		$model = Wallets::model()->findByAttributes(array('wallet_address'=>$_POST['wallet']));
		$model->blocknumber = '0x0';
		$model->update();

		echo CJSON::encode(array(
			'wallet' => $_POST['wallet'],
			"blocknumber"=>'0x0',
		));
	}

	/**
	 * @param POST string address the Ethereum Address to be paid
	 */
	public function actionCheckAddress(){
        // $settings=Settings::load();
		//
		// if( !webRequest::checkUrl( $settings->poa_url ) ) {
		// 	echo CJSON::encode(array(
		// 		'id'=>time(),
		// 		'response'=>false,
		// 	));
		// 	return;
		// }
        // mi connetto al nodo poa
		// $web3 = new Web3($settings->poa_url);
		// $web3 = new Web3(WebApp::getPoaNode());
		$poaNode = WebApp::getPoaNode();
		if (!$poaNode){
			$save = new Save;
			$save->WriteLog('bolt','wallet','checkAddress',"All Nodes are down.");
				echo CJSON::encode(array(
					'id'=>time(),
					'response'=>false,
				));
				return;
		}
		$web3 = new Web3($poaNode);
		$utils = $web3->utils;
		$response = $utils->isAddress($_POST['to']);


		echo CJSON::encode(array(
			'id' => $_POST['to'],
			"response"=>$response,
		));
	}



	public function actionEstimateGas(){
		$fromAccount = $_POST['from'];
		$toAccount = $_POST['to'];
		$amount = $_POST['amount'];

		// $settings=Settings::load();

		// if( !webRequest::checkUrl( $settings->poa_url ) ) {
		// 	echo CJSON::encode(array(
		// 		'id'=>time(),
		// 		'success'=>false,
		// 	));
		// 	return;
		// }
        // mi connetto al nodo poa
		// $web3 = new Web3($settings->poa_url);
		// $web3 = new Web3(WebApp::getPoaNode());
		$poaNode = WebApp::getPoaNode();
		if (!$poaNode){
			$save = new Save;
			$save->WriteLog('bolt','wallet','estimateGas',"All Nodes are down.");
				echo CJSON::encode(array(
			 		'id'=>time(),
					'error'=>"All Nodes are down.",
			 		'success'=>false,
			 	));
			 	return;
		}
		$web3 = new Web3($poaNode);

		$eth = $web3->eth;
		$personal = $web3->personal;
		$utils = $web3->utils;
		$hex = $utils->toWei($amount, 'ether')->toHex();

		$gasPrice = null;
		// estimateGas
	    $eth->estimateGas([
	        	'from' => $fromAccount,
	        	'to' => $toAccount,
	        	'value' => '0x'.$hex
	    	], function ($err, $gas) use ($utils, $eth, $fromAccount, $toAccount, &$gasPrice) {
	        	if ($err !== null) {
	            	echo CJSON::encode(array("error"=>$err->getMessage()));
	            	exit;
	        	}
				$value = (string) $gas * 1;
				$gasPrice = $value / pow(10,8);
	    });
		//echo '<pre>'.print_r($gasPrice,true).'</pre>';
		//exit;
		$send_json = array(
			'gasPrice' => $gasPrice,
			'id' => time(), // id ci deve essere per il s.w.
		);
    	echo CJSON::encode($send_json);
	}


	/**
	 * Saves the Subscription for push messages.
	 * @param POST VAPID KEYS
	 * this function NOT REQUIRE user to login
	 */
	public function actionSaveSubscription()
	{
		ini_set("allow_url_fopen", true);
		//
 		$raw_post_data = file_get_contents('php://input');
 		if (false === $raw_post_data) {
 			throw new \Exception('Could not read from the php://input stream or invalid Subscription object received.');
 		}
 		$raw = json_decode($raw_post_data);
		$browser = $_SERVER['HTTP_USER_AGENT'];

		$Criteria=new CDbCriteria();
		$Criteria->compare('id_user',Yii::app()->user->objUser['id_user'], false);
		$Criteria->compare('browser',$browser, false);

		$vapidProvider=new CActiveDataProvider('PushSubscriptions', array(
			'criteria'=>$Criteria,
		));

		if ($vapidProvider->totalItemCount == 0 && $raw != null ){
			//save
			$vapid = new PushSubscriptions;
			$vapid->id_user = Yii::app()->user->objUser['id_user'];
			$vapid->browser = $browser;
			$vapid->endpoint = $raw->endpoint;
			$vapid->auth = $raw->keys->auth;
			$vapid->p256dh = $raw->keys->p256dh;
			$vapid->type = 'wallet';

			if (!$vapid->save()){
				echo '[WalletController] SaveSubscription: Cannot save subscription on server!';
				exit;//
			}
			echo '[WalletController] SaveSubscription: Subscription saved on server!';
		}else{
			//delete
			$iterator = new CDataProviderIterator($vapidProvider);
			foreach($iterator as $data) {
				echo print_r($data->id_subscription,true).',';
				#exit;
				$vapid=PushSubscriptions::model()->findByPk($data->id_subscription)->delete();

				// if($vapid!==null)
				// 	$vapid->delete();
			}
			echo '[WalletController] SaveSubscription: Subscriptions deleted on server!';
		}
	}
}
