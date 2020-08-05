<?php
require_once Yii::app()->params['libsPath'] . '/ethereum/web3/vendor/autoload.php';
require_once Yii::app()->params['libsPath'] . '/ethereum/ethereum-tx/vendor/autoload.php';
use Web3\Web3;
use Web3p\EthereumTx\Transaction;

class WalletETHController extends Controller
{
	public $balance = 0;
	public $transaction = null;
	public $gasprice = null;
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
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array(
					'getBalance',
					'loadGAS', // INVIA ETHER SE IL VALORE SCENDE SOTTO 0
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
		$this->balance = $value / 1000000000000000000;
	}

	private function getBalance(){
		return $this->balance;
	}

	private function setGasPrice($gasprice){
		$value = (string) $gasprice * 1;
		$this->gasprice = $value / 100000000;
	}
	private function getGasPrice(){
		return $this->gasprice;
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


	public function actionLoadGAS(){
		//Carico i parametri
		$settings=Settings::load();
		if ($settings === null
			|| empty($settings->poa_sealerAccount)
			|| empty($settings->poa_sealerPrvKey)
			//|| empty($settings->poa_url)
		){
			echo CJSON::encode(array(
				"error"=>'Errore: I parametri di configurazione POA non sono stati trovati',
				'id'=>time()
			));
			exit;
		}

		$fromAccount = $settings->poa_sealerAccount; //'0x654b98728213cf1e20e90b1942fdc5597984eb70'; // node1 fujitsu gabcoin
		$amount = 1;
		$toAccount = $_POST['to'];
		$hex = dechex(21004);
		$gas = '0x'.$hex;

		$prv_key = crypt::Decrypt($settings->poa_sealerPrvKey); //'8303D3CA466B73ED5A65DCAB439947793426172C7777F29A8DB68586D4A079D6'; // chiave privata gabcoin Node1 fujitsu server

		// recupero la nonce per l'account
		$nonce = 0;
		// $web3 = new Web3($settings->poa_url);
		// $web3 = new Web3(WebApp::getPoaNode());
		$poaNode = WebApp::getPoaNode();
		if (!$poaNode){
			$save = new Save;
			$save->WriteLog('bolt','walletETH','loadGAS',"All Nodes are down.");
			echo CJSON::encode(array(
				"error"=>"All Nodes are down.",
				'id'=>time()
			));
			exit;
		}
		$web3 = new Web3($poaNode);
		$web3->eth->getTransactionCount($fromAccount, function ($err, $res) use (&$nonce) {
			if($err !== null) {
				echo CJSON::encode(array(
					"error"=>$err->getMessage(),
					'id'=>time()
				));
				exit;
			}
			$nonce = $res;
		});

		self::setNonce(gmp_intval($nonce->value));

		$transaction = new Transaction([
		   'nonce' => '0x'.dechex(self::getNonce()), //è un object BigInteger
		   'from' => $fromAccount, //indirizzo commerciante
		   'to' => $toAccount, //indirizzo contratto
		   'gas' => '0x200b20', // $gas se supera l'importo 0x200b20 va in eerrore gas exceed limit !!!!!!
		   'gasPrice' => '1000', // gasPrice giusto?
		   'value' => 1 * pow(10, 18),
		   'chainId' => $settings->poa_chainId,
		   'data' =>  '0x0', //$data_tx['selector'] . $data_tx['address'] . $data_tx['amount'],
		]);

		$transaction->offsetSet('chainId', $settings->poa_chainId);
		// echo '<pre>Transazione: '.print_r($transaction,true).'</pre>';
		// echo '<pre>$prv_key: '.print_r($prv_key,true).'</pre>';
		// echo '<pre>$_POST: '.print_r($_POST,true).'</pre>';
		// exit;
		$signed_transaction = $transaction->sign($prv_key); // la chiave derivata da json js AES to PHP
		// echo '<pre>Transazione firmata: '.print_r($signed_transaction,true).'</pre>';
		$web3->eth->sendRawTransaction(sprintf('0x%s', $signed_transaction), function ($err, $tx) {
			if ($err !== null) {
				$jsonBody = $this->getJsonBody($err->getMessage());

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
			self::setTransaction($tx);
		});

		if (self::getTransaction() === null){
			echo CJSON::encode(array(
				"error"=>'Invalid nonce: '.self::getNonce(),
				'id'=>time()
			));
			exit;
		}
		$invoice_timestamp = time();

		$send_json = array(
			'id' => $invoice_timestamp, //NECESSARIO PER IL SALVATAGGIO IN  indexedDB quando ritorna al Service Worker
			'id_token' => 0,
			'data'	=> date("d M Y H:i:s",$invoice_timestamp),
			'status' => "complete",
			'token_price' => $amount,
			'from_address' => $fromAccount,
			'to_address' => $toAccount,
			'url' => "#",
		);

    	echo CJSON::encode($send_json);
	}

	//recupera lo streaming json dal contenuto txt del body
	private function getJsonBody($response)
	{
		$start = strpos($response,'{',0);
		$substr = substr($response,$start);
		return json_decode($substr, true);
	}


	public function actionGetBalance(){
		$my_address = $_POST['my_address'];

		//carico le impostazioni dell'applicazione
		$settings=Settings::load();
		if ($settings === null){//} || empty($settings->poa_port)){
			echo CJSON::encode(array(
				"error"=>'Errore: I parametri di configurazione Token non sono stati trovati',
				'id'=>time(),
				'balance'=>0,
			));
			exit;
		}
		//if( webRequest::checkUrl( $settings->poa_url ) ) {
			// mi connetto al nodo poa
			// $web3 = new Web3($settings->poa_url);
			// $web3 = new Web3(WebApp::getPoaNode());
		$poaNode = WebApp::getPoaNode();
		if (!$poaNode){
			$save = new Save;
			$save->WriteLog('bolt','walletETH','getBalance',"All Nodes are down.");
		}else{
			$web3 = new Web3($poaNode);

			$eth = $web3->eth;
			$balance = 0;

			//recupero il balance
			$web3->eth->getBalance($my_address, function ($err, $balance){
				if ($err !== null) {
					echo CJSON::encode(array(
						"error"=>$err->getMessage(),
						'id'=>time(),
						'balance'=>0,
					));
					exit;
				}
				self::setBalance($balance->toString());//imposto il balance globalmente
			});
		}


		//finalmente ritorno all'app e restituisco l'url con il qr-code della transazione da pagare!!!
		$send_json = array(
			'balance' => self::getBalance(),
			'id'=> $my_address,
		);
	    echo CJSON::encode($send_json);
	}


}
