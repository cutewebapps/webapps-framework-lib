<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */

/**
 * Based on Zend Framework                                                                                                  
 *                                                                                                                 
 * LICENSE                                                                                                         
 *                                                                                                                 
 * This source file is subject to the new BSD license that is bundled                                              
 * with this package in the file LICENSE.txt.                                                                      
 * It is also available through the world-wide-web at this URL:                                                    
 * http://framework.zend.com/license/new-bsd                                                                       
 * If you did not receive a copy of the license and are unable to                                                  
 * obtain it through the world-wide-web, please send an email                                                      
 * to license@zend.com so we can send you a copy immediately.                                                      
 *                                                                                                                 
 * @category   Zend                                                                                                
 * @package    Zend_InfoCard                                                                                       
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)                            
 * @license    http://framework.zend.com/license/new-bsd     New BSD License                                       
 * @version    $Id: InfoCard.php 20096 2010-01-06 02:05:09Z bkarwin $                                              
 */
abstract class DBx_Adapter_Abstract
{
    /**
     * Fetch mode
     *
     * @var integer
     */
    protected $_fetchMode = DBx_Registry::FETCH_ASSOC;
    
    /**
     * User-provided configuration
     *
     * @var array
     */
    protected $_config = array();

    /**
     * Query profiler object, of type DBx_Profiler
     * or a subclass of that.
     *
     * @var DBx_Profiler
     */
    protected $_profiler;

    /**
     * Default class name for the profiler object.
     *
     * @var string
     */
    protected $_defaultProfilerClass = 'DBx_Profiler';

    /**
     * Database connection
     *
     * @var object|resource|null
     */
    protected $_connection = null;

    /**
     * Specifies the case of column names retrieved in queries
     * Options
     * DBx_Registry::CASE_NATURAL (default)
     * DBx_Registry::CASE_LOWER
     * DBx_Registry::CASE_UPPER
     *
     * @var integer
     */
    protected $_caseFolding = DBx_Registry::CASE_NATURAL;

    /**
     * Specifies whether the adapter automatically quotes identifiers.
     * If true, most SQL generated by DBx_Registry classes applies
     * identifier quoting automatically.
     * If false, developer must quote identifiers themselves
     * by calling quoteIdentifier().
     *
     * @var bool
     */
    protected $_autoQuoteIdentifiers = true;

    /**
     * Keys are UPPERCASE SQL datatypes or the constants
     * DBx_Registry::INT_TYPE, DBx_Registry::BIGINT_TYPE, or DBx_Registry::FLOAT_TYPE.
     *
     * Values are:
     * 0 = 32-bit integer
     * 1 = 64-bit integer
     * 2 = float or decimal
     *
     * @var array Associative array of datatypes to values 0, 1, or 2.
     */
    protected $_numericDataTypes = array(
        DBx_Registry::INT_TYPE    => DBx_Registry::INT_TYPE,
        DBx_Registry::BIGINT_TYPE => DBx_Registry::BIGINT_TYPE,
        DBx_Registry::FLOAT_TYPE  => DBx_Registry::FLOAT_TYPE
    );

    /** Weither or not that object can get serialized
     *
     * @var bool
     */
    protected $_allowSerialization = true;

    /**
     * Weither or not the database should be reconnected
     * to that adapter when waking up
     *
     * @var bool
     */
    protected $_autoReconnectOnUnserialize = false;


