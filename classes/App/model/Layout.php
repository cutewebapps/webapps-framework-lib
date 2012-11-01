<?php

class App_Layout
{
    /** @var App_Layout */
    protected static $_instance = null;

    protected $view = null;

    protected $_bEnabled = 1;

    protected $_strPath = '';

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

    public function disableLayout()
    {
        $this->_bEnabled = 0;
    }

    public function getView()
    {
        return $this->view;
    }

    public function isEnabled()
    {
        return $this->_bEnabled;
    }

    public function __construct( App_View $view )
    {
        $this->_broker = new App_ViewHelper_Broker();
        $this->view    = $view;
    }
/*
    public function action( $strAction, $strController, $strModule, $arrParams = array() )
    {
        $dispatch = new App_Dispatcher();
        return $dispatch->runAction( $strAction, $strController, $strModule, $arrParams );
    }
*/
    /** @return void */
    public function setPath( $strPath )
    {
        $this->_strPath = $strPath;
    }
    /** @return string */
    public function getPath()
    {
        return $this->_strPath;
    }
    
    /* view must be initialized */
    public function render()
    {
        $this->view->render();
        if ( $this->isEnabled() ) {
            ob_start();

            $arrPaths = $this->getPath();
            if ( !is_array( $arrPaths )) $arrPaths = array( $arrPaths );

            $bSuccess = false;
            foreach ( $arrPaths as $strPath ) {
                if ( file_exists( $strPath ) ) {
                    require $strPath; $bSuccess = true; break;
                }
            }
            if ( !$bSuccess ) {
                throw new App_Exception( 'Layout was not found at '.implode( ",", $arrPaths ));
            }


            $strContents = ob_get_contents();
            ob_end_clean();
            return $strContents;
        } else {
            return $this->view->getContents();
        }
        
    }

    public function broker( $strNamespace = 'App' )
    {
        return $this->view->broker( $strNamespace );
    }

    public function __get( $strName )
    {
        return $this->view->__get( $strName );
    }

    public function __call( $name, $arguments )
    {
        return call_user_func_array( array($this->view, $name), $arguments );
    }
    
    public function getExtension()
    {
        return 'phtml';
    }
}
