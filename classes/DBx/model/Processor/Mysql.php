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
class DBx_Processor_Mysql extends DBx_Processor
{
        // this function should be commonly used in creating mysql-dumps
	// it gets some symbols from sql-string, skipping comments
	// and screened symbols in quotations
	function next($s, $pos = 0)
	{

		$ch = $this->_ch($s, $pos);
		$len = strlen($s);
		$result = $pos;
		while ($len > $result && preg_match('/^\s$/', $ch)) {
			$result++;
			if (preg_match('/\S$/', $this->expr))
				$this->expr .= ' ';
			else if ($ch == "\n")
				$this->expr .= "\n";
			$ch = $this->_ch($s, $result);
		}
		if (strstr('+*&|^%!@/=,[]{}', $ch)) {
			$this->expr .= $ch;
			return $result;
		}

		if ($ch == '\'') {
			$this->expr .= $ch;
			do {
				$result++;
				if ($this->_ch($s, $result) == '\\') {
					$result++;
					$this->expr .= $this->_ch($s, $result);
				}
				$this->expr .= $this->_ch($s, $result);
			} while ($len > $result &&
			$this->_ch($s, $result) != '\'');
		} else if ($ch == '"') {
			$this->expr .= $ch;
			do {
				$result++;
				if ($this->_ch($s, $result) == '\\') {
					$result++;
					$this->expr .= $this->_ch($s, $result);
				}
				$this->expr .= $this->_ch($s, $result);
			} while ($len > $result &&
			$this->_ch($s, $result) != '"');
		} else if ($ch == '#' ||
				( $ch == '/' && $this->_ch($s, $result + 1) == '/' ) ||
				( $ch == '-' && $this->_ch($s, $result + 1) == '-' )) {

			$comment = '';
			if ($ch != '#')
				$result++;
			do {
				$result++;
			} while ($len > $result && $this->_ch($s, $result) != "\n" && $this->_ch($s, $result) != "\r");
		} else {
			$this->expr .= $ch;
		}
		return $result;
	}
}