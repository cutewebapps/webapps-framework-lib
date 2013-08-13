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
abstract class DBx_Adapter_Read extends DBx_Adapter_Abstract
{

    /**
     * Default class name for a DB statement.
     *
     * @var string
     */
    protected $_defaultStmtClass = 'DBx_Adapter_Statement';

    /**
     *
     * @var array
     */
    protected $_metaTables = NULL;


    public function __construct($config)
    {
        $options = array(
            DBx_Registry::FETCH_MODE             => $this->_fetchMode,
        );
        if (array_key_exists(DBx_Registry::FETCH_MODE, $options)) {
            if (is_string($options[DBx_Registry::FETCH_MODE])) {
                $constant = 'DBx_Registry::FETCH_' . strtoupper($options[DBx_Registry::FETCH_MODE]);
                if(defined($constant)) {
                    $options[DBx_Registry::FETCH_MODE] = constant($constant);
                }
            }
            $this->setFetchMode((int) $options[DBx_Registry::FETCH_MODE]);
        }


        parent::__construct( $config );
    }



    public function queryRead($sql, $bind = array())
    {
        // TODO: check that this SQL is READ-only
        return $this->_query( $sql, $bind );
    }

    public function queryFoundRows()
    {
        return intval( $this->fetchOne( 'SELECT FOUND_ROWS()') );
    }

    /**
     * Creates and returns a new DBx_Select object for this adapter.
     *
     * @return DBx_Select
     */
    public function select()
    {
        return new DBx_Select($this);
    }

    /**
     * Get the fetch mode.
     *
     * @return int
     */
    public function getFetchMode()
    {
        return $this->_fetchMode;
    }

    /**
     * Fetches all SQL result rows as a sequential array.
     * Uses the current fetchMode for the adapter.
     *
     * @param string|DBx_Select $sql  An SQL SELECT statement.
     * @param mixed                 $bind Data to bind into SELECT placeholders.
     * @param mixed                 $fetchMode Override current fetch mode.
     * @return array
     */
    public function fetchAll($sql, $bind = array(), $fetchMode = null)
    {
        if ($fetchMode === null) {
            $fetchMode = $this->_fetchMode;
        }
        $stmt = $this->queryRead($sql, $bind);
        $result = $stmt->fetchAll($fetchMode);
        return $result;
    }

    /**
     * Fetches the first row of the SQL result.
     * Uses the current fetchMode for the adapter.
     *
     * @param string|DBx_Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @param mixed                 $fetchMode Override current fetch mode.
     * @return array
     */
    public function fetchRow($sql, $bind = array(), $fetchMode = null)
    {
        if ($fetchMode === null) {
            $fetchMode = $this->_fetchMode;
        }
        $stmt = $this->queryRead($sql, $bind);
        $result = $stmt->fetch($fetchMode);
        return $result;
    }

    /**
     * Fetches all SQL result rows as an associative array.
     *
     * The first column is the key, the entire row array is the
     * value.  You should construct the query to be sure that
     * the first column contains unique values, or else
     * rows with duplicate values in the first column will
     * overwrite previous data.
     *
     * @param string|DBx_Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchAssoc($sql, $bind = array())
    {
        $stmt = $this->queryRead($sql, $bind);
        $data = array();
        $tmp = array();
        while ($row = $stmt->fetch(DBx_Registry::FETCH_ASSOC)) {
            $tmp = array_values(array_slice($row, 0, 1));
            $data[$tmp[0]] = $row;
        }
        return $data;
    }

    /**
     * Fetches the first column of all SQL result rows as an array.
     *
     * @param string|DBx_Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchCol($sql, $bind = array())
    {
        $stmt = $this->queryRead($sql, $bind);
        $result = $stmt->fetchAll(DBx_Registry::FETCH_COLUMN, 0);
        return $result;
    }

    /**
     * Fetches all SQL result rows as an array of key-value pairs.
     *
     * The first column is the key, the second column is the
     * value.
     *
     * @param string|DBx_Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchPairs($sql, $bind = array())
    {
        $stmt = $this->queryRead($sql, $bind);
        $data = array();
        while ($row = $stmt->fetch(DBx_Registry::FETCH_NUM)) {
            $data[$row[0]] = $row[1];
        }
        return $data;
    }

    /**
     * Fetches the first column of the first row of the SQL result.
     *
     * @param string|DBx_Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return string
     */
    public function fetchOne($sql, $bind = array())
    {
        $stmt = $this->queryRead($sql, $bind);
        $result = $stmt->fetchColumn(0);
        return $result;
    }


