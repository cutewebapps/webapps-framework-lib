<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_View extends Sys_Editable
{
    /** @var App_Layout */
    protected $_layout = null;
    /** @var App_ViewHelper_Broker */
    protected $_broker = null;
    /** @var string */
    protected $_strContents = '';
    /** @var string|array */
    protected $_path = '';
    /**
     * @return void
     */
    public function __construct()
    {
        $this->_broker = new App_ViewHelper_Broker( 'App' );
        $this->_broker->setView( $this );
        $this->_layout = new App_Layout( $this );
    }
    public function __call( $name, $arguments )
    {
        $this->_broker->setNamespace( 'App' );
        return call_user_func_array( array($this->_broker, $name), $arguments );
    }
    /**
     * @return App_Layout
     */
    public function getLayout()
    {
        return $this->_layout;
    }
    /**
     * for overriding
     * @return string 
     */
    public function getExtension()
    {
        return 'phtml';
    }
    /**
     * @return string
     */
    public function base()
    {
        return preg_replace( '/\/$/', '', App_Application::getInstance()->getConfig()->base );
    }
    /**
     * @return string
     */
    public function staticpath()
    {
        $strPath = preg_replace( '/\/$/', '', App_Application::getInstance()->getConfig()->base_static ).'/';
        if ( $strPath == '' ) $strPath = $this->base().'/';
        $strPath .= 'static/';
        return $strPath;
    }
    /**
     * @return string
     */
    public function cdnpath()
    {
        $strPath = preg_replace( '/\/$/', '', App_Application::getInstance()->getConfig()->base_cdn ).'/';
        if ( $strPath == '' ) $strPath = $this->base().'/';
        $strPath .= 'cdn/';
        return $strPath;
    }
    /**
     * @return string
     */
    public function baseDir()
    {
        return CWA_APPLICATION_DIR;
    }
    /**
     * @return App_ViewHelper_Broker
     */
    public function broker( $strNamespace = 'App' )
    {
        $this->_broker->setNamespace( $strNamespace );
        return $this->_broker;
    }
    /**
     * @return mixed
     */
    public function action( $strAction, $strController, $strModule, $arrParams = array() )
    {
        $dispatch = new App_Dispatcher();
        $arrParams[ 'nolayout' ] = 1;
        // $arrParams[ 'section' ] = ''; ?
        return $dispatch->runAction( $strAction, $strController, $strModule, $arrParams );
    }

    /** @return string */
    public function url( $arrParams )
    {
        $arrSplittedParams = array();
        // section, module, controller, action

        if ( isset( $arrParams['section'] )) {
            if ( $arrParams[ 'section' ] != 'frontend' ) {
                //TODO: get theme from the section name??
                //$arrSplittedParams []= urlencode( $arrParams[ 'section' ] );
                $arrSplittedParams []= 'admin';// TEMPORARY!!!!
            }
            unset( $arrParams[ 'section' ] );
        }
        if (defined( 'CWA_NO_REWRITE' )) {
            $arrAmpParams = array();
            foreach( $arrParams as $strKey => $value ) {
                $arrAmpParams[] = $strKey.'='.rawurlencode( $value );
            }
            return $this->base(). '/?'.implode( '&amp;', $arrAmpParams );
        }
        
        if ( isset( $arrParams['module'] ) &&  isset( $arrParams['controller'] ) && isset( $arrParams['action'] ) ) {
            
            $arrSplittedParams []= urlencode( $arrParams[ 'module' ] );
            $arrSplittedParams []= urlencode( $arrParams[ 'controller' ] );
            $arrSplittedParams []= urlencode( $arrParams[ 'action' ] );

            unset( $arrParams[ 'module' ] );
            unset( $arrParams[ 'controller' ] );
            unset( $arrParams[ 'action' ] );
        }
        foreach ( $arrParams as $strKey => $value ) {
            if ( !is_array( $value ) ) {
                $arrSplittedParams [] = $strKey.'/'.urlencode( $value );
            } else {
                foreach( $value as $strValue ) {
                    $arrSplittedParams [] = $strKey.'/'.urlencode( $strValue );
                }
            }
        }

        return $this->base(). '/'.implode( '/', $arrSplittedParams );
    }

    /**
     * @return string
     */
    public function partial( $strPath, $arrParams = array() )
    {
        $strClass = 'App_View';
        if ( App_Application::getInstance()->getConfig()->default_renderer )
            $strClass = App_Application::getInstance()->getConfig()->default_renderer;
        
        if ( isset( $arrParams['renderer'] ))
            $strClass = $arrParams['renderer'];

        $subView = new $strClass();
        $arrPaths = $this->getPath();
        if ( is_string( $arrPaths )) $arrPaths = array( $arrPaths );
        
        $strFullPath = '';
        foreach ( $arrPaths as $strFullFile ) {
            // Sys_Io::out( dirname( dirname( dirname( $strFullFile ))) .'/'.$strPath );
            $strPossiblePath = dirname( dirname( dirname( $strFullFile ))) .'/'.$strPath;
            if ( file_exists( $strPossiblePath ) ) {
               $strFullPath = $strPossiblePath;
               break;
            }
        }
        if ( $strFullPath == '' )
            throw new App_Exception( 'Cannot find theme path for rendering partial');

        $subView->setPath( $strFullPath );
        foreach( $arrParams as $strKey => $value ) {
            $subView->$strKey = $value;
        }
        return $subView->render();
    }

    /**
     * @param string|array $path
     * @return void */
    public function setPath( $path )
    {
        $this->_path = $path;
    }
    /** @return string | array */
    public function getPath()
    {
        return $this->_path;
    }
    /** @return string */
    public function getContents()
    {
        return $this->_strContents;
    }
    /**
     * @return string
     */
    public function render()
    {
        ob_start();
        $arrPaths = $this->getPath();
        if ( is_string( $arrPaths )) $arrPaths = array( $arrPaths );

        $bSuccess = false;
        foreach ( $arrPaths as $strPath ) {
            if ( file_exists( $strPath ) ) {
                require $strPath; $bSuccess = true; break;
            }
        }
        if ( !$bSuccess ) {
            throw new App_Exception( 'Template was not found at '.implode( ",", $arrPaths ));
        }

        $this->_strContents = ob_get_contents();
        ob_end_clean();
        return $this->_strContents;
    }
    /**
     * @return string
     */
    public function translate( $str )
    {
        if ( class_exists( 'Lang_Hash' ) ) {
            $strLang = $this->lang;
            return Lang_Hash::get( $str, $strLang, '' );
        }
        return $str;
    }
    /**
     * @return string
     */
    public function escape( $str )
    {
        return htmlspecialchars( $str, ENT_QUOTES );
    }
    /**
     * @param string $strIndex
     * @param mixed default
     * @return mixed
     */
    public function getInflection( $strIndex, $default = '' )
    {
        if ( isset( $this->inflection[ $strIndex ] ))
            return $this->inflection[ $strIndex ];
        return $default;
    }
    /**
     * @return Sys_Config
     */
    public function getAppConfig()
    {
        return App_Application::getInstance()->getConfig();
    }
    /**
     * @return string
     */
    public function getAppLanguage()
    {
        $strResult = '';
        if ( is_object( App_Application::getInstance()->getConfig()->lang ) ) {
           $strResult = App_Application::getInstance()->getConfig()->lang->default_lang;
        }
        if ( $strResult == '' ) $strResult == 'en';
        return $strResult;
    }
}
