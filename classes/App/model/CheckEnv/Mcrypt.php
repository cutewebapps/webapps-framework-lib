<?php

class App_CheckEnv_MCrypt 
{
    public function __construct()
    {
        App_CheckEnv::assert( extension_loaded("mcrypt"), 'MCrypt extension is not supported');
        
    }
}