<?php


class App_RelativedateHelper extends App_ViewHelper_Abstract
{
    public function relativedate($date)
    {
        if ($date == '' || substr($date, 0, 10) == '0000-00-00')
            return '';
        $d = abs(strtotime(date('Y-m-d H:i:s')) - strtotime($date));
        $bfr = ''; $tp ='';
        if (strtotime(date('Y-m-d H:i:s')) - strtotime($date) >= 0)
            $tp = ' ago';
        else
            $bfr = 'in ';
        if ($d < 60)
            return $bfr."$d seconds" . $tp;

        if ($d < 3600) {
            $result = floor($d / 60);
            $pl = ($result > 1) ? 's' : '';
            return $bfr."$result minute$pl" . $tp;
        }
        if ($d < 86400) {
            $result = floor($d / (60 * 60));
            $pl = ($result > 1) ? 's' : '';
            return $bfr."$result hour$pl" . $tp;
        }
        if ($d < 2592000) {
            $result = floor($d / (60 * 60 * 24));
            $pl = ($result > 1) ? 's' : '';
            return $bfr."$result day$pl" . $tp;
        }
        $result = floor($d / (60 * 60 * 24 * 30));
        $pl = ($result > 1) ? 's' : '';
        return $bfr."$result month$pl" . $tp;
    }
}
