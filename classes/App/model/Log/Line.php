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
    
    /**
     * constructor
     * @param string $strLine
     */
    public function __construct( $strLine )
    {
        $this->_strLine = $strLine;
        // Sys_Io::out( $this->_strLine );
    }
    
    /**
     * 
     * @return string
     */
    protected function _endOfUrl()
    {
        return Sys_String::x( '@ HTTP/\d+\.\d+\"(.+)$@', $this->_strLine );
    }
    
    /**
     * 
     * @return string
     */
    public function getUrl()
    {
        $arrMatches = array();
        if ( preg_match( '@"(GET|POST|HEAD) (.+) HTTP@', $this->_strLine, $arrMatches ) ) {
            return $arrMatches[2];
        }
        return '';
    }
    /**
     * 
     * @return string
     */
    public function getUrlWithoutParams()
    {
        return preg_replace( '@\?.*$@', '', $this->getUrl() );
    }
    
    /**
     * @return array
     */
    public function getUrlParams()
    {
        $arrPath =  parse_url( $this->getUrl());
        if ( !isset( $arrPath['query']  )) return array();
        $arrOut  = array();
        
        parse_str( preg_replace( '@^\?@', '', $arrPath['query']), $arrOut );
        return $arrOut;
    }
    
    /**
     * 
     * @return int
     */
    public function getHttpStatus()
    {
        $sEnd = trim( $this->_endOfUrl());
        return Sys_String::x( '@^(\d+)@', $sEnd );
    }
    
    /**
     * 
     * @return decimal
     */
    public function getRequestTime()
    {
        $sEnd = trim( $this->_endOfUrl());
        return Sys_String::x( '@\[\[(.+)\]\]@', $this->_strLine );
    }

    /**
     * 
     * @return int
     */
    public function getBodySize()
    {
        $sEnd = trim( $this->_endOfUrl());
        return Sys_String::x( '@^(\d+)\s+(\d+)@sim', $sEnd, 2 );
    }
    
    /**
     * 
     * @return string
     */
    public function getDate()
    {
        return date( 'Y-m-d H:i:s', strtotime( Sys_String::x( '@\[(.+)\]@simU', $this->_strLine ) ));
    }
    
    /**
     * 
     * @return int
     */
    public function getUnixTime()
    {
        return strtotime( Sys_String::x( '@\[(.+)\]@simU', $this->_strLine ) );
    }
 
    /**
     * 
     * @return string
     */
    public function getIp()
    {
        return Sys_String::x( '@^([\d\.]+)@', $this->_strLine );
    }
    
    /**
     * @return string
     */
    public function debug()
    {
        return print_r( array(
            'IP'     => $this->getIp(),
            'DATE'   => $this->getDate(),
            'URL'    => $this->getUrl(),
            'STATUS' => $this->getHttpStatus(),
            'TIME'   => $this->getRequestTime(),
            'SIZE'   => $this->getBodySize(),
        ), true );
    }
}