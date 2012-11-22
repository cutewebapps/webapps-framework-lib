<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */
class App_AbstractCtrl
{
    public $view = null;

    protected $_strTemplate = '';
    protected $_arrContexts = array(); // allowed contexts for the controller action
    protected $_arrParams   = array();

    protected $_strScriptName = '';

    protected function _hasParam( $strParam )
    {
        return isset( $this->_arrParams[ $strParam ]  );
    }
    public function hasParam( $strParam ) { return $this->setParam( $strParam ); }

    protected function _setParam( $strParam, $value )
    {
       $this->_arrParams[ $strParam ] = $value;
    }
    public function setParam( $strParam, $value ) { return $this->_setParam( $strParam, $value ); }

    protected function isPost()
    {
        return count( $_POST ) > 0;
    }
    
    protected function _hasFile( $strParam )
     {
         return ( isset( $_FILES[$strParam]['tmp_name'] ) )
             && ( $_FILES[$strParam]['tmp_name'] != '' );
     }

     protected function _saveUploaded( $strIndex, $strFileName )
     {
         move_uploaded_file(  $_FILES[ $strIndex ]['tmp_name'], $strFileName );
     }


    protected function _getParam( $strParam, $strDefault = '' )
    {
        return $this->_hasParam( $strParam ) ? $this->_arrParams[ $strParam ] : $strDefault;
    }
    public function getParam( $strParam, $strDefault = '' ) { return $this->_getParam( $strParam, $strDefault ); }
    
    protected function _getIntParam( $strParam, $strDefault = 0 )
    {
        return $this->_hasParam( $strParam ) ? intval( $this->_arrParams[ $strParam ] ) : $strDefault;
    }
    public function getIntParam( $strParam, $strDefault = '' ) { return $this->_getIntParam( $strParam, $strDefault ); }
    /**
     * @return int 0 or 1
     * @param string $strParam
     * @param int $intDefault
     */
    protected function _getBoolParam( $strParam, $intDefault = 0 )
    {
       
        switch ( strtolower( $this->_getParam( $strParam ) ) ) {
            case 1: 
            case 'true':
            case 'on':
                return 1;
            case 0: 
            case 'false':
            case 'off':
                return 0;
            default:
                return $intDefault;
        }
    }

    protected function _getAllParams()
    {
        return $this->_arrParams;
    }

    public function getAllParams()
    {
        return $this->_getAllParams();
    }

    protected function _isPost()
    {
        return count( $_POST ) > 0;
    }

    public function __construct( $arrParams = array() )
    {
        $this->_arrParams = $arrParams;
        $config = App_Application::getInstance()->getConfig();
        
        $strViewClass = 'App_View';
        if ( $config->default_renderer != '' )
            $strViewClass = $config->default_renderer;
        if ( isset( $arrParams[ 'renderer' ] ))
            $strViewClass = $arrParams[ 'renderer' ];
        
        
        $this->view = new $strViewClass();
        $this->_strScriptName = $arrParams[ 'action' ];
        
        if ( isset( $arrParams[ 'lang' ] ) ) {

            // checking that language is allowed in config
            if ( is_object( $config->lang ) &&
                    is_object(  $config->lang->sections ) ) {
                
                $arrSections = $config->lang->sections->toArray();
                if ( isset( $arrSections[ $arrParams['section'] ] ) ) {
                    if (!in_array( $arrParams['lang'] , $arrSections[ $arrParams['section'] ])) {
                        throw new App_PageNotFound_Exception( 'Language is not supported' );
                    }
                }
            }
            $this->view->lang = $arrParams[ 'lang' ];
        }

        if ( isset( $arrParams['template'] )  && $arrParams['template'] != '' ) {
            $this->_strScriptName .= '-'.$arrParams['template'];
        }
        
    }
    /**
        * @return SimpleView
        */
    public function getView()
    {
        return $this->view;
    }

    public function _forward( $strAction )
    {
        throw App_Exceptions( "Action forwarding will burn in hell" );
    }

    public function setRender( $strViewName )
    {
        $this->_strScriptName = $strViewName;
    }

    public function getRender()
    {
        return $this->_strScriptName;
    }

    /**
    * @return mixed
    */
    protected function getComponentName()
    {
    	$arrayParts = explode('_', get_class($this));
        return reset($arrayParts);
    }

    /**
    * class for future overloading
    * @return mixed
    */
    protected function getClassName()
    {
        $strControllerClassName = get_class( $this );
        if ( substr( $strControllerClassName, -4 ) == 'Ctrl' ) {
            return substr( $strControllerClassName, 0, strlen($strControllerClassName) - 4 );
        }
        return $strControllerClassName;
    }
}
