<?php

class App_HeadMetaHelper extends App_ViewHelper_Abstract
{
    protected static $_instance = null;

    protected $_arrName      = array();
    protected $_arrHttpEquiv = array();

    /**
     * @return App_Layout
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @return App_HeadMetaHelper
     */
    public function headMeta()
    {
        return self::getInstance();
    }

    /**
     * @return App_HeadMetaHelper
     */
    public function addName( $name, $content )
    {
        $this->_arrName[ $name ] = $content;
        // echo( 'added: '. $name. ' = '.$content );
        return $this;
    }

    /**
     * @return App_HeadMetaHelper
     */
    public function addHttpEquiv( $name, $content )
    {
        $this->_arrHttpEquiv[ $name ] = $content;
        return $this;
    }


    /**
     * @return App_HeadMetaHelper
     */
    public function setCharset( $strCharset )
    {
        return $this->addHttpEquiv( 'Content-Type', "text/html; charset=" . $strCharset );
        
    }

    /**
     * @return App_HeadMetaHelper
     */
    public function setNotScalable()
    {
        return $this->addName( 'viewport', 'width=device-width,user-scalable=no' );
    }

    /**
     * @return App_HeadMetaHelper
     */
    public function enableResponsiveDesign()
    {
        $strBrowser = isset( $_SERVER['HTTP_USER_AGENT' ] ) ? $_SERVER['HTTP_USER_AGENT' ] : '';
        if ( preg_match( '@MSIE (\d+)@sim', $strBrowser, $arrMatch ) ) {
            $nIeVersion = intval( $arrMatch[1]);
    //        if ( $nIeVersion < 9 )
      //          $this->getView()->HeadScript()
      //               ->append( '//css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js' );
        }
        return $this->addName( 'viewport', 'width=device-width, initial-scale=1.0' );
    }

    /**
     * @return string
     */
    public function get()
    {
        $arrStrResults = array();
        foreach( $this->_arrHttpEquiv as $strKey => $strValue )
            if ( $strValue != '' )
                $arrStrResults[] = "\t".'<meta http-equiv="'.htmlspecialchars( $strKey, ENT_QUOTES )
                    .'" content="'.htmlspecialchars ( $strValue, ENT_QUOTES ).'" />';
        foreach( $this->_arrName as $strKey => $strValue )
            if ( $strValue != '' )
                $arrStrResults[] = "\t".'<meta name="'.htmlspecialchars( $strKey, ENT_QUOTES )
                    .'" content="'.htmlspecialchars ( $strValue, ENT_QUOTES ).'" />';
        return implode( "\n", $arrStrResults );
    }
    
    /**
     * @return App_HeadMetaHelper
     */
    public function addDescription( $content )
    {
        return $this->addName('description', $content );
    }
    
    /**
     * @return App_HeadMetaHelper
     */
    public function addDesigner( $content )
    {
        return $this->addName('designer', $content );
    }
    
    /**
     * @return App_HeadMetaHelper
     */
    public function addAuthor( $content )
    {
        return $this->addName( 'author', $content );
    }   
    /**
     * @return App_HeadMetaHelper
     */
    public function addCopyright( $content = '' )
    {
        if ( $content == '' )
            $content = 'Copyright '.date('Y');
        
        return $this->addName( 'copyright', $content );
    }    
    /**
     * @return App_HeadMetaHelper
     */
    public function addContentLanguage( $locale  = 'en-GB')
    {
        return $this->addHttpEquiv( 'content-language', $locale );
    }    
    /**
     * @return App_HeadMetaHelper
     */
    public function noRobots()
    {
        return $this->addName( 'robots', 'noindex,nofollow' );
    }
    
    /**
     * @return App_HeadMetaHelper
     */
    public function noCache()
    {
        return $this->addHttpEquiv( 'pragma', 'nocache' );
    }
}
