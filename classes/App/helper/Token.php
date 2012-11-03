<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */


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