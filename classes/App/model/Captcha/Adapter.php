<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

/**
 * Based on Zend Framework                                                                                                  
 *                                                                                                                 
 * LICENSE                                                                                                         
 *                                                                                                                 
 * This source file is subject to the new BSD license that is bundled                                              
 * with this package in the file LICENSE.txt.                                                                      
 * It is also available through the world-wide-web at this URL:                                                    
 * http://framework.zend.com/license/new-bsd                                                                       
 * If you did not receive a copy of the license and are unable to                                                  
 * obtain it through the world-wide-web, please send an email                                                      
 * to license@zend.com so we can send you a copy immediately.                                                      
 *                                                                                                                 
 * @category   Zend                                                                                                
 * @package    Zend_InfoCard                                                                                       
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)                            
 * @license    http://framework.zend.com/license/new-bsd     New BSD License                                       
 * @version    $Id: InfoCard.php 20096 2010-01-06 02:05:09Z bkarwin $                                              
 */

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
