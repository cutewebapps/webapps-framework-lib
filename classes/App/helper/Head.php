<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */
class App_HeadHelper extends App_ViewHelper_Abstract
{
    /** 
     * output for the head
     * @return string
     */
    
    public function head()
    {
        $b = $this->getView()->broker();

        // in future: append additional classes from local namespaces
        return $b->headMeta()->get()
             . $b->headTitle()->get()
             . $b->headLink()->get()
             . $b->headStyle()->get()
             . $b->headScript()->get() . "\n\n";
    }
}