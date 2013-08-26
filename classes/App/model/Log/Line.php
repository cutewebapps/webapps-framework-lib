<?php
/**
 * typical NGINX main-format
 * 
 * log_format  main  '$remote_addr - $remote_user [$time_local] [[$request_time]] "$request" '
 *    '$status $body_bytes_sent "$http_referer" '
 *    '"$http_user_agent" "$http_x_forwarded_for"';
 * 
 */
class App_Log_Line
{
    protected $_strLine = null;
    
    public function __construct( $strLine )
    {
        $this->_strLine = $strLine;
        // Sys_Io::out( $this->_strLine );
    }
    
    protected function _endOfUrl()
    {
        return Sys_String::x( '@ HTTP/\d+\.\d+\"(.+)$@', $this->_strLine );
    }
    
    public function getUrl()
    {
        $arrMatches = array();
        if ( preg_match( '@"(GET|POST|HEAD) (.+) HTTP@', $this->_strLine, $arrMatches ) ) {
            return $arrMatches[2];
        }
        return '';
    }
    
    public function getHttpStatus()
    {
        $sEnd = trim( $this->_endOfUrl());
        return Sys_String::x( '@^(\d+)@', $sEnd );
    }
    
    public function getRequestTime()
    {
        $sEnd = trim( $this->_endOfUrl());
        return Sys_String::x( '@\[\[(\d+)\]\]@', $sEnd );
    }

    public function getBodySize()
    {
        $sEnd = trim( $this->_endOfUrl());
        return Sys_String::x( '@^(\d+)\s+(\d+)@sim', $sEnd, 2 );
    }
    
    public function getDate()
    {
        return date( 'Y-m-d H:i:s', strtotime( Sys_String::x( '@\[(.+)\]@simU', $this->_strLine ) ));
    }
    public function getUnixTime()
    {
        return strtotime( Sys_String::x( '@\[(.+)\]@simU', $this->_strLine ) );
    }
 
    public function getIp()
    {
        return Sys_String::x( '@^([\d\.]+)@', $this->_strLine );
    }
    
    public function debug()
    {
        print_r( array(
            'IP'     => $this->getIp(),
            'DATE'   => $this->getDate(),
            'URL'    => $this->getUrl(),
            'STATUS' => $this->getHttpStatus(),
            'TIME'   => $this->getRequestTime(),
            'SIZE'   => $this->getBodySize(),
        ));
    }
}