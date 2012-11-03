<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_RedirectHelper extends App_ViewHelper_Abstract
{
    /**
     * Sends redirection header if possible and returns HTML for meta redirection
     * 
     * @param string $strUrl
     * @param integer $strHttpCode (optional)
     * @return string
     * @throws App_Exception
     */
    public function redirect( $strUrl, $strHttpCode = 302 )
    {
        if ( !headers_sent() ) {
            // TODO: 301 or 302
            if ( $strHttpCode != 302 ) {
                if ( !in_array($strHttpCode, array(300,301,303,307) ) ) {
                    throw new App_Exception( "Invalid redirect code");
                }
                header( 'Status: ' . $strHttpCode  );
            }
            header( 'Location: ' . $strUrl );
        } 
        return '<meta http-equiv="refresh" content=\'0;url="' 
            . htmlspecialchars( $strUrl ) . '"\' />';
    }
}
