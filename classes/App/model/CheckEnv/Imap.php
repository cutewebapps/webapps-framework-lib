<?php

class App_CheckEnv_Imap
{
    public function __construct()
    {
        App_CheckEnv::assert( function_exists("imap_mime_header_decode"), 'IMAP extension is not supported');
    }
}