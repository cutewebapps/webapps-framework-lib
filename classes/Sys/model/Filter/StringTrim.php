<?php

class Sys_Filter_StringTrim implements Sys_Filter_Interface
{
    /**
     * List of characters provided to the trim() function
     *
     * If this is null, then trim() is called with no specific character list,
     * and its default behavior will be invoked, trimming whitespace.
     *
     * @var string|null
     */
    protected $_charList;

    /**
     * Sets filter options
     *
     * @param  string|array|Sys_Config $options
     * @return void
     */
    public function __construct($options = null)
    {
        if ($options instanceof Sys_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options          = func_get_args();
            $temp['charlist'] = array_shift($options);
            $options          = $temp;
        }

        if (array_key_exists('charlist', $options)) {
            $this->setCharList($options['charlist']);
        }
    }

    /**
     * Returns the charList option
     *
     * @return string|null
     */
    public function getCharList()
    {
        return $this->_charList;
    }

    /**
     * Sets the charList option
     *
     * @param  string|null $charList
     * @return Sys_Filter_StringTrim Provides a fluent interface
     */
    public function setCharList($charList)
    {
        $this->_charList = $charList;
        return $this;
    }

    /**
     * Defined by Sys_Filter_Interface
     *
     * Returns the string $value with characters stripped from the beginning and end
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        if (null === $this->_charList) {
            return $this->_unicodeTrim((string) $value);
        } else {
            return $this->_unicodeTrim((string) $value, $this->_charList);
        }
    }

    /**
     * Unicode aware trim method
     * Fixes a PHP problem
     *
     * @param string $value
     * @param string $charlist
     * @return string
     */
    protected function _unicodeTrim($value, $charlist = '\\\\s')
    {
        $chars = preg_replace(
            array( '/[\^\-\]\\\]/S', '/\\\{4}/S', '/\//'),
            array( '\\\\\\0', '\\', '\/' ),
            $charlist
        );

        $pattern = '^[' . $chars . ']*|[' . $chars . ']*$';
        return preg_replace("/$pattern/sSD", '', $value);
    }
}
