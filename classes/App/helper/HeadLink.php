<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_HeadLinkHelper extends App_ViewHelper_Abstract
{
    protected static $_instance = null;
    protected $_arrItems = array();
    protected $_arrLinks = array();

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
    /**
     * @return App_HeadLinkHelper
     */
    public function headLink()
    {
        return self::getInstance();
    }
    /**
     * @return App_HeadLinkHelper
     */
    public function append( $file )
    {
        $this->_arrItems[] = $file;
        return $this;        
    }
    /**
     * @return App_HeadLinkHelper
     */
    public function prepend( $file )
    {
        $this->_arrItems[ - count( $this->_arrItems ) ] = $file;
        ksort( $this->_arrItems );
        return $this;
    }
    /**
     * @return App_HeadLinkHelper
     */
    public function icon( $file )
    {
        $this->_arrLinks [] = array(
            'rel' => 'shortcut icon',
            'href' => $file
        );
        return $this;
    }
    /**
     *
     * @param array $arrProperties
     * @return App_HeadLinkHelper
     */
    public function add( array $arrProperties )
    {
        $this->_arrLinks [] = $arrProperties;
        return $this;
    }
    /**
     * @return string
     */
    public function get()
    {
        $arrStrResults = array();
        foreach( $this->_arrItems as $strFile )
            $arrStrResults[ $strFile ] = '<link type="text/css" rel="stylesheet" href="'.$strFile.'" />';
        
        foreach ( $this->_arrLinks as $arrProps ) {
            $arrHtmlAttr = array();
            foreach( $arrProps as $strKey => $strVal ) { $arrHtmlAttr[] = $strKey.'="'.$strVal.'"'; }
            $arrStrResults[] = '<link '.implode( " ", $arrHtmlAttr).'/>';
        }
        
        return "\n".implode( "\n", $arrStrResults );
    }
}