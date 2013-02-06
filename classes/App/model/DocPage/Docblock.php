<?php

class App_DocPage_Docblock
{
    protected $strContents = '';
    
    public function __construct( $strDocblock ) 
    {
        $this->strContents = $strDocblock;
    }
    /**
     * @return string
     */
    public function getRawText()
    {
        $arrDirtyLines = explode( "\n", $this->getPlainText() );
        $arrLines = array();
        foreach ($arrDirtyLines as $strLine ) {
            if ( trim( $strLine ) && ! preg_match( '/^@(join|center|left|right)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\.(\S+)\s*$/', trim( $strLine ))) {
                $arrLines [] = trim( $strLine );
            }
        }
        return implode( "<br />", $arrLines );
    }
    /**
     * @return string
     */
    public function getPlainText()
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
        return implode( "\n", $arrLines );
    }
    
    /**
     * @return array
     */
    public function getJoins()
    {
        $arrLines = explode( "\n", $this->getPlainText());
        $arrResult = array();
        foreach ( $arrLines as $strLine ) {
            if ( preg_match( '/^@(join|left|right)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\.(\S+)\s*$/', trim( $strLine ), $arrMatch )) {
                $arrResult [] = array(
                    'position'       => $arrMatch[1] == 'left' ? 'left' : 'right',
                    'rel_own'        => $arrMatch[2],
                    'column_own'     => str_replace( '`', '', $arrMatch[3] ),
                    'rel_foreign'    => $arrMatch[4],
                    'table'          => str_replace( '`', '', $arrMatch[5] ),
                    'column_foreign' => str_replace( '`', '', $arrMatch[6] ),
                );
            }
        }
        //Sys_Debug::dumpDie( $arrResult );
        return $arrResult;
    }
}