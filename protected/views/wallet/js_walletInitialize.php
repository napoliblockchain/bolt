<?php
$cryptedIdUser = crypt::Encrypt(Yii::app()->user->objUser['id_user']);

$cryptURL = Yii::app()->createUrl('wallet/crypt');// crypta da js
$saveAddress = Yii::app()->createUrl('wallet/saveAddress'); //salva l'indirizzo generato

$targetAddress = (isset($_GET['linkToAvatarAddress']) ?  $_GET['linkToAvatarAddress'] : null);
$targetDestination = ($targetAddress === null ? false : true);

$myWalletInitialize = <<<JS

var targetDestination = '{$targetDestination}';
var targetAddress = '{$targetAddress}';
var cryptedIdUser = '{$cryptedIdUser}';

var seed = null;
var lw = lightwallet;

$(function(){
	/*
	 *
	 * FUNZIONE INIZIALE DI INIZIALIZZAZIONE DEL WALLET
	 *
	*/
	//LEGGO LE INFORMAZIONI DEL WALLET DA IndexedDB
	var isEquel = null;
	var my_address;
	//var isEquel;
	readFromId('wallet',"{$from_address}")
		.then(function(data) {
			console.log('[Wallet IndexedDB]',data);
			// if (typeof data[0] !== 'undefined') {
			if (typeof data[0] !== 'undefined') {
				for (var dt of data) {
					if (null === data.id){
						$('#initializeWallet').modal({backdrop: 'static',keyboard: false});
						break;
					}else{
						var address_1 = new String("{$from_address}");
						var iduser_1 = new String(cryptedIdUser);
						var address_2 = new String(dt.id);
						var iduser_2 = new String(dt.id_user);

						isEquel_1 = JSON.stringify(address_1) === JSON.stringify(address_2);
						console.log('[Wallet isEquel_1]',isEquel_1);
						isEquel_2 = JSON.stringify(iduser_1) === JSON.stringify(iduser_2);
						console.log('[idUSer isEquel_2]',isEquel_2);

						console.log('[Wallet Mysql / IndexedDB]',address_1,address_2);
						console.log('[idUSer Mysql / IndexedDB]',iduser_1,iduser_2);

						isEquel = isEquel_1 * isEquel_2;
						console.log('[isEquel]',isEquel);
					}

					// isEquel = JSON.stringify(address_1) === JSON.stringify(address_2);
					//
					// console.log('[Wallet isEquel]',isEquel);
					// console.log('[Wallet Mysql / IndexedDB]',address_1,address_2);
					if ( isEquel ){
						/*  START 	*/
						my_address = data[0].id;
						console.log('[Wallet Address recuperato]', my_address);
						break;
					}else{
					//	clearAllData('wallet')
						//.then(function(){
							$('#initializeWallet').modal({backdrop: 'static',keyboard: false});
							break;
					//	})
					}
				// }else{
				//
				}
			}else{
				/**
				 * questa funzione permette di scegliere se ripristinare il wallet o crearne uno nuovo
				 */
				$('#initializeWallet').modal({backdrop: 'static',keyboard: false});
			}
		})
		// .then(function() {
		// 	console.log('[then  checkForPIN');
		// 	if (isEquel){
		// 		checkForPIN();
		// 	}
		// })
		.then(function() {
			console.log('[then  saveOnDesktop');
			if (isEquel){
				saveOnDesktop();
			}
		})
		.then(function() {
			console.log('[then  Start is',isEquel);
			if (isEquel){

				backend.checkPin();
				erc20.Balance(my_address);
				eth.Balance(my_address); // carico gas se <= 0

				// da richiamare all'avvio
				blockchain.sync(my_address);
				setTimeout(function(){ backend.checkNotify() }, 500);
				setTimeout(function(){ blockchain.scanForNew(my_address) }, 2000);

				console.log('[Target Destination]',targetDestination);
				// controlla se è stato inviato il _GET target per inviare i
				// token al contatto, quindi avvio la maschera di send direttamente
				if (targetDestination){
					$("#WalletTokenForm_to").val(targetAddress);
					$('#scrollmodalInvia').modal().show();
				}
			}
		})
		.catch(function(err) {
			//se c'è un errore, probabilmente non esiste il db wallet pertanto inizializzo
			if (!isEquel){
				$('#initializeWallet').modal({backdrop: 'static',keyboard: false});
			}
		});




});




