<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_Test_Crawler extends App_Test_Case
{
    /**
     * @var App_Http_Browser
     */
    protected $_browser = null;

    /**
     * @var array
     */
    public $jsonResult = null;

    /**
     * @return App_Http_Browser
     */
    public function getBrowser()
    {
        return $this->_browser;
    }

    protected $_arrNeed = array();
    public function setNeedTitle( $bValue = true )
    {
        $this->_arrNeed[ 'title' ] = $bValue;
        return $this;
    }
    public function setNeedJson( $bValue = true )
    {
        $this->_arrNeed[ 'json' ] = $bValue;
        return $this;
    }

    protected $_bDebugJson = false;
    public function setDebugJson( $bValue = true)
    {
        $this->_bDebugJson = $bValue;
        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function isNeeded( $strKey )
    {
        return isset( $this->_arrNeed[ $strKey ] ) && $this->_arrNeed[ $strKey ];
    }



    public function __construct()
    {
        $this->_browser = new App_Http_Browser();
        $this->_browser
            ->setCacheFolder( App_Application::getInstance()->getConfig()->cache_dir, 'test-cookies.txt' )
            ->setUseCache( false )
            ->setSaveCache( false )
            ->init();
    }

    protected $_strToken = '';
    public function getToken()
    {
        if ( $this->_strToken == '' ) {
            
            if ( ! is_object( App_Application::getInstance()->getConfig()->test ) || 
                 ! App_Application::getInstance()->getConfig()->test->token )
                throw new App_Exception( 'Using token is not configured for this environment' );
            
            // we should have array set up in config
            // - where to take token from JSON request
            $strTokenUrl = $this->getBaseUrl() 
                    . App_Application::getInstance()->getConfig()->test->token;
            // Sys_Io::out( 'getting token from '. $strTokenUrl, '', array( 'color' => 'gray' ) );
            
            $this->getBrowser()->httpGet( $strTokenUrl );
            if ( $this->getBrowser()->HttpBody == '' ) 
                throw new App_Exception( 'Empty result for getting token' );
            
            if ( substr( $this->getBrowser()->HttpBody, 0, 1) != '{' ) 
                throw new App_Exception( 'Invalid result for getting token, JSON expected' );
            
            $arrJson = json_decode( $this->getBrowser()->HttpBody, true );
            if ( !is_array( $arrJson ))
                throw new App_Exception( 'Invalid array for getting token' );
            if ( !isset( $arrJson['token'] ))
                throw new App_Exception( 'Token expected in JSON result' );
            
            $this->_strToken = $arrJson['token'];
        }
        
        return $this->_strToken;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        if ( !is_object( App_Application::getInstance()->getConfig()->test) )
            throw new App_Exception( 'Testing is not configured for this environment' );
        
        if ( App_Application::getInstance()->getConfig()->test->crawl );
            return App_Application::getInstance()->getConfig()->test->crawl;

        throw new App_Exception( 'Please configure crawl-path for your environment' );
    }


    /**
     *
     * @param array $arrTestProperties
     * @return void
     */
    public function visit( array $arrTestProperties )
    {
        $this->jsonResult = null;
        if ( !isset( $arrTestProperties['uri'] ) ) {
            throw new Sys_Exception( 'URI of test item is not specified' );
        }

        $strUrl = $this->getBaseUrl() . $arrTestProperties['uri'];

        if ( isset( $arrTestProperties['post'] ) ) {
            $this->getBrowser()->httpPost( $strUrl, $arrTestProperties['post'] );
        } else {
            $this->getBrowser()->httpGet( $strUrl );
        }
        // Sys_Debug::dump( $this->getBrowser()->Info );

        $strExt = ' ('.sprintf( '%.3f', $this->getBrowser()->Info['total_time']) .'s) ';
        if ( $this->getBrowser()->HttpStatus > 400 )
            throw new App_Http_Exception( "HTTP Status: " . $this->getBrowser()->HttpStatus );

        if ( isset($arrTestProperties['status'] ) ) {
            if ( $arrTestProperties['status'] != $this->getBrowser()->HttpStatus )
               throw new App_Http_Exception( "HTTP Status: " . $this->getBrowser()->HttpStatus
                    .", expected ".$arrTestProperties['status'] );
        }
        if ( strstr( $this->getBrowser()->HttpBody, '<b>Fatal error</b>:') ) {
             throw new App_Http_Exception( 'Fatal Error');
        }

        if ( isset($arrTestProperties['location'] ) ) {
            if ( $arrTestProperties['location'] != $this->getBrowser()->Info['url'] )
                throw new App_Http_Exception( "Expected current uri to be ".$arrTestProperties['location'].' instead of '. $this->getBrowser()->$Info['url'] );
        }

        if ( isset( $arrTestProperties['matches'] ) ) {
            if ( !stristr( $this->getBrowser()->HttpBody, $arrTestProperties['matches'] ) ) {
                throw new App_Http_Exception( "Expected response to match \"".$arrTestProperties['matches']."\"" );
            }
        }
       
        if ( $this->isNeeded( 'json' ) || isset( $arrTestProperties['json'] ) ) {

            $this->jsonResult = json_decode( trim( $this->getBrowser()->HttpBody ), true );
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    break;
                case JSON_ERROR_DEPTH:
                    throw new App_Http_Exception( 'JSON - Maximum stack depth exceeded' );
                case JSON_ERROR_STATE_MISMATCH:
                    throw new App_Http_Exception( 'JSON - Underflow or the modes mismatch');
                case JSON_ERROR_CTRL_CHAR:
                    throw new App_Http_Exception( 'JSON - Unexpected control character found');
                case JSON_ERROR_SYNTAX:
                    throw new App_Http_Exception( 'JSON - Syntax error, malformed JSON');
                case JSON_ERROR_UTF8:
                    throw new App_Http_Exception( 'JSON - Malformed UTF-8 characters, possibly incorrectly encoded');
                default:
                    throw new App_Http_Exception( 'JSON - unknown error' );
            }
            if ( isset( $arrTestProperties['json'] ) && is_array(  $arrTestProperties['json'] ) ) {

                // if ( $this-> ) Sys_Debug::dump( $this->jsonResult );
                foreach ( $arrTestProperties['json'] as $strRequiredField ) {
                    if ( !isset( $this->jsonResult[ $strRequiredField ] ) ) {
                        
                        if ( $this->_bDebugJson )
                            Sys_Debug::dump( $this->jsonResult );
                        
                        throw new App_Http_Exception( "Field '$strRequiredField' required in JSON response" );
                    }

                }
            }
        }

        if ( $this->isNeeded( 'title' ) ) {
            $strTitle = Sys_String::x( '@<title([^>]*)>(.*)</title>@simU', $this->getBrowser()->HttpBody, 2 );
            if ( $strTitle == '' )
                throw new App_Http_Exception( 'Title expected' );
        }

    }
    
    public function crawl( $arrMap )
    {
        $nErrors = 0;
        $nTotal = 0;
        foreach ( $arrMap as $arrTestProperties ) {
            $strResult = 'OK';
            $strExt   = '';
            $arrOutputProps =  array();
            try {
                $this->visit( $arrTestProperties );
            } catch( App_Http_Exception $ex ) {
                $strResult = 'FAIL ('.$ex->getMessage().')';
                $arrOutputProps = array( 'color' => 'red' );
                $nErrors++;
            }
            $this->out( '* '.$arrTestProperties['uri'].$strExt.' - '.$strResult, '', $arrOutputProps );
            
            $nTotal ++;
        }

        $this->assertEquals( $nErrors, 0, $nErrors.' out of '.$nTotal.' crawler errors' );
    }
}