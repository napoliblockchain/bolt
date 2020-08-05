<?php

class UsersController extends Controller
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
				'actions'=>array(
					'index', // mostra elenco soci
					'view', //visualizza dettagli socio
					'create', //crea manualmente un socio
					'update', //modifica socio
					'delete', //elimina socio
					//'changepwd', //fa il cambio della password
					'resetpwd', //resetta la password del socio (admin)
					'2fa', //abilita il 2fa
					'2faRemove', //rimuove il 2fa
					'print', //stampa lista soci
					'export',//exporta in excel lista soci
					'saveSubscription', //salva lo sottoscrizinoe dell'user per le notifiche push
				),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		//$settings = Settings::loadUser(crypt::Decrypt($id));
		$social = Socialusers::model()->findByAttributes(['id_user'=>crypt::Decrypt($id)]);
		if (null === $social){
			$social = new Socialusers;
		}

		//carico il wallet selezionato nei settings
		$settings=Settings::loadUser(Yii::app()->user->objUser['id_user']);
		if (!(isset($settings->id_wallet))){
			$wallet_address = '0x0000000000000000000000000000000000000000';
		}else{
			$wallet = Wallets::model()->findByPk($settings->id_wallet);
			$wallet_address = $wallet->wallet_address;
		}

		$this->render('view',array(
			'model'=>$this->loadModel(crypt::Decrypt($id)),
			'social'=>$social,
			'from_address'=>$wallet_address, // indirizzo del wallet dell'utente
		));
	}



	/**
	 * Effettua il reset della password dell'utente selezionato e la invia via mail.
	 * @param integer $id the ID dell'UTENTE
	 */
	public function actionResetpwd()
	{
		$id = $_POST['id'];
		$users=Users::model()->findByPk(crypt::Decrypt($id));
		$users->activation_code = md5(Utils::passwordGenerator()); //creo un nuovo activation_code
		$users->save();
		#$activation_code = crypt::Encrypt($users->activation_code.','.$id);
		#echo $activation_code;
		NMail::SendMail('recovery',crypt::Encrypt($users->id_user),$users->email,'Password0',$users->activation_code);
		$return['txt'] = Yii::t('lang','Sent!');
		echo CJSON::encode($return);
	}


	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		//$settings = Settings::loadUser(crypt::Decrypt($id));
		$social = Socialusers::model()->findByAttributes(['id_user'=>crypt::Decrypt($id)]);
		if (null === $social){
			$social = new Socialusers;
		}

		$model=$this->loadModel(crypt::Decrypt($id));
		// echo "<pre>".print_r($_POST,true)."</pre>";
		// exit;

		if(isset($_POST['Socialusers']))
		{
			$social->attributes=$_POST['Socialusers'];
			// echo "<pre>".print_r($social->attributes,true)."</pre>";
			// exit;

			if($social->save()){
				$save = new Save;
				$save->WriteLog('bolt','users','update','User updated ['.$social->email.'] by '.Yii::app()->user->objUser['email']);
				$this->redirect(array('view','id'=>$id));
			}
		}
		//carico il wallet selezionato nei settings
		$settings = Settings::loadUser(Yii::app()->user->objUser['id_user']);
		if (empty($settings->id_wallet)){
			$from_address = '0x0000000000000000000000000000000000000000';
		}else{
			$wallet = Wallets::model()->findByPk($settings->id_wallet);
			$from_address = $wallet->wallet_address;
		}

		$this->render('update',array(
			'model'=>$model,
				'from_address'=>$from_address,
					'social'=>$social,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function action2fa($id)
	{
		$model=$this->loadModel(crypt::Decrypt($id));

		if(isset($_POST['Users']))
		{
			$key = CHtml::encode($_POST['Users']['ga_cod']);
			$user  = Users::model()->findByPk(crypt::Decrypt($id));
			$ga = new GoogleAuthenticator();
			$checkResult = $ga->verifyCode($_POST['Users']['ga_secret_key'], $key, 2);    // 2 = 2*30sec clock tolerance

			if ($checkResult)
			{
				$model->ga_secret_key = $_POST['Users']['ga_secret_key'];

				if($model->save())
					$this->redirect(array('settings/index','id'=>crypt::Encrypt($model->id_user)));
			}
		}
		//carico il wallet selezionato nei settings
		$settings = Settings::loadUser(Yii::app()->user->objUser['id_user']);
		if (empty($settings->id_wallet)){
			$from_address = '0x0000000000000000000000000000000000000000';
		}else{
			$wallet = Wallets::model()->findByPk($settings->id_wallet);
			$from_address = $wallet->wallet_address;
		}

		$ga         = new GoogleAuthenticator();
        $secret     = $ga->createSecret();
        $qrCodeUrl  = $ga->getQRCodeGoogleUrl(Yii::app()->name, $secret);

        $this->render('2fa',array(
			'model'=>$model,
			'qrCodeUrl'=>$qrCodeUrl,
			'secret'=>$secret,
			'from_address'=>$from_address, // indirizzo del wallet dell'utente
		));
	}
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function action2faRemove($id)
	{
		$model=$this->loadModel(crypt::Decrypt($id));

		if(isset($_POST['Users']))
		{
			$key = CHtml::encode($_POST['Users']['ga_cod']);
			$user  = Users::model()->findByPk(crypt::Decrypt($id));
			$ga = new GoogleAuthenticator();
			$checkResult = $ga->verifyCode($user->ga_secret_key, $key, 2);    // 2 = 2*30sec clock tolerance

			if ($checkResult)
			{
				$model->ga_secret_key = NULL;
				if($model->save())
					$this->redirect(array('settings/index','id'=>crypt::Encrypt($model->id_user)));
			}
		}

		//carico il wallet selezionato nei settings
		$settings = Settings::loadUser(Yii::app()->user->objUser['id_user']);
		if (empty($settings->id_wallet)){
			$from_address = '0x0000000000000000000000000000000000000000';
		}else{
			$wallet = Wallets::model()->findByPk($settings->id_wallet);
			$from_address = $wallet->wallet_address;
		}

        $this->render('2faRemove',array(
			'model'=>$model,
			'from_address'=>$from_address, // indirizzo del wallet dell'utente
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		//elimino tutte le impostazioni dell'utente
		SettingsUser::model()->deleteAllByAttributes(['id_user' => crypt::Decrypt($id)]);


		$this->loadModel(crypt::Decrypt($id))->delete();

		$save = new Save;
		$save->WriteLog('bolt','users','delete','User ['.crypt::Decrypt($id).'] deleted by '.Yii::app()->user->objUser['email']);
		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}




	/**
	 * Mostra gli utenti approvati e slo quelli ATTVI, cioè che hanno un pagamento valido!
	 */
	public function actionIndex()
	{
		$modelc=new Users('search');
		$modelc->unsetAttributes();

		if(isset($_GET['Users']))
			$modelc->attributes=$_GET['Users'];

		#echo "<pre>".print_r($_GET,true)."</pre>";
		#exit;


		$this->render('index',array(
			'modelc'=>$modelc,
		));



		// $criteria=new CDbCriteria();
		// $criteria->compare('status_activation_code',1,false);
		//
		// //creo la lista degli utenti abilitati
		// $users = Users::model()->findAll($criteria);
		// $reminders = array();
		// $limit = 44;
		// if (isset($_GET['list']) && $_GET['list']=='all')
		// 	$limit=-20000;
		//
		//
		// // per ciascun utente verifico i pagamenti
		// foreach($users as $item) {
		// 	$timelapse = Yii::app()->controller->StatoPagamenti($item->id_user,true);
		// 	if ($timelapse < -$limit){
		// 		$reminders[] = $item;
		// 	}
		// }
		//
		// $dataProvider=new CActiveDataProvider('Users', array(
		// 	'data' => $reminders,
		// 	'sort'=>array(
	    // 		'defaultOrder'=>array(
	    //   			'id_user'=>true
	    // 		)
	  	// 	),
		// ));
		//
		//
		// $this->render('index',array(
		// 	'dataProvider'=>$dataProvider,
		// ));
	}






	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Users the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Users::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Users $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='users-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	/**
	 * esporta in un foglio excel le transazioni
	 */
	public function actionExport()
	{
		$dataProvider=new CActiveDataProvider('Users', array(
			'sort'=>array(
	    		'defaultOrder'=>array(
	      			'id_user'=>false
	    		)
	  		),
		));

		//carico le impostazioni dell'applicazione
		$settings=Settings::load();
		if ($settings === null || empty($settings->gdpr_titolare)){//} || empty($settings->poa_port)){
			echo CJSON::encode(array("error"=>'Errore: I parametri di configurazione non sono stati trovati'));
			exit;
		}

		#echo "<pre>".print_r($transactions, true)."</pre>";
		#exit;
		$Creator = $settings->gdpr_titolare; //"ICT Nucleo Informatico - Napoli";
		$LastModifiedBy = ''; //"Sergio Casizzone";
		$Title = "Office 2007 XLSX Test Document";
		$Subject = "Office 2007 XLSX Test Document";
		$Description = "Estrazione dati per Office 2007 XLSX, generated using PHP classes.";
		$Keywords = "office 2007 openxml php";
		$Category = "export";

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		// Set document properties
		$objPHPExcel->getProperties()->setCreator($Creator)
									 ->setLastModifiedBy($LastModifiedBy)
									 ->setTitle($Title)
									 ->setSubject($Subject)
									 ->setDescription($Description)
									 ->setKeywords($Keywords)
									 ->setCategory($Category);

		// Add header

		$colonne = array('a','b','c','d','e');
		$intestazione = array(
			"#",
			"Tipo Utente",
			"Cognome",
			"Nome",
			"email",
		);

		//creazione foglio excel
		foreach ($colonne as $n => $l){
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($l.'1', $intestazione[$n]);
		}
		$transactions = new CDataProviderIterator($dataProvider);
		$riga = 2;
		foreach($transactions as $item) {
			// Miscellaneous glyphs, UTF-8

			$objPHPExcel->setActiveSheetIndex(0)
			            ->setCellValue('A'.$riga, $item->id_user)
			            ->setCellValue('B'.$riga, UsersType::model()->findByPk($item->id_users_type)->desc)
						->setCellValue('C'.$riga, $item->surname)
						->setCellValue('D'.$riga, $item->name)
						->setCellValue('E'.$riga, $item->email);

			$riga++;
		}

		// Rename worksheet
		$objPHPExcel->getActiveSheet()->setTitle('export');
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		// Redirect output to a client’s web browser (Excel5)
		$time = time();
		$date = date('Y/m/d H:i:s', $time);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$date.'-export.xls"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');
		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	/**
	 * Prints all models.
	 */
	public function actionPrint(){
		//carico i SETTINGS della WebApp
		$settingsWebApp = Settings::load();

		//carico l'estensione pdf
		Yii::import('application.extensions.MYPDF.*');

		// create new PDF document
		$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(Yii::app()->params['adminName']);
		$pdf->SetAuthor(Yii::app()->params['shortName']);
		$pdf->SetTitle("Elenco Utenti");
		$pdf->SetSubject('Elenco Utenti');

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
		//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, myPDF_HEADER_STRING);
		//$pdf->SetHeaderData(Yii::app()->basePath.'../../'.Yii::app()->params['logoAssociazione'], 42, Yii::app()->params['adminName'], Yii::app()->params['indirizzo']);
		$gdpr_address = $settingsWebApp->gdpr_address
			."\n".$settingsWebApp->gdpr_cap
			." - ".$settingsWebApp->gdpr_city
			."\nC.F./P.Iva: ".$settingsWebApp->gdpr_vat;
		$pdf->SetHeaderData(
			Yii::app()->basePath.'../../'.Yii::app()->params['logoAssociazionePrint'],
			26,
			$settingsWebApp->gdpr_titolare,
			$gdpr_address
		);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}
		// ---------------------------------------------------------

		// stabilisco i criteri di ricerca
		$criteria=new CDbCriteria;


		//carico la tabella
		$dataProvider= new CActiveDataProvider('Users', array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>array(
					'id_user'=>true,
				)
			),
		));

		$iterator = new CDataProviderIterator($dataProvider);
		$x = $dataProvider->totalItemCount;
		foreach($iterator as $data) {
			$loadData[$x][] = $x;
			$loadData[$x][] = UsersType::model()->findByPk($data->id_users_type)->desc;

			$loadData[$x][] = $data->surname.' '.$data->name;
			$loadData[$x][] = $data->email;

			//$loadData[$x][] = str_replace("&nbsp;"," ",strip_tags(Yii::app()->controller->StatoPagamenti($data->id_user)));

			$x--;
		}
		// echo "<pre>".print_r($loadData, true)."</pre>";
		// exit;



		$header['head'] = array('#', 'Tipo', 'Nominativo', 'email');
		$header['title'] = 'Lista Utenti';

		// print colored table
		$pdf->ColoredTable($header, $loadData, 'soci');
		// reset pointer to the last page
		$pdf->lastPage();

		//Close and output PDF document
		ob_end_clean();

		//Close and output PDF document
		$pdf->Output('users.pdf', 'I');
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
		$Criteria->compare('type','bolt', false);

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
			$vapid->type = 'bolt'; //definisco il tipo di sottoscrizione

			if (!$vapid->save()){
				echo 'Cannot save subscription on server!';
				exit;//
			}
			// echo 'Subscription saved on server!';
		}else{
			//delete
			$iterator = new CDataProviderIterator($vapidProvider);
			foreach($iterator as $data) {
				//echo '<pre>'.print_r($data->id_subscription,true).'</pre>';
				#exit;
				$vapid=PushSubscriptions::model()->findByPk($data->id_subscription)->delete();

				// if($vapid!==null)
				// 	$vapid->delete();
			}
			// echo 'Subscriptions deleted on server!';
		}
	}

}
