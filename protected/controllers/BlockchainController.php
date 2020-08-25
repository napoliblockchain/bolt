<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Settings');
Yii::import('libs.NaPacks.WebApp');
Yii::import('libs.NaPacks.SaveModels');
Yii::import('libs.NaPacks.Save');

require_once Yii::app()->params['libsPath'] . '/ethereum/web3/vendor/autoload.php';

use Web3\Web3;
use Web3\Contract;

class BlockchainController extends Controller
{
	public $transactions = null;
	public $maxBlocksToScan = 500; // 200 IS FOR TESTING AT HOME 1500; //don't touch it (1500)
	public $logfilehandle = null;
	public $transactionFounded = [];
	public $decimals = 0;

	public function init()
	{
		Yii::app()->language = ( isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'it' );
		Yii::app()->sourceLanguage = ( isset($_COOKIE['langSource']) ? $_COOKIE['langSource'] : 'it_it' );

		new JsTrans('js',Yii::app()->language); // javascript translation

		// if (session_id() && isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['facade'] <> 'dashboard'){
		// 	Yii::app()->user->logout();
		// 	$this->redirect(Yii::app()->homeUrl);
		// }
	}



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
				'actions'=>array(
					'syncBlockchain', //scansiona la blockchain per nuove transazioni
					'getBlockNumber', // recupera il blocknumber della blockchain
					'checkTransaction', // fa il check della singola transazione (DA MODIFICARE!!!)
					'scanForNew', // fa il check delle transazioni che si trovano nello stato NEW
				),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Pos the loaded model
	 * @throws CHttpException
	 */
	public function loadWallet($wa)
	{
		$model=Wallets::model()->findByAttributes(array('wallet_address'=>$wa));
		if($model===null)
			throw new CHttpException(404,'The requested wallet ['.$wa.'] does not exist.');
		return $model;
	}
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Pos the loaded model
	 * @throws CHttpException
	 */
	public function loadTokensByHash($txhash)
	{
		$model=Tokens::model()->findByAttributes(array('txhash'=>$txhash));
		//if($model===null)
		//	throw new CHttpException(404,'The requested token transaction does not exist.');
		return $model;
	}
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Transactions the loaded model
	 * @throws CHttpException
	 */
	public function loadTokens($id)
	{
		$model=Tokens::model()->findByPk($id);
		return $model;
	}



	/**
	 * Questa funziona fa un check sulla blockchain per verificare la
	 * presenza della transazione
	 * @param integer $id the ID of the model to be loaded
	 * che ricerca l'hash della transazione
	 */
	 public function actionCheckTransaction($id)
	{
		#echo '<pre>'.print_r($id,true).'</pre>';
		#exit;

		$model=$this->loadTokens(crypt::decrypt($id));
		$wallets = Wallets::model()->findByAttributes(['id_user'=>Yii::app()->user->objUser['id_user']]);

		$command = 'send';
		// if ($wallets->wallet_address <> $model->from_address)
		// 	$command = 'receive';

		//eseguo lo script che si occuperà in background di verificare lo stato dell'invoice appena creata...
		$cmd = Yii::app()->basePath.DIRECTORY_SEPARATOR.'yiic ' .$command. ' --id='.$id;
		Utils::execInBackground($cmd);
		echo 'Reloading page...';
		//sleep(2);
	}

	/**
	*	Questa funzione controlla il db per cercare transazioni che sono nello stato new e le aggiorna
	*	Viene richiamata una volta soltanto al refresh del wallet
	*/
	public function actionScanForNew(){
		$search_address = $_POST['my_address'];
		$SEARCH_ADDRESS = strtoupper($_POST['my_address']);

		//Carico i parametri della webapp
		$settings=Settings::load();
		if ($settings === null){
			throw new CHttpException(404,'The requested settings page does not exist.');
		}
		// if( !webRequest::checkUrl( $settings->poa_url ) ) {
		// 	echo CJSON::encode(array(
		// 		'id'=>time(),
		// 		'error'=>'Url not found.',
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
			$save->WriteLog('bolt','blockchain','scanForNew',"All Nodes are down.");
				echo CJSON::encode(array(
					'id'=>time(),
					'error'=>'All Nodes are down.',
					'success'=>false,
				));
				return;
		}
		$web3 = new Web3($poaNode);

