<?php

class App_PlaceHolderHelper extends App_ViewHelper_Abstract
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

    public function PlaceHolder()
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