<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_CheckEnv_Dom
{
    public function __construct()
    {
        App_CheckEnv::assert( extension_loaded("dom"), 'DOM extension is not supported');
    }
}