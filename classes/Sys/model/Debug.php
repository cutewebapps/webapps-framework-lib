<?php


/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

define( 'DEBUG_COOKIE_NAME', 'debug_123123' );

class Sys_Debug
{
    public static function dumpRequest( $strFile  = '' )
    {
        global $HTTP_RAW_POST_DATA;
        ob_start();
        
        Sys_Io::out( 'GET' );       print_r( $_GET );
        Sys_Io::out( 'COOKIE' );    print_r( $_COOKIE );
        Sys_Io::out( 'POST' );      print_r( $_POST );
        Sys_Io::out( 'SERVER' );    print_r( $_SERVER );
        Sys_Io::out( 'ENV' );       print_r( $_ENV );

        if ( isset( $HTTP_RAW_POST_DATA )) {
            Sys_Io::out( 'HTTP_RAW_POST_DATA' );
            Sys_Io::out( $HTTP_RAW_POST_DATA );
        }
        $strInput = file_get_contents( 'php://input' );
        if ( $strInput) {
            Sys_Io::out( 'php://input' );
            Sys_Io::out( $strInput );
        }
        
        $strContents = ob_get_contents();
        ob_end_clean();

        if ( $strFile != '' ) {
            $file = new Sys_File( $strFile );
            $file->save( $strContents );
        }
    }
    
    public static function dumpToFile( $obj, $strFile = '' )
    {
        ob_start();
        Sys_Debug::dumpPlain( $arr, $strCaption );
        $strContents = ob_get_contents();
        ob_end_clean();

        if ( $strFile != '' ) {
            $file = new Sys_File( $strFile );
            $file->save( $strContents );
        }
    }


    public static function dumpHtml( $x, $caption = '' )
    {
            $backtrace = debug_backtrace();
            $head_color = '#777';
            echo '<pre style="text-align:left; padding: 3px; border:1px solid #bcbcbc; background: #fff;">';
            $head = '';
            if ($caption != '') $head .= '<strong>' . $caption . '</strong>  '; else $head .= '<strong>DEBUG</strong>';
            $head .='  <span style="font: 10px/1.3 Tahoma;">at <strong>'
                    . $backtrace[1]['file'] . ' in line ' . $backtrace[1]['line'] . '</strong></span>';
            echo '<h4 style="padding: 2px 5px; margin:0 0 10px 0; text-align:left; font:14px/1.4 Tahoma;background: '
                    .$head_color.';color: #fff;">' . $head . '</h4>';
            print_r( $x );
            echo '</pre>';
    }

    public static function dumpXml( $arr )
    {
	    $arrResult = array();
	    foreach ($arr as $key => $value) {
	         if (is_array($value)) {
	             $arrResult[] = '<'.$key.'>'."\n".self::dumpXml($value).'</'.$key.'>';
			 } else {
	             $arrResult[] = '<'.$key.'>'.htmlspecialchars( $value, ENT_QUOTES ).'</'.$key.'>';
			 }
		}
		echo implode( "\n",$arrResult);
    }
    
    public static function dumpPlain( $arr, $caption = '' )
    {
        echo "\n\n";
        if ( $caption != "" ) echo $caption."\n";

        $backtrace = debug_backtrace();
	if ( isset( $backtrace[1] )) {
	        echo $backtrace[1]['file'] . ' in line ' . $backtrace[1]['line'];
	}
    	print_r( $arr );
        
    }

    public static function dump( $arr, $strCaption = "" )
    {
        if ( PHP_SAPI == "cli")
            self::dumpPlain( $arr, $strCaption );
        else
            self::dumpHtml( $arr, $strCaption );
        
        $confException = App_Application::getInstance()->getConfig()->exceptions;
        if ( is_object( $confException ) && $confException->on_debug ) {
            throw new App_Exception("Debugging is forbidden on this environment" );
        }
    }
    
    public static function quoteWrap($var){
	switch(gettype($var)){
            case 'string':
                return '\''.$var.'\'';
            case 'NULL':
                return "null";
            default :
                return $var;
	}
    }

    /**
     * Get dump of array in PHP syntax
     * @param array $arr
     * @param integer $depth 
     * @return string
     */
    public static function dumpPhp( $arr, $depth=0 )
    {
        $string = '';
        $string .= "array( \n";
        $depth++;
        foreach( $arr as $key => $val){
                $string .= str_repeat('    ',$depth).self::quoteWrap($key). ' => ';
                if(is_array($val)){
                    $string .= self::dumpPhp($val,$depth).",\n";
                } else {
                    $string .= self::quoteWrap($val).",\n";
                }
        }
        $depth--;
        $string .= str_repeat('    ',$depth).")";
        
        if ( $depth != 0 ) return $string;
        return self::dumpHtml( $string );
    }

    
    public static  function dumpTable( $arr )
    {
        $arrColumns = array();
        $arrColumnsIndex = array();
        $nMaxColumn = 1;
        foreach ( $arr as $keyRow => $arrFields ) {
            foreach ( $arrFields as $key => $value ) {
                if ( !isset( $arrColumns[ $key ] ) ) {
                    $arrColumns[ $key ] = '<th>'.$key.'</th>';
                    $arrColumnsIndex[ $nMaxColumn  ] = $key;
                    $nMaxColumn ++;
                }
            }
        }
        
        $strOut = '<table border cellspacing="0" cellpaddin="3" class="table table-bordered table-striped"><thead><tr><th>#</th>'.implode( "", $arrColumns ).'</tr></thead>';
        $strOut .= '<tbody>';
        foreach ( $arr as $keyRow => $arrFields ) {
            $strOut .= '<tr><td>'.$keyRow.'</td>';
            for( $i = 1; $i < $nMaxColumn; $i ++ ) {
                $strKey = $arrColumnsIndex[ $i  ];
                if ( isset( $arrFields[ $strKey ] ) ) $strOut .= '<td>'.$arrFields[ $strKey ].'</td>';
            }
            $strOut .= '</tr>'."\n";
        }
        $strOut .= '</tbody></table>';
        
        return $strOut;
    }
    
