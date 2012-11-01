<?php

class App_ViewHelper_Abstract
{
    /*
     * @var App_View
     */
    protected $_view = null;
    /*
     * @param $view App_View
     */
    public function setView( App_View $view )
    {
        $this->_view = $view;
    }
    /**
     * @return App_View
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * View Class will override all other methods...
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call( $name, $arguments = array() )
    {
        return call_user_func_array( array($this->getView(), $name), $arguments );
    }
}