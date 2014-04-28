<?php

class App_Database_Patch
{
    /** @var DBx_Adapter_Read */
    public $objReadDb = null;
    /** @var DBx_Adapter_Write */
    public $objWriteDb = null;

    /**
     * @var boolean
     */
    public $bDebug = false;

    /**
     * @var string
     */
    public $strConnectionIndex = 'default';

    public function __construct( $strConnectionIndex = 'default', $bDebug = false )
    {
        $this->objReadDb = DBx_Registry::getInstance()->get( $strConnectionIndex )->getDbAdapterRead();
        $this->objWriteDb = DBx_Registry::getInstance()->get( $strConnectionIndex )->getDbAdapterWrite();

        $this->bDebug = $bDebug;
        $this->strConnectionIndex = $strConnectionIndex;
        $this->metadata = array();
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

    /**
     * @param string $sOut
     * @return void
     */
    protected function out( $sOut )
    {
        if ( $this->bDebug ) {
            Sys_Io::out( $sOut );
        }
    }

    protected function _applyKeySchema( $strTable, $strSqlVal )
    {
        // $this->out( '_applyKeySchema( '.$strTable.': '.$strSqlVal );
    }


    protected function _deleteColumnSchema( $strTable, $strColumn )
    {
        $dbRead = $this->getDSbAdapterRead();
        $metadata = $dbRead->describeTable( $strTable, null );

        $this->out( '_deleteColumnSchema( '.$strTable.': '.$strColumn );

        $this->getDbAdapterWrite()->queryWrite(
            'ALTER TABLE `'.$strTable.'` DROP COLUMN `'.$strColumn.'`' );
    }

    protected function _applyColumnSchema( $strTable, $strColumn, $strSqlVal )
    {
       // $this->out( '_applyColumnSchema( '.$strTable.') COLUMN='.$strColumn.', VALUE="'.$strSqlVal.'" ' );

        if ( isset( $this->metadata[$strTable][$strColumn] )) {
            // print_r( $this->metadata[$strTable][ $strColumn ] ); die;
            // TODO: check column could be updated....
            // $this->getDbAdapterWrite()->queryWrite(
            // 'ALTER TABLE `'.$strTable.'` MODIFY COLUMN `'.$strColumn.'`' );
        } else {
            $this->out( "CREATING NEW COLUMN ".$strTable.' '.$strColumn );
            $this->getDbAdapterWrite()->queryWrite(
                'ALTER TABLE '.$strTable.' ADD COLUMN `'.$strColumn.'` '.$strSqlVal );
        }

    }

    protected function _recheckTableDropped( $strTable )
    {
        if ( $this->getDbAdapterRead()->hasTable( $strTable ) ) {
            $this->out( 'dropping table'.$strTable.' ' );
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
        $dbRead = $this->getDbAdapterRead();
        $this->metadata[ $strTable ]= $dbRead->describeTable( $strTable, null );

        foreach ( $arrFields as $strSqlKey => $strSqlVal ) {
            if ( preg_match( '/^\!/', $strSqlVal ) ) {
                $this->_deleteColumnSchema( $strTable, substr( $strSqlVal, 1 ) );
            } elseif ( preg_match( '/^\d+$/', $strSqlKey ) ) {
                $this->_applyKeySchema( $strTable, $strSqlVal );
            } else {
                $this->_applyColumnSchema( $strTable, $strSqlKey, $strSqlVal );
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
                $this->out('Creating '.$strTable.' Table');
                $this->getDbAdapterWrite()->addTableSql( $strTable, $this->_createTableSqlLines( $arrFields ) );
            } else {
                $this->out('Reviewing  '.$strTable.' Table');
                $this->_recheckSchemaFields( $strTable, $arrFields );
            }
        }
    }

}