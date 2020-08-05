<?php
$decryptURL = Yii::app()->createUrl('wallet/decrypt');// crypta da js

$masterSeed = <<<JS

	const masterSeedButton = document.querySelector('#showMasterSeed');
	masterSeedButton.addEventListener('click', function() {
		$('#masterSeed').html('<center><img width=15 src="'+ajax_loader_url+'"></center>');

			readAllData('mseed')
			.then(function(data) {
				console.log('[Master Seed IndexedDB]',data);
				if (typeof data[0] !== 'undefined') {
					$.ajax({
						url:'{$decryptURL}',
						type: "POST",
						data: {'cryptedseed': data[0].cryptedseed},
						dataType: "json",
						success:function(data){
							$('#masterSeed').html(data.decryptedseed);
						},
						error: function(j){
							console.log('error',j);
						}
					});

				}else{
					$('#masterSeed').text('Backup not found!');
				}
			})
	});

	//al click sull'indirizzo token in RICEVI lo copia negli appunti
	$("#showMasterSeedModal .modal-body").click(function(){
		if (!navigator.clipboard) {
				fallbackCopyTextToClipboard();
				return;
		}
		navigator.clipboard.writeText($('#masterSeed').text()).then(function() {
			console.log('Async: Copying to clipboard was successful!');
			$('#copyAddressModal').modal('show');
		}, function(err) {
			console.error('Async: Could not copy text: ', err);
		});
	});

	//nel caso in cui non funzioni il navigator.clipboard, utilizzo java standard
	function fallbackCopyTextToClipboard() {
		var textArea = $('#masterSeed');
		textArea.value = $('#masterSeed').text();
		textArea.focus();
		textArea.select();

		try {
			var successful = document.execCommand('copy');
			var msg = successful ? 'successful' : 'unsuccessful';
			console.log('Fallback: Copying text command was ' + msg);
			$('#copyAddressModal').modal('show');
		} catch (err) {
			console.error('Fallback: Oops, unable to copy', err);
		}

		//document.body.removeChild(textArea);
	}

JS;

Yii::app()->clientScript->registerScript('masterSeed', $masterSeed);
?>
