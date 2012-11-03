<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class Sys_Sql_Beautifier
{
    protected $strSQL = '';
    protected $_strResult = '';
    protected $nPos = 0;
    protected $nLength = 0;

    public function __construct( $strSQL = '' )
    {
        $this->strSQL = $strSQL;
        $this->_strResult = '';
    }

    protected function _newLine( $nChars = 0 )
    {
        $this->_strResult .= "\n".str_repeat( "  ", $nChars );
    }
    protected function _append( $str )
    {
        $this->_strResult .= $str;
    }

    public function toString()
    {
        $nIndent = 1;
        $this->nPos = 0;
        $this->nLength = strlen( $this->strSQL );
        $this->_strResult = '';
        while ( $this->nPos <= $this->nLength ) {
            $ch = substr( $this->strSQL, $this->nPos, 1 );
            if ( $ch == '(' ) {

                // $this->_newLine( ++ $nIndent );
                $this->_append( $ch );
                $this->_newLine( ++$nIndent );

                $this->nPos ++;
                
            } else if ( $ch == ')' ) {

                $this->_newLine( --$nIndent );
                $this->_append( $ch );
                
                $this->nPos ++;
                
            } else if ( preg_match( '@^(FROM)\s@i',
                    substr( $this->strSQL, $this->nPos ), $arrMatch ) ) {
                
                $this->_newLine( $nIndent+1 );
                $this->_append( $arrMatch[1].' ' );
                $this->nPos += strlen( $arrMatch[1] );


            } else if ( preg_match( '@^(SELECT|FROM|INNER JOIN|INNER|LEFT JOIN|JOIN|WHERE|HAVING|ORDER|AND|OR)\s@i',
                    substr( $this->strSQL, $this->nPos ), $arrMatch ) ) {
                $this->_newLine( $nIndent );
                $this->_append( $arrMatch[1].' ' );
                $this->nPos += strlen( $arrMatch[1] );

            } else {
                $this->_append( $ch );
                $this->nPos ++;
            }
        }
        

        return $this->_strResult;
    }
}