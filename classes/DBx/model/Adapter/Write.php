<?php

abstract class DBx_Adapter_Write extends DBx_Adapter_Abstract
{
    
    public function __construct($config)
    {
        parent::__construct( $config );
    }

    abstract public function isWriterFor( $strReaderClass );
    

    public function queryWrite($sql, $bind = array())
    {
       // connect to the database if needed
        $this->_connect();

        // prepare and execute the statement with profiling
        $stmt = $this->prepare($sql);
        $stmt->execute($bind);

        // return the results embedded in the prepared statement object
        return $stmt;
    }

    /**
     * Leave autocommit mode and begin a transaction.
     *
     * @return DBx_Adapter_Abstract
     */
    public function beginTransaction()
    {
        $this->_connect();
        $q = $this->_profiler->queryStart('begin', DBx_Profiler::TRANSACTION);
        $this->_beginTransaction();
        $this->_profiler->queryEnd($q);
        return $this;
    }

    /**
     * Commit a transaction and return to autocommit mode.
     *
     * @return DBx_Adapter_Abstract
     */
    public function commit()
    {
        $this->_connect();
        $q = $this->_profiler->queryStart('commit', DBx_Profiler::TRANSACTION);
        $this->_commit();
        $this->_profiler->queryEnd($q);
        return $this;
    }

    /**
     * Roll back a transaction and return to autocommit mode.
     *
     * @return DBx_Adapter_Abstract
     */
    public function rollBack()
    {
        $this->_connect();
        $q = $this->_profiler->queryStart('rollback', DBx_Profiler::TRANSACTION);
        $this->_rollBack();
        $this->_profiler->queryEnd($q);
        return $this;
    }

    /**
     * Inserts a table row with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $bind Column-value pairs.
     * @return int The number of affected rows.
     */
    public function insert($table, array $bind)
    {
        // extract and quote col names from the array keys
        $cols = array();
        $vals = array();
        $i = 0;
        foreach ($bind as $col => $val) {
            $cols[] = $this->quoteIdentifier($col, true);
            if ($val instanceof DBx_Expr) {
                $vals[] = $val->__toString();
                unset($bind[$col]);
            } else {
                if ($this->supportsParameters('positional')) {
                    $vals[] = '?';
                } else {
                    if ($this->supportsParameters('named')) {
                        unset($bind[$col]);
                        $bind[':col'.$i] = $val;
                        $vals[] = ':col'.$i;
                        $i++;
                    } else {
                        throw new DBx_Adapter_Exception(get_class($this) ." doesn't support positional or named binding");
                    }
                }
            }
        }

        // build the statement
        $sql = "INSERT INTO "
             . $this->quoteIdentifier($table, true)
             . ' (' . implode(', ', $cols) . ') '
             . 'VALUES (' . implode(', ', $vals) . ')';

        // execute the statement and return the number of affected rows
        if ($this->supportsParameters('positional')) {
            $bind = array_values($bind);
        }
        $stmt = $this->queryWrite($sql, $bind);
        $result = $stmt->rowCount();
        return $result;
    }
    
   /**
     * $this->getTableName()
     * @param array $arrRecords
     * @return query result
     */
    public function insertArray( $table, $arrRecords)
    {
        if (count($arrRecords) == 0)
            return false;

        $arrFields = array();
        $arrFieldsIndex = array();
        $arrValues = array();
        foreach ($arrRecords as $i => $arrRecord) {
            $arrColumnValues = array();
            foreach ($arrRecord as $strField => $strValue) {
                if (!isset($arrFieldsIndex[$strField])) {
                    $arrFieldsIndex[$strField] = count($arrFieldsIndex);
                    $arrFields[$arrFieldsIndex[$strField]] = $this->quoteIdentifier( $strField , true);
                }
                $nIndex = $arrFieldsIndex[$strField];
                $arrColumnValues[$nIndex] = $this->_quote($strValue);
            }
            $arrValues [] = '(' . implode(',', $arrColumnValues) . ')';
        }

        $sql = "INSERT INTO " . $this->quoteIdentifier( $table, true ). ' (' . implode(',', $arrFields)
                . ') VALUES ' . "\n" . implode(',' . "\n", $arrValues) . '';
        
        $stmt = $this->queryWrite($sql);
        $result = $stmt->rowCount();
        return $result;
    }    

    /**
     * Updates table rows with specified data based on a WHERE clause.
     *
     * @param  mixed        $table The table to update.
     * @param  array        $bind  Column-value pairs.
     * @param  mixed        $where UPDATE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function update($table, array $bind, $where = '')
    {
        /**
         * Build "col = ?" pairs for the statement,
         * except for DBx_Expr which is treated literally.
         */
        $set = array();
        $i = 0;
        foreach ($bind as $col => $val) {
            if ($val instanceof DBx_Expr) {
                $val = $val->__toString();
                unset($bind[$col]);
            } else {
                if ($this->supportsParameters('positional')) {
                    $val = '?';
                } else {
                    if ($this->supportsParameters('named')) {
                        unset($bind[$col]);
                        $bind[':col'.$i] = $val;
                        $val = ':col'.$i;
                        $i++;
                    } else {
                        /** @see DBx_Adapter_Exception */
                        throw new DBx_Adapter_Exception(get_class($this) ." doesn't support positional or named binding");
                    }
                }
            }
            $set[] = $this->quoteIdentifier($col, true) . ' = ' . $val;
        }

