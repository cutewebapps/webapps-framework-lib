<?php
/*
 * TEMPORARY CLASS
 *
 */

class DBx_Processor
{
    public $expr = '';
    public $pos = 0;
    public $verbose = 0;
    
    protected $objDbRead;
    protected $objDbWrite;

    public function __construct()
    {
        $this->objDbRead    = DBx_Registry::getInstance()->get('default')->getDbAdapterRead();
        $this->objDbWrite   = DBx_Registry::getInstance()->get('default')->getDbAdapterWrite();
    }
    
    protected function _ch($s, $pos)
    {
        return substr($s, $pos, 1);
    }

    function nextExpression($s)
    {
        $start = $this->pos;
        $len = strlen($s);
        $this->expr = '';
        $nLimitChars = 10000;
        do {
            $next_stop = $this->next($s, $this->pos);
            $this->pos = $next_stop + 1;
            $this->expr = trim($this->expr);

            $nLimitChars --;
        } while ($nLimitChars && $len > $next_stop && $this->_ch($s, $next_stop) != ';');
        $this->expr = preg_replace('/;\s*$/', '', $this->expr);
        return $this->expr;
    }
    
    public function setVerbose( $bValue = true )
    {
        $this->Verbose = $bValue;
    }

    public function execute( $strContents )
    {
        $this->pos = 0;
        $nLength  = strlen( $strContents );
        $nLimit = 10;
        while ( $this->pos < $nLength ) {
            
            $strExpression = $this->nextExpression( $strContents );
            Sys_Io::out( $strExpression );

            $nLimit -- ;
            if ( $nLimit == 0 ) break;
        }
    }

    public function executeFile( $strFileName )
    {
        $f = new Sys_File( $strFileName );
        if  ( ! $f->exists() )
            throw new DBx_Exception( 'SQL file was not found for execution '.$strFileName );

        $this->execute( file_get_contents( $f->getName() ) );
    }
}