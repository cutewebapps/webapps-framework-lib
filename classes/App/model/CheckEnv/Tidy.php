<?php

class App_CheckEnv_Tidy
{
    public function __construct()
    {
        App_CheckEnv::assert( function_exists("tidy_parse_string"), 'Tidy (pecl) extension is not supported');
    }
}