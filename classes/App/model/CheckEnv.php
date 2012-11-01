<?php

class App_CheckEnv 
{
    // list of tests that should be run sequentially
    // 
    protected $_arrSequential = array();
    
    /*
     * each value. ClassName => Description
     */
    public function addSequentialCheck( $strClassName, $strDescription )
    {
        $this->_arrSequential[ $strClassName ] = $strDescription;
    }
    
    public function __construct() 
    {
        // adding framework requirements
        $this->addSequentialCheck( 'App_CheckEnv_Framework', 'Framework Requirements' );
        
        // register tests from application config...?
        $objEnv = App_Application::getInstance()->getConfig()->checkenv;
        if ( is_object( $objEnv ) ) {
            foreach( $objEnv as $strId => $value ) 
                $this->addSequentialCheck( $strId, $value );
        }
    }
    
    public function run()
    {
        if ( !ini_get( 'safe_mode') )
            set_time_limit( 0 );
        
        // render page with
        foreach ( $this->_arrSequential as  $strClassName => $strDescription ) {
            try {
                $obj = new $strClassName();
                Sys_Io::out( '[+] ' . $strDescription . ' - OK');
            } catch( App_CheckEnv_Exception $e ) {
                Sys_Io::out( 'ERROR: '.$strDescription.': ' . $e->getMessage() );
            }
        }
        
    }

    public static function assert( $bValue, $strErrorMessage )
    {
        if ( !$bValue ) {
            throw new App_CheckEnv_Exception( $strErrorMessage );
        }
    }
    
}
