<?php

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
        if (defined('WC_APPLICATION_ENV'))
            return WC_APPLICATION_ENV;
        if ( Sys_Config::isRegistered( 'Environment') )
            return Sys_Config::get( 'Environment');
        return (getenv('WC_APPLICATION_ENV') ? getenv('WC_APPLICATION_ENV') : 'production');
    }

    /**
     * detects whether we are on local server 
     */
    static function isLocal ()
    {
        if (isset($_SERVER['HTTP_HOST']))
            return (strstr($_SERVER['HTTP_HOST'], 'local.') !== false ||
                strstr($_SERVER['HTTP_HOST'], '.local') !== false);
        else 
            if (self::getApplicationEnv() == 'local');
    }


    /**
     * detects whether we are on live server 
     */
    static function isProduction ()
    {
        return (! self::isLocal() );
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
            $uamatches = array(
            'midp', 'j2me', 'avantg', 'docomo', 'novarra', 'palmos', 
            'palmsource', '240x320', 'opwv', 'chtml', 'pda', 
            'windows\sce', 'mmp\/', 'blackberry', 'mib\/', 'symbian', 
            'wireless', 'nokia', 'hand', 'mobi', 'phone', 'cdm', 'up\.b', 
            'audio', 'SIE\-', 'SEC\-', 'samsung', 'HTC', 'mot\-', 
            'mitsu', 'sagem', 'sony', 'alcatel', 'lg', 'erics', 'vx', 
            'NEC', 'philips', 'mmm', 'xx', 'panasonic', 'sharp', 'wap', 
            'sch', 'rover', 'pocket', 'benq', 'java', 'pt', 'pg', 'vox', 
            'amoi', 'bird', 'compal', 'kg', 'voda', 'sany', 'kdd', 'dbt', 
            'sendo', 'sgh', 'gradi', 'jb', '\d\d\di', 'moto', 'webos', 
            'iphone', 'android', 'netfront');
            
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