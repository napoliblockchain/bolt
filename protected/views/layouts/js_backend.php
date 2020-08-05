<?php
/**
 * nuova gestione delle notifiche
 */

$urlBackend = Yii::app()->createUrl('backend/notify');
$urlNewsRead = Yii::app()->createUrl('backend/updateNews');
$urlNewsReadAll = Yii::app()->createUrl('backend/updateAllNews');
$fileNotify = Yii::app()->request->baseUrl.'/css/sounds/notify.mp3';

$myBackendScript = <<<JS
	var urlBackend = '{$urlBackend}';
  var urlNewsRead = '{$urlNewsRead}';
	var urlNewsReadAll = '{$urlNewsReadAll}';
  var urlSound = '{$fileNotify}';

	$(function(){
  	backend = {
			checkPin: function()
			{
				readAllData('pin').then(function(pin) {
					if (typeof pin[0] !== 'undefined') {
	          if (null !== pin[0].id && pin[0].stop != 0){
	            var checktime = {
                adesso : new Date().getTime() /1000 | 0,
                scadenza : pin[0].id + (pin[0].stop * 60),
            	};
	            var differenza = checktime.scadenza - checktime.adesso;
	            if (differenza <= 0 && isPinRequest==false){
	              isPinRequest = true;
	              askPin(pin[0].pin).then(function(){
									setTimeout(function(){ backend.checkPin() }, 5000);
								})
							}else{
	              if (!isPinRequest){
	                isPinRequest = updatePinTimestamp();
									setTimeout(function(){ backend.checkPin() }, 5000);
	              }
	            }
	          }else{
	            console.log('[backend: checkPin] Pin impostato a 0');
							setTimeout(function(){ backend.checkPin() }, 5000);
	          }
	        }else{
	          console.log('[backend: checkPin] Nessun pin impostato!');
						setTimeout(function(){ backend.checkPin() }, 5000);
	      	}
	      });
			},
			checkNotify: function()
			{
				$.ajax({
					url:urlBackend,
					type: "POST",
					data: { 'countedNews' : $('#countedNews').val() },
					dataType: 'json',
					success: function(response) {
						backend.handleResponse(response);
						setTimeout(function(){ backend.checkNotify() }, 5000);
					},
					error: function(data) {
						setTimeout(function(){ backend.checkNotify() }, 5000);
					}
				});
			},
			Alarm: function(){
				navigator.vibrate = navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate;
				if (navigator.vibrate) {
						navigator.vibrate(60);
				}
				var audio = new Audio(urlSound);
				audio.play();
			},
			handleResponse: function(response)
			{
				if (response.playAlarm == true){
					backend.Alarm();
				}
				if (response.playSound == true){
					backend.playSound();
				  //VERIFICO QUESTE ULTIME 3 TRANSAZIONI PER AGGIORNARE IN REAL-TIME LO STATO (IN CASO CI SI TROVA SULLA PAGINA TRANSACTIONS)
				  for (var key in response.status) {
				    var status = response.status[key];
				    //backend.updateTransactionRows(status,key);
				  }
				}

				$("#notifiche_dropdown").fadeIn(1000).css("display","");
				$('#quantity_notify').html(response.countedUnread);
				$('#notifiche__contenuto').html(response.htmlTitle);
				$('#notifiche__contenuto').append(response.htmlContent);
				if (response.countedUnread > 0){
				  $("#quantity_circle").fadeIn(1000).css("display","");
					$("#quantity_circle").css("background","#ff4b5a");
				}else{
				    $("#quantity_circle").fadeIn(1000).css("display","none");
				}
			},
    	updateTransactionRows(item, index)
			{
        console.log('update transaction status id:',index, item);
        //TODO: verifica che l'id sia questo in wallet/index o token/index
        $( "#transactionstatus_"+index ).html(tokenStatus(item)).fadeIn(3000);
      },
      playSound: function(){
          navigator.vibrate = navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate;
          if (navigator.vibrate) {
              navigator.vibrate(60);
          }
					var audio = new Audio(urlSound);
					audio.play();
      },
      openEnvelope: function(id_notification){
        event.preventDefault();
        event.stopPropagation();
        var submitUrl = $('#news_'+id_notification).attr('href');

        // metto a read il valore del messaggio
        $.ajax({
					url:urlNewsRead,
					type: "POST",
          data: { 'id_notification' : id_notification },
        	dataType: 'json',
          success: function(response) {
						if (response.success)
            	location.href = submitUrl;
    				},
    				error: function(data) {
            	console.log(data);
    				},
				});
			},
			openAllEnvelopes: function(){
	      event.preventDefault();
	      event.stopPropagation();
	      var submitUrl = $('#seeAllMessages').attr('href');
	      $.ajax({
					url:urlNewsReadAll,
					type: "POST",
	        data: { },
	       	dataType: 'json',
	        success: function(response) {
						if (response.success)
	            location.href = submitUrl;
	    			},
	    			error: function(data) {
	            console.log(data);
	    			},
				});
			}
        }
    	//funzioni da richiamare all'avvio

    });

JS;
Yii::app()->clientScript->registerScript('myBackendScript', $myBackendScript, CClientScript::POS_HEAD);
?>
