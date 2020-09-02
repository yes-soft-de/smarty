importScripts("[PLUGIN_URL]assets/js/localforage.min.js");
[FIREBASE]
//DO-NOT COPY THIMPRESS
importScripts("[PLUGIN_URL]assets/js/firebase-app.js");
importScripts("[PLUGIN_URL]assets/js/firebase-messaging.js");	
firebase.initializeApp([FIREBASE_OBJECT]);
const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
	var notificationTitle = "Message Title";
	var notificationOptions = {
		body: "Message body.",
		icon: "/firebase-logo.png"
	};
	return self.registration.showNotification(notificationTitle,
	notificationOptions);
});
[/FIREBASE]
const OFFLINE_VERSION = [SW_VERSION];
const CACHE_NAME = 'vibebp_'+[SW_VERSION];
const OFFLINE_URL = [OFFLINE_URL];
const DEFAULT_IMAGE=[DEFAULT_IMAGE];
self.addEventListener("install", event => {
  	console.log("Attempting to install service worker and cache static assets",event);
  	event.waitUntil(
    caches.open(CACHE_NAME)
    	.then(cache => {
    		let static_cache_assets = [STATIC_ASSETS];
    		static_cache_assets.push(OFFLINE_URL);
    		static_cache_assets.push(DEFAULT_IMAGE);
    		static_cache_assets.push("./");
    		static_cache_assets.map(function(cache_url){	    			
    			const request = new Request(cache_url, {mode: "no-cors"});
    			fetch(request).then(response => cache.put(request, response));
			})
	    })
  	);
});
self.addEventListener("activate", event => {
	caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.filter(function(cacheName) {
          if(cacheName.indexOf("vibebp") == 0 && cacheName != CACHE_NAME){
          	return true
          }
        }).map(function(cacheName) {
          return caches.delete(cacheName);
        })
      );
    })
});
self.addEventListener("fetch", event => {
	if (!event.request.url.startsWith(self.location.origin)) {
	   return;
	}
	if (event.request.url.indexOf('browser-sync') > -1) {return;}
	if (/wp-admin/.test(event.request.url)){return;}
	if (/wp-login/.test(event.request.url)){return;}
	if (/\?ver\=/.test(event.request.url)){return;}
	if (event.request.url === (self.location.origin + '/')) {return;}
  	if (/wp-json/.test(event.request.url)){
  		let force_refresh = 0;
  		if(event.request.url.indexOf('vibebp_reload')){
  			force_refresh = 1;
  		}
  		if(event.request.method === "POST"){
			let cloned = event.request.clone();
			localforage.setItem('last_request',cloned.url);
			event.respondWith(
				fetch(cloned).then(res=>res.clone().json())
  				.then(response => {
					return localforage.setItem(cloned.url,JSON.stringify(response)).then(()=>{
						return new Response(JSON.stringify(response),  { "status" : 200 , "statusText" : "ok!" })
					});
					
				}).catch(()=>{
					return localforage.getItem(event.request.url).then((res)=>{
						return new Response(res, { "status" : 200 , "statusText" : "ok!" });	
					})
	    		})
       		);

		
	  	}else{
	   		if(event.request.method === "GET"){
		 		event.respondWith(
	       			caches.match(event.request).then(cachedResponse => {
			 	        if (cachedResponse) {
			 	          return cachedResponse;
			 	        }
	       		}));
			}
	  	}
  	}else{

  		const destination = event.request.destination;
  		if(/wp-content\/plugins/.test(event.request.url) || /wp-includes/.test(event.request.url)){
		  	switch (destination) {
			    case 'style':
			    case 'script':
			    	event.respondWith(
			    		fetch(event.request).catch(()=>{
			    			return caches.match(event.request)
			    		})
		       		);
			    break;
			    case 'image':
			    	event.respondWith(
		       			fetch(event.request).catch(()=>{
			    			return caches.match(DEFAULT_IMAGE)
			    		})
		       		);
			    break;
		    }
		}

		if(destination == 'document'){

			event.respondWith((async () => {
		      	try {
		        // First, try to use the navigation preload response if it's supported.
		        	const preloadResponse = await event.preloadResponse;
		        	if (preloadResponse) {
		          		return preloadResponse;
		        	}

		        	const networkResponse = await fetch(event.request);
		        	return networkResponse;
		      	} catch (error) {
			        const cache = await caches.open(CACHE_NAME);
		        	const cachedResponse = await cache.match(OFFLINE_URL);
		        	return cachedResponse;
		      	}
		    })());
		}

		if(destination == 'font'){
			event.respondWith(
       			caches.match(event.request).then(cachedResponse => {
		 	        if (cachedResponse) {
		 	          return cachedResponse;
		 	        }
       		}).catch(function(){
       			const request = new Request(event.request.url, {mode: "no-cors"});
					return fetch(request).then(response => caches.put(request, response));
       		}));
		}
  	}
});
self.addEventListener("onsync", event => {
	console.log("onSync");
});
self.addEventListener("onmessage", event => {
	console.log("Message");
});
self.addEventListener("onpush", event => {
	console.log("Push");
});

