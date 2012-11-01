<?php

class App_Test_Case
{
    protected $_bVerbose = false;
    protected $_bTraceback = false;

    public function setVerbose( $bValue = 1 )
    {
        $this->_bVerbose = $bValue;
        return $this;
    }
    
    public function setTraceback( $bValue = 1 )
    {
        $this->_bTraceback = $bValue;
        return $this;
    }
    public function getTraceback()
    {
        return $this->_bTraceback;
    }

    public function out( $strMessage, $strClass= '', $arrStyles = array() )
    {
        if ( $this->_bVerbose ) Sys_Io::out( $strMessage, $strClass, $arrStyles );
        return $this;
    }

    public function setUp()
    {}
    
    public function tierDown()
    {}
    
    
    public function fail( $strMessage )
    {
        throw new App_Test_Exception( $strMessage );
    }

    public function assertTrue( $b, $strMessage = 'Failed assertTrue' )
    {
        if ( !$b ) throw new App_Test_Exception( $strMessage );
    }
    
    public function assertFalse( $b, $strMessage = 'Failed assertFalse' )
    {
        if ( $b ) throw new App_Test_Exception( $strMessage );
    }
    
    public function assertEquals( $v1, $v2, $strMessage = 'Failed assertEquals' )
    {
        if ( $v1 != $v2 ) throw new App_Test_Exception( $strMessage );
    }

    public function assertFileExists( $strFileName, $strMessage = 'Failed assertFileExists' )
    {
        if ( !file_exists($strFileName) ) throw new App_Test_Exception( $strMessage );
    }

    public function assertFileNotExists( $strFileName, $strMessage = 'Failed assertFileExists' )
    {
        if ( file_exists($strFileName) ) throw new App_Test_Exception( $strMessage );
    }

    public function assertLessThanOrEqual($expected, $actual, $strMessage = 'Failed assertLessThanOrEqual'  )
    {
        if ( !( $expected <= $actual )  ) throw new App_Test_Exception( $strMessage );

    }

    public function assertLessThan($expected, $actual, $strMessage = 'Failed assertLessThan')
    {
        if ( !( $expected < $actual )  ) throw new App_Test_Exception( $strMessage );
    }

    public function assertGreaterThan($expected, $actual, $strMessage = 'Failed assertGreaterThan')
    {
        if ( !( $expected > $actual )  ) throw new App_Test_Exception( $strMessage );
    }
    
    public function assertGreaterThanOrEqual($expected, $actual, $strMessage = 'Failed assertGreaterThanOrEqual')
    {
        if ( !( $expected >= $actual )  ) throw new App_Test_Exception( $strMessage );
    }

   
    public function assertIsObject($mixed, $strMessage = 'Failed assertIsObject' )
    {
        if ( !is_object( $mixed ) ) throw new App_Test_Exception( $strMessage );
    }
    
    public function assertIsNotObject($mixed, $strMessage = 'Failed assertIsNotObject' )
    {
        if ( !is_object( $mixed ) ) throw new App_Test_Exception( $strMessage );
    }

    
    public function getFixture( $strPath )
    {
        return  WC_APPLICATION_DIR .'/test/fixtures/'.$strPath;
    }

    /** @return App_Dispatcher */
    public function getDispatcher()
    {
        return  new App_Dispatcher(
            App_Application::getInstance()->getConfig()->routes->toArray(),
            App_Application::getInstance()->getConfig()->default_controller
        );
    }
    
    public function assertControllerList( $strAction, $strController, $strModule, $arrParams, $strMessage = '' )
    {
        $arrParams['norender']  = 1;
        $view = $this->getDispatcher()->runAction( $strAction, $strController, $strModule, $arrParams );
        // $this->out( count( $view->listObjects ) );
        $this->assertGreaterThan( count( $view->listObjects ), 0, $strMessage );
    }
    
    public function assertControllerObject( $strAction, $strController, $strModule, $arrParams, $strMessage = '' )
    {
        $arrParams['norender']  = 1;
        $view = $this->getDispatcher()->runAction( $strAction, $strController, $strModule, $arrParams );
        $this->assertIsObject( $view->object, $strMessage );
    }    
}