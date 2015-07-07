<?php
/**
 * Class for parsing PHP file 
 * 
 */
class App_DocPage_Class
{
    /**
     * store name of the class
     * @var string
     */
    protected $strName = '';
    
    /**
     * raw comment for the class
     * @var string
     */
    protected $strDocBlock = '';
    
    /**
     *
     * @var array of App_DocPage_Class_Method
     */
    protected $arrMethods = array();
    
    /**
     *
     * @var array of App_DocPage_Class_Property
     */
    protected $arrProperties = array();
    
    public $arrTokens = array();
    public $nStartToken = 0;
    public $nEndToken = 0;
    
    public function toArray()
    {
        return array( 
            'name' => $this->getName(),
            'docblock' => $this->getDocBlock(),
            'start' => $this->nStartToken, 
            'end' => $this->nEndToken );
    }
    
    /**
     * Parse PHP by walking through its tokens (from tokenizer extension)
     * 
     * @param array $arrTokens
     * @param integer $nStartIndex
     * @return what offset we stopped parsing
     */
    public function parseTokens( array $arrTokens, $nStartIndex  )
    {
        $this->arrTokens = $arrTokens;
        $this->strName = '';
        $strLastDocBlock = '';
        
        $this->nStartToken = $nStartIndex;
        $this->nEndToken = $nStartIndex;
        
        $nDocBlockIndex = $this->nStartToken;
        $bInside = false;
        $strMode = '';
        while( $this->nEndToken < count( $arrTokens ) ) {
            
            $token = $arrTokens[ $this->nEndToken ];
            
            
            if ( $token == '{' ) {
               
                $bInside = true;
            } else if ( is_array( $token )) {
                    
                if ( ! $bInside &&  $token[ 0 ] == T_DOC_COMMENT ) {
                    
                    // external class docblock comment
                    $strLastDocBlock .= "\n".$token[ 1 ];
                    if ( $this->strName != ''  ) {
                        $nDocBlockIndex = $this->nEndToken;
                    }
                   
                } else if ( $token[ 0 ] == T_CLASS ) {
                    $strMode = 'class';
                    if ( $this->strName != '' ) {
                        // we met new class 
                        // and roll back position to the docblock comment
                        // if ( $strLastDocBlock != '' ) {
                         //   $this->nEndToken = $nDocBlockIndex - 1;
                        // }
                        return $this->nEndToken - 1;
                    }
                } else if ( $token[ 0 ] == T_WHITESPACE ) {
                    // no change of mode
                } else if ( $strMode == 'class' && $token[ 0 ] == T_STRING ) {
                    
                    $this->strName = $token[ 1 ];
                    $this->strDocBlock = $strLastDocBlock;
                    $nDocBlockIndex = $this->nEndToken;
                   
                    $strLastDocBlock = '';
                    $strMode = '';
                } 
            }
            
            $this->nEndToken ++ ;
        }
        return $this->nEndToken;
    }
    /**
     * get parsed Class Name
     * @return string
     */
    public function getName()
    {
        return $this->strName;
    }
    /**
     * get Docblock content
     * @return string
     */
    public function getDocBlock()
    {
        $docblock = new App_DocPage_Docblock( $this->strDocBlock );
        return $docblock ->getRawText();
    }
    
    /**
     * parse what is the parent of the class
     * @return string
     */
    public function parseParent()
    {
        $strMode = ''; 
        for( $i = $this->nStartToken; $i < $this->nEndToken; $i ++ ) {
            $token = $this->arrTokens[ $i ];
            if ( is_array( $token )) {
                if ( $token[ 0 ] == T_EXTENDS ) {
                    $strMode  = 'extends';
                } else if ( $strMode == 'extends' && $token[0] == T_STRING ) {
                    return $token[1];
                }
            }
        }
        return '';
    }
    
