<?php

class App_Form_Element
{
    /**
     * Element filters
     * @var array
     */
    protected $_filters = array();

    /**
     * Custom error messages
     * @var array
     */
    protected $_errorMessages = array();

    /**
     * Validation errors
     * @var array
     */
    protected $_errors = array();

    /**
     * 'Allow empty' flag
     * @var bool
     */
    protected $_allowEmpty = true;

    /**
     * Is the error marked as in an invalid state?
     * @var bool
     */
    protected $_isError = false;

    /**
     * Has the element been manually marked as invalid?
     * @var bool
     */
    protected $_isErrorForced = false;

    /**
     * Element label
     * @var string
     */
    protected $_label;

    /**
     * Required flag
     * @var bool
     */
    protected $_required = false;

    /**
     * Array of initialized validators
     * @var array Validators
     */
    protected $_validators = array();

    /**
     * Array of un-initialized validators
     * @var array
     */
    protected $_validatorRules = array();

    /**
     * Element label
     * @var string
     */
    protected $_helper;


    /**
     * @var string
     */
    protected $_name;

    /**
     * @var string
     */
    protected $_value;
    
    /**
     * Initialize object; used by extending classes
     *
     * @return void
     */
    public function init()
    {
    }


    /**
     * Filter a name to only allow valid variable characters
     *
     * @param  string $value
     * @param  bool $allowBrackets
     * @return string
     */
    public function filterName($value, $allowBrackets = false)
    {
        $charset = '^a-zA-Z0-9_\x7f-\xff';
        if ($allowBrackets) {
            $charset .= '\[\]';
        }
        return preg_replace('/[' . $charset . ']/', '', (string) $value);
    }

    /**
     * Set element name
     *
     * @param  string $name
     * @return App_Form_Element
     */
    public function setName($name)
    {
        $name = $this->filterName($name);
        if ('' === $name) {
            throw new App_Form_Exception('Invalid name provided; must contain only valid variable characters and be non-empty');
        }

        $this->_name = $name;
        return $this;
    }

    /**
     * Return element name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Return element name
     *
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Set element value
     *
     * @param  mixed $value
     * @return Zend_Form_Element
     */
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }
    
    public function isValid($value, $context = null)
    {
        $this->setValue($value);
        $value = $this->getValue();
        return true; // TEMP!
    }

    public function __construct( $spec, $options = null)
    {
        if (is_string($spec)) {
            $this->setName($spec);
        } elseif (is_array($spec)) {
            $this->setOptions($spec);
        } elseif ($spec instanceof Sys_Config) {
            $this->setConfig($spec);
        }
        if (is_string($spec) && is_array($options)) {
            $this->setOptions($options);
        } elseif (is_string($spec) && ($options instanceof Sys_Config)) {
            $this->setConfig($options);
        }
    }
}