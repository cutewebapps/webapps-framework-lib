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
    public static function getClassName() { return 'App_Package'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
}

class App_Update extends DBx_ReadWrite_Object
{
    protected $_strComponent;
    protected $_strOldVersion = '0.0.0';
    protected $_objPackage = null;
    public static function getClassName() { return 'App_Update'; }
    protected function _getComponentName() { return $this->_strComponent; }
    protected function _getOldVersion() { return $this->_strOldVersion; }
    // for sub-class overloading
    public function update() {}
    public function __construct( $strComponent, $strConnectionIndex )
    {
        $this->setDbIndex( $strConnectionIndex );

        $this->_strComponent = $strComponent;
        $this->_strOldVersion = $this->_getPackageVersion( $strComponent );
        Sys_Io::out( 'Component: ' . $strComponent . ', old version: '.$this->_strOldVersion.' ', false );

        $this->_objAdapterR = DBx_Registry::getInstance()->get( $strConnectionIndex )->getDbAdapterRead();
        $this->_objAdapterW = DBx_Registry::getInstance()->get( $strConnectionIndex )->getDbAdapterWrite();
    }
    public function isVersionBelow($version)
    {
        $version = strtolower($version);
        $version = preg_replace('/(\d)pr(\d?)/', '$1a$2', $version);
        return version_compare($version, strtolower( $this->_getOldVersion() )) > 0;
    }
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
     * whether option is enabled in component section of the config
     * @return boolean 
     */
    public function isEnabled( $strParam )
    {
          $strComponent = strtolower( $this->_strComponent );
          $objConfigCms = App_Application::getInstance()->getConfig()->$strComponent;
          if ( !is_object( $objConfigCms ) )
                return false;
          return $objConfigCms->$strParam;
    }
    private function _getPackageVersion( $strComponent )
    {
        // $strSelect = 'SELECT package_version FROM host_packages WHERE package_name=\''.$strComponent.'\' ';
        // $arrResult = $this->getDbAdapterRead()->fetchRow( $strSelect );
        
        $tbl = App_Package::Table();
        $selectPackage = $tbl->select()->where ( 'package_name = ? ', $strComponent );
        $this->_objPackage = $tbl->fetchRow( $selectPackage );

        if ( !is_object( $this->_objPackage )  ) {
            $this->_objPackage = $tbl->createRow();
            $this->_objPackage->package_name = $strComponent;
            return '0.0.0';
        }
        else 
            return $this->_objPackage->package_version;
    }
    public static function run( $arrNamespaces, $strConnectionIndex = 'default' )
    {
        if ( Sys_Global::get( 'Environment') == '' )
            throw new App_Exception( 'Running patch interface without environment defined' );

        
        
        /* @var DBx_Adapter_Read */
        $objReadDb = DBx_Registry::getInstance()->get( $strConnectionIndex )->getDbAdapterRead();
        /* @var DBx_Adapter_Write */
        $objWriteDb = DBx_Registry::getInstance()->get( $strConnectionIndex )->getDbAdapterWrite();
        
        $objCache = DBx_Registry::getInstance()->get( $strConnectionIndex )->getCache();        
        if ( is_object( $objCache )) $objCache->clean();
        
        

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

        foreach ( $arrNamespaces as $strNamespace => $strNamespaceDir ) {
            $strUpdateClassFile = $strNamespaceDir . '/Update.php';
            if ( file_exists( $strUpdateClassFile )) {
                include_once( $strUpdateClassFile );
                // Get Version of namespace
                $strClass = $strNamespace .'_Update';
                $objUpdate = new $strClass( $strNamespace, $strConnectionIndex );
                $objUpdate->update();
            }
        }
        
        if ( is_object( $objCache )) $objCache->clean();
    }
}