    /**
     * parse - what interfaces are being implemented
     * @return array of string
     */
    public function parseImplements()
    {
        $strMode = ''; 
        $arrResults = array();
        for( $i = $this->nStartToken; $i < $this->nEndToken; $i ++ ) {
            $token = $this->arrTokens[ $i ];
            if ( is_array( $token )) {
                if ( $token[ 0 ] == T_IMPLEMENTS ) {
                    $strMode  = 'implements';
                } else if ( $strMode == 'implements' && $token[0] == T_STRING ) {
                    $arrResults[] = $token[1];
                }
            }
        }
        return $arrResults;
    }    
    /**
     * Gets index of the first token
     * @return integer
     */
    public function getStartIndex()
    {
        return $this->nStartToken;
    }
    /**
     * Gets index of the last token
     * @return integer
     */
    public function getEndIndex()
    {
        return $this->nEndToken;
    }
    /**
     * @return void
     */
    public function parseMethodsAndProperties()
    {
        $this->arrBlocks = array();
        
        $this->arrMethods = array();
        $this->arrProperties = array();
        $this->arrControllerActions = array();
        
        $strMode = ''; 
        $strScope = 'public';
        
        $arrCurrent = array(); // current method storage
        $strDocComment = '';
        for( $i = $this->nStartToken; $i < $this->nEndToken; $i ++ ) {
            $token = $this->arrTokens[ $i ];
            
            if ( $strMode == 'args' && isset( $arrCurrent['args'] ) ) {
                if( is_array( $token ) ) {
                    $arrCurrent['args'].= $token[1];
                } else if ( $token !=  ")" ) {
                    $arrCurrent['args'].= $token;
                }
            }
            
            if ( $token == "{" ) {
                if ( count( $arrCurrent ) > 0 ) {
                    $this->arrBlocks []= $arrCurrent;
                    $arrCurrent = array();
                    $strDocComment = '';
                }
            } else if ( $token == "(" ) {                    
                $strMode = 'args';
            } else if ( $token == ")" ) {    
                $strMode = '';
            } else if ( is_array( $token )) {
                
                if ( $token[ 0 ] == T_DOC_COMMENT ) {
        
                    $strDocComment .= "\n" . $token[ 1 ];
                    
                } else if ( $token[ 0 ] == T_PUBLIC ) {
                    $strScope = 'public';
                    if ( count( $arrCurrent ) > 0 ) {
                        $this->arrBlocks []= $arrCurrent;
                        $arrCurrent = array();
                    }
                        
                } else if ( $token[ 0 ] == T_VAR ) {
                    $strScope = 'public';
                    $arrCurrent = array();
                } else if ( $token[ 0 ] == T_PROTECTED ) {
                    $strScope = 'protected';
                    $arrCurrent = array();
                } else if ( $token[ 0 ] == T_PRIVATE ) {
                    $strScope = 'private';
                    $arrCurrent = array();
                } else if ( $token[ 0 ] == T_STATIC ) {
                    $strScope .= ' static';
                } else if ( $strScope != '' && $token[ 0 ] == T_VARIABLE ) {
                    
                    $arrCurrent = array(
                        'type'  => 'var',
                        'scope' => $strScope,
                        'name'  => str_replace( '"', '', str_replace( '\'', '', $token[1] )),
                        'docblock' => $strDocComment
                    );
                    $strScope = '';
                    $strDocComment = '';
                    $strMode = '';
                                        
                } else if ( $token[ 0 ] == T_FUNCTION ) {
                    
                    $strMode  = 'wait_name';
                    $arrCurrent = array(
                        'type'  => 'function',
                        'scope' => $strScope,
                        'docblock' => $strDocComment,
                        'args' => ''
                    );
                    $strScope = '';
                    $strDocComment = '';
                   // Sys_Debug::dump( $this->arrTokens[ $i+2 ] );
                } else if ( $strMode == 'wait_name' && $token[0] == T_STRING ) {
                        $arrCurrent[ 'name' ]  = $token[ 1 ];
                        $strMode = 'wait_args';
                }
            }
        }
        //Sys_Debug::dumpDie( $this->arrBlocks );
        
    }
    
    /**
     * 
     * @return array
     */
    public function getMethods()
    {
        $arrMethods = array();
        foreach( $this->arrBlocks as $arrBlock ) { 
	    if  (!isset( $arrBlock['name'] ) ) continue;
            if ( $arrBlock['type' ] == 'function' && !preg_match( '@Action$@', $arrBlock['name'] ) )
                $arrMethods [] = $arrBlock; 
        }
        return $arrMethods;
    }
    
    /**
     * 
     * @return array
     */
    public function getProperties()
    {
        $arrProperties = array();
        foreach( $this->arrBlocks as $arrBlock ) { 
            if ( $arrBlock['type' ] == 'var' )
                $arrProperties [] = $arrBlock; 
        }
        return $arrProperties;
    }
    /**
     * Get the array of available views template for that controller action
     * @return array
     */
    protected function getControllerActionViews( $strActionName )
    {
        $strCtrl = preg_replace( '@Ctrl$@', '', $this->getName() );
        $arrView = explode( '-_-', Sys_String::toLowerDashedCase( $strCtrl ) );
        
        $strAction = Sys_String::toLowerDashedCase( $strActionName );
        $dir = new Sys_Dir( CWA_APPLICATION_DIR . '/theme' );
        
        $objCache = new Sys_Cache_Memory();
        $arrFiles = $objCache->load( 'themes-list' );
        if ( $arrFiles === false ) {
            $arrFiles = $dir->getFiles();
            $objCache->save( $arrFiles, 'themes-list' );
        }
        $arrResults = array();
        
        foreach ( $arrFiles as $strFile ) {
            $strBase = strtolower( preg_replace( '@\.(.+)$@', '', basename( $strFile )));
            $arrFileParts = explode( "/", $strFile );
            $ns = $arrFileParts[ count( $arrFileParts ) - 3 ];
            $ctrl = $arrFileParts[ count( $arrFileParts ) - 2 ];
            
            if ( $ns == $arrView[0] && $ctrl == $arrView[1] ) {
                if ( $strBase == $strAction || substr( $strBase, 0, strlen( $strAction ) + 1 ) == $strAction.'-' ) {
                    $arrResults[]     = str_replace (CWA_APPLICATION_DIR . '/theme', '', $strFile);    
                }
            }
           //  $arrResults []= $strFile;
        }
        return $arrResults;
    }
    /**
     * 
     * @return array
     */
    public function getControllerActions()
    {
        $arrMethods = array();
        foreach( $this->arrBlocks as $arrBlock ) { 
	    if  (!isset( $arrBlock['name'] ) ) continue;
            if ( $arrBlock['type' ] == 'function' && preg_match( '@Action$@', $arrBlock['name'] ) ) {
                // TODO: add discovered views for the method..
                $arrBlock['shortname'] = str_replace ('Action', '', $arrBlock['name'] );
                $arrBlock['views'] = $this->getControllerActionViews( $arrBlock['shortname'] );
                
                $arrMethods [] = $arrBlock; 
            }
        }
        return $arrMethods;
    }

