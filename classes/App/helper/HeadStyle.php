<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_HeadStyleHelper extends App_ViewHelper_Abstract
{
    protected static $_instance = null;
    protected $_arrItems = array();
    /**
     * @return App_Layout
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function headStyle()
    {
        return self::getInstance();
    }
    public function append( $strCss, $strWrapper = '' )
    {
        if ( $strWrapper != '' && $strCss != '' ) $strCss =  $strWrapper .'{'.$strCss."\n}\n";
        $this->_arrItems[] = $strCss;
        return $this;
    }
    public function prepend( $strCss, $strWrapper = '' )
    {
        if ( $strWrapper != '' && $strCss != '' ) $strCss =  $strWrapper .'{'.$strCss."\n}\n";
        $this->_arrItems[ - count( $this->_arrItems ) ] = $strCss;
        ksort( $this->_arrItems );
        return $this;
    }
    public function appendLess( $strLess, $strWrapper = '' )
    {
        $less = new App_Less_Compiler();
        $strCss = $less->parse( $strLess );
        if ( $strWrapper != '' && $strCss != '' ) $strCss =  $strWrapper .'{'.$strCss."\n}\n";
        
        $this->_arrItems[] = $strCss;
        return $this;
    }
    public function prependLess( $strLess, $strWrapper = '' )
    {
        $less = new App_Less_Compiler();
        $strCss = $less->parse( $strLess );
        if ( $strWrapper != '' && $strCss != ''  ) $strCss =  $strWrapper .'{'.$strCss."\n}\n";
        
        $this->_arrItems[ - count( $this->_arrItems ) ] = $strCss;
        ksort( $this->_arrItems );
        return $this;
    }    
    public function get( $bMinify = true )
    {
        if ( count( $this->_arrItems ) == 0 ) return '';

        $arrStrResults = array();
        foreach( $this->_arrItems as $strCss ) {

            if ( $bMinify )  {
                $min = new App_Css_Minify( $strCss );
                $strCss = $min->parse();
            }

            if ( trim( $strCss ) != '' )
                $arrStrResults[] = trim( $strCss );
        }
        
        if ( count( $arrStrResults ) == 0 ) return "";
        return "\n<style type=\"text/css\">\n<!--\n"
                .implode( "\n", $arrStrResults )
                ."\n-->\n".'</style>'."\n";
    }
}