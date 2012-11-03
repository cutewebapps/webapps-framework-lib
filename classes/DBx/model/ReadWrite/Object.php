<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

/**
 * Based on Zend Framework                                                                                                  
 *                                                                                                                 
 * LICENSE                                                                                                         
 *                                                                                                                 
 * This source file is subject to the new BSD license that is bundled                                              
 * with this package in the file LICENSE.txt.                                                                      
 * It is also available through the world-wide-web at this URL:                                                    
 * http://framework.zend.com/license/new-bsd                                                                       
 * If you did not receive a copy of the license and are unable to                                                  
 * obtain it through the world-wide-web, please send an email                                                      
 * to license@zend.com so we can send you a copy immediately.                                                      
 *                                                                                                                 
 * @category   Zend                                                                                                
 * @package    Zend_InfoCard                                                                                       
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)                            
 * @license    http://framework.zend.com/license/new-bsd     New BSD License                                       
 * @version    $Id: InfoCard.php 20096 2010-01-06 02:05:09Z bkarwin $                                              
 */

class DBx_ReadWrite_Object extends DBx_ReadWrite_Abstract
{
    /** @return boolean */
    public function canRead() { return $this->_strAdapterReadClass != ''; }
    /** @return boolean */
    public function canWrite() { return $this->_strAdapterWriteClass != ''; }

    /** @var DBx_Adapter_Read */
    protected $_objAdapterR = null;
    /** @var DBx_Adapter_Write */
    protected $_objAdapterW = null;
    
    /** @var string */
    protected $_strAdapterReadClass  = 'DBx_Adapter_Read';
    /** @var string */
    protected $_strAdapterWriteClass = 'DBx_Adapter_Write';

    /** @var Sys_Cache_Abstract */
    protected $_cache = null;
    
    /** @var DBx_Adapter_Read */
    public function getDbAdapterRead()  { return $this->_objAdapterR; }
    /** @var DBx_Adapter_Write */
    public function getDbAdapterWrite() { return $this->_objAdapterW; }
    
    
    /**
     * @return Sys_Cache_Abstract 
     */
    public function getCache()
    {
        return $this->_cache;
    }
    
    /**
     *
     * @return boolean
     */
    public function isConnected() {
        return $this->getDbAdapterRead() || $this->getDbAdapterWrite();
    }

    public function connect( $arrConnectionProperties )
    {
        if ( !isset( $arrConnectionProperties['params'] ) ) {
            throw new DBx_ReadWrite_Exception ( 'Params of the connection are not defined' );
        }
        $this->_strAdapterReadClass = '';
        $this->_objAdapterR = null;
        $this->_strAdapterWriteClass = '';
        $this->_objAdapterW = null;
        
        $this->_cache = null;
        if ( isset( $arrConnectionProperties['cache'] ) ) {
            
            if ( isset( $arrConnectionProperties['cache']['class'] )  && $arrConnectionProperties['cache']['class'] != '' ) {
                $strCacheClass = $arrConnectionProperties['cache']['class'];
                $arrCacheOptions = array();
                if ( isset( $arrConnectionProperties['cache']['options'] ) )
                    $arrCacheOptions = $arrConnectionProperties['cache']['options'];

                $this->_cache = new $strCacheClass( $arrCacheOptions );
                DBx_Table_Abstract::setDefaultMetadataCache($this->_cache);
            }
        }
        
        // Sys_Io_Debug::dump( $arrConnectionProperties );

        if ( isset( $arrConnectionProperties['read'] ) ) {
            $strDriverClass = $this->_strAdapterReadClass = $arrConnectionProperties['read'];
            if ( !class_exists( $strDriverClass ))
                throw new DBx_ReadWrite_Exception( 'Driver class '.$strDriverClass.' was not found ' );
            $this->_objAdapterR = new $strDriverClass( $arrConnectionProperties[ 'params' ] );
            if ( !( $this->_objAdapterR instanceof DBx_Adapter_Read ) )
                throw new DBx_Exception( 'Invalid Read DB Adapter '.$strDriverClass );
            $this->_objAdapterR->connect();

            if ( !$this->_objAdapterR->isConnected() )
                throw new DBx_Exception( 'Read database connection was not established' );
        }
        if ( isset( $arrConnectionProperties['write'] ) ) {
            $strDriverClass = $this->_strAdapterWriteClass = $arrConnectionProperties['write'];
            if ( !class_exists( $strDriverClass ))
                throw new DBx_ReadWrite_Exception( 'Driver class '.$strDriverClass.' was not found ' );
           
            // DBx_Adapter_Abstract
            
            $this->_objAdapterW = new $strDriverClass( $arrConnectionProperties[ 'params' ] );
            if ( !( $this->_objAdapterW instanceof DBx_Adapter_Write ) )
                throw new DBx_Exception( 'Invalid Read DB Adapter '.$strDriverClass );

            if ( !$this->_objAdapterW->isWriterFor( $this->_strAdapterReadClass ) ) {
                // Sys_Io::out( 'adapters are different' );
                $this->_objAdapterW->connect();
            } else {
                // Sys_Io::out( 'adapters are same' );
                $this->_objAdapterW->setConnection( $this->_objAdapterR );
            }
            if ( !$this->_objAdapterW->isConnected() )
                throw new DBx_Exception( 'Write database connection was not established' );
        }

        if ( !isset( $arrConnectionProperties['read'] ) &&
                !isset( $arrConnectionProperties['write'] )  ) {
            throw new DBx_Exception('DB connection is neither readable nor writable');
        }
        return true;
    }

    public function disconnect()
    {
        /*
        if ( null != $this->_objAdapterR  )
            if ( !$this->_objAdapterR->disconnect() ) return false;
        if ( null != $this->_objAdapterW  )
            if ( !$this->_objAdapterW->disconnect() ) return false;
         */
        return true;
    }

}