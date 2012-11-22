<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_Exception              extends Exception {};
/** 
 * for marking code branches as deprecated 
 */
class App_Exception_Deprecated   extends App_Exception {};
/** for marking code branches as incorrect and illegal */
class App_Exception_Inacceptable extends App_Exception {};
/** exception to raise on hackers alert */
class App_Exception_Security     extends App_Exception {};
/**
 * For pages that are not found
 */
class App_Exception_PageNotFound extends App_Exception {};
/**
 * For places that are forbidden
 */
class App_Exception_AccessDenied extends App_Exception {};
/**
 * For server errors and unhandled exceptions
 */
class App_Exception_ServerError  extends App_Exception {};

/**
 * Class of web application instance
 */
class App_Application
{
    const ENGINE = 'CWA';

    /** @var Sys_Application */
    protected static $_instance = null;
    /** @var Sys_Config */
    protected $_objConfig = null;
    /** @var array of string */
    protected $_arrNamespaces = array();

    protected $_strId;
    
    
    public function __construct()
    {
        $this->_strId = substr( md5( mt_rand(0,123123123) ), 0, 12);
    }
    public function getInstanceId()
    {
        return $this->_strId;
    }
    
    /**
     * @return Sys_Application
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function loadApplicationConfig($strDirectory)
    {
        $fn1 = $strDirectory . '/config.php';
        $arrConfig = include $fn1;
        if (is_array($arrConfig))
            $this->_objConfig = new Sys_Config($arrConfig, true);
        else
            $this->_objConfig = new Sys_Config(array(), true);

        if ( !Sys_Global::isRegistered( 'Environment') && getenv('CWA_ENV') ) {
            Sys_Global::set( 'Environment', getenv( 'CWA_ENV' ) );	
	}


        if ( !Sys_Global::isRegistered( 'Environment') && isset( $_SERVER['HTTP_HOST']) ) {

	    $strHost = preg_replace( '@:\d+$@', '', $_SERVER[ 'HTTP_HOST' ] );

            // if environment can be identified from HTTP host
            if ( is_object( $this->getConfig()->env ) ) {
                $arrHttpEnvironments = $this->getConfig()->env->toArray();
                if ( isset( $arrHttpEnvironments[ $strHost ] )) {
                    Sys_Global::set( 'Environment', $arrHttpEnvironments[ $strHost ]);
                }
            }
        }

        if ( !Sys_Global::isRegistered( 'Environment') )
            Sys_Global::set( 'Environment', 'local' );

        // load environment-specific options, overriding global config settings
        $fn2 = $strDirectory . '/env_' . Sys_Global::get('Environment') . '.php';
        if (file_exists($fn2)) {
            $arrConfigLocal = include $fn2;
            if (is_array($arrConfigLocal)) {
                $this->_objConfig->merge(new Sys_Config($arrConfigLocal));
            }
        }
        if (!is_object($this->getConfig()->namespaces)) {
            throw new Sys_Exception('Application namespaces are not defined');
        }
        if (!is_object($this->getConfig()->routes)) {
            throw new Sys_Exception('Application routes are not defined');
        }

        if (isset($_SERVER['HTTP_HOST'])) {
            if (!isset($this->getConfig()->base)) {
                throw new Sys_Exception('Application Base URL is not defined');
            }
            if (!is_object($this->getConfig()->sections)) {
                throw new Sys_Exception('Application Sections are not defined');
            }
            Sys_Global::set( 'RootUrl', $this->getConfig()->base );
        }
        if (is_object($this->getConfig()->php_settings)) {
            $arrSettings = $this->getConfig()->php_settings->toArray();
            foreach ($arrSettings as $strKey => $strValue)
                ini_set($strKey, $strValue);
        }
    }

    /**
     * @return Sys_Config
     */
    public function getConfig()
    {
        return $this->_objConfig;
    }

    /**
     * @return string
     */
    public function getTempDir()
    {
        if (is_object($this->getConfig()->temp_dir)) {
            return $this->getConfig()->temp_dir;
        }
        return '/tmp/';
    }
    /**
     * @return string
     */
    public static function getBaseUrl()
    {
        return self::getInstance()->getConfig()->base;
    }
    /**
     * @return array
     */
    public static function getClassNamespaces()
    {
        $arrPackages = self::getInstance()->getConfig()->namespaces->toArray();
        $arrResult = array();
        foreach ($arrPackages as /*$strPackage =>*/ $strPackageName) {
            // $arrPackages[ $strPackage ] = Sys_Global::get('ClassesRoot') . '/' . $strPackage;
            $arrResult[ $strPackageName ] = Sys_Global::get('ClassesRoot') . '/' . $strPackageName;
        }
        return $arrResult;
    }

    public function connectDb()
    {
        if ( is_object( self::getInstance()->getConfig()->connections ) ) {
            foreach( self::getInstance()->getConfig()->connections as $strConnName => $configConnProperties ) {
                DBx_Registry::getInstance()->setConnection( $strConnName, $configConnProperties->toArray() );
            }
        } 
    }

    public function run()
    {
        global $argv;
       
        $objDispatcher = new App_Dispatcher(
            $this->getConfig()->routes->toArray(),
            $this->getConfig()->default_controller
        );
        if ( isset( $_SERVER['REQUEST_URI'] ) ) {
            if ( defined( 'CWA_NO_REWRITE' ) ) {
                $objDispatcher->runUrl( '/' );
            } else {
                $objDispatcher->runUrl( $_SERVER['REQUEST_URI'] );
            }
        } else {
            $objDispatcher->runCli( $argv );
        }
    }

}
