<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

class Lang_String_List extends DBx_Table_Rowset
{
}

class Lang_String_Form_Filter extends App_Form_Filter
{
    public function createElements()
    {
        $elemLang = new App_Form_Element( 'langs_lang', 'text' );
        $this->addElement( $elemLang );

        $elemComponent = new App_Form_Element( 'langs_component', 'text' );
        $this->addElement( $elemComponent );

        $elemOriginal = new App_Form_Element( 'langs_original', 'text' );
        $this->addElement( $elemOriginal );

        $elemValue = new App_Form_Element( 'langs_translation', 'text' );
        $this->addElement( $elemValue );
    }
}

class Lang_String_Form_Edit extends App_Form_Edit
{
    public function createElements()
    {
        $elemValue = new App_Form_Element( 'langs_translation', 'text' );
        $this->addElement( $elemValue );
    }
}

class Lang_String_Table extends DBx_Table
{
/**
 * database table name
 */
    protected $_name='lang_string';
/**
 * database table primary key
 */
    protected $_primary='langs_id';
}

class Lang_String extends DBx_Table_Row
{
    public static function getClassName() { return 'Lang_String'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    /** @return string(2) */
    public function getLang() { return $this->langs_lang; }
    /** @return string */
    public function getComponent() { return $this->langs_component; }
    /** @return string */
    public function getOriginal() { return $this->langs_original; }
    /** @return string */
    public function getTranslation() { return $this->langs_translation; }
}