<?php

class App_Exception_Handler
{
    public function getConfig()
    {
        return App_Application::getInstance()->getConfig();
    }

    // TODO: in future: pushing notifications to external servers...
    
    public function log( $exception )
    {
        $confException = $this->getConfig()->exceptions;
        if  (is_object(  $confException ) ) {

            if ( $confException->log ) {
                $strFileName = $confException->log .'/'.date('Y-m-d').'.log';
//                if ( ini_get( 'safe_mode' ) ) $strFileName = $confException->log;

                // same exception info into LOG file
                $strFile = new Sys_File( $strFileName );
                $strBody = date("Y-m-d H:i:s")."\t".$_SERVER['REMOTE_ADDR']
                                ."\t".App_Dispatcher::$strUrl.' ';
                $strBody .= "\n***\t".$exception->getMessage();
                $strBody .= "\n".self::backTraceString( $exception->getTrace() ) ."\n\n";
                $strFile->append( $strBody );
            }
        }
    }

    public function mail( $exception )
    {
        $confException = $this->getConfig()->exceptions;
        if  (is_object(  $confException ) ) {

            // mail exception info to somebody
            $strTo = $confException->mail->to;
            $strSubject = $confException->mail->subject.' '
                    .'"'.$exception->getMessage().'"'
                    .' for '.$_SERVER['REMOTE_ADDR'];
            $strHeaders =
                    'Content-Type: text/html; charset="utf-8"' . "\r\n"
                    .$confException->mail->headers;

            // preparing message - more detailed than in logs
            $strMessage = "<pre style='font-size:12px'>"."***\t[".date('Y-m-d H:i:s')."]\n";
            $strMessage .= "\n***\t<strong>".$exception->getMessage().'</strong>';
            $strMessage .= "\n".self::backTraceString( $exception->getTrace() ) ."\n\n\n";

            foreach ( $_SERVER as $strKey => $strValue )  {
                if ( !is_array( $strValue ) ) {
                    $strMessage .= '_SERVER['.$strKey . ']=' . $strValue ."\n";
                } else {
                    ob_start();
                    print_r( $strValue );
                    $strContents = ob_get_contents();
                    ob_end_clean();
                    $strMessage .= '_SERVER['.$strKey . ']=' .$strContents;
                }
            }
            foreach ( $_POST as $strKey => $strValue )  {
                $strMessage .= '_POST['.$strKey . ']=' . $strValue ."\n";
            }
            foreach ( $_COOKIE as $strKey => $strValue )  {
                $strMessage .= '_COOKIE['.$strKey . ']=' . $strValue ."\n";
            }
            $strMessage .= '</pre>';
            
            // using php mailer to send
            if ($confException->mail->method == 'php-mailer') {
                
                $mail = new App_Mailer();

                $mail->IsSMTP();
                $mail->SMTPAuth = true;
                $mail->Host     = $confException->smtp->host;
                $mail->Username = $confException->smtp->username;
                $mail->Password = $confException->smtp->password;

                // from
                $arrFrom = explode(' ', $confException->mail->headers);
                $strFrom = $arrFrom[1];
                
                $mail->From = $strFrom;
                $mail->AddReplyTo($mail->From);

                // to
                $arrTo = explode(',', $strTo);
                
                foreach ($arrTo as $strTo)
                    $mail->AddAddress($strTo);
                
                // attachments
                //$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // name is optional

                $mail->IsHTML( true );
                $mail->WordWrap = 300;
                $mail->SetXMailer( 'Exception Debug Mailer' );

                // subject and body
                $mail->Subject = $strSubject;
                $mail->Body    = $strMessage;
                //$mail->AltBody = $strMessage;

                $mail->Send();                
                
            } else {
                mail( $strTo, $strSubject, $strMessage, $strHeaders );
            }
            
        }
    }

    public function process( $exception )
    {
        $confException = $this->getConfig()->exceptions;
        if  (is_object(  $confException ) ) {
            if ( $confException->log )  $this->log( $exception );
            if ( $confException->mail ) $this->mail( $exception );
        }
    }

    public static function backTraceString( $arrTraceLines )
    {
        $arrStrings = array();
        $nIterator = 0;
        foreach ( $arrTraceLines as $arrLine )
        {
            $strClass = '';
            if ( isset( $arrLine['class'] )) $strClass = $arrLine['class'] . '::';
            $strFunction = '';
            if ( isset( $arrLine['function'] )) $strClass = $arrLine['function'] . '()';

            if ( isset( $arrLine['file'] ) &&
                 isset( $arrLine['line'] ) ) {
                $arrStrings [] = $nIterator.'. '.$arrLine['file'].':'.$arrLine['line'].' '.$strClass.$strFunction;
            } else {
                $arrStrings [] = $nIterator.'. '.$strClass.$strFunction;
            }
            $nIterator ++;
        }
        return implode( "\n", $arrStrings );
    }
    
}