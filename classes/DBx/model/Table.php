<?php


class DBx_Table extends DBx_Table_Abstract
{
    /**
     * Documentation
     *
     * @var mixed
     */
    protected $_primary = 'ID';
    /**
     * @var string
     */
    protected $_strAdapterNameRead   = 'default';
    /**
     * @var string
     */
    protected $_strAdapterNameWrite  = 'default';

    /**
     * Array of table indexes
     *
     * @var mixed
     */
    protected $_indexes = NULL;

    /**
     * __construct() - For concrete implementation of DBx_Table
     *
     * @param string|array $config string can reference a key for a db adapter
     *                             OR it can reference the name of a table
     * @param array|DBx_Table_Definition $definition
     */
    public function __construct($config = array(), $definition = null)
    {
        if ($definition !== null && is_array($definition)) {
            $definition = new DBx_Table_Definition($definition);
        }

        if (!isset($config['read'])){
            $config['read'] =  $this->_strAdapterNameRead;
        }
        if (!isset($config['write'])){
            $config['write'] = $this->_strAdapterNameWrite;
        }
        
        //Sys_Debug::dumpDie( $config );
        
        /*if (is_string($config)) {
            if (Zend_Registry::isRegistered($config)) {
                trigger_error(__CLASS__ . '::' . __METHOD__ . '(\'registryName\') is not valid usage of Zend_Db_Table, '
                    . 'try extending DBx_Table_Abstract in your extending classes.',
                    E_USER_NOTICE
                    );
                $config = array(self::ADAPTER => $config);
            } else {
                // process this as table with or without a definition
                if ($definition instanceof DBx_Table_Definition
                    && $definition->hasTableConfig($config)) {
                    // this will have DEFINITION_CONFIG_NAME & DEFINITION
                    $config = $definition->getTableConfig($config);
                } else {
                    $config = array(self::NAME => $config);
                }
            }
        }*/
        parent::__construct($config);
        $this->setRowClasses();
    }


	/**
	 * Загружает информацию о индексах таблицы
	 * @todo Добавить "грамотную" загрузку составных (на несколько столбцов) индексов
	 * @return void
	 */
	protected function _loadIndexesMeta()
	{
	    $strSQL = 'SHOW INDEX FROM ';
	    $strSQL .= $this->getAdapterRead()->quoteIdentifier($this->getTableName());
	    $arrIndexes = $this->getAdapterRead()->fetchAll($strSQL);
	    foreach($arrIndexes as $arrIndex){
	        $this->_indexes[$arrIndex['Key_name']] = $arrIndex;
	    }
	}

    /**
    * Возвращает имя таблицы
    * @return string
    */
	public function getTableName()
	{
		return $this->_name;
//        return $this->info(self::NAME);
	}

	protected function setRowClasses()
	{
            $strObjectName = $this->getObjectName();
		// System_Loader::loadClass($strObjectName);
	    $this->setRowClass($strObjectName);
	    $this->setRowsetClass($strObjectName . '_List');
	}

    /**
    * Documentation
    * @author
    * @return mixed
    */
	protected function getObjectName()
	{
		$classes = explode('_', get_class($this));
		array_pop($classes);
		return implode('_', $classes);
	}

    /**
     * @return string
     * @author Igor Muravinets
     */
    public function getControllerName()
    {
        return $this->_strControllerName;
    }

    /**
     * @param array $data
     * @param $defaultSource
     * @return DBx_Row
     */
	public function createRow(array $data = array(), $defaultSource = null)
	{
	    if (is_null($defaultSource)){
	        $defaultSource = self::DEFAULT_DB_WRITE;
	    }
	    return parent::createRow($data, $defaultSource);
	}

	/**
	 * Get options list
	 * @param string $KeyField
	 * @param string $ValueField
	 * @param array $Filters
	 * @param mixed $mOrder The column(s) and direction to order by.
	 * @return System_Db_List
	 */
	public function getOptionsList($KeyField = 'ID', $ValueField = 'Name', $Filters = array(), $mOrder = null)
	{
		$select = $this->select();
        $select->from($this, array('key' => $KeyField, 'value' => $ValueField));
        if (is_array($Filters) && count($Filters)){
        	foreach ($Filters as $Filter) {
        		$select->where($Filter['cond'], $Filter['value']);
        	}
        }
        if (is_null($mOrder)) {
            $mOrder = $ValueField . ' ASC';
        }
        $select->order($mOrder);
        return $this->fetchAll($select);
	}

    /**
    * Функция очистки данных в таблице
    * @author norbis
    * @return void
    */
    public function truncate()
        {
	    $strSQL = 'TRUNCATE TABLE ';
	    $strSQL .= $this->getAdapterWrite()->quoteIdentifier($this->getTableName());
	    return $this->getAdapterWrite()->queryWrite($strSQL);
	}

   /**
    * Возвращает имя ключа
    * @author norbis
    * @return string
    */
	public function getIdentityName()
	{
		if (is_array($this->_primary)) {
		    return $this->_primary[1];
		}

	    return $this->_primary;
	}

   /**
    * Квотирует переданное значение
    * @author norbis
    * @return string
    */
    public function quote($strValue)
    {
        return $this->getAdapterRead()->quote($strValue);
    }