/*
 * queste funzioni mostrano le maschere iniziali
*/
$("button[id='CallSecondPageModal']").click(function(){
 	$('#initializeWalletSecond').modal({backdrop: 'static',keyboard: false});
});

$("button[id='CallFirstPageModal']").click(function(){
 	$('#initializeWallet').modal({backdrop: 'static',keyboard: false});
});
$("button[id='CallThirdPageModal']").click(function(){
 	$('#initializeWalletThird').modal({backdrop: 'static',keyboard: false});
});
$("button[id='CallSecondPageModalFromThird']").click(function(){
 	$('#initializeWalletSecond').modal({backdrop: 'static',keyboard: false});
});
$("button[id='CalloldSeedModal']").click(function(){
	$('#oldSeedModal').modal({backdrop: 'static',keyboard: false});
});
$("button[id='newWallet']").click(function(){
	$('#seedModal').modal({backdrop: 'static',keyboard: false});
	newWallet();
});
$("button[id='CallRepeatSeedModal']").click(function(){
	$('#repeatSeedModal').modal({backdrop: 'static',keyboard: false});
});


// verifichiamo l'inserimento del vecchio seed e la password di criptazione
$("button[id='oldSeedConferma']").off('dblclick'); //dovrebbe disabilitare il doppio click
$("button[id='oldSeedConferma']").on('click dblclick',function(e){
	/*  Prevents default behaviour  */
	e.preventDefault();
	/*  Prevents event bubbling  */
	e.stopPropagation();

	var oldHtml = $('#oldSeedConferma').html();
	$('#oldSeedConferma').html('<div class="blockchain-pairing__loading"><center><img width=15 src="'+ajax_loader_url+'" alt="loading..."></center></div>');

	var seed = $('#old_seed').val();
 	if (WordCount(seed) != 12 || !(isSeedValid(seed)) ){
		$('#old_seed_em_').addClass('alert alert-danger');
		$('#old_seed_em_').show().text(Yii.t('js','Invalid Seed!'));
		$('#oldSeedConferma').html(oldHtml);
		return;
	}
	$('#old_seed_em_').removeClass('alert alert-danger');
	$('#old_seed_em_').hide().text('');

	console.log('seed valido');

	// la password viene generata in automatico dal sistema di 32 caratteri
	var password = generateEntropy(32);
	//adesso salviamo in local storage il seed e la password
	initializeVault(password,seed);
});

function saveOnDesktop() {
	if (deferredPrompt) {
		deferredPrompt.prompt();
		deferredPrompt.userChoice.then(function(choiceResult) {
			// console.log('[deferred prompt]',choiceResult.outcome);
			if (choiceResult.outcome === 'dismissed') {
	  			console.log('[deferred prompt] User cancelled installation');
			} else {
	  			console.log('[deferred prompt] User added to home screen');
			}
		});
		deferredPrompt = null;
	}
}

// verifica la validità di un seed
function isSeedValid(seed){
	if (!lw.keystore.isSeedValid(seed))
		return false;
 	else
		return true;
}

/*
 * questa funzione genera il nuovo seed del wallet
 */
function newWallet()
{
	seed = lw.keystore.generateRandomSeed(generateEntropy(Math.floor(Math.random() * 1001)+1000));

	$('#seedText').html(seed);
	$('#seedInput').val(seed);
}

//torno alla schermata principale
$("button[name='cryptIndietro']").click(function(){
	$('#initializeWallet').modal({backdrop: 'static',keyboard: false});
});

