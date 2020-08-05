<?php
Yii::app()->language = ( isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'it' );
Yii::app()->sourceLanguage = ( isset($_COOKIE['langSource']) ? $_COOKIE['langSource'] : 'it_it' );
new JsTrans('js',Yii::app()->language); // javascript translation

$myNfcScript = <<<JS

if (!("NDEFReader" in window))
	$('#statusNFC').show().text("Web NFC is not available. Please make sure the 'Web NFC' is enabled on Android.");

	//controllo la pressione del pulsante NFC
	var NFCwriteButton = document.querySelector('#NFCwriteButton');
	var NFCscanButton = document.querySelector('#NFCscanButton');

	if (/Chrome\/(\d+\.\d+.\d+.\d+)/.test(navigator.userAgent)){
	// Let's log a warning if the sample is not supposed to execute on this
	// version of Chrome.
		if (81 > parseInt(RegExp.$1)) {
		  console.log('Warning! Keep in mind this sample has been tested with Chrome ' + 81 + '.');
		  $('#statusNFC').show().text('Warning! Keep in mind Web NFC has been tested with Chrome ' + 81 + '.');
		  $('#NFCwriteButton').prop('disabled', true);
		}else{


			NFCwriteButton.addEventListener("click", async () => {
				console.log("User clicked write button");

				try {
					const writer = new NDEFWriter();
					await writer.write("{$from_address}");
					console.log("> Message written");
				} catch (error) {
					console.log("Argh! " + error);
				}
			});


			NFCscanButton.addEventListener("click", async () => {
				console.log("User clicked scan button");

				try {
					const reader = new NDEFReader();
					await reader.scan();
					console.log("> Scan started");
					reader.addEventListener("error", () => {
				  		console.log("Argh! Cannot read data from the NFC tag. Try a different one?");
					});
					reader.addEventListener("reading", ({ message, serialNumber }) => {
				  		console.log('> Serial Number: '+serialNumber);
				  		console.log('> Records: '+message.records.length);
						$('#WalletTokenForm_to').val(message);
						// $('#scrollmodalNFC').modal('hide');
					});
				} catch (error) {
					console.log("Argh! " + error);
				}
			});
		}

	}


JS;
Yii::app()->clientScript->registerScript('myNfcScript', $myNfcScript);
?>