    /**
     * @return string
     */
    protected function _getAlterTableSQL()
    {
        $strSQL = 'ALTER TABLE ';
        $strSQL .= $this->getAdapterWrite()->quoteIdentifier($this->_name);
        return $strSQL;
    }

    /**
     * @param string $strColumnName
     * @param string $strColumnType
     */
	public function addColumn($strColumnName, $strColumnType)
	{
	    $strSQL = $this->_getAlterTableSQL();
	    $strSQL .= ' ADD COLUMN ';
	    $strSQL .= $this->getAdapterWrite()->quoteIdentifier($strColumnName);
	    $strSQL .= ' ';
	    $strSQL .= $strColumnType;
		return $this->getAdapterWrite()->queryWrite($strSQL);
	}

        /**
         * @param string $strColumnName
         * @return boolean
         */
        public function hasColumn($strColumnName)
        {
            $this->_setupMetadata();
            if (isset($this->_metadata[$strColumnName])) {
                return true;
            }
            return false;
        }

	/**
	 * @param string $strColumnName
	 */
	public function dropColumn($strColumnName)
	{
        $strSQL = $this->_getAlterTableSQL();
	    $strSQL .= ' DROP COLUMN ';
	    $strSQL .= $this->getAdapterWrite()->quoteIdentifier($strColumnName);
        return $this->getAdapterWrite()->queryWrite($strSQL);
	}

    /**
     * Modifying column
     * @param string $strColumnName
     * @param string $strColumnType
     * @param string $strNewColumnName if not null than column will be renamed
     * @return DBx_Statement_Interface
     */
	public function modifyColumn($strColumnName, $strColumnType, $strNewColumnName = null)
	{
	    $strSQL = $this->_getAlterTableSQL();
	    $strSQL .= ' CHANGE ';
	    $strSQL .= $this->getAdapterWrite()->quoteIdentifier($strColumnName);
	    $strSQL .= ' ';
	    if (!is_null($strNewColumnName)) {
            $strSQL .= $this->getAdapterWrite()->quoteIdentifier($strNewColumnName);
        } else {
            $strSQL .= $this->getAdapterWrite()->quoteIdentifier($strColumnName);
        }
	    $strSQL .= ' ';
	    $strSQL .= $strColumnType;
		return $this->getAdapterWrite()->queryWrite($strSQL);
	}

        /**
         * @return boolean 
         */
	public function hasIndex($strIndexName)
	{
	    $this->_loadIndexesMeta();
	    if (isset($this->_indexes[$strIndexName])){
	        return true;
	    }
	    return false;
	}

	/**
	 * Adds index for current table, $strIndexType: INDEX|UNIQUE|FULLTEXT.
	 * @todo Adding PRIMARY KEY
         * 
	 * @param string $strIndexName
	 * @param array $arrIndexColumns
	 * @param string $strIndexType
	 */
        public function addIndex($strIndexName, $arrIndexColumns, $strIndexType = 'INDEX')
        {
            $arrIndexTypes = array('INDEX', 'UNIQUE', 'FULLTEXT');

            if (!in_array($strIndexType, $arrIndexTypes)){
                throw new DBx_Exception('Index type should be one of the INDEX|UNIQUE|FULLTEXT');
            }
            if (!$arrIndexColumns) {
                throw new DBx_Exception('$arrIndexColumns is empty');
            }
            if (!is_array($arrIndexColumns)) {
                $arrIndexColumns = array($arrIndexColumns);
            }
            if (!count($arrIndexColumns)){
                throw new DBx_Exception('Array $arrIndexColumns should contains at least one table column');
            }
            foreach ($arrIndexColumns as $intKey => $strColumnName) {
                $arrIndexColumns[$intKey] = $this->getAdapterWrite()->quoteIdentifier($strColumnName);
            }
            
            $strSQL = $this->_getAlterTableSQL();
            $strSQL .= ' ADD ' . $strIndexType . ' ' . $this->getAdapterWrite()->quoteIdentifier($strIndexName);
            $strSQL .= ' (' . implode(',', $arrIndexColumns) . ')';
            return $this->getAdapterWrite()->queryWrite( $strSQL );
        }

        public function dropIndex($strIndexName)
        {
            $strSQL = $this->_getAlterTableSQL();
            $strSQL .= ' DROP KEY ' . $this->getAdapterWrite()->quoteIdentifier($strIndexName);
            return $this->getAdapterWrite()->queryWrite($strSQL);
        }

        public function modifyIndex($strIndexName, $strIndexType = 'INDEX', $arrIndexFields = array() )
        {
            if ($this->isExistIndex($strIndexName)){
                $this->dropIndex($strIndexName);
            }
            $this->addIndex($strIndexName, $strIndexType, $arrIndexFields);
        }


    public function getIterator( $strField, $arrWhereConditions = array() )
    {
        $select = $this->select()->from( $this->getTableName(), 'MAX('.$strField.') as max_value' );
        foreach( $arrWhereConditions as $strKey => $strValue )
            $select->where( $strKey, $strValue );

        $objResult = $this->fetchRow( $select );
        if ( is_object( $objResult ) ) {
            return $objResult->max_value + 1;
        } else {
            return 1;
        }
    }
}
