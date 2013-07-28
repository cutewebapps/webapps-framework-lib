<?php

class DBx_Profiler_ReadWriteLog extends DBx_Profiler
{
    public function __construct()
    {
        parent::__construct();
        $this->setFilterQueryType ( DBx_Profiler::SELECT |
                                    DBx_Profiler::INSERT |
                                    DBx_Profiler::UPDATE | 
                                    DBx_Profiler::DELETE );
    }
    
    public function queryEnd($queryId)
    {
        $result = parent::queryEnd( $queryId );
        $strReadLogFile  = App_Application::getInstance()->getConfig()->dbread_log;
        $strWriteLogFile  = App_Application::getInstance()->getConfig()->dbwrite_log;
        
        if ( isset( $this->_queryProfiles[$queryId] ) ) {
            $qp = $this->_queryProfiles[$queryId];
            $strIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'LOCAL';
            
            // Sys_Debug::dumpDie( $qp );
            if ( $qp->getQueryType() == 32 && $strReadLogFile ) {
            
                $f = new Sys_File( $strReadLogFile );
                $f->append( date('Y-m-d H:i:s') . "\t$strIp\t[[" . sprintf( "%0.5f",$qp->getElapsedSecs()) . "]]\t".$qp->getQuery()."\n" );
                
            } else if ( $strWriteLogFile ) {
            
                $f = new Sys_File( $strWriteLogFile );
                $f->append( date('Y-m-d H:i:s') . "\t$strIp\t[[" . sprintf( "%0.5f",$qp->getElapsedSecs()) . "]]\t".$qp->getQuery()."\n" );
            }
        }
        return $result;
    }
}