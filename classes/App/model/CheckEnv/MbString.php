<?php

class App_CheckEnv_MbString
{
    public function __construct()
    {
        App_CheckEnv::assert( fucntion_exists("mb_check_encoding"), 'MB String extension is not supported');
        
    }
}