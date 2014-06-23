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
    /**
     * @var string
     */
    protected $_strDirectory;
    
    /**
     * Initialize Directory object - with full folder path
     * Folder might not exist at that moment.
     * 
     * @param string $strDir
     * @param bool $bEnsureExists
     */
    public function __construct( $strDir, $bEnsureExists = false )
    {
        $this->_strDirectory = $strDir;
        if ( $bEnsureExists ) {
            if ( !$this->exists() ) {
                $this->create(0777, true);
            }
        }
    }
    
    /**
     * replace base in the beginning of the path
     * 
     * @return string $strPath
     * @return string $strBase
     * @return string
     */
    protected function _replaceBase( $strPath, $strBase )
    {
        $strPath = str_replace ('\\', '/', $strPath );
        $nBaseLen = strlen( $strBase );
        
        // replace $strBase at the beginning with $this->getName()
        if ( substr( $strPath, 0, $nBaseLen ) == $this->getName() ) {
            $strPath = $this->getName() . substr( $strPath, $nBaseLen );
        }
        
        return $strPath;
    }
    /**
     * 
     * @return get Full Path to the folder
     */
    public function getName() 
    {
        return $this->_strDirectory;
    }
    
    /**
     * @return boolean
     */
    public function exists()
    {
        return file_exists( $this->getName() ) && is_dir( $this->getName() );
    }
    /**
     * 
     * @param string $strAccessMode
     * @param boolean $boolRecursive
     * @return boolean whether directory was created
     */
    public function create( $strAccessMode = '', $boolRecursive = false )
    {
        $strParentDir = dirname($this->_strDirectory);
        $bResult = true;
        if (file_exists($this->_strDirectory)) {
            $bResult = false;
        } else if (! $boolRecursive and 
            (! file_exists($strParentDir) or ! is_writeable($strParentDir))) {
            $bResult = false;
        } else if ($strAccessMode != '') {
            $oldUmask = umask(0);
            $bResult = mkdir($this->_strDirectory, $strAccessMode, $boolRecursive);
            umask($oldUmask);
        } else {
            $bResult = mkdir($this->_strDirectory, 0777, $boolRecursive);
        }
        return $bResult;
    }
    
    /**
     * remove all files inside the directory 
     * @return void
     */
    public function clean()
    {
        if ( is_dir( $this->_strDirectory ) ) {
            $h = opendir($this->_strDirectory);
            while ($h && $f = readdir($h)) { 
                if ( $f != '.' && $f != '..') {
                    $fn = $this->_strDirectory . '/' . $f;
                    if ( is_dir($fn) ) {
                        $dir = new Sys_Dir($fn);
                        $dir->delete();
                    } else {
                        $file = new Sys_File( $fn );
                        $file->delete();
                    }
                }
            }
	    if ( $h ) { closedir( $h ); }
            
        }
    }
    
    /**
     * @return void
     */
    public function delete() 
    {
        $this->clean();
        if ( is_dir( $this->_strDirectory ) ) {
            $dir = new Sys_Dir($this->_strDirectory);
            rmdir( $dir->getName() );
        }
    }
    
    /**
     * Copy contents of the folder from another folder
     * @param Sys_Dir $dirOriginal
     */
    public function cloneFromFolder( Sys_Dir $dirOriginal )
    {
        if ( !$dirOriginal->exists() ) {
            throw new App_Exception( 'Original folder doesnds\'t exists '.$dirOriginal->getName() );
        }
        if ( !$this->exists() ) {
            $this->create( '', true );
        }
        
        // precreate all nececcaru folders
        $strBase = $dirOriginal->getName();
        $arrDirs = $dirOriginal->getAllDirs(); sort( $arrDirs );
        foreach ( $arrDirs as $strDir ) {
            $strRelativeDest = $this->_replaceBase( $strDir, $strBase );
            
            Sys_Io::out( "Creating " . $strRelativeDest );
            if ( !is_dir( $strRelativeDest )) { mkdir( $strRelativeDest ); }
        }

        // copy all files
        $arrFiles = $dirOriginal->getFiles( '', true );
        foreach ( $arrFiles as $strFile ) {
            $strRelativeDest = $this->_replaceBase( $strFile, $strBase );
            if ( !file_exists( $strRelativeDest )) { copy( $strFile, $strRelativeDest ); }
        }
        
        return $this;
    }
    
    /*
     * Get list of files in the folder
     */
    public function getFiles( $strRegex = '', $bRecursive = true ) {
        $rz = array(); 
        $h = opendir( $this->getName() );
        $arrInner = array();
        while( $h && $pn = readdir( $h ) ) {
            if ( $pn != '.' && $pn != '..' ) {
                
                $strFull = $this->getName() . '/' . $pn;
                if ( is_dir( $strFull ) ) {
                    $arrInner[] = $strFull;
                } else if ( $strRegex == '' || preg_match( $strRegex, $pn ) ) { 
                    $rz [] = $strFull;
                }
            }
        }
            
        if ( $h ) { closedir( $h ); }
        if ( $bRecursive ) {
            foreach ( $arrInner as $strInnerDir ) {
                $dir  = new Sys_Dir( $strInnerDir );
                $arrList = $dir->getFiles( $strRegex );
                foreach ( $arrList as $strFile ) { $rz [] = $strFile; }     
            }
        } 
        return $rz;        
    }
    
    /**
     * Get List of inner sub-folders by regular expression, 
     * 
     * 
     * @param string $strRegex
     * @return array
     */
    public function getDirs( $strRegex = '' ) {
        $h = opendir( $this->getName() );
        $rz = array(); // collecting result
        while( $h && $pn = readdir( $h ) ) {
            if ( substr( $pn, 0, 1 ) != '.' ) {
                
                $strFull = $this->getName() . '/' . $pn;
                // Sys_Io::out( $strFull );
                
                if ( is_dir( $strFull ) && ( $strRegex == '' || preg_match( $strRegex, $pn ) ) ) { 
                    $rz [] = $strFull;
                }
            }
        }
        if ( $h ) { closedir( $h ); }
        return $rz;        
    }
    
    /**
     * Getting all sub folders of current folder recursively
     * @param string $strRegex
     * @return array
     */
    public function getAllDirs( $strRegex = '' )
    {
        $arrDirs = array();
        foreach ( $this->getDirs( $strRegex ) as $strDir ) {
            $arrDirs[ $strDir ] = $strDir;
            
            $dirFolder = new Sys_Dir( $strDir );
            foreach ( $dirFolder->getAllDirs( $strRegex ) as $strSubdir ) {
                $arrDirs[ $strSubdir ] = $strSubdir;
            }
        }
        return $arrDirs;
    }
}
