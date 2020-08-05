<?php
require_once Yii::app()->params['libsPath'] . '/ethereum/web3/vendor/autoload.php';
require_once Yii::app()->params['libsPath'] . '/ethereum/ethereum-tx/vendor/autoload.php';
require_once Yii::app()->params['libsPath'] . '/ethereum/criptojs-aes.php';

use Web3\Web3;
use Web3\Contract;
use Web3p\EthereumTx\Transaction;

class WalletERC20Controller extends Controller
{
	public $balance = 0;
	public $ethbalance = 0;
	public $transaction = null;
	public $decimals = 0;
	public $count = 0;

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
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array(
					'getBalance', // CONTROLLA IL token BALANCE dell'address
					'send', // invia token
					'checkTxpool', // controlla la blockchain
					'checkBalances', // beta tester conttrollano il balance
				),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	private function setBalance($balance){
		$value = (string) $balance * 1;
		$this->balance = $value;
	}

	private function getBalance(){
		return $this->balance;
	}

	private function setEthBalance($balance){
		$value = (string) $balance * 1;
		$this->ethbalance = $value / 1000000000000000000;
	}

	private function getEthBalance(){
		return $this->ethbalance;
	}

	private function setTransaction($transaction){
		$this->transaction = $transaction;
	}
	private function getTransaction(){
		return $this->transaction;
	}
	private function setDecimals($decimals){
		$this->decimals = $decimals;
	}
	private function getDecimals(){
		return $this->decimals;
	}
	private function setNonce($count){
		$this->count = $count;
	}
	private function getNonce(){
		return $this->count;
	}


	public function actionCheckBalances($address){
		if ($address == '0x0')
			$this->redirect(['contacts/index']);
		//carico le impostazioni dell'applicazione
		$settings=Settings::load();
		if ($settings === null){//} || empty($settings->poa_port)){
			echo CJSON::encode(array(
				"error"=>'Error: Token configuration parameters were not found',
				'id'=>time()
			));
			exit;
		}
		self::setDecimals($settings->poa_decimals);
		//if( webRequest::checkUrl( $settings->poa_url ) ) {
			// mi connetto al nodo poa
			// $web3 = new Web3($settings->poa_url);
			// $web3 = new Web3(WebApp::getPoaNode());
		$poaNode = WebApp::getPoaNode();
		$balanceToken = 0;
		$balanceGas = 0;

		if (!$poaNode){
			$save = new Save;
			$save->WriteLog('bolt','walletERC20','checkBalances',"All Nodes are down.");
		}else{
			$web3 = new Web3($poaNode);
			$contract = new Contract($web3->provider, $settings->poa_abi);
			$utils = $web3->utils;

			//recupero il balance
			$web3->eth->getBalance($address, function ($err, $balanceGas){
				if ($err !== null) {
					echo $err->getMessage();
					exit;
				}
				self::setEthBalance($balanceGas->toString());//imposto il balance globalmente
			});

			$balanceGas = self::getEthBalance();

			$contract->at($settings->poa_contractAddress)->call('balanceOf', $address, [
	            'from' => $address
	        ], function ($err, $result) use ($contract, $utils) {
	            if ($err !== null) {
					echo $err->getMessage();
					exit;
	            }
				#echo '<pre>'.print_r($result,true).'</pre>';
				#exit;
	            if (isset($result)) {
					//$balance = (string) $result[0]->value;
					$value = $utils->fromWei($result[0]->value, 'ether');
					$Value0 = (string) $value[0]->value;
					$Value1 = (float) $value[1]->value / pow(10, self::getDecimals());

					self::setbalance($Value0 + $Value1);
	            }
				#echo '<pre>'.print_r($balance,true).'</pre>';
	        });
			//recupero il valore del balance dalla variabile globale
			$balanceToken = self::getBalance();
		}

		$balances = array(
			'address' => $address,
			'token' => $balanceToken,
			'gas'=> $balanceGas,
		);

		$this->render('balance',array(
			'balances'=>$balances, //lista transazioni tokens
		));

	}




