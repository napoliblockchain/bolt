<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Settings');
Yii::import('libs.NaPacks.WebApp');
Yii::import('libs.NaPacks.SaveModels');
Yii::import('libs.NaPacks.Save');
Yii::import('libs.NaPacks.Push');
Yii::import('libs.ethereum.eth');
Yii::import('libs.Utils.Utils');
Yii::import('libs.webRequest.webRequest');

require_once Yii::app()->params['libsPath'] . '/ethereum/web3/vendor/autoload.php';

use Web3\Web3;
use Web3\Contract;

class wtCommand extends CConsoleCommand
{
	public $transactions = null;
	public $logfilehandle = null;
	public $transactionsFound = [];
	public $decimals = 0;

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
				'actions'=>array('index'),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}



	public function actionIndex(){
		set_time_limit(0); //imposto il time limit unlimited

		// $seconds deve essere inferiore ai secondi di emissione dei blocchi
		// che in questo momento è 15
		$seconds = 8; // 8 secondi mi permette di cercare almeno 1 volta all'interno di 1 blocco
		$events = false;
		$replayMax = 450; // 1 ora diviso 8 secondi
		$replay = 0;

		// preparo il log file
		$nomeLogFile = Yii::app()->basePath."/log/watchtower.log";
		// if (!is_writable($nomeLogFile)){
		// 	chmod($nomeLogFile, 0755);  // octal; correct value of mode
		// }

		//non c'è output, pertanto salvo gli errori nel log file
		$myfile = fopen($nomeLogFile, "a");
		$this->setLogFile($myfile);

		// Carico la lista dei wallet da controllare
		$listaWallets = CHtml::listData( Wallets::model()->findAll(), 'id_wallet' , 'wallet_address');
		if ($listaWallets === null )
			$listaWallets = [0=>'0x0000000000000000000000000000000000000000'];
		#echo '<pre>'.print_r($listaWallets,true).'</pre>';
		#exit;

		//Carico i parametri della webapp
		$settings=Settings::load();
		if ($settings === null){
			$this->log('The requested settings page does not exist.');
			exit;
		}
		$poaNode = WebApp::getPoaNode();
		if (!$poaNode){
			$this->log("All Nodes are down.");
			exit;
		}
		$web3 = new Web3($poaNode);
		$this->log('Start watchtower. Connecting to '.$poaNode);
		// mi connetto al nodo poa
		// $web3 = new Web3($settings->poa_url);
		$eth = $web3->eth;
		$utils = $web3->utils;
		$contract = new Contract($web3->provider, $settings->poa_abi);

		self::setDecimals($settings->poa_decimals);

		// il trigger serve a non ripetere le medesime operazioni sulla
		// stessa transazione
		$trigger = [];
		$blocknumber = null;

		// START LOOP
		while (true)
		{
			// cerco le transazioni in pending
			$response = null;
			$eth->getBlockByNumber('latest',true, function ($err, $block) use (&$response){

				if ($err !== null) {
					$this->log("Error reading block: ".$err->getMessage());
				}
				$response = $block;
				if (isset($block->transactions))
					self::setTransactions($block->transactions);
			});
			$transactions = self::getTransactions();
			$blocknumber = hexdec($response !== null ? $response->number : '0');
			$blockTime = date('Y-m-d H:i:s A',$response !== null ? hexdec($response->timestamp) : time());

			$replay ++;
			if ($replay >$replayMax)
				$replay = 0;

			if ($events){
				$events = !$events;
				$this->log("Reloading after new events from block ".$blocknumber);
			}
			if ($replay==1){
				$this->log("Waiting for new events from block ".$blocknumber." created at: ".$blockTime);
			}
			// echo '<pre>'.print_r($response,true).'</pre>';
			// exit;
			if (!empty($transactions))
			{
				foreach ($transactions as $transaction)
				{
					// se il destinatario non è lo smart contract, è una transazione ethereum
					// altrimenti è una transazione avvenuta tramite smart contract
					if (strtoupper($transaction->to) == strtoupper($settings->poa_contractAddress) ){

						$ReceivingType = 'token';
						$transactionId = $transaction->hash;
						$transactionContract = '';
						$contract->eth->getTransactionReceipt($transactionId, function ($err, $receipt) use (&$transactionContract)
						{
							if ($err !== null) {
								$this->log("Error reading transaction receipt: ".$err->getMessage());
							}
							if ($receipt)
								$transactionContract = $receipt;
						});
						// se la ricevuta non è vuota...
						if ($transactionContract <> '' && !(empty($transactionContract->logs))){
							$receivingAccount = $transactionContract->logs[0]->topics[2];
							$receivingAccount = str_replace('000000000000000000000000','',$receivingAccount);

							// controllo tutti i wallet in possesso nel db
							foreach ($listaWallets as $id_wallet => $wallet_address) {
								// se l'indirizzo è in logs[0]->topics[2] || in from
								if (strtoupper($receivingAccount) == strtoupper($wallet_address) || strtoupper($transaction->from) == strtoupper($wallet_address)){
									$this->log("Account $wallet_address found.");

									$tokens = $this->loadTokensByHash($transactionContract->transactionHash); // carico l'eventuale transazione nel db
									if (null===$tokens){ // se la transazione non è presente nel db
										$this->log("Invoice not found, then trying to create it...");
										//carico info del wallet
										$wallets = $this->loadWallet($wallet_address);

										// $this->log("Token transaction not found.");
										$this->log("Token transaction found: <pre>".print_r($transactionContract,true)."</pre>");
										//salva la transazione
										$timestamp = 0;
										$transactionValue = self::wei2eth($transactionContract->logs[0]->data, pow(10, self::getDecimals())); //decimali del token
										$rate = eth::getFiatRate('token'); // rate in real time del token (al momento peggato 1:1)

										$eth->getBlockByHash($transaction->blockHash,true, function ($err, $block) use (&$timestamp){
											if ($err !== null) {
												$this->log("Error reading receipt timestamp: ".$err->getMessage());
											}
											$timestamp = hexdec($block->timestamp);
										});
										$attributes = array(
											'id_user' => $wallets->id_user,
											'status'	=> 'complete',
											'type'	=> 'token',
											'token_price'	=> $transactionValue,
											'token_ricevuti'	=> 0,
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

										// $this->log("Token transaction save: <pre>".print_r($attributes,true)."</pre>");
										//salvo la transazione in db. Restituisce object
										$save = new Save;
								 		$tokens = $save->Token($attributes);
										$this->log("Invoice transaction save: ".$tokens->id_token);

										$this->setTransactionFound(array(
											'id_token' => crypt::Encrypt($tokens->id_token),
											'id_user' => $wallets->id_user,
											//'data'	=> date('d/m/Y H:i:s', $tokens->invoice_timestamp),
											'data' => WebApp::dateLN($tokens->invoice_timestamp,$tokens->id_token),
											'status' => $tokens->status,
											'token_price' => $tokens->token_price,
											'token_type' => $tokens->type,
											'from_address' => $tokens->from_address,
											'to_address' => $tokens->to_address,
											'url' => Yii::app()->createUrl("tokens/views",['id'=>crypt::Encrypt($tokens->id_token)]),
										));

										// notifica per chi ha inviato
										// visto che mi trovo in un ciclo foreach devo distinguere il SENDER O RECEIVER
										// tramite ricerca su from o to

										$notification = array(
											'type_notification' => 'token',
											'id_user' => $wallets->id_user,
											'id_tocheck' => $tokens->id_token,
											'status' => 'complete',
											'description' => 'A transaction you '.($wallet_address == $tokens->from_address ? 'sent' : 'received').' has been completed.',
											'url' => Yii::app()->createUrl("tokens/view",['id'=>crypt::Encrypt($tokens->id_token)]),
											'timestamp' => time(),
											'price' => $tokens->token_price,
											'deleted' => 0,
										);
										$save = new Save;
										Push::Send($save->Notification($notification),'bolt');
									}else{
										$this->log("Txpool transaction found: <pre>".print_r($transactionContract,true)."</pre>");
										// $this->log("trigger: <pre>".print_r($trigger,true)."</pre>");
										if (!(in_array($tokens->id_token,$trigger))){
											$tokens->status = 'complete';
											$tokens->update();

											$this->log("Invoice transaction updated: ".$tokens->id_token);
										}else{
											$this->log("Transaction already updated: ".$tokens->id_token);
										}
										$events = true;
 									}// end null in db token
									$trigger = [$tokens->id_token];
									break; // esce dal loop
								} // end appartenenza wallet a logs[0]->topics[2] || from
							} // end foreach wallet address
						} // end !empty transaction contract
					} // end smart contract || ethereum check
				} // end foreach transaction
			} // end !empty transactions

			$transactionsList = $this->gettransactionsFound();

			if (!(empty($transactionsList))){
				// $this->log('<pre>Transactions Found'.print_r($this->gettransactionsFound(),true).'</pre>');
				$this->log('New transactions Found');

				$transactionsList = [];
				//$attributes = [];
				$save = null;
				$this->emptyTransactionsFound();
				$events = true;
			}

			sleep($seconds);
		}
	}
	/* ****************************** fine ************************ */


	/**
	 * Returns the data model based on the attribute 'wallet_address' given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Wallets the loaded model
	 */
	public function loadWallet($wallet_address)
	{
		$model=Wallets::model()->findByAttributes(array('wallet_address'=>$wallet_address));
		if($model===null){
			$this->log('The requested wallet does not exist: '.$wallet_address);
			exit;
		}

		return $model;
	}
	/**
	 * Returns the data model based on the attribute 'txhash' given in the GET variable.
	 * If the data model is not found, it return a null object.
	 * @param integer $id the ID of the model to be loaded
	 * @return Tokens the loaded model
	 * @throws null
	 */
	public function loadTokensByHash($txhash)
	{
		$model=Tokens::model()->findByAttributes(array('txhash'=>$txhash));
		return $model;
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
		return hexdec($number)/ pow(10, $decimals);
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
	// Imposta il LOG file
	private function setLogFile($file){
		$this->logfilehandle = $file;
	}
	// Legge il log file
	private function getLogFile(){
		return $this->logfilehandle;
	}
	// Salva le transazioni del blocco
	private function setTransactions($transactions){
		$this->transactions = $transactions;
	}
	// Legge le transazioni del blocco
	private function getTransactions(){
		return $this->transactions;
	}

	//scrive a video e nel file log le informazioni richieste
	private function log($text){
		$save = new Save;
		$save->WriteLog('napay','commands','wt', $text);
		echo "\r\n" .date('Y/m/d h:i:s a - ', time()) .$text;
	}

	// Salvo in un array la transazione che corrisponde ai criteri di salvataggio in db
	private function setTransactionFound($transaction){
		#echo '<pre>Transazione prima '.print_r($transaction,true).'</pre>';

		$this->transactionsFound["id"] = time();
		$this->transactionsFound["success"] = true;
		$this->transactionsFound["openUrl"] = $transaction['url']; //"index.php?r=tokens/index";
		$this->transactionsFound["transactions"][] = $transaction;
		//array_push($this->transactionsFound,$transaction);
		#echo '<pre>Transazione dopo '.print_r($this->gettransactionsFound(),true).'</pre>';

	}
	//recupero la lista delle transazioni salvate
	private function gettransactionsFound(){
		return $this->transactionsFound;
	}

	//recupero la lista delle transazioni salvate
	private function emptyTransactionsFound(){
		$this->transactionsFound = [];
	}
	private function setDecimals($decimals){
		$this->decimals = $decimals;
	}
	private function getDecimals(){
		return $this->decimals;
	}


}
?>
