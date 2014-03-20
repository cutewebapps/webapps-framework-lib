<?php

class App_Mail_Abstract
{
    /**
     * @var string
     */
    public $strFrom = '';
    /**
     * @var string
     */
    public $strFromName = '';
    /**
     * @var string
     */
    public $strReplyTo = '';
    /**
     * @var string
     */
    public $strReturnPath = '';
    /**
     * @var string
     */
    public $strBody = '';

    /**
     * @var string
     */
    public $strTo = '';
    /**
     * @var string
     */
    public $strSubject = '';
    /**
     * @var array
     */
    public $arrCC = array();
    /**
     * @var array
     */
    public $arrBCC = array();
    /**
     * @var array
     */
    public $arrEmbed = array();
    /**
     * Alternative Message
     * @var string
     */
    public $strAltBody = '';
    /**
     * @var string
     */
    public $nSenderType = 0;
    /**
     * @var array
     */
    public $arrParams = array();

    /**
     * this class must be overwritten by child
     * @return string
     * @throws App_Exception
     */
    public function getClassName()
    {
        // throw new App_Exception( 'getClassName is not overloaded' );
    }
    /**
     * initializing mail class
     * @return void
     */
    public function __construct( $arrProperties = array() )
    {
        $this->arrParams = $arrProperties;
        
        if  (isset( $this->arrParams['from'] ))
            $this->strFrom = $this->arrParams['from'];
        else if  (isset( $this->arrParams['from_emal'] ))
            $this->strFrom = $this->arrParams['from_email'];
        
        if  (isset( $this->arrParams['from_name'] ))
            $this->strFromName = $this->arrParams['from_name'];
        
        
        if  (isset( $this->arrParams['to'] ))
            $this->strTo = $this->arrParams['to'];
        if  (isset( $this->arrParams['subject'] ))
            $this->strSubject = $this->arrParams['subject'];

        if  ( isset( $this->arrParams['cc'] ) ) {
            $this->addCC( $this->arrParams['cc'] );
        }
        if  ( isset( $this->arrParams['bcc'] ) ) {
            $this->addBCC( $this->arrParams['bcc'] );
        }
        
        if  (isset( $this->arrParams['template'] ) && $this->arrParams['template'] != '' ) {
            
            $strRendererClass = 'App_View';
            if  (isset( $this->arrParams['renderer'] ) && class_exists( $this->arrParams['renderer'] ) ) {
                $strRendererClass = $this->arrParams['renderer'];
            }

            $view = new $strRendererClass();
            $view->setPath( $this->arrParams['template'] );
            foreach( $this->arrParams as $key => $value ) $view->$key = $value;
            
            $this->strBody = $view->render();
        }
    }

    /**
     * set mail Delivery address
     * @param string $strTo
     * @return Gateway_Mail
     */
    public function setTo( $strTo )
    {
        $this->strTo = $strTo;
        return $this;
    }
    
    /**
     * Set cc email
     * @param mixed $strCC
     */
    public function setCC( $strCC )
    {
        if ( is_array( $strCC ))
            $this->arrCC = $strCC;
        else 
            $this->arrCC = array( $strCC );
        return $this;
    }
    /**
     * 
     * @param mixed $strCC
     * @return App_Mail_Abstract
     */
    public function addCC( $strCC )
    {
        if ( is_array( $strCC )) {
            foreach ( $strCC as $sCC )
                if ( !in_array( $sCC, $this->arrCC ) )
                    $this->arrCC[] = $sCC;
        } else 
            $this->arrCC[] = $strCC;
        return $this;
    }
    
    /**
     * Set cc email
     * @param mixed $strBCC
     */
    public function setBCC( $strBCC )
    {
        if ( is_array( $strBCC ))
            $this->arrBCC = $strBCC;
        else 
            $this->arrBCC = array( $strBCC );
        return $this;
    }
   /**
    * 
    * @param mixed $strCC
    * @return App_Mail_Abstract
    */
    public function addBCC( $strCC )
    {
        if ( is_array( $strCC )) {
            foreach ( $strCC as $sCC )
                if ( !in_array( $sCC, $this->arrBCC ) )
                    $this->arrBCC[] = $sCC;
        } else 
            $this->arrBCC[] = $strCC;
        return $this;
    }

    /**
     * Set mail subject
     * @param string $strSubject
     * @return Gateway_Mail
     */
    public function setSubject( $strSubject )
    {
        $this->strSubject = $strSubject;
        return $this;
    }

    public function setFrom( $strFrom )
    {
        $this->strFrom = $strFrom;
        return $this;
    }

    /**
     * Set body of the message
     * @param string $strBody
     * @return Gateway_Mail
     */
    public function setBody( $strBody )
    {
        $this->strBody = $strBody;
        return $this;
    }

    
    /**
     * Set alt message text
     * @param string $strAltBody
     */
    public function setAltBody( $strAltBody )
    {
        $this->strAltBody = $strAltBody;
    }
    
    /**
     * set address for reply
     * @param string $strEmail
     * @return Gateway_Mail
     */
    public function setReplyTo( $strEmail )
    {
        $this->strReplyTo = $strEmail;
        return $this;
    }
    
    /**
     * set address for reply
     * @param string $strEmail
     * @return Gateway_Mail
     */
    public function setReturnPath( $strEmail )
    {
        $this->strReturnPath = $strEmail;
        return $this;
    }

