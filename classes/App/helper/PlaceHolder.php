<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_PlaceHolderHelper extends App_ViewHelper_Abstract
{
    /**
     * @var App_PlaceHolderHelper
     */
    protected static $_instance = null;
    /**
     * @var array
     */
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
    public function placeHolder()
    {
        return self::getInstance();
    }
    public function start( $strPlaceHolder )
    {
        $this->_arrItems[ $strPlaceHolder ] = '';
        ob_start();
    }
    public function end( $strPlaceHolder )
    {
        $this->_arrItems[ $strPlaceHolder ] .= ob_get_contents();
        ob_end_clean();
    }
    public function get( $strPlaceHolder )
    {
        return $this->_arrItems[ $strPlaceHolder ];
    }
}