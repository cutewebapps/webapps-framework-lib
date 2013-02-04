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
     * store parent class  name
     * @var string
     */
    protected $strParent = '';
    
    /**
     * What interface are implemented in that class
     * @var array
     */
    protected $arrImplements = array();
    
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
    
    /**
     * Parse PHP by walking through its tokens (from tokenizer extension)
     * 
     * @param array $arrTokens
     * @param integer $nStartIndex
     * @return what offset we stopped parsing
     */
    public function parseTokens( array $arrTokens, $nStartIndex  )
    {
        $nIndex = $nStartIndex;
        return $nIndex;
    }
}