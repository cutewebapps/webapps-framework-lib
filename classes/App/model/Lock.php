<?php

class App_Lock
{

    protected $strFunctionName = '';
    protected $nLockSecondsTimeout = '';

    /**
     *
     * @param string $strName - file name for lock
     * @param int $nTimeout in seconds
     */
    public function __construct($strName, $nTimeout = 600)
    {
        $this->strFunctionName = $strName;
        $this->nLockSecondsTimeout = $nTimeout;
        
            $dir = new Sys_Dir( $this->getLocksDir() );
            if ( !$dir->exists() ) {
                $dir->create( '', true );
                chmod($this->getLocksDir(), 0777);
            }
    }

    protected function getLocksDir()
    {
        return App_Application::getInstance()->getConfig()->cache_dir.'/locks';
    }

    /*
     * @return lock physical filename
     */

    protected function getLockFile()
    {
        return $this->getLocksDir() . '/' . $this->strFunctionName . '';
    }

    public function getLockName()
    {
        return $this->strFunctionName;
    }

    /**
     * returns false if the lock already exists
     * returns true if lock was just created
     * @return boolean 
     */
    public function lock()
    {
        clearstatcache();
        
        $file = new Sys_File( $this->getLockFile() );
        if ( $file->exists() ) {
            if (time() - filemtime($this->getLockFile()) < $this->nLockSecondsTimeout) {
                return false;
            }
            unlink($this->getLockFile());
        }
        
        $file->write( '' );
        if ( $file->exists() ) return true;

        return false;
    }

    /**
     * @return void
     */
    public function unlock()
    {
        $strLockFile = $this->getLockFile();
        if (file_exists($strLockFile))
            unlink($strLockFile);
    }

    /*
     * wait for unlock
     * @param $nSeconds number of seconds until timeout
     * @return false, it not succeeded
     */

    public function wait($nSeconds = 30)
    {
        for ($i = 0; $i < $nSeconds; $i++) {
            clearstatcache();
            if (!file_exists($this->getLockFile()))
                return true;
            usleep(5);
        }
        return false;
    }

}