<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class Lang_Hash
{
    /**
    * @var Lang_String_List
    */
    public static $lstCachedComponents = array();

    /**
    * @param string $strKey
    * @param string $strDefault
    * @return string
    */
    public static function get( $strKey, $strLang = '', $strComponent = ''  )
    {
        if ( $strLang == '' ) {
            if ( App_Application::getInstance()->getConfig()->lang->detect_from_browser ) {
                $arrLanguagesDetectedInBrowser = App_Application::getInstance()->getConfig()->lang->detect_from_browser->toArray();
                if ( isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"] )) {
                    $strBrowserLang = substr( $_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2 );
                    if ( in_array( $strBrowserLang, $arrLanguagesDetectedInBrowser ) ) {
                        $strLang = $strBrowserLang;
                    }
                }
            }
            // Sys_Debug::dump( $arrLanguagesDetectedInBrowser );
            if ( $strLang == '' ) {
                $strLang = App_Application::getInstance()->getConfig()->lang->default_lang;
            }
        }
       
        if ( $strComponent == '' )
            $strComponent = App_Application::getInstance()->getConfig()->lang->default_component;

        $strSource = App_Application::getInstance()->getConfig()->lang->source;
        if ( $strSource == '' || $strSource == 'db' ) {

            $tbl = Lang_String::Table();
            $x = '';
            if ( !isset( self::$lstCachedComponents[ $strComponent ] ) ) {
                
                $selectComponent = $tbl->select()
                        ->where( 'langs_component = ?', $strComponent )
                        ->where( 'langs_lang = ?', $strLang );
                $listRows = $tbl->fetchAll( $selectComponent );

                self::$lstCachedComponents[ $strComponent ] = array();
                /** @var Lang_String $objString */
                foreach ( $listRows as $objString) {
                    self::$lstCachedComponents[ $strComponent ][ $objString->getOriginal() ] = $objString->getTranslation();
                }
            }
        } else if ( $strSource == 'json' ) {

            $strFileToRead = App_Application::getInstance()->getConfig()->lang->read;
            if ( !isset( self::$lstCachedComponents[ $strComponent ] ) ) {

                 //Sys_Debug::dump( (array)json_decode( file_get_contents( $strFileToRead ) ) );
                 self::$lstCachedComponents[ $strComponent ] = (array)json_decode( file_get_contents( $strFileToRead ));
            }
            
        } else if ( $strSource == 'csv' ) {

            $strFileToRead = App_Application::getInstance()->getConfig()->lang->read;
            if ( !isset( self::$lstCachedComponents[ $strComponent ] ) ) {
                // fgetcsv($handle, $length)
                self::$lstCachedComponents[ $strComponent ] = array();

                if (($handle = fopen( $strFileToRead, "r" )) !== false) {
                    while (($data = fgetcsv($handle, 4096, ',' )) !== false ) {
                        if ( isset( $data[ 0 ] )  && isset( $data[ 1 ] ) ) {
                            self::$lstCachedComponents[ $strComponent ][  $data[0]  ] = $data[1];
                        }
                    }
                    fclose($handle);
                }
            }

        }
        
        if ( isset( self::$lstCachedComponents[ $strComponent ] [ $strKey ] ) &&
                    self::$lstCachedComponents[ $strComponent ] [ $strKey ] != '')
                return self::$lstCachedComponents [ $strComponent ][ $strKey ];
        
        return $strKey;
    }

    /**
     * @param string $strKey
     * @param string $strValue
     * @param string $strLang
     * @param string $strComponent
     *
     */
    public static function set($strKey, $strValue, $strLang, $strComponent ) {

        $strSource = App_Application::getInstance()->getConfig()->lang->source;
        if ( $strSource == '' || $strSource == 'db' ) {
            $tbl = Lang_String::Table();
            $select = $tbl->select()
                ->where('langs_original = ?', $strKey)
                ->where('langs_component = ?', $strComponent)
                ->where('langs_lang = ?', $strLang );
        
            $objRow = $tbl->fetchRow($select);
            if (!is_object($objRow)) {
                $objRow = $tbl->createRow();
                $objRow->langs_original = $strKey;
            }
            $objRow->langs_lang        = $strLang;
            $objRow->langs_component   = $strComponent;
            $objRow->langs_translation = $strValue;
            $objRow->save();
        }
        
        self::$lstCachedComponents[ $strComponent ] [$strKey] = $strValue;
    }

    public static function scan( $strPath, $strLang, $strComponent = '' )
    {
        if ( $strLang == '' )
            $strLang = App_Application::getInstance()->getConfig()->lang->default_lang;
        
        if ( $strComponent == '' )
            $strComponent = App_Application::getInstance()->getConfig()->lang->default_component;

        $arrPatterns = array( '$this->translate', '$this->view->translate', 'Lang_Hash::get' );
        $objDir = new Sys_Dir( $strPath );
        foreach ( $objDir->getFiles() as $strFile ) {
            $strContents = file_get_contents( $strFile );
            foreach ( $arrPatterns as $strFunc ) {
                $arrMatchFunc = Sys_String::xAll( '@'.preg_quote( $strFunc ).'\((.+)\)@simU', $strContents );
                foreach( $arrMatchFunc as $strFunc ) if ( substr( trim( $strFunc ), 0, 1 ) != '$' ) {
                    $strFunc = preg_replace( '@(\'|")$@', '', trim( $strFunc ) );
                    $strFunc = preg_replace( '@^(\'|")@', '', $strFunc );
                    
                    if ( self::get( $strFunc, $strLang ) == $strFunc ) {
                        self::set( $strFunc, '', $strLang, $strComponent );
                    }
                }
            }
        }

        //$tblString = Lang_String::Table();
        //$lstErrors = $tblString->fetchAll( $tblString->select()->where( 'langs_translation LIKE ? ', '%//%' ) );
        //foreach ( $lstErrors as $objString ) $objString->delete();
    }
}