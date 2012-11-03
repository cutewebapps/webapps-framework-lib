<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

/**
 * Sys_Io - some universal output
 */

class Sys_Io {

    /**
     * @return bool
     */
    public static function isCli() 
    {
        return PHP_SAPI == 'cli';
    }
    /**
     * puts EOL to output
     * @return string
     */
    public static function crlf()
    {
        if ( isset( $_SERVER['HTTP_HOST'] ) )
            echo '<br />';
        else
            echo "\n";
    }

    /**
     * @param string $str
     * @param string $strClassName
     * @param array $arrStyle
     * @return void
     */
    public static function out( $str, $strClassName = '', $arrStyle = array() )
    {
        $arrAttr = array();
        if ( $strClassName ) $arrAttr [] = 'class="'.$strClassName.'"';
        if ( count($arrStyle) > 0 ) {
            $arrStyleCss  = array();
            foreach( $arrStyle as $strProp => $strValue ) {
                $arrStyleCss[] = $strProp.':'.$strValue;
            }
            $arrAttr [] = 'style="'.implode( ';', $arrStyleCss ).'"';           
        } 
        $chSpace = '';
        if ( count( $arrAttr ) > 0 ) $chSpace = ' '; 
        
        if ( !self::isCli() )
            echo '<div'.$chSpace.implode(' ',$arrAttr).'>'.$str.'</div>'."\n";
        else 
            echo $str."\n";
    }
    
    /**
     * @return string
     */
    public static function headerXml()
    {
        return '<'.'?xml version="1.0" encoding="utf-8" ?'.'>'."\n";
    }
     
}

