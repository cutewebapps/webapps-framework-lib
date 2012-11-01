<?php

class App_TokenHelper extends App_ViewHelper_Abstract
{
    /**
     * @return string
     */
    public function token()
    {
        return App_Token::generate();
    }
}