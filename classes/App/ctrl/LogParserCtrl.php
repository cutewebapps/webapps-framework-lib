<?php

class App_LogParserCtrl extends App_AbstractCtrl
{
    /**
     * Example usage:
     * php -u app/log-parser/build/report/default
     */
    public function buildAction()
    {
        if ( ! Sys_Mode::isCli() )
            throw new App_Exception( 'log parser should be run from console only' );
        
        $objParser = App_Application::getInstance()->getConfig()->log_parser;
        if ( !is_object( $objParser ))
            throw new App_Exception( 'log parser was not configured for this environment' );
            
        $objReport = new App_Log_Report( $this->_getParam( 'report', 'default' ) );
        $objReport->build();
        $objReport->debug();
        die;
    }
    
}