<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 *
 * Licensed under GPL, Free for usage and redistribution.
 */

class Sys_Mode
{
    /**
     * checks whether certain develop mode is available
     * @param string strMode what mode should be checked
     */
    static function is ($strMode = '')
    {
        $strCookie = 'debugmode';

        if ($strMode == '') {
            // returning the value of cookie
            return (isset( $_COOKIE[$strCookie])) ? ($_COOKIE[$strCookie]) : '';
        }
        // searching the value to check in comma-separated values of cookie
        if (isset($_COOKIE[ $strCookie ]))
            $vals = explode(';', str_replace(',', ';', $_COOKIE[$strCookie]));
        else
            $vals = array();
        foreach ($vals as $i => $v) {
            if ($v != '' && $strMode == $v) return 1;
        }
    }

   /**
    * whether we'are running from console
    * @return void
    */
    public static function isConsole()
    {
        return ( !isset( $_SERVER['HTTP_HOST'] ) );
    }
    /**
     * APLLICATION_ENV is set up in apache config to detect
     * host type in CGI mode
     */
    public static function getApplicationEnv ()
    {
        if (defined('CWA_ENV'))
            return CWA_ENV;
        if ( Sys_Global::isRegistered( 'Environment') )
            return Sys_Global::get( 'Environment');
        return (getenv('CWA_ENV') ? getenv('CWA_ENV') : 'local');
    }

    /**
     * detects whether we are on local server
     */
    static function isLocal ()
    {
        return (self::getApplicationEnv() == 'local');
    }
    /**
     * detects whether we are on test server
     */
    static function isTest()
    {
        return (self::getApplicationEnv() == 'local');
    }
/**
     * detects whether we are on demo server
     */
    static function isDemo()
    {
        return (self::getApplicationEnv() == 'demo');
    }
/**
     * detects whether we are on staging server
     */
    static function isStaging()
    {
        return (self::getApplicationEnv() == 'staging');
    }

    /**
     * detects whether we are on live server
     */
    static function isProduction ()
    {
        if (self::getApplicationEnv() == 'production')
            return true;

        return (! self::isLocal() ) && (! self::isTest() && (! self::isDemo() ) && (! self::isStaging() ));
    }


    /**
     * Do not use it to detect production website, use is Live
     */
    static function isWww ()
    {
        if ( isset($_SERVER['HTTP_HOST']))
            return (strstr($_SERVER['HTTP_HOST'], 'www.') !== false);
        else
            return false;
    }
    /**
     * detects whether we are under HTTPS
     */
    static function isSsl ()
    {
        if ( isset($_SERVER['HTTPS']))
            return (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on');
        else
            return false;
    }

    static public function getScheme()
    {
	return self::isSsl() ? 'https' : 'http';
    }


    /**
     * detects whether user-agent is iPad
     */
    static public function isIpad() {
        if ( self::is('ipad') ) return false;
        if ( isset( $_SERVER['HTTP_USER_AGENT'] ) )
            return strstr( $_SERVER['HTTP_USER_AGENT'], 'iPad' );
        return false;
    }
    /**
     * detects whether it is Internet Explorer with some Version
     */
    static public function isMsIe( $nMaxVersion = '' )
    {
        if ( isset( $_SERVER['HTTP_USER_AGENT'] ) )  {
            if ( preg_match( '/MSIE (\d+)/sim', $_SERVER['HTTP_USER_AGENT'], $arrMatch ) ) {
                $nVersion = intval( $arrMatch[1] );
                if ( $nMaxVersion != '' ) {
                    //if we require some version
                    return $nMaxVersion >= $nVersion;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * detects whether it is command-line mode
     */
    public static function isCli()
    {
        return ( PHP_SAPI == 'cli' );
    }

    /**
     * detects whether user-agent is mobile device
     */
    static function isMobile ()
    {
        global $_is_mobile;
        if (isset($_is_mobile)) return $_is_mobile;

        if (self::is('pda')) {
            $_is_mobile = true;
            return true;
        }
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            $_is_mobile = true;
            return true;
        }
        if (isset($_SERVER['HTTP_X_SKYFIRE_PHONE'])) {
            $_is_mobile = true;
            return true;
        }
        if (isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])) {
            $_is_mobile = true;
            return true;
        }
        if (self::isIpad()) {
            $_is_mobile = false;
            return false;
        }
        if (isset($_SERVER['HTTP_ACCEPT']) && preg_match('/wap\.|\.wap/i', $_SERVER['HTTP_ACCEPT'])) {
            $_is_mobile = true;
            return true;
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];

            // Quick Array to kill out matches in the user agent
            // that might cause false positives
            $badmatches = array(
            'OfficeLiveConnector', 'MSIE\s8\.0', 'OptimizedIE8',
            'MSN\sOptimized', 'Creative\sAutoUpdate', 'Swapper');
            foreach ($badmatches as $badstring) {
                if (preg_match('/' . $badstring . '/i',
                $_SERVER['HTTP_USER_AGENT'])) {
                    $_is_mobile = false;
                    return false;
                }
            }
            // Now we'll go for positive matches
            $uamatches = array( 'iphone', 'android', 'ipod', 'ipad');

            foreach ($uamatches as $uastring) {
                if (preg_match('/' . $uastring . '/i', $ua)) {
                    $_is_mobile = true;
                    return true;
                }
            }
        }
        $_is_mobile = false;
        return false;
    }

}
