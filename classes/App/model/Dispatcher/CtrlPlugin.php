<?php

abstract class App_Dispatcher_CtrlPlugin
{
    /** @var App_Dispatcher */
    protected $_dispatcher = null;

    public function  __construct( $objDispatcher )
    {
        $this->_dispatcher = $objDispatcher;
    }
    /** @var App_Dispatcher */
    public function getDispatcher() { return $this->_dispatcher; }

    /** for children overloading */
    public function preDispatch() {}
    /** for children overloading */
    public function postDispatch() {}

    /** get all methods of current controller */
    public function __call( $name, $arguments )
    {
        return call_user_func_array( array($this->getDispatcher()->getController(), $name), $arguments );
    }
}