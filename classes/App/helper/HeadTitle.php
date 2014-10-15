<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_HeadTitleHelper extends App_ViewHelper_Abstract
{
    protected static $_instance = null;
    protected $_strTitle = '';
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
     * @return App_HeadTitleHelper
     */
    public function headTitle()
    {
        return self::getInstance();
    }
    
    /**
     * @return App_HeadTitleHelper
     */
    public function set( $strTitle )
    {
        $this->_strTitle = $strTitle;
        return $this;
    }
    
    /**
     * @return boolean
     */
    public function hasTitle()
    {
        return ( $this->_strTitle != '' );
    }

    /**
     * @return string
     */
    public function _getAttributesFromArray( $arrAttr )
    {
	$arrHtml = array();
        foreach ( $arrAttr as $key => $value )     {
		$arrHtml []= $key.'="'.$value.'"';
        }
	return " ".implode( " ",$arrHtml );
    }

    /**
     * Output of the title as H1-tag
     * @return App_HeadTitleHelper
     */
    public function h1( $strTitle = '', $arrAttr = array() )
    {
	$sAttr = $this->_getAttributesFromArray( $arrAttr );
        echo '<h1'.$sAttr.'>'. (( $strTitle != '' ) ? $strTitle : $this->_strTitle) .'</h1>'."\n";
        return $this;
    }
    
    /**
     * Output of the title as h2 tag (useful with some css frameworks)
     * @return App_HeadTitleHelper
     */
    public function h2( $strTitle = '', $arrAttr = array() )
    {
	$sAttr = $this->_getAttributesFromArray( $arrAttr );
        echo '<h2'.$sAttr.'>'. (( $strTitle != '' ) ? $strTitle : $this->_strTitle) .'</h2>'."\n";
        return $this;
    }
    
    /**
     * Output of the title as h2 tag (useful  with some css frameworks)
     * @return App_HeadTitleHelper
     */
    public function h3( $strTitle = '', $arrAttr = array() )
    {
	$sAttr = $this->_getAttributesFromArray( $arrAttr );
        echo '<h3'.$sAttr.'>'. (( $strTitle != '' ) ? $strTitle : $this->_strTitle) .'</h3>'."\n";
        return $this;
    }
    
    /**
     * Get helper output in HEAD-tead
     * @return string
     */
    public function get()
    {
        return "\n".'<title>'.htmlspecialchars( $this->_strTitle, ENT_QUOTES ).'</title>';
    }
}