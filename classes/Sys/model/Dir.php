<?php


/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */
class Sys_Dir
{
    protected $_strDirectory;
    
    public function __construct( $strDir ) 
    {
        $this->_strDirectory = $strDir;
    }
    
    public function getName() 
    {
        return $this->_strDirectory;
    }
    
    /**
     * @return bool
     */
    public function exists()
    {
        return file_exists( $this->getName() ) && is_dir( $this->getName() );
    }
    
    public function create( $strAccessMode = '', $boolRecursive = false )
    {
        $strParentDir = dirname($this->_strDirectory);
        if (file_exists($this->_strDirectory)) {
            return false;
        }
        if (! $boolRecursive and 
            (! file_exists($strParentDir) or ! is_writeable($strParentDir))) {
            return false;
        }
        if ($strAccessMode != '') {
            $oldUmask = umask(0);
            $boolMkDir = mkdir($this->_strDirectory, $strAccessMode, $boolRecursive);
            umask($oldUmask);
        } else {
            return mkdir($this->_strDirectory, 0777, $boolRecursive);
        }
        return $boolMkDir;
    }
    
    /**
     * remove all files inside the directory 
     * @return void
     */
    public function clean()
    {
        if ( !is_dir( $this->_strDirectory ) ) return;
        
        $h = opendir($this->_strDirectory);
        while ($h && $f = readdir($h)) if ( $f != '.' && $f != '..') {
            $fn = $this->_strDirectory . '/' . $f;
            if ( is_dir($fn) ) {
                $dir = new Sys_Dir($fn);
                $dir->delete();
            } else {
                $file = new Sys_File( $fn );
                $file->delete();
            }
        }
        if ( $h ) closedir( $h );
    }
    
    /**
     * @return void
     */
    public function delete() 
    {
        $this->clean();
        
        if ( !is_dir( $this->_strDirectory ) ) return;
        $dir = new Sys_Dir($this->_strDirectory);
        rmdir( $dir->getName() );
    }
    
    public function getFiles( $strRegex = '', $bRecursive = true ) {
        $rz = array(); 
        $h = opendir( $this->getName() );
        $arrInner = array();
        while( $h && $pn = readdir( $h ) ) 
            if ( $pn != '.' && $pn != '..' ) {
                
                $strFull = $this->getName() . '/' . $pn;
                if ( is_dir( $strFull ) )
                    $arrInner[] = $strFull;
                else if ( $strRegex == '' || preg_match( $strRegex, $pn ) ) { 
                    $rz [] = $strFull;
                }
            }
            
        if ( $h ) closedir( $h );
        if ( $bRecursive ) {
            foreach ( $arrInner as $strInnerDir ) {
                $dir  = new Sys_Dir( $strInnerDir );
                $arrList = $dir->getFiles( $strRegex );
                foreach ( $arrList as $strFile ) $rz [] = $strFile;      
            }
        } 
        return $rz;        
    }
    
    public function getDirs( $strRegex = '' ) {
        $h = opendir( $this->getName() );
        $arrInner = array();
        while( $h && $pn = readdir( $h ) ) 
            if ( substr( $pn, 0, 1 ) != '.' ) {
                
                $strFull = $this->getName() . '/' . $pn;
                if ( is_dir( $strFull ) )
                    $arrInner[] = $strFull;
            }
        if ( $h ) closedir( $h );
        return $arrInner;        
    }
}