    public function setConnection( DBx_Adapter_Abstract $copy )
    {
        $this->_connection = $copy->getConnection();
    }
    /**
     * Constructor.
     *
     * $config is an array of key/value pairs or an instance of Zend_Config
     * containing configuration options.  These options are common to most adapters:
     *
     * dbname         => (string) The name of the database to user
     * username       => (string) Connect to the database as this username.
     * password       => (string) Password associated with the username.
     * host           => (string) What host to connect to, defaults to localhost
     *
     * Some options are used on a case-by-case basis by adapters:
     *
     * port           => (string) The port of the database
     * persistent     => (boolean) Whether to use a persistent connection or not, defaults to false
     * protocol       => (string) The network protocol, defaults to TCPIP
     * caseFolding    => (int) style of case-alteration used for identifiers
     *
     * @param  array|Zend_Config $config An array or instance of Zend_Config having configuration data
     * @throws DBx_Adapter_Exception
     */
    public function __construct($config)
    {
        /*
         * Verify that adapter parameters are in an array.
         */
        if (!is_array($config)) {
            throw new DBx_Adapter_Exception('Adapter parameters must be in an array');
        }

        $this->_checkRequiredOptions($config);

        $options = array(
            DBx_Registry::CASE_FOLDING           => $this->_caseFolding,
            DBx_Registry::AUTO_QUOTE_IDENTIFIERS => $this->_autoQuoteIdentifiers,
            // DBx_Registry::FETCH_MODE             => $this->_fetchMode,
        );
        $driverOptions = array();

        /*
         * normalize the config and merge it with the defaults
         */
        if (array_key_exists('options', $config)) {
            // can't use array_merge() because keys might be integers
            foreach ((array) $config['options'] as $key => $value) {
                $options[$key] = $value;
            }
        }
        if (array_key_exists('driver_options', $config)) {
            if (!empty($config['driver_options'])) {
                // can't use array_merge() because keys might be integers
                foreach ((array) $config['driver_options'] as $key => $value) {
                    $driverOptions[$key] = $value;
                }
            }
        }

        if (!isset($config['charset'])) {
            $config['charset'] = null;
        }

        if (!isset($config['persistent'])) {
            $config['persistent'] = false;
        }

        $this->_config = array_merge($this->_config, $config);
        $this->_config['options'] = $options;
        $this->_config['driver_options'] = $driverOptions;


        // obtain the case setting, if there is one
        if (array_key_exists(DBx_Registry::CASE_FOLDING, $options)) {
            $case = (int) $options[DBx_Registry::CASE_FOLDING];
            switch ($case) {
                case DBx_Registry::CASE_LOWER:
                case DBx_Registry::CASE_UPPER:
                case DBx_Registry::CASE_NATURAL:
                    $this->_caseFolding = $case;
                    break;
                default:
                    /** @see DBx_Adapter_Exception */
                    throw new DBx_Adapter_Exception('Case must be one of the following constants: '
                        . 'DBx_Registry::CASE_NATURAL, DBx_Registry::CASE_LOWER, DBx_Registry::CASE_UPPER');
            }
        }

        // obtain quoting property if there is one
        if (array_key_exists(DBx_Registry::AUTO_QUOTE_IDENTIFIERS, $options)) {
            $this->_autoQuoteIdentifiers = (bool) $options[DBx_Registry::AUTO_QUOTE_IDENTIFIERS];
        }

        // obtain allow serialization property if there is one
        if (array_key_exists(DBx_Registry::ALLOW_SERIALIZATION, $options)) {
            $this->_allowSerialization = (bool) $options[DBx_Registry::ALLOW_SERIALIZATION];
        }

        // obtain auto reconnect on unserialize property if there is one
        if (array_key_exists(DBx_Registry::AUTO_RECONNECT_ON_UNSERIALIZE, $options)) {
            $this->_autoReconnectOnUnserialize = (bool) $options[DBx_Registry::AUTO_RECONNECT_ON_UNSERIALIZE];
        }

        // create a profiler object
        $profiler = false;
        if (array_key_exists(DBx_Registry::PROFILER, $this->_config)) {
            $profiler = $this->_config[DBx_Registry::PROFILER];
            unset($this->_config[DBx_Registry::PROFILER]);
        }
        $this->setProfiler($profiler);
    }

    /**
     * Check for config options that are mandatory.
     * Throw exceptions if any are missing.
     *
     * @param array $config
     * @throws DBx_Adapter_Exception
     */
    protected function _checkRequiredOptions(array $config)
    {
        // we need at least a dbname
        if (! array_key_exists('dbname', $config)) {
            /** @see DBx_Adapter_Exception */
            throw new DBx_Adapter_Exception("Configuration array must have a key for 'dbname' that names the database instance");
        }

        if (! array_key_exists('password', $config)) {
            /**
             * @see DBx_Adapter_Exception
             */
            throw new DBx_Adapter_Exception("Configuration array must have a key for 'password' for login credentials");
        }

        if (! array_key_exists('username', $config)) {
            /**
             * @see DBx_Adapter_Exception
             */
            throw new DBx_Adapter_Exception("Configuration array must have a key for 'username' for login credentials");
        }
    }

    /**
     * Returns the underlying database connection object or resource.
     * If not presently connected, this initiates the connection.
     *
     * @return object|resource|null
     */
    public function getConnection()
    {
        $this->_connect();
        return $this->_connection;
    }

