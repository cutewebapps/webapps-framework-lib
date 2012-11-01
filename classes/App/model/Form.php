<?php

class App_Form  //
{

    protected $_elements = array();

    /**
     * Custom form-level error messages
     * @var array
     */
    protected $_errorMessages = array();

    /**
     * Are there errors in the form?
     * @var bool
     */
    protected $_errorsExist = false;

    /**
     * Has the form been manually flagged as an error?
     * @var bool
     */
    protected $_errorsForced = false;

    /**
     * Retrieve a single element
     *
     * @param  string $name
     * @return Zend_Form_Element|null
     */
    public function getElement($name)
    {
        if (array_key_exists($name, $this->_elements)) {
            return $this->_elements[$name];
        }
        return null;
    }

    public function getElements()
    {
        return $this->_elements;
    }

    public function isValid( $data )
    {
        if (!is_array($data)) {
            throw new App_Form_Exception(__METHOD__ . ' expects an array');
        }
        $valid    = true;
        $context = $data;

        
        foreach ($this->getElements() as $key => $element) {
            
            //echo '<div> Checking '.$data[$key].' : '.$element->getValue().'</div>';
            if (!isset($data[$key])) {
                $valid = $element->isValid(null, $context) && $valid;
            } else {
                $valid = $element->isValid($data[$key], $context) && $valid;
                // $data = $this->_dissolveArrayUnsetKey($data, $belongsTo, $key);
            }
        }
        $this->_errorsExist = !$valid;

        // If manually flagged as an error, return invalid status
        if ($this->_errorsForced) {
            return false;
        }

        return $valid;
    }

    // for further overloading
    public function getObjectName()
    {
        return str_replace( '_', ' ', get_called_class());
    }
    
    public function setDefaults( $defaults )
    {

    }

    public function createElements()
    {
        
    }


    /**
     * Add a new element
     *
     * $element may be either a string element type, or an object of type
     * App_Form_Element. If a string element type is provided, $name must be
     * provided, and $options may be optionally provided for configuring the
     * element.
     *
     * If a App_Form_Element is provided, $name may be optionally provided,
     * and any provided $options will be ignored.
     *
     * @param  string|App_Form_Element $element
     * @param  string $name
     * @param  array|App_Config $options
     * @return App_Form
     */
    public function addElement($element, $name = null, $options = null)
    {
        if (is_string($element)) {
            if (null === $name) {
                throw new App_Form_Exception('Elements specified by string must have an accompanying name');
            }
            $this->_elements[$name] = $this->createElement($element, $name, $options);
            
        } elseif ($element instanceof App_Form_Element) {
            if (null === $name) {
                $name = $element->getName();
            }
            $this->_elements[$name] = $element;
        }
        return $this;
    }

    /**
     * Create an element
     *
     * Acts as a factory for creating elements. Elements created with this
     * method will not be attached to the form, but will contain element
     * settings as specified in the form object (including plugin loader
     * prefix paths, default decorators, etc.).
     *
     * @param  string $type
     * @param  string $name
     * @param  array|Sys_Config $options
     * @return App_Form_Element
     */
    public function createElement($type, $name, $options = null)
    {
        if (!is_string($type)) {
            throw new App_Form_Exception('Element type must be a string indicating type');
        }

        if (!is_string($name)) {
            throw new App_Form_Exception('Element name must be a string');
        }

//        $prefixPaths              = array();
//        $prefixPaths['decorator'] = $this->getPluginLoader('decorator')->getPaths();
//        if (!empty($this->_elementPrefixPaths)) {
//            $prefixPaths = array_merge($prefixPaths, $this->_elementPrefixPaths);
//        }

        if ($options instanceof Sys_Config) {
            $options = $options->toArray();
        }

//        if ((null === $options) || !is_array($options)) {
//            $options = array('prefixPath' => $prefixPaths);
//        } elseif (is_array($options)) {
//            if (array_key_exists('prefixPath', $options)) {
//                $options['prefixPath'] = array_merge($prefixPaths, $options['prefixPath']);
//            } else {
//                $options['prefixPath'] = $prefixPaths;
//            }
//        }
        
        $class = 'App_Form_Element_'.Sys_String::toCamelCase( $type );
        // $class = $this->getPluginLoader(self::ELEMENT)->load($type);
        $element = new $class($name, $options);
        return $element;
    }


    public function createDecorators()
    {
        
    }

