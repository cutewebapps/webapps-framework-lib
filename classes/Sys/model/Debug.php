<?php

define( 'DEBUG_COOKIE_NAME', 'debug_123123' );

class Sys_Debug
{
    public static function dumpRequest( $strFile  = '' )
    {
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
        echo $backtrace[2]['file'] . ' in line ' . $backtrace[2]['line'];
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

    public static function dumpDie( $arr  = '', $strCaption = "" )
    {
        self::dump( $arr, $strCaption );
        die;
    }
}
