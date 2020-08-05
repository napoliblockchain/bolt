<?php
class AlertCommand extends CConsoleCommand
{
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


	public function actionIndex($idUser,$idInstitute){
		$save = new Save;
		$save->WriteLog('bolt','commands','alert countdown',"Start : Checking countdown id user #: $idUser");
		set_time_limit(0); //imposto il time limit unlimited

		$id_user = crypt::Decrypt($idUser);
		$id_institute = crypt::Decrypt($idInstitute);

		// TEST con id MANUALE
		// $id_user = $idUser;   // 1
		// $id_institute = $idInstitute; // 8

		$institute = Institutes::model()->findByPk($id_institute);

		$expiring_seconds = $institute->max_wait_time * 60;
		while (true)
		{
			$save->WriteLog('bolt','commands','alert countdown',"Seconds: ".$expiring_seconds);
			$ipnflag = false;

			if ($expiring_seconds < 0){//invoice expired
				$ipnflag = true;
			}

			if ($ipnflag){ //send ipn in case flag is true: può venire
					$save->WriteLog('bolt','commands','alert countdown',"End: Send ALERT");

					//QUINDI INVIO UN MESSAGGIO DI NOTIFICA
					$notification = array(
						'type_notification' => 'alarm',
						'id_user' => $id_user,
						'id_tocheck' => 0,
						'status' => 'failed',
						'description' => $institute->max_wait_message,
						'url' => '#',
						'timestamp' => time(),
						'price' => 0,
						'deleted' => 0,
					);
					Push::Send($save->Notification($notification),'bolt');
					break;
			}
			// echo "<br/>";
			// echo 'Mancano '.$expiring_seconds.' secondi.';

			$expiring_seconds --;
			sleep(1);
		}
	}
}
?>
