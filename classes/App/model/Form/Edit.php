<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 *
 * Licensed under GPL, Free for usage and redistribution.
 */

class App_Form_Edit extends App_Form
{
    public function init()
    {
        parent::init();
        $this->createElements();
        // $this->addElement('submit', 'submit', array('label' => 'Add ' . $this->getObjectName()));
        $this->setAttrib('id', get_class($this));
        $this->createDecorators();
    }

    public function createElements()
    {
    }

    public function allowEditing( $arrFields )
    {
        if ( !is_array( $arrFields ) )
            throw new App_Exception ( 'Array of edited fields should be defined' );

        foreach ( $arrFields as $strField ) {
            $element = new App_Form_Element( $strField, 'hidden');
            $this->addElement( $element );
        }
    }

    public function setDefaults($defaults)
    {
        if (isset($defaults['ID']) && $defaults['ID']) {
            $element = $this->getElement('submit');
            if ( is_object( $element ))  {
                $element->setLabel('Edit ' . $this->getObjectName());
            }
        }
        return parent::setDefaults($defaults);
    }

    public function getObjectName()
    {
        $classes = explode('_', get_class( $this ));
        array_pop( $classes );
        array_pop( $classes );
        unset( $classes[0] ); // the first word is usually a namespace
        $strCamelCase = implode( ' ', $classes );

        $o = '';
        for($i = 0; $i < strlen ( $strCamelCase ); $i ++) {
            $ch = substr ( $strCamelCase, $i, 1 );
            if ($i > 0 && $ch == strtoupper ( $ch ) && ! preg_match ( '/^\d+$/', $ch ))  { $o .= ' '; }
            $o .= $ch;
        }
        return $o;
    }
}
