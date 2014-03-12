<?php

class App_MailCtrl extends App_AbstractCtrl
{
    public function contactAction()
    {
        if ( $this->_isPost() && is_object( App_Application::getInstance()->mail ) ) {
            $objConfig = App_Application::getInstance()->mail->contact;
            if ( is_object( $objConfig )) {
                $mail = new App_Mail_Contact();
                $mail->setBody( "New contact message: <br />".implode( "<br />", $arrLines ) );
                $mail->send();
            }
        }
    }
    
    public function subscribeAction()
    {
        if ( $this->_isPost() && is_object( App_Application::getInstance()->mail ) ) {
            // TODO: validation for valid email

            $objConfig = App_Application::getInstance()->mail->subscribe;
            if ( is_object( $objConfig )) {
                // send mail about subscription (if configured)
                $mail = new App_Mail_Subscription( $objConfig );

                $arrLines = array();
                $arrLines []= 'Email: '.$this->_getParam( 'subscribe_email' );
                if ( $this->_hasParam( 'subscribe_event' ) )
                    $arrLines []= 'Event ID: '.$this->_getParam( 'subscribe_event' );
                if ( $this->_hasParam( 'subscribe_first' ) )
                    $arrLines []= 'First Name: '.$this->_getParam( 'subscribe_first' );
                if ( $this->_hasParam( 'subscribe_last' ) )
                    $arrLines []= 'Last Name: '.$this->_getParam( 'subscribe_last' );

                $mail->setBody( "New subscription: <br />".implode( "<br />", $arrLines ) );
                $mail->send();
            }
        }
    }
    
}