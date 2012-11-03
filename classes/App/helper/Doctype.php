<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */
class App_DoctypeHelper extends App_ViewHelper_Abstract
{
    public function doctype( $strDocType = 'HTML4' )
    {
        switch( $strDocType ) 
        {
            case 'HTML4':
                return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'
                   ."\n";
            default:
                return '<!DOCTYPE html>';
        }
    }
        
}