<?php
/**
 * Class for parsing PHP file
 * 
 * @todo: must parse classes methods, classes inheritables and their docblocks
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
                    $strScope = 'public';
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
     * 
     * @return array
     */
    public function getControllerActions()
    {
        $arrMethods = array();
        foreach( $this->arrBlocks as $arrBlock ) { 
            if ( $arrBlock['type' ] == 'function' && preg_match( '@Action$@', $arrBlock['name'] ) ) {
                // TODO: add discovered views for the method..
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
                    return $token[1];
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
    
}