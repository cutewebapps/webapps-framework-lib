<?php


class App_Mail_List
{
    protected $arrReceipients = array();

    /**
     * @param string|array $strEmails
     * @return App_Mail_List
     */
    public function add( $strEmails )
    {
        if ( is_array( $strEmails )) {
            foreach( $strEmails as $sEmail ) {
                $this->add( $sEmail );
            }
        } else if ( trim( $strEmails ) != '' ) {
            $strEmails = str_replace( ';', ',', $strEmails );
            foreach ( explode( ",", $strEmails ) as $strMerchantEmail ) {
                if ( trim( $strMerchantEmail) == '' ) { continue; }
                $this->arrRecepients[ trim( $strMerchantEmail ) ] = trim( $strMerchantEmail );
            }
        }

        return $this;
    }

    /**
     * @param string $strEmails
     * @return boolean
     * @throws Exception
     */
    public function validate( $strEmails )
    {
        if ( is_array( $strEmails )) {
            foreach( $strEmails as $sEmail ) {
                $this->validate( $sEmail );
            }
        } else {
            $strEmails = str_replace( ';', ',', $strEmails );
            foreach ( explode( ",", $strEmails ) as $strMerchantEmail ) {
                if ( trim( $strMerchantEmail ) == '' ) { continue; }

                if ( ! Sys_String::isEmail( trim( $strMerchantEmail ) ) ) {
                    throw new Exception( 'Invalid e-mail '.$strMerchantEmail );
                }
            }
        }
        return true;
    }

    /**
     * @return array
     */
    public function getAsArray()
    {
        return  $this->arrRecepients;
    }

}