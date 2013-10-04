<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

// require base loader
require CWA_DIR_CLASSES.'/Sys/model/Loader.php';

function cwa_init_esc( $val )
{
    return str_replace("\\'","'",
        str_replace('\\"','"', str_replace( "\\\\", "\\", $val)));
}
function cwa_init_web_application()
{

    if ( defined( 'CWA_DIR_CLASSES' ) )
        Sys_Global::set('ClassesRoot', CWA_DIR_CLASSES );

    // try to defined environment from envir variables or constants
    if ( defined('CWA_ENV') )
          Sys_Global::set( 'Environment', CWA_ENV );
    else if ( getenv( 'CWA_ENV' ) != '' )
          Sys_Global::set( 'Environment', getenv( 'CWA_ENV' ));

    // in Php5.2 sometimes magic quotes are on inspite of everything...
    // magic quoates are deprecated in 5.3
    // check this only for PHP version lower than 5.3
    $bCheckQuotes = true;
    if ( version_compare( PHP_VERSION, '5.3.0' ) > 0 ) {
        $bCheckQuotes = false;
    }
   
    if ( $bCheckQuotes && get_magic_quotes_gpc() == 1 ){
        while (list($key,$val)=each($_POST))    { $_POST[$key]  = cwa_init_esc($val); }
        while (list($key,$val)=each($_GET))     { $_GET[$key]   = cwa_init_esc($val); }
        while (list($key,$val)=each($_REQUEST)) { $_REQUEST[$key] = cwa_init_esc($val); }
    }

    App_Application::getInstance()->loadApplicationConfig( CWA_APPLICATION_DIR.'/config' );
    // check for redirects here:
    if ( isset( $_SERVER['HTTP_HOST'] ) ) {
        $strRedirect = App_Application::getInstance()->getConfig()->redirect;
        if ( $strRedirect != '' ) {
            $strRedirect = preg_replace( '@/$@', '', $strRedirect );
            $strRequestUri = $_SERVER[ 'REQUEST_URI' ];
            header( 'Location: '.$strRedirect.$strRequestUri );
        }
    }
    App_Application::getInstance()->connectDb();
}

function cwa_run_web_application()
{
    // run routers - path to all application controllers
    App_Application::getInstance()->run();
}


