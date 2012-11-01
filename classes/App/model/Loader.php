<?php

// require base loader
require WC_DIR_CLASSES.'/Sys/model/Loader.php';


function wc_init_esc( $val )
{
    return str_replace("\\'","'",
        str_replace('\\"','"', str_replace( "\\\\", "\\", $val)));
}

function wc_init_web_application()
{

    if ( defined( 'WC_DIR_CLASSES' ) )
        Sys_Global::set('ClassesRoot', WC_DIR_CLASSES );

    // try to defined environment from envir variables or constants
    if ( defined('WC_APPLICATION_ENV') )
          Sys_Global::set( 'Environment', WC_APPLICATION_ENV );
    else if ( getenv( 'WC_APPLICATION_ENV' ) != '' )
          Sys_Global::set( 'Environment', getenv( 'WC_APPLICATION_ENV' ));

    // sometimes magic quotes are on inspite of everything...
    if ( get_magic_quotes_gpc() == 1 ){
        while (list($key,$val)=each($_POST))    { $_POST[$key]  = wc_init_esc($val); }
        while (list($key,$val)=each($_GET))     { $_GET[$key]   = wc_init_esc($val); }
        while (list($key,$val)=each($_REQUEST)) { $_REQUEST[$key] = wc_init_esc($val); }
    }

    App_Application::getInstance()->loadApplicationConfig( WC_APPLICATION_DIR.'/config' );
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

function wc_run_web_application()
{
    // run routers - path to all application controllers
    App_Application::getInstance()->run();
}

function wc_patch_web_application() 
{
    // get all namespaces
    define( 'WC_DISABLE_PLUGINS', 1 );
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
function wc_check_web_application_environment() 
{
    define( 'WC_DISABLE_PLUGINS', 1 );
    $checker = new App_CheckEnv();
    $checker->run();
}

function wc_test_web_application()
{
    global $argv;
    define( 'WC_DISABLE_PLUGINS', 1 );
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

