<?php

/**
 * WalletForm class.
 */
class WalletTokenForm extends CFormModel
{
	public $from;
	public $to;
	public $amount;
	public $memo;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			// username and password are required
			array('from, to, amount', 'required'),
			array('amount', 'numerical', 'integerOnly'=>true),
			array('memo', 'length', 'max'=>300),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'from'=>Yii::t('model','from'),
			'to'=>Yii::t('model','to'),
			'amount'=>Yii::t('model','Amount'),
			'memo' => Yii::t('model','Message'),
		);
	}

}
