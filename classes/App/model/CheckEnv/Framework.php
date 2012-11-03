<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

/**
 * checking requirements for framework 
 */
class App_CheckEnv_Framework 
{

    public function __construct()
    {
       
        App_CheckEnv::assert( version_compare( PHP_VERSION, '5.2.0' ) > 0,
                "PHP version must be at least 5.2.0" );
        App_CheckEnv::assert( function_exists( 'mb_convert_encoding' ), 
                "MB convert encoding is missing" );
        App_CheckEnv::assert( function_exists( 'gzcompress' ),
                'Zlib is required for more efficient data storage & unpacking' );
    }
    
}
