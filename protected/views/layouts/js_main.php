<?php

Yii::app()->language = ( isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'it' );
Yii::app()->sourceLanguage = ( isset($_COOKIE['langSource']) ? $_COOKIE['langSource'] : 'it_it' );

new JsTrans('js',Yii::app()->language); // javascript translation

$urlBlocknumber = Yii::app()->createUrl('blockchain/getBlockNumber'); //sync blockchain
$urlBlockchain = Yii::app()->createUrl('blockchain/syncBlockchain'); //sync blockchain


$urlBlockchainScanForNew = Yii::app()->createUrl('blockchain/scanForNew'); //sync blockchain
$tokenStatus = Yii::app()->createUrl('tokens/status');// stato dell'invoice
$checkInstituteAlarm = Yii::app()->createUrl('backend/checkInstituteAlarm'); // stato dellìallarme

$myBlockchain = <<<JS

  //dichiarazione globale
  var blockchain;
  var ajax_loader_url = 'css/images/loading.gif';
  var paginaweb;
  var isPinRequest = false;
  var backend;

  // restituisce html dello status dell'invoice
  function tokenStatus(status){
  	$.ajax({
  		url:'{$tokenStatus}',
  		type: "POST",
  		data:{
  			'status'	: status,
  		},
  		dataType: "json",
  		success:function(data){
  			return data.status;
  		},
  		error: function(j){
  			return "null";
  		}
  	});
  }

	blockchain = {
		sync: function(my_address){
      console.log('[blockchain: sync]',my_address);

      $.ajax({
          url:'{$urlBlocknumber}',
          type: "POST",
          data: {'my_address': my_address},
          dataType: "json",
          success:function(data){
            if (data.success)
            {
              console.log('[blockchain: sync] difference from blocks:',data.diff);
              console.log('[blockchain: sync] actual block number:',data.chainBlocknumber);
              $('.pulse-button').removeClass('pulse-button-offline');
              if (data.diff > 0){
                if (data.diff < 2)
                  $('.sync-star').addClass('text-success fa-spin');

                if (data.diff > 240){ // 1 ora
                  $('.sync-blockchain').html('<div class="blockchain-pairing__loading"><center><img width=15 src="'+ajax_loader_url+'" alt="'+Yii.t('js','loading...')+'"></center></div>');
                  $('.sync-difference').html('<small>'+Yii.t('js','Synchronizing the blockchain: {number} blocks left.', {number:data.diff})+'</small>');
                }
              //   //if ('serviceWorker' in navigator && 'SyncManager' in window){
            	// 		//navigator.serviceWorker.ready.then(function(sw) {
        						var post = {
        							id: new Date().toISOString(), // id of indexedDB
        							url		: '{$urlBlockchain}', // url checkTransactions
                      search_address: my_address, // indirizzo da controllare
                      chainBlock: data.chainBlocknumber,
        						};
                    writeData('sync-blockchain', post).then(function() {
                      blockchain.callRegisterSyncBlockchain(my_address);
                    });
              //
        			// 			writeData('sync-blockchain', post)
        			// 				.then(function(response) {
              //           // console.log('[blockchain: sync] event register:', post);
        			// 					//return sw.sync.register('sync-blockchain');
              //           return response;
              //           //return navigator.serviceWorker.sync.register('sync-blockchain');
        			// 				})
              //         .then(function(){
              //           navigator.serviceWorker.ready.then(function(sw) {
              //             console.log('[blockchain: sync] event register:', post);
              //             return sw.sync.register('sync-blockchain');
              //           });
              //         })
        			// 				.catch(function(err) {
        			// 					console.log(err);
        			// 			  });
            	// 		//});
              //   //}else{
              //     // RICHIEDO LA FUNZINOE POST tramite ajax
              //
              // //  }

              }else{
                $('.sync-difference').html('');
                $('.blockchain-pairing__loading').remove();
                $('.sync-star').removeClass('text-success fa-spin');
              }
            }else{
              $('.pulse-button').addClass('pulse-button-offline');
            }

            // leggo adesso np-transaction
            // Se è vuoto torno subito a sync
            // se è pieno proseguo...
            readAllData('np-transactions').then(function(data) {
              if (typeof data[0] !== 'undefined') {
                blockchain.readTransactions(my_address);
              }else{
                //$('.sync-star').removeClass('text-success fa-spin');
                setTimeout(function(){ blockchain.sync(my_address) }, 7000);
              }
            });
          },
          error: function(j){
            console.log('[blockchain: sync] ERROR!');
            $('.pulse-button').addClass('pulse-button-offline');
            setTimeout(function(){ blockchain.sync(my_address) }, 7000);
          }
      });
		},
    callRegisterSyncBlockchain: function (my_address){
  		readAllData('sync-blockchain').then(function(data) {
        navigator.serviceWorker.ready.then(function(sw) {
          console.log('[blockchain: sync] event register:', data);
          return sw.sync.register('sync-blockchain');
        });
 			 })
    },

    // legge dalla tabella np-transactions eventuali transazioni trovate
    // nella blockchain
    readTransactions: function(my_address){
      readAllData('np-transactions').then(function(data) {
				if (typeof data[0] !== 'undefined') {
          console.log('[blockchain: readTransactions] np-transactions PIENO: ',data);
          if (data[0].success){
            console.log('[blockchain: readTransactions] np-transactions SUCCESS: ',data);
            for (var dt of data[0].transactions) {
              blockchain.addNewRow(dt);
              eth.txFound(dt.id_token);
            }
            // avvio un check syncronizzazione per verificare se
            // l'address è di un Istituto
            if ('serviceWorker' in navigator && 'SyncManager' in window){
      				navigator.serviceWorker.ready
      					.then(function(sw) {
      						var post = {
      							id: new Date().toISOString(), // id of indexedDB
      							url		: '{$checkInstituteAlarm}',
      							transactions: JSON.stringify(data[0].transactions),
      						};
      						writeData('sync-alarm', post)
      							.then(function() {
      								return sw.sync.register('sync-alarm');
      							})
      							.catch(function(err) {
      								console.log(err);
      						});
      					});
          	}

            setTimeout(function(){ erc20.Balance(my_address) }, 1000);
          }
          clearAllData('np-transactions');
				} else {
          console.log('[blockchain: readTransactions] Nothing!');
				}

        setTimeout(function(){ blockchain.sync(my_address) }, 7000);
  		});
    },
    scanForNew: function(my_address){
      $.ajax({
        url:'{$urlBlockchainScanForNew}',
        type: "POST",
        data: {
          'my_address': my_address,
        },
        dataType: "json",
        success:function(response){
          console.log('[ScanForNew]',response);
          if (response.success){
            var options = {
              title: Yii.t('js','[Bolt] - New message'),
              body: Yii.t('js','Transactions updated. Do you want to view them?'), //walletStatus(data.status),
              icon: 'src/images/icons/app-icon-96x96.png',
              vibrate: [100, 50, 100, 50, 100 ], //in milliseconds vibra, pausa, vibra, ecc.ecc.
              badge: 'src/images/icons/app-icon-96x96.png', //solo per android è l'icona della notifica
              tag: 'confirm-notification', //tag univoco per le notifiche.
              renotify: true, //connseeo a tag. se è true notifica di nuovo
              data: {
                openUrl: response.openUrl,
              },
              actions: [
                {action: 'openUrl', title: Yii.t('js','Yes'), icon: 'css/images/chk_on.png'},
                {action: 'close', title: Yii.t('js','No'), icon: 'css/images/chk_off.png'},
              ],
            };
            displayNotification(options);
          }
        },
        error: function(j){
          console.log(j);
        }
      });
    },
		// // controlla se il db di ricezione indexedDB è stato preparato dal service worker
		// isReadyReceived: function(my_address){
		// 	readAllData('np-transactions').then(function(data) {
    //
		// 		if (typeof data[0] !== 'undefined') {
    //       console.log('Trovato np-blockchain PIENO: ',data);
    //       if (data[0].success){
    //         console.log('Trovato np-blockchain SUCCESS: ',data);
    //         for (var dt of data[0].transactions) {
    //           blockchain.addNewRow(dt);
    //           eth.txFound(dt.id_token);
    //         }
    //         setTimeout(function(){ erc20.Balance(my_address) }, 1000);
    //         setTimeout(function(){ blockchain.sync(my_address) }, 1000);
    //       }else{
    //         setTimeout(function(){ blockchain.sync(my_address) }, 7000);
    //       }
    //       clearAllData('np-blockchain');
		// 		} else {
    //       //console.log('Trovato np-blockchain VUOTO: ',data);
		// 			//console.log('waiting 9.5 sec il sw writing blockchain datas on indexedDB ...');
		// 			setTimeout(function(){ blockchain.sync(my_address) }, 7000);
		// 		}
    //
		// 	});
		// },
    addNewRow: function(data){
			$("<tr class='even animazione'>"
            +"<td><i class='zmdi zmdi-star-outline'></i></td>"
            +"<td><a href='"+data.url+"'>"+data.data+"</a></td>"
            +"<td class='desc __sending_now-"+data.id_token+"'><a href='"+data.url+"'>"+data.status+"</a></td>"
            +"<td style='text-align:center;' class='__sending_now_price-"+data.id_token+"'>"+data.token_price+"</td>"
            +"<td class='mobile-not-show'>"+(data.token_price < 0 ? data.to_address : data.from_address)+"</td>"
            +"<td style='width:50px;'><i class='fa fa-unlock ' style='color:red;'></i><span style='font-size:0.8em;'>1</span></td>"
            +"</tr>").prependTo("#tokens-grid table.items > tbody");

              $('.animazione').addClass("animationTransaction");
		},
	}



JS;
Yii::app()->clientScript->registerScript('myBlockchain', $myBlockchain, CClientScript::POS_HEAD);
