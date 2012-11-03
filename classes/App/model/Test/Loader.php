<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_Test_Loader
{

    public function runTestCase( $strFile )
    {
        $strClassName = preg_replace( '@\.(.*)$@', '', basename( $strFile ) );
        
        require_once( $strFile );
        $testCase = new $strClassName();
        $arrMethods = get_class_methods( $testCase );
        $testCase->setVerbose( false );
        $testCase->setUp();

        foreach ( $arrMethods as $strMethod ) {
            
            $testCase->setVerbose( false );
            if ( substr( $strMethod, 0, 4 ) == 'test' ) {

                $strResult = '[OK]';
                try {
                    $testCase->$strMethod();
                    Sys_Io::out( ' - '.$strClassName.'::'.$strMethod.' '.$strResult );
                } catch ( Exception $e ) {
                    
                    $arrTrace = $e->getTrace();
                    $strResult = '[FAIL] '.basename($arrTrace[0]['file']).':'.$arrTrace[0]['line'].' "'.$e->getMessage().'"';
                    Sys_Io::out( ' - '.$strClassName.'::'.$strMethod.' '.$strResult, '', array( 'color' => 'red' ) );
                    
                    if ( $testCase->getTraceback() ) {
                        
                        if ( PHP_SAPI != 'cli') 
                            echo '<pre>'.$testCase->getDispatcher()->backTraceString( $e->getTrace() ).'</pre>';
                        else
                            echo $testCase->getDispatcher()->backTraceString( $e->getTrace() );
                    }
                }
               
            }
        }
        $testCase->tierDown();
        
    }


    public function runSingle( $strClassName )
    {
        if ( ! Sys_String::x( '@^([a-z\-0-9]+)$@i', $strClassName ) )
                throw new App_Exception( 'Invalid or insecure test class name' ) ;
        
        $strClassName = str_replace( '-', '/', $strClassName );
        $file = new Sys_Dir( CWA_APPLICATION_DIR.'/test/'.$strClassName.'.php' );
        
        if ( ! file_exists( $file->getName() ))
              throw new App_Exception( 'Test class not found: '. $file->getName() );

        $this->runTestCase( $file->getName() );
    }

    public function run( $arrGroups = array() )
    {
        if ( !is_array( $arrGroups )) $arrGroups = array( $arrGroups );
        
        $config = App_Application::getInstance()->getConfig()->test;
        App_Application::getInstance()->getConfig()->action_log = '';

        if ( !is_object( $config ) || !is_object( $config->group ))
            throw new App_Exception( 'Test groups are not configured' );
            
        foreach ( $arrGroups as $strGroup ) {
            
            if ( $config->group->$strGroup ) {
                $strPath = $config->group->$strGroup;

                $dir = new Sys_Dir( CWA_APPLICATION_DIR.'/test/'.$strPath );
                if ( $dir->exists() ) {
                    $arrFiles = $dir->getFiles( '/\.phpt?$/' );
                    
                    foreach ( $arrFiles as $strTestFile ) {
                        $this->runTestCase( $strTestFile );
                    }
                    
                } else {
                    Sys_Io::out( '[ERROR] GROUP PATH NOT FOUND ' .$dir->getName(), '', array( 'color' => 'red' ) );
                }
            } else {
                Sys_Io::out( '[ERROR] GROUP NOT FOUND ' .$strGroup, '', array( 'color' => 'red' ) );
            }
        }
        
    }
}