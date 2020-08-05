<?php
$serviceWorker = <<<JS

  // quando cambi questi valori modificali anche in sw.js
    var CACHE_STATIC_NAME = 'bolt-static-007';
    var CACHE_DYNAMIC_NAME = 'bolt-dynamic-007';

    function trimCache(cacheName, maxItems) {
    	caches.open(cacheName)
    		.then(function(cache) {
    			return cache.keys()
    				.then(function(keys) {
    					if (keys.lenght > maxItems) {
    						cache.delete(keys[0])
    							.then(trimCache(cacheName, maxItems));
    					}
    				});
    		});
    }
    var serviceWorker = document.querySelector('.delete-serviceWorker');
    function deleteServiceWorker() {
      console.log("exiting [sw]...");
    	if ('serviceWorker' in navigator) {
    		console.log('Deleting service worker!');
    		trimCache(CACHE_STATIC_NAME, 0);
    		trimCache(CACHE_DYNAMIC_NAME, 0);
    		navigator.serviceWorker.getRegistrations()
    			.then(function(registrations) {
    				for (var i = 0; i < registrations.lenght; i++) {
              console.log('unregistering sw...',i);
    					registrations[i].unregister();
    				}
    			})
    	}
    }

    self.addEventListener('install', function(e) {
      console.log('skiping sw');
      self.skipWaiting();
    });

    self.addEventListener('activate', function(e) {
      self.registration.unregister()
        .then(function() {
          return self.clients.matchAll();
        })
        .then(function(clients) {
            console.log('unregistering sw...');
          clients.forEach(client => client.navigate(client.url))
        });
    });

    // chiama delete sul pulsante logout
    serviceWorker.addEventListener('click', deleteServiceWorker);
    // ma intanto lo chiama appena entrato
    //deleteServiceWorker();
JS;
Yii::app()->clientScript->registerScript('serviceWorker', $serviceWorker);


?>