    /**
     * @return array
     */
    public function getTokens()
    {
        $arrResult = array();
        for( $i = $this->nStartToken; $i < $this->nEndToken; $i ++ ) {
            $arrResult []= $this->arrTokens[ $i ];
        }
        return $arrResult;
    }
    
    /**
     * Gets the table name
     * @return string
     */
    public function getTable()
    {
        $strMode = '';
        for( $i = $this->nStartToken; $i < $this->nEndToken; $i ++ ) {
            $token = $this->arrTokens[ $i ];
            if ( is_array( $token )) {
                if ( $token[ 0 ] == T_VARIABLE && $token[ 1 ] == '$_name' ) {
                    $strMode = 'waiting';
                } else if ( $strMode =='waiting' && $token[ 0 ] == T_CONSTANT_ENCAPSED_STRING ) {
                    return str_replace( '\'', '', $token[1] );
                }
            }
        }
        return '';
    }
    /**
     * Gets the primary key
     * @return string
     */
    public function getPrimaryKey()
    {
        $strMode = '';
        for( $i = $this->nStartToken; $i < $this->nEndToken; $i ++ ) {
            $token = $this->arrTokens[ $i ];
            if ( is_array( $token )) {
                if ( $token[ 0 ] == T_VARIABLE && $token[ 1 ] == '$_primary' ) {
                    $strMode = 'waiting';
                } else if ( $strMode =='waiting' && $token[ 0 ] == T_CONSTANT_ENCAPSED_STRING ) {
                    return $token[1];
                }
            }
        }
        return '';
    }
 
    protected $_arrPositionOffset = array( 'left' => 0, 'center' => 0, 'right' => 0 );
    
    protected function _getTableArray( $strTableName, $strPosition = 'right' )
    {
        
        // should be configurable
        $dbR = DBx_Registry::getInstance()->get()->getDbAdapterRead();
        $nRowHeight = 22;
        
        
        $arrFields = array();
        // walk thought fields of the table, and collect them in array
        $arrColumns = ( $dbR->describeTable( $strTableName ) );
        $nY = 0;
        foreach ( $arrColumns as $arrColumn ) {
            unset( $arrColumn[ 'SCHEMA_NAME' ] );
            unset( $arrColumn[ 'TABLE_NAME' ] ); 
            unset( $arrColumn[ 'COLUMN_POSITION' ] );
            
            $arrColumn[ 'Y' ] =  $nY;
            $arrFields[] = $arrColumn;
            $nY += $nRowHeight;
        }
        
        $nOffset = $this->_arrPositionOffset[ $strPosition ];
        $this->_arrPositionOffset[ $strPosition ] += ( count( $arrFields ) + 2 ) * $nRowHeight;
        
        return array(
            'name' => $strTableName,
            'fields' => $arrFields,
            'position' =>  $strPosition,
            'offset' =>  $nOffset,
            'height' => ( count( $arrFields ) + 1 ) * $nRowHeight
        );
    }
    /**
     * Get array of tables with coordinates
     * @return array
     */
    public function getSchemaTables()
    {
        $this->_arrPositionOffset = array( 'left' => 0, 'center' => 0, 'right' => 0 );
        $arrTables = array();
        $arrTables[] = $this->_getTableArray( $this->getTable(), 'center' ); 
        
        $docblock = new App_DocPage_Docblock( $this->strDocBlock );
        foreach( $docblock->getJoins() as $arrJoin ) {
            $arrTables[] = $this->_getTableArray( $arrJoin[ 'table' ], $arrJoin[ 'position' ] ); 
        }
        // Sys_Debug::dump( $arrTables );
        return $arrTables;
    }
    
    /**
     * 
     * @return array
     */
    public function getSchemaConnections()
    {
        $this->_arrPositionOffset = array( 'left' => 0, 'center' => 0, 'right' => 0 );
        
        $docblock = new App_DocPage_Docblock( $this->strDocBlock );
        $arrJoins = $docblock->getJoins();
        foreach ( $arrJoins as $key => $val ) {
            $arrJoins[ $key ]['from'] = $this->getTable();
        }
        return $arrJoins;
    }
}
