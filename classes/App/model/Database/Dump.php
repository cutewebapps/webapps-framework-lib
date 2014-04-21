<?php

class App_Database_Dump
{
    /* @var DBx_Adapter_Read */
    public $objReadDb = null;
    public $strConnectionIndex = 'default';

    public function __construct( $strConnectionIndex = 'default')
    {
        $this->objReadDb = DBx_Registry::getInstance()->get( $strConnectionIndex )->getDbAdapterRead();
        $this->strConnectionIndex = $strConnectionIndex;
    }

    /**
    * @return void
    */
    public function cleanCache()
    {
        $objCache = DBx_Registry::getInstance()->get( $this->strConnectionIndex )->getCache();
        if ( is_object( $objCache )) {
            $objCache->clean();
        }
    }

    /**
    * @return DBx_Adapter_Read
    */
    public function getDbAdapterRead()
    {
        return $this->objReadDb;
    }

    /**
     * Get array of tables with fields
     * @return array
     */
    public function getAsArray()
    {
        // TODO: walk through all tables and get the fields and keys in associative array
        return array();
    }
}