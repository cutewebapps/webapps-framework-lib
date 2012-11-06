<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

/**
 * not tested throroughly
 */
class App_BundleHelper extends App_ViewHelper_Abstract
{
    public function getLocalVersion()
    {
        $fn = CWA_APPLICATION_DIR.'/cdn/version.txt';
        if ( file_exists( $fn ) )
            return file_get_contents( $fn );
        return '0.' .filemtime( CWA_APPLICATION_DIR.'/index.php' );
    }

    /**
     *  @return string 
     */
    public function bundle( $strPath )
    {
        $strBundleMode = App_Application::getInstance()->bundle;
        
        switch ( $strBundleMode ) {
            case "localfile":
                // local file case: version from component (or local file name)
                // recommeded for dev and productions without CDNs
                return $this->base().$strPath.'?v='.$this->getLocalVersion();
                
            case "cdn":
                // next case: version for usage with CDN
                // when each release copies are stored and never deleted
                // instead of CSS/JS folder - file is taken from "bundle-XXX" folder 
                // to preserve same heirarchy depth.
                // 
                // !not tested throughly!
                $path = dirname( dirname( $strPath ));
                return $this->base().$path.'/bundle-'.($this->getLocalVersion()).'/'.basename( $strPath );

            default: 
                // default case - for developer machine
                return $this->base().$strPath.'?t='.time();
        }
   }
   
}