    /**
     * Returns the configuration variables in this adapter.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Set the adapter's profiler object.
     *
     * The argument may be a boolean, an associative array, an instance of
     * DBx_Profiler, or an instance of Zend_Config.
     *
     * A boolean argument sets the profiler to enabled if true, or disabled if
     * false.  The profiler class is the adapter's default profiler class,
     * DBx_Profiler.
     *
     * An instance of DBx_Profiler sets the adapter's instance to that
     * object.  The profiler is enabled and disabled separately.
     *
     * An associative array argument may contain any of the keys 'enabled',
     * 'class', and 'instance'. The 'enabled' and 'instance' keys correspond to the
     * boolean and object types documented above. The 'class' key is used to name a
     * class to use for a custom profiler. The class must be DBx_Profiler or a
     * subclass. The class is instantiated with no constructor arguments. The 'class'
     * option is ignored when the 'instance' option is supplied.
     *
     * An object of type Zend_Config may contain the properties 'enabled', 'class', and
     * 'instance', just as if an associative array had been passed instead.
     *
     * @param  DBx_Profiler|Zend_Config|array|boolean $profiler
     * @return DBx_Adapter_Abstract Provides a fluent interface
     * @throws DBx_Profiler_Exception if the object instance or class specified
     *         is not DBx_Profiler or an extension of that class.
     */
    public function setProfiler($profiler)
    {
        $enabled          = null;
        $profilerClass    = $this->_defaultProfilerClass;
        $profilerInstance = null;
        $profilerIsObject = is_object($profiler);
        if ( $profilerIsObject ) {
            if ($profiler instanceof DBx_Profiler) {
                $profilerInstance = $profiler;
            } else {
                throw new DBx_Profiler_Exception('Profiler argument must be an instance of either DBx_Profiler'
                    . ' or Zend_Config when provided as an object');
            }
        }

        if (is_array($profiler)) {
            if (isset($profiler['enabled'])) {
                $enabled = (bool) $profiler['enabled'];
            }
            if (isset($profiler['class'])) {
                $profilerClass = $profiler['class'];
            }
            if (isset($profiler['instance'])) {
                $profilerInstance = $profiler['instance'];
            }
        } else if (!$profilerIsObject) {
            $enabled = (bool) $profiler;
        }

        if ($profilerInstance === null) {
            $profilerInstance = new $profilerClass();
        }
        if (!$profilerInstance instanceof DBx_Profiler) {
             throw new DBx_Profiler_Exception('Class ' . get_class($profilerInstance) . ' does not extend '
                . 'DBx_Profiler');
        }

        if (null !== $enabled) {
            $profilerInstance->setEnabled($enabled);
        }

        $this->_profiler = $profilerInstance;
        return $this;
    }


    /**
     * Returns the profiler for this adapter.
     *
     * @return DBx_Profiler
     */
    public function getProfiler()
    {
        return $this->_profiler;
    }

    /**
     * Get the default statement class.
     *
     * @return string
     */
    public function getStatementClass()
    {
        return $this->_defaultStmtClass;
    }

    /**
     * Set the default statement class.
     *
     * @return DBx_Adapter_Abstract Fluent interface
     */
    public function setStatementClass($class)
    {
        $this->_defaultStmtClass = $class;
        return $this;
    }

    /**
     * Prepares and executes an SQL statement with bound data.
     *
     * @param  mixed  $sql  The SQL statement with placeholders.
     *                      May be a string or DBx_Select.
     * @param  mixed  $bind An array of data to bind to the placeholders.
     * @return DBx_Statement_Interface
     */
    protected function _query($sql, $bind = array())
    {
        // connect to the database if needed
        $this->_connect();

        // is the $sql a DBx_Select object?
        if ($sql instanceof DBx_Select) {
            if (empty($bind)) {
                $bind = $sql->getBind();
            }

            $sql = $sql->assemble();
        }

        // make sure $bind to an array;
        // don't use (array) typecasting because
        // because $bind may be a DBx_Expr object
        if (!is_array($bind)) {
            $bind = array($bind);
        }

        // prepare and execute the statement with profiling
        $stmt = $this->prepare($sql);
        $stmt->execute($bind);

        // return the results embedded in the prepared statement object
        $stmt->setFetchMode($this->_fetchMode);
        return $stmt;
    }