function cwa_dump_web_application( $arrTables = array(), $strConnection = 'default' )
{
    $objConnectionConfig = App_Application::getInstance()->getConfig()->connections->$strConnection;
    $arrParams = $objConnectionConfig->params->toArray();

    App_CheckEnv::assert(  isset( $arrParams['host'] ), 'No host defined for "'.$strConnection.'" connection' );
    App_CheckEnv::assert(  isset( $arrParams['username'] ), 'No username defined for "'.$strConnection.'" connection' );
    App_CheckEnv::assert(  isset( $arrParams['dbname'] ), 'No database defined for "'.$strConnection.'" connection' );
    $strHost     = $arrParams['host'];
    $strUsername = $arrParams['username'];
    $strPassword = isset( $arrParams['password'] ) ? $arrParams['password'] : '' ;
    $strDatabase = $arrParams['dbname'];
    $strCharset  = isset( $arrParams['charset'] ) ? $arrParams['charset'] : '' ;

    App_CheckEnv::assert( mysql_connect( $strHost, $strUsername, $strPassword ),
            "no mysql connection for ".$strConnection );
    App_CheckEnv::assert( mysql_selectdb( $strDatabase ),
            "mysql database cannot be selected for ".$strConnection );
    
    App_CheckEnv::assert( CWA_APPLICATION_DIR.'/cdn', 
            "CDN folder is not found for output" );

    // if we dont know the list of tables, lets get it
    $rs = mysql_query('SHOW TABLE STATUS FROM ' . $strDatabase ) or die( mysql_error() );
    $arrAllTables = array();
    while( $rs && $r = mysql_fetch_array( $rs )) {
        $arrAllTables[ $r['Name'] ]  = $r['Name'];
    }
        
        
    if ( count( $arrTables ) != 0 ) {
        // verify each table
        foreach ( $arrTables as $strTable ) {
            if ( !isset( $arrAllTables[ $strTable ] ))
                throw new Exception( $strTable.' table not found in the database' );
        }
    } else {
        $arrTables = $arrAllTables;
    }
    
    $out = '';
  
    $out .= "
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

";
    if ( $strCharset != '' ) $out .= 'SET NAMES '.$strCharset.";\n";
    
    foreach(  $arrTables as $strTable ) {
        Sys_Io::out( $strTable );
        
        $rst = mysql_query("SHOW CREATE TABLE `$strTable`") or die( mysql_error() );
        $fi = array();
        if ($rst && $row2 = mysql_fetch_row($rst)) {
            $o = "DROP TABLE IF EXISTS `$strTable`;\n" . $row2[1] . ";\n\n";
            // echo $o;
            $fields = explode("\n", $row2[1]);
            // dbg( $fields );
            foreach ($fields as $i => $fld) {
                if (preg_match("/^\s*`([^`]+)`(.+)$/", $fld, $m)) {
                    $fi[$m[1]] = $m[2];
                }
            }
            $out .= $o;
        }
        // for each table record..
        $rsr = mysql_query("SELECT * FROM `$strTable`") or die( mysql_error() );
        while ($rsr && $row2 = mysql_fetch_array($rsr)) {
            $o = "\r\nINSERT INTO " . $strTable . " (";
            $col = 0;
            foreach ($row2 as $column => $value) {
                if (substr($column, 0) != substr(intval($column), 0)) {
                    if ($col != 0)
                        $o .= ",";
                    $o .= '`'."$column".'`';
                    $col ++;
                }
            }
            $o .= ")\r\n\tVALUES (";
            $col = 0;
            foreach ($row2 as $column => $value) {
                if (substr($column, 0) != substr(intval($column), 0)) {
                    if ($col != 0)
                        $o .= ",";
                    $v = "";
                    if ($value == "") {
                        $v = "NULL";
                        if (strstr($fi[$column], "NOT NULL"))
                            $v = "''";
                    } else {
                        $v = "'" . str_replace("\\", "\\\\", str_replace("'", "''", $value)) . "'";
                    }
                    $o .= $v;
                    $col ++;
                }
            }
            $o .= ");\r\n";
            $out .= $o;
        }
    }

    $out .= '
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

';
    
    
    $fn = trim(strtolower( $strDatabase )) . "_" . date("YmdHis") . ".zip";
    $z = new Sys_ZipFile();
    $z->add_file($out, trim(strtolower( $strDatabase )) . "_" . date("Ymd") . ".sql");
    $f = fopen( CWA_APPLICATION_DIR."/cdn/".$fn, "wb");
    if ($f) {
        fwrite($f, $z->file());
        fclose($f);
        if ( file_exists( $fn )) { chmod( $fn, 0666); }
        echo "<br />Please, download <a href='./cdn/$fn'>$fn</a> ("  . filesize( CWA_APPLICATION_DIR."/cdn/".$fn ) . " bytes) ";
    } else {
        echo 'Sorry, file was not created ';
    }
}


function cwa_patch_web_application() 
{
    // get all namespaces
    define( 'CWA_DISABLE_PLUGINS', 1 );
    $checker = new App_CheckEnv();
    $checker->run();
    
    $arrNamespaces = App_Application::getInstance()->getClassNamespaces();
    DBx_Table_Abstract::setDefaultMetadataCache( null );
    App_Update::run( $arrNamespaces );
}

/**
 * Routine for checking web applications environment
 * Can be put as a separate script 
 */
function cwa_check_web_application_environment() 
{
    define( 'CWA_DISABLE_PLUGINS', 1 );
    $checker = new App_CheckEnv();
    $checker->run();
}
function cwa_test_web_application()
{
    global $argv;
    define( 'CWA_DISABLE_PLUGINS', 1 );
    $loader = new App_Test_Loader();

    if ( isset( $_REQUEST['test'] )) {
        $loader->runSingle( $_REQUEST['test'] );
    } else {
        if ( isset( $_REQUEST['run'] )) {
            $loader->run( $_REQUEST['run'] );
        } else {
            $arrGroups = $argv;
            unset( $arrGroups[0 ] );
            
            if ( isset( $arrGroups[ 1 ] ) ) {
                if ( $arrGroups[ 1 ] == "group" ) {
                    $loader->run( $arrGroups[ 2 ] );
                    return;
                } else if ( $arrGroups[ 1 ] == "single" ) {
                    $loader->runSingle( $arrGroups[ 2 ] );
                    return;
                }
            }
            
            $loader->run( $arrGroups );
        }
    }
}