    /**
     * Get dump of array in PHP syntax
     *
     * @param <type> $strSQL
     * @return <type>
     */
    public static function dumpSql( $strSQL )
    {
        $sqlFormatter = new Sys_Sql_Beautifier( $strSQL );
        return self::dumpHtml( $sqlFormatter->toString() );
    }

    /** 
     * @warning: please avoid this function, user dump + die instead separately,
     * reason: traceback could be useless
     * 
     * @param type $arr
     * @param type $strCaption
     */
    public static function dumpDie( $arr  = '', $strCaption = "" )
    {
        self::dump( $arr, $strCaption );
        die;
    }
    
    /**
     * 
     * @param mixed $arr
     * @param string $strCaption
     */
    public static function alert( $sContent, $strPath = '' ) 
    {

        if ( Sys_Global::isRegistered( "DISABLE_ALERTS" ) ) {
            // avoid recursion on exceptions, do not produce alerts on alerts
            return;
        }
        $objAppConfig = App_Application::getInstance()->getConfig()->alert;
        if ( !is_object( $objAppConfig )) return;


        $strHtml  = $sContent; 
        $strPlain  = $sContent;
        
        if ( is_array( $sContent ) || is_object( $sContent ) ) {
            ob_start(); 
            self::dumpPlain( $sContent );
            $strPlain = ob_get_contents();
            ob_end_clean();
            
            ob_start(); 
            self::dumpHtml( $sContent );
            $strHtml = ob_get_contents();
            ob_end_clean();
            
        }
        
        //  alert could be saved in logs ( ->alert_log )
        if ( $objAppConfig->log ) {
            $logFile = new Sys_File( $objAppConfig->log );
            $logFile->append( "\n\n[".date("Y-m-d H:i:s")."]\n" . $strPlain );
        }
        
        // sending alert to a server
        Sys_Global::set( "DISABLE_ALERTS", 1);
        if ( $objAppConfig->server ) {
            
            if ( is_string( $objAppConfig->server ) )
                $arrServers = array( $objAppConfig->server );
            else
                $arrServers = $objAppConfig->server->toArray();
                        
            foreach( $arrServers as $strServer ) {
                try {
                    $browser = new App_Http_Browser();
                    $browser->ConnectTimeout  = 3;
                    $browser->DownloadTimeout  = 5;
                    $browser->httpPostRaw( $strServer.$strPath, $strHtml );
                } catch ( Exception $e ) {
                    // not reaching the server is not a problem to stop at 
                }
            }
        }
        Sys_Global::set( "DISABLE_ALERTS", "");
    }
    
    
     /**
     * 
     * @param mixed $arr
     * @param string $strCaption
     */
    public static function alertHtml( array $arrProperties, $sContent1, $sContent2 = '', $sContent3 = '', $sContent4 = '', $sContent5 = '' ) 
    {
        if ( Sys_Global::isRegistered( "DISABLE_ALERTS" ) ) {
            // avoid recursion on exceptions, do not produce alerts on alerts
            return;
        }
        $objAppConfig = App_Application::getInstance()->getConfig()->alert;
        if ( !is_object( $objAppConfig )) return;
        
        //  alert could be saved in logs ( ->alert_log )
        if ( $objAppConfig->log ) {
            $logFile = new Sys_File( $objAppConfig->log );
            $logFile->append( "\n\n[".date("Y-m-d H:i:s")."]\n" . print_r( $arrProperties, true )."\n"
                    . implode( "\n", array( $sContent1, $sContent2, $sContent3, $sContent4, $sContent5 ) ) );
        }
        
        $strPath = 'alert';
        if ( isset( $arrProperties['alert'] ) ) {
            $strPath = $arrProperties['alert'];
            unset( $arrProperties['alert'] );
        }
        
        // sending alert to a server
        Sys_Global::set( "DISABLE_ALERTS", 1);
        if ( $objAppConfig->server ) {
            
            if ( is_string( $objAppConfig->server ) )
                $arrServers = array( $objAppConfig->server );
            else
                $arrServers = $objAppConfig->server->toArray();
                        
            foreach( $arrServers as $strServer ) {
                try {
                    $browser = new App_Http_Browser();
                    $browser->ConnectTimeout  = 3;
                    $browser->DownloadTimeout  = 5;
                    
                    $arrResult = $arrProperties;
                    if ( $sContent1 ) $arrResult[ 'HTML_CONTENTS_1' ] = $sContent1;
                    if ( $sContent2 ) $arrResult[ 'HTML_CONTENTS_2' ] = $sContent2;
                    if ( $sContent3 ) $arrResult[ 'HTML_CONTENTS_3' ] = $sContent3;
                    if ( $sContent4 ) $arrResult[ 'HTML_CONTENTS_4' ] = $sContent4;
                    if ( $sContent5 ) $arrResult[ 'HTML_CONTENTS_5' ] = $sContent5;
                    
                    $browser->httpPostRaw( $strServer.$strPath, json_encode( $arrResult ) );
                    
                } catch ( Exception $e ) {
                    // not reaching the server is not a problem to stop at 
                }
            }
        }
        Sys_Global::set( "DISABLE_ALERTS", "");
    }
}
