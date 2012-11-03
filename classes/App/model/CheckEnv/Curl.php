<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_CheckEnv_Curl
{
    public function __construct()
    {
        App_CheckEnv::assert( extension_loaded("curl"),    'Curl Extension is not supported' );
        App_CheckEnv::assert( extension_loaded("sockets"), "Sockets Extension is not supported" );
        App_CheckEnv::assert( extension_loaded("openssl"), 'OpenSSL Extension is not supported' );
        
        // this is not enough as Curl can be compiled without SSL support
        $modules = new App_CheckEnv_Phpinfo();
	if( $modules->isLoaded('curl') ) { // Test if curl is loaded
            App_CheckEnv::assert(  
		      strstr( $modules->getModuleSetting( 'curl', 'cURL Information'), 'OpenSSL/' )
                   || strstr( $modules->getModuleSetting( 'curl', 'SSL Version' ), 'OpenSSL/' ), 
                      'Curl Is Compiled without OpenSSL Support' );
	} 
    }
}