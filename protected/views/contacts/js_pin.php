<?php
$myHistoryScript = <<<JS

$(function(){
	setTimeout(function(){ backend.checkPin() }, 100);
	setTimeout(function(){ backend.checkNotify() }, 500);
	setTimeout(function(){ blockchain.sync('{$from_address}') }, 500);
});

//controllo pa pressione del pulsante conferma PIN ed aggiorno il timestamp
var pinRequestButton = document.querySelector('#pinRequestButton');
pinRequestButton.addEventListener('click', function(){
	$('#pinRequestButton').html('<img width=20 src="'+ajax_loader_url+'" alt="'+Yii.t('js','loading...')+'">');
	isPinRequest = updatePinTimestamp();
	$('#pinRequestButton').prop('disabled', true);
	$('#pinRequestButton').addClass('disabled');
});

JS;
Yii::app()->clientScript->registerScript('myHistoryScript', $myHistoryScript);
?>
