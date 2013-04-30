<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

/**
 * @param string $strClassName
 * @return boolean
 */
function checkClassIsLoaded( $strClassName )
{
    return class_exists( $strClassName );
}

/**
 * Splitt class name into parts and return them
 * Those parts will be joined to define real class path
 * 
 * @param string $strClassName
 * @return array
 * @throws Exception
 */
function getClassParts( $strClassName )
{
    $arrParts = explode('_', $strClassName);
    if (count($arrParts) == 0)
        throw new Exception('ERROR: Empty Class Name');
    
    if ( count($arrParts) == 1 && strstr( $strClassName, '\\' ) ) {
        // throw new Exception('ERROR: CLASS='. $strClassName );
        return explode( '\\', $strClassName );
    }
    
    $strLastPart = $arrParts[count($arrParts) - 1];
    if (count($arrParts) >= 1 ) {
        if (substr($strLastPart, -4) == 'Ctrl')
                $arrParts[0] .= '/ctrl';
        else if (substr($strLastPart, -10) == 'CtrlPlugin') {
                $arrParts[0] .= '/ctrl/plugin';
                unset( $arrParts[ count( $arrParts ) - 1 ] );
        } else if (substr($strLastPart, -6) == 'Plugin') {
                $arrParts[0] .= '/plugin';
        } else if (substr($strLastPart, -6) == 'Helper') {
                $arrParts[0] .= '/helper';
                $arrParts[ count( $arrParts ) - 1 ] = substr( $strLastPart, 0, strlen($strLastPart) -6 );
        } else
                $arrParts[0] .= '/model';
    } else {
        // if class name is a single part,
        $arrParts[0] = 'model/'.$strLastPart.'/Base.php';
    }
    
    return $arrParts;
}

/**
 * Autoloade for framework classes
 * @param string $strClassName
 * @return boolean
 * @throws Exception
 */
function __cwa_autoload($strClassName) 
{
    // hack check for sanitizing paths...
    if ( preg_match( '@[\/\.]@', $strClassName )) 
        throw new Exception( 'Class Name '.$strClassName.' could not be allowed');

    
    $strPath = str_replace( "\\", "/", trim( CWA_DIR_CLASSES ) ) 
            . '/' . implode('/', getClassParts( $strClassName) ) . '.php';
       // if ( PHP_SAPI == "cli") {
       //      die( 'PATH: '. $strPath );
       // }
    if (file_exists($strPath))  {
        require $strPath; 
        // die( $strPath .' included' );
        return true;
    }
    return false;
}

spl_autoload_register('__cwa_autoload');
