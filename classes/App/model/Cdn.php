<?php

/**
 * This class is for CDN management from the backend
 */
class App_Cdn
{
    protected $_arrProperties = array();
    protected $_ftpConnection = null;

    protected $_isVerbose   = false;

    protected $_strCdnName   = '';
    protected $_strCdnFolder = '';

    /** @return void */
    public function setVerbose( $bVerbose = 1 )
    {
        $this->_isVerbose = $bVerbose;
    }

    /** @return boolean */
    public function isVerbose()
    {
        return $this->_isVerbose;
    }

    /** @return resource */
    public function getConnectionId()
    {
        return $this->_ftpConnection;
    }

    /** @return boolean */
    public function isFtp()
    {
        return ( isset($this->_arrProperties['scheme'] )
              && $this->_arrProperties['scheme'] == 'ftp' );
    }
    
    /** @return boolean */
    public function isLocal()
    {
        return !isset($this->_arrProperties['scheme']) || $this->_arrProperties['scheme'];
    }

    /**
     * @return string
     */
    public function getHttpPath() { return $this->_arrProperties['http']; }
    /**
     * @return string
     */
    public function getHost() { return $this->_arrProperties['host']; }
    /**
     * @return int
     */
    public function getPort() { return isset( $this->_arrProperties['port'] ) ? intval( $this->_arrProperties['port']) : 21 ; }
    /**
     * @return string
     */
    public function getLogin() { return isset( $this->_arrProperties['user'] ) ? $this->_arrProperties['user'] : 'anonymous' ; }
    /**
     * @return string
     */
    public function getPassword() { return isset( $this->_arrProperties['password'] ) ? $this->_arrProperties['password'] : ''; }

    
    public function __construct( $strCdnName, $bVerbose = 0 )
    {
        $this->setVerbose( $bVerbose );

        $this->_strCdnName = $strCdnName;
        $configCdn = App_Application::getInstance()->getConfig()->cdn;
        $this->_arrProperties = $configCdn->$strCdnName->toArray();

        if ( $this->isFtp() ) {
            $this->_ftpConnection = ftp_connect( $this->getHost(), $this->getPort() );
            if ( !$this->_ftpConnection )
                throw new App_Cdn_Exception( 'No FTP Connection ('
                        .$this->getHost().':'.$this->getPort(). ')' );
            if ( $this->isVerbose() )
                Sys_Io::out( 'Connected to FTP' );

            if ( ! ftp_login( $this->getConnectionId(), $this->getLogin(), $this->getPassword() )) {
                throw new App_Cdn_Exception( 'FTP Login Failure ('
                        .$this->getLogin().'@'.$this->getHost().':'.$this->getPort(). ')' );
            }
            if ( $this->isVerbose() )
                Sys_Io::out( 'User Password Accepted' );

            if ( ! ftp_pasv( $this->getConnectionId(), true) ) {
                throw new App_Cdn_Exception( 'Failed to switch into PASV mode' );
            }
            if ( $this->isVerbose() )
                Sys_Io::out( 'PASV mode enabled' );


        }
        
    }

    public function __destruct()
    {
        if ( $this->isFtp() ) {
            if ( $this->getConnectionId() != null ) {
                ftp_close( $this->getConnectionId() );
                if ( $this->isVerbose() )
                    Sys_Io::out( 'FTP Connection closed' );
            }
        }
        $this->_ftpConnection = null;
    }

    public function putFile( $strSourceFile, $strDestinationFile )
    {
        if ( $this->isFtp() ) {
            return ftp_put( $this->getConnectionId(), $strDestinationFile, $strSourceFile, FTP_BINARY );
        } else {
            
        }
    }

}