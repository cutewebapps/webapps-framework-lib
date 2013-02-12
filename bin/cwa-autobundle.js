#!/usr/bin/env node

var fs = require('fs');



var walk = function(dir, done) {
  var results = [];
  fs.readdir(dir, function(err, list) {
    if (err) return done(err);
    var i = 0;
    (function next() {
      var file = list[i++];
      if (!file) return done(null, results);
      var basename = file;
      file = dir + '/' + basename;
      
      
        fs.stat(file, function(err, stat) {
          if (stat && stat.isDirectory()) {
            walk(file, function(err, res) {
              results = results.concat(res);
              next();
            });
          } else {
              //console.log( file );
            if ( basename == "autobundle.js" ) {
                results.push( [dir, basename] );
            }
            next();
          }
        });
      
    })();
  });
};

walk( process.env.CWA_HOME + "/htdocs", function(err, results) {
  if (err) throw err;
  console.log( "Launched: " + results.length + " processes ");
  // console.log(results);
  for ( var i = 0; i < results.length; i++ ) {
      var dir = results[ i ][0];
      console.log( dir );
      
      var spawn = require('child_process').spawn;
      process.chdir( dir );
      var child = spawn( '/usr/local/bin/node', ['autobundle.js']);
      child.stdout.on('data', function (data) { console.log(data.toString()); });
      child.stderr.on('data', function (data) { console.log(data.toString()); });
      child.on('exit', function (code) {
      if (code !== 0) { console.log('process exited with code ' + code); }
        child.stdin.end();
      });
                
  }
  
} );