    /**
     * Send mail
     * input $to,$subject,$body
     * @return bool
     */
    public function send()
    {
        if ( ! $this->strFrom )
            throw new App_Mail_Exception( 'Mail: From is missing' );
        if ( ! $this->strTo )
            throw new App_Mail_Exception( 'Mail: To is missing' );
        if ( ! trim( $this->strSubject ) )
            throw new App_Mail_Exception( 'Mail: Subject is missing, cannot be delivered ' );
        if ( ! trim($this->strBody ) )
            throw new App_Mail_Exception( 'Mail: Message is missing, cannot be delivered ' );

        $strCharset = App_Application::getInstance()->getConfig()->charset;
        if ( $strCharset == '' ) $strCharset = 'utf-8';

        $strEnv = strtoupper(Sys_Global::get('Environment'));
        $this->objConfig = App_Application::getInstance()->getConfig()->mail;
        
        if (is_object($this->objConfig))
            $this->strFrom = $this->objConfig->from;
        
        if ( ! is_object($this->objConfig) ) {
            throw new App_Exception( 'Mail configuration section must be defined' );
        }

        $mail = new App_Mailer();
        if ($this->objConfig->use_lang != 'en')
            $mail->SetLanguage( $this->objConfig->use_lang );

        $mail->IsSMTP();                    // set mailer to use SMTP
        $mail->SMTPSecure   = isset( $this->arrParams['ssl'] ) ? $this->arrParams['ssl'] : $this->objConfig->smtp->ssl;
        $mail->Host   = isset( $this->arrParams['host'] ) ? $this->arrParams['host'] : $this->objConfig->smtp->host;
        $mail->Port   = isset( $this->arrParams['port'] ) ? $this->arrParams['port'] : $this->objConfig->smtp->port;
        if ( $mail->Port == '' ) $mail->Port = 25;

        if ( isset( $this->arrParams['username'] ) && $this->arrParams['username']  != '' ) {
            $mail->SMTPAuth = true;             // turn on SMTP authentication
            $mail->Username = $this->arrParams['username'];
            $mail->Password = isset( $this->arrParams['password'] ) ? $this->arrParams['password'] : '';
        } else if ( $this->objConfig->smtp->username ) {
            $mail->SMTPAuth = true;             // turn on SMTP authentication
            $mail->Username = $this->objConfig->smtp->username;
            $mail->Password = $this->objConfig->smtp->password;
        }

        // Sys_Debug::dump( $this->arrParams ); Sys_Debug::dump( $mail ); die;

        // from
        $mail->From = ( isset( $this->arrParams['from_email'] ) ) ? $this->arrParams['from_email'] : $this->strFrom;
        $mail->FromName = ( isset( $this->arrParams['from_name'] ) ) ? $this->arrParams['from_name'] : ( $this->strFromName ? $this->strFromName : ' No-REPLY' );
        $strReplyTo = ( isset( $this->arrParams['reply_to'] ) ) ? $this->arrParams['reply_to'] : $this->strReplyTo ;
        if ( $strReplyTo ) $mail->AddReplyTo( $strReplyTo );

        $mail->ReturnPath = ( isset( $this->arrParams['return_path'] ) ) ? $this->arrParams['return_path'] : $this->strReturnPath ;
        // if( is_string( $this->strCC ) && trim($this->strCC) != "" ){
        
        foreach ( $this->arrCC as $strCc ) {
            if ( $strCc != '' ) continue;
            $mail->AddCC( $strCc );
        }
        foreach ( $this->arrBCC as $strCc ) {
            if ( $strCc != '' ) continue;
            $mail->AddBCC( $strCc );
        }
        
        //}
        if ( $this->strAltBody ) {
            $mail->AltBody = $this->strAltBody;
        }

        $mail->AddAddress($this->strTo); // name is optional

        // embeded images
        if (count($this->arrEmbed)) {
            foreach ($this->arrEmbed as $arrEmbed) {
                $mail->AddEmbeddedImage($arrEmbed['path'], $arrEmbed['cid'], $arrEmbed['name']);                    
            }
        }

        $mail->IsHTML( $this->objConfig->use_html );
        $mail->WordWrap = $this->objConfig->word_wrap;
        $mail->SetXMailer( $this->objConfig->xmailer ." ($strEnv)" );

        // subject and body
        $mail->Subject = $this->strSubject;
        $mail->Body    = $this->strBody;
        $bMailResult = $mail->Send();


        if( $mail->ErrorInfo )
            throw new App_Exception( $mail->ErrorInfo );
        
        Sys_Debug::alertHtml( array(
                'subject' => $this->strSubject,
                'from_email' => $mail->From,
                'from_name' => $mail->FromName,
                'reply_to' => $strReplyTo,
                'return_path' => $mail->ReturnPath,
                'to' => $this->strTo,
                'cc' => implode( ",", $this->arrCC ),
                'bcc' => implode( ",", $this->arrBCC ),
                'host' => $mail->Host.':'.$mail->Port
        ), $this->strBody );        
        
        // saving temp file with email
        $strMessage = $this->strBody;
        //$strMessage =  $mail->GetSentMIMEMessage();
        
        if ( App_Application::getInstance()->getConfig()->mail_dir ) {
            
            $file = new Sys_File( App_Application::getInstance()->getConfig()->mail_dir
                    .'/'.date('Y-m-d').'/_system/'.$this->getClassName() .'_'
                    . date( 'H-i-s').'-'.mt_rand(0,100).'.html' );
            
            $file->save( "To:".$this->strTo."\nSubject:".$this->strSubject
                    ."\nTime:".date('Y-m-d H:i:s')."\n".$strMessage );
        }
        return $bMailResult;
    }

}