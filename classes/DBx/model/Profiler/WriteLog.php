<?php

class DBx_Profiler_WriteLog extends DBx_Profiler
{
    public function __construct()
    {
        parent::__construct();
        $this->setFilterQueryType ( DBx_Profiler::INSERT |
                                    DBx_Profiler::UPDATE | 
                                    DBx_Profiler::DELETE );
    }
    
    public function queryEnd($queryId)
    {
        $result = parent::queryEnd( $queryId );
        $strLogFile  = App_Application::getInstance()->getConfig()->dbwrite_log;
        if ( $strLogFile && isset( $this->_queryProfiles[$queryId] )) {
            $qp = $this->_queryProfiles[$queryId];
            
            $strIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'LOCAL';
            // Sys_Debug::dump( $qp );
            $f = new Sys_File( $strLogFile );
            $f->append( date('Y-m-d H:i:s') . "\t$strIp\t[[" . sprintf( "%0.5f",$qp->getElapsedSecs()) . "]]\t".$qp->getQuery()."\n" );
        }
        return $result;
    }
}