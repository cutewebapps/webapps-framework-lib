<?php

class App_RedirectHelper extends App_ViewHelper_Abstract
{
    public function redirect( $strUrl, $strHttpCode = 302 )
    {
        if ( !headers_sent() ) {
            // TODO: 301 or 302
            header( 'Location: ' . $strUrl );
        } 
        return '<meta http-equiv="refresh" content=\'0;url="' . htmlspecialchars( $strUrl ) . '"\' />';
    }
}