	public function actionGetBalance(){
		$my_address = $_POST['my_address'];

		//carico le impostazioni dell'applicazione
		$settings=Settings::load();
		if ($settings === null){//} || empty($settings->poa_port)){
			echo CJSON::encode(array(
				"error"=>'Error: Token configuration parameters were not found',
				'id'=>time()
			));
			exit;
		}
		self::setDecimals($settings->poa_decimals);
		//if( webRequest::checkUrl( $settings->poa_url ) ) {
			// mi connetto al nodo poa
			// $web3 = new Web3($settings->poa_url);
			// $web3 = new Web3(WebApp::getPoaNode());
		// $balance = 0;
		$poaNode = WebApp::getPoaNode();
		if (!$poaNode){
			$save = new Save;
			$save->WriteLog('bolt','walletERC20','getBalance',"All Nodes are down.");
		}else{
			$web3 = new Web3($poaNode);
			$contract = new Contract($web3->provider, $settings->poa_abi);
			$utils = $web3->utils;

			$contract->at($settings->poa_contractAddress)->call('balanceOf', $my_address, [
	            'from' => $my_address
	        ], function ($err, $result) use ($contract, $utils) {
	            if ($err !== null) {
					echo CJSON::encode(array(
						"error"=>$err->getMessage(),
						'id'=>time()
					));
					exit;
	            }
				#echo '<pre>'.print_r($result,true).'</pre>';
				#exit;
	            if (isset($result)) {
					//$balance = (string) $result[0]->value;
					$value = $utils->fromWei($result[0]->value, 'ether');
					$Value0 = (string) $value[0]->value;
					$Value1 = (float) $value[1]->value / pow(10, self::getDecimals());

					self::setbalance($Value0 + $Value1);
	            }
				#echo '<pre>'.print_r($balance,true).'</pre>';
	        });
			//recupero il valore del balance dalla variabile globale
			//$balance = self::getBalance();
		}

		$send_json = array(
			'balance' => self::getBalance(),
			//'eurbalance' => round(NaPay::getFiatRate('tts') * $balance,2),
			//'id' => crypt::Encrypt($wallets->id_wallet),
			//'id' => time(),
			'id'=> $my_address,
		);
    	echo CJSON::encode($send_json);
	}