    /**
     * Given an array, an optional arrayPath and a key this method
     * dissolves the arrayPath and unsets the key within the array
     * if it exists.
     *
     * @param array $array
     * @param string|null $arrayPath
     * @param string $key
     * @return array
     */
    protected function _dissolveArrayUnsetKey($array, $arrayPath, $key)
    {
        $unset =& $array;
        $path  = trim(strtr((string)$arrayPath, array('[' => '/', ']' => '')), '/');
        $segs  = ('' !== $path) ? explode('/', $path) : array();

        foreach ($segs as $seg) {
            if (!array_key_exists($seg, (array)$unset)) {
                return $array;
            }
            $unset =& $unset[$seg];
        }
        if (array_key_exists($key, (array)$unset)) {
            unset($unset[$key]);
        }
        return $array;
    }

    /**
     * Converts given arrayPath to an array and attaches given value at the end of it.
     *
     * @param  mixed $value The value to attach
     * @param  string $arrayPath Given array path to convert and attach to.
     * @return array
     */
    protected function _attachToArray($value, $arrayPath)
    {
        // As long as we have more levels
        while ($arrayPos = strrpos($arrayPath, '[')) {
            // Get the next key in the path
            $arrayKey = trim(substr($arrayPath, $arrayPos + 1), ']');

            // Attach
            $value = array($arrayKey => $value);

            // Set the next search point in the path
            $arrayPath = trim(substr($arrayPath, 0, $arrayPos), ']');
        }

        $value = array($arrayPath => $value);

        return $value;
    }

    /**
     * This is a helper function until php 5.3 is widespreaded
     *
     * @param array $into
     * @access protected
     * @return void
     */
    protected function _array_replace_recursive(array $into)
    {
        $fromArrays = array_slice(func_get_args(),1);

        foreach ($fromArrays as $from) {
            foreach ($from as $key => $value) {
                if (is_array($value)) {
                    if (!isset($into[$key])) {
                        $into[$key] = array();
                    }
                    $into[$key] = $this->_array_replace_recursive($into[$key], $from[$key]);
                } else {
                    $into[$key] = $value;
                }
            }
        }
        return $into;
    }

    /**
     * Add a custom error message to return in the event of failed validation
     *
     * @param  string $message
     * @return App_Form
     */
    public function addErrorMessage($message)
    {
        $this->_errorMessages[] = (string) $message;
        return $this;
    }

    /**
     * Add multiple custom error messages to return in the event of failed validation
     *
     * @param  array $messages
     * @return App_Form
     */
    public function addErrorMessages(array $messages)
    {
        foreach ($messages as $message) {
            $this->addErrorMessage($message);
        }
        return $this;
    }

    /**
     * Same as addErrorMessages(), but clears custom error message stack first
     *
     * @param  array $messages
     * @return App_Form
     */
    public function setErrorMessages(array $messages)
    {
        $this->clearErrorMessages();
        return $this->addErrorMessages($messages);
    }

    /**
     * Retrieve custom error messages
     *
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->_errorMessages;
    }

    /**
     * Clear custom error messages stack
     *
     * @return App_Form
     */
    public function clearErrorMessages()
    {
        $this->_errorMessages = array();
        return $this;
    }

    /**
     * Mark the element as being in a failed validation state
     *
     * @return App_Form
     */
    public function markAsError()
    {
        $this->_errorsExist  = true;
        $this->_errorsForced = true;
        return $this;
    }

    /**
     * Add an error message and mark element as failed validation
     *
     * @param  string $message
     * @return App_Form
     */
    public function addError($message)
    {
        $this->addErrorMessage($message);
        $this->markAsError();
        return $this;
    }

    /**
     * Add multiple error messages and flag element as failed validation
     *
     * @param  array $messages
     * @return App_Form
     */
    public function addErrors(array $messages)
    {
        foreach ($messages as $message) {
            $this->addError($message);
        }
        return $this;
    }

    /**
     * Overwrite any previously set error messages and flag as failed validation
     *
     * @param  array $messages
     * @return App_Form
     */
    public function setErrors(array $messages)
    {
        $this->clearErrorMessages();
        return $this->addErrors($messages);
    }

    // Form metadata:

    /**
     * Set form attribute
     *
     * @param  string $key
     * @param  mixed $value
     * @return App_Form
     */
    public function setAttrib($key, $value)
    {
        $key = (string) $key;
        $this->_attribs[$key] = $value;
        return $this;
    }

    /**
     * Add multiple form attributes at once
     *
     * @param  array $attribs
     * @return App_Form
     */
    public function addAttribs(array $attribs)
    {
        foreach ($attribs as $key => $value) {
            $this->setAttrib($key, $value);
        }
        return $this;
    }

    /**
     * Set multiple form attributes at once
     *
     * Overwrites any previously set attributes.
     *
     * @param  array $attribs
     * @return App_Form
     */
    public function setAttribs(array $attribs)
    {
        $this->clearAttribs();
        return $this->addAttribs($attribs);
    }

