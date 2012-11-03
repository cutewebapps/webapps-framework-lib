<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class Sys_Date
{
    const ISO   = 'ISO';
    const EURO  = 'EU';
    const US    = 'US';

    protected $_datetime = '';
    protected $_format = self::ISO;
    /**
     * Common function: get list of ascending dates in the given period
     * Ulike simple multiplication of seconds should not be vulnerable to the shift of dates in October!
     *
     * @param date $dt1
     * @param date $dt2
     * @param string $strFormat (optional)
     */
    public static function getList($dt1, $dt2, $strFormat = 'Y-m-d') {
        if ($dt1 > $dt2) {
            $t = $dt1; $dt1 = $dt2; $dt2 = $t;
        }
        $dtCurrent = date ( 'Y-m-d', strtotime ( $dt1 ) );
        $arrDates = array ();
        while ( $dtCurrent <= $dt2 ) {
            $arrDates [$dtCurrent] = date ( $strFormat, strtotime ( $dtCurrent ) );
            $dtCurrent = date ( 'Y-m-d', strtotime ( $dtCurrent ) + 25 * 60 * 60 );
        }
        return $arrDates;
    }

    /**
     * @param integer $nTime
     * @return Sys_Date
     */
    public static function fromTime( $nTime )
    {
        return new Sys_Date( date('Y-m-d H:i:s', $nTime), Sys_Date::ISO );
    }
    /**
     * @param string $strDate
     * @return Sys_Date
     */
    public static function fromIso( $strDate )
    {
        return new Sys_Date( $strDate, Sys_Date::ISO );
    }
    /**
     * @param string $strDate
     * @return Sys_Date
     */
    public static function fromUs( $strDate )
    {
        return new Sys_Date( $strDate, Sys_Date::US );
    }
    /**
     * @param string $strDate
     * @return Sys_Date
     */
    public static function fromEuro( $strDate )
    {
        return new Sys_Date( $strDate, Sys_Date::EURO );
    }

    protected $_strRenderEmpty = '';
    /**
     * @return Sys_Date
     */
    public function renderEmpty( $strEmpty )
    {
        $this->_strRenderEmpty = $strEmpty;
        return $this;
    }


    public function __construct( $strDate, $format  = '' )
    {
        if ( $format == '' ) $format = App_Application::getInstance()->getConfig()->dateformat;
        if ( $format == '' ) $format = self::ISO;
        
        $strTime = '';
        if ( preg_match( "@^([\/\.\d]+)\s+(\S+)$@", $strDate, $arrMatch )) {
            // split it, if the date was given with time
            $strDate = $arrMatch[ 1 ];
            $strTime = $arrMatch[ 2 ];
        }

        $H = '00'; $i = '00'; $s = '00';
        if ( $strTime ) {
            if ( preg_match( "@^(\d]+):(\d]+):(\d]+)@", $strDate, $arrMatch )) {
                $H = $arrMatch[1];
                $i = $arrMatch[2];
                $s = $arrMatch[3];
            } else if ( preg_match( "@^(\d]+):(\d]+)@", $strDate, $arrMatch )) {
                $H = $arrMatch[1];
                $i = $arrMatch[2];
                $s = '00';
            }
        }
        
        switch( $format ) {
            case self::EURO:
                $arrParts = explode( ".", $strDate );
                $nDay     = $arrParts[2];
                $nMonth   = $arrParts[1];
                $nYear    = $arrParts[0];
                $this->_datetime = date('Y-m-d', strtotime( $nYear.'-'.$nMonth.'-'.$nDay ) );
                $this->_datetime .= ' '.$H.':'.$i.':'.$s;
                break;
            case self::US:
                $arrParts = explode( "/", $strDate );
                $nMonth   = $arrParts[0];
                $nDay     = $arrParts[1];
                $nYear    = $arrParts[2];
                $this->_datetime = date('Y-m-d', strtotime( $nYear.'-'.$nMonth.'-'.$nDay ) );
                $this->_datetime .= ' '.$H.':'.$i.':'.$s;
                break;
            default:
                $this->_datetime = $strDate;
        }

    }

    /**
     * @return boolean
     */
    public function isEmpty()
    {
        return ( $this->_datetime == '' ||
                substr( $this->_datetime, 0, 4 ) == '0000'||
                0 >= date( 'Y', strtotime( $this->_datetime ) ));
    }

    /**
     *
     * @param ISO|EURO|US $format
     * @return string
     */
    public function getDate( $format = '' )
    {
        if ( $format == '' ) $format = App_Application::getInstance()->getConfig()->dateformat;
        if ( $format == '' ) $format = $this->_format;

        if ( $this->isEmpty() ) {
            return $this->_strRenderEmpty;
        }

        switch( $format ) {
            case self::EURO:
                return date('d.m.Y', strtotime( $this->_datetime ) );
                break;
            case self::US:
                return date('m/d/Y', strtotime( $this->_datetime ) );
                break;
            default:
                return date('Y-m-d', strtotime( $this->_datetime ) );
        }
    }

    /*
     * @return string
     */
    public function getTime24()
    {
        return date('H:i', strtotime( $this->_datetime ));
    }
    /*
     * @return string
     */
    public function getTime12()
    {
        return date('g:iA', strtotime( $this->_datetime ));
    }

    /*
     * @param ISO|US|EURO
     * @return string
     */
    public function getDateTime( $format = '' )
    {
        if ( $format == '' ) $format = $this->_format;
        
        if ( $format == self::EURO || $format == self::ISO  ) {
            return $this->getDate().' '.$this->getTime24();
        } else {
            return $this->getDate().' '.$this->getTime12();
        }
    }

    /*
     * @return string
     */
    public function __toString()
    {
        return $this->_datetime;
    }
}
