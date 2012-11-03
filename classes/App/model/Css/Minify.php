<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_Css_Minify 
{
    public $strCss;
    
    public function __construct( $strCss)
    {
        $this->strCss = $strCss;
    }
    
    public function parse() 
    {
        // minification of the CSS
        $strCss = preg_replace( '@/\*(.+)\*'.'/@simU', '', $this->strCss ); // remove comments
        $strCss = preg_replace( '/\s*,\s*/sim',    ',',   $strCss );  // remove trailing space after comma
        $strCss = preg_replace( '/\s*;\s*/sim',    ';',   $strCss );  // remove trailing space after semicolon
        $strCss = preg_replace( '/\s*:\s*/sim',    ':',   $strCss );  // remove trailing space after colon
        $strCss = preg_replace( '/\s*>\s*/sim',    ':',   $strCss );  // remove trailing space after >

        $strCss = preg_replace( '/\s+/sim',      ' ',   $strCss );  // collapse space
        $strCss = preg_replace( '/}\s+/sim',     "}\n", $strCss ); // add line breaks
        $strCss = preg_replace( "/\n$/sim",      '',    $strCss ); // remove last break
        $strCss = preg_replace( '/\s*{\s*'.'/sim',  '{',  $strCss ); // trim inside brackets
        $strCss = preg_replace( '/;\s*}/sim',    '}',   $strCss ); // trim inside brackets
        $strCss = str_replace( ';}', '}', $strCss );              // remove extra char    
        
        $this->strCss = $strCss;
        return $this->strCss;
    }
}