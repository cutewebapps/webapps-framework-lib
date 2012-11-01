<?php


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