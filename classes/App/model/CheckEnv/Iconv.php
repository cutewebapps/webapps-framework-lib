<?php

class App_CheckEnv_Iconv
{
    public function __construct()
    {
        App_CheckEnv::assert( function_exists("iconv_mime_decode"), 'Inconv extension is not supported');
    }
}