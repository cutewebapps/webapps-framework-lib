<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

interface Sys_Cache_Abstract {
    
    /**
     * @param data $data
     * @param string $strTag
     * @return boolean
     */
    public function save( $data, $strTag );
    
    /**
     * @param string $strTag
     * @return mixed
     */
    public function load( $strTag );
    
    
    /**
     * @return void 
     */
    public function clean();
            
}