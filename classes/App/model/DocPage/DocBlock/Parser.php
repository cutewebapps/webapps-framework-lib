<?php

/**
 * Class to parse data from DocBlock
 */
class App_DocPage_DocBlock_Parser
{
    protected $strContent = '';
    
    public function __construct( $sContent = '' )
    {
        $this->strContent = $sContent;
    }
    /**
     * @return string
     */
    public function getTextComment()
    {
        return '';
    }
    /**
     * @return array of App_DocPage_DocBlock_Argument
     */
    public function getArguments()
    {
        return '';
    }
    
}