<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */
class App_AbstractCtrl extends App_Parameter_Storage
{
    public    $view = null;

    protected $_strTemplate = '';
    protected $_arrContexts = array(); // allowed contexts for the controller action
    protected $_strScriptName = '';

    /**
     * 
     * @return boolean
     */
    protected function isPost()
    {
        $strRequestMethod = isset( $_SERVER[ 'REQUEST_METHOD' ] ) ? $_SERVER[ 'REQUEST_METHOD' ] : '';
        return ( $strRequestMethod == 'POST' || $strRequestMethod == 'PUT' );
    }
    
    /**
     * 
     * @param string $strParam
     * @return boolean
     */
    protected function _hasFile( $strParam )
    {
        return ( isset( $_FILES[$strParam]['tmp_name'] ) )
            && ( $_FILES[$strParam]['tmp_name'] != '' );
    }

    /**
     * 
     * @return array
     */
    protected function _getAllFiles()
    {
        $arrResult = array();
        foreach( $_FILES as $strKey => $F ) {
            if ( $F[ 'tmp_name' ] != ''  && $F['error'] == 0 ) 
                $arrResult[ $strKey ] = $F;
        }
        return $arrResult;
    }
    
    /**
     * @returu
     */ 
    protected function _getFile( $strKey )
    {
        $arrFiles = $this->_getAllFiles();
        if ( isset( $arrFiles[ $strKey ] ) )
            return $arrFiles[ $strKey ];
        return array();
    }
   
    /**
     * @return array
     */
    protected function _getFileErrors()
    {
        $arrResult = array();
        foreach( $_FILES as $strKey => $F ) {
            if ( $F[ 'error' ] != 0 && $F['error'] != UPLOAD_ERR_NO_FILE ) {
                switch( $F['error']) {
                    case UPLOAD_ERR_INI_SIZE: 
                        $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini"; 
                        break; 
                    case UPLOAD_ERR_FORM_SIZE: 
                        $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"; 
                        break; 
                    case UPLOAD_ERR_PARTIAL: 
                        $message = "The uploaded file was only partially uploaded"; 
                        break; 
                    case UPLOAD_ERR_NO_FILE: 
                        $message = "No file was uploaded"; 
                        break; 
                    case UPLOAD_ERR_NO_TMP_DIR: 
                        $message = "Missing a temporary folder"; 
                        break; 
                    case UPLOAD_ERR_CANT_WRITE: 
                        $message = "Failed to write file to disk"; 
                        break; 
                    case UPLOAD_ERR_EXTENSION: 
                        $message = "File upload stopped by extension"; 
                        break; 
                    default: 
                        $message = "Unknown upload error"; 
                        break; 
                }
                $arrResult[ $strKey ] = $message;
            }
        }
        return $arrResult;
        
    }

    /**
     * 
     * @param string $strIndex
     * @param string $strFileName
     * @return void
     */
    protected function _saveUploaded( $strIndex, $strFileName )
    {
        move_uploaded_file(  $_FILES[ $strIndex ]['tmp_name'], $strFileName );
    }


    /**
     * 
     * @return boolean
     */
    protected function _isPost()
    {
        $strRequestMethod = isset( $_SERVER[ 'REQUEST_METHOD' ] ) ? $_SERVER[ 'REQUEST_METHOD' ] : '';
        return ( $strRequestMethod == 'POST' || $strRequestMethod == 'PUT' );
    }

    /**
     * 
     * @param array $arrParams
     * @throws App_Exception
     */
    public function __construct( $arrParams = array() )
    {
        $this->_arrParams = $arrParams;
        $config = App_Application::getInstance()->getConfig();
        
        $strViewClass = 'App_View';
        if ( $config->default_renderer != '' )
            $strViewClass = $config->default_renderer;
        if ( isset( $arrParams[ 'renderer' ] )) {
            $strViewClass = $arrParams[ 'renderer' ];
        }
        if ( !isset( $arrParams[ 'action' ]) ) {
            throw new App_Exception( 'Action must be defined' );
        }

        $this->view = new $strViewClass();
        $this->_strScriptName = $arrParams[ 'action' ];
        
        if ( isset( $arrParams[ 'lang' ] ) ) {

            // checking that language is allowed in config
            if ( is_object( $config->lang ) &&
                    is_object(  $config->lang->sections ) ) {
                
                $arrSections = $config->lang->sections->toArray();
                if ( isset( $arrSections[ $arrParams['section'] ] ) ) {
                    if (!in_array( $arrParams['lang'] , $arrSections[ $arrParams['section'] ])) {
                        throw new App_Exception( 'Language is not supported for this section' );
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

    /**
     * should not be used
     * @param string $strAction
     * @throws App_Exception_Inacceptable
     */
    public function _forward( $strAction )
    {
        throw new App_Exception_Inacceptable( "Action forwarding will burn in hell" );
    }

    /**
     * 
     * @param string $strViewName
     * @return void
     */
    public function setRender( $strViewName )
    {
        $this->_strScriptName = $strViewName;
    }

    /**
     * @return string
     */
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
    public function getClassName()
    {
        $strControllerClassName = get_class( $this );
        if ( substr( $strControllerClassName, -4 ) == 'Ctrl' ) {
            return substr( $strControllerClassName, 0, strlen($strControllerClassName) - 4 );
        }
        return $strControllerClassName;
    }
    
}
