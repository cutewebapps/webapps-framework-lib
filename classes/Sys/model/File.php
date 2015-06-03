<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */


class Sys_File_Exception extends Exception {}

class Sys_File 
{
    private $_strFileName = '';
    public function __construct ($strName)
    {
        $this->_strFileName = str_replace( '\\', '/', $strName );
        $this->_strFileName = preg_replace( '@^file://@', '', $this->_strFileName );
    }
    
    public function getName ()
    {
        return $this->_strFileName;
    }
    
    public function exists ($bCached = false)
    {
        global $callFileExists;
        if (! $bCached)
            return file_exists($this->getName());
            
        $filename = $this->getName();
        $dir = dirname(realpath($filename));
        $fn = basename(realpath($filename));
        if (! isset($callFileExists))
            $callFileExists = array();
        if (! isset($callFileExists[$dir])) {
            $callFileExists[$dir] = array();
            if (is_dir($dir)) {
                $h = opendir($dir);
                while ($h && (($f = readdir($h)) !== false))
                    if (substr($f, 0, 1) != '.') {
                        $callFileExists[$dir][$f] = $f;
                    }
                closedir($h);
            }
        }
        return isset($callFileExists[$dir][$fn]);
    }
    
    public function isWritable ()
    {
        return is_writable($this->getName());
    }
    
    public function delete ()
    {
        if (! file_exists($this->getName()))
            return false;
        if (! is_writeable(dirname($this->getName())))
            throw new Sys_File_Exception( 'Folder '. dirname( $this->getName() ). ' is not writable' );
        return unlink($this->getName());
    }
    
    public function copy ($dest, $overwrite = true)
    {
        if (! $this->exists() || is_file($dest) && ! $overwrite) {
            return false;
        }
        return copy($this->path, $dest);
    }
    
    public function link ( $strSource ) 
    {
        if ( PHP_OS != 'WINNT') {
            $strCommand = 'ln -s '.escapeshellarg( $strSource ).' '.escapeshellarg( $this->getName() );
            // Sys_Io::out( $strCommand );
            Sys_Cmd::run( $strCommand, '', true);
        } else {
            // TRICKY: works only for Vista ++
            // this branch is not checked completely
            // Sys_Cmd::run( 'mklink '.escapeshellarg( $this->getName() ).' '.escapeshellarg( $strSource ) );
        }
    }
    
    public function checkDirectory ($strChMod = 0777)
    {
        if (dirname($this->getName()) != '' and 
            !file_exists( dirname($this->getName() ) ) ) {

            $dir = new Sys_Dir(dirname($this->getName() ) );
	    // suppress error that can be possible on creation
            try{ $dir->create( $strChMod, true); } catch( Exception $e ) { };
        }
    }
    public function read ( $onLockNotCritical = true)
    {
        $filename = $this->getName();
        
        if (! file_exists($filename) or ! is_readable($filename)) {
            return false;
        }
        $f = fopen($filename, 'r');
        if (! $f) {
            return ''; //note: no error, just empty contents
        }
        $strContent = '';
        if (flock($f, LOCK_SH)) {
            clearstatcache();
            $intFileSize = filesize($filename);
            if ($intFileSize > 0) {
                $strContent = fread($f, $intFileSize);
            } else {
                $strContent = '';
            }
            flock($f, LOCK_UN);
            fclose($f);
        } else {
            if ($onLockNotCritical)
                return '';
            else
                throw new Sys_File_Exception( 'Error on reading of file: ' . $filename );
        }
        return $strContent;
    }
    /**
     * @deprecated - use write() instead
     */
    public function save($c, $emptyAllowed = false, $strChMod = '')
    {
        return $this->write($c, $emptyAllowed, $strChMod );
    }
    public function write ($c, $emptyAllowed = false, $strChMod = '')
    {
        if (! $emptyAllowed and $this->getName() == '') {
            return false;
        }
        $this->checkDirectory();
        if (! $this->exists() and ! is_writable(dirname($this->getName()))) {
            return false;
        }
        if ($this->exists() and ! $this->isWritable()) {
            throw new Sys_File_Exception( 'Cannot write to a file - file is not writable '.$this->getName());
        }
        $f = fopen($this->getName(), 'wb');
        if (! $f) {
            throw new Sys_File_Exception( 'Cannot write to a file - file cannot be opened '.$this->getName());
        }
        if (flock($f, LOCK_EX)) {
            fwrite($f, $c);
            flock($f, LOCK_UN);
            fclose($f);
            if ($strChMod != '')
                chmod($this->getName(), $strChMod);
        }
        return true;
    }
    
    public function append ($c, $strChMod = '')
    {
        if ($c == '')
            return false;
        if ($this->getName() == '')
            throw new Sys_File_Exception( 'Cannot append to an empty File' );
        if (! $this->exists())
            return $this->write($c);
        if ($this->exists() and ! $this->isWritable())
            throw new Sys_File_Exception( 'Cannot append to a file - file is not writable '.$this->getName());
        $f = fopen($this->getName(), 'ab');
        if (! $f)
            throw new Sys_File_Exception( 'Cannot append to a file - file cannot be opened  '.$this->getName());
        fwrite($f, $c);
        fclose($f);
        
        if ($strChMod != '') {
           chmod($this->getName(), $strChMod);
        }
        return true;
    }

}