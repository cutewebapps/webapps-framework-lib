<?php

class App_Http_Browser
{
    public $ConnectTimeout  = 30;
    public $DownloadTimeout = 120;
    public $UserAgent       = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16.0.912.63 Safari/535.7";
    public $curl = null;
    public $CookieFile = '';

    public $LastHttpUrl = '';
    public $LastFormData = array();
    public $NextFormData = array();
    public $HttpStatus;
    public $HttpHeaders;
    public $HttpBody;
    public $Info = array(); //info from last request


    public $bNoFollow = false;
    /**
     * @var integer
     */
    protected $_nPagesVisited = 0;

    /**
     * @var boolean
     */
    protected $_bUseCache = false;
    /**
     * @var boolean
     */
    protected $_bSaveCache = false;

/**
     * @param array $_f
     * @return string
     */
    protected function _mergeParams( &$_f ) {
        $params='';
        if ( isset($_f) && is_array($_f) ){
            $i=0;
            foreach ($_f as $key=>$value){
                if($i>0) $params .= '&';
                $params.=$key . '=' . urlencode($value); $i++;
            }
        }
        return $params;
    }

    /**
     * put cUrl responce in HttpStatus, HttpHeaders, HttpBody
     * @return void
     * @param string $strBuffer
     */
    protected function _parseResponse( $strBuffer )
    {
        $this->_nPagesVisited ++;

        // save buffer to a history in a cache
        if ( $this->_bSaveCache ) {
            $file = new Sys_File( $this->_strCacheDir.'/'.date('His').'-'.$this->_nPagesVisited.'.htm' );
            $file->save( $strBuffer );
        }

        $hdr = ""; $body = "";
	$result = -1; $msg = "";

        $answer = explode( "\r\n\r\n", trim( $strBuffer ) );
	$h = array(); $a = $answer; $status = 0;
	foreach ( $answer as $key => $aa ) {
            if ( substr( trim( $aa ), 0, 7 ) == "HTTP/1." ) {
                $h[] = $aa;
                if ( preg_match("'HTTP/(\d\.\d)\s+(\d+).*'i", trim( $aa ), $matches )) {
                        $status = $matches[2];
                }
                unset( $a[ $key ] );
            } else break;
        }

        $this->HttpStatus  = $status;
	$this->HttpHeaders = implode("\r\n\r\n", $h );
	$this->HttpBody    = implode("\r\n\r\n", $a );
    }

    /**
     * Get location of redirection
     * @return string
     */
    public function getLocation()
    {
        return trim( Sys_String::x( '@Location:(.+)\s@simU', $this->HttpHeaders.' ' ) );
    }
    
    /**
     * @param string $strUrl
     * @return App_Http_Browser
     */
    public function httpGet( $strUrl )
    {
        $this->init();
        if ( $this->LastHttpUrl != '' ) {
            curl_setopt($this->curl, CURLOPT_REFERER, $this->LastHttpUrl );
        }

        curl_setopt( $this->curl, CURLOPT_URL, $strUrl );
        $buff = curl_exec( $this->curl );

        $this->errorMsg = curl_error( $this->curl );
        $this->errorNumber = curl_errno( $this->curl );
        $this->Info = curl_getinfo($this->curl);
        curl_close( $this->curl );
        if ( $this->errorNumber != 0 ) {
            throw new App_Http_Exception( $this->errorMsg );
        }
        $this->LastHttpUrl = $strUrl;
        $this->_parseResponse( $buff );
        return $this;
    }