	/**
	 * Sends Token. Save transaction and send notification
	 * @param string $_POST['from'] the from ethereum address
	 * @param string $_POST['to'] the to ethereum address
	 * @param integer $_POST['amount'] the amount to be send
	 * @return 'id_token' 'data' 'status' 'token_price' 'my_address'  'url'
	 * @throws CJSON
	 */
	 public function actionSend()
	 {
		//echo '<pre>post: '.print_r($_POST,true).'</pre>';
		#exit;

		$fromAccount = $_POST['from'];
 		$toAccount = $_POST['to'];
 		$amount = $_POST['amount'];

		$memo = $_POST['memo'];
		//$gas = $_POST['gas'];
		//$gas = '0x'. dechex(pow($_POST['gas'],8));

		$pow = $_POST['gas'] * pow(10,10);
		$hex = dechex($pow);
		$gas = '0x'.$hex;

		$prv_key = cryptoJsAesDecrypt(crypt::Decrypt($_POST['prv_pas']), $_POST['prv_key']);

		if (null === $prv_key){
			echo CJSON::encode(array(
				"error"=>true,
				"message"=>'The Service Worker probably isn\'t working.',
				'id'=>time()
			));
			exit;
		}
		//echo '<pre>$prv_key: '.print_r($prv_key,true).'</pre>';
		// echo '<pre>pow: '.print_r($pow,true).'</pre>';
		// echo '<pre>gas: '.print_r($gas,true).'</pre>';
		//
		// exit;



		//Carico i parametri
		$settings=Settings::load();
		if ($settings === null ){//} || empty($settings->poa_port)){
			echo CJSON::encode(array(
				"error"=>'Errore: I parametri di configurazione POA non sono stati trovati',
				'id'=>time()
			));
			exit;
		}
		self::setDecimals($settings->poa_decimals);

		$amountForContract = $amount * pow(10, self::getDecimals()); //PERCHE' I DECIMALI DEL TOKEN SONO 2

		//CREO la transazione
		/**
		  * This is fairly straightforward as per the ABI spec
		  * First you need the function selector for test(address,uint256) which is the first four bytes of the keccak-256 hash of that string, namely 0xba14d606.
		  * Then you need the address as a 32-byte word: 0x000000000000000000000000c5622be5861b7200cbace14e28b98c4ab77bd9b4.
		  * Finally you need amount (10000) as a 32-byte word: 0x0000000000000000000000000000000000000000000000000000000000002710
			*	0x03746bfdeacebf4f37e099511c16683df3bac8eb																										 0000000000000000000000000000000000000000000000000000000000000079
		*/

		$data_tx = [
			'selector' => '0xa9059cbb', //ERC20	0xa9059cbb function transfer(address,uint256)
			'address' => self::Encode("address", $toAccount), // $receiving_address è l'indirizzo destinatario,
			'amount' => self::Encode("uint", $amountForContract), //$amount l'ammontare della transazione (da moltiplicare per 10^2)
		];

		//if( webRequest::checkUrl( $settings->poa_url ) ) {
			// recupero la nonce per l'account
			$nonce = 0;
			// $web3 = new Web3($settings->poa_url);
			// $web3 = new Web3(WebApp::getPoaNode());
			$poaNode = WebApp::getPoaNode();
			if (!$poaNode){
				$save = new Save;
				$save->WriteLog('bolt','walletERC20','send',"All Nodes are down.");
				echo CJSON::encode(array(
					"error"=>"All Nodes are down.",
					'id'=>time()
				));
				exit;
			}else{
				$web3 = new Web3($poaNode);
			}

			$web3->eth->getTransactionCount($fromAccount, function ($err, $res) use (&$nonce) {
				if($err !== null) {
					echo CJSON::encode(array(
						"error"=>$err->getMessage(),
						"getTransactionCount"=>'error',
						'id'=>time()
					));
					exit;
				}
				$nonce = $res;
			});

				// echo '<pre>ERRORE: [ricerca nonce] '.print_r('0x'.dechex(gmp_intval($nonce->value)),true).'</pre>';
				// exit;

			self::setNonce(gmp_intval($nonce->value));

			while (self::getNonce() < 1000)
			{
				$transaction = new Transaction([
				   'nonce' => '0x'.dechex(self::getNonce()), //è un object BigInteger
				   'from' => $fromAccount, //indirizzo commerciante
				   'to' => $settings->poa_contractAddress, //indirizzo contratto
				   'gas' => '0x200b20', // $gas se supera l'importo 0x200b20 va in eerrore gas exceed limit !!!!!!
				   'gasPrice' => '1000', // gasPrice giusto?
				   'value' => '0',
				   'chainId' => $settings->poa_chainId,
				   'data' =>  $data_tx['selector'] . $data_tx['address'] . $data_tx['amount'],
			    ]);

				$transaction->offsetSet('chainId', $settings->poa_chainId);
				// echo '<pre>Transazione: '.print_r($transaction,true).'</pre>';
				// echo '<pre>$prv_key: '.print_r($prv_key,true).'</pre>';
				// echo '<pre>$_POST: '.print_r($_POST,true).'</pre>';
				// exit;
				$signed_transaction = $transaction->sign($prv_key); // la chiave derivata da json js AES to PHP
				#echo '<pre>Transazione firmata: '.print_r($signed_transaction,true).'</pre>';

				$web3->eth->sendRawTransaction(sprintf('0x%s', $signed_transaction), function ($err, $tx) {
					if ($err !== null) {
						$jsonBody = $this->getJsonBody($err->getMessage());
						// (
    				// 	[jsonrpc] => 2.0
    				// 	[id] => 800379331
    				// 	[error] => Array
        		// 	(
            // 			[code] => -32001
            // 			[message] => Nonce too low
        		// 	)
						// )
						// echo '<pre>ERRORE: [ricerca nonce] '.print_r($this->getJsonBody($err->getMessage()),true).'</pre>';
						// exit;
						if ($jsonBody['error']['code'] == -32001){
							$count = self::getNonce() +1;
							self::setNonce($count);
						}else{
							echo CJSON::encode(array(
								"error"=>'Error: '.$jsonBody['error']['message'],
								'id'=>time()
							));
							exit;
						}
					}
					//echo 'TX: ' . $tx;
					//exit;
					self::setTransaction($tx);

				});
				if (self::getTransaction() !== null){
					break;
				}
			}
			// echo '<pre>ERRORE: [get nonce] '.print_r(self::getNonce(),true).'</pre>';
			// exit;
			//
			if (self::getTransaction() === null){
				echo CJSON::encode(array(
					"error"=>'Invalid nonce: '.self::getNonce(),
					'id'=>time()
				));
				exit;
			}

			// blocco in cui presumibilmente avviene la transazione
			$response = null;
			$web3->eth->getBlockByNumber('latest',false, function ($err, $block) use (&$response){
				if ($err !== null) {
					throw new CHttpException(404,'Errore: '.$err->getMessage());
				}
				$response = $block;
			});

			//salva la transazione ERC20
	 		$timestamp = time();
	 		$invoice_timestamp = $timestamp;

	 		//calcolo expiration time
	 		$totalseconds = $settings->poa_expiration * 60; //poa_expiration è in minuti, * 60 lo trasforma in secondi
	 		$expiration_timestamp = $timestamp + $totalseconds; //DEFAULT = 15 MINUTES

	 		//$rate = $this->getFiatRate(); // al momento il token è peggato 1/1 sull'euro
			$rate = eth::getFiatRate('token'); //

	 		$attributes = array(
	 			'id_user' => Yii::app()->user->objUser['id_user'],
	 			'status'	=> 'new',
				'type'	=> 'token',
	 			'token_price'	=> $amount,
	 			'token_ricevuti'	=> 0,
	 			'fiat_price'		=> abs($rate * $amount),
	 			'currency'	=> 'EUR',
	 			'item_desc' => 'wallet',
	 			'item_code' => '0',
	 			'invoice_timestamp' => $invoice_timestamp,
	 			'expiration_timestamp' => $expiration_timestamp,
	 			'rate' => $rate,
	 			'from_address' => $fromAccount,
				'to_address' => $toAccount,
				'blocknumber' => hexdec($response->number), // numero del blocco in base 10
				'txhash'	=> self::getTransaction(),
	 		);
			//salvo la transazione in db. Restituisce object
			$save = new Save;
			$tokens = $save->Token($attributes);

			// salvo l'eventuale messaggio inserito
			if (!empty($memo)){
				$save = new Save;
				$message = $save->Memo([
					'id_token'=>$tokens->id_token,
					'memo'=>crypt::Encrypt($memo)
				]);
			}

	 		//salva la notifica
	 		$notification = array(
	 			'type_notification' => 'token',
	 			'id_user' => $tokens->id_user,
	 			'id_tocheck' => $tokens->id_token,
	 			'status' => 'new',
				//'description' => Notifi::description($tokens->status, $tokens->type),
				'description' => 'You have sent a new transaction.',
				'url' => Yii::app()->createUrl("tokens/view",['id'=>crypt::Encrypt($tokens->id_token)]),
	 			'timestamp' => $timestamp,
	 			'price' => $rate * $amount,
	 			'deleted' => 0,
	 		);
			$save = new Save;
			Push::Send($save->Notification($notification),'bolt');

			//eseguo lo script che si occuperà in background di verificare lo stato dell'invoice appena creata...
			$cmd = Yii::app()->basePath.DIRECTORY_SEPARATOR.'yiic send --id='.crypt::Encrypt($tokens->id_token);
			Utils::execInBackground($cmd);

	 		//adesso posso uscire
	 		$send_json = array(
				'id' => $invoice_timestamp, //NECESSARIO PER IL SALVATAGGIO IN  indexedDB quando ritorna al Service Worker
	 			'id_token' => crypt::Encrypt($tokens->id_token),
	 			'data'	=> WebApp::dateLN($invoice_timestamp,$tokens->id_token),
	 			'status' => WebApp::walletStatus($tokens->status),
				'token_price' => WebApp::typePrice($tokens->token_price,'sent'),
				// 'from_address' => substr($fromAccount,0,7).'&hellip;',
				// 'to_address' => substr($toAccount,0,7).'&hellip;',
				'from_address' => $fromAccount,
				'to_address' => $toAccount,
	 			'url' => Yii::app()->createUrl("tokens/view",['id'=>crypt::Encrypt($tokens->id_token)]),
	 		);
		// }else{
		// 	$send_json = array(
		// 		'id' => $invoice_timestamp, //NECESSARIO PER IL SALVATAGGIO IN  indexedDB quando ritorna al Service Worker
	 	// 		'id_token' => 0,
	 	// 		'data'	=> date("d M Y H:i:s",$invoice_timestamp),
	 	// 		'status' => "invalid",
		// 		'token_price' => "0",
		// 		// 'from_address' => substr($fromAccount,0,7).'&hellip;',
		// 		// 'to_address' => substr($toAccount,0,7).'&hellip;',
		// 		'from_address' => $fromAccount,
		// 		'to_address' => $toAccount,
	 	// 		'url' => "#",
	 	// 	);
		// }
     	echo CJSON::encode($send_json);
	}

