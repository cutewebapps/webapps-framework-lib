<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_CheckEnv_Iconv
{
    public function __construct()
    {
        App_CheckEnv::assert( function_exists("iconv_mime_decode"), 'Inconv extension is not supported');
    }
}