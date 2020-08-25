<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Notifi');
Yii::import('libs.NaPacks.WebApp');

class BackendController extends Controller
{

	public function init()
	{
		Yii::app()->language = ( isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'it' );
		Yii::app()->sourceLanguage = ( isset($_COOKIE['langSource']) ? $_COOKIE['langSource'] : 'it_it' );

		new JsTrans('js',Yii::app()->language); // javascript translation

		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['facade'] <> 'dashboard'){
			Yii::app()->user->logout();
			$this->redirect(Yii::app()->homeUrl);
		}
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
					'notify', // leggo e creo html per i messaggi
					'updateNews', // aggiorno i messaggi cliccati da 0 a 1 (unread -> read)
					'updateAllNews', // aggiorno tutti i messaggi  da 0 a 1 (unread -> read)
					'checkInstituteAlarm', // verifica se un transazione è stata creata da un istituto
					'checkCountdown', // effettua il conteggio sul countdown
				),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	// 'checkInstituteAlarm', // verifica se un transazione è stata creata da un istituto
	public function actionCheckInstituteAlarm()
	{
		//set_time_limit(0); //imposto il time limit unlimited

		// echo "<pre>".print_r($_POST,true)."</pre>";
		// exit;
		$transactions = CJSON::decode($_POST['transactions']);

		if (isset($transactions[0]))
		  $tokens = $transactions[0];
		else
			$tokens = CJSON::decode($transactions);

			// echo "<pre>".print_r($_POST,true)."</pre>";
			// echo "<pre>".print_r($transactions,true)."</pre>";
			// echo "<pre>".print_r($tokens,true)."</pre>";
			// exit;

		 $institute = Institutes::model()->findByAttributes(['wallet_address'=>$tokens['from_address']]);
		 if ($institute !== null && $institute->max_wait_time>0){
			 echo CJSON::encode([
				 'success'=>true,
				 'data'=>array(
					'id'=>time(), // necessario per salvataggio in indexedDB
				 	'finish_time'=>time() + $institute->max_wait_time * 60,
				 	'message'=>$institute->max_wait_message,
				 	'url'=>Yii::app()->createUrl('backend/checkCountdown')
				)
			 ],true);

		 }else{
			 echo CJSON::encode(['success'=>false],true);
		 }
	}

	public function actionCheckCountdown()
	{
		if(time() >= $_POST['finish_time']){
			$save = new Save;
			$save->WriteLog('bolt','backend','sw countdown',"Start: Send ALERT");
			//QUINDI INVIO UN MESSAGGIO DI NOTIFICA
			$notification = array(
			    'type_notification' => 'alarm',
			    'id_user' => Yii::app()->user->objUser['id_user'],
			    'id_tocheck' => 0,
			    'status' => 'failed',
			    'description' => $_POST['message'],
			    'url' => '#',
			    'timestamp' => time(),
			    'price' => 0,
			    'deleted' => 0,
			);
			Push::Send($save->Notification($notification),'bolt');
			$save->WriteLog('bolt','backend','sw countdown',"End: Send ALERT");
			echo CJSON::encode([
				'success'=>true,
				'openUrl'=>Yii::app()->createUrl('wallet/index')
			],true);
		}else{
			sleep(1);
			echo CJSON::encode([
				'success'=>false,
				'time'=>time()-$_POST['finish_time'],
			],true);
		}
	}

	// aggiorna tutte le notifiche in "letta"
	// update all rows
	public function actionUpdateAllNews(){
		$updateAll = Yii::app()->db->createCommand(
    					"UPDATE np_notifications_readers nr
        				SET nr.alreadyread = 1
        				WHERE nr.id_user = " . Yii::app()->user->objUser['id_user'] . ";"
            		)->execute();

		 //
		 // echo "<pre>".print_r($updateAll,true)."</pre>";
		 // exit;
		echo CJSON::encode(['success'=>true],true);
	}



  // aggiorna la notifica in "letta"
	public function actionUpdateNews(){
		$model = Notifications_readers::model()->findByAttributes([
			'id_user'=> Yii::app()->user->objUser['id_user'],
			'id_notification' => $_POST['id_notification'],
		]);
		if (null !== $model){
			$model->alreadyread = 1;
			$model->update();
		}
		echo CJSON::encode(['success'=>true],true);
	}
	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionNotify()
	{
		 // echo "<pre>".print_r($_POST,true)."</pre>";
		 // exit;
		$criteria = new CDbCriteria();
		$criteria->compare('id_user',Yii::app()->user->objUser['id_user'],false);

		$news = Notifications_readers::model()->orderById()->findAll($criteria);

		$response['countedRead'] = 0;
		$response['countedUnread'] = 0;
		$response['htmlTitle'] = '';
		$response['htmlContent'] = ''; // ex content
		$response['playSound'] = false;
		$response['playAlarm'] = false;

		foreach ($news as $key => $item) {
			($item->alreadyread == 0 ? $response['countedUnread'] ++ : $response['countedRead'] ++);
		}

		$x=1;
		foreach ($news as $key => $item) {
			// echo "<pre>".print_r($notify,true)."</pre>";
			// exit;

			if ($x == 1){
				$response['htmlTitle'] .= '<div class="notifi__title">';
				if ($response['countedUnread']>0){
					$response['htmlTitle'] .= '<p>' . Yii::t('lang','You have {n} unread message.|You have {n} unread messages.',$response['countedUnread']) . '</p>';
				}else{
					$response['htmlTitle'] .= '<p>' . Yii::t('lang','You have read all messages.') . '</p>';
				}
				$response['htmlTitle'] .= '</div>';
			}
			// Leggo la notifica tramite key
			$notify = Notifications::model()->findByPk($item->id_notification);
			$notifi__icon = Notifi::Icon($notify->type_notification);
			$notifi__color = Notifi::Color($notify->status);

			// verifico che sia un allarme
			if ($notify->type_notification == 'alarm' && $item->alreadyread == 0)
				$response['playAlarm'] = true;

			$response['htmlContent'] .= '
				<a href="'.htmlentities($notify->url).'" id="news_'.$notify->id_notification.'">
					<div class="notifi__item">
						<div class="'.$notifi__color.' img-cir img-40">
							<i class="'.$notifi__icon.'"></i>
						</div>
						<div class="content">
							<div onclick="backend.openEnvelope('.$notify->id_notification.');" >';
								if ($item->alreadyread == 0){
									$response['htmlContent'] .= '<p style="font-weight:bold;">';
								}else{
									$response['htmlContent'] .= '<p>';
								}

								$response['htmlContent'] .= WebApp::translateMsg($notify->description);
								$response['htmlContent'] .= '</p>';

								// se il tipo notifica è help o contact ovviamente non mostro il prezzo della transazione
								if ($notify->type_notification <> 'help'
										&& $notify->type_notification <> 'contact'
										&& $notify->type_notification <> 'alarm'
								){
									$response['htmlContent'] .= '<p>'.$notify->price.'</p>';
									//VERIFICO QUESTE ULTIME 3 TRANSAZIONI PER AGGIORNARE IN REAL-TIME LO STATO (IN CASO CI SI TROVA SULLA PAGINA TRANSACTIONS)
									$response['status'][$notify->id_tocheck] = $notify->status;
								}
								$response['htmlContent'] .= '
								<span class="date text-primary">'.WebApp::timeToLocalDate($notify->timestamp).'</span>
							</div>
						</div>
					</div>
				</a>
			';


			$x++;
			if ($x>3)
				break;
		}
		if ($response['countedRead'] == 0 && $response['countedUnread'] == 0){
			$response['htmlContent'] .= '<div class="notifi__title">';
			$response['htmlContent'] .= '<p>' . Yii::t('lang','You have no messages to read.') . '</p>';
			$response['htmlContent'] .= '</div>';
		}else{
			$response['htmlContent'] .= '
				<div class="notifi__footer">
					<a id="seeAllMessages" onclick="backend.openAllEnvelopes();" href="'.htmlentities(Yii::app()->createUrl('messages/index')).'">'.Yii::t('lang','See all messages').'</a>
				</div>
			';
		}
		echo CJSON::encode($response,true);
	}
}
