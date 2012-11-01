<?php

class Sys_Filter_StringToLower implements Sys_Filter_Interface
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
     * @param string|array|Sys_Config $options OPTIONAL
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
     * @return Sys_Filter_StringToLower Provides a fluent interface
     * @throws Sys_Filter_Exception
     */
    public function setEncoding($encoding = null)
    {
        if ($encoding !== null) {
            if (!function_exists('mb_strtolower')) {
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
     * Returns the string $value, converting characters to lowercase as necessary
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        if ($this->_encoding !== null) {
            return mb_strtolower((string) $value, $this->_encoding);
        }

        return strtolower((string) $value);
    }
}
