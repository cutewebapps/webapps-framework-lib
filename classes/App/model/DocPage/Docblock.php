<?php

class App_DocPage_Docblock
{
    protected $strContents = '';
    
    public function __construct( $strDocblock ) 
    {
        $this->strContents = $strDocblock;
    }
    
    public function getRawText()
    {
        $arrDirtyLines = explode( "\n", trim( 
                preg_replace(  '@^\s*\*@simU', '',
                preg_replace(  "@\*/@simU", '', preg_replace( "@^\/\*\*@simU", '', 
                $this->strContents )))));
        
        $arrLines = array();
        foreach ($arrDirtyLines as $strLine ) {
            if ( trim( $strLine ) ) {
                $arrLines [] = trim( $strLine );
            }
        }
        return implode( "<br />", $arrLines );
    }
}