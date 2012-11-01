<?php

interface App_Captcha_Adapter extends App_Validate_Interface
{
    /**
     * Generate a new captcha
     *
     * @return string new captcha ID
     */
    public function generate();

    /**
     * Display the captcha
     *
     * @param  Zend_View_Interface $view
     * @param  mixed $element
     * @return string
     */
    public function render( App_View $view = null, $element = null );

    /**
     * Set captcha name
     *
     * @param  string $name
     * @return App_Captcha_Adapter
     */
    public function setName($name);

    /**
     * Get captcha name
     *
     * @return string
     */
    public function getName();

    /**
     * Get optional private decorator for this captcha type
     *
     * @return _Form_Decorator_Interface|string
     */
    public function getDecorator();
}
