<?php

/*
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

/**
 * Sys_ZipFile - allows to create a Zip file in memory
 * 
 * @TODO: Netbeans shows a couple of warnings. All @ should be removed.
 */

class Sys_ZipFile 
{
    var $datasec = array();
    var $ctrl_dir = array();
    var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
    var $old_offset = 0;
    
    /**
     * Add folder 
     * @param string $name
     * @return void
     */
    function add_dir($name) 
    {
        $name = str_replace("\\", "/", $name);
        $fr = "\x50\x4b\x03\x04";
        $fr .= "\x0a\x00";
        $fr .= "\x00\x00";
        $fr .= "\x00\x00";
        $fr .= "\x00\x00\x00\x00";
        $fr .= pack("V",0);
        $fr .= pack("V",0);
        $fr .= pack("V",0);
        $fr .= pack("v", strlen($name) );
        $fr .= pack("v", 0 );
        $fr .= $name;
        $fr .= pack("V",isset( $crc ) ? $crc : '');
        $fr .= pack("V",isset( $c_len ) ? $c_len  : '' );
        $fr .= pack("V",isset( $unc_len ) ? $unc_len : '' );
        $this -> datasec[] = $fr;
        $new_offset = strlen(implode("", $this->datasec));
        $cdrec = "\x50\x4b\x01\x02";
        $cdrec .="\x00\x00";
        $cdrec .="\x0a\x00";
        $cdrec .="\x00\x00";
        $cdrec .="\x00\x00";
        $cdrec .="\x00\x00\x00\x00";
        $cdrec .= pack("V",0);
        $cdrec .= pack("V",0);
        $cdrec .= pack("V",0);
        $cdrec .= pack("v", strlen($name) );
        $cdrec .= pack("v", 0 );
        $cdrec .= pack("v", 0 );
        $cdrec .= pack("v", 0 );
        $cdrec .= pack("v", 0 );
        $ext = "\x00\x00\x10\x00";
        $ext = "\xff\xff\xff\xff";
        $cdrec .= pack("V", 16 );
        $cdrec .= pack("V", $this -> old_offset );
        $this -> old_offset = $new_offset;
        $cdrec .= $name;
        $this -> ctrl_dir[] = $cdrec;
    }

    /**
     * 
     * @param string $data
     * @param string $name
     * @return void
     */
    function add_file($data, $name) {
       $name = str_replace("\\", "/", $name);
       $fr = "\x50\x4b\x03\x04";
       $fr .= "\x14\x00";
       $fr .= "\x00\x00";
       $fr .= "\x08\x00";
       $fr .= "\x00\x00\x00\x00";
       $unc_len = strlen($data);
       $crc = crc32($data);
       $zdata = gzcompress($data);
       $zdata = substr( substr($zdata, 0, strlen($zdata) - 4), 2);
       $c_len = strlen($zdata);
       $fr .= pack("V",$crc);
       $fr .= pack("V",$c_len);
       $fr .= pack("V",$unc_len);
       $fr .= pack("v", strlen($name) );
       $fr .= pack("v", 0 );
       $fr .= $name;
       $fr .= $zdata;
       $fr .= pack("V",$crc);
       $fr .= pack("V",$c_len);
       $fr .= pack("V",$unc_len);
       $this -> datasec[] = $fr;
       $new_offset = strlen(implode("", $this->datasec));
       $cdrec = "\x50\x4b\x01\x02";
       $cdrec .="\x00\x00";
       $cdrec .="\x14\x00";
       $cdrec .="\x00\x00";
       $cdrec .="\x08\x00";
       $cdrec .="\x00\x00\x00\x00";
       $cdrec .= pack("V",$crc);
       $cdrec .= pack("V",$c_len);
       $cdrec .= pack("V",$unc_len);
       $cdrec .= pack("v", strlen($name));
       $cdrec .= pack("v", 0 );
       $cdrec .= pack("v", 0 );
       $cdrec .= pack("v", 0 );
       $cdrec .= pack("v", 0 );
       $cdrec .= pack("V", 32 );
       $cdrec .= pack("V", $this -> old_offset );
       $this -> old_offset = $new_offset;
       $cdrec .= $name;
       $this -> ctrl_dir[] = $cdrec;
   }
   /**
    * @return string
    */
   function file() 
   {
       $data = implode("", $this -> datasec);
       $ctrldir = implode("", $this -> ctrl_dir);
       return
           $data.
           $ctrldir.
           $this -> eof_ctrl_dir.
           pack("v", sizeof($this -> ctrl_dir)).
           pack("v", sizeof($this -> ctrl_dir)).
           pack("V", strlen($ctrldir)).
           pack("V", strlen($data)).
           "\x00\x00";
   }
}

