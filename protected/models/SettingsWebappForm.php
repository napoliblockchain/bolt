
<?php

/**
 * This is the model class for table "np_settings".
 *
 * The followings are the available columns in table 'np_settings':
 * @property integer $id_exchanges
 * @property string $denomination
 * @property string $sshhost
 * @property string $sshuser
 * @property string $sshpassword
 *
 */
class SettingsWebappForm extends CFormModel
{
	//BTCPayServer
	//public $BTCPayServerAddress;
	//public $BTCPayServerAddressWebApp;
	public $BPS_Admin_Email;
	public $BPS_Admin_Password;

	//Exchange
	public $id_exchange;
	public $exchange_secret;
	public $exchange_key;
	public $only_for_bitstamp_id;

	//Associazione
	// public $association_percent;
	// public $association_receiving_address;
	// public $quota_iscrizione_socio;
	// public $quota_iscrizione_socioGiuridico;

	//POA TOKEN
	// public $poa_url;
	// public $poa_port;
	// public $poa_expiration;
	// public $poa_contractAddress;
	// public $poa_abi;
	// public $poa_bytecode;
	// public $poa_chainId;
	// public $poa_blockexplorer;

	//sin per pairing con BTCPayServer
	// public $sin;
	// public $token;

	//server host
	public $sshhost;
	public $sshuser;
	public $sshpassword;
	public $rpchost;
	public $rpcport;

	//varie
	public $step;
	public $version;

	//GDPR
	public $gdpr_titolare;
	public $gdpr_vat;
	public $gdpr_address;
    public $gdpr_city;
    public $gdpr_country;
    public $gdpr_cap;
	public $gdpr_telefono;
	public $gdpr_fax;
	public $gdpr_email;
	public $gdpr_dpo_denomination;
	public $gdpr_dpo_email;
	public $gdpr_dpo_telefono;

	//VAPID keys for Push messages
	public $VapidPublic;
	public $VapidSecret;

	//PAYPAL
	// public $PAYPAL_CLIENT_ID;
	// public $PAYPAL_CLIENT_SECRET;
	// public $PAYPAL_MODE;


	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			//array('BTCPayServerAddress', 'required'),
			array('id_exchange, poa_expiration', 'numerical', 'integerOnly'=>true),
			array('quota_iscrizione_socio, quota_iscrizione_socioGiuridico, poa_chainId', 'numerical', 'integerOnly'=>false),
			array('exchange_secret, exchange_key, association_receiving_address, poa_contractAddress', 'length', 'max'=>250),
			array('only_for_bitstamp_id, association_percent, poa_port', 'length', 'max'=>10),
			array('poa_url,BTCPayServerAddress,BTCPayServerAddressWebApp,version, BPS_Admin_Email, BPS_Admin_Password', 'length', 'max'=>50),
			array('sin,token,sshhost,sshuser,sshpassword,rpchost,rpcport,poa_blockexplorer', 'length', 'max'=>1000),
			array('poa_abi,poa_bytecode', 'length', 'max'=>15000),
			array('gdpr_titolare, gdpr_address, gdpr_city, gdpr_country, gdpr_cap, gdpr_dpo_denomination', 'length', 'max'=>250),
			array('gdpr_vat, gdpr_telefono, gdpr_fax, gdpr_email, gdpr_dpo_email, gdpr_dpo_telefono', 'length', 'max'=>50),
			array('VapidPublic,VapidSecret', 'length', 'max'=>150),
			array('PAYPAL_CLIENT_ID,PAYPAL_CLIENT_SECRET,PAYPAL_MODE', 'length', 'max'=>150),
		);
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id_setting' => Yii::t('model','Id Impostazioni'),
			'BTCPayServerAddress' => Yii::t('model','URL di BTCServer Utente'),
			'BTCPayServerAddressWebApp' => Yii::t('model','URL di BTCServer Associazione'),
			'BPS_Admin_Email' => Yii::t('model','Email Amministratore di BTCServer Utente'),
			'BPS_Admin_Password' => Yii::t('model','Password Amministratore di BTCServer Utente'),

			'id_exchange' => Yii::t('model','Id Exchange'),
			'exchange_secret' => Yii::t('model','Chiave Segreta Exchange'),
			'exchange_key' => Yii::t('model','Chiave Pubblica Exchange'),
			'only_for_bitstamp_id'=>Yii::t('model','Bitstamp ID Api'),
			'association_percent'=>Yii::t('model','Percentuale incasso sulle transazioni'),
			'association_receiving_address'=>Yii::t('model','Indirizzo BTC di ricezione'),

			//
			'poa_url'=>Yii::t('model','URL del nodo POA'),
			'poa_port'=>Yii::t('model','Porta del nodo POA'),
			'poa_contractAddress'=>Yii::t('model','Indirizzo dello Smart Contract'),
			'poa_abi'=>Yii::t('model','Smart Contract ABI'),
			'poa_bytecode'=>Yii::t('model','Smart Contract bytecode'),
			'poa_expiration'=>Yii::t('model','Il pagamento scade se l\'ammontare totale non è stato pagato dopo xxx minuti'),
			'poa_chainId'=>Yii::t('model','Chain Id'),
			'poa_blockexplorer'=>Yii::t('model','URL Block Explorer'),

			//
			'version'=>Yii::t('model','Versione applicazione'),
			'quota_iscrizione_socio'=>Yii::t('model','Quota Iscrizione (Persona Fisica)'),
			'quota_iscrizione_socioGiuridico'=>Yii::t('model','Quota Iscrizione (Persona Giuridica)'),
			'sin'=>Yii::t('model','SIN Pairing Associazione'),
			'token'=>Yii::t('model','Token Pairing'),

			'sshhost' => Yii::t('model','Indirizzo tcp/ip Host VPS'),
			'sshuser' => Yii::t('model','Utente ssh'),
			'sshpassword'=>Yii::t('model','Password'),
			'rpchost' => Yii::t('model','Indirizzo tcp/ip Container Electrum'),
			'rpcport' => Yii::t('model','Porta Container Electrum'),

			'gdpr_titolare' =>Yii::t('model','Denominazione'),
			'gdpr_address' =>Yii::t('model','Indirizzo'),
			'gdpr_vat' =>Yii::t('model','Partita Iva'),
			'gdpr_cap' =>Yii::t('model','Cap'),
			'gdpr_city' =>Yii::t('model','Città'),
			'gdpr_country' =>Yii::t('model','Stato'),
			'gdpr_telefono' =>Yii::t('model','Telefono'),
			'gdpr_fax' =>Yii::t('model','Fax'),
			'gdpr_email' => Yii::t('model','e-mail'),
			'gdpr_dpo_denomination' => Yii::t('model','Data Protection Officer (DPO)'),
			'gdpr_dpo_email' => Yii::t('model','DPO email'),
			'gdpr_dpo_telefono' => Yii::t('model','DPO Telefono'),

			'VapidPublic' => Yii::t('model','Chiave pubblica'),
			'VapidSecret' => Yii::t('model','Chiave privata'),

			'PAYPAL_CLIENT_ID' => Yii::t('model','Chiave pubblica'),
			'PAYPAL_CLIENT_SECRET' => Yii::t('model','Chiave privata'),
		);
	}
}
