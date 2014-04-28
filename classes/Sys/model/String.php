<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class Sys_String {
    
    /**
     * return result of regular expression
     * @param string $r - regular expression
     * @param string $co - contents to parse
     * @param int or array of int $n - index of required element
     */
    public static function x($r, $co, $n = 1)
    {
        if (preg_match($r, $co, $m)) {
            if ( !is_array( $n ) ) {
                return $m[$n];
            } else {
                $arrRow = array();
                foreach( $n as $index ) $arrRow [] = $m[ $index ];
                return $arrRow;
            }
        }
        return '';
    }
    
    /**
     * return array of results given by single preg_match
     * except 0 result
     * @return array
     * @param string $r regular expression
     * @param string $co contents
     */
    public static function xMatch($r, $co)
    {
        $e = array();
        if (preg_match($r, $co, $m))
            for ($i = 1; $i < count($m); $i ++) {
                $e[] = $m[$i];
            }
        return $e;
    }
    /**
     * @return array
     * @param string $r  regular expression
     * @param string $co contents
     * @param int    $n  index of elements to extract
     */ 
    public static function xAll($r, $co, $n = 1)
    {
        $e = array();
        preg_match_all($r, $co, $m);
        for ($i = 0; $i < count($m[0]); $i ++) {
            if ( !is_array( $n ) ) {
                $e[] = $m[$n][$i];
            } else {
                $arrRow = array();
                foreach( $n as $index ) $arrRow [] = $m[ $index ][ $i ];
                $e[] = $arrRow;
            }
        }
        return $e;
    }

    /**
     * whether date in the string is empty 
     * @return bool
     * @param unknown_type $dt
     */
    public static function isEmptyDate( $dt ) {
         return ( $dt == '' || substr( $dt, 0, 10 ) == '0000-00-00' );
    }
    
    /**
     * Check whether given parameter is a valid email string
     * @param string $strEmail 
     * @return boolean 
     */
    public static function isEmail( $strEmail )
    {
        $regex = '/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';
        return preg_match( $regex, $strEmail );
    }

/**
     * translates dashed lower case string into Camel case
     * widely used in class, packages, uri, and function namings
     * @param string $str
     */
    public static function toCamelCase( $str, $chReplaceDash  = '' ) {
        $o = ''; $bNextUpper = true;
        for($i = 0; $i < strlen ( $str ); $i ++) {
            $ch = substr ( $str, $i, 1 );
            
            if ($i > 0 && $ch == '-' ) {
                /*$o .= '-'; */
                $o .= $chReplaceDash;
                $bNextUpper = true;
            } else {
                if ( $bNextUpper ) {
                    $o .= strtoupper ( $ch );
                    $bNextUpper = false;
                } else {
                    $o .= strtolower ( $ch );
                }
            }
        }
        return $o;     
    }
    
    /**
     * translates camel case string into dashed lower case 
     * widely used in class, packages, uri, and function namings
     * @param string $str
     */
    public static function toLowerDashedCase( $str ) {
        $o = '';
        for($i = 0; $i < strlen ( $str ); $i ++) {
            $ch = substr ( $str, $i, 1 );
            if ($i > 0 && $ch == strtoupper ( $ch ) && 
                ! preg_match ( '/^\d+$/', $ch ))
                    $o .= '-';
            $o .= strtolower ( $ch );
        }
        return $o;
    }

    /**
     * get database quoted string
     * @return string 
     * @param string $str
     */
    public static function quote( $str ) {
        return '\''.str_replace( '\'', '\'\'', $str ).'\'';
    }
    
    public static function getAutoSlug( $strSlug ) {
        $s = $strSlug;
        $s = preg_replace( '/^\s+/sim', '', $s );
        $s = preg_replace( '/\s+$/sim', '', $s );
        $s = preg_replace( '/\&/sim', '-and-', $s );
        $s = preg_replace( '/[^-_0-9A-Za-z]+/sim', ' ', $s );
        $s = preg_replace( '/\s+/sim', '-', $s );
        $s = preg_replace( '/--/sim', '-', $s );
        $s = preg_replace( '/-+$/sim', '', $s );
        $s = preg_replace( '/^-+/sim', '', $s );
        return strtolower( $s );
    }  
}