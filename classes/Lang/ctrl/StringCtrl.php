<?php

class Lang_StringCtrl extends App_DbTableCtrl
{

    public function scanAction()
    {
        $arrFolders = App_Application::getInstance()->getConfig()->lang->folders->toArray();
        foreach ( $arrFolders as $strFolder ) {
            Lang_Hash::scan( $strFolder, $this->_getParam( 'current_lang', '' ) );
        }
        
        $strComponent = App_Application::getInstance()->getConfig()->lang->default_component;
        $strSource = App_Application::getInstance()->getConfig()->lang->source;
        if ( $strSource == 'json')  {

            $strWrite = App_Application::getInstance()->getConfig()->lang->write;
            if ( $strWrite == '' ) throw new App_Exception( 'File output should be provided' );
            

            // Sys_Debug::dump ( Lang_Hash::$lstCachedComponents );
            // die ( 'scanning into '. $strWrite.' '.count( Lang_Hash::$lstCachedComponents[$strComponent] ) );
            
            $fileJson = new Sys_File( $strWrite );
            $fileJson->save( str_replace( ',', ",\n",
                    json_encode( Lang_Hash::$lstCachedComponents[$strComponent] ) ));
            
        } else if ( $strSource == 'csv')  {
            
            $strWrite = App_Application::getInstance()->getConfig()->lang->write;
            if ( $strWrite == '' ) throw new App_Exception( 'File output should be provided' );

            $fileCsv = new Sys_File( $strWrite );
            $arrLines = array();
            foreach ( Lang_Hash::$lstCachedComponents[$strComponent] as $strKey => $strValue ) {
                $arrLines []   = '"' . str_replace( '"', '""', $strKey )
                             . '","' . str_replace( '"', '""', $strValue ).'"';
            }
            $fileCsv->save( implode ("\n", $arrLines ) );
        }


    }

    public function getlistAction()
    {
        if ( $this->_hasParam( 'rescan' ) ) {
            if ( is_object( App_Application::getInstance()->getConfig()->lang )
                    && App_Application::getInstance()->getConfig()->lang->folders ) {

                $this->scanAction();
                header( 'Location: '.preg_replace( '/\?.*$/', '', $_SERVER['REQUEST_URI'] ) );
                die;
            }
        }

        $this->view->current_lang =  $this->_getParam( 'langs_lang' );
        parent::getlistAction();
    }
}