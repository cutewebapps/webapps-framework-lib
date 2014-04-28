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
class DBx_Table_Row extends DBx_Table_Row_Abstract
{
    /**
    * @return string
    * @warning: late static binding is supported only in PHP 5.3 ++,
    * thats why you shoudl redeclare status functions from this class
    * for each descendant if you're planning to use earlier PHP versions
    */ 
    public static function getClassName()
    {
        return get_called_class();
    }
    /**
     * @return object of Table
     */
    public static function Table()
    {
        $strRowClass = self::getClassName();
        $strTableClass = $strRowClass . '_Table';
        return new $strTableClass();
    }
    /**
     * @return object of Form
     */
    public static function Form( $strFormName )
    {
        $strRowClass =  self::getClassName();
        $strTableClass = $strRowClass . '_Form_'.$strFormName;
        return new $strTableClass();
    }
    /**
     * @return string
     */
    public static function FormClass( $strFormName  )
    {
        $strRowClass =  self::getClassName();
        $objForm = forward_static_call( array( $strRowClass, 'Form'), $strFormName );
        if ( is_object( $objForm ) ) {
            return get_class( $objForm );
        } else {
            return  $strRowClass . '_Form_'.$strFormName;
        }
    }
    /**
     * @return string of Table class;
     */
    public static function TableClass()
    {
        $strRowClass =  self::getClassName();
        return $strRowClass . '_Table';
    }
    /**
     * @return string of Table physical name;
     */
    public static function TableName()
    {
        return self::Table()->getTableName();
    }
    /**
     * @return object of Table_Rowset 
     */
    public static function RowSet()
    {
        $strRowClass = self::getClassName();
        $strTableClass = $strRowClass . '_List';
        return new $strTableClass();
    }


    /**
      * @return int
      */
    public function getId()
    {
        $strIdFieldName = $this->_table->getIdentityName();
        return $this->$strIdFieldName;
    }
    /**
    * Заполняет поле на измение
    * @author norbis
    * @return bool
    */
    public function isChanged($strFieldName)
    {
        if (in_array($strFieldName, array_keys($this->_cleanData)) && $this->_cleanData[$strFieldName] == $this->$strFieldName){
            return FALSE;
        }
        return TRUE;
    }

    /**
      * @return mixed
      */
    public function getOldData($strFieldName)
    {
        if (!in_array($strFieldName, array_keys($this->_cleanData))){
            throw new DBx_Table_Row_Exception("Specified column \"{$strFieldName}\" is not in the row");
        }
        return $this->_cleanData[$strFieldName];
    }

    /**
     * Возвращает флаг сохранения объекта в БД.
     * @return boolean
     */
    public function isSaved()
    {
        return !empty($this->_cleanData);
    }

    /**
     * @param string|object $table класс таблицы
     * @param string $strKeyField    - field from external table
     * @param string $strJoinedField - field from current table - leave empty if we expect a join always
     */
    public function getJoinedObject( $table, $strKeyField = '', $strJoinedField = '' )
    {
        $tblObject  = null; $strTableClass = '';
        if ( is_object( $table ) ) {
            $tblObject = $table;
            $strTableClass = get_class( $tblObject );
        } else {
            $strTableClass = $table;
            $tblObject = new $strTableClass();
        }
        
        if ( $strKeyField == '' ) {
            // auto-detect fields to join looking in reference map
            if ( isset( $this->_referenceMap[ $strTableClass ] ) ) {
                $strKeyField = $this->_referenceMap[ $strTableClass ]['refColumns'];
                $strJoinedField = $this->_referenceMap[ $strTableClass ]['columns'];
            } else {
                throw new DBx_Table_Row_Exception( 'No table class in Reference Map' );
            }
        }

        $objResult = null;
    	if ( isset( $this->$strKeyField ) ) {
    		$objResult = $tblObject->createRow();
                $strKey = $value = '';
    		foreach ( $objResult->toArray() as $strKey => $value )
    			$objResult->$strKey = $this->_data[ $strKey ];
    	} else {
    		$objResult = $tblObject->find( $this->$strJoinedField )->current();
    	}
    	return $objResult;
    }


    /**
      * function for further overloading
      * @return boolean
      */
    public function canBeEdited()
    {
	return true;
    }

    /**
      * function for further overloading
      * @return boolean
      */
    public function canBeDeleted()
    {
	return true;
    }
    
    /**
     * 
     * @return boolean
     */
    public function canBeViewed()
    {
        return true;
    }

}
