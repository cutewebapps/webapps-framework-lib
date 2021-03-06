<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

/**
 * View Helper for getting main namespace in the project
 * (ns parameter in the config)
 */

class App_NsHelper extends App_ViewHelper_Abstract
{
    protected $strProjectNs = '';
    /**
     *  @return string 
     */
    public function ns()
    {
        $this->strProjectNs = App_Application::getInstance()->getConfig()->ns;
        return $this;
    }
    /**
     * @return string
     */
    public function __toString()
    {
        return Sys_String::toLowerDashedCase( $this->strProjectNs );
    }
   
    /**
     * @return mixed
     * @warning: 5.3+ function
     */
    public function auth()
    {
        $strAuthClass = $this->strProjectNs .'_Auth';
        return $strAuthClass::getInstance();
    }
    /**
     * @return mixed
     */
    public function broker()
    {
        return $this->getView()->broker( $this->strProjectNs );
    }
    
    /**
     * all undefined functions in namespace are assumed to be a call to a broker
     * @return mixed
     */
    public function __call( $name, $arguments = array() )
    {
        return call_user_func_array( array($this->broker(), $name), $arguments );
    }
}