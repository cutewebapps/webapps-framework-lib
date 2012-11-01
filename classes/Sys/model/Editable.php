<?php

class Sys_Editable {
    /**
     * @var array
     */
    protected $_data = array();

    /**
     * Retrieve a value and return $default if there is no element set.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null) {
        $result = $default;
        if (array_key_exists($name, $this->_data)) {
            $result = $this->_data[$name];
        }
        return $result;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        return $this->get($name);
    }

    /**
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function __set($name, $value) {
        $this->_data[$name] = $value;
    }

    /**
     * @return array
     */
    public function toArray() {
        return $this->_data;
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->_data = array();
    }

    public function setup( array $arrData )
    {
        foreach( $arrData as $strKey => $value  )
            $this->_data[ $strKey ] = $value;
    }
}
 
