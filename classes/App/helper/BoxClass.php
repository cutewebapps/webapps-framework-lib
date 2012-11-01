<?php

class App_BoxClassHelper extends App_ViewHelper_Abstract
{
    protected static $_instance = null;

    protected $_arrHash = array();

    public function boxclass()
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

    /** @return App_BoxClassHelper */
    public function setClass( $strBox, $strValue )
    {
        $this->_arrHash[ $strBox ] = $strValue;
        return $this;
    }

    /** @return string */
    public function getClass( $strBox )
    {
        if ( isset( $this->_arrHash[ $strBox ] ) )
            return $this->_arrHash[ $strBox ];
        return '';
    }

}