<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_CheckEnv_Tidy
{
    public function __construct()
    {
        App_CheckEnv::assert( function_exists("tidy_parse_string"), 'Tidy (pecl) extension is not supported');
    }
}