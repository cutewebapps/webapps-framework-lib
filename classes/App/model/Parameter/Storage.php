<?php

class App_Parameter_Storage
{
    protected $_arrParams   = array();

    /**
     * @param array $arrParams
     */
    public function __construct( $arrParams = array() )
    {
        $this->_arrParams = $arrParams;
    }
    /**
     *
     * @param string $strParam
     * @return boolean
     */
    protected function _hasParam( $strParam )
    {
        if ( is_array( $strParam ) ) {
            // if we have array on input - every param must match
            foreach( $strParam as $scalarValue ) {
                if ( !isset( $this->_arrParams[ $scalarValue ] ) ) { return false; }
            }
            return true;
        }
        return isset( $this->_arrParams[ $strParam ]  );
    }
    /**
     *
     * @param string $strParam
     * @return boolean
     */
    public function hasParam( $strParam )
    {
        return $this->_hasParam( $strParam );
    }

    /**
     *
     * @param string $strParam
     * @param mixed $value
     * @return App_AbstractCtrl
     */
    protected function _setParam( $strParam, $value )
    {
        $this->_arrParams[ $strParam ] = $value;
        return $this;
    }
    /**
     *
     * @param string $strParam
     * @param mixes $value
     * @return App_AbstractCtrl
     */
    public function setParam( $strParam, $value )
    {
        return $this->_setParam( $strParam, $value );
    }


    /**
     *
     * @param string  $strParam
     * @param mixed $strDefault
     * @return mixed
     */
    protected function _getParam( $strParam, $strDefault = '' )
    {
        if ( $strParam == ''  || $strParam == null ) {
            throw new App_Exception( 'Trying to query invalid param' );
        }
        return $this->_hasParam( $strParam ) ? $this->_arrParams[ $strParam ] : $strDefault;
    }
    /**
     *
     * @param string $strParam
     * @param mixed $strDefault
     * @return mixed
     */
    public function getParam( $strParam, $strDefault = '' )
    {
        return $this->_getParam( $strParam, $strDefault );
    }

    /**
     *
     * @param string $strParam
     * @param int $strDefault
     * @return int
     */
    protected function _getIntParam( $strParam, $strDefault = 0 )
    {
        if ( !$this->_hasParam( $strParam ) )
            return $strDefault;
        switch ( strtolower( $this->_arrParams[ $strParam ] ) ) {
            case 'false': return 0;
            case 'true':  return 1;
        }

        return intval( $this->_arrParams[ $strParam ] );
    }

    /**
     *
     * @param string $strParam
     * @param mixed $strDefault
     * @return int
     */
    public function getIntParam( $strParam, $strDefault = '' ) { return $this->_getIntParam( $strParam, $strDefault ); }
    /**
     * @return int 0 or 1
     * @param string $strParam
     * @param int $intDefault
     */
    protected function _getBoolParam( $strParam, $intDefault = 0 )
    {

        switch ( strtolower( $this->_getParam( $strParam ) ) ) {
            case 1:
            case 'true':
            case 'on':
                return 1;
            case 0:
            case 'false':
            case 'off':
                return 0;
            default:
                return $intDefault;
        }
    }

    /**
     *
     * @return array
     */
    protected function _getAllParams()
    {
        return $this->_arrParams;
    }

    /**
     *
     * @return array
     */
    public function getAllParams()
    {
        return $this->_getAllParams();
    }

    /**
     * @param string $strParam
     */
    protected function _adjustIntParam( $strParam )
    {

        if ( $this->hasParam( $strParam ) ) {
            if ( is_array( $strParam ) ) {
                foreach( $strParam as $sParam ) {
                    $this->_adjustIntParam( $sParam );
                }
            } elseif ( $this->_getParam( $strParam ) == 'true' ) {
                $this->_setParam( $strParam, 1 );
            } else if ( $this->_getParam( $strParam ) == 'false' ) {
                $this->_setParam( $strParam, 0 );
            } else if ( $this->_getParam( $strParam ) == 'on' ) {
                $this->_setParam( $strParam, 1 );
            } else if ( $this->_getParam( $strParam ) == 'off' ) {
                $this->_setParam( $strParam, 0 );
            }
        }
    }

    /**
     * Adjust Date Parameter - that is present in the request
     *
     * @param string $strParam
     * @throws Sys_Date_Exception
     */
    protected function _adjustDateParam( $strParam )
    {
        $format = App_Application::getInstance()->getConfig()->dateformat;
        if ( !$format ) {
            throw new App_Exception( 'dateformat was not configured for this application' );
        }

        if ( is_array( $strParam ) ) {
            foreach(  $strParam  as $sParam ) {
                $this->_adjustDateParam( $sParam );
            }
        } else {
            $dt = new Sys_Date( $this->_getParam( $strParam ), $format );
            $this->_setParam( $strParam, $dt->getDate( Sys_Date::ISO ));
        }
    }

    /**
     * @param string $strParam
     * @throws Sys_Date_Exception
     */
    protected function _adjustDateTimeParam( $strParam )
    {
        $format = App_Application::getInstance()->getConfig()->dateformat;
        if ( !$format ) {
            throw new App_Exception( 'dateformat was not configured for this application' );
        }

        if ( is_array( $strParam ) ) {
            foreach( $strParam as $sParam ) {
                $this->_adjustDateTimeParam( $sParam );
            }
        } else {

            $dt = new Sys_Date( $this->_getParam( $strParam ), $format );
            $this->_setParam( $strParam, $dt->getDate( Sys_Date::ISO ).' '.$dt->getTime24());
        }
    }


    protected function _require( $arrConfiguration )
    {
        $arrErrors = array();
        $arrPushed = array();
        foreach ( $arrConfiguration as $arrParam ) {
            if ( ! isset( $arrParam['field'] ) ) {
                throw new App_Exception('field expected in require configuration');
            }
            $field = $arrParam['field'];
            if ( isset( $arrPushed[ $field ] )) continue; // do not push errors second time for the same fields

            $strMethod = isset( $arrParam['method'] ) ? $arrParam['method'] : '';
            $strMessage = isset( $arrParam['message'] ) ? $arrParam['message'] : '';
            $val  = trim( $this->_getParam($field ) );

            switch ( $strMethod ) {
                case '':
                    // require non-empty value
                    if ( $val == '' ) {

                        $bCheck = true;
                        if ( isset( $arrParam['if'] ) ) $bCheck  = $arrParam['if'];

                        if ( $bCheck ) {
                            array_push( $arrErrors, array( $field => $strMessage ) ) ;
                            $arrPushed[ $field ] = 1;
                        }
                    }
                    break;
                case 'date':
                    // require non-empty value
                    try{
                        $this->_adjustDateParam( $field );
                    } catch ( Sys_Date_Exception $e ) {
                        array_push( $arrErrors, array( $field => $e->getMessage() ) ) ;
                        $arrPushed[ $field ] = 1;
                    }
                    break;
                case 'datetime':
                    // require non-empty value
                    try{
                        $this->_adjustDateTimeParam( $field );
                    } catch ( Sys_Date_Exception $e ) {
                        array_push( $arrErrors, array( $field => $e->getMessage() ) ) ;
                        $arrPushed[ $field ] = 1;
                    }
                    break;
                case 'overzero':
                    $bCondition = ( $val < 0 );

                    if ( $bCondition ) {
                        array_push( $arrErrors, array( $field => $strMessage ) ) ;
                        $arrPushed[ $field ] = 1;
                    }
                    break;

                case 'min':
                    if ( ! isset( $arrParam['value'] ) )
                        throw new App_Exception('value expected in require configuration');
                    $bCondition = (double)$val <= (double)$arrParam['value'];

                    if ( isset( $arrParam['equal'] ) )
                        $bCondition = (double)$val < (double)$arrParam['value'];

                    if ( $bCondition ) {
                        array_push( $arrErrors, array( $field => $strMessage ) ) ;
                        $arrPushed[ $field ] = 1;
                    }
                    break;

                case 'max':
                    if ( ! isset( $arrParam['value'] ) )
                        throw new App_Exception('value expected in require configuration');

                    $bCondition = (double)$val >= (double)$arrParam['value'];
                    if ( isset( $arrParam['equal'] ) )
                        $bCondition = (double)$val > (double)$arrParam['value'];
                    if ( $bCondition ) {
                        array_push( $arrErrors, array( $field => $strMessage ) ) ;
                        $arrPushed[ $field ] = 1;
                    }
                    break;

                case 'email':
                    if ( !Sys_String::isEmail( $val ) ) {
                        array_push( $arrErrors, array( $field => $strMessage ) ) ;
                        $arrPushed[ $field ] = 1;
                    }
                    break;
            }
        }
        return $arrErrors;
    }
}
