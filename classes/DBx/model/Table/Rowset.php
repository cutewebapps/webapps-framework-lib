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
class DBx_Table_Rowset extends DBx_Table_Rowset_Abstract
{
    /**
     * get array of objects from this rowset object,
     * preimary key of the record will be the key in this array, 
     * and record objects will be the values
     * 
     * @param string|null $strColumnName
     * @param string|null $strKeyColumn
     * @return array
     */
    public function getAsArray( $strColumnName = null, $strKeyColumn = null )
    {
        $arrRecords = array();
        foreach ($this as $objRow){
            $key = $strKeyColumn == null ? $objRow->getId() : $objRow->$strKeyColumn;
            $val = ( $strColumnName == null ) ? $objRow : $objRow->$strColumnName;
            $arrRecords[ $key ] = $val;
        }
        return $arrRecords;
    }
    /**
     * get array of array of objects from this rowset object,
     * @param string|null $strColumnName
     * @param string|null $strKeyColumn
     * @return array
     */
    public function getAsArrays( $strColumnName = null, $strKeyColumn = null )
    {
        $arrRecords = array();
        foreach ($this as $objRow){
            $key = $strKeyColumn == null ? $objRow->getId() : $objRow->$strKeyColumn;
            $val = ( $strColumnName == null ) ? $objRow : $objRow->$strColumnName;
            
            if ( !isset( $arrRecords[ $key ] ) ) $arrRecords[ $key ]= array();
            $arrRecords[ $key ][] = $val;
        }
        return $arrRecords;
    }
    
    /**
     * 
     * @param string | null  $strColumnName
     * @return array
     */
    public function getIds($strColumnName = null)
    {
        $arrValues = array();
        $strColumnName = (is_null($strColumnName)) ? $this->_table->getIdentityName() : $strColumnName;
        foreach ($this as $objRow){
            $arrValues[] = $objRow->$strColumnName;
        }

        return $arrValues;
    }

    /**
     * Check whether the list of objects contain the record with given values
     * @param array $arrParams
     * @return boolean
     */
    public function hasRecord( $arrParams = array() )
    {
        foreach ( $this as $objRow ) {
            $bMatch = true;
            foreach ( $arrParams as $strField => $strValue ) {
                if ( $objRow->$strField != $strValue  ) {
                    $bMatch = false; break;
                }
            }
            if ( $bMatch ) return true;
        }
        return false;
    }

    /**
     *
     * @param array $arrParams
     * @return boolean
     */
    public function hasNoRecord( $arrParams = array() )
    {
        foreach ( $this as $objRow ) {
            $bMatch = true;
            foreach ( $arrParams as $strField => $strValue ) {
                if ( $objRow->$strField != $strValue  ) {
                    $bMatch = false; break;
                }
            }
            if ( $bMatch ) return false;
        }
        return true;
    }


}