    /**
     * Quote a raw string.
     *
     * @param string $value     Raw string
     * @return string           Quoted string
     */
    protected function _quote($value)
    {
        if (is_int($value)) {
            return $value;
        } elseif (is_float($value)) {
            return sprintf('%F', $value);
        }
        return "'" . addcslashes($value, "\000\n\r\\'\"\032") . "'";
    }

    /**
     * Safely quotes a value for an SQL statement.
     *
     * If an array is passed as the value, the array values are quoted
     * and then returned as a comma-separated string.
     *
     * @param mixed $value The value to quote.
     * @param mixed $type  OPTIONAL the SQL datatype name, or constant, or null.
     * @return mixed An SQL-safe quoted value (or string of separated values).
     */
    public function quote($value, $type = null)
    {
        $this->_connect();

        if ($value instanceof DBx_Select) {
            return '(' . $value->assemble() . ')';
        }

        if ($value instanceof DBx_Expr) {
            return $value->__toString();
        }

        if (is_array($value)) {
            foreach ($value as &$val) {
                $val = $this->quote($val, $type);
            }
            return implode(', ', $value);
        }

        if ($type !== null && array_key_exists($type = strtoupper($type), $this->_numericDataTypes)) {
            $quotedValue = '0';
            switch ($this->_numericDataTypes[$type]) {
                case DBx_Registry::INT_TYPE: // 32-bit integer
                    $quotedValue = (string) intval($value);
                    break;
                case DBx_Registry::BIGINT_TYPE: // 64-bit integer
                    // ANSI SQL-style hex literals (e.g. x'[\dA-F]+')
                    // are not supported here, because these are string
                    // literals, not numeric literals.
                    $matches = array();
                    if (preg_match('/^(
                          [+-]?                  # optional sign
                          (?:
                            0[Xx][\da-fA-F]+     # ODBC-style hexadecimal
                            |\d+                 # decimal or octal, or MySQL ZEROFILL decimal
                            (?:[eE][+-]?\d+)?    # optional exponent on decimals or octals
                          )
                        )/x',
                        (string) $value, $matches)) {
                        $quotedValue = $matches[1];
                    }
                    break;
                case DBx_Registry::FLOAT_TYPE: // float or decimal
                    $quotedValue = sprintf('%F', $value);
            }
            return $quotedValue;
        }

