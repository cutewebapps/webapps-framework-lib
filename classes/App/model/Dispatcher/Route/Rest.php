<?php
/**
 * COMPACT WAY of Having RESTful routes
 * 
 * Example in routes configuration: 
 * 
 * $appRoute = new App_Dispatcher_Route_Rest();
 * return $appRoute->setPath( 'users' )->setSection( 'backend' )->setModule( 'user' )->setController( 'account' )->getAsArray();
 * 
 * Or you can have multiple REST models declared
 * 
 * return  App_Dispatcher_Route_Rest::Table( array( 
 *    array( 'path' => 'users', 'section' => 'backend', 'module'=> 'user', 'controller' => 'account'),
 *    array( 'path' => 'roles', 'section' => 'backend', 'module'=> 'user', 'controller' => 'role'),
 * ) );

 */

class App_Dispatcher_Route_Rest extends App_Parameter_Storage
{
    public function setPath( $sPath ) { $this->_setParam('path', $sPath );  return $this; }
    public function setSection( $sValue ) { $this->_setParam('section', $sValue ); return $this; }
    public function setModule( $sValue ) { $this->_setParam('module', $sValue );  return $this; }
    public function setController( $sValue ) { $this->_setParam('controller', $sValue );  return $this; }
           
    /**
     * @param array $arrRoutes
     * @return array
     */
    public static function Table( array $arrRoutes = array() )
    {
        $arrResults = array();
        foreach ( $arrRoutes as $arrRouteParams ) {
             $appRoute = new App_Dispatcher_Route_Rest( $arrRouteParams );
             $arrResults []= $appRoute->getAsArray();
        }
        return $arrResults;
    }
    
    /**
     * @return array
     */
    public function getAsArray()
    {
        return array(
            array(
                'route' => '/'.$this->getParam('path').'/all',
                'defaults' => array( 
                    'module' => $this->getParam('module'), 
                    'controller' => $this->getParam('controller'), 
                    'action' => 'getlist', 
                    'section' => $this->getParam('section'), 
                    'format' => 'json', 
                    'results' => 1000000 ),
            ),
            array(
                'route' => '/'.$this->getParam('path').'/new',
                'defaults' => array(
                    'module' => $this->getParam('module'), 
                    'controller' => $this->getParam('controller'), 
                    'action' => 'edit', 
                    'section' =>  $this->getParam('section'), 
                    'format' => 'json', ),
            ),
            array(
                'type' => 'regex',
                'route' => '/'.$this->getParam('path').'/(\d+)/remove$',
                'defaults' => array( 
                    'module' => $this->getParam('module'), 
                    'controller' => $this->getParam('controller'),
                    'action' => 'delete',
                    'section' =>  $this->getParam('section'), 
                    'format' => 'json' ),
                'map' => array( 1 => '_id' )
            ),
            array(
                'type' => 'regex',
                'route' => '/'.$this->getParam('path').'/(\d+)$',
                'defaults' => array( 
                    'module' => $this->getParam('module'), 
                    'controller' => $this->getParam('controller'), 
                    'action' => 'edit', 
                    'section' =>  $this->getParam('section'), 
                    'format' => 'json', ),
                'map' => array( 1 => '_id' )
            ),
            array(
                'method' => 'DELETE',
                'type' => 'regex',
                'route' => '/'.$this->getParam('path').'/(\d+)$',
                'defaults' => array( 
                    'module' => $this->getParam('module'), 
                    'controller' => $this->getParam('controller'), 
                    'action' => 'delete', 
                    'section' =>  $this->getParam('section'), 
                    'format' => 'json', ),
                'map' => array( 1 => '_id' )
            ),
            array(
                'method' => 'PUT',
                'route' => '/'.$this->getParam('path').'',
                'defaults' => array( 
                    'module' => $this->getParam('module'), 
                    'controller' => $this->getParam('controller'), 
                    'action' => 'edit', 
                    'section' =>  $this->getParam('section'), 
                    'format' => 'json', ),
            )
        );
    }
    
}