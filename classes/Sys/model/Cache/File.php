<?php

class Sys_Cache_File implements Sys_Cache_Abstract {
    
    protected $_options = array(
        'cache_dir' => '',
        'lifetime'  => 3600
    );
    
    public function __construct( $options = array() ) 
    {
        foreach( $options as $strKey => $strValue ) {
            $this->_options[ $strKey ] = $strValue;
        }
        if ( $this->_options['cache_dir'] == '' )  {
            throw new Sys_Exception( 'Cache Dir was not set up correctly' );
        }
        
        $dir = new Sys_Dir( $this->_options['cache_dir'] );
        if ( ! $dir->exists() ) $dir->create( 0777, true );
        
        
        if ( !is_dir( $this->_options['cache_dir'] ) )  {
            throw new Sys_Exception( 'Cache Dir was not created' );
        }        
    }
    
    protected function _getFileName( $strTag )
    {
        return $this->_options['cache_dir'] .'/'.$strTag;
    }
    
    /**
     * @param data $data
     * @param string $strTag
     * @return boolean
     */
    public function save( $data, $strTag )
    {
        $file = new Sys_File( $this->_getFileName( $strTag) );
        $file->save( serialize( $data ));
        
        return true;
    }
    
    /**
     * @param string $strTag
     * @return mixed
     */
    public function load( $strTag )
    {
        if ( file_exists( $this->_getFileName( $strTag) ) ) {
            
            $nTimePassed = time() - filemtime(  $this->_getFileName( $strTag) );
            if ( $nTimePassed > intval( $this->_options['lifetime'] ) )
                return false;
            
            return unserialize( file_get_contents( $this->_getFileName( $strTag) ) );
        }
        
        return false;
    }
    
    /**
     * @return void 
     */
    public function clean() {
         $dir = new Sys_Dir( $this->_options['cache_dir'] );
         $dir->clean();
    }    
}