<?php

class App_Database_Patch
{
    /* @var DBx_Adapter_Read */
    public $objReadDb = null;
    /* @var DBx_Adapter_Write */
    public $objWriteDb = null;

    public $strConnectionIndex = 'default';

    public function __construct( $strConnectionIndex = 'default')
    {
        $this->objReadDb = DBx_Registry::getInstance()->get( $strConnectionIndex )->getDbAdapterRead();
        $this->objWriteDb = DBx_Registry::getInstance()->get( $strConnectionIndex )->getDbAdapterWrite();

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
     * @return DBx_Adapter_Write
     */
    public function getDbAdapterWrite()
    {
        return $this->objWriteDb;
    }


    protected function _applyKeySchema( $strTable, $strSqlVal )
    {
    }


    protected function _deleteColumnSchema( $strTable, $strColumn )
    {
        $dbRead = $this->getDbAdapterRead();
        $metadata = $dbRead->describeTable( $strTable, null );

        $this->getDbAdapterWrite()->queryWrite(
            'ALTER TABLE `'.$strTable.'` DROP COLUMN `'.$strColumn.'`' );
    }

    protected function _applyColumnSchema( $strTable, $strSqlVal )
    {
        $dbRead = $this->getDbAdapterRead();
        $metadata = $dbRead->describeTable( $strTable, null );

        // TODO: smarter column modification
        // $this->getDbAdapterWrite()->queryWrite(
        // 'ALTER TABLE `'.$strTable.'` MODIFY COLUMN `'.$strColumn.'`' );
    }

    protected function _recheckTableDropped( $strTable )
    {
        if ( $this->getDbAdapterRead()->hasTable( $strTable ) ) {
            $this->getDbAdapterWrite()->queryWrite( 'DROP TABLE IF EXISTS `'.$strTable.'`' );
        }
    }

    /**
     *
     * @param string $strTable
     * @param array $arrFields
     */
    protected function _recheckSchemaFields( $strTable, array $arrFields )
    {
        foreach ( $arrFields as $strSqlKey => $strSqlVal ) {
            if ( preg_match( '/^\!/', $strSqlVal ) ) {
                $this->_deleteColumnSchema( $strTable, substr( $strSqlVal, 1 ) );
            } elseif ( preg_match( '/^\d+$/', $strSqlKey ) ) {
                $this->_applyKeySchema( $strTable, $strSqlVal );
            } else {
                $this->_applyColumnSchema( $strTable, $strSqlVal );
            }
        }
    }
    /**
     *
     * @param array $arrFields
     * @return string
     */
    protected function _createTableSqlLines( $arrFields )
    {
        $arrLines = array();
        foreach ( $arrFields as $strSqlKey => $strSqlVal ) {
            if ( preg_match( '/^\!/', $strSqlVal ) ) {
                continue;
            }
            if ( preg_match( '/^\d+$/', $strSqlKey ) ) {
                $arrLines[] = $strSqlVal;
            } else {
                $arrLines[] = $strSqlKey.' '.$strSqlVal;
            }
        }
        return implode( ",\n", $arrLines );
    }
    /**
     *
     * @param array $arrSchema
     */
    public function applySchema( array $arrSchema )
    {
        foreach ( $arrSchema as $strTable => $arrFields ) {
            if ( !is_array( $arrFields ) && preg_match( '/^\!/', $arrFields ) ) {
                $this->_recheckTableDropped( substr( $arrFields, 1 ) );
                continue;
            }

            $arrMatch = array();
            $bHasTable = false;
            if (  preg_match( '@(.+)\.(.+)$@', $strTable, $arrMatch ) ) {
                // get table presense in case of another database
                // Sys_Debug::dumpDie( $arrMatch );
                $bHasTable = $this->getDbAdapterRead()->hasTable( $arrMatch[2], $arrMatch[1]  );
            } else {
                $bHasTable = $this->getDbAdapterRead()->hasTable( $strTable );
            }


            if ( ! $bHasTable ) {
                Sys_Io::out('Creating '.$strTable.' Table');
                $this->getDbAdapterWrite()->addTableSql( $strTable, $this->_createTableSqlLines( $arrFields ) );
            } else {
                $this->_recheckSchemaFields( $strTable, $arrFields );
            }
        }
    }

}