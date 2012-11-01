<?php

class App_FooterHelper extends App_ViewHelper_Abstract
{
    public function footer()
    {
        return $this->getView()->broker()->FooterScript()->get() . "\n\n";
    }
}