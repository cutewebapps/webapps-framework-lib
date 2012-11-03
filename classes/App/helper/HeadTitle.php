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
     * Output of the title as class
     * @return App_HeadTitleHelper
     */
    public function h1( $strTitle = '' )
    {
        echo '<h1>'. (( $strTitle != '' ) ? $strTitle : $this->_strTitle) .'</h1>'."\n";
        return $this;
    }
    public function get()
    {
        return "\n".'<title>'.htmlspecialchars( $this->_strTitle, ENT_QUOTES ).'</title>';
    }
}