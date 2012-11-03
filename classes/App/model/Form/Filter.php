<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_Form_Filter extends App_Form
{
    public function init()
    {
        parent::init();
        $this->createElements();
        // $this->setAttrib('id', get_class($this));
        $this->createDecorators();
    }

    public function allowFiltering( $arrFields )
    {
        if ( !is_array( $arrFields ) )
            throw new App_Exception ( 'Array of filtering fields should be defined' );

        foreach ( $arrFields as $strField ) {
            $element = new App_Form_Element( $strField, 'hidden');
            $this->addElement( $element );
        }
    }
    
}