//torno alla schermata visualizza seed
$("button[name='confirmIndietro']").click(function(){
	$('#seedModal').modal({backdrop: 'static',keyboard: false});
	$('#repeat_seed_em_').removeClass('alert alert-danger');
	$('#repeat_seed_em_').hide().text('');
});

// verifico validità del seed
$("button[id='cryptConferma']").click(function(){
	var oldHtml = $('#cryptConferma').html();
	$('#cryptConferma').html('<img width=20 src="'+ajax_loader_url+'" alt="'+Yii.t('js','loading...')+'">');

	var seed = $('#seedInput').val();
	var confirm_seed = $('#repeat_seed').val();

	if (WordCount(confirm_seed) != 12 || !(isSeedValid(confirm_seed)) || confirm_seed != seed){
		$('#repeat_seed_em_').addClass('alert alert-danger');
		$('#repeat_seed_em_').show().text(Yii.t('js','Invalid Seed!'));
		$('#cryptConferma').html(oldHtml);
		return;
	}
	$('#repeat_seed_em_').removeClass('alert alert-danger');
	$('#repeat_seed_em_').hide().text('');

	// la password viene generata in automatico dal sistema di 32 caratteri
	var password = generateEntropy(32);

	initializeVault(password,seed);
});

// adesso salviamo in local storage il seed e la password
function initializeVault(password, seed) {
	//prima crypto tramite php la pwd
	$.ajax({
		url:'{$cryptURL}',
		type: "POST",
		data: {
			'pass': password,
			'seed': seed
		},

		dataType: "json",
		success:function(data){
			var pwd_crypted  = data.cryptedpass;
			var seed_crypted  = data.cryptedseed;
			var iduser_crypted  = data.cryptediduser;
			//console.log('vault',password,seed);
			lw.keystore.createVault({
			    password: password,
			    seedPhrase: seed,
			    hdPathString: "m/0'/0'/0'"
				}, function (err, ks) {
				  ks.keyFromPassword(password, function (err, pwDerivedKey) {
		        if (!ks.isDerivedKeyCorrect(pwDerivedKey)) {
		          throw new Error("Incorrect derived key!");
		        }

		        try {
		          ks.generateNewAddress(pwDerivedKey, 1);
		        } catch (err) {
		          console.log(err);
		          console.trace();
		        }
		        var address = ks.getAddresses()[0];
		        var prv_key = ks.exportPrivateKey(address, pwDerivedKey);

						var post = {
							id			: address, // id of indexedDB
							id_user		: iduser_crypted,
							prv_php 	: CryptoJS.AES.encrypt(JSON.stringify(prv_key), password, {format: CryptoJSAesJson}).toString(),
							prv_pas		: pwd_crypted,
						};
						console.log('address and key in post: ', post);

						writeData('wallet', post)
							.then(function() {
								console.log('Saved wallet info in indexedDB', post);
								$('#cryptConferma').html('<img width=20 src="'+ajax_loader_url+'" alt="'+Yii.t('js','loading...')+'">');
							})

							.then(function() {
								//save at mysql a user's wallet address
								$.ajax({
									url:'{$saveAddress}',
									type: "POST",
									data: {'address': address},
									dataType: "json",
									success:function(data){
										console.log('[saving wallet info]',data);
										var post2 = {
											id : new Date().toISOString(), // id of indexedDB
											cryptedseed : seed_crypted,
										}
										writeData('mseed', post2)
										.then(function() {

											setTimeout(function(){ location.reload() }, 250);

										});

									},
									error: function(j){
										console.log('error',j);
									}
								});
							})
							.catch(function(err) {
								console.log(err);
							});
				    });
				});
			// Quindi, chiedo di installare la webapp sulla home del cell
			saveOnDesktop();

		},
		error: function(j){
			console.log('error',j);
		}
	});
}

JS;
Yii::app()->clientScript->registerScript('myWalletInitialize', $myWalletInitialize, CClientScript::POS_END);