		$eth = $web3->eth;
		$utils = $web3->utils;
		$contract = new Contract($web3->provider, $settings->poa_abi);
		self::setDecimals($settings->poa_decimals);

		//creo iterazione con from_address e to_address
		// FIN QUANDO NON FACCIO IL MERGE DEL $dataProvider

		$array = ['from_address','to_address'];

		// effettuo un ciclo prima con From_address, poi con to_address
		foreach ($array as $array_address){
			// carico la tabella tokens con lo status = 'new'
			$criteria=new CDbCriteria;
			$criteria->compare('status','new',true);
			$criteria->compare('type','token',true);
			$criteria->compare($array_address,$search_address,true);

			$dataProvider =  new CActiveDataProvider('Tokens', array(
				'criteria'=>$criteria,
			));

			$iterator = new CDataProviderIterator($dataProvider);
			foreach($iterator as $transaction) {
				//smart contract
				$ReceivingType = 'token';
				$transactionId = $transaction->txhash;
				$transactionContract = '';

				if (empty($transactionId) || $transactionId == '0x0')
					continue;

				$contract->eth->getTransactionReceipt($transactionId, function ($err, $receipt) use (&$transactionContract)
				{
					if ($err !== null) {
						echo CJSON::encode(array('success'=>false,'error'=>$err->getMessage()));
						exit;
					}
					if ($receipt)
						$transactionContract = $receipt;
				});
				// echo '<pre>tx Contract'.print_r($transactionContract,true).'</pre>';
				// exit;

				if ($transactionContract <> '' && !(empty($transactionContract->logs))){
					$receivingAccount = $transactionContract->logs[0]->topics[2];
					$receivingAccount = str_replace('000000000000000000000000','',$receivingAccount);

					if (strtoupper($receivingAccount) == $SEARCH_ADDRESS || strtoupper($transactionContract->from) == $SEARCH_ADDRESS){
						//ricevi o hai inviato
						$tokens = $transaction; // per compatibiltà
						$tokens->status = 'complete';
						$tokens->update();

						$this->setTransactionFound(array(
							'id_token' => crypt::Encrypt($tokens->id_token),
							'data' => WebApp::dateLN($tokens->invoice_timestamp,$tokens->id_token),
							'status' => WebApp::walletStatus($tokens->status),
							'token_price' => WebApp::typePrice($tokens->token_price,(strtoupper($transactionContract->from) == $SEARCH_ADDRESS ? 'sent' : 'received')),
							'from_address' => $tokens->from_address,
							'to_address' => $tokens->to_address,
							'url' => Yii::app()->createUrl("tokens/view",['id'=>crypt::Encrypt($tokens->id_token)]),
						));

						//salva la notifica
						$notification = array(
							'type_notification' => 'token',
							'id_user' => Wallets::model()->findByAttributes(['wallet_address'=>$search_address])->id_user,
							'id_tocheck' => $tokens->id_token,
							'status' => 'complete',
							// 'description' => Notifi::description($tokens->status, $tokens->type),
							'description' => (strtoupper($receivingAccount) == $SEARCH_ADDRESS ? 'A transaction you received has been completed.' : 'A transaction you sent has been completed.'),
							'url' => Yii::app()->createUrl("tokens/view",['id'=>crypt::Encrypt($tokens->id_token)]),
							'timestamp' => time(),
							'price' => $tokens->token_price,
							'deleted' => 0,
						);
						$save = new Save;
						$save->Notification($notification); // il messaggio push viene inviato tramite sw js

					}
				}
			}
		}


