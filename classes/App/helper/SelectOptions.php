<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */


class App_SelectOptionsHelper extends App_ViewHelper_Abstract
{
    /**
     * returns HTML for displaying options of a dropdown, 
     * with a specified default value
     * 
     * @param array $arrValues
     * @param string $arrStrDefault
     * @return string
     */
    public function selectoptions( $arrValues, $arrStrDefault = '' )
    {
	$o = '';
	foreach ( $arrValues as $value => $title ) {
		$s = '';
		if ( strtolower($arrStrDefault) == strtolower($value) ) $s = ' selected="selected" ';
                // wtf was style?
                //
		// if ($style != '') $s .= " style=\"" . $style . "\" ";
		$o .= '<option value="'.$value.'"'.$s.'>'.$title.'</option>'."\n";
	}
	return $o;
    }
}