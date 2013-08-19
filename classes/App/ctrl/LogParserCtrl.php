<?php

class App_LogParserCtrl extends App_AbstractCtrl
{
    /**
     * Example usage:
     * php index.php -u app/log-parser/build/report/default/debug/1/
     */
    public function buildAction()
    {
        if ( ! Sys_Mode::isCli() )
            throw new App_Exception( 'log parser should be run from console only' );
        
        $objParser = App_Application::getInstance()->getConfig()->log_report;
        if ( !is_object( $objParser ))
            throw new App_Exception( 'log parser was not configured for this environment' );
            
        $objReport = new App_Log_Report( $this->_getParam( 'report', 'default' ) );
        
        $strLine= '91.219.233.53 - - [19/Aug/2013:10:16:12 +0200] [[0.113]] "GET /fjernkontroll/garasjeporter/ HTTP/1.1" 200 14630 "http://new.portspesialisten.com/fjernkontroll/" "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.95 Safari/537.36" "-"';
        $line = new App_Log_Line( $strLine );
        $line->debug(); die;
        
        
        
        $objReport->build( $this->_getIntParam("debug", 0 ) );
        $objReport->debug();
        die;
    }
    
}