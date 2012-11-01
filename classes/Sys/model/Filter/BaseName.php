<?php

class Sys_Filter_BaseName implements Sys_Filter_Interface
{
    /**
     * Defined by Sys_Filter_Interface
     *
     * Returns basename($value)
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        return basename((string) $value);
    }
}
