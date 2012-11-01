<?php

class App_HeadScriptHelper extends App_ViewHelper_Abstract
{
    /**
     * @var App_HeadScriptHelper
     */
    protected static $_instance = null;

    /**
     * @var array
     */
    protected $_arrItems = array();

    protected $_arrFileToAlias = array();
    
    /**
     * Full Path => Dependencies
     * @var array
     */
    protected $_arrUnresolved = array();
    
    /**
     * @var string
     */
    protected $_strNextAlias = '';
    
    /**
     * @var string
     */
    protected $_strHeadScriptContents = '';

    /**
     * @return App_Layout
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /**
     * @return App_HeadScriptHelper
     */
    public function headScript()
    {
        return self::getInstance();
    }

    /**
     *
     * @param string $strNextAlias 
     * @return App_HeadScriptHelper
     */
    public function alias( $strNextAlias )
    {
        $this->_strNextAlias = strtolower( $strNextAlias );
        return $this;
    }
    
    /**
     * @param string $strFile
     * @return string
     */
    protected function _getAliasName( $strFile )
    {
        if ( $this->_strNextAlias  ) {
            // if alias was manually defined, we are already know it..
            return strtolower( $this->_strNextAlias );
        } 
        $strAlias = basename( preg_replace( '@\?.+$@', '', strtolower( $strFile ) ) );
        $strAlias = preg_replace ( '@[_\-d]+$@sim', '', $strAlias );
        $strAlias = preg_replace ( '@\.js$@sim', '', $strAlias );
        $strAlias = preg_replace ( '@\.min$@sim', '', $strAlias );
        $strAlias = preg_replace ( '@\.pack$@sim', '', $strAlias );
        return $strAlias;
    }
    /**
     *
     * @param mixed $deps
     * @return boolean 
     */
    protected function _isSatisfied( $deps )
    {
        if ( $deps == '' )
            return true;
        
        $arrDeps = $deps;
        if ( !is_array( $arrDeps) ) $arrDeps = array( $deps );
        
        foreach( $arrDeps as $strDepsAlias ) {
            
            // if at least one alias was not used already, 
            if ( !isset( $this->_arrItems [ $strDepsAlias ] ) )
                return false;
        }
        return true;
    }
    /**
     *@return void 
     */
    protected function _resolveDeps()
    {
        
        // try to resolve existing dependencies
        $bAdded = false;
        
        // 1) walk through prepended deps
        foreach ( $this->_arrUnresolved as $strAlias => $arrProps ) 
                if ( $arrProps['type'] == 'prepend') {

                  
             if ( $this->_isSatisfied( $arrProps['deps'] ) ) {

                $this->_arrItems[ $strAlias ] = $arrProps['file'];
                $bAdded = true;
                unset( $this->_arrUnresolved [ $strAlias ] );
             }
        }
        
        // 2) walk through appended deps
        foreach ( $this->_arrUnresolved as $strAlias => $arrProps ) 
                if ( $arrProps['type'] == 'append') {
                    
             if ( $this->_isSatisfied( $arrProps['deps'] ) ) {
                $this->_arrItems[ $strAlias ] = $arrProps['file'];
                $bAdded = true;
                unset( $this->_arrUnresolved [ $strAlias ] );
             }
             
        }
        
        // if ( $bAdded )
            // $this->_resolveDeps();
    }
    /**
     * @param string $strFile
     * @param mixed $deps
     * @return App_HeadScriptHelper
     */
    public function append( $strFile, $deps = '' )
    {
        if (! in_array( $strFile, $this->_arrItems )) {

            $strAlias = $this->_getAliasName( $strFile );
            $this->_arrFileToAlias [ $strFile ] = $strAlias;
            
            if ( $this->_isSatisfied( $deps ) ) {
                $this->_arrItems[ $strAlias ] = $strFile;
                $this->_resolveDeps();
            } else 
                $this->_arrUnresolved[ $strAlias ] = array( 
                    'type'  => 'append',
                    'deps'  => $deps,
                    'alias' => $strAlias,
                    'file'  => $strFile );
        }
        $this->_strNextAlias = '';
        return $this;
    }

    /**
     * @param string $strJsCode
     * @return App_HeadScriptHelper
     */
    public function appendScript( $strJsCode )
    {
        $this->_strHeadScriptContents .= $strJsCode."\n";
        return $this;
    }

    /**
     * @param string $strFile
     * @param mixed $deps
     * @return App_HeadScriptHelper
     */
    public function prepend( $strFile, $deps = '' )
    {
        if (! in_array( $strFile, $this->_arrItems )) {
            
            $strAlias = $this->_getAliasName( $strFile );
            $this->_arrFileToAlias [ $strFile ] = $strAlias;
            
            
            $arrNewItems = array();
            $bInserted = false;
            foreach ( $this->_arrItems as $strItemAlias => $strItemFile ) {
                if ( $this->_isSatisfied( $deps ) ) {
                    $arrNewItems[ $strAlias ] = $strFile;
                    $bInserted = true;
                }
                $arrNewItems[ $strItemAlias ] = $strItemFile;
            }
            
            if ( !$bInserted ) {
                if ( $this->_isSatisfied( $deps ) ) {
                    $arrNewItems[ $strAlias ] = $strFile;
                    $bInserted = true;
                } else {
                    $this->_arrUnresolved[ $strAlias ] = array( 
                        'type'  => 'prepend',
                        'deps'  => $deps,
                        'alias' => $strAlias,
                        'file'  => $strFile );
                }
            }
            
            if ( $bInserted )
                $this->_resolveDeps();
            
            $this->_arrItems = $arrNewItems;
        }
        
        $this->_strNextAlias = '';
        return $this;
    }

    /**
     * used by layout renderer
     * @return string 
     */
    public function get()
    {
//     Sys_Debug::dumpDie( $this->_arrItems );
    
        if ( count( $this->_arrUnresolved ) ) {
            $arrUnresolvedNames = array();
            foreach( $this->_arrUnresolved as $arrProps ) $arrUnresolvedNames[]=  $arrProps['alias'];

            Sys_Debug::dump( $this->_arrUnresolved );
            Sys_Debug::dump( $this->_arrItems );
            throw new App_Exception( 'Unresolved HeadScript dependency: '
                    . implode( ',', $arrUnresolvedNames ) );
        }
        //Sys_Debug::dumpDie( $this->_arrFileToAlias );
        
        $arrStrResults = array();
        foreach( $this->_arrItems as $strFile )
            $arrStrResults[ $strFile ] = '<script type="text/Javascript" src="'.$strFile.'"></script>';
        $strOutput = "\n".implode( "\n", $arrStrResults );

        if ( $this->_strHeadScriptContents ) {
            $strOutput .= "\n".'<script type="text/JavaScript">'."\n//<!--\n"
                . $this->_strHeadScriptContents ."//-->\n</script>\n";
        }
        return $strOutput;
    }
}