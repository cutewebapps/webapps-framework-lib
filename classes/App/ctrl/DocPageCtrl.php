<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 * 
 */
class App_DocPageCtrl extends App_DbTableCtrl
{
    /**
     * rendering the page - static 
     */
    public function getAction()
    {
        $strPage = trim( $this->getParam( 'page' ) );
        if ( $strPage == '' )
            throw new App_DocPage_Exception( "Page name should be provided" );
        
        $this->view->object = null;
        $this->view->strPage = $strPage; 

        // define whether it is a 
        // - index page ( generated and saved )
        // - static page ( written manually and saved )
        // - model class page ( generating on fly from classes source )
        // - controller class page ( generating on fly from classes source )
        $objConfig = App_Application::getInstance()->getConfig()->documentation;
        if ( !is_object( $objConfig ) )
            throw new App_DocPage_Exception( "Documentation section was not configured for this environment" );
            
        // disable any auto-appending characters
        $this->view->enableAutoAppend( false );
        
        if ( $objConfig->indexpath  ) {
            $strPath = $objConfig->indexpath.'/'.$strPage.'.php';
            
            if ( file_exists( $strPath ) ) {
                $this->view->object = new App_DocPage();
                $this->view->object->render( $strPage, $strPath );
                return;
            }
        }
        if ( $objConfig->articlepath ) {
            $strPath = $objConfig->articlepath.'/'.$strPage.'.php';
            
            if ( file_exists( $strPath ) ) {
                $this->view->object = new App_DocPage();
                $this->view->object->render( $strPage, $strPath );
                return;
            }
        }
        if ( class_exists( $strPage ) ) {
            $strPath = CWA_DIR_CLASSES . '/' . implode('/', getClassParts( $strPage ) ) . '.php';
            if ( file_exists( $strPath ) ) {
                $this->view->object = new App_DocPage();
                $this->view->object->renderClass( $strPage, $strPath );
                return;
            }
        }
        
        throw new App_Exception_PageNotFound();
        
    }
    
    public function indexAction()
    {
        
    }
}