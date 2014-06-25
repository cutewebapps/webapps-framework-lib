<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

/** for PHP 5.4-- compatibility */
if (!function_exists('http_response_code'))
{
    function http_response_code($newcode = NULL)
    {
        static $code = 200;
        if($newcode !== NULL)
        {
            header('X-PHP-Response-Code: '.$newcode, true, $newcode);
            if(!headers_sent())
                $code = $newcode;
        }       
        return $code;
    }
}

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

                $strIp = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
                
                // same exception info into LOG file
                $strFile = new Sys_File( $strFileName );
                $strBody = date("Y-m-d H:i:s")."\t".$strIp
                                ."\t".App_Dispatcher::$strUrl.' ';
                $strBody .= "\n***\t".$exception->getMessage();
                $strBody .= "\n".self::backTraceString( $exception->getTrace() ) ."\n\n";
                $strFile->append( $strBody );
            }
        }
    }

    public function alert( $exception )
    {
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
                if ( is_string( $strValue ) ) {
                    $strMessage .= '_POST['.$strKey . ']=' . $strValue ."\n";
                } else {
                    $strMessage .= '_POST['.$strKey . ']=' . print_r( $strValue, true ) ."\n";
                }
            }
            foreach ( $_COOKIE as $strKey => $strValue )  {
                $strMessage .= '_COOKIE['.$strKey . ']=' . $strValue ."\n";
            }
            $strMessage .= '</pre>';    
            
            Sys_Debug::alert( $strMessage );
    }
    
    public function mail( $exception )
    {
        $confException = $this->getConfig()->exceptions;
        if  (is_object(  $confException ) ) {

            // mail exception info to somebody
            $strIp = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
            $strTo = $confException->mail->to;
            $strSubject = $confException->mail->subject.' '
                    .'"'.$exception->getMessage().'"'
                    .' for '.$strIp;
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
                if ( is_string( $strValue ) ) {
                    $strMessage .= '_POST['.$strKey . ']=' . $strValue ."\n";
                } else {
                    $strMessage .= '_POST['.$strKey . ']=' . print_r( $strValue, true ) ."\n";
                }
            }
            foreach ( $_COOKIE as $strKey => $strValue )  {
                $strMessage .= '_COOKIE['.$strKey . ']=' . $strValue ."\n";
            }
            $strMessage .= '</pre>';
            
            // using php mailer to send
            if ($confException->mail->method == 'php-mailer') {
                
                $mail = new App_Mailer();

                $mail->IsSMTP();
                $mail->Host     = $confException->smtp->host;
                if ( $confException->smtp->port ) {
                    $mail->Port = $confException->smtp->port;
		}
                if ( $confException->smtp->username != "" ) {
                    $mail->SMTPAuth = true;
                    $mail->Username = $confException->smtp->username;
                    $mail->Password = $confException->smtp->password;
                }
		if ( $confException->smtp->ssl ) {
                    $mail->SMTPSecure  = $confException->smtp->ssl;
		}     
//Sys_Debug::dumpDie( $mail );
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
        if ( PHP_SAPI != "cli" && !headers_sent() ) {
            http_response_code( 501 );
        }
        $confException = $this->getConfig()->exceptions;
        if  (is_object(  $confException ) ) {
            if ( $confException->log )  $this->log( $exception );
            if ( $confException->mail ) $this->mail( $exception );
            if ( $confException->alert ) $this->alert( $exception );
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