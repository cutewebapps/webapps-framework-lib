<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_CheckEnv_InnoDb
{
/**
 * checking requirements for PDO + mysql
 */            
    public function __construct()
    {
        // checking all connections with given credentials
        $connections = App_Application::getInstance()->getConfig()->connections;
        if ( $connections ) {
            foreach( $connections as $strConnIndex => $objConnectionConfig ) {
                
                $arrParams = $objConnectionConfig->params->toArray();
                
                App_CheckEnv::assert(  isset( $arrParams['host'] ), 'No host defined for "'.$strConnIndex.'" connection' );
                App_CheckEnv::assert(  isset( $arrParams['username'] ), 'No username defined for "'.$strConnIndex.'" connection' );
                $strHost     = $arrParams['host'];
                $strUsername = $arrParams['username'];
                $strPassword = isset( $arrParams['password'] ) ? $arrParams['password'] : '' ;
                
                App_CheckEnv::assert( mysql_connect( $strHost, $strUsername, $strPassword ),
                        "no mysql connection for ".$strConnIndex );
                
                $rs = mysql_query( 'SHOW ENGINES');
                $bMet = false;
                while( $rs && $r = mysql_fetch_array( $rs )) {
                    if ( strtolower( $r[ 0 ] ) == 'innodb' ) {
                        $bMet = true; break;
                    }
                }
                App_CheckEnv::assert( $bMet, "InnoDB engine support is disabled" );
            }
        }
    }
    
}
