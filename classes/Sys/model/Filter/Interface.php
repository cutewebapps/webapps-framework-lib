<?php

interface Sys_Filter_Interface
{
    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @throws Sys_Filter_Exception If filtering $value is impossible
     * @return mixed
     */
    public function filter($value);
}
