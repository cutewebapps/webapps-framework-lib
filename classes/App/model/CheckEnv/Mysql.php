<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_CheckEnv_Mysql
{
/**
 * checking requirements for PDO + mysql
 */            
    public function __construct()
    {
        App_CheckEnv::assert( extension_loaded("pdo_mysql"),     "MySQL PDO extension is not loaded" );
        App_CheckEnv::assert( function_exists("mysql_connect"),     "MySQL connect function was not found" );
        
        // checking all connections with given credentials
        $connections = App_Application::getInstance()->getConfig()->connections;
        if ( $connections ) {
            foreach( $connections as $strConnIndex => $objConnectionConfig ) {
                
                $arrParams = $objConnectionConfig->params->toArray();
                
                App_CheckEnv::assert(  isset( $arrParams['host'] ), 'No host defined for "'.$strConnIndex.'" connection' );
                App_CheckEnv::assert(  isset( $arrParams['username'] ), 'No username defined for "'.$strConnIndex.'" connection' );
                App_CheckEnv::assert(  isset( $arrParams['dbname'] ), 'No database defined for "'.$strConnIndex.'" connection' );
                $strHost     = $arrParams['host'];
                $strUsername = $arrParams['username'];
                $strPassword = isset( $arrParams['password'] ) ? $arrParams['password'] : '' ;
                $strDatabase = $arrParams['dbname'];
                
                App_CheckEnv::assert( mysql_connect( $strHost, $strUsername, $strPassword ),
                        "no mysql connection for ".$strConnIndex );
                App_CheckEnv::assert( mysql_selectdb( $strDatabase ),
                        "mysql database cannot be selected for ".$strConnIndex );
            }
        }
    }
    
}
