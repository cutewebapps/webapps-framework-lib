<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */
class App_FooterHelper extends App_ViewHelper_Abstract
{
    public function footer()
    {
        return $this->getView()->broker()->FooterScript()->get() . "\n\n";
    }
}