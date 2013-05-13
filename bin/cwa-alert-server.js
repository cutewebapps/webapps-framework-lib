#!/usr/bin/env node
var http  = require('http'), fs = require("fs");
var sys = require('sys')
var exec = require('child_process').exec;

ServerThread = function( response, url, body ) {

	this.sFolder = process.env.TEMP;
	if  ( !this.sFolder )  this.sFolder = "/tmp";

	this.sFolder += "/nwapp_" + parseInt( Math.random() * 10000 );

        if ( body.indexOf( 'HTML_CONTENTS' ) > -1 ) {
            var json = JSON.parse( body );
            // console.log( json );
            
            body = "<table border='1' cellspacing='0' cellpadding='3' style='border-color:#eee; width:100%;'><tbody>";
            for ( var key in json ) {
                if ( key.indexOf( "HTML_CONTENTS" ) == 0 ) {
                    body += "<tr><td colspan='2' style='padding:20px'>" + json[key]  + "</td></tr>";
                } else {
                    body += "<tr><td>" + key + "</td><td>" + json[key] + "</td></tr>";
                }
            }
            body += "</tbody></table>";
            
        } else {
            body = '<pre>' + body + '</pre>';
        }

        var dt = new Date();
	this.sIndexHtml = "<!DOCTYPE html>\n" + "<html>\n<head>\n<title>Alert</title>\n" +
		'<meta name="viewport" content="width=device-width, initial-scale=1.0" />' +
		'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' +
		'</head>' +
		'<body><h1 style="font-family:arial">' + url + '</h1><strong style="display:block;font-family:arial">' + dt + '</strong>' + body + '</body></html>';
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