		if (!(empty($this->getTransactionFound()))){
			echo CJSON::encode($this->getTransactionFound());
		}else{
			echo CJSON::encode(array(
				'id'=>time(),
				'success'=>false,
				'error'=>'',
			));
		}
	}

	/**
	* Cerca nella blockchain le transazioni relative al wallet address PRINCIPALE
	* partendo dal block number in cui è stato generato il wallet
	* Valore standard 0x0 = blocco 0
	* Salva in db quelle trovate
	* Aggiorna il blocco sul DB del wallet
	* La ricerca avviene in questo modo:
	*		Carica il blockNumber relativo al wallet dal db.
	*		Cerca nei blocchi successivi fino a xxx (Valore impostato nei settings. Si
	*   aggira dai 500 ai 1500 a seconda della velictà della comunicazione.
	*		Se non trova nulla, aggiorna esclusivamente nel db il blocco sopraggiunto ed esce.
	*		Se trova una o più transazioni:
	*			- carico txhash della transazione
	*			- verifico presenza txhash in db per quel wallet
	*			- se non è presente salvo transazione
	*			- Salvo nel db l'ultimo blocco cercato
	*
	 * @param from: indirizzo che invia
	 * @param to: dovrebbe essere lo smart-contract
	 * @param logs[0]->topics[2]: indirizzo che riceve
	 * @param logs[0]->data: importo
	 * @param logs[0]->blockNumber: numero del blocco
	*
	*/
	public function actionSyncBlockchain(){
		 // echo '<pre>'.print_r($_POST,true).'</pre>';
 		 // exit;
		set_time_limit(0); //imposto il time limit unlimited

		$chainBlock = $_POST['chainBlock'];

		$filename = Yii::app()->basePath."/log/blockchain-search.log";
		$myfile = fopen($filename, "a");

		//carico info del wallet
		$wallets = $this->loadWallet($_POST['search_address']);
		$SEARCH_ADDRESS = strtoupper($_POST['search_address']);

		//Carico i parametri della webapp
		$settings=Settings::load();
		if ($settings === null){
			throw new CHttpException(404,'The requested settings page does not exist.');
		}

		// carico il nodo poa
		$poaNode = WebApp::getPoaNode();
		if (!$poaNode){
			$save = new Save;
			$save->WriteLog('bolt','blockchain','SyncBlockchain',"All Nodes are down.");
				echo CJSON::encode(array(
					'id'=>time(),
					'error'=>'All Nodes are down.',
					'success'=>false,
				));
				exit;
		}
		$web3 = new Web3($poaNode);
		$eth = $web3->eth;
		$utils = $web3->utils;
		$contract = new Contract($web3->provider, $settings->poa_abi);
		$savedBlock = $wallets->blocknumber; //dovrebbe essere già stato salvato in formato hex

		self::setDecimals($settings->poa_decimals);

		// numero massimo di blocchi da scansionare
		if (isset($settings->maxBlocksToScan))
			self::setMaxBlocksToScan($settings->maxBlocksToScan);


		// Inizio il ciclo sui blocchi
		for ($x=0; $x<self::getMaxBlocksToScan();$x++)
		{
			if ((hexdec($savedBlock)+$x) <= hexdec($chainBlock)){
				//somma del valore del blocco in decimali
				$searchBlock = '0x'. dechex (hexdec($savedBlock) + $x );

				$eth->getBlockByNumber($searchBlock,true, function ($err, $block){
					if ($err !== null) {
						$save = new Save;
						$save->WriteLog('bolt','blockchain','SyncBlockchain',"Error while searching blocks.");
						echo CJSON::encode(array(
							'success'=>false,
							'error'=>$err->getMessage()
						));
						exit;
					}
					self::setTransactions($block->transactions);
				});
				$transactions = self::getTransactions();
				if (!empty($transactions))
				{
					foreach ($transactions as $transaction)
					{
						//controlla transazioni ethereum
						if (strtoupper($transaction->to) <> strtoupper($settings->poa_contractAddress) ){
						 	$ReceivingType = 'ether';
						}else{
							//smart contract
							$ReceivingType = 'token';
							$transactionId = $transaction->hash;
							$transactionContract = '';
							$contract->eth->getTransactionReceipt($transactionId, function ($err, $receipt) use (&$transactionContract)
							{
	              if ($err !== null) {
									$save = new Save;
									$save->WriteLog('bolt','blockchain','SyncBlockchain',"Error while getting transaction receipt.");
									echo CJSON::encode(array(
										'success'=>false,
										'error'=>$err->getMessage()
									));
									exit;
								}
	              if ($receipt)
									$transactionContract = $receipt;
							});
							if ($transactionContract <> '' && !(empty($transactionContract->logs)))
							{
								$receivingAccount = $transactionContract->logs[0]->topics[2];
								$receivingAccount = str_replace('000000000000000000000000','',$receivingAccount);

								// verifica se nella transazione ricevi o hai inviato
								if (strtoupper($receivingAccount) == $SEARCH_ADDRESS || strtoupper($transactionContract->from) == $SEARCH_ADDRESS){
									$save = new Save;

									// carica da db tramite hash (che è univoco)
									$tokens = $this->loadTokensByHash($transactionContract->transactionHash);

									// SE da DB è null, è NECESSARIA LA NOTIFICA per chi invia e riceve
									if (null===$tokens){
										$save->WriteLog('bolt','blockchain','SyncBlockchain',"Transaction found but it isn\'t in DB. I\'ll save it in DB.");

										//salva la transazione
										$timestamp = 0;
										$transactionValue = self::wei2eth($transactionContract->logs[0]->data, self::getDecimals()); // decimali del token
										$rate = eth::getFiatRate('token');

										// con questa funzione recupero il timestamp della transazione
										// NB: il timestamp è quello sul server POA.
										$eth->getBlockByHash($transaction->blockHash,true, function ($err, $block) use (&$timestamp){
											if ($err !== null) {
												$save->WriteLog('bolt','blockchain','SyncBlockchain',"Error while getting block by hash.");
												echo CJSON::encode(array(
													'success'=>false,
													'error'=>$err->getMessage()
												));
												exit;
											}
											$timestamp = hexdec($block->timestamp);
										});

										// salvo la transazione NULL in db. Restituisce object
										$attributes = array(
											'id_user' => $wallets->id_user,
											'status'	=> 'complete',
											'type'	=> 'token',
											'token_price'	=> $transactionValue,
											'token_ricevuti'	=> $transactionValue,
											'fiat_price'		=>  abs($rate * $transactionValue),
											'currency'	=> 'EUR',
											'item_desc' => 'wallet',
											'item_code' => '0',
											'invoice_timestamp' => $timestamp,
											'expiration_timestamp' => $timestamp + 60*15, //15 min. standard
											'rate' => $rate,
											'from_address' => $transaction->from,
											'to_address' => $receivingAccount,
											'blocknumber' => hexdec($transactionContract->blockNumber),
											'txhash'	=> $transactionContract->transactionHash,
										);
								 		$tokens = $save->Token($attributes);
										$save->WriteLog('bolt','blockchain','SyncBlockchain',"Saving transaction: <pre>".print_r($attributes,true)."</pre>\n");

										// imposto l'array contenente le transazioni e che sarà restituito alla funzione chiamante
										$this->setTransactionFound(array(
											'id_token' => crypt::Encrypt($tokens->id_token),
											'data' => WebApp::dateLN($tokens->invoice_timestamp,$tokens->id_token),
											'status' => WebApp::walletStatus($tokens->status),
											'token_price' => WebApp::typePrice($transactionValue,(strtoupper($transactionContract->from) == $SEARCH_ADDRESS ? 'sent' : 'received')),
											'from_address' => $tokens->from_address,
											'to_address' => $tokens->to_address,
											'url' => Yii::app()->createUrl("tokens/view",['id'=>crypt::Encrypt($tokens->id_token)]),
											'title' => Yii::t('notify','New transaction'),
											'message' => Yii::t('notify','A transaction you received has been completed.'),
										));

										// notifica per chi ha inviato (from_address)
										$notification = array(
											'type_notification' => 'token',
											'id_user' => Wallets::model()->findByAttributes(['wallet_address'=>$tokens->from_address]) === null ? 1 : Wallets::model()->findByAttributes(['wallet_address'=>$tokens->from_address])->id_user,
											'id_tocheck' => $tokens->id_token,
											'status' => 'complete',
											'description' => 'A transaction has been completed.',
											'url' => Yii::app()->createUrl("tokens/view",['id'=>crypt::Encrypt($tokens->id_token)]),
											'timestamp' => time(),
											'price' => $tokens->token_price,
											'deleted' => 0,
										);
										$save->Notification($notification);

										// notifica per chi riceve (to_address)
										$notification = array(
											'type_notification' => 'token',
											// 'id_user' => Wallets::model()->findByAttributes(['wallet_address'=>$tokens->to_address])->id_user,
											'id_user' => Yii::app()->user->objUser['id_user'],
											'id_tocheck' => $tokens->id_token,
											'status' => 'complete',
											'description' => 'A transaction you received has been completed.',
											'url' => Yii::app()->createUrl("tokens/view",['id'=>crypt::Encrypt($tokens->id_token)]),
											'timestamp' => time(),
											'price' => $tokens->token_price,
											'deleted' => 0,
										);
										$save->Notification($notification);
										$save->WriteLog('bolt','blockchain','SyncBlockchain',"Notification saved.");
									}else{
										// se NON è NULL notifico solo il ricevente
										if (strtoupper($tokens->to_address) == $SEARCH_ADDRESS){
											if ($tokens->token_ricevuti == 0 ){ //&& $tokens->status <> 'complete'){
												$this->setTransactionFound(array(
													'id_token' => crypt::Encrypt($tokens->id_token),
													'data' => WebApp::dateLN($tokens->invoice_timestamp,$tokens->id_token),
													'status' => WebApp::walletStatus($tokens->status),
													'token_price' => WebApp::typePrice($tokens->token_price,(strtoupper($transaction->from) == $SEARCH_ADDRESS ? 'sent' : 'received')),
													'from_address' => $tokens->from_address,
													'to_address' => $tokens->to_address,
													'url' => Yii::app()->createUrl("tokens/view",['id'=>crypt::Encrypt($tokens->id_token)]),
													'title' => Yii::t('notify','New transaction'),
													'message' => Yii::t('notify','A transaction you received has been completed.'),
												));
												$tokens->status = 'complete';
												$tokens->token_ricevuti = $tokens->token_price;
												$tokens->update();
												$save->WriteLog('bolt','blockchain','SyncBlockchain',"Transaction ".crypt::Encrypt($tokens->id_token)." updated.");

												//salva la notifica
										 		$notification = array(
										 			'type_notification' => 'token',
										 			'id_user' => Yii::app()->user->objUser['id_user'],
										 			'id_tocheck' => $tokens->id_token,
										 			'status' => 'complete',
													'description' => 'You received a new transaction.',
													'url' => Yii::app()->createUrl("tokens/view",['id'=>crypt::Encrypt($tokens->id_token)]),
										 			'timestamp' => time(),
										 			'price' => $tokens->token_price,
										 			'deleted' => 0,
										 		);
												$save->Notification($notification);
											}
										}
									}
									// in entrambi i casi controllo che il wallet di chi invia sia
									// di un Istituto, nel qual caso Faccio partire il timer
									// per il messaggio di alert
									$institute = Institutes::model()->findByAttributes(['wallet_address'=>$tokens->from_address]);
									if ($institute !== null){
										// eseguo lo script che si occuperà in background di
										// inviare il messaggio di alert all'utente
										$cmd = Yii::app()->basePath.DIRECTORY_SEPARATOR.'yiic alert --iduser='.crypt::Encrypt(Wallets::model()->findByAttributes(['wallet_address'=>$tokens->to_address])->id_user). ' --idInstitute='.crypt::Encrypt($institute->id_institute);
										Utils::execInBackground($cmd);
									}
								}
							}
						} //endif 'ethereum or token'
	      	}
				}//for loop
				//aggiorno il numero dei blocchi sul wallet
				$wallets->blocknumber = $searchBlock;
				$wallets->update();
			}else{
				break;
			}
		}

		if (!(empty($this->getTransactionFound()))){
			// restituisco le transazioni
			echo CJSON::encode($this->getTransactionFound());
		}else{
			// non ho trovato nulla
			echo CJSON::encode(array(
				'id'=>time(),
				'success'=>false,
				'error'=>'',
			));
		}
	}

	/**
	 * funzione che recupera ilo blocco attuale
	 * @param null
	 */
	public function actionGetBlockNumber()
	{
		$search_address = $_POST['my_address'];
		$return = [
			 'id'=>time(),
			 "walletBlocknumber"=>0,
			 "chainBlocknumber"=>0,
			 "diff"=>0,
			 "my_address"=>$search_address,
			 "success"=>false,
		];
		// echo '<pre>'.print_r($_POST,true).'</pre>';
		// exit;
 		$wallets = $this->loadWallet($search_address);

 		// Carico i parametri della webapp
 		$settings=Settings::load();

		// recupero il nodo della POA
		$poaNode = WebApp::getPoaNode();
		if (!$poaNode){
			$save = new Save;
			$save->WriteLog('bolt','blockchain','getBlockNumber',"All Nodes are down.");
			echo CJSON::encode($return);
			exit;
		}
		$web3 = new Web3($poaNode);
 		$eth = $web3->eth;

		$response = null;
		$eth->getBlockByNumber('latest',false, function ($err, $block) use (&$response,&$return){
			if ($err !== null) {
				echo CJSON::encode($return);
				exit;
			}
			$response = $block;
		});

		//calcolo la differenza tra i blocchi
		$difference = hexdec($response->number) - hexdec($wallets->blocknumber);

		echo CJSON::encode(array(
			'id'=>time(),
			"walletBlocknumber"=>$wallets->blocknumber,
			"chainBlocknumber"=>$response->number,
			"diff"=>$difference,
			"my_address"=>$search_address,
			"success"=>true,
		));
	 }


	/*
	 * funzione che riceve il valore della transazione nello smart contract
	 * in formato: hex (0x000000000000000000000000000000000000000000000000000000000000012c)
	 * e la trasforma in un numero intero, più i decimali del token
	 */
	private function wei2eth($wei,$decimals)
	{
		$value = substr_replace('0x','0',$wei);
		$array = str_split($wei);
		$number = '';
		$flag = false;
		foreach($array as $digit){
			if ($digit != '0' && $flag == false){
				$number .= $digit;
				$flag = true;
			}
			if ($flag == true)
				$number .= $digit;

		}
		#fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : <pre>Digit: ".print_r($number,true)."</pre>\n");
		return hexdec($number) / pow(10, $decimals);
	}
	/*
	 * funzione che riceve il valore della transazione eth
	 * in formato hex (0xde0b6b3a7640000)
	 * e la trasforma in un numero intero (ether)
	 */
	private function hex2eth($wei)
	{
		$big = hexdec($wei) / pow(10, 18);
		#fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : <pre>from big Digit: ".print_r($big,true)."</pre>\n");
		return $big;
	}

	private function setTransactions($transactions){
		$this->transactions = $transactions;
	}
	private function getTransactions(){
		return $this->transactions;
	}
	private function setLogFile($file){
		$this->logfilehandle = $file;
	}
	private function getLogFile(){
		return $this->logfilehandle;
	}

	private function setMaxBlocksToScan($maxBlocksToScan){
		$this->maxBlocksToScan = $maxBlocksToScan;
	}
	private function getMaxBlocksToScan(){
		return $this->maxBlocksToScan;
	}

	private function setTransactionFound($transaction){
		$this->transactionFounded["id"] = time();
		$this->transactionFounded["success"] = true;
		$this->transactionFounded["openUrl"] = "index.php?r=tokens/index";
		$this->transactionFounded["transactions"][] = $transaction;
	}
	private function getTransactionFound(){
		return $this->transactionFounded;
	}
	private function setDecimals($decimals){
		$this->decimals = $decimals;
	}
	private function getDecimals(){
		return $this->decimals;
	}

}
