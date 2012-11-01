<?php

class App_CheckEnv_Dom
{
    public function __construct()
    {
        App_CheckEnv::assert( extension_loaded("dom"), 'DOM extension is not supported');
    }
}