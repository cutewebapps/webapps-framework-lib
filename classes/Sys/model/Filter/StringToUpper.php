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

class Sys_Filter_StringToUpper implements Sys_Filter_Interface
{
    /**
     * Encoding for the input string
     *
     * @var string
     */
    protected $_encoding = null;

    /**
     * Constructor
     *
     * @param string|array $options OPTIONAL
     */
    public function __construct($options = null)
    {
        if ($options instanceof Sys_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = func_get_args();
            $temp    = array();
            if (!empty($options)) {
                $temp['encoding'] = array_shift($options);
            }
            $options = $temp;
        }

        if (!array_key_exists('encoding', $options) && function_exists('mb_internal_encoding')) {
            $options['encoding'] = mb_internal_encoding();
        }

        if (array_key_exists('encoding', $options)) {
            $this->setEncoding($options['encoding']);
        }
    }

    /**
     * Returns the set encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Set the input encoding for the given string
     *
     * @param  string $encoding
     * @return Sys_Filter_StringToUpper Provides a fluent interface
     * @throws Sys_Filter_Exception
     */
    public function setEncoding($encoding = null)
    {
        if ($encoding !== null) {
            if (!function_exists('mb_strtoupper')) {
                throw new Sys_Filter_Exception('mbstring is required for this feature');
            }

            $encoding = (string) $encoding;
            if (!in_array(strtolower($encoding), array_map('strtolower', mb_list_encodings()))) {
                throw new Sys_Filter_Exception("The given encoding '$encoding' is not supported by mbstring");
            }
        }

        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Defined by Sys_Filter_Interface
     *
     * Returns the string $value, converting characters to uppercase as necessary
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        if ($this->_encoding) {
            return mb_strtoupper((string) $value, $this->_encoding);
        }

        return strtoupper((string) $value);
    }
}
