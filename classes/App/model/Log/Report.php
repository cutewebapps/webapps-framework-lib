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
    protected $_bDebug = 0;
    
   public function __construct( $strReportName )
   {
        $this->_objConfig = App_Application::getInstance()->getConfig()->log_report->$strReportName;
        if ( !is_object( $this->_objConfig ))
            throw new App_Exception( 'log parser report '.$strReportName.' was not configured' );

        $this->_arrTypes = explode( ",", trim(preg_replace( '@\s+@', '', $this->_objConfig->check )));
        
        $this->_minTime = time() - intval( $this->_objConfig->frequency );
        $this->_maxTime = time();
        
        if ( $this->_bDebug )
            Sys_Io::out( 'from '. date('Y-m-d H:i:s', $this->_minTime ).' to '. date( 'Y-m-d H:i:s', $this->_maxTime ) );
    }
    
    public function build( $bDebug )
    {
        $this->_bDebug = $bDebug;
        
        $arrFiles  = $this->_objConfig->files;
        foreach( $arrFiles as $strFile ) {
            if ( $this->_bDebug )
                Sys_Io::out( 'parsing '.$strFile );
            
            $f = fopen( $strFile,  'r' );
            if ( $f ) {
                while( ( $strLine = fgets( $f )) !== false ) {
                    $this->_parseLine( $strLine );
                }
                fclose( $f );
            }
        }        
        //sort each report by v desc
        foreach ( $this->_arrReports as $strReportName => $arrRows ) {
   	    if ( count( $arrRows ) > 0 ) 
            	uasort( $arrRows, array( $this, 'sort' ) );
            $this->_arrReports[ $strReportName ] = $arrRows;
        }
    }
    
    public function sort( $v1, $v2 )
    {
	if ( !isset( $v2['q'] )) return 0;
        return ( $v2['q'] - $v1['q'] );
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
        // $line->debug(); die;
        
        //we'll only match the time
        $nTime = $line->getUnixTime();        
        if ( $this->_minTime > $nTime || $this->_maxTime < $nTime )
            return;
        
        // Sys_Io::out( $nTime );
        $strStatus = $line->getHttpStatus();
        $strUrl = $line->getUrl();

        //we'll only match allowed URLs
        if ( is_object( $this->_objConfig->exclude )) {
            foreach ( $this->_objConfig->exclude as $strExclude ) {
                if ( substr( $strUrl,0, strlen( $strExclude ) ) == $strExclude )
                        return;
            }
        }
        
        foreach ( $this->_arrTypes as $strType ) {
            switch ( $strType ) {
                case 'SLOW':
                    // slowliness information - for all requests
                    if ( $line->getRequestTime() != "") {
                        $this->_add( 'SLOW', $line->getUrlWithoutParams(), $line->getRequestTime() );
                        // echo $line->debug(); die;
                    }
                    break;
                
                case 'VOLUME':
                    // collect all traffic information (regardless statuses)
                    $this->_add( 'VOLUME', $line->getUrlWithoutParams(), $line->getBodySize() );
                    break;
               
                case 'ADWORDS':
                    
                    $arrP = $line->getUrlParams();
                    if ( isset( $arrP['gclid'] )) {
                        
                        if ( !isset( $this->_arrReports[ 'ADWORDS' ] ))
                            $this->_arrReports[ 'ADWORDS' ] = array();

                        $this->_arrReports['ADWORDS'][ $line->getIp() ] = array(
                            'start'  => $line->getDate(),
                            'url'    => $line->getUrlWithoutParams(),
                            'gclid'  => $arrP['gclid'], 
                            'ip'     => $line->getIp(),
                            'status' => $line->getHttpStatus(),
                            'requests'  => 0
                        );
                        
                        if ( is_object( $this->_objConfig->goal ) ) {
                            foreach(   $this->_objConfig->goal  as $strGoal => $strRegex  ){
                                $this->_arrReports['ADWORDS'][ $line->getIp() ][  $strGoal ] = $strGoal.':NEVER';
                            }
                        }
                    }
                    
                    if ( isset( $this->_arrReports['ADWORDS'][ $line->getIp() ] ) ) {
                        
                        $this->_arrReports['ADWORDS'][ $line->getIp() ]['requests']  ++;

                        if ( is_object( $this->_objConfig->goal ) ) {
                            foreach(  $this->_objConfig->goal as $strGoal => $strRegex  ){

                                if ( preg_match( '@'.$strRegex.'@i', $line->getUrlWithoutParams() ) ) { 
                                    $this->_arrReports['ADWORDS'][ $line->getIp() ][  $strGoal ] 
                                            = $strGoal.':'.$line->getDate();
                                }
                            }
                        }
                    }
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
        if ( !$this->_objConfig->save_path )
            throw new App_Exception( 'save_path was not configured for the report' );
            
        $dir = new Sys_Dir( $this->_objConfig->save_path .'/'
               . date( $this->_objConfig->dir_format ? $this->_objConfig->dir_format : 'Ymd/Hi' ) );
        
        foreach ( $this->_arrReports as $strReportName => $arrRows ) {
            if( !$dir->exists() ) $dir->create( '', true );
            $file = new Sys_File( $dir->getName().'/'.$strReportName.'.csv' );
            
            $strOut = '';
            foreach( $arrRows as $strUrl => $arrProps ) {
                $strOut .= '"'.$strUrl.'",'.implode( ',', $arrProps )."\n";
            }
            $file->save( $strOut );
        }
    }
    
    /**
     * 
     * @return string
     */
    public function getSavePath()
    {
        return $this->_objConfig->save_path;
    }
    
    /**
     * 
     * @return int
     */
    public function count()
    {
        return count( $this->_arrReports  );
    }
    
    /**
     * @return void
     */
    public function debug()
    {
        print_r( $this->_arrReports );
    }
}