	/**
	 * Questa funzione controlla lo stato della transazione token
	 * Viene interrogato dal SW dopo che è stato registrata la richiesta in 'sync-txPool'
	 * La risposta viene salvata in indexedDB
	 * @param POST
	 * @param integer id_token the ID of the model to be searched
	 * @return
	 */
	public function actionCheckTxpool(){
		$model = $this->loadModel(crypt::Decrypt($_POST['id_token']));
		$wallets = Wallets::model()->findByAttributes(['id_user'=>Yii::app()->user->objUser['id_user']]);

		echo CJSON::encode(array(
			'id' => time(), //NECESSARIO PER IL SALVATAGGIO IN  indexedDB quando ritorna al Service Worker
			"status"=>$model->status,
			//"status_wlink"=>"<a href='index.php?r=tokens/view&id=".crypt::Encrypt($model->id_token)."'>". WebApp::walletStatus($model->status) ."</a>",
			"status_wlink"=>WebApp::translateMsg($model->status),
			"openUrl"=>Yii::app()->createUrl('tokens/view',array('id'=>crypt::Encrypt($model->id_token))), // url per i messaggi push
			'to_address'=>$model->to_address,
			'from_address'=>$model->from_address,
			'token_price'=>$model->token_price,
			'token_price_wsymbol' => WebApp::typePrice($model->token_price,($model->from_address == $wallets->wallet_address ? 'sent' : 'received')),
			'id_token'=>$_POST['id_token'],
		));
	}

