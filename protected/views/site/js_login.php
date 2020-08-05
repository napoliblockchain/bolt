<?php
$check2faURL = Yii::app()->createUrl('site/check2fa'); // verifica esistenza 2fa da richiedere

$login = <<<JS
	var accediButton = document.querySelector('#accedi-button');
	var twofaButton = document.querySelector('#Conferma2fa');
	var installWebAppFromGoogle = document.querySelector('#gSignIn');

	var dbTestOnPrivateBrowsing = window.indexedDB.open('test');
	dbTestOnPrivateBrowsing.onerror = function(){
	  console.log("[IndexedDB] Can't use indexedDB. You probably is in Private Mode Browsing!");
		$('#privateBrowsingAlert').show().text('Can\'t use the app. You probably is in Private Mode Browsing!');
	}

	function check1fa (event){
		event.preventDefault();
		check2fa(
			$('#LoginForm_username').val(),
			$('#LoginForm_oauth_provider').val()
		);
	}


	function check2fa(username, oauth_provider){
		$.ajax({
			url:'{$check2faURL}',
			type: "POST",
			data:{
				'username': username,
				'oauth_provider': oauth_provider,
			},
			dataType: "json",
			success:function(data){
				console.log('Fetching 2fa',data);
				if (data.response===true){
					$('#2faModal').modal({
						backdrop: 'static',
						keyboard: false
					});
				}else{
					confirmForm();
				}
			},
			error: function(j){
				console.log(j);
			}
		});
		console.log(username);
	}

	function confirmForm(){
		$('#login-form').submit();
	}

	// in questo modo passo l'event alla fuzione check2fa
	// event è sempre il primo parametro di una funzione
	accediButton.addEventListener('click', function(event){
		check1fa(event);
	});
	twofaButton.addEventListener('click', confirmForm);
	installWebAppFromGoogle.addEventListener('click', installWebApp);

	function installWebApp()
	{
		// chiede di installare la webapp sul desktop
		if (deferredPrompt) {
			deferredPrompt.prompt();

			deferredPrompt.userChoice.then(function(choiceResult) {
				console.log(choiceResult.outcome);
				if (choiceResult.outcome === 'dismissed') {
					console.log('User cancelled installation');
				} else {
					console.log('User added to home screen');
				}
			});
			deferredPrompt = null;
		}
	}

JS;
Yii::app()->clientScript->registerScript('login', $login, CClientScript::POS_END);

?>
