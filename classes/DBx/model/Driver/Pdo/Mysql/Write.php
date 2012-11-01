<?php

class DBx_Driver_Pdo_Mysql_Write extends DBx_Driver_Pdo_Write
{
    protected $_pdoType = 'mysql';

    /**
     * checks for valid Reader-pair class
     * function is used for avoiding second connection
     * 
     * @param string $strReaderClass
     * @return boolean 
     */
    public function isWriterFor( $strReaderClass )
    {
        return ( $strReaderClass == 'DBx_Driver_Pdo_Mysql_Read' );
    }
}