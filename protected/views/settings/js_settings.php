<?php
$urlRescan = Yii::app()->createUrl('wallet/rescan');
$cryptoURL = Yii::app()->createUrl('wallet/crypt');// crypta da js
$urlSavesubscription = Yii::app()->createUrl('wallet/saveSubscription'); //save subscription for push messages
$urlChangeLanguage = Yii::app()->createUrl('settings/changelanguage');

$settingsWebapp = Settings::load();
$vapidPublicKey = $settingsWebapp->VapidPublic;

$myCreateToken = <<<JS
    var ajax_loader_url = 'css/images/loading.gif';

    readAllData('pin')
        .then(function(pin){
            if (typeof pin[0] !== 'undefined') {
                $('#SettingsUserForm_scadenzaPin').val(pin[0].stop);
                //$('#SettingsUserForm_scadenzaPin').prop('disabled', true);
                //$('#SettingsUserForm_scadenzaPin').addClass('disabled');
                $('#SettingsUserForm_scadenzaPin').hide();
                $('#pinRemoveButtonModal').show();
                $('.masterSeedMessagePinEnabled').show();
            }else{
                $('.masterSeedMessagePinDisabled').show();
            }
        })

    setTimeout(function(){ backend.checkPin() }, 100);
    setTimeout(function(){ backend.checkNotify() }, 500);
    setTimeout(function(){ blockchain.sync('{$wallet_address}') }, 500);


    /*
     * This code checks if service workers and push messaging is supported by the current browser and if it is, it registers our sw.js file.
     */
    const applicationServerPublicKey = '{$vapidPublicKey}';
    const pushButton = document.querySelector('.js-push-btn');
    const pushButtonModal = document.querySelector('.js-push-btn-modal');
    const pushButtonModalText = document.querySelector('.js-push-btn-modal-text');

    let isSubscribed = false;
    let swRegistration = null;

    if ('serviceWorker' in navigator && 'PushManager' in window) {
        console.log('Push is supported');
        navigator.serviceWorker.register('sw.js')
            .then(function(swReg) {
                console.log('Service Worker is registered again');

                swRegistration = swReg;
                initializeUI();
            })
            .catch(function(error) {
                console.error('Service Worker Error', error);
            });
    } else {
        console.warn('Push messaging is not supported');
        pushButtonModalText.textContent = Yii.t('js','Push Not Supported');
    }

    /*
     * check if the user is currently subscribed
     */
    function initializeUI() {
        pushButton.addEventListener('click', function() {
            pushButtonModal.disabled = true;
            if (isSubscribed) {
                unsubscribeUser();
            } else {
                subscribeUser();
            }
        });
        // Set the initial subscription value
        swRegistration.pushManager.getSubscription()
            .then(function(subscription) {
                isSubscribed = !(subscription === null);

                updateSubscriptionOnServer(subscription);

            if (isSubscribed) {
              console.log('User IS subscribed.');
            } else {
              console.log('User is NOT subscribed.');
            }

            updateBtn();
        });

    }
    /*
    * change the text if the user is subscribed or not
    */
    function updateBtn() {
        if (Notification.permission === 'denied') {
           pushButtonModalText.textContent = Yii.t('js','Notifications are locked');
           pushButtonModal.disabled = true;
           updateSubscriptionOnServer(null);
           return;
         }

         if (isSubscribed) {
           pushButtonModalText.textContent = Yii.t('js','Disable');
           $('.js-push-btn-modal').prop('data-target', 'pushDisableModal');
         } else {
           pushButtonModalText.textContent = Yii.t('js','Enable');
            $('.js-push-btn-modal').prop('data-target', 'pushEnableModal');
         }

         pushButtonModal.disabled = false;
   }

    /*
     * SUBSCRIBE A USER
     */
    function subscribeUser() {
        const applicationServerKey = urlBase64ToUint8Array(applicationServerPublicKey);
        swRegistration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: applicationServerKey
        })
        .then(function(subscription) {
            console.log('User is subscribed.');
            updateSubscriptionOnServer(subscription);
            isSubscribed = true;
            updateBtn();
        })
        .catch(function(err) {
            console.log('Failed to subscribe the user: ', err);
            updateBtn();
        });
    }
    /*
    * UNSUBSCRIBE A USER
    */
    function unsubscribeUser() {
      swRegistration.pushManager.getSubscription()
      .then(function(subscription) {
        if (subscription) {
          return subscription.unsubscribe();
        }
      })
      .catch(function(error) {
        console.log('Error unsubscribing', error);
      })
      .then(function() {
        updateSubscriptionOnServer(null);

        console.log('User is unsubscribed.');
        isSubscribed = false;

        updateBtn();
      });
    }

    /*
     *  Send subscription to application server
    */
    function updateSubscriptionOnServer(subscription) {
        if (subscription) {
            sub = JSON.stringify(subscription);
            console.log('Salvo la sottoscrizione',subscription);
        }else{
            sub = JSON.stringify(null);
            console.log('Elimino la sottoscrizione');
        }

        $.ajax({
            url:'{$urlSavesubscription}',
            type: "POST",
            data: sub,
            dataType: "html",
            success:function(res){
                console.log(res);
            },
            error: function(j){
                console.log('ERRORE Update subscription',j);
            }
        });
    }


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


    $(".walletMessage").click(function(){
        $(".walletMessage").hide(3500, function(){
            $('.walletMessage').text('');
        });
    });

    //EFFETTUA il cambio lingua
    var language = document.querySelector('#SettingsUserForm_language');
    language.addEventListener('change', function(){
        $.ajax({
            url:'{$urlChangeLanguage}',
            type: "POST",
            data: {lang: $('#SettingsUserForm_language').val() },
            dataType: "json",
            success:function(data){
                if (data.success){
                    location.href = location.href;
                }
                console.log(data);
            },
            error: function(j){
                console.log(j);
            }
        });
    });


    //EFFETTUA L'OPERAZIONE DI RESCAN DELLA BLOCKCHAIN INSERENDO 0 COME BLOCCO NEI PARAMETRI DEL WALLET
    var rescan = document.querySelector('#rescan');
    rescan.addEventListener('click', function(){
        $.ajax({
            url:'{$urlRescan}',
            type: "POST",
            data: {wallet: '{$wallet_address}'},
            dataType: "json",
            beforeSend: function() {
                $('#rescan').hide();
                $('#rescan').after('<div class="bitpay-pairing__loading float-right"><img width=20 src="'+ajax_loader_url+'" alt="loading..."></div>');
            },
            success:function(data){
                $('.bitpay-pairing__loading').remove();
                //$('#rescan').show();
                $(".show-rescan").show();
            },
            error: function(j){
                console.log(j);
                $('.bitpay-pairing__loading').remove();
                //$('#rescan').show();
            }
        });
    });




    // SELECT IMPOSTAZIONE PIN - MOSTRA LA SCHERMATA DI RICHIESTA INSERIMENTO DEL PIN
    var pin = document.querySelector('#SettingsUserForm_scadenzaPin');
    pin.addEventListener('change', function(){
      if (this.value >0){
        $('.pin-numpad').append(pin_numpad);
        $('.easy-numpad-frame').css("top","1px");

        $('#pinNewModal').modal({
				  backdrop: 'static',
				  keyboard: false
			  });
      }else{
        dropNumpad();
      }
    });


    // INTERCETTA IL PULSANTE indietro SULLA PRIMA SCHERMATA e reset pulsanti
    var pinNewButtonBack = document.querySelector('#pinNewButtonBack');
    pinNewButtonBack.addEventListener('click', function(){
        dropNumpad(true);
        $('#pinNewButton').prop('disabled', true);
    });

    // INTERCETTA IL PULSANTE DI CONFERMA SULLA PRIMA SCHERMATA E MOSTRA QUELLA DI VERIFICA DEL PIN
    var pinNewButton = document.querySelector('#pinNewButton');
    pinNewButton.addEventListener('click', function(){
        dropNumpad();
        $('#pinNewModal').modal("hide");
        $('.pin-confirm-numpad').append(pin_confirm_numpad);
        $('.easy-numpad-frame').css("top","1px");
        $('#pinVerifyButton').prop('disabled', true);
        $('#pinVerifyButton').addClass('disabled');

        $('#pinVerifyModal').modal({
            backdrop: 'static',
            keyboard: false
        });
    });


    // INTERCETTA IL PULSANTE indietro SULLA seconda SCHERMATA e reset pulsanti
    var pinVerifyButtonBack = document.querySelector('#pinVerifyButtonBack');
    pinVerifyButtonBack.addEventListener('click', function(){
        dropNumpad(true);
        $('#pinNewButton').prop('disabled', true);
        $('#pinVerifyButton').prop('disabled', true);
    });

    // intercetta il pulsante di conferma VERIFICA Pin sulla seconda schermata e salva il PIN
    var pinVerifyButton = document.querySelector('#pinVerifyButton');
    pinVerifyButton.addEventListener('click', function(){
      //cripta il pin
      $.ajax({
    		url:'{$cryptoURL}',
    		type: "POST",
    		data: {'pass': $('#pin_password').val()},
    		dataType: "json",
          beforeSend: function(){
            $('#pinVerifyButtonText').html('<img width=20 src="'+ajax_loader_url+'" alt="'+Yii.t('js','loading...')+'">');
          },
    		  success:function(data){
            var post = {
        			id		: new Date().getTime() /1000 | 0, // timestamp
        			stop	: $('#SettingsUserForm_scadenzaPin').val(),
              pin     : data.cryptedpass,
        	  };
            clearAllData('pin').then(function(){
              writeData('pin', post).then(function() {
                console.log('Saved pin info in indexedDB', post);
                setTimeout(function(){
                  location.href = location.href
                }, 500);
              })
              //.then(function(){
                //


                // dropNumpad();
                // isPinRequest = false;
                // $('#pinVerifyModal').modal("hide");
                // $('#SettingsUserForm_scadenzaPin').hide();
                //
                // //show button remove pin
                // $('#pinRemoveButtonModal').show();
                // $('#pinRemoveButton').prop('disabled', true);
                // $('#pinRemoveButton').addClass('disabled');
                // $('.masterSeedMessagePinEnabled').show();
                // $('.masterSeedMessagePinDisabled').hide();
                // $('#pinNewButton').prop('disabled', true);
                // $('#pinVerifyButton').prop('disabled', true);
                // $('#pinVerifyButtonText').html(Yii.t('js','Confirm'));
                // setTimeout(function(){ backend.checkPin() }, 500);
              //})
              .catch(function(err) {
                console.log(err);
              });
            })
          },
          error: function(j){
    			  console.log('error',j);
    		  }
      });
    });


    // intercetta il pulsante Remove PIN e mostra la schermata di inserimento pin
    var pinRemoveButtonModal = document.querySelector('#pinRemoveButtonModal');
    pinRemoveButtonModal.addEventListener('click', function(){
        dropNumpad();
        isPinRequest = true;
        $('.pin-remove-numpad').append(pin_ask_numpad);
        $('.easy-numpad-frame').css("top","1px");
        readAllData('pin')
            .then(function(pin) {
                if (typeof pin[0] !== 'undefined') {
                    if (null !== pin[0].id && pin[0].stop != 0){
                        askPin(pin[0].pin,1);
                    }
                }
            });
    });

    // intercetta il pulsante di conferma RIMOZIONE pin e lo elimina
    var pinRemoveButton = document.querySelector('#pinRemoveButton');
    pinRemoveButton.addEventListener('click', function(){
        clearAllData('pin')
            .then(function(){
              location.href = location.href;
                // dropNumpad();
                // $('#pinRemoveModal').modal("hide");
                // $('#SettingsUserForm_scadenzaPin').prop('disabled', false);
                // $('#SettingsUserForm_scadenzaPin').show();
                // $('#SettingsUserForm_scadenzaPin').removeClass('disabled');
                // //disable button
                // $('#pinNewButton').prop('disabled', true);
                // $('#pinNewButton').addClass('disabled');
                // //show button remove pin
                // $('#pinRemoveButtonModal').hide();
                // $('#SettingsUserForm_scadenzaPin').val(0);
                //
                // $('#pinRemoveButton').prop('disabled', true);
                //
                // $('.masterSeedMessagePinEnabled').hide();
                // $('.masterSeedMessagePinDisabled').show();
                //
                // setTimeout(function(){ backend.checkPin() }, 500);
            });
    });

    // intercetta il pulsante di annulla RIMOZIONE pin e ripristina lo stato di verifica scadenza pin
    var pinRemoveButtonBack = document.querySelector('#pinRemoveButtonBack');
    pinRemoveButtonBack.addEventListener('click', function(){
        dropNumpad(false);
        $('#pinRemoveButton').prop('disabled', true);
        isPinRequest = false;
    });

    //controllo pa pressione del pulsante conferma PIN ed aggiorno il timestamp
    var pinRequestButton = document.querySelector('#pinRequestButton');
    pinRequestButton.addEventListener('click', function(){
        $('#pinRequestButton').html('<img width=20 src="'+ajax_loader_url+'" alt="'+Yii.t('js','loading...')+'">');
        isPinRequest = updatePinTimestamp();
    });

JS;
Yii::app()->clientScript->registerScript('myCreateToken', $myCreateToken, CClientScript::POS_END);
?>
