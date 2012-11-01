<?php

class Sys_Io {
    
    static protected $_instance = null;

    /**
     * @return bool
     */
    public static function isCli() 
    {
        return PHP_SAPI == 'cli';
    }
    /**
     * puts EOL to output
     * @return void
     */
    public static function crlf()
    {
        if ( isset( $_SERVER['HTTP_HOST'] ) )
            echo '<br />';
        else
            echo "\n";
    }

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
            echo '<div'.$chSpace.implode(' ',$arrAttr).'>'.$str.'</div>';
        else 
            echo $str."\n";
    }
    
    public static function headerXml()
    {
        return '<'.'?xml version="1.0" encoding="utf-8" ?'.'>'."\n";
    }
    
    /**
     * get instance of the standard 
     * @return Sys_Io
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
}