    /**
     * Retrieve a single form attribute
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttrib($key)
    {
        $key = (string) $key;
        if (!isset($this->_attribs[$key])) {
            return null;
        }

        return $this->_attribs[$key];
    }

    /**
     * Retrieve all form attributes/metadata
     *
     * @return array
     */
    public function getAttribs()
    {
        return $this->_attribs;
    }

    /**
     * Remove attribute
     *
     * @param  string $key
     * @return bool
     */
    public function removeAttrib($key)
    {
        if (isset($this->_attribs[$key])) {
            unset($this->_attribs[$key]);
            return true;
        }

        return false;
    }

    /**
     * Clear all form attributes
     *
     * @return App_Form
     */
    public function clearAttribs()
    {
        $this->_attribs = array();
        return $this;
    }

    /**
     * Set form action
     *
     * @param  string $action
     * @return App_Form
     */
    public function setAction($action)
    {
        return $this->setAttrib('action', (string) $action);
    }

    /**
     * Get form action
     *
     * Sets default to '' if not set.
     *
     * @return string
     */
    public function getAction()
    {
        $action = $this->getAttrib('action');
        if (null === $action) {
            $action = '';
            $this->setAction($action);
        }
        return $action;
    }

    /**
     * Set form method
     *
     * Only values in {@link $_methods()} allowed
     *
     * @param  string $method
     * @return App_Form
     * @throws App_Form_Exception
     */
    public function setMethod($method)
    {
        $method = strtolower($method);
        if (!in_array($method, $this->_methods)) {
            throw new App_Form_Exception(sprintf('"%s" is an invalid form method', $method));
        }
        $this->setAttrib('method', $method);
        return $this;
    }

    /**
     * Retrieve form method
     *
     * @return string
     */
    public function getMethod()
    {
        if (null === ($method = $this->getAttrib('method'))) {
            $method = self::METHOD_POST;
            $this->setAttrib('method', $method);
        }
        return strtolower($method);
    }

    /**
     * Set encoding type
     *
     * @param  string $value
     * @return App_Form
     */
    public function setEnctype($value)
    {
        $this->setAttrib('enctype', $value);
        return $this;
    }

    /**
     * Get encoding type
     *
     * @return string
     */
    public function getEnctype()
    {
        if (null === ($enctype = $this->getAttrib('enctype'))) {
            $enctype = self::ENCTYPE_URLENCODED;
            $this->setAttrib('enctype', $enctype);
        }
        return $this->getAttrib('enctype');
    }


    /**
     * When calling renderFormElements or render this method
     * is used to set $_isRendered member to prevent repeatedly
     * merging belongsTo setting
     */
    protected function _setIsRendered()
    {
        $this->_isRendered = true;
        return $this;
    }

    /**
     * Get the value of $_isRendered member
     */
    protected function _getIsRendered()
    {
        return (bool)$this->_isRendered;
    }
    
    /**
     * Retrieve value for single element
     *
     * @param  string $name
     * @return mixed
     */
    public function getValue($name)
    {
        $element = $this->getElement($name);
        if ($element) {
            return $element->getValue();
        }
        return null;
    }

    /**
     * Retrieve all form element values
     *
     * @param  bool $suppressArrayNotation
     * @return array
     */
    public function getValues($suppressArrayNotation = false)
    {
        $values = array();
        $eBelongTo = null;

        foreach ($this->getElements() as $key => $element) {
            //if (!$element->getIgnore()) {
                $merge = array();
                //if (($belongsTo = $element->getBelongsTo()) !== $eBelongTo) {
                //    if ('' !== (string)$belongsTo) {
                //       $key = $belongsTo . '[' . $key . ']';
                //    }
                //}
                $merge = $this->_attachToArray($element->getValue(), $key);
                $values = $this->_array_replace_recursive($values, $merge);
            //}
        }

        //if (!$suppressArrayNotation &&
            // $this->isArray() &&
            //!$this->_getIsRendered()) {
            //$values = $this->_attachToArray($values, $this->getElementsBelongTo());
        //}

        return $values;
    }

        /**
     * Render form
     *
     * @param  App_View $view
     * @return string
     */
    public function render(App_View $view = null)
    {
        if (null !== $view) {
            $this->setView($view);
        }

        $content = '';
//        foreach ($this->getDecorators() as $decorator) {
//            $decorator->setElement($this);
//            $content = $decorator->render($content);
//        }
//        $this->_setIsRendered();
        return $content;
    }

    /**
     * Serialize as string
     *
     * Proxies to {@link render()}.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $return = $this->render();
            return $return;
        } catch (Exception $e) {
            $message = "Exception caught by form: " . $e->getMessage()
                     . "\nStack Trace:\n" . $e->getTraceAsString();
            trigger_error($message, E_USER_WARNING);
            return '';
        }
    }

    public function init()
    {
        
    }
}