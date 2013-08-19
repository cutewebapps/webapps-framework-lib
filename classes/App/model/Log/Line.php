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
    }
    
    protected function _endOfUrl()
    {
        
    }
    
    
    public function getUrl()
    {
        $arrMatches = array();
        if ( preg_match( '@"(GET|POST|HEAD) (.+)"@', $this->_strLine, $arrMatches ) ) {
            return $arrMatches[2];
        }
        return '';
    }
    
    public function getHttpStatus()
    {
        
    }
    
    public function getRequestTime()
    {
    }

    public function getBodySize()
    {
    }
    
    public function getDate()
    {
    }
 
    public function getIp()
    {
        if ( preg_match( '@(\d+\.\d+\.\d+\.\d+)@', $this->_strLine, $arrMatches ) ) {
            return $arrMatches[2];
        }
        return '';
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