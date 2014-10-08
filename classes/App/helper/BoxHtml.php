<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_BoxHtmlHelper extends App_ViewHelper_Abstract
{
    protected static $_instance = null;

    protected $_arrHash = array();

    public function boxhtml()
    {
        return self::getInstance();
    }
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

    /** @return App_BoxHtmlHelper */
    public function setHtml( $strBox, $strValue )
    {
        $this->_arrHash[ $strBox ] = $strValue;
        return $this;
    }

    /** @return string */
    public function getHtml( $strBox )
    {
        if ( isset( $this->_arrHash[ $strBox ] ) )
            return $this->_arrHash[ $strBox ];
        return '';
    }

    /** @return App_BoxHtmlHelper */
    public function addHtml( $strBox, $strValue )
    {
	$arrExisting = isset( $this->_arrHash[ $strBox ] ) ? explode( " ", trim( $this->_arrHash[ $strBox ] ) ) : array();
	$arrNew = explode( " ", trim( $strValue ) );
	foreach( $arrNew as $sCssHtml ) {
	    if ( !in_array( $sCssHtml, $arrExisting ) ) {
		$arrExisting[] = $sCssHtml;
	    } 		
	}
	$this->_arrHash[ $strBox ] = implode( " ", $arrExisting );
	return $this;
    }

}
