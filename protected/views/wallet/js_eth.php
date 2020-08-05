<?php
Yii::app()->language = ( isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'it' );
Yii::app()->sourceLanguage = ( isset($_COOKIE['langSource']) ? $_COOKIE['langSource'] : 'it_it' );
new JsTrans('js',Yii::app()->language); // javascript translation

// $urlCheckAddress = Yii::app()->createUrl('wallet/checkAddress'); //check address is valid
$eth_GetBalance = Yii::app()->createUrl('walletETH/getBalance'); //get Balance eth
$eth_LoadGAS = Yii::app()->createUrl('walletETH/loadGAS'); //load address with GAS
$erc20_GetBalance = Yii::app()->createUrl('walletERC20/getBalance'); //get Balance eth and ERC20
//$urlEstimateGas = Yii::app()->createUrl('wallet/estimateGas');//check Gas
//$erc20_Send = Yii::app()->createUrl('walletERC20/send'); //send Ethereum or ERC20
// $cryptURL = Yii::app()->createUrl('wallet/crypt');// crypta da js
//$saveAddress = Yii::app()->createUrl('wallet/saveAddress'); //salva l'indirizzo generato
$urlCheckTxpool = Yii::app()->createUrl('walletERC20/checkTxpool'); //check transaction status

// $urlReceive = Yii::app()->createUrl('walletETH/receive'); //receive
//$changeQrCode = Yii::app()->createUrl('wallet/changeQrCode'); //check transaction status



$myEthScript = <<<JS
var ajax_loader_url = 'css/images/loading.gif';
var urltxFound = '{$urlCheckTxpool}';

var maxRepeat = 0; //max repeat for checking balance

var erc20;
var eth;


