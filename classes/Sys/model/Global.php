<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

/**
 * Sys_Global - container for global variables
 */
class Sys_Global
{
    /**
     * @var array
     */
    protected static $_arrValues = array();

    /**
     * 
     * @param string $strKey
     * @return boolean
     */
    public static function isRegistered( $strKey )
    {
        return isset( self::$_arrValues[ $strKey ] );
    }

    /**
     * 
     * @param string $strKey
     * @param mixed $default
     * @return mixed
     */
    public static function get( $strKey, $default = null )
    {
        return isset( self::$_arrValues[ $strKey ] ) 
            ? self::$_arrValues[ $strKey ] : $default ;
    }

    /**
     * 
     * @param string $strKey
     * @param mixed $value
     * @return void
     */
    public static function set( $strKey, $value )
    {
        self::$_arrValues[ $strKey ] = $value;
    }
}