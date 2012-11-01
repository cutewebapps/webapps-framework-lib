<?php

class App_TagClassHelper extends App_ViewHelper_Abstract
{
    /** @return string  - html attribute of class */
    public function tagClass( $arrClasses )
    {
        $arrList = array();
        
        
        foreach ( $arrClasses as $strClass => $bValue ) {
            if ( $bValue ) $arrList[ $strClass ] = $strClass;
        }

        if ( count( $arrList ) > 0 ) {
            return ' class="'.implode( " ", $arrList ).'" ';
        }
        return '';
    }

}