    /**
     * @param string $strUrl
     * @param array $arrData
     * @param array $arrFiles
     * @return App_Http_Browser
     */
    public function httpPost( $strUrl, $arrData, $arrFiles = array() )
    {
        $this->init();
        if ( $this->LastHttpUrl != '' ) {
            curl_setopt($this->curl, CURLOPT_REFERER, $this->LastHttpUrl );
        }

        curl_setopt( $this->curl, CURLOPT_URL, $strUrl );
	curl_setopt( $this->curl, CURLOPT_POST,1);
        if ( count( $arrFiles ) > 0 ) {
            $arrParams = $arrData;
            foreach ( $arrFiles as $strFieldName => $strFileName ) {
                $arrParams[ $strFieldName ] = '@'.$strFileName;
            }
            curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $arrParams );
        } else {
            curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $this->_mergeParams( $arrData ) );
        }

        $buff = curl_exec( $this->curl );

        $this->errorMsg = curl_error( $this->curl );
        $this->errorNumber = curl_errno( $this->curl );
        $this->Info = curl_getinfo($this->curl);
        curl_close( $this->curl );
        if ( $this->errorNumber != 0 ) {
            throw new App_Http_Exception( $this->errorMsg );
        }
        $this->LastHttpUrl = $strUrl;
        $this->_parseResponse( $buff );
        return $this;
    }

    /**
     * 
     * @param string  $strUrl
     * @param string $strRawBody
     * @return App_Http_Browser
     */
    public function httpPostRaw( $strUrl, $strRawBody, $arrHeaders = array() )
    {
         $this->init();
        if ( $this->LastHttpUrl != '' ) {
            curl_setopt($this->curl, CURLOPT_REFERER, $this->LastHttpUrl );
        }

        curl_setopt( $this->curl, CURLOPT_URL, $strUrl );
	curl_setopt( $this->curl, CURLOPT_POST,1);
        curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $strRawBody );
        
        curl_setopt( $this->curl, CURLOPT_HTTPHEADER, $arrHeaders );
        
        $buff = curl_exec( $this->curl );

        $this->errorMsg = curl_error( $this->curl );
        $this->errorNumber = curl_errno( $this->curl );
        $this->Info = curl_getinfo($this->curl);
        curl_close( $this->curl );
        if ( $this->errorNumber != 0 ) {
            throw new App_Http_Exception( $this->errorMsg );
        }
        $this->LastHttpUrl = $strUrl;
        $this->_parseResponse( $buff );
        return $this;
    }

    /**
     * @return App_Http_Browser
    */
    public function init()
    {
        $this->curl = curl_init();

        // skip all SSL stuff
        curl_setopt( $this->curl, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $this->curl, CURLOPT_SSL_VERIFYHOST, 0 );

        // force HTTP/1.0 in order not to make locve with chunked transfers...
        curl_setopt( $this->curl, CURLOPT_HTTP_VERSION,  CURL_HTTP_VERSION_1_0 );

        // follow redirects, this can get errors on hostings with safe-mode
        if ( ! $this->bNoFollow ) {
            curl_setopt($this->curl,  CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt($this->curl,  CURLOPT_MAXREDIRS, 5);
        }

        // we will parse header later
        curl_setopt($this->curl,  CURLOPT_HEADER, 1 );
        curl_setopt($this->curl,  CURLOPT_CONNECTTIMEOUT,   $this->ConnectTimeout );
        curl_setopt($this->curl,  CURLOPT_TIMEOUT,          $this->DownloadTimeout );
        curl_setopt($this->curl,  CURLOPT_USERAGENT,        $this->UserAgent );

        // return the content
        curl_setopt($this->curl,  CURLOPT_RETURNTRANSFER, 1);

        if ( $this->CookieFile != "" ) {
            curl_setopt($this->curl, CURLOPT_COOKIEJAR,  $this->CookieFile );
            curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->CookieFile );
        }
        return $this;
    }


    public function setUseCache( $bValue = 1 )
    {
        $this->_bUseCache = $bValue;
        return $this;
    }

    public function setSaveCache( $bValue = 1 )
    {
        $this->_bSaveCache = $bValue;
        return $this;
    }

    
    /**
     * @param string $strCacheFolder
     * @return void
     */
    public function setCacheFolder( $strCacheFolder, $strCookieFile = 'cookie.txt' )
    {
        $this->CookieFile = $strCacheFolder.'/'.$strCookieFile;
        if ( $strCookieFile != 'cookie.txt' ) {
            // if using default cookies, we should empty cookie file
            $file = new Sys_File( $this->CookieFile );
            $file->save( '' );
            if ( ! $file->exists() ) {
                throw new App_Http_Exception( 'Cannot create folder for http cache and sessions' );
            }
        }

        $this->_strCacheDir = $strCacheFolder.'/'.date('Ymd');
        // Sys_Io::out( 'cache Dir was created '.$this->_strCacheDir  );
        return $this;
    }
    
 /**
   * converting relative URL into absolute
   * @param string $rel
   * @param string $base
   * @return string
   */
    public function relUrlToAbs( $rel, $base )
    {
        if ( $rel == '' )
            return $base;

        /* return if already absolute URL */
        if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;

        /* queries and anchors */
        if ( isset( $rel[0] )) {
            if ($rel[0]=='#') return $base.$rel;
            if ($rel[0]=='?') {
            	   $request_chr = strpos($base, '?');
            	   if ($request_chr) {
            	   	    $base = substr($base, 0, $request_chr);
            	   }
            	   return $base.$rel;
            }
        }

        /* parse base URL and convert to local variables:
         $scheme, $host, $path */
        extract( parse_url($base) );

        /* remove non-directory element from path */
        $path = preg_replace('#/[^/]*$#', '', $path);

        /* destroy path if relative url points to root */
        if ( isset( $rel[0] )) {
            if ($rel[0] == '/') $path = '';
        }

        /* dirty absolute URL */
        $abs = "$host$path/$rel";

        /* replace '//' or '/./' or '/foo/../' with '/' */
        $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
        for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

        /* absolute URL is ready! */
        return $scheme.'://'.$abs;
    }
}