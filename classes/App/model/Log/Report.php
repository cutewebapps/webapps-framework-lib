<?php

class App_Log_Report
{
    
    // key is report 
    // each report is a set of URLS with associative array
    protected $_arrReports = array();
    
    protected $_arrTypes = array();
    
    protected $_objConfig = null;
    
    protected $_minTime = 0;
    protected $_maxTime = 0;
    
    
   public function __construct( $strReportName )
   {
        $this->_objConfig = App_Application::getInstance()->getConfig()->log_parser->$strReportName;
        if ( !is_object( $this->_objConfig ))
            throw new App_Exception( 'log parser report '.$strReportName.' was not configured' );

        $this->_arrTypes = trim(preg_replace( '\s+', '', $this->_objConfig->check ));
        
        $this->_minTime = time() - $this->_objConfig->frequency;
        $this->_maxTime = time();
    }
    
    public function build()
    {
        $arrFiles  = $this->_objConfig->files;
        foreach( $arrFiles as $strFile ) {
            Sys_Io::out( 'parsing '.$strFile );
            
            $f = fopen( $strFile );
            if ( $f ) {
                while( ( $strLine = fgets( $f )) !== false ) {
                    $this->_parseLine( $strLine );
                }
                fclose( $f );
            }
        }        
        // TODO: sort each report by v desc
    }
    
    protected function _add( $strKey, $strUrl, $value )
    {
        if ( !isset( $this->_arrReports[ $strKey ] ))
            $this->_arrReports[ $strKey ] = array();
        
        if ( !isset( $this->_arrReports[ $strKey ][$strUrl] ))
            $this->_arrReports[ $strKey ][$strUrl] = array(
                'q' => 0, 'v' => $value
            );
        
        $this->_arrReports[ $strKey ][$strUrl]['q'] ++;
        $this->_arrReports[ $strKey ][$strUrl]['v'] += intval( $value );
    }   

    protected function _parseLine( $strLine )
    {
        $line = new App_Log_Line( $strLine );
        //we'll only match the time
        $nTime = strtotime( $line->getDate() );        
        if ( $this->_minTime <= $nTime && $this->_maxTime >= $nTime )
            return;
        
        $strStatus = $line->getHttpStatus();
        $strUrl = $line->getUrl();

        foreach ( $this->_arrTypes as $strType ) {
            switch ( $strType ) {
                case 'SLOW':
                    // slowliness information - for all requests
                    $this->_add( 'SLOW', $strUrl, $line->getRequestTime() );
                    break;
                
                case 'VOLUME':
                    // collect all traffic information (regardless statuses)
                    $this->_add( 'VOLUME', $strUrl, $line->getBodySize() );
                    break;
                
                default:
                    // otherwise we should grab by the status
                    if ( $strStatus == $strType )
                        $this->_add( $strStatus, $strUrl, $line->getBodySize() );
                    break;
            }
        }
    }
    
    public function save()
    {
        // TODO:
    }
    
    public function debug()
    {
        print_r( $this->_arrReports );
    }
}