        $where = $this->_whereExpr($where);

        /**
         * Build the UPDATE statement
         */
        $sql = "UPDATE "
             . $this->quoteIdentifier($table, true)
             . ' SET ' . implode(', ', $set)
             . (($where) ? " WHERE $where" : '');

        /**
         * Execute the statement and return the number of affected rows
         */
        $stmt = null;
        if ($this->supportsParameters('positional')) {
            $stmt = $this->queryWrite($sql, array_values($bind));
        } else {
            $stmt = $this->queryWrite($sql, $bind);
        }
        $result = $stmt->rowCount();
        return $result;
    }

    /**
     * Deletes table rows based on a WHERE clause.
     *
     * @param  mixed        $table The table to update.
     * @param  mixed        $where DELETE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function delete($table, $where = '')
    {
        $where = $this->_whereExpr($where);

        /**
         * Build the DELETE statement
         */
        $sql = "DELETE FROM "
             . $this->quoteIdentifier($table, true)
             . (($where) ? " WHERE $where" : '');

        /**
         * Execute the statement and return the number of affected rows
         */
        $stmt = $this->queryWrite($sql);
        $result = $stmt->rowCount();
        return $result;
    }

    /**
     * Convert an array, string, or DBx_Expr object
     * into a string to put in a WHERE clause.
     *
     * @param mixed $where
     * @return string
     */
    protected function _whereExpr($where)
    {
        if (empty($where)) {
            return $where;
        }
        if (!is_array($where)) {
            $where = array($where);
        }
        foreach ($where as $cond => &$term) {
            // is $cond an int? (i.e. Not a condition)
            if (is_int($cond)) {
                // $term is the full condition
                if ($term instanceof DBx_Expr) {
                    $term = $term->__toString();
                }
            } else {
                // $cond is the condition with placeholder,
                // and $term is quoted into the condition
                $term = $this->quoteInto($cond, $term);
            }
            $term = '(' . $term . ')';
        }

        $where = implode(' AND ', $where);
        return $where;
    }


    /**
     * Gets the last ID generated automatically by an IDENTITY/AUTOINCREMENT column.
     *
     * As a convention, on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2), this method forms the name of a sequence
     * from the arguments and returns the last id generated by that sequence.
     * On RDBMS brands that support IDENTITY/AUTOINCREMENT columns, this method
     * returns the last value generated for such a column, and the table name
     * argument is disregarded.
     *
     * @param string $tableName   OPTIONAL Name of table.
     * @param string $primaryKey  OPTIONAL Name of primary key column.
     * @return string
     */
    abstract public function lastInsertId($tableName = null, $primaryKey = null);

    /**
     * Begin a transaction.
     */
    abstract protected function _beginTransaction();

    /**
     * Commit a transaction.
     */
    abstract protected function _commit();

    /**
     * Roll-back a transaction.
     */
    abstract protected function _rollBack();

    /**
     * Prepare a statement and return a PDOStatement-like object.
     *
     * @param string $sql SQL query
     * @return DBx_Statement|PDOStatement
     */
    abstract public function prepare($sql);


    public function dropTable($strTableName, $bDebug = false )
    {
    	$strSQL = 'DROP TABLE ' . ($strTableName );
    	$result = $this->queryWrite( $strSQL );
    	return $result;
    }

    public function addTableSql(
            $strTableName, 
            $strSql, 
            $strPrimaryKey = '', 
            $strEngine = 'InnoDB', 
            $strDefaultCharset = 'UTF8')
    {
    	// remove line comments
    	$strSql = preg_replace( '/--.*$/imU', '', $strSql ); //
    	// remove comma at the end of statement (as it will be often left there by accident)
    	$strSql = preg_replace( '/\,\s*$/i', '', $strSql );
        
        $strSQL = '';
        $strSQL .= 'CREATE TABLE '.$strTableName;
        $strSQL .= ' ( '.$strSql;
        if ( $strPrimaryKey != '' )
        	$strSQL .= ', '."\n".'PRIMARY KEY (' . $strPrimaryKey  . ')';
        $strSQL .= ' ) ';
        $strSQL .= " ENGINE={$strEngine} ";
        if ( $strDefaultCharset != '' )
            $strSQL .= " DEFAULT CHARSET={$strDefaultCharset} ";
            
        $result = $this->_query( $strSQL );
        return $result;
    }            
    
}