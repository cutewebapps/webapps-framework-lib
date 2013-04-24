#!/usr/bin/env node
var http  = require('http'), fs = require("fs");
var sys = require('sys')
var exec = require('child_process').exec;

ServerThread = function( response, url, body ) {

	this.sFolder = process.env.TEMP;
	if  ( !this.sFolder )  this.sFolder = "/tmp";

	this.sFolder += "/nwapp_" + parseInt( Math.random() * 10000 );

        var dt = new Date();
	this.sIndexHtml = "<!DOCTYPE html>\n" + "<html>\n<head>\n<title>Alert</title>\n" +
		'<meta name="viewport" content="width=device-width, initial-scale=1.0" />' +
		'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' +
		'</head>' +
		'<body><h1 style="font-family:arial">' + url + '</h1><strong style="font-family:arial">' + dt + '</strong><pre>' + body + '</pre></body></html>';
	this.sPackageJson = '{  "name": "Alert", "main": "index.html",  "window": { "toolbar": false, "width": 800 } }';


	console.log( this.sFolder );

	// create folder
	_that = this;
	fs.mkdir( this.sFolder ,function(e){
		if ( e ) console.log( e );

		fs.writeFileSync( _that.sFolder + "/index.html", _that.sIndexHtml );
		fs.writeFileSync( _that.sFolder + "/package.json", _that.sPackageJson );

		exec("nw "+ _that.sFolder, function (error, stdout, stderr) {
			if ( stdout != "" ) sys.print('stdout: ' + stdout);
			if ( stderr != "" ) sys.print('stderr: ' + stderr);
			if (error !== null) {
				console.log('exec error: ' + error);
			}
		});
	});

        response.write( '["ok"]' );
	response.end();
        
	console.log( 'response sent' );      
};


var port = 6789;
http.createServer(function (req, res) {

    // console.log( "URL: " + req.url );
    
    var fullBody = '';
    req.on('data', function(chunk) {
      // append the current chunk of data to the fullBody variable
      fullBody += chunk.toString();
    });
    req.on('end', function() {
      // request ended -> do something with the data
      res.writeHead(200, "OK", {'Content-Type': 'application/json'});
      // parse the received body data
      var thread = new ServerThread( res, req.url, fullBody );
    });

}).listen( port, '0.0.0.0');
console.log('Server running at http://0.0.0.0:'+port+'/');


// new ServerThread( null, "wow, this is our body" );