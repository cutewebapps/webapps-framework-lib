<?php

class Sys_Cache_Memory implements Sys_Cache_Abstract {
    
    protected $_options = array(
        'var' => '__CACHE__',
    );
    
    public function __construct( $options = array() ) 
    {
        foreach( $options as $strKey => $strValue ) {
            $this->_options[ $strKey ] = $strValue;
        }
        $GLOBALS[ $this->_options['var'] ] = array();
    }
    
    /**
     * @param data $data
     * @param string $strTag
     * @return boolean
     */
    public function save( $data, $strTag )
    {
        $GLOBALS[ $this->_options['var'] ][ $strTag ] = $data;
        return true;
    }
    
    /**
     * @param string $strTag
     * @return mixed
     */
    public function load( $strTag )
    {
        if ( isset( $GLOBALS[ $this->_options['var'] ][ $strTag ] ) )
            return $GLOBALS[ $this->_options['var'] ][ $strTag ];
        
        return false;
    }
    
    /**
     * @return void 
     */
    public function clean() {
        $GLOBALS[ $this->_options['var'] ] = array();
    }

}