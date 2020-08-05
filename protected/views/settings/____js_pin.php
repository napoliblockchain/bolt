<?php
Yii::app()->language = ( isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'it' );
Yii::app()->sourceLanguage = ( isset($_COOKIE['langSource']) ? $_COOKIE['langSource'] : 'it_it' );
new JsTrans('js',Yii::app()->language); // javascript translation

$myPinScript = <<<JS

	//controllo la pressione del pulsante conferma PIN ed aggiorno il timestamp
	var pinRequestButton = document.querySelector('#pinRequestButton');
	pinRequestButton.addEventListener('click', function(){
		$('#pinRequestButton').html('<img width=20 src="'+ajax_loader_url+'" alt="'+Yii.t('js','loading...')+'">');
		isPinRequest = updatePinTimestamp();
		$('#pinRequestButton').prop('disabled', true);
		$('#pinRequestButton').addClass('disabled');
	});

JS;
Yii::app()->clientScript->registerScript('myPinScript', $myPinScript);
?>
