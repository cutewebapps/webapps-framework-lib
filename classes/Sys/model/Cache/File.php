<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class Sys_Cache_File implements Sys_Cache_Abstract {
    
    protected $_options = array(
        'cache_dir' => '',
        'lifetime'  => 3600
    );
    
    public function __construct( $options = array() ) 
    {
        foreach( $options as $strKey => $strValue ) {
            $this->_options[ $strKey ] = $strValue;
        }
        if ( $this->_options['cache_dir'] == '' ) {
            $this->_options['cache_dir']  = App_Application::getInstance()->getConfig()->cache_dir;
            if ( $this->_options['cache_dir'] == '' ) 
                throw new Sys_Exception( 'Cache Dir was not set up correctly' );
        }
        
        $dir = new Sys_Dir( $this->_options['cache_dir'] );
        if ( ! $dir->exists() ) $dir->create( 0777, true );
        
        
        if ( !is_dir( $this->_options['cache_dir'] ) )  {
            throw new Sys_Exception( 'Cache Dir was not created' );
        }        
    }
    
    protected function _getFileName( $strTag )
    {
        return $this->_options['cache_dir'] .'/'.$strTag;
    }
    
    /**
     * @param data $data
     * @param string $strTag
     * @return boolean
     */
    public function save( $data, $strTag )
    {
        $file = new Sys_File( $this->_getFileName( $strTag) );
        $file->save( serialize( $data ));
        
        return true;
    }
    
    /**
     * @param string $strTag
     * @return mixed
     */
    public function load( $strTag )
    {
        if ( file_exists( $this->_getFileName( $strTag) ) ) {
            
            $nTimePassed = time() - filemtime(  $this->_getFileName( $strTag) );
            if ( $nTimePassed > intval( $this->_options['lifetime'] ) )
                return false;
            
            return unserialize( file_get_contents( $this->_getFileName( $strTag) ) );
        }
        
        return false;
    }
    
    /**
     * clean all file cache if no tag was given
     * @return void 
     */
    public function clean( $strTag = '' ) {
        $strPath = $this->_options['cache_dir'];
        if ( $strTag != '' ) {
            $strPath = $this->_options['cache_dir'].'/'.$strTag;
        }
        
        if ( is_dir( $strPath ) )  {
            $dir = new Sys_Dir( $strPath );
            $dir->delete();
        } else if ( file_exists( $strPath ) )  {
            unlink( $strPath );
        }
    }    
}