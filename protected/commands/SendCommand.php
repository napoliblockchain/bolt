<?php
require_once Yii::app()->params['libsPath'] . '/ethereum/web3/vendor/autoload.php';

use Web3\Web3;
use Web3\Contract;

class SendCommand extends CConsoleCommand
{
	public $transaction = null;
	public $logfilehandle = null;
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
		$save = new Save;

		$SendingType = "null";

		$nomeLogFile = Yii::app()->basePath."/log/send-command.log";
		// if (!is_writable($nomeLogFile)){
		// 	chmod($nomeLogFile, 0755);  // octal; correct value of mode
		// }

		//non c'è output, pertanto salvo gli errori nel log file
		$myfile = fopen($nomeLogFile, "a");
		$this->setLogFile($myfile);

		$save->WriteLog('bolt','commands','send',"Start : Checking tx id #: $id");

		set_time_limit(0); //imposto il time limit unlimited

		//carico l'invoice
		$tokens = $this->loadModel(crypt::Decrypt($id));
		$save->WriteLog('bolt','commands','send',"Tokens loaded.");

		//Carico i parametri
		$settings=Settings::load();
		if ($settings === null){//} || empty($settings->poa_port)){
			$save->WriteLog('bolt','commands','send',"Settings NOT loaded.",true);
		}

		self::setDecimals($settings->poa_decimals);

		// mi connetto al nodo poa
		// $web3 = new Web3($settings->poa_url);
		$poaNode = WebApp::getPoaNode();
		if (!$poaNode){
			$save->WriteLog('bolt','commands','send',"All Nodes are down.",true);
		}
		$web3 = new Web3($poaNode);
		$contract = new Contract($web3->provider, $settings->poa_abi);
		$save->WriteLog('bolt','commands','send',"Web3 connected.");

		$eth = $web3->eth;
		$utils = $web3->utils;

		$value = 0;
		$array = [];
		$transactionValue = 0;

		$SendingType = $tokens->type;
		$expiring_seconds = $settings->poa_expiration * 60;
		while (true)
		{
			$save->WriteLog('bolt','commands','send'," : $SendingType status: ".$tokens->status.", Seconds: ".$expiring_seconds);
			$ipnflag = false;

			if ($tokens->type == 'ether'){
				$web3->eth->getTransactionByHash($tokens->txhash, function ($err, $transaction){
					if ($err !== null) {
						$save->WriteLog('bolt','commands','send'," : error ether: ".$err->getMessage(), true);
					}
					self::setTransaction($transaction);
				});

				$transaction = self::getTransaction();
				#fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : block number: ".$transaction->blockNumber."\n");

				//controlla lo status del blocco in cui viene inclusa la Transazione
				if (!empty($transaction->blockNumber)){
					$value = $utils->fromWei($transaction->value, 'ether');
					$array = (array) $value;
					$transactionValue = (string) $array[0]->value;

					$tokens->status = 'complete';//payment is major of token_price
					$ipnflag = true;
				}
			}else{
				$contract->eth->getTransactionReceipt($tokens->txhash, function ($err, $transaction)  {
					if ($err !== null) {
						$save->WriteLog('bolt','commands','send'," : error token: ".$err->getMessage());
						sleep(5); //exit;
					}
					self::setTransaction($transaction);
				});
				$transaction = self::getTransaction();


				//controlla lo status del blocco in cui viene inclusa la Transazione
				if (!empty($transaction->blockNumber)){
					if (!empty($transaction->logs)){
						//controlla se il fromAccount sia lo stesso del sender
						// fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : token transaction: <pre>".print_r($transaction,true)."</pre>\n");
						// fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : check sender " .$transaction->from. " & reicever: ".$tokens->from_address."\n");

						if (strtoupper($transaction->from) == strtoupper($tokens->from_address)){
							$transactionValue = self::wei2eth($transaction->logs[0]->data, self::getDecimals()); //2 decimali del token

							$tokens->status = 'complete';//payment is major of token_price
							$ipnflag = true;

							//QUINDI INVIO UN MESSAGGIO DI NOTIFICA
							$notification = array(
								'type_notification' => 'token',
								'id_user' => $tokens->id_user,
								'id_tocheck' => $tokens->id_token,
								'status' => $tokens->status,
								'description' => 'A transaction you sent has been completed.',
								//'url' => Yii::app()->createUrl("tokens/view",['id'=>crypt::Encrypt($tokens->id_token)]),
								'url' => 'index.php?r=tokens/view&id='.crypt::Encrypt($tokens->id_token),
								'timestamp' => time(),
								'price' => $tokens->fiat_price,
								'deleted' => 0,
							);
							Push::Send($save->Notification($notification),'bolt');

							// fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : notification: <pre>".print_r($notification,true)."</pre>\n");

						}
					}
				}
			}

			if ($expiring_seconds < 0){//invoice expired
				if ($tokens->status <> 'complete')
					$tokens->status = 'failed';
				$ipnflag = true;
			}

			if ($ipnflag){ //send ipn in case flag is true: può venire
				#echo '<pre>'.print_r($tokens->attributes,true).'</pre>';

				// SALVA I TOKEN
				if ($tokens->update()){
					$save->WriteLog('bolt','commands','send',"End   : $SendingType wallet invoice n. $id, Status: $tokens->status, Price: $transactionValue.");
					$this::sendIpn($tokens->attributes, $SendingType);

				}
				else
					$save->WriteLog('bolt','commands','send',"Error : Cannot save $SendingType transaction on invoice n. $id, Status: $tokens->status.");

				break;
			}
			#echo $expiring_seconds;
			$expiring_seconds --;
			sleep(1);
		}
		#fwrite($this->getLogFile(), date('Y/m/d h:i:s a', time()) . " : Uscito...\n");
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

	private function setTransaction($transaction){
		$this->transaction = $transaction;
	}
	private function getTransaction(){
		return $this->transaction;
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


	private function sendIpn($ipn, $SendingType){
		$myfile = fopen(Yii::app()->basePath."/log/send-ipn.log", "a");
        $date = date('Y/m/d h:i:s a', time());
		#fwrite($myfile, $date . " : 0. Start wallet Ipnlogger\n");
		//
		$tokens = (object) $ipn;

		if (true === empty($tokens)) {
			fwrite($myfile, $date . " : Error. Could not decode the JSON payload from Token Server.\n");
			fclose($myfile);
			throw new \Exception('Could not decode the JSON payload from Token Server.');
		}else{
			#fwrite($myfile, $date . " : 2. Json ok.\n");
		}

		//ADESSO POSSO USCIRE CON UN MESSAGGIO POSITIVO ;^)
		fwrite($myfile, $date . " : IPN received for $SendingType Wallet Invoice n. ".crypt::Encrypt($tokens->id_token).", Status=" .$tokens->status.", Price=". $tokens->token_price. "\n");
		fclose($myfile);
	}
}
?>