    /**
     * Return the most recent value from the specified sequence in the database.
     * This is supported only on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
     *
     * @param string $sequenceName
     * @return string
     */
    public function lastSequenceId($sequenceName)
    {
        return null;
    }

    /**
     * Generate a new value from the specified sequence in the database, and return it.
     * This is supported only on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
     *
     * @param string $sequenceName
     * @return string
     */
    public function nextSequenceId($sequenceName)
    {
        return null;
    }


    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    abstract public function listTables();

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME => string; name of database or schema
     * TABLE_NAME  => string;
     * COLUMN_NAME => string; column name
     * COLUMN_POSITION => number; ordinal position of column in table
     * DATA_TYPE   => string; SQL datatype name of column
     * DEFAULT     => string; default expression of column, null if none
     * NULLABLE    => boolean; true if column can have nulls
     * LENGTH      => number; length of CHAR/VARCHAR
     * SCALE       => number; scale of NUMERIC/DECIMAL
     * PRECISION   => number; precision of NUMERIC/DECIMAL
     * UNSIGNED    => boolean; unsigned property of an integer type
     * PRIMARY     => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    abstract public function describeTable($tableName, $schemaName = null);

    /**
     * Prepare a statement and return a PDOStatement-like object.
     *
     * @param string|DBx_Select $sql SQL query
     * @return DBx_Statement|PDOStatement
     */
    abstract public function prepare($sql);

    /**
     * Set the fetch mode.
     *
     * @param integer $mode
     * @return void
     * @throws DBx_Adapter_Exception
     */
    abstract public function setFetchMode($mode);

    /**
     * Adds an adapter-specific LIMIT clause to the SELECT statement.
     *
     * @param mixed $sql
     * @param integer $count
     * @param integer $offset
     * @return string
     */
    abstract public function limit($sql, $count, $offset = 0);


    /**
     * Check if the adapter supports real SQL parameters.
     *
     * @param string $type 'positional' or 'named'
     * @return bool
     */
    abstract public function supportsParameters($type);

    /**
     * Retrieve server version in PHP style
     *
     * @return string
     */
    abstract public function getServerVersion();

     /**
     * loading the list of tables meta info
     * @param boolean $bReload force reload
     */
    protected function _loadMetaTables($bReload = false )
    {
        if (!$this->_metaTables || $bReload) {
            $this->_metaTables = $this->fetchAll( 'SHOW TABLE STATUS' );
        }
    }

    public function hasTable( $strTable, $strSchema = null )
    {
        if ( $strSchema == null ) {
            $arrTables = $this->listTables( true );
        } else {
            // if we will me looking in foreign database
            $lstTables = $this->fetchAll( 'SHOW TABLE STATUS IN '.$strSchema );
            foreach( $lstTables as $arrTable ) {
                if ( strtolower( $arrTable['Name'] ) == strtolower( $strTable ))
                    return true;
            }
            return false;
        }
        return in_array( $strTable, $arrTables );
    }

    public function isTableEmpty( $strTable, $bReload = false )
    {
        $this->_loadMetaTables( $bReload );
        foreach( $this->_metaTables as $arrProps ) {
            if ( strtolower( $arrProps['Name'] ) == strtolower( $strTable )) {
                return $arrProps['Rows']  == 0;
            }
        }
        return false;
    }
}