	//recupera lo streaming json dal contenuto txt del body
	private function getJsonBody($response)
	{
		$start = strpos($response,'{',0);
		$substr = substr($response,$start);
		return json_decode($substr, true);
	}

	private function Str2Hex(string $str): string {
		 $hex = "";
		 for ($i = 0; $i < strlen($str); $i++) {
			 $hex .= dechex(ord($str[$i]));
		 }
		 return $hex;
	}

	/* funzione per codificare il valore $value del tipo $type in hex */
	private function Encode(string $type, $value): string {
		 $len = preg_replace('/[^0-9]/', '', $type);

		 if (!$len) {
			 $len = null;
		 }

		 $type = preg_replace('/[^a-z]/', '', $type);
		 switch ($type) {
			 case "hash":
			 case "address":
				 if (substr($value, 0, 2) === "0x") {
					 $value = substr($value, 2);
				 }
				 break;
			 case "uint":
			 case "int":
				 //$value = BcMath::DecHex($value);
				 $value = dechex($value);
				 break;
			 case "bool":
				 $value = $value === true ? 1 : 0;
				 break;
			 case "string":
				 $value = self::Str2Hex($value);
				 break;
			 default:
				 echo 'Cannot encode value of type '. $type;
				 break;
		 }
		 return substr(str_pad(strval($value), 64, "0", STR_PAD_LEFT), 0, 64);
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
