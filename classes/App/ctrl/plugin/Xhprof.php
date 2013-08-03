<?php

class App_Xhprof_CtrlPlugin extends App_Dispatcher_CtrlPlugin
{
 
    public function preDispatch()
    {
        $bEnabled = App_Application::getInstance()->getConfig()->xhprof;
        if ( $bEnabled ) {
            ini_set( 'xhprof.output_dir', App_Application::getInstance()->getConfig()->xhprof );
            xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
        }
        
        return true;
    }

    public function postDispatch()
    {
        $bEnabled = App_Application::getInstance()->getConfig()->xhprof;
        if ( $bEnabled ) {
            $xhprof_data = xhprof_disable();
            // ct=1 wt=18(wall time) cpu=0 mu=5472 (memory usage) pmu=4720 (peak memory usage)
            
            $strId = date('His').'~'.mt_rand(10000,99999);
            if ( isset( $_SERVER['REQUEST_URI'] ) )
                $strId .= '~'.urlencode( $_SERVER['REQUEST_URI'] );
            if ( isset( $_SERVER['REMOTE_ADDR'] ) )
                $strId .= '~'.$_SERVER['REMOTE_ADDR'];
                
            $dir = new Sys_Dir( App_Application::getInstance()->getConfig()->xhprof.'/'.date('Ymd') );
            if ( !$dir->exists() ) $dir->create( '', true );
                
            $debugFile = new Sys_File( $dir->getName().'/'.$strId.'.txt' );
            $strOut = '';
            foreach( $xhprof_data as $key => $arrStat ) {
                $arrColumns = array( $key );
                foreach ( $arrStat as $k => $v ) $arrColumns[] = $k.'='.$v;
                $strOut .= implode( " ", $arrColumns ) . "\n";
            }
            $debugFile->save( $strOut );
        }
        return true;
    }
}