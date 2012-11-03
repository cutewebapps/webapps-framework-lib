<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_ViewHelper_Broker extends App_ViewHelper_Abstract
{
    protected $_strNamespace = 'App';

    public function __construct( $strNamespace = 'App' )
    {
        $this->setNamespace( $strNamespace );
    }

    public function setNamespace( $strNamespace )
    {
        $this->_strNamespace = $strNamespace;
    }

    public function __call( $strName, $arguments = array() )
    {

        $strClass  = $this->_strNamespace . '_' . ucfirst( $strName ).'Helper';
        if ( class_exists( $strClass ) ) {
            $objViewHelper = new $strClass();
            $objViewHelper->setView( $this->getView() );
            return call_user_func_array(array($objViewHelper, $strName), $arguments);
        } else {
            throw new App_Exception( 'ViewHelper Class not found ' .$strClass );
        }
    }
}