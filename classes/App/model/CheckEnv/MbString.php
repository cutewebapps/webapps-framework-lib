<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_CheckEnv_MbString
{
    public function __construct()
    {
        App_CheckEnv::assert( fucntion_exists("mb_check_encoding"), 'MB String extension is not supported');
        
    }
}