        return $this->_quote($value);
    }

    /**
     * Quotes a value and places into a piece of text at a placeholder.
     *
     * The placeholder is a question-mark; all placeholders will be replaced
     * with the quoted value.   For example:
     *
     * <code>
     * $text = "WHERE date < ?";
     * $date = "2005-01-02";
     * $safe = $sql->quoteInto($text, $date);
     * // $safe = "WHERE date < '2005-01-02'"
     * </code>
     *
     * @param string  $text  The text with a placeholder.
     * @param mixed   $value The value to quote.
     * @param string  $type  OPTIONAL SQL datatype
     * @param integer $count OPTIONAL count of placeholders to replace
     * @return string An SQL-safe quoted value placed into the original text.
     */
    public function quoteInto($text, $value, $type = null, $count = null)
    {
        if ($count === null) {
            return str_replace('?', $this->quote($value, $type), $text);
        } else {
            while ($count > 0) {
                if (strpos($text, '?') !== false) {
                    $text = substr_replace($text, $this->quote($value, $type), strpos($text, '?'), 1);
                }
                --$count;
            }
            return $text;
        }
    }

    /**
     * Quotes an identifier.
     *
     * Accepts a string representing a qualified indentifier. For Example:
     * <code>
     * $adapter->quoteIdentifier('myschema.mytable')
     * </code>
     * Returns: "myschema"."mytable"
     *
     * Or, an array of one or more identifiers that may form a qualified identifier:
     * <code>
     * $adapter->quoteIdentifier(array('myschema','my.table'))
     * </code>
     * Returns: "myschema"."my.table"
     *
     * The actual quote character surrounding the identifiers may vary depending on
     * the adapter.
     *
     * @param string|array|DBx_Expr $ident The identifier.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string The quoted identifier.
     */
    public function quoteIdentifier($ident, $auto=false)
    {
        return $this->_quoteIdentifierAs($ident, null, $auto);
    }

    /**
     * Quote a column identifier and alias.
     *
     * @param string|array|DBx_Expr $ident The identifier or expression.
     * @param string $alias An alias for the column.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string The quoted identifier and alias.
     */
    public function quoteColumnAs($ident, $alias, $auto=false)
    {
        return $this->_quoteIdentifierAs($ident, $alias, $auto);
    }

    /**
     * Quote a table identifier and alias.
     *
     * @param string|array|DBx_Expr $ident The identifier or expression.
     * @param string $alias An alias for the table.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string The quoted identifier and alias.
     */
    public function quoteTableAs($ident, $alias = null, $auto = false)
    {
        return $this->_quoteIdentifierAs($ident, $alias, $auto);
    }

    /**
     * Quote an identifier and an optional alias.
     *
     * @param string|array|DBx_Expr $ident The identifier or expression.
     * @param string $alias An optional alias.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @param string $as The string to add between the identifier/expression and the alias.
     * @return string The quoted identifier and alias.
     */
    protected function _quoteIdentifierAs($ident, $alias = null, $auto = false, $as = ' AS ')
    {
        $quoted  = '';
        if ($ident instanceof DBx_Expr) {
            $quoted = $ident->__toString();
        } elseif ($ident instanceof DBx_Select) {
            $quoted = '(' . $ident->assemble() . ')';
        } else {
            if (is_string($ident)) {
                $ident = explode('.', $ident);
            }
            if (is_array($ident)) {
                $segments = array();
                foreach ($ident as $segment) {
                    if ($segment instanceof DBx_Expr) {
                        $segments[] = $segment->__toString();
                    } else {
                        $segments[] = $this->_quoteIdentifier($segment, $auto);
                    }
                }
                if ($alias !== null && end($ident) == $alias) {
                    $alias = null;
                }
                $quoted = implode('.', $segments);
            } else {
                $quoted = $this->_quoteIdentifier($ident, $auto);
            }
        }
        if ($alias !== null) {
            $quoted .= $as . $this->_quoteIdentifier($alias, $auto);
        }
        return $quoted;
    }

    /**
     * Quote an identifier.
     *
     * @param  string $value The identifier or expression.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string        The quoted identifier and alias.
     */
    protected function _quoteIdentifier($value, $auto=false)
    {
        if ($auto === false || $this->_autoQuoteIdentifiers === true) {
            $q = $this->getQuoteIdentifierSymbol();
            return ($q . str_replace("$q", "$q$q", $value) . $q);
        }
        return $value;
    }

    /**
     * Returns the symbol the adapter uses for delimited identifiers.
     *
     * @return string
     */
    public function getQuoteIdentifierSymbol()
    {
        return '"';
    }

    /**
     * Helper method to change the case of the strings used
     * when returning result sets in FETCH_ASSOC and FETCH_BOTH
     * modes.
     *
     * This is not intended to be used by application code,
     * but the method must be public so the Statement class
     * can invoke it.
     *
     * @param string $key
     * @return string
     */
    public function foldCase($key)
    {
        $value = '';
        switch ($this->_caseFolding) {
            case DBx_Registry::CASE_LOWER:
                $value = strtolower((string) $key);
                break;
            case DBx_Registry::CASE_UPPER:
                $value = strtoupper((string) $key);
                break;
            case DBx_Registry::CASE_NATURAL:
            default:
                $value = (string) $key;
        }
        return $value;
    }

    /**
     * called when object is getting serialized
     * This disconnects the DB object that cant be serialized
     *
     * @throws DBx_Adapter_Exception
     * @return array
     */
    public function __sleep()
    {
        if ($this->_allowSerialization == false) {
            /** @see DBx_Adapter_Exception */
            throw new DBx_Adapter_Exception(get_class($this) ." is not allowed to be serialized");
        }
        $this->_connection = false;
        return array_keys(array_diff_key(get_object_vars($this), array('_connection'=>false)));
    }

    /**
     * called when object is getting unserialized
     *
     * @return void
     */
    public function __wakeup()
    {
        if ($this->_autoReconnectOnUnserialize == true) {
            $this->getConnection();
        }
    }

    /**
     * Abstract Methods
     */

    /**
     * Creates a connection to the database.
     *
     * @return void
     */
    abstract protected function _connect();

    /**
     * Test if a connection is active
     *
     * @return boolean
     */
    abstract public function isConnected();

    /**
     * Force the connection to close.
     *
     * @return void
     */
    abstract public function closeConnection();

}