// funzioni eth. e erc20.
$(function(){

	eth = {
		loadGas: function(my_address){
			$.ajax({
				url:'{$eth_LoadGAS}',
				type: "POST",
				data: {'to': my_address},
				dataType: "json",
				success:function(data){
					// $('.balance-eth').text(data.balance);
					// $('#balance-eth').val(data.balance);
					console.log('[GAS loaded] ',data.balance);
				},
				error: function(j){
					console.log('error');
				}
			});

		},
		Balance: function(my_address){
			// altrimenti seguo lo standard
			$.ajax({
				url:'{$eth_GetBalance}',
				type: "POST",
				data: {'my_address': my_address},
				dataType: "json",
				success:function(data){
					console.log('[eth balance for testing]',data);
					if (data.balance >= 1){
						console.log('[GAS is plenty] eth:',data.balance);
					}else{
						console.log('[GAS needs to load] eth:',data.balance);
						eth.loadGas(my_address);
					}
				},
				error: function(j){
					console.log('error');
				}
			});
		},
		// controlla se il db di ricezione indexedDB è stato preparato dal service worker
		isReadySent: function(suffix){
			readAllData('np-send-'+suffix)
				.then(function(data) {
					if (typeof data[0] !== 'undefined') {
						if (data[0].error){
							$('.sufee-alert').show().addClass( "alert alert-warning" );
							$('#errorMessageOnSend').html('<small>'+data[0].error+'</small>');
							$('#errorMessage').html('<small>'+data[0].error+'</small>');
						}else{
							$('#tokenConfirm').show();
							$('.bitpay-pairing__loading').remove();
							$('#scrollmodalGas').modal('hide');
							for (var dt of data) {
								eth.addNewRow(dt);
								eth.txFound(dt.id_token);
							}
						}
						clearAllData('np-send-'+suffix);
					} else {
						setTimeout(function(){ eth.isReadySent(suffix) }, 500);
					}
				});
		},
		// controlla se il db di ricezione indexedDB è stato preparato dal service worker
		// isReadyReceived: function(){
		// 	readAllData('np_receive')
		// 		.then(function(data) {
		// 		  	if (typeof data[0] !== 'undefined') {
		// 				$('#tokenReceiveOk').show();
		// 				$('.bitpay-pairing__loading').remove();
		// 				for (var dt of data) {
		// 					eth.addNewRow(dt);
		// 					eth.txFound(dt.id_token);
		// 				}
		// 				clearAllData('np_receive');
		// 			} else {
		// 				setTimeout(function(){ eth.isReadyReceived() }, 500);
		// 			}
		// 		});
		// },

		//aggiunge una riga alla tabella transazioni
		//lo status in questo caso, dovrebbe essere sempre new perchè abbiamo
		//intercettato l'invio del token. La traduzione avviene tramite PHP
		// in walleterc20/send
		addNewRow: function(data){
			$("<tr class='even animazione'>"
        +"<td><i class='zmdi zmdi-star-outline'></i></td>"
			  +"<td><a href='"+data.url+"'>"+data.data+"</a></td>"
			  +"<td class='desc __sending_now-"+data.id_token+"'><a href='"+data.url+"'>"+data.status+"</a></td>"
			  +"<td style='text-align:center;' class='__sending_now_price-"+data.id_token+"'>"+data.token_price+"</td>"
        +"<td class='mobile-not-show'>"+data.to_address+"</td>"
        +"<td style='width:50px;'><i class='fa fa-unlock ' style='color:red;'></i><span style='font-size:0.8em;'>1</span></td>"
        +"</tr>").prependTo("#tokens-grid table.items > tbody");

			  $('.animazione').addClass("animationTransaction");
		},

		//verifica che ci siano transaz. in indexedDB
		isTxInIndexedDB: function(id_token){
			readAllData('np-txPool')
				.then(function(data) {
					console.log('[isTxInIndexedDB] reading np-txPool',data);
				  	if (typeof data[0] !== 'undefined') {
						clearAllData('np-txPool');
						eth.responseTxPool(data[0]);
					}
					else {
						maxRepeat ++;
						console.log('[isTxInIndexedDB] Repeat search',maxRepeat);
						if (maxRepeat <7200)
							setTimeout(function(){ eth.isTxInIndexedDB(id_token) }, 50);
					}
				});
		},
		//analizza la risposta da txpool
		responseTxPool: function(data){
			console.log('[ResponseTxPool]: (check if exist id_token) ',data);
			if (data.status !== 'new'){
				//$( ".__sending_now-"+data.id_token+' a span').text(Yii.t('js',data.status)).fadeIn(1600);
				$( ".__sending_now-"+data.id_token+' a span').html(data.status_wlink).fadeIn(1600);
				$( ".__sending_now-"+data.id_token+' a span').removeClass( "incorso" );
				$( ".__sending_now-"+data.id_token+' a span').removeClass( "btn-outline-seconary" );
				$( ".__sending_now-"+data.id_token+' a span').addClass( "btn btn-outline-success" );

				$( ".__sending_now_price-"+data.id_token ).html(data.token_price_wsymbol);

				// PREDISPONGO LA NOTIFICA CHE VERRA' MOSTRATA SOLO SE SUPPORTATA DAL BROWSER
				var options = {
					title: Yii.t('js','[Bolt] - New message'),
					body: Yii.t('js','Transaction status with a price of {price} is now completed. Do you want to view it?', {price:data.token_price}),
					icon: 'src/images/icons/app-icon-96x96.png',
					vibrate: [100, 50, 100, 50, 100 ], //in milliseconds vibra, pausa, vibra, ecc.ecc.
					badge: 'src/images/icons/app-icon-96x96.png', //solo per android è l'icona della notifica
					tag: 'confirm-notification', //tag univoco per le notifiche.
					renotify: true, //connseeo a tag. se è true notifica di nuovo
					data: {
					   openUrl: data.openUrl,
					},
					actions: [
						{action: 'openUrl', title: Yii.t('js','Yes'), icon: 'css/images/chk_on.png'},
						{action: 'close', title: Yii.t('js','No'), icon: 'css/images/chk_off.png'},
					],
				};
				displayNotification(options);

				setTimeout(function(){ erc20.Balance('{$from_address}') }, 1000);
			}else{
				setTimeout(function() {
					eth.txFound(data.id_token);
				}, 1000);
			}
		},

		// controlla se la transazione è passata dallo stato 'new' ad un altro stato.
		// la transazione viene modificata nel controller commands/Receive o send e verifica lo stato nel blocco
		txFound: function(id_token){
			if ('serviceWorker' in navigator && 'SyncManager' in window){
				navigator.serviceWorker.ready
					.then(function(sw) {
						var post = {
							id: new Date().toISOString(), // id of indexedDB
							url		: urltxFound, //urlmy of getBalance
							id_token: id_token,
						};
						writeData('sync-txPool', post)
							.then(function() {
								return sw.sync.register('sync-txPool');
							})
							.then(function() {
								// check if transaction is in pool
								maxRepeat = 0;
								eth.isTxInIndexedDB(id_token);
							})
							.catch(function(err) {
								console.log(err);
						});
					});
			} else {
				// altrimenti seguo lo standard
				$.ajax(urltxFound,
				{
					type: "POST",
					data: {
						'id_token': id_token,
						'my_wallet': my_wallet,
					},
					dataType: 'json',
					success: function(data) {
						eth.responseTxPool(data);
					},
					error: function(j) {
						console.log(j);
					}
				});
			}
		}
	}

	erc20 = {
		Balance: function(my_address){
			//altrimenti seguo la procedura standard
			$.ajax({
				url:'{$erc20_GetBalance}',
				type: "POST",
				data: {'my_address': my_address},
				dataType: "json",
				beforeSend: function() {
					$('.balance-erc20').hide();
					$('.balance-erc20').after('<div class="__erc20_loading1 float-right"><img width=20 src="'+ajax_loader_url+'" alt="loading..."></div>');
				},
				success:function(data){
					if (data.error){
						$('.__erc20_loading1').remove();
						$('.balance-erc20').show();
						$('.sufee-alert').show().addClass( "alert alert-warning" );
						$('#errorMessage').html('<small>'+data.error+'</small>');
						return false;
					}else{
						//console.log('Fetched erc20 balance from web', data);

						$('.__erc20_loading1').remove();
						$('.balance-erc20').show();
						$('.balance-erc20').text(data.balance);
						//$('.balance-erc20-eur').text(parseFloat(data.eurbalance).toFixed(2));
						$('#balance-erc20').val(data.balance);
						//totalErc20Eth += data.eurbalance;

						//$('.balance-total-erc20-eth').text(parseFloat(totalErc20Eth).toFixed(2));

					}
				},
				error: function(j){
					console.log('error');
				}
			});
		}
	}

}); // FINE FUNCTION()

JS;
Yii::app()->clientScript->registerScript('myEthScript', $myEthScript, CClientScript::POS_END);
