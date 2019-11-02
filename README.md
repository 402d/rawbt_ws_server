# RawBT: Websocket Server for ESC/POS Printers

RawBT requires PHP 5.4.0+ to run.  
The utility is distributed as a Phar package.

## Installing on user PC
0. Install PHP (if not installed yet)

1. Download last release from https://github.com/402d/rawbt_ws_server/releases
2. Unpack
3. Copy files from dist folder
4. Rename one from examples config
5. Edit connect params
6. Run server (rawbt)
 
## Front-end 
https://rawbt.ru/mike42/example_rawbt/
```js
function pc_print(data){
    var socket = new WebSocket("ws://127.0.0.1:40213/");
    socket.bufferType = "arraybuffer";
    socket.onerror = function(error) {
	  alert("Error");
    };			
	socket.onopen = function() {
		socket.send(data);
		socket.close(1000, "Work complete");
	};
}		
function android_print(data){
    window.location.href = data;  
}
function ajax_print(url, btn) {
    $.get(url, function (data) {
		var ua = navigator.userAgent.toLowerCase();
		var isAndroid = ua.indexOf("android") > -1; 
		if(isAndroid) {
		    android_print(data);
		}else{
		    pc_print(data);
		}
    });
}
```

## Back-end demo
https://github.com/mike42/escpos-php/blob/development/example/rawbt-receipt.php

**It is enough to specify the desired connector**
```php
    $connector = new RawbtPrintConnector();
```
