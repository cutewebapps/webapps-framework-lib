<?php

/**
 * checking version for PHP is at least 5.3
 */
class App_CheckEnv_Php53 
{

    public function __construct()
    {
        App_CheckEnv::assert( version_compare( PHP_VERSION, '5.3.0' ) > 0,
                "PHP version must be at least 5.3.0" );
    }
    
}
