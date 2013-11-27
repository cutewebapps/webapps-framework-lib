<?php

class App_Test_Json
{
    protected $_arrParams = array();
    protected $_sResult = '';
    protected $_sFullResult = '';
    
    
    function __construct( $arrParams )
    {
        $this->_arrParams = $arrParams;
    }

    public function runHttpTest()
    {
        $strUrl = $this->_arrParams['url'];
        $http = new App_Http_Browser();
        $http->bNoFollow = true;
        $http->httpGet( $strUrl );
        
        $this->_sFullResult = $http->HttpHeaders.' '.$http->HttpBody;
        
        // Sys_Debug::dump( $http->HttpHeaders );
        // Sys_Debug::dump( $http->getLocation() );
        
        if ( isset( $this->_arrParams['status']) )
            if ( $http->HttpStatus != $this->_arrParams['status'] )
                throw new Exception( 'Http status : '.$http->HttpStatus .', '. $this->_arrParams['status'].' expected' );
        if ( isset( $this->_arrParams['matches']) )
            if ( ! stristr( $http->HttpBody, $this->_arrParams['matches'] ))
                throw new Exception( 'Http Body:  expected \''.htmlspecialchars( $this->_arrParams['matches'] ).'\'' );
        if ( isset( $this->_arrParams['location']) )
            if ( ! stristr( $http->getLocation(), $this->_arrParams['location'] ))
                throw new Exception( 'Redirect: '.$http->getLocation().', expected \''.htmlspecialchars( $this->_arrParams['location'] ).'\'' );
    }
    
    public function runShellTest()
    {
        $strCmd = $this->_arrParams['shell'];
        //ob_start();
        $strAnswer = shell_exec( $strCmd );
        $this->_sFullResult = $strAnswer;
        // ob_end_clean();
        if ( isset( $this->_arrParams['matches']) )
            if ( ! strstr( $strAnswer, $this->_arrParams['matches'] ))
                throw new Exception( 'Expected '.htmlspecialchars( $this->_arrParams['matches'] ) );
    }
    
    public function runMailTest()
    {
        $mail = new App_Mailer();
        
        $mail->AddAddress( $this->_arrParams['mailto'] );
        
        $mail->SetLanguage( 'en' );
        $mail->IsSMTP();    // set mailer to use SMTP
        $mail->SMTPSecure   = isset( $this->_arrParams['ssl'] )? $this->_arrParams['ssl'] : '';
        $mail->Host         = $this->_arrParams['host']; 
        $mail->Port         = isset( $this->_arrParams['port'] )? $this->_arrParams['port'] : 25;
        if ( isset( $this->_arrParams['username'] ) ) {
            $mail->SMTPAuth = true;             // turn on SMTP authentication
            $mail->Username =  $this->_arrParams['username'];
            $mail->Password =  isset( $this->_arrParams['password'] ) ? $this->_arrParams['password'] : '';
        }
        $mail->From = $this->_arrParams['from_email'];
        $mail->FromName = $this->_arrParams['from_name'];
        $mail->AddReplyTo( $mail->From );
        // $mail->AddBCC( 'webcerebrium@gmail.com' );
            
        if( isset( $this->_arrParams['cc'] ) ){
            $mail->AddCC( $this->strCC );
        }
        if ( isset( $this->_arrParams['alt_body'] ) ) {
            $mail->AltBody = $this->strAltBody.' <br />Date sent: '.date('Y-m-d H:i:s');
        }
        $mail->IsHTML( true );
        $mail->Subject = $this->_arrParams['subject'];
        $mail->Body    = $this->_arrParams['body'].' <br />Date sent: '.date('Y-m-d H:i:s');

        $mail->Send();
        if( $mail->ErrorInfo )
            throw new App_Exception( $mail->ErrorInfo );
    }
    
        
    public function runLookUpTest()
    {
        $strDomain = $this->_arrParams['lookup'];
        $strServer = isset( $this->_arrParams['server'] ) ? $this->_arrParams['server'] : '';
        
        $strAnswer = shell_exec( 'nslookup '.$strDomain.' '.$strServer );
        $this->_sFullResult = $strAnswer;
        
        // Sys_Debug::dump( $strAnswer );
        
        // ob_end_clean();
        if ( isset( $this->_arrParams['matches']) )
            if ( ! stristr( $strAnswer, $this->_arrParams['matches'] ))
                throw new Exception( 'Expected '.htmlspecialchars( $this->_arrParams['matches'] ) );
    }
    
    
    public function run()
    {
        $this->_sResult  = 'ok';
        try {
            
            if ( isset( $this->_arrParams['url'] )) {
                $this->runHttpTest();
            } elseif ( isset( $this->_arrParams['shell'] )) {
                $this->runShellTest();
            } elseif ( isset( $this->_arrParams['mailto'] )) {
                $this->runMailTest();
            } elseif ( isset( $this->_arrParams['lookup'] )) {
                $this->runLookUpTest();
            } else {
                throw new Exception("Unrecognized test");
            }
            
        } catch ( Exception $e ) {
            $this->_sResult  = $e->getMessage();
        }
    }
    /**
     * 
     * @return string
     */

    public function getTitle()
    {
        if ( isset( $this->_arrParams['url'] ))
            return $this->_arrParams['url'];
        if ( isset( $this->_arrParams['shell'] ))
            return $this->_arrParams['shell'];
        if ( isset( $this->_arrParams['mailto'] ))
            return 'mailto:'.$this->_arrParams['mailto'];
        if ( isset( $this->_arrParams['lookup'] ))
            return 'nslookup '.$this->_arrParams['lookup'];
    }
    /**
     * 
     * @return string
     */
    public function getResultStyle()
    {
        if ( $this->_sResult == 'ok' )
            return 'color:darkgreen';
        return 'color:red';
    }
    
    /**
     * 
     * @return string
     */
    public function getResultString()
    {
        return $this->_sResult;
    }
     /**
     * 
     * @return string
     */
    public function getFullResultString()
    {
        return $this->_sFullResult;
    }
    
    /**
     * 
     * @return string
     */
    public function getTimeFinished()
    {
        return date('H:i:s');
    }
}