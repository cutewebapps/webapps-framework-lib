<?php

function checkClassIsLoaded( $strClassName )
{
    return class_exists( $strClassName );
}


function __autoload($strClassName) 
{
    $arrParts = explode('_', $strClassName);

    if (count($arrParts) == 0)
        die('ERROR: Empty Class Name');

    if ($arrParts[0] == 'Zend') {
        throw new Exception( 'calling to Zend Class '.$strClassName );
        // actually we will never be here... (even if zend framework will be autoloaded)
        return true;
    } else {
        $strLastPart = $arrParts[count($arrParts) - 1];

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

        $strPath = WC_DIR_CLASSES . '/' . implode('/', $arrParts) . '.php';
         // echo ( $strPath ).'<br />';
        if (file_exists($strPath)) {
            // TODO: hack check
            include $strPath;
            return true;
        }
        return false;
    }
}

spl_autoload_register('__autoload');
