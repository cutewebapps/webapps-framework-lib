<?php

class Sys_Filter_Int implements Sys_Filter_Interface
{
    /**
     * Defined by Sys_Filter_Interface
     *
     * Returns (int) $value
     *
     * @param  string $value
     * @return integer
     */
    public function filter($value)
    {
        return (int) ((string) $value);
    }
}
