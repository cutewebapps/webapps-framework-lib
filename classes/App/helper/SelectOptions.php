<?php

class App_SelectOptionsHelper extends App_ViewHelper_Abstract
{
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