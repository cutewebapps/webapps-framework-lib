<?php

class App_MailCtrl extends App_AbstractCtrl
{
    public function contactAction()
    {
        if ( $this->_isPost() && is_object( App_Application::getInstance()->getConfig()->mail ) ) {
            $arrErrors = array();
            $objConfig = App_Application::getInstance()->getConfig()->mail->contact;
            if ( is_object( $objConfig )) {

                $strEmail = $this->_getParam( 'email' );
                if ( trim( $strEmail ) == '' ) {
                    array_push( $arrErrors, array( 'email' => 'Email was not provided' ) );
                } else if ( ! Sys_String::isEmail( $strEmail ) ) {
                    array_push( $arrErrors, array( 'email' => 'Please provide valid e-mail' ) );
                }
                
                if ( count( $arrErrors ) == 0 ) {
                    $mail = new App_Mail_Contact( $objConfig->toArray() );
                    $arrLines = array();
                    foreach ( $this->_getAllParams() as $strKey => $strValue ) {
                        $arrLines []= $strKey.': '.$strValue;
                    }
                    if ( $this->_getParam( 'email' ) ) {
                        $mail->setReplyTo( $this->_getParam( 'email' ) );
                    }
                    $mail->setBody( "<pre style='font-size:14px'>" . "New contact message: <br />". implode( "<br />", $arrLines )."</pre>"       
                                    ." Date: ".date("Y-m-d H:i:s") );
                    $mail->send();
                }
                
            } else {
                array_push( $arrErrors, array( 'message' => 'Contact form was not configured' ) );
            }
            
            $this->view->arrError = $arrErrors;
            if ( count( $arrErrors ) == 0 )
                $this->view->lstMessages = array( 'Message was successfully delivered.' );
        }
    }
    /**
     * This is a very basic action that can be used tor subscriptions
     * As a result of subscription
     * 
     */
    public function subscribeAction()
    {
        if ( $this->_isPost() && is_object( App_Application::getInstance()->getConfig()->mail ) ) {
            $arrErrors  = array();
            
            // validation for valid email
            $strEmail = $this->_getParam( 'subscribe_email' );
            if ( trim( $strEmail ) == '' ) {
                array_push( $arrErrors, array( 'subscribe_email' => 'Email was not provided' ) );
            } else if ( ! Sys_String::isEmail( $strEmail ) ) {
                array_push( $arrErrors, array( 'subscribe_email' => 'Please provide valid e-mail' ) );
            }
            
            $objConfig = App_Application::getInstance()->getConfig()->mail->subscribe;
            if ( is_object( $objConfig )) {

                if ( count( $arrErrors ) == 0 ) {
                    // send mail about subscription (if configured)
                    $mail = new App_Mail_Abstract( $objConfig->toArray()  );
                    $arrLines = array();
                    $arrLines []= 'Email: '.$this->_getParam( 'subscribe_email' );
                    if ( $this->_hasParam( 'subscribe_event' ) )
                        $arrLines []= 'Event ID: '.$this->_getParam( 'subscribe_event' );
                    if ( $this->_hasParam( 'subscribe_first' ) )
                        $arrLines []= 'First Name: '.$this->_getParam( 'subscribe_first' );
                    if ( $this->_hasParam( 'subscribe_last' ) )
                        $arrLines []= 'Last Name: '.$this->_getParam( 'subscribe_last' );

                    $mail->setReplyTo( $strEmail );
                    $mail->setBody( "New subscription: <br />".implode( "<br />", $arrLines ) );
                    $mail->send();
                }
                
            } else {
                array_push( $arrErrors, array( 'message' => 'Subscription was not configured' ) );
            }
            
            $this->view->arrError = $arrErrors;
            if ( count( $arrErrors ) == 0 )
                $this->view->lstMessages = array( 'You were successfully subscribed' );
        }
    }
    
}