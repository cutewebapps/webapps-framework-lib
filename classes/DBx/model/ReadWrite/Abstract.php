<?php

class DBx_ReadWrite_Abstract
{
    /** @var string */
    protected $_strConnectionName = 'default';

    /** @return void */
    public function setDbIndex( $strIndex )
    {
        $this->_strConnectionName = $strIndex;
    }

    /** @return int */
    public function getDbIndex()
    {
        return $this->_strConnectionName;
    }

    /** @return Sys_Db_Adapter_Read */
    public function getDbAdapterRead()
    {
        return DBx_Registry::getInstance()->get( $this->getDbIndex() )->getDbAdapterRead();
    }

    /** @return Sys_Db_Adapter_Write */
    public function getDbAdapterWrite()
    {
        return DBx_Registry::getInstance()->get( $this->getDbIndex() )->getDbAdapterWrite();
    }
}

