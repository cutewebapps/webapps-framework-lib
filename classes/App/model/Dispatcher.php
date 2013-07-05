<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

require CWA_DIR_CLASSES.'/App/model/Dispatcher/CtrlPlugin.php';

/** handling fatal errors separately - with register_shutdown function*/
function exceptionErrorHandler($errno, $errstr, $errfile, $errline )
{
    global $_EXCEPTION_WAS_CAUGHT;
    $_EXCEPTION_WAS_CAUGHT = 1;
    
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
function shutdownErrorHandler()
{
    global $_EXCEPTION_WAS_CAUGHT;
    if ( isset( $_EXCEPTION_WAS_CAUGHT) ) return;
    
    $last_error = error_get_last();
    if ( !isset( $last_error['message'] ) ) return;

    $strErrorMessage = $last_error['message'];
    if ( isset( $last_error['file'] ) )
        $strErrorMessage .= ' at '. $last_error['file'];
    if ( isset( $last_error['line'] ) )
        $strErrorMessage .= ':'. $last_error['line'];

    $confException = App_Application::getInstance()->getConfig()->exceptions;
    if  (is_object( $confException ) ) {
       
        if ( $confException->log ) {

            $strFileName = $confException->log.'/'.date('Y-m-d').'.log';
            //if ( ini_get( 'safe_mode' ) ) $strFileName = $confException->log;

            // same exception info into LOG file
            $strFile = new Sys_File( $strFileName );
            $strBody = date("Y-m-d H:i:s")."\t".$_SERVER['REMOTE_ADDR']
                                ."\t".App_Dispatcher::$strUrl.' ';
            $strBody .= "\n***\t".$strErrorMessage;
            $strBody .= "\n\n";
            $strFile->append( $strBody );
        }

        if ( $confException->alert ) {
            $strMessage = "***\t[".date('Y-m-d H:i:s')."]\n";
            $strMessage .= "\n***\t<strong>".$strErrorMessage.'</strong>'."\n\n";
            
            foreach ( $_SERVER as $strKey => $strValue )  {
                if ( !is_array( $strValue ) ) {
                    $strMessage .= '_SERVER['.$strKey . ']=' . $strValue ."\n";
                } else {
                    ob_start();
                    print_r( $strValue );
                    $strContents = ob_get_contents();
                    ob_end_clean();
                    $strMessage .= '_SERVER['.$strKey . ']=' .$strContents;
                }
            }
            foreach ( $_POST as $strKey => $strValue )  {
                if ( is_string( $strValue ) ) {
                    $strMessage .= '_POST['.$strKey . ']=' . $strValue ."\n";
                } else {
                    $strMessage .= '_POST['.$strKey . ']=' . print_r( $strValue, true ) ."\n";
                }
            }
            foreach ( $_COOKIE as $strKey => $strValue )  {
                $strMessage .= '_COOKIE['.$strKey . ']=' . $strValue ."\n";
            }
            Sys_Debug::alert( $strMessage );
        }
        
        if ( $confException->mail ) {
            // mail exception info to somebody
            $strTo = $confException->mail->to;
            $strSubject = $confException->mail->subject.' '
                    .'"'.$strErrorMessage.'"'
                    .' for '.$_SERVER['REMOTE_ADDR'];
            $strHeaders =
                    'Content-Type: text/html; charset="utf-8"' . "\r\n"
                    .$confException->mail->headers;

            // preparing message - more detailed than in logs
            $strMessage = "<pre style='font-size:12px'>"."***\t[".date('Y-m-d H:i:s')."]\n";
            $strMessage .= "\n***\t<strong>".$strErrorMessage.'</strong>'."\n\n";
            
            foreach ( $_SERVER as $strKey => $strValue )  {
                if ( !is_array( $strValue ) ) {
                    $strMessage .= '_SERVER['.$strKey . ']=' . $strValue ."\n";
                } else {
                    ob_start();
                    print_r( $strValue );
                    $strContents = ob_get_contents();
                    ob_end_clean();
                    $strMessage .= '_SERVER['.$strKey . ']=' .$strContents;
                }
            }
            foreach ( $_POST as $strKey => $strValue )  {
                if ( is_string( $strValue ) ) {
                    $strMessage .= '_POST['.$strKey . ']=' . $strValue ."\n";
                } else {
                    $strMessage .= '_POST['.$strKey . ']=' . print_r( $strValue, true ) ."\n";
                }
            }
            foreach ( $_COOKIE as $strKey => $strValue )  {
                $strMessage .= '_COOKIE['.$strKey . ']=' . $strValue ."\n";
            }
            $strMessage .= '</pre>';
            
            // using php mailer to send
            if ($confException->mail->method == 'php-mailer') {
                
                $mail = new App_Mailer();

                $mail->IsSMTP();
                $mail->Host     = $confException->smtp->host;
                if ( $confException->smtp->username != "" ) {
                    $mail->SMTPAuth = true;
                    $mail->Username = $confException->smtp->username;
                    $mail->Password = $confException->smtp->password;
                }

                // from
                $arrFrom = explode(' ', $confException->mail->headers);
                $strFrom = $arrFrom[1];
                
                $mail->From = $strFrom;
                $mail->AddReplyTo($mail->From);

                // to
                $arrTo = explode(',', $strTo);
                
                foreach ($arrTo as $strTo)
                    $mail->AddAddress($strTo);
                
                // attachments
                //$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // name is optional

                $mail->IsHTML( true );
                $mail->WordWrap = 300;
                $mail->SetXMailer( 'Exception Debug Mailer' );

                // subject and body
                $mail->Subject = $strSubject;
                $mail->Body    = $strMessage;
                //$mail->AltBody = $strMessage;

                $mail->Send();                
                
            } else {
                mail( $strTo, $strSubject, $strMessage, $strHeaders );
            }
        }

        if ( $confException->render ) {
            echo file_get_contents( CWA_APPLICATION_DIR.'/'.$confException->render  );
        }
    }
}

class App_Dispatcher
{
    /**
     * full version of current URL
     * @var string
     */
    static public $strFullUrl  = '';
    /**
     * 
     * @var string
     */
    static public $strUrl      = '';
    /** 
     * Time of dispatcher start - for checking performance
     * @var Sys_Timeframe
     */
    public    $timeframe  = '';
    protected $_arrRoutes = array();
    protected $_strResponse = '';
    protected $_arrUrlParams = array();
    protected $_strDefaultController = '';
    protected $_objCurrentController = null;
    protected $_strCurrentAction = '';
    public function getInstanceId()
    {
        return App_Application::getInstance()->getInstanceId();
    }
    public function getUrlParams()
    {
        return $this->_arrUrlParams;
    }
    public function getController()
    {
        return $this->_objCurrentController;
    }
    public function getAction()
    {
        return $this->_strCurrentAction;
    }
    public function getConfig()
    {
        return App_Application::getInstance()->getConfig();
    }
    public function preDispatch()
    {
        if ( defined( 'CWA_DISABLE_PLUGINS') )
            return true;
        
        if ( is_object( $this->getConfig()->ctrlplugin ) ) {
    
            foreach( $this->getConfig()->ctrlplugin as $strPluginClass ) {
                $objPlugin = new $strPluginClass( $this );
                if ( !$objPlugin->preDispatch() ) return false;
            }
         }
         return true;
    }
    public function postDispatch()
    {
        if ( defined( 'CWA_DISABLE_PLUGINS') )
            return true;

        if ( is_object( $this->getConfig()->ctrlplugin ) ) {
            foreach( $this->getConfig()->ctrlplugin as $strPluginClass ) {
                $objPlugin = new $strPluginClass( $this );
                $objPlugin->postDispatch();
            }
         }
         return true;
    }
    public function __construct( $arrRoutes = array(), $strDefaultController = '' )
    {
        $this->_arrRoutes = $arrRoutes;
        $this->_strDefaultController = $strDefaultController;
    }
    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->_arrRoutes;
    }
    protected function _getSectionFromTheme( $strTheme )
    {
        $arrSections = $this->getConfig()->sections->toArray();
        foreach ( $arrSections as $strSection => $strConfigTheme )
            if ( $strConfigTheme == $strTheme ) { return $strSection; }
        return '';
    }
    protected function _getSectionFromSlug( $strSlug )
    {
        $areas = $this->getConfig()->user->area;
        if ( is_object( $areas )) {
            $arrSections = $areas->toArray();

            foreach ( $arrSections as $strSection => $arrProperties )
                if ( isset( $arrProperties['section'] ) &&
                     $strSection == $strSlug ) { return $arrProperties['section']; }
        }
        return '';
    }
    public function runAction( $strAction, $strController, $strModule, $arrParams = array() )
    {
        $strClass = Sys_String::toCamelCase( $strModule ) .'_'
            . Sys_String::toCamelCase( $strController ) . 'Ctrl';

        $arrParams[ 'action' ] = $strAction;
        $arrParams[ 'controller' ] = $strController;
        $arrParams[ 'module' ] = $strModule;
        return $this->runControllerAction( $strAction, $strClass, $arrParams );
    }
    protected function _log( $arrParams )
    {
        if ( $this->getConfig()->action_log ) {
            // if action log was enabled, we should write the actions
            $strPath = $arrParams['module'].'/'.$arrParams['controller'].'/'.$arrParams['action'];
            if ( isset( $arrParams['template'] ))
                $strPath .= '-'.$arrParams['template'];
            if ( isset( $arrParams['section'] ))
                $strPath = $arrParams['section']. '/'.$strPath;
            
            $arrMyParams = $arrParams;
            unset( $arrMyParams[  'section' ] );
            unset( $arrMyParams[  'module' ] );
            unset( $arrMyParams[  'controller' ] );
            unset( $arrMyParams[  'action' ] );
            unset( $arrMyParams[  'template'] );
            
            $strLine = date('Y-m-d H:i:s').' ['.$this->getInstanceId().'] '.$strPath.' '.json_encode( $arrMyParams );
            
            $strLogFile = new Sys_File( $this->getConfig()->action_log );
            $strLogFile->append( $strLine. "\n" );
        }
    }
    public function runControllerAction( $strAction, $strControllerClass, $arrParams = array() )
    {
        $this->_log( $arrParams );
        $strControllerAction = Sys_String::toCamelCase( $strAction ).'Action';

	if ( !class_exists( $strControllerClass ) ) {
            throw new App_Exception_PageNotFound( 'No controller class for this call' );
        }
        if ( !method_exists($strControllerClass, $strControllerAction ) ) {
            throw new App_Exception_PageNotFound( 'No action for this controller' );
        }
        if ( isset( $arrParams['default_renderer'] ) ) {
            if ( !class_exists( $arrParams['default_renderer'] ) ) {
                throw new App_Exception_PageNotFound( "Invalid renderer class ".$arrParams['default_renderer']);
            }
        }

        $this->_objCurrentController = new $strControllerClass( $arrParams );
        $this->_strCurrentAction = $strAction;

        if ( self::$strUrl != '' )
            $this->_objCurrentController->view->url = (string) self::$strUrl;
        
        if ( isset( $arrParams['noplugin'] ) ) {
            $this->_objCurrentController->$strControllerAction();
        } else {
            if ( $this->preDispatch() ) {
                $this->_objCurrentController->$strControllerAction();
                $this->postDispatch();
            }
        }

        if ( !isset( $arrParams['section'] )) {
            foreach ($this->getConfig()->sections as $strSection => $strSectionTheme ) {
                if ( $strSection != '' ) { $arrParams['section'] = $strSection; break; }
            }
        }


        $strTheme = 'admin';
        foreach ($this->getConfig()->sections as $strSection => $strSectionTheme )
            if ( $arrParams['section'] == $strSection ) { $strTheme = $strSectionTheme; break; }
        //  Sys_Debug::dumpDie( 'THEME: '. $strTheme.' for SECTION='.$arrParams['section'] );

        $strModule     = $arrParams['module'];
        $strController = $arrParams['controller'];
        list( $strModule, $strController ) = explode( '_', preg_replace( '/Ctrl$/', '', $strControllerClass ));
        $strModule     = Sys_String::toLowerDashedCase( $strModule );
        $strController = Sys_String::toLowerDashedCase( $strController );

        $this->_objCurrentController->view->inflection = $arrParams;

        if ( isset( $arrParams['norender'] ) ) {
            // TODO: (security) this should be disabled for URL-queries
            // in this case we're returning pure objects..
            return $this->_objCurrentController->view;
        }

        $strSuffix = '';
        if ( isset( $arrParams['format'] ) ) {
            // TODO: severe formats validation
            $strSuffix = '.'.$arrParams[ 'format' ];
        }

        $arrThemes = $strTheme;
        if ( is_string( $arrThemes )) $arrThemes = array( $strTheme );
        else if ( $arrThemes instanceof Sys_Config ) $arrThemes = $arrThemes->toArray();

        $arrScriptPaths = array();
        if ( $this->_objCurrentController->getRender() == ""  ) {
            return "";
        }
        
        foreach ( $arrThemes as $strThemeName ) {
            $arrScriptPaths []= implode( '/', array(
                CWA_APPLICATION_DIR,
                'theme',
                $strThemeName,
                $arrParams['section'],
                $strModule,
                $strController,
                $this->_objCurrentController->getRender() . $strSuffix
                    .'.' . $this->_objCurrentController->view->getExtension()
            ));
        }

        
        $this->_objCurrentController->view->setPath( $arrScriptPaths );

        $strLayoutBaseName = 'layout';
        if ( isset( $arrParams['layout'] ) && $arrParams['layout'] != '' ) {
            $strLayoutBaseName .= '-'.$arrParams['layout'];
        }

        $arrLayoutPaths = array();
        foreach ( $arrThemes as $strThemeName ) {
            $arrLayoutPaths []= implode( '/', array(
                CWA_APPLICATION_DIR,
                'theme',
                $strThemeName,
                $arrParams['section'],
                $strLayoutBaseName.'.'.$this->_objCurrentController->view->getLayout()->getExtension()
            ));
        }
        
        // die( 'PATH' . $strLayoutPath );
        $strCharset = 'utf-8';
        // detect charset from application config
        if ( $this->getConfig()->charset != '' ) {
            $strCharset = $this->getConfig()->charset;
        }
        // detect charset from controller parameter
        if ( isset( $arrParams['charset'] ) && $arrParams['charset'] != '' ) {
            $strCharset = $arrParams['charset'];
        }
        $this->_objCurrentController->view->charset = $strCharset;

        if ( isset( $arrParams['nolayout'] ) ) {
            $this->_objCurrentController->view->getLayout()->disableLayout();
        }
        if ( isset( $arrParams['format'] ) ) {
            // TODO: think of layouts in different contexts
            $this->_objCurrentController->view->getLayout()->disableLayout();

            switch ( $arrParams['format' ] ) {
                case 'xml' :
                    header( 'Content-Type: text/xml; charset='.$strCharset );
                    break;
                case 'csv' :
                    header( 'Content-Type: text/plain; charset='.$strCharset );
                    break;
            }


        } else  if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) &&
                     $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {

            $this->_objCurrentController->view->getLayout()->disableLayout();
            
        } else {
            // if we dont have format, we can set up headers:
            if ( PHP_SAPI != "cli" && !headers_sent() )
                header( 'Content-Type: text/html; charset='.$strCharset );
        }

        if ( isset( $arrParams['noheaders'] ) ) {
            $this->_objCurrentController->view->noheaders = $arrParams['noheaders'];
        }   

        $this->_objCurrentController->view->getLayout()->setPath( $arrLayoutPaths );
        if ( isset( $arrParams['output'] ) && $arrParams['output'] != '' ) { 
            $strResult = $this->_objCurrentController->view->getLayout()->render();
            
            // all output folders must be listed in a config section
            $strFiltered = $arrParams['output'];
            if ( is_object( $this->getConfig()->output ) ) {
                $arrOutput = $this->getConfig()->output->toArray();
                if ( ! in_array( $strFiltered, $arrOutput )) {
                    throw new App_Exception( 'No such output entry in a config' );
                }
            } else {
                throw new App_Exception( 'No output entry in a config' );
            }
            
            $file = new Sys_File( CWA_APPLICATION_DIR . $strFiltered );
            if ( $strResult != '' ) $file->save( $strResult );

            return ""; 
        } else {
            return $this->_objCurrentController->view->getLayout()->render();
        }   
    }
    public function runCli( $arrArguments )
    {
        $arrControllerParams = array(
            'action' => 'index',
        );
        unset( $arrArguments[0] );

        if ( $this->getConfig()->action_log ) {
            // if action log was enabled, we should write the actions
            $strLine = date('Y-m-d H:i:s').' ['.$this->getInstanceId().'] RUNNING CLI '.json_encode( $arrArguments );

            $strLogFile = new Sys_File( $this->getConfig()->action_log );
            $strLogFile->append( "\n".$strLine. "\n" );
        }


        $strKey = ''; 
        foreach( $arrArguments as $strParamName ) {
            if ( substr( $strParamName, 0, 1 )  == '-' ) {
                $strKey = substr( $strParamName, 1 );
            } else if ( $strKey != '' ) {
                $arrControllerParams[ $strKey ] = $strParamName;
                $strKey = '';
            }
        }
        if ( isset( $arrControllerParams[ 'env'] ) ) {
            Sys_Global::set( 'Environment', $arrControllerParams[ 'env' ] );
        }

        if ( !Sys_Global::isRegistered( 'Environment') )
            throw new App_Exception( 'Running command line interface without environment defined' );


        $arrControllerParams['section'] = 'cli';
        if ( !isset( $arrControllerParams[ 'module' ] ) && isset( $arrControllerParams[ 'm' ] ) ) {
            $arrControllerParams['module'] = $arrControllerParams['m'];
            unset( $arrControllerParams['m'] );
        }
        if ( !isset( $arrControllerParams[ 'controller' ] ) && isset( $arrControllerParams[ 'c' ] ) ) {
            $arrControllerParams['controller'] = $arrControllerParams['c'];
            unset( $arrControllerParams['c'] );
        }
        if ( !isset( $arrControllerParams[ 'action' ] ) && isset( $arrControllerParams[ 'a' ] ) ) {
            $arrControllerParams['action'] = $arrControllerParams['a'];
            unset( $arrControllerParams['a'] );
        }
        if ( isset( $arrControllerParams[ 'u' ] ) )  {

            $arrUrlParams = explode( '/', $arrControllerParams[ 'u' ] );
            unset( $arrControllerParams[ 'u' ] );

            if ( $arrUrlParams[0] == '' ) unset( $arrUrlParams[0] );

            $strKey = '';
            $nParamIndex = 0;
            if ( count( $arrUrlParams ) > 0 )
            foreach ( $arrUrlParams as $nKey => $strParam ) {
                
                if ( $nParamIndex == 0 ) {
                    $arrControllerParams[ 'module' ] = $strParam;
                    $nParamIndex++;
                } else if ( $nParamIndex == 1 ) {
                    $arrControllerParams[ 'controller' ] = $strParam;
                    $nParamIndex++;
                } else if ( $nParamIndex == 2 ) {
                    $arrControllerParams[ 'action' ] = $strParam;
                    $nParamIndex++;
                } else {
                    if ( $strKey == '' ) {
                        $strKey = $strParam;
                    } else {
                        $arrControllerParams[ $strKey ] = $strParam;
                        $strKey = '';
                    }
                    $nParamIndex++;
                }
            }
        }

        $arrControllerParams['nolayout'] = 1;

        if (  ! isset( $arrControllerParams['module'] ) 
           || ! isset( $arrControllerParams['controller'] ) ) {
            Sys_Io::out( 'ERROR: module/controller was not specified' );
        }

        try {
            echo $this->runAction( 
                $arrControllerParams['action'],
                $arrControllerParams['controller'],
                $arrControllerParams['module'],
                $arrControllerParams );
        } catch ( App_Exception_PageNotFound $exception ) {
            echo $this->runControllerAction(  'page-not-found', $this->_strDefaultController, $arrControllerParams );
        } catch ( App_Exception_AccessDenied $exception ) {
            echo $this->runControllerAction(  'access-denied', $this->_strDefaultController, $arrControllerParams );
        } catch ( App_Exception_ServerError $exception ) {
            echo $this->runControllerAction(  'server-error', $this->_strDefaultController, $arrControllerParams );
        } catch ( Exception $exception ) {

            $ex = new App_Exception_Handler();
            $ex->process( $exception ); // - save into logs and mail if configured
            die( "\n". 'EXCEPTION: ' . $exception->getMessage(). "\n". App_Exception_Handler::backTraceString( $exception->getTrace() ));
        }
        
    }
    public function runUrl( $strUrl )
    {
        self::$strFullUrl = $strUrl;
        $strUrl = preg_replace( '/\?.*$/', '', $strUrl );
        self::$strUrl = $strUrl;

        $this->timeframe = new Sys_Timeframe();

        if ( $this->getConfig()->action_log ) {
            // if action log was enabled, we should write the actions
            $strLine =  date('Y-m-d H:i:s').' ['.$this->getInstanceId().'] RUNNING URL '.$strUrl;
            $strLogFile = new Sys_File( $this->getConfig()->action_log );
            $strLogFile->append( "\n".$strLine. "\n" );
        }

        
        
        $strBase = $this->getConfig()->base;
        if ( $strBase != '' && $strBase != '/' ) {
            if ( substr( $strUrl, 0, strlen( $strBase ) ) == $strBase ) {
                $strUrl = substr( $strUrl, strlen( $strBase ));
                if ( substr( $strUrl, 0, 1 ) != '/' ) $strUrl = '/'.$strUrl;
            }
        }
        $this->_arrUrlParams = explode( '/', $strUrl );

        if ( $this->getConfig()->session ) {
            if ($this->getConfig()->session->options ) {

                App_Session::setOptions( $this->getConfig()
                        ->session->options->toArray());
            }
            App_Session::start();
        }

        $strControllerClass = '';
        $strControllerAction = '';
        $arrControllerParams = array(
            'section' => $this->getConfig()->default_section,
            'module' => '',
            'controller' => '',
            'action' => '',
        );
        
        if ( $arrControllerParams[ 'section' ] == '' )
            $arrControllerParams[ 'section' ] = 'frontend';

        // Sys_Debug::dump( $arrControllerParams );

        try {
            $bDetected = 0;

            $confException = $this->getConfig()->exceptions;
            if ( is_object( $confException ) && $confException->on_errors ) {
                set_error_handler( "exceptionErrorHandler" );
                register_shutdown_function( 'shutdownErrorHandler' );
            }

            foreach ( $this->getRoutes() as $strRouteIndex => $arrRouteProperties ) {

                $strRouteType = isset( $arrRouteProperties['type'] ) ? $arrRouteProperties['type'] : 'static';
                if ( !isset($arrRouteProperties['route']) )
                    throw new Exception( '"route" param is not defined for '.$strRouteIndex.' route' );
                if ( !isset($arrRouteProperties['defaults']) || !is_array($arrRouteProperties['defaults']))
                    throw new Exception( '"defaults" param is not defined for '.$strRouteIndex.' route' );
                $strRoute     =  $arrRouteProperties['route'];
                $arrDefaults  =  $arrRouteProperties['defaults'];
                $arrExclude  =  isset( $arrRouteProperties['exclude'] ) ? $arrRouteProperties['exclude'] : array();
                if ( is_string( $arrExclude )) $arrExclude = array( $arrExclude );
                $arrMatches   = array();

                if ( Sys_Mode::is('dispatcher') ) {
                    Sys_Io::out( '@'.$strRoute. '@i'. $strUrl );
                }
                
                $bShouldBeChecking = true;
                foreach ( $arrExclude as $strExcludePattern ) {
                    if ( preg_match( '@'.$strExcludePattern.'@i', $strUrl, $arrMatches ) ) {
                        $bShouldBeChecking = false;
                    }
                }

                switch ( $strRouteType ) {
                    case 'regex'  :

                        if ( $bShouldBeChecking && preg_match( '@'.$strRoute.'@i', $strUrl, $arrMatches ) ) {

                            if ( Sys_Mode::is('dispatcher') ) {
                                print_r( $arrMatches );die;
                            }
                            
                            $arrMap       = isset( $arrRouteProperties['map'] ) ? $arrRouteProperties['map'] : array();
                            $strControllerClass = Sys_String::toCamelCase( $arrDefaults['module'] )
                                    .'_'.Sys_String::toCamelCase( $arrDefaults['controller'] ).'Ctrl';
                            $strControllerAction = $arrDefaults['action'];
                            $arrControllerParams = $arrDefaults;
                            foreach( $arrMap as $nIndex => $strParamName ) {
                                $arrControllerParams[ $strParamName ] = $arrMatches[ $nIndex ];
                            }
                            $bDetected = 1;
                        }
                        break;
                    case 'static' : default:
                        if ( $bShouldBeChecking && $strUrl == $strRoute ) {
                            $strControllerClass = Sys_String::toCamelCase( $arrDefaults['module'] )
                                    .'_'.Sys_String::toCamelCase( $arrDefaults['controller'] ).'Ctrl';
                            $strControllerAction = $arrDefaults['action'];
                            $arrControllerParams = $arrDefaults;
                            $bDetected = 1;
                        }

                        // die( 'STATIC ROUTE '.$strControllerClass.' :: '.$strControllerAction );
                        // create new Controller from defaults
                        break;
                }
                if ( $bDetected ) break;
            }

            if ( !$bDetected ) {
                // TODO: split URL into parameters and define Module/Controller/Action
                $arrUrlParams = explode( '/', $strUrl );
                if ( $arrUrlParams[0] == '' ) { 
                    unset( $arrUrlParams[0] );
                    // array_shift($arrUrlParams);
                }
                // check first param to be a valid section
                $strKey = '';
                $nParamIndex = 0;
                $bSectionDetected = 0;
                if ( count( $arrUrlParams ) > 0 )
                foreach ( $arrUrlParams as $nKey => $strParam ) {
                    $strParam = rawurldecode( $strParam );
                    
                    if ( $nParamIndex == 0 && !$bSectionDetected && $this->_getSectionFromSlug( $strParam )) {
                        // case when section is given in URL
                        $arrControllerParams[ 'section' ] = $this->_getSectionFromSlug( $strParam );
                        $bSectionDetected = 1;
                    } else if ( $nParamIndex == 0 ) {
                        $arrControllerParams[ 'module' ] = $strParam;
                        $nParamIndex++;
                    } else if ( $nParamIndex == 1 ) {
                        $arrControllerParams[ 'controller' ] = $strParam;
                        $nParamIndex++;
                    } else if ( $nParamIndex == 2 ) {
                        $arrControllerParams[ 'action' ] = $strParam;
                        $nParamIndex++;
                    } else {

                        if ( $strKey == '' ) {
                            $strKey = $strParam;
                        } else {
                            $arrControllerParams[ $strKey ] = $strParam;
                            $strKey = '';
                        }
                        $nParamIndex++;
                    }
                }
            }

            foreach( $_GET as $key => $param ) {
                $arrControllerParams[ $key ] = $param;
            }
            foreach( $_POST as $key => $param ) {
                $arrControllerParams[ $key ] = $param;
            }


            if ( isset( $arrControllerParams['module'] ) &&
                    isset( $arrControllerParams['controller'] ) &&
                    isset( $arrControllerParams['action'] ) ) {
                $strControllerClass = Sys_String::toCamelCase( $arrControllerParams['module'] )
                    .'_'.Sys_String::toCamelCase( $arrControllerParams['controller'] ).'Ctrl';
                $strControllerAction = $arrControllerParams['action'];
            }

            if ( !isset( $arrControllerParams[ 'section' ] ) || $arrControllerParams[ 'section' ] == '' ) {
                
                $arrControllerParams[ 'section' ] = $this->getConfig()->default_section;
                if ( $arrControllerParams[ 'section' ] == '' )
                    $arrControllerParams[ 'section' ] = 'frontend';
            }

            $arrSections = $this->getConfig()->sections->toArray();
            if ( !isset( $arrSections[ $arrControllerParams[ 'section' ] ]  )) {
                throw new App_Exception( 'No valid section for this call' );
            }
            // Sys_Debug::dumpDie( $arrControllerParams );
            
            if ( $strControllerClass == '' )
                throw new App_Exception_PageNotFound( 'No controller for this call' );
            if ( $strControllerAction == '' )
                throw new App_Exception_PageNotFound( 'No controller action for this call' );

             // echo 'DEBUG ACTION:'. $strControllerAction .', CLASS '. $strControllerClass . "\n";

            // check security
            App_Permission::check( $arrControllerParams );
            // check CSRF-token presence( where it is configured )
            App_Permission::checkToken( $arrControllerParams );
            
            echo $this->runControllerAction(  $strControllerAction, $strControllerClass, $arrControllerParams );

        } catch ( App_Exception_PageNotFound $exception ) {

            echo $this->runControllerAction(  'page-not-found', $this->_strDefaultController, $arrControllerParams );

        } catch ( App_Exception_AccessDenied $exception ) {

            echo $this->runControllerAction(  'access-denied', $this->_strDefaultController, $arrControllerParams );

        } catch ( App_Exception_ServerError $exception ) {

            echo $this->runControllerAction(  'server-error', $this->_strDefaultController, $arrControllerParams );
            
        } catch ( Exception $exception ) {

            $ex = new App_Exception_Handler();
            $ex->process( $exception ); // - save into logs and mail if configured
            
            $confException = $this->getConfig()->exceptions;
            if  (is_object(  $confException ) ) {
                
                if ( $confException->throw ) {
                    // displaying exception for debug simplicity
                    if ( $confException->html && isset($_SERVER['HTTP_HOST'] ) ) echo '<pre>';
                    throw $exception;
                } else {
                    // hiding exception, displaying Server Error Page
                    echo $this->runControllerAction(  'server-error',
                            $this->_strDefaultController, array_merge( $arrControllerParams, array('exception' => $exception) ) );
                }
            } else {
                    echo $this->runControllerAction(  'server-error',
                    $this->_strDefaultController, array_merge( $arrControllerParams, array('exception' => $exception) ) );
            }
        }

        $bAutoAppend = true;
        if ( is_object( $this->_objCurrentController->view ) 
            && ! $this->_objCurrentController->view->canAutoAppend() ) {
            $bAutoAppend = false;
        }
        
        $strBottom = '';
        if ( !isset( $arrControllerParams['format'] )  && !isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {
            if ( App_Application::getInstance()->getConfig()->display_time_generated ) {
                if ( !isset( $arrControllerParams['format'] )  && !isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {
                    $strBottom .= ' Generated in '. $this->timeframe->getCurrent().'. ';
                }
            }
            if ( App_Application::getInstance()->getConfig()->display_build ) {
                $strVersionFile = CWA_APPLICATION_DIR.'/cdn/version.txt';
                if ( file_exists( $strVersionFile ) ) {
                    $strBottom .= ' Build #'. file_get_contents( $strVersionFile ) .' from '.date('m/d/Y H:i', filemtime( $strVersionFile ) ).'';
                }
            }
        }
        if ( $bAutoAppend && $strBottom != '' ) {
            echo '<div style="margin-top:10px;font-size:10px;text-align:center">'.$strBottom.'</div>';
        }
    }
    /**
     * Get backtrace string
     * @param array $arrTraceLines
     * @return string
     */
    public function backTraceString( $arrTraceLines )
    {
        return App_Exception_Handler::backTraceString( $arrTraceLines );
    }
}
