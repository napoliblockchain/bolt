<?php
Yii::app()->language = ( isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'it' );
Yii::app()->sourceLanguage = ( isset($_COOKIE['langSource']) ? $_COOKIE['langSource'] : 'it_it' );
new JsTrans('js',Yii::app()->language); // javascript translation

$urlCheckTxpool = Yii::app()->createUrl('wallet/checkTxpool'); //check transaction status
$urlCheckAddress = Yii::app()->createUrl('wallet/checkAddress'); //check address is valid
$urlEstimateGas = Yii::app()->createUrl('wallet/estimateGas');//check Gas
$erc20_Send = Yii::app()->createUrl('walletERC20/send'); //send Ethereum or ERC20

$myWalletScript = <<<JS

paginaweb = 'wallet';
//console.log('paginaweb',paginaweb);

var ajax_loader_url = 'css/images/loading.gif';
var urltxFound = '{$urlCheckTxpool}';
var prefix = 'erc20';
var totale = 0;
var gasPrice = 0;

var maxRepeat = 0; //max repeat for checking balance

var countDecimals = function(value) {
	// console.log('[countDecimals]',Math.floor(value),value);
	if (Math.floor(value) != value)
		return value.toString().split(".")[1].length || 0;
	return 0;
}


//funzione check dati Send token
//$(function(){

	$("#tokenAvanti").click(function(){
		var flagError = false;
		$('#WalletForm_amount_em_').text('');
		$('#WalletForm_to_em_').text('');

		if ($("#WalletTokenForm_amount").val() == 'undefined'){
			$('#WalletForm_amount_em_').show();
			$('#WalletForm_amount_em_').text(Yii.t('js','Invalid amount!'));
			flagError = true;
		}else{
			$('#WalletForm_to_em_').hide();
		}

		if ($("#WalletTokenForm_amount").val() <=0){
			$('#WalletForm_amount_em_').show();
			$('#WalletForm_amount_em_').text(Yii.t('js','Invalid amount!'));
			flagError = true; //IN TEST NON CONTROLLO L'INPUT
		}else{
			$('#WalletForm_to_em_').hide();
		}

		if (countDecimals($("#WalletTokenForm_amount").val()) >2){
			$('#WalletForm_amount_em_').show();
			$('#WalletForm_amount_em_').text(Yii.t('js','Use a maximum of 2 decimal places.'));
			flagError = true; //IN TEST NON CONTROLLO L'INPUT
		}else{
			$('#WalletForm_to_em_').hide();
		}

		if (eval($("#WalletTokenForm_amount").val()) > eval($("#balance-erc20").val())){
			$('#WalletForm_amount_em_').show();
			$('#WalletForm_amount_em_').text(Yii.t('js','Amount is higher than Balance.'));
			flagError = true; //IN TEST NON CONTROLLO L'INPUT
		}else{
			$('#WalletForm_to_em_').hide();
		}

		if ($("#WalletTokenForm_to").val() == ''){
			$('#WalletForm_to_em_').show();
			$('#WalletForm_to_em_').text(Yii.t('js','Recipient address not entered.'));
			flagError = true;
		}else{
			var isAddressChecked = false;
			//check if response is in cache
			if ('indexedDB' in window) {
				readAllData('np_checkaddress')
					.then(function(data) {
				  	console.log('Fetching addresses validation indexedDB', data);
					for (var dt of data) {
						console.log('wallet address value',$('#WalletTokenForm_to').val());
						console.log('wallet address indexed',dt.id);
						if (dt.id == $('#WalletTokenForm_to').val()){
							if (dt.response != true){
								$('#WalletForm_to_em_').show();
								$('#WalletForm_to_em_').text(Yii.t('js','Wrong destination address.'));
								flagError = true;
							}else{
								isAddressChecked = true;
								$('#WalletForm_to_em_').hide();
							}
						}
					}
				});
			}
			if (!isAddressChecked){
				// ALTRIMENTI PROCEDURA ON LINE STANDARD
				$.ajax({
					url:'{$urlCheckAddress}',
					type: "POST",
					data:{
						'to'	: $('#WalletTokenForm_to').val(),
					},
					dataType: "json",
					success:function(data){
						console.log('checking address by web',data);
						if (data.response===true){
							$('#WalletForm_to_em_').hide();
							prosegui();
						}else{
							$('#WalletForm_to_em_').show();
							$('#WalletForm_to_em_').text(Yii.t('js','Wrong destination address.'));
							flagError = true;
						}
					},
					error: function(j){
						var json = jQuery.parseJSON(j.responseText);
						$('#WalletForm_to_em_').show();
						$('#WalletForm_to_em_').text(Yii.t('js','Unable to verify destination address.'));
						flagError = true;
					}
				});
			}
		}


		function prosegui(){
			//se ci sono errori di input non proseguo
			if (flagError){
				return false;
			}else{
				//calcolo il gas dal web
				$.ajax({
					url:'{$urlEstimateGas}',
					type: "POST",
					beforeSend: function() {
						$('#tokenAvanti').hide();
						$('#tokenAvanti').after('<div class="bitpay-pairing__loading"><center><img width=20 src="'+ajax_loader_url+'" alt="loading..."></center></div>');
					},
					data:{
						'from'		: $('#WalletTokenForm_from').val(),
						'to'		: $('#WalletTokenForm_to').val(),
						'amount'	: $('#WalletTokenForm_amount').val(),
					},
					dataType: "json",
					success:function(data){
						console.log('Fetched gas from web', data);
						//return;
						//$('.bitpay-pairing__loading').remove();
						//$('#tokenAvanti').show();

				   		$('#gasPrice').val(data.gasPrice);
						gasPrice = data.gasPrice;

						var totale = $("#balance-erc20").val()-$("#WalletTokenForm_amount").val();
						$('#amount').text($("#WalletTokenForm_amount").val());
						$('#totale').text( totale.toFixed(2) );
						showProsegui();
					},
					error: function(j){
						$('#tokenAvanti').show();
						console.log(j);
					}
				});
			}
		}

	});

	/*
	 * Funzione che mostra il gas
	 */
	function showProsegui(){
		$('#tokenAvanti').show();
		$('.bitpay-pairing__loading').remove();

		$('#scrollmodalGas').modal('show');
		$('#scrollmodalInvia').modal('hide');
	}


	// chiudo la finestra dopo la conferma di invio token e
	// aggiorno i dati allo stato inziale
	function updateconfirmMask(){
		$('#scrollmodalGas').modal('hide');
		$('input[id=gassing-value]').attr('checked', false);
		//$('#gasPrice').text('');
		$('#errorMessageOnSend').html('');
		$(".sufee-alert").hide().removeClass( "alert alert-warning " );
		$('#tokenConfirmOk').hide();
		$('#tokenConfirm').show();
		$('#WalletTokenForm_to').val('');
		$('#WalletTokenForm_amount').val('');
		$('#WalletTokenForm_memo').val('');
	}

	/*
	 * Pulsante INVIO TOKEN/ETH
	 * - con questa funzione è possibile inviare Token ed Ethereum
	 * - Al click del pulsante "Conferma" parte in background la creazione della transazione
	 */
	$("button[name='tokenConfirm']").off('dblclick'); //dovrebbe disabilitare il doppio click
	$("button[name='tokenConfirm']").on('click dblclick',function(e){
		$('#tokenConfirm').hide();
		$('#tokenConfirm').after('<div class="bitpay-pairing__loading"><center><img width=20 src="'+ajax_loader_url+'" alt="loading..."></center></div>');
		/*  Prevents default behaviour  */
		e.preventDefault();
		/*  Prevents event bubbling  */
		e.stopPropagation();

		url = '{$erc20_Send}';

		my_wallet = $('#WalletTokenForm_from').val();

		var post = {
			id		: new Date().toISOString(), // id of indexedDB
			url		: url, //url send Ethereum or ERC20
			from	: $('#WalletTokenForm_from').val(),
			to		: $('#WalletTokenForm_to').val(),
			amount	: $('#WalletTokenForm_amount').val(),
			gas		: $('#gasPrice').val(), //$('#gasPrice').text(),
			memo 	: $('#WalletTokenForm_memo').val(),
			prv_key : null,
			prv_pas : null,
		};
		console.log('post senza chiave',post);

		// USO IL SERVICE WORKER
		if ('serviceWorker' in navigator && 'SyncManager' in window){
			navigator.serviceWorker.ready
				.then(function(sw) {
					var serWork = sw; // firefox fix
					//leggo la priv_key dallo storage
					var prv_key = null;
					readAllData('wallet')
						.then(function(data) {
						  	if (typeof data[0] !== 'undefined') {
								post.prv_key = data[0].prv_php;
								post.prv_pas = data[0].prv_pas;
								console.log('post con chiave',post);
								//return;
								writeData('sync-send-'+prefix, post)
									.then(function() {
										console.log('Registered sync-send equest in indexedDB', post);
										return serWork.sync.register('sync-send-'+prefix);
									})
									.then(function() {
										eth.isReadySent(prefix);
										updateconfirmMask();
									})
									.catch(function(err) {
										console.log(err);
									});
							} else {
								console.log('Chiave privata non trovata!');
								return;
							}
						})
				});
		} else {
			$.ajax({
				url: url,
				type: "POST",
				data:{
					'from'		: $('#WalletTokenForm_from').val(),
					'to'		: $('#WalletTokenForm_to').val(),
					'amount'	: $('#WalletTokenForm_amount').val(),
					'gas'		: $('#gasPrice').val(),
					'prv_key' : 'null',
					'prv_pas' : 'null',
				},
				dataType: "json",
				success:function(data){

					//console.log('send w/o sw data',data);
					//if (data.error){
						$('#tokenConfirm').show();
						$('.bitpay-pairing__loading').remove();
						$('.sufee-alert').show().addClass( "alert alert-warning" );
						$('#errorMessageOnSend').html('<small>'+data.message+'</small>');
						$('#errorMessage').html('<small>'+data.message+'</small>');
						//updateconfirmMask();
						return false;
					//}else{
						// senza sw non funziona perchè mancano le chiavi del wallet !!
						// eth.addNewRow(data);
						// eth.txFound	(data.id_token);
					//}
				},
				error: function(j){
					console.log(j);
				}
			});
		}
	});


	//al click del pulsante su balance, mostra il balance del GAS
	// $("div[id='btnBalanceErc20']").click(function(){
	// 	$('#btnBalanceErc20').addClass('animationBalanceOut');
	// 	$('#btnBalanceErc20').removeClass('animationBalanceIn');
	// 	$('#btnBalanceEth').show();
	//
	// 	$('#btnBalanceEth').addClass('animationBalanceIn');
	// 	$('#btnBalanceEth').removeClass('animationBalanceOut');
	// 	$('#btnBalanceErc20').hide();
	// });

	//al click del pulsante del GAS, mostra il balance
	// $("div[id='btnBalanceEth']").click(function(){
	// 	$('#btnBalanceEth').addClass('animationBalanceOut');
	// 	$('#btnBalanceEth').removeClass('animationBalanceIn');
	// 	$('#btnBalanceErc20').show();
	//
	// 	$('#btnBalanceErc20').addClass('animationBalanceIn');
	// 	$('#btnBalanceErc20').removeClass('animationBalanceOut');
	// 	$('#btnBalanceEth').hide();
	// });


	//al click sull'indirizzo token in RICEVI lo copia negli appunti
	$(".copyonClickAddress").click(function(){
		if (!navigator.clipboard) {
		  	fallbackCopyTextToClipboard();
		  	return;
		}
		navigator.clipboard.writeText($('#inputcopyWalletAddress').val()).then(function() {
		  	//console.log('Async: Copying to clipboard was successful!');
			$('#copyAddressModal').modal('show');
		}, function(err) {
		  	//console.error('Async: Could not copy text: ', err);
		});
	});

	//nel caso in cui non funzioni il navigator.clipboard, utilizzo java standard
	function fallbackCopyTextToClipboard() {
		var textArea = document.createElement("textarea");
		textArea.value = $('#inputcopyWalletAddress').val();
		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();

		try {
			var successful = document.execCommand('copy');
			var msg = successful ? 'successful' : 'unsuccessful';
			//console.log('Fallback: Copying text command was ' + msg);
			$('#copyAddressModal').modal('show');
		} catch (err) {
			//console.error('Fallback: Oops, unable to copy', err);
		}

		document.body.removeChild(textArea);
	}




	// funziona che seleziona il gas da aggiungere alla transazione
	$("input[id='gassing-value']").click(function(){
		//set initial state.
	 	$('#gassing-value').val(this.checked);

	 	$('#gassing-value').change(function() {
			if(this.checked) {
				// var returnVal = confirm("Are you sure?");
				// $(this).prop("checked", returnVal);
				$('input[id=gassing-value]').attr('checked', true);
				$('#gassing-text').html(Yii.t('js','Fast'));
				// TODO: invece di calcolo a mano
				// va fatta una ricerca su blockchain
				$('#gasPrice').text((gasPrice*2.24).toFixed(5));
			}else{
				 $('input[id=gassing-value]').attr('checked', false);
				 $('#gassing-text').html(Yii.t('js','Standard'));
				 $('#gasPrice').text(gasPrice.toFixed(5));
			}
			$('#gassing-value').val(this.checked);
		});
	});



JS;
Yii::app()->clientScript->registerScript('myWalletScript', $myWalletScript);
