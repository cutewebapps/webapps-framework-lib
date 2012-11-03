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

    // sometimes magic quotes are on inspite of everything...
    if ( get_magic_quotes_gpc() == 1 ){
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
            $loader->run( $arrGroups );
        }
    }
}

