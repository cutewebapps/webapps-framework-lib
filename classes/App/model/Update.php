<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

/** 
 *  These are the models for host_packages table 
 *  which is responsible for storage of component versions
 * 
 *  Actually these classes should be used only by App_Update, 
 *  none of the components should use them directly 
*/

class App_Package_Table extends DBx_Table
{
    protected $_name    = 'host_packages';
    protected $_primary = 'package_id';
}

class App_Package_List extends DBx_Table_Rowset
{}

class App_Package extends DBx_Table_Row
{
    /** @return string */
    public static function getClassName() { return 'App_Package'; }
    /** @return string */
    public static function TableClass() { return self::getClassName().'_Table'; }
    /** @return mixed */
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    /** @return string */
    public static function TableName() { return self::Table()->getTableName(); }
}

class App_Update extends DBx_ReadWrite_Object
{
    protected $_strComponent;
    protected $_strOldVersion = '0.0.0';
    protected $_objPackage = null;

    /**
     * @return string
     */
    public static function getClassName() { return 'App_Update'; }

    /**
     * @return string
     */
    protected function _getComponentName() { return $this->_strComponent; }

    /**
     * @return string
     */
    protected function _getOldVersion() { return $this->_strOldVersion; }

    // for sub-class overloading
    public function update() {}


    public function __construct( $strComponent, $strConnectionIndex )
    {
        $this->setDbIndex( $strConnectionIndex );

        $this->_strComponent = $strComponent;
        $this->_strOldVersion = $this->_getPackageVersion( $strComponent );
        Sys_Io::out( 'Component: ' . $strComponent . ', old version: '.$this->_strOldVersion.' ', false );

        $this->_objAdapterR = null;
        $this->_objAdapterW = null;
        
        if ( DBx_Registry::getInstance()->hasConnection( $strConnectionIndex )) {
            $objConnection = DBx_Registry::getInstance()->get( $strConnectionIndex );
            $this->_objAdapterR = $objConnection->getDbAdapterRead();
            $this->_objAdapterW = $objConnection->getDbAdapterWrite();
        }
        
    }

    /**
     * @param string $version
     * @return bool
     */
    public function isVersionBelow($version)
    {
        $version = strtolower($version);
        $version = preg_replace('/(\d)pr(\d?)/', '$1a$2', $version);
        return version_compare($version, strtolower( $this->_getOldVersion() )) > 0;
    }

    /**
     * @param string $strNewVersion
     */
    public function save( $strNewVersion )
    {
        if ( $strNewVersion == $this->_getOldVersion() )
            return;

        Sys_Io::out( 'new version: '. $strNewVersion );
        $this->_objPackage->package_version = $strNewVersion;
        $this->_objPackage->save();
        $this->_strOldVersion = $strNewVersion;
    }

    /**
     * returns whether option is enabled in component section of the config
     * @return bool
     */
    public function isEnabled( $strParam )
    {
          $strComponent = strtolower( $this->_strComponent );
          $objConfigCms = App_Application::getInstance()->getConfig()->$strComponent;
          return ( !is_object( $objConfigCms ) ) ? false : $objConfigCms->$strParam;
    }

    /**
     * @param string $strComponent
     * @return string
     */
    private function _getPackageVersion( $strComponent )
    {
        $tbl = App_Package::Table();
        $selectPackage = $tbl->select()->where ( 'package_name = ? ', $strComponent );
        $this->_objPackage = $tbl->fetchRow( $selectPackage );

        $sResult = '0.0.0';
        if ( !is_object( $this->_objPackage )  ) {
            $this->_objPackage = $tbl->createRow();
            $this->_objPackage->package_name = $strComponent;

        } else {
            $sResult = $this->_objPackage->package_version;
        }
        return $sResult;
    }


    /**
     * @param array $arrNamespaces
     * @param string $strConnectionIndex
     * @throws App_Exception
     */
    public static function run( $arrNamespaces, $strConnectionIndex = 'default' )
    {
        if ( Sys_Global::get( 'Environment') == '' ) {
            throw new App_Exception( 'Running patch interface without environment defined' );
        }

        if ( ! DBx_Registry::getInstance()->hasConnection( $strConnectionIndex ))  {
            return ;
        }

        /* @var DBx_Adapter_Read */
        $objReadDb = DBx_Registry::getInstance()->get( $strConnectionIndex )->getDbAdapterRead();
        /* @var DBx_Adapter_Write */
        $objWriteDb = DBx_Registry::getInstance()->get( $strConnectionIndex )->getDbAdapterWrite();
        
        $objCache = DBx_Registry::getInstance()->get( $strConnectionIndex )->getCache();        
        if ( is_object( $objCache )) { $objCache->clean(); }

        if ( !$objReadDb->hasTable( 'host_packages' ) ) {
            Sys_Io::out( 'adding table of packages' );
            
            $objWriteDb->addTableSql( 'host_packages', '
                `package_id`      INT NOT NULL AUTO_INCREMENT,
                `package_name`    VARCHAR(50) NOT NULL,
                `package_version` VARCHAR(32) DEFAULT \'0.0.0\' NOT NULL,
                INDEX i_package_name( `package_name` ) ', 'package_id'
            );
            Sys_Io::out( 'table was added' );
        }

        // Sys_Debug::dump( $arrNamespaces );
        foreach ( $arrNamespaces as $strNamespace => $strNamespaceDir ) {
            $strUpdateClassFile = $strNamespaceDir . '/Update.php';
            // Sys_Debug::dump( $strUpdateClassFile );
            if ( file_exists( $strUpdateClassFile )) {

                require_once  $strUpdateClassFile;
                // Get Version of namespace
                $strClass = $strNamespace .'_Update';
                $objUpdate = new $strClass( $strNamespace, $strConnectionIndex );
                $objUpdate->update();
            }
        }
        
        if ( is_object( $objCache )) { $objCache->clean(); }
    }

    /**
     * @param array $arrSchema
     * @param string $strConnectionIndex
     */
    public function applySchema( $arrSchema, $strConnectionIndex  = 'default' )
    {
        $patch = new App_Database_Patch( $strConnectionIndex );
        $patch->applySchema( $arrSchema );
    }

}
