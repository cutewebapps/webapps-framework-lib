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

    /**
     * 
     * @param type $strParam
     * @return boolean
     */
    protected function _hasParam( $strParam )
    {
        return isset( $this->_arrParams[ $strParam ]  );
    }
    /**
     * 
     * @param type $strParam
     * @return boolean
     */
    public function hasParam( $strParam ) 
    {
        return $this->_hasParam( $strParam ); 
    }

    /**
     * 
     * @param type $strParam
     * @param type $value
     */
    protected function _setParam( $strParam, $value )
    {
       $this->_arrParams[ $strParam ] = $value;
    }
    /**
     * 
     * @param type $strParam
     * @param type $value
     * @return type
     */
    public function setParam( $strParam, $value ) 
    { 
        return $this->_setParam( $strParam, $value ); 
    }

    /**
     * 
     * @return boolean
     */
    protected function isPost()
    {
        return count( $_POST ) > 0;
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
     * @param type $strIndex
     * @param type $strFileName
     * @return void
     */
    protected function _saveUploaded( $strIndex, $strFileName )
    {
        move_uploaded_file(  $_FILES[ $strIndex ]['tmp_name'], $strFileName );
    }


    /**
     * 
     * @param type $strParam
     * @param type $strDefault
     * @return type
     */
    protected function _getParam( $strParam, $strDefault = '' )
    {
        return $this->_hasParam( $strParam ) ? $this->_arrParams[ $strParam ] : $strDefault;
    }
    /**
     * 
     * @param string $strParam
     * @param mixed $strDefault
     * @return mixed
     */
    public function getParam( $strParam, $strDefault = '' ) 
    { 
        return $this->_getParam( $strParam, $strDefault ); 
    }
    
    /**
     * 
     * @param stirng $strParam
     * @param int $strDefault
     * @return int
     */
    protected function _getIntParam( $strParam, $strDefault = 0 )
    {
        if ( !$this->_hasParam( $strParam ) ) 
            return $strDefault;
        switch ( strtolower( $this->_arrParams[ $strParam ] ) ) {
            case 'false': return 0;
            case 'true':  return 1;
        }
        
        return intval( $this->_arrParams[ $strParam ] );
    }
    
    /**
     * 
     * @param string $strParam
     * @param mixed $strDefault
     * @return int
     */
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

    /**
     * 
     * @return array
     */
    protected function _getAllParams()
    {
        return $this->_arrParams;
    }

    /**
     * 
     * @return array
     */
    public function getAllParams()
    {
        return $this->_getAllParams();
    }

    /**
     * 
     * @return boolean
     */
    protected function _isPost()
    {
        return count( $_POST ) > 0;
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
     * @param type $strAction
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
    protected function getClassName()
    {
        $strControllerClassName = get_class( $this );
        if ( substr( $strControllerClassName, -4 ) == 'Ctrl' ) {
            return substr( $strControllerClassName, 0, strlen($strControllerClassName) - 4 );
        }
        return $strControllerClassName;
    }
    
    /**
     * @param string $strParam
     */
    protected function _adjustIntParam( $strParam )
    {
        if ( $this->hasParam( $strParam ) ) {
            if ( $this->_getParam( $strParam ) == 'true' )
                $this->_setParam( $strParam, 1 );
            else if ( $this->_getParam( $strParam ) == 'false' )
                $this->_setParam( $strParam, 0 );
            else if ( $this->_getParam( $strParam ) == 'on' )
                $this->_setParam( $strParam, 1 );
            else if ( $this->_getParam( $strParam ) == 'off' )
                $this->_setParam( $strParam, 0 );
        }
    }
  
    /**
     * @param string $strParam
     * @throws Sys_Date_Exception
     */
    protected function _adjustDateParam( $strParam )
    {
        $format = App_Application::getInstance()->getConfig()->dateformat;
        if ( !$format )
            throw new App_Exception( 'dateformat was not configured for this application' );
        
        $dt = new Sys_Date( $this->_getParam( $strParam ), $format );
        $this->_setParam( $strParam, $dt->getDate( Sys_Date::ISO ));
    }
    
    protected function _require( $arrConfiguration )
    {
        $arrErrors = array();
        $arrPushed = array();
        foreach ( $arrConfiguration as $arrParam ) {
            if ( ! isset( $arrParam['field'] ) )
                throw new App_Exception('field expected in require configuration');            
            $field = $arrParam['field'];
            if ( isset( $arrPushed[ $field ] )) continue; // do not push errors second time for the same fields
            
            $strMethod = isset( $arrParam['method'] ) ? $arrParam['method'] : '';
            $strMessage = isset( $arrParam['message'] ) ? $arrParam['message'] : '';
            $val  = trim( $this->_getParam($field ) );
            
            switch ( $strMethod ) {
                case '': 
                    // require non-empty value
                    if ( $val == '' ) {
                        
                        $bCheck = true;
                        if ( isset( $arrParam['if'] ) ) $bCheck  = $arrParam['if'];
                        
                        if ( $bCheck ) {
                            array_push( $arrErrors, array( $field => $strMessage ) ) ; 
                            $arrPushed[ $field ] = 1;
                        }
                    }
                    break;
                case 'date': 
                    // require non-empty value
                    try{ 
                        $this->_adjustDateParam( $field );
                    } catch ( Sys_Date_Exception $e ) {
                        array_push( $arrErrors, array( $field => $e->getMessage() ) ) ;
                        $arrPushed[ $field ] = 1;
                    }                    
                    break;
                
                case 'min':
                    if ( ! isset( $arrParam['value'] ) )
                        throw new App_Exception('value expected in require configuration');
                    $bCondition = (double)$val <= (double)$arrParam['value'];
                    
                    if ( isset( $arrParam['equal'] ) )
                        $bCondition = (double)$val < (double)$arrParam['value'];
                    
                    if ( $bCondition ) {
                        array_push( $arrErrors, array( $field => $strMessage ) ) ;
                        $arrPushed[ $field ] = 1;
                    }
                    break;
                    
                case 'max':
                    if ( ! isset( $arrParam['value'] ) )
                        throw new App_Exception('value expected in require configuration');
                    
                    $bCondition = (double)$val >= (double)$arrParam['value'];
                    if ( isset( $arrParam['equal'] ) )
                        $bCondition = (double)$val > (double)$arrParam['value'];
                    if ( $bCondition ) {
                        array_push( $arrErrors, array( $field => $strMessage ) ) ;
                        $arrPushed[ $field ] = 1;
                    }
                    break;
                
                case 'email': 
                    if ( !Sys_String::isEmail( $val ) ) {
                        array_push( $arrErrors, array( $field => $e->getMessage() ) ) ;
                        $arrPushed[ $field ] = 1;
                    }
                    break;
            }
        }
        return $arrErrors;
    }
}
