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
     * Output of the title as H1-tag
     * @return App_HeadTitleHelper
     */
    public function h1( $strTitle = '' )
    {
        echo '<h1>'. (( $strTitle != '' ) ? $strTitle : $this->_strTitle) .'</h1>'."\n";
        return $this;
    }
    
    /**
     * Output of the title as h2 tag (useful with some css frameworks)
     * @return App_HeadTitleHelper
     */
    public function h2( $strTitle = '' )
    {
        echo '<h2>'. (( $strTitle != '' ) ? $strTitle : $this->_strTitle) .'</h2>'."\n";
        return $this;
    }
    
    /**
     * Output of the title as h2 tag (useful  with some css frameworks)
     * @return App_HeadTitleHelper
     */
    public function h3( $strTitle = '' )
    {
        echo '<h3>'. (( $strTitle != '' ) ? $strTitle : $this->_strTitle) .'</h3>'."\n";
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