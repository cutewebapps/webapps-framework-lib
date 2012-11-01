<?php

abstract class App_WebsiteCtrl extends App_AbstractCtrl
{
    abstract public function pageNotFoundAction();

    abstract public function accessDeniedAction();

    abstract public function serverErrorAction();
}