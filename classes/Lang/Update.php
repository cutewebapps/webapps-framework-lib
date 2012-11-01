<?php

class Lang_Update extends App_Update
{
    const VERSION = '0.1.0';
    public static function getClassName() { return 'Lang_Update'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    
    public function update()
    {
        if ( $this->isVersionBelow( '0.1.0' ) ) {
            $this->_install();
        }
        $this->save( self::VERSION );
    }
    /**
     * @return array
     */
    public static function getTables()
    {
        return array(
            Lang_String::TableName(),
        );
    }

    protected function _install()
    {
        if (!$this->getDbAdapterRead()->hasTable('lang_string')) {
            Sys_Io::out('Creating Translation Table');

            $this->getDbAdapterWrite()->addTableSql('lang_string', '
                  `langs_id`            int(11)       NOT NULL AUTO_INCREMENT,
                  `langs_lang`          CHAR(2)       NOT NULL DEFAULT \'en\',
                  `langs_component`     CHAR(30)      NOT NULL DEFAULT \'\',
                  `langs_original`      VARCHAR(255)  NOT NULL,
                  `langs_translation`   TEXT          NOT NULL,
                  INDEX `i_key` (`langs_lang`,`langs_component`,`langs_original`) ',
                  'langs_id' );
        }
    }

}
