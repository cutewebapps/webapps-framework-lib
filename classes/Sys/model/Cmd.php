<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */
class Sys_Cmd
{
    /** @var Sys_Cmd */
    static protected $_instance = null;

    /** @var int */
    protected $_nLastReturn = -1;
    /** @var string */
    protected $_strLastCommand = '';
    /** @var string */
    protected $_strLastOutput = '';
    /** @var string */
    protected $_strLastErrorStream = '';
    /** @var string */
    protected $_strLastWriteStream = '';
    /** @var resource */
    protected $_resourceLastProcess = null;

    /**
     * @return string
     */
    public function getLastCommand() 
    {
        return $this->_strLastCommand;
    }
    /**
     * @return int
     */
    public function getLastResult() 
    {
        return $this->_nLastReturn;
    }
    /**
     * @return string
     */
    public function getLastOutput()
    {
        return $this->_strLastOutput;
    }
    /**
     * @return string
     */
    public function getLastErrorStream()
    {
        return $this->_strLastErrorStream;
    }
    /**
     * @return string
     */
    public function getLastWriteStream()
    {
        return $this->_strLastWriteStream;
    }

    /**
     * Flushes current output. *WARNING* This function is not for Zend Framework UI usage!
     * Use it carefully only for continuous operations in controllers,
     * which are running from console!
     * @return void
     * */
    public static function flu ()
    {
        $st = ob_get_status();
        if (! empty($st)) ob_flush();
        flush();
    }

    /** @return string **/
    public function runShellCommand( $cmd, $input = '', $output = true)
    {

        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'));
        $pipes = array();
        $this->_nLastReturn = -1;
        $this->_strLastCommand = $cmd;
        $this->_strLastOutput = '';
 

        $this->_resourceLastProcess = proc_open($cmd, $descriptorspec, $pipes);

        if ( is_resource( $this->_resourceLastProcess ) ) {
            fwrite($pipes[0], $input);
            fclose($pipes[0]);
            for( $i = 1; $i <= 2; $i ++ ) {
                $s = '';
                do {
                    $s = fgets($pipes[$i], 128);
                    $this->_strLastOutput .= $s;

                    if ( $i == 1 )
                        $this->_strLastWriteStream .= $s;
                    else if ( $i == 2 )
                        $this->_strLastErrorStream .= $s;

                    if ($output) { echo $s; self::flu();}

                } while ( $s );
                fclose($pipes[ $i ]);
            }
            $this->_nLastReturn = proc_close( $this->_resourceLastProcess );
        }

        return $this->_strLastOutput;
    }

    /*
     * Runs command and returns its output in result.
     * *WARNING* Use it in UI only if you're 100% sure that operation is fast!
     */
    public static function run ( $cmd, $input = '', $output = true )
    {
        return self::getInstance()->runShellCommand( $cmd, $input, $output );
    }

    /**
    * @return string
    */
    public static function getCommandPath( $strWhich )
    {
        return trim( self::run( 'which '.$strWhich, '', false ));
    }

    /**
     * get instance of the standard
     * @return Sys_Cmd
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}