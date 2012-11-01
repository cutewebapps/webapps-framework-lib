<?php

class App_Permission
{
    const ALL = "*";
    const NONE = "";

    /**
     * @return boolean
     */
    public static function deny()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public static function allow()
    {
        return true;
    }

    /**
     * @param string $strPath  = "app.index.*"
     * @param array $arrParams
     * @return boolean
     */
    public static function matchPath( $strPath, $arrParams )
    {
        if ( !strstr( $strPath, '.' ) ) {
            throw new App_Exception( 'Invalid action path: '.$strPath );
        }
        
        $arrPathParts   =  explode( '.', strtolower( $strPath ));
        $strModule      =  trim( $arrPathParts[0] );
        $strController  =  trim( $arrPathParts[1] );
        $strAction      =  trim( $arrPathParts[2] );

        if ( $strModule  != '*' ) {
            if ( $strModule == '' || $strModule != strtolower( $arrParams['module'] ) )
                return false;
        }
        if ( $strController  != '*' ) {
            if ( $strController == '' || $strController != strtolower( $arrParams['controller'] ) )
                return false;
        }
        if ( $strAction  != '*' ) {
            if ( $strAction == '' || $strAction != strtolower( $arrParams['action'] ) )
                return false;
        }
        return true;
    }

    public static function checkAllowed( $func, $arrParams )
    {
        if ( $func == self::ALL )  {
            return true;
        } else if ( $func == self::NONE )  {
            return false;
        }
        return ( call_user_func( $func, $arrParams  ) );
    }

    public static function matchRules( $arrParams, $objConfig )
    {
        // allow everything by default
        $bAllowed = true;

        // walk throw  list  of rules
        if ( is_object( $objConfig ) ) {
            foreach ( $objConfig as $strPath => $func ) {
                if ( self::matchPath( $strPath, $arrParams )) {
                    $bAllowed = self::checkAllowed( $func, $arrParams );
                }
            }
        }
        return $bAllowed;
    }

    /**
     * @param array $arrParams
     * @throws App_Exception
     * @return boolean
     */
    public static function check( $arrParams )
    {
        

        $objConfig = App_Application::getInstance()->getConfig()->security;
        if ( ! is_object( $objConfig ) )
            return true; // allow action if security entry in the config
        $strSection = $arrParams['section'];
        $objSectionConfig = $objConfig->$strSection;
        if ( ! is_object( $objSectionConfig ) )
            return true; // allow action if no section is defined in security
        $objSectionActionConfig = $objConfig->$strSection->action;
        if ( ! is_object( $objSectionActionConfig ) )
            return true; // allow action if no action entry in section

        $bAllowed = self::matchRules( $arrParams, $objSectionActionConfig );
        if ( ! $bAllowed ) {
            $strPath = $arrParams['section'].':'.$arrParams['module']
                .'.'.$arrParams['controller'].'.'.$arrParams['action'];
            throw new App_Exception( 'Action security violation: '.$strPath );
        }
        return false;
    }

    /**
     * if security/section/token is not defined, it is not required
     * @return void
     */
    public static function checkToken( $arrParams )
    {
        
        $objConfig = App_Application::getInstance()->getConfig()->security;
        if ( ! is_object( $objConfig ) ) return;

        $strSection = $arrParams['section'];
        $objSectionConfig = $objConfig->$strSection;
        if ( ! is_object( $objSectionConfig ) )
            return;
        $objSectionActionConfig = $objConfig->$strSection->token;
        if ( ! is_object( $objSectionActionConfig ) )
            return; // allow action if no token entry in section

        $bRequired = self::matchRules( $arrParams, $objSectionActionConfig );
        if ( $bRequired ) {
            // if CSRF-token validation is required
            // generator class can be configured!!
            if ( ! App_Token::isValid() ) {
                throw new App_Exception( 'Invalid token, security violation' );
            }
        }

    }
}