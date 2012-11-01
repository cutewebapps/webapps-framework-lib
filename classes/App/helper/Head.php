<?php

class App_HeadHelper extends App_ViewHelper_Abstract
{
    public function head()
    {
        $b = $this->getView()->broker();

        // in future: append additional classes from local namespaces
        return $b->headMeta()->get()
             . $b->headTitle()->get()
             . $b->headLink()->get()
             . $b->headStyle()->get()
             . $b->headScript()->get() . "\n\n";
    }
}