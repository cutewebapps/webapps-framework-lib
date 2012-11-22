<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

abstract class App_WebsiteCtrl extends App_AbstractCtrl
{
    abstract public function pageNotFoundAction();

    abstract public function accessDeniedAction();

    abstract public function serverErrorAction();
}