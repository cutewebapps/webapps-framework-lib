<?php

class DBx_Table_Rowset extends DBx_Table_Rowset_Abstract
{
    public function getIds($strColumnName = null)
    {
        $arrValues = array();
        $strColumnName = (is_null($strColumnName)) ? $this->_table->getIdentityName() : $strColumnName;
        foreach ($this as $objRow){
            $arrValues[] = $objRow->$strColumnName;
        }

        return $arrValues;
    }

    /**
     * Check whether the list of objects contain the record with given values
     * @param array $arrParams
     * @return boolean
     */
    public function hasRecord( $arrParams = array() )
    {
        foreach ( $this as $objRow ) {
            $bMatch = true;
            foreach ( $arrParams as $strField => $strValue ) {
                if ( $objRow->$strField != $strValue  ) {
                    $bMatch = false; break;
                }
            }
            if ( $bMatch ) return true;
        }
        return false;
    }

    /**
     *
     * @param array $arrParams
     * @return boolean
     */
    public function hasNoRecord( $arrParams = array() )
    {
        foreach ( $this as $objRow ) {
            $bMatch = true;
            foreach ( $arrParams as $strField => $strValue ) {
                if ( $objRow->$strField != $strValue  ) {
                    $bMatch = false; break;
                }
            }
            if ( $bMatch ) return false;
        }
        return true;
    }


}
