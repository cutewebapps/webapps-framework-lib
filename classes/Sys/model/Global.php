<?php

class Sys_Global
{
    protected static $_arrValues = array();

    public static function isRegistered( $strKey )
    {
        return isset( self::$_arrValues[ $strKey ] );
    }

    public static function get( $strKey, $default = null )
    {
        return isset( self::$_arrValues[ $strKey ] ) ? self::$_arrValues[ $strKey ] : $default ;
    }

    public static function set( $strKey, $value )
    {
        return self::$_arrValues[ $strKey ] = $value;
    }
}