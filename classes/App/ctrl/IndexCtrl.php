<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

/**
 * controller for small websites 
 * introducing 404, 403, 501 actions and home page rendering
 */
class App_IndexCtrl extends App_WebsiteCtrl
{
    /**
     * this is a standart action for all types of pages
     */
    public function indexAction() 
    {}

    public function delayedAction() 
    {
	echo PHP_EOL;
	Sys_Io::out( date('c')." delayed ".$this->_getParam('title').' start' );
	sleep( $this->_getIntParam( 'delay',  5 ) );

	Sys_Io::out( date('c')." delayed ".$this->_getParam('title').' finished after '.$this->_getIntParam( 'delay',  5000 ).' msec' );
	die;
    }


    /**
     * this is a standart action for testing exception that was not caught
     */
    public function throwAction()
    {
        throw new App_Exception('test exception: internal server error');
    }
    /**
     * this is a standart action for testing fatal errors look
     */
    public function fatalAction()
    {
        // new App_class_that_was_not_found();
        $this->methodThatWillNeverExists();
    }
    /**
     * this is a standart action for testing debug on production
     */
    public function debugAction()
    {
        Sys_Debug::dumpDie( "Sample debug message" );

    }
    /**
     * this is a standart action for access-denied page 
     */
    public function deniedAction()
    {
        throw new App_AccessDenied_Exception('test exception: access was denied');
    }


    public function pageNotFoundAction() 
    {
        if ( PHP_SAPI != "cli" && !headers_sent()) {
            header('HTTP/1.1 404 Page Not Found');
        }
        if ( is_object( App_Application::getInstance()->getConfig()->lang ) ) {
            $this->view->lang = App_Application::getInstance()->getConfig()->lang->default_lang;
        }
        $this->setRender( '404' );
    }

    public function accessDeniedAction() 
    {
        if ( PHP_SAPI != "cli" && !headers_sent()) {
            header('HTTP/1.1 403 Access Denied');
        }
        $this->setRender( '403' );
    }

    public function serverErrorAction() 
    {
        if ( PHP_SAPI != "cli" && !headers_sent()) {
            header('HTTP/1.1 501 Server Error');
        }
        $this->setRender( '501' );
    }
}