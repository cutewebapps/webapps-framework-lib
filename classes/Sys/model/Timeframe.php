<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */


/**
 * use this class for debug only!
 * this class allows you to measure time of diff. parts of the code:
 *
 * Simplest example:
 *
 * $timeframe = new Sys_Timeframe();  // tick start
 *                                    // .... something happened here
 * echo $timeframe->get();            // display how much time passed (in user-friendly version)
 *
 */
class Sys_Timeframe
{
    var $fltStart = null;
    var $fltEnd = null;

    function __construct()
    {
        $this->start();
    }

    protected function getMicrotime()
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }

    public function start()
    {
        $this->fltEnd = $this->fltStart = $this->getMicrotime();
    }

    public function stop()
    {
        $this->fltEnd  = $this->getMicrotime();
    }
    
    public function get()
    {
        if( $this->fltEnd  == $this->fltStart ) $this->stop();

        $fltDiff = ($this->fltEnd - $this->fltStart );
        $fltSeconds = intval( $fltDiff ) % 60 + sprintf('%.3f', $fltDiff - intval( $fltDiff ));

        $nMinutes = intval( (intval( $fltDiff ) % ( 60*60 ) ) / 60);
        $arrStr = array();
        if ( $nMinutes > 0 ) $arrStr []= $nMinutes.' minutes';
        $arrStr []= $fltSeconds.' seconds';
        return implode( ', ', $arrStr );
    }
    
    public function getCurrent()
    {
        $this->stop();
        return $this->get();
    }

}
