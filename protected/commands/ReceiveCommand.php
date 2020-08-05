<?php
// DA CONTROLLARE/VALUTARE UTILIZZO DI QUESTA FUNZIONE!!!

class ReceiveCommand extends CConsoleCommand
{
	public $logfilehandle = null;
	public $transactions = null;
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

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Pos the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Tokens::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');

		return $model;
	}

	public function actionIndex($id){
		$ReceivingType = "null";
		$nomeLogFile = Yii::app()->basePath."/log/receive-command.log";
		if (!is_writable($nomeLogFile)){
			chmod($nomeLogFile, 0755);  // octal; correct value of mode
		}

		//non c'è output, pertanto salvo gli errori nel log file
		$myfile = fopen($nomeLogFile, "a");
		$this->setLogFile($myfile);
		fwrite($this->getLogFile(), "\r\n" . date('Y/m/d h:i:s a', time()) . " : Start : Checking invoice n. $id\n");

		set_time_limit(0); //imposto il time limit unlimited

		//carico l'invoice
		$tokens = $this->loadModel(crypt::Decrypt($id));
		#fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : tokens loaded\n");

		/***	AUTOLOADER ETHEREUM CLASSES	*/
		require_once Yii::app()->basePath . '/extensions/web3/vendor/autoload.php';

		//Carico i parametri
		$settings=Settings::load();
		if ($settings === null){//} || empty($settings->poa_port)){
			#fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : Settings NOT loaded\n");
			exit;
		}
		#fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : ethereum loaded\n");
		self::setDecimals($settings->poa_decimals);

		// mi connetto al nodo poa
		// $web3 = new Web3\Web3($settings->poa_url);
		// $web3 = new Web3(WebApp::getPoaNode());
		$poaNode = WebApp::getPoaNode();
		if (!$poaNode){
			$save->WriteLog('bolt','commands','send',"All Nodes are down.",true);
		}
		$web3 = new Web3($poaNode);
		#fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : web3 connected\n");
		$eth = $web3->eth;
		$utils = $web3->utils;
		$contract = new Web3\Contract($web3->provider, $settings->poa_abi);

		$expiring_seconds = $tokens->expiration_timestamp +1 - time();
		$transactionValue = $tokens->token_ricevuti;

		while(true){
			$ipnflag = false;
			if ($tokens->status == 'new' || $tokens->status == 'paidPartial'){ //se il valore è new, paidPartial proseguo

				$eth->getBlockByNumber('latest',true, function ($err, $block){
					if ($err !== null) {
						fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : error: ".$err->getMessage()."\n");
						exit;
					}
					#echo '<pre>'.print_r($block,true).'</pre>';
					self::setTransactions($block->transactions);//imposto il balance globalmente
				});

				$transactions = self::getTransactions();

				if (!empty($transactions)){
					foreach ($transactions as $transaction){
						//controlla transazioni ethereum
						if (strtoupper($transaction->to) == strtoupper($tokens->to_address)){ //eureka!! indirizzo ricevente trovato
							$ReceivingType = 'ether';
							$transactionValue = self::hex2eth($transaction->value);

							/*
							* MODIFICHE PRESE DA ISSUE #141
							* https://github.com/sc0Vu/web3.php/issues/141
							*/
							#list($quotient, $residue) = $utils::fromWei($transaction->value,'ether');
							#$transactionValue = floatval($quotient->toString().'.'.str_pad($residue->toString(), 18, '0', STR_PAD_LEFT));

							//check if transaction deriva da wallet o da pos keypad
							$tokens->rate = eth::getFiatRate('eth'); //
							$tokens->status = 'complete';
							$tokens->token_price = $transactionValue;
							$tokens->fiat_price = $tokens->rate * $transactionValue;
							$tokens->type = 'ether';
							$tokens->token_ricevuti = $transactionValue;
							$tokens->txhash = $transaction->hash;

							$ipnflag = true;
							break; //foreach
						}

						//controlla transazioni sul token (tramite smart contract)
						if (strtoupper($transaction->to) == strtoupper($settings->poa_contractAddress))
						{ 	//eureka!! indirizzo smart-contract trovato
							$ReceivingType = 'token';
							$transactionId = $transaction->hash;
							$transactionContract = '';
							$contract->eth->getTransactionReceipt($transactionId, function ($err, $recepit) use (&$transactionContract)
							{
	                            if ($err !== null) {
									fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : <pre>ERRORE: ".print_r($err,true)."</pre>\n");
	                                exit;
	                            }
	                            if ($recepit)
									$transactionContract = $recepit;
							});
							if ($transactionContract  <> ''){
								$receivingAccount = $transactionContract->logs[0]->topics[2];
								$receivingAccount = str_replace('000000000000000000000000','',$receivingAccount);

								$transactionValue = self::wei2eth($transactionContract->logs[0]->data, self::getDecimals()); //2 decimali del token

								//controlla se il toAccount sia lo stesso del ricevente
								if (strtoupper($receivingAccount) == strtoupper($tokens->to_address))
								{
									$tokens->rate = eth::getFiatRate('token'); //
									$tokens->status = 'complete';
									$tokens->token_price = $transactionValue;
									$tokens->fiat_price = $tokens->rate * $transactionValue;
									$tokens->type = 'token';
									$tokens->token_ricevuti = $transactionValue;
									$tokens->txhash = $transaction->hash;
									$ipnflag = true;

									//QUINDI INVIO UN MESSAGGIO DI NOTIFICA
									$notification = array(
										'type_notification' => 'token',
										'id_user' => $tokens->id_user,
										'id_tocheck' => $tokens->id_token,
										'status' => $tokens->status,
										//'description' => Notifi::description($tokens->status, $tokens->type),
										'description' => 'A transaction you received has been completed.',
										'url' => Yii::app()->createUrl("tokens/views",['id'=>crypt::Encrypt($tokens->id_token)]),
										'timestamp' => time(),
										'price' => $tokens->fiat_price,
										'deleted' => 0,
									);
									$save = new Save;
									Push::Send($save->Notification($notification),'bolt');


									//break; //foreach
									#fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : <pre>".print_r($value,true)."</pre>\n");
								}
							}
                        }
					}//foreach loop
				}
				if ($expiring_seconds < 0 && $token->status <> 'complete'){//invoice expired
					$tokens->status = 'expired';
					$ipnflag = true;
				}
			}
			if ($expiring_seconds < 0){//invoice expired
				$ipnflag = true;
			}
			if ($ipnflag){ //send ipn in case flag is true: può venire
				// SALVA I TOKEN
				if ($tokens->update()){
					fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : End   : $ReceivingType invoice n. $id, Status: $tokens->status, Received: $tokens->token_price.\n");
					$this::sendIpn($tokens->attributes,$ReceivingType);
				}else{
					fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : Error : Cannot save $ReceivingType transaction on invoice n. $id, Status: $tokens->status.\n");
				}

				break;
			}

			//conto alla rovescia fino alla scadenza dell'invoice
			#fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : '.$ReceivingType.' Status: ".$tokens->status.", Seconds: ".$expiring_seconds."\n");
			#echo "<br>".$expiring_seconds;
			$expiring_seconds --;
			sleep(1);
		}
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
    	return hexdec($number)/ pow(10, $decimals);
	}
	/*
	 * funzione che riceve il valore della transazione eth
	 * in formato hex (0xde0b6b3a7640000)
	 * e la trasforma da wei in un numero intero (ether)
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
	private function setDecimals($decimals){
		$this->decimals = $decimals;
	}
	private function getDecimals(){
		return $this->decimals;
	}


	private function sendIpn($ipn, $ReceivingType){
		$myfile = fopen(Yii::app()->basePath."/log/receive-ipn.log", "a");
        $date = date('Y/m/d h:i:s a', time());
		#fwrite($myfile, $date . " : 0. Start Token Ipnlogger\n");
		//
		$tokens = (object) $ipn;

		if (true === empty($tokens)) {
			fwrite($myfile, $date . " : Error. Could not decode the JSON payload from Token Server.\n");
			fclose($myfile);
			throw new \Exception('Could not decode the JSON payload from Token Server.');
		}else{
			#fwrite($myfile, $date . " : 2. Json ok.\n");
		}

		// //QUINDI INVIO UN MESSAGGIO DI NOTIFICA
		// $notification = array(
		// 	'type_notification' => $tokens->type,
		// 	'id_user' => $tokens->id_user,
		// 	'id_tocheck' => $tokens->id_token,
		// 	'status' => $tokens->status,
		// 	'description' => Notifi::description($tokens->status, $tokens->type),
		// 	'url' => Yii::app()->createUrl("tokens/views",['id'=>crypt::Encrypt($tokens->id_token)]),
		// 	'timestamp' => time(),
		// 	'price' => $tokens->token_price,
		// 	'deleted' => 0,
		// );
		// $save = new Save;
		// Push::Send($save->Notification($notification));

		//ADESSO POSSO USCIRE CON UN MESSAGGIO POSITIVO ;^)
		fwrite($myfile, $date . " : IPN received for $ReceivingType Invoice n. ".crypt::Encrypt($tokens->id_token).", Status=" .$tokens->status.", Price=". $tokens->token_price. "\n");
		fclose($myfile);
	}
}
?>
