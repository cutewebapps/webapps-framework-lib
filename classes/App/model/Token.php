<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

/**
 * implementation of CSRF protection
 */
class App_Token
{
    /**
     * @return string
     */
    public static function generate()
    {
        $objConfig = App_Application::getInstance()->getConfig()->csrf;
        if ( $objConfig && $objConfig->generator ) {
            return call_user_func( $objConfig->generator );
        }
        // if not configured, using default algorithm:

        $objSession = new App_Session_Namespace( 'csrf' );
        $strToken = $objSession->token;
        if ( !$strToken ) {
            $strToken = md5( uniqid(mt_rand() . microtime() ) );
            $objSession->token = $strToken;
            $objSession->time = time();
        }
        return $strToken;
    }

    public static  function isValid()
    {
        $objConfig = App_Application::getInstance()->getConfig()->csrf;
        if ( $objConfig && $objConfig->validator ) {
            return call_user_func( $objConfig->validator);
        }

        // if not configured, using default algorithm:

        //xhr.setRequestHeader('X-CSRF-Token', csrf_token);
        $strReceivedToken = isset( $_REQUEST[ 'token' ] ) ? $_REQUEST[ 'token' ] : '';
        if ( isset( $_SERVER['X-CSRF-Token'] ))
            $strReceivedToken = $_SERVER['X-CSRF-Token'];

        $objSession = new App_Session_Namespace( 'csrf' );
        return ( $objSession->token == $strReceivedToken );
    }

}