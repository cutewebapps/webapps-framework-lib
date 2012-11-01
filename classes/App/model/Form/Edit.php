<?php

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
        return implode( '_', $classes );
    }
}