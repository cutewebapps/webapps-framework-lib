<?php
/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 * 
 * Licensed under GPL, Free for usage and redistribution.
 */
abstract class App_DbTableCtrl extends App_AbstractCtrl
{
     /**
     * Model to controller
     *
     * @var DBx_Table
     */
    protected $_model = null;

     /**
     * Model name
     *
     * @var string
     */
    protected $_modelName = null;

     /**
     * Meta data from model db table
     *
     * @var array
     */
    protected $_modelMetaData = null;

     /**
     * Select from model db table
     *
     * @var DBx_Table_Select
     */
    protected $_select = null;

     /**
     * Select from model db table, using in getlist action for obtain
     * objects count
     * @var DBx_Table_Select
     */
    protected $_selectCount = null;

     /**
     * Documentation
     * @var DBx_Table_Row
     */
    protected $_object = null;

     /**
     * Documentation
     * @var DBx_Table_Rowset
     */
    protected $_list = null;

     /**
     * Documentation
     * @var App_Form
     */
    protected $_objectForm = null;


    protected $_identity = null;

    /**
    * Return Table class name
    * @return string
    */
    protected function _getModelName()
    {

        checkClassIsLoaded( $this->getClassName() );
        return $this->getClassName().'_Table';
    }

    /**
    * @return App_Form
    */
    protected function getFormObject($strName = 'Edit', $strAction = '')
    {
	    checkClassIsLoaded( $this->getClassName() );
        $strFormClass = $this->getClassName() . '_Form_' . $strName;

        /** @var $objForm App_Form */
        $objForm = new $strFormClass;
        if ( $strAction != '' )
            $objForm->setAction( $strAction );
        return $objForm;
    }
    /**
     * joining objects for getAction() and getlistAction()
     */
    protected function _joinTables()
    {
    }

    protected function _filterSort( $paramSort, $paramDir )
    {
        if (is_array($paramSort)) {
            $this->_select->order($paramSort);
        } else {
            $this->_select->order($paramSort . ' ' . $paramDir);
        }
    }

    /**
    * filtering objects for getlistAction()
    * @return void
    */
    protected function _filterList()
    {
        $boolNoFilter = (int)$this->_getParam('nofilter', 0);
	if ($boolNoFilter)  {
            return;
	}

        $this->_objectForm = $this->getFormObject( 'Filter' );
      	$this->_objectForm->createElements();
        $arrParams = $this->_getAllParams();

        $this->view->formFilter = $this->_objectForm;
        if ($this->_objectForm->isValid($arrParams)){
            $arrValues = $this->_objectForm->getValues();
            $this->_objectForm->setDefaults($arrValues);

            foreach ($arrValues as $strFieldName => $strFieldValue) {
                if ($strFieldValue !== '' && $strFieldValue !== null) {

                    $this->_filterField($strFieldName, $strFieldValue);
                }
            }
        }
    }

    /**
     * function for overloading in children
     * @param string $strFieldName
     * @param string $strFieldValue
     */
    protected function _filterField($strFieldName, $strFieldValue)
    {
        $strSQLWherePattern = "`{$strFieldName}` = ?";
        $strSQLWhereArgs = $strFieldValue;
        
        $this->_select->where($strSQLWherePattern, $strSQLWhereArgs);
        $this->_selectCount->where($strSQLWherePattern, $strSQLWhereArgs);
    }

    protected function _filterDate( $strExistingField, $strFieldName, $strFieldValue )
    {
        if ( $strFieldName == $strExistingField.'_from' ) {
            // Sys_Io::out('from');
            $this->_select->where( $strExistingField . ' >= ? ', $strFieldValue );
            $this->_selectCount->where( $strExistingField . ' >= ? ', $strFieldValue );
            return true;
        }
        if ( $strFieldName == $strExistingField.'_to' ) {
            // Sys_Io::out('to');
            $this->_select->where( $strExistingField . ' <= ? ', $strFieldValue );
            $this->_selectCount->where( $strExistingField . ' <= ? ', $strFieldValue );
            return true;
        }
        if ( $strFieldName == $strExistingField.'_date' ) {
            $this->_select->where( 'DATE('.$strExistingField .') = ?', $strFieldValue );
            $this->_selectCount->where( 'DATE( '.$strExistingField . ')= ? ', $strFieldValue );
            return true;
        }
        return false;
    }


    protected function _changeField($strFieldName, $strFieldValue)
    {
        if (!isset($this->_modelMetaData[$strFieldName])){
            return;
        }
        foreach ($this->_list as $object){
            $object->$strFieldName = $strFieldValue;
        }
    }
    protected function _saveEdit()
    {
    	$this->_object->save();
    }

    /**
    * Возвращает общее кол-во строк в выборке
    * @return integer
    */
    protected function _getRowsCount()
    {
        return $this->_model->getAdapterRead()->queryFoundRows();
//	$objSelectCount = $this->_model->select();
//	$objSelectCount->setIntegrityCheck(false);
//	$objSelectCount->from($this->_select, array('count' => new DBx_Expr('COUNT(*)')));
//	$objCount = $this->_model->fetchRow($objSelectCount);
//	return (int)$objCount->count;
    }


    public function init()
    {
        $this->_modelName 	= $this->_getModelName();

        if ( $this->_model == null ) { // allow controller to set this up instead of init
            $this->_model 		= new $this->_modelName;
        }
        $this->_modelMetaData 	= $this->_model->info( DBx_Table::METADATA);

        //if (!$this->getInvokeArg('noViewRenderer')) {
            $this->view->metadata 	= $this->_modelMetaData;
        //}

        $this->_select = $this->_model->select();
        $this->_select->setIntegrityCheck(false);
        $this->_select->from($this->_model);

        $this->_selectCount = $this->_model->select();
        $this->_selectCount->setIntegrityCheck(false);
        $this->_selectCount->from($this->_model, array('count' => new DBx_Expr('COUNT(*)')));

        $this->view->noheaders = 0;
        if ( $this->_getParam('noheaders') != '' )
            $this->view->noheaders = $this->_getIntParam( 'noheaders' );
        $this->view->output = '';
        if ( $this->_getParam('output') != '' )
            $this->view->output = $this->_getParam( 'output' );

    }

    public function getAction()
    {
        $this->init();
        $strIndentity = $this->_model->getIdentityName();

        if ( $this->_identity ) {
            // if identity was set by descendant
            $paramID = $this->_identity;
        } else {
            $paramID = $this->_getParam($strIndentity);
	    if ( $paramID === NULL ){
                throw new App_Exception( 'Param ' . $this->_model->getIdentityName()
                    . ' or should be defined for getAction', 404 );
                }
        }
        $this->_joinTables();
        $this->_select->where($this->_model->getTableName() . '.' . $strIndentity . ' = ?' , $paramID);

        $object = $this->_model->fetchRow($this->_select);
	    if (!is_object($object)){
            throw new App_Exception('Object with ' . $strIndentity . '='
                    . $paramID . ' not found in DB', 404);
        }
	    $this->_object = $object;

	    $this->view->row = $object->toArray();
	    $this->view->object = $object;

        $this->view->default = '';
        if ( $this->_getParam('default') != '' )
            $this->view->default = $this->_getParam( 'default' );
    }

    public function editAction()
    {
        $this->init();
	    $strIndentity = $this->_model->getIdentityName();
	    $paramID = $this->_getParam($strIndentity);

        if (!$paramID){
            $this->_object = $this->_model->createRow();
	} else {
            $this->_object = $this->_model->find($paramID)->current();
            if (!is_object($this->_object)){
                throw new App_Exception('Object with ID=' . $paramID . ' was not found in DB');
            }
	}

        $this->_objectForm = $this->getFormObject('Edit');
        $this->_objectForm->init();
        $this->_objectForm->setDefaults($this->_object->toArray());

        if ($this->_isPost()) {
            $arrMessages = array();
            $arrErrors = array();

            if ($this->_objectForm->isValid( $this->_getAllParams() )){

                $arrayElements = $this->_objectForm->getElements();
  // Sys_Debug::dump( $_POST );
  // Sys_Debug::dump( $arrayElements );
                foreach ($arrayElements as $nameElement => $objectElement){
                   if ($nameElement == $strIndentity){
                       continue;
                   }

                   if (isset($this->_modelMetaData[$nameElement])){
                       if ($this->_objectForm->getValue($nameElement) !== NULL){
                           $this->_object->$nameElement = $this->_objectForm->getValue($nameElement);
                       } else {
//                           echo '<div> '.$nameElement. ' INNN "'.$this->_objectForm->getValue($nameElement).'"</div>';
                       }
                   }
                }

//                Sys_Debug::dumpDie( $arrayElements );
                try {
                    $this->_saveEdit();
                    $this->_objectForm->setDefaults($this->_object->toArray());

                    $arrMessages []= $this->_objectForm->getObjectName() . ' successfully saved';

                    if (!$paramID){
                        $this->view->new = 1;
                    }

                } catch ( Exception $e ) {
                    $arrErrors[]= $this->_objectForm->getObjectName() . ' was not saved. ' . $e->getMessage();
                }
                
            } else {
                 $arrErrors []= 'Please, fill the form correctly!';
            }

            $this->view->lstMessages = $arrMessages;
            $this->view->lstErrors = $arrErrors;
        }
        $this->view->object = $this->_object;

        $this->view->return = '';
        if ( $this->_getParam('return') != '' )
            $this->view->return = $this->_getParam( 'return' );
    }

    public function getlistAction()
    {
        $this->init();
	$this->_joinTables();
        $this->_filterList();

        $paramStart     = $this->_getIntParam('start', 0);
        $paramPage      = $this->_getIntParam('page', 1);
        $paramResults   = $this->_getIntParam('results', 50);
        $prmNoPager     = $this->_getIntParam('nopager', 0);

        $allRowsCount = 0;
        

        if ( $paramPage < 1 ) $paramPage = 1;
        if ( $paramStart == 0 ) $paramStart = ($paramPage - 1) * $paramResults;

        $this->_select->limit($paramResults, $paramStart);

        $paramSort      = $this->_getParam('sort', $this->_model->getIdentityName());
        $paramDir       = $this->_getParam('dir', 'asc');
        $this->_filterSort( $paramSort, $paramDir );

        if( $this->_getParam( 'exclude') ) {
            if ( is_array( $this->_getParam( 'exclude') ) ) {
                $arrExclude = $this->_getParam( 'exclude');
                if ( count( $arrExclude  ) > 0 ) {
                    //excluding array
                    $strExcluded = implode( ',', $arrExclude );
                    $this->_select->where( $this->_model->getIdentityName().' NOT IN ('.$strExcluded.')' );
                    $this->_selectCount->where( $this->_model->getIdentityName().' NOT IN ('.$strExcluded.')' );
                }

            } else {
                // excluding scalar
                $this->_select->where(
                    $this->_model->getIdentityName().' <> ?', $this->_getParam('exclude') );
                $this->_selectCount->where(
                    $this->_model->getIdentityName().' <> ?', $this->_getParam('exclude') );
            }
        }

        if (!$prmNoPager) $this->_select->calcFoundRows();
        $listObjects = $this->_model->fetchAll($this->_select);
        if (!$prmNoPager) $allRowsCount = $this->_getRowsCount();
        $pagesCount = ceil($allRowsCount / $paramResults);

        $this->_list = $listObjects;
        $this->view->listObjects = $listObjects;
        $this->view->arrID = $listObjects->getIds();
        $this->view->rows = $listObjects->toArray();
        $this->view->page = $paramPage;
        $this->view->results = $paramResults;
        $this->view->start = $paramStart;
        $this->view->sort = $paramSort;
        $this->view->dir = $paramDir;
        $this->view->pagesCount = $pagesCount;
        $this->view->totalCount = $allRowsCount;
        $this->view->identityName = $this->_model->getIdentityName();

        // default: parameter for options
        $this->view->default = '';
        if ( $this->_getParam('default') != '' )
            $this->view->default = $this->_getParam( 'default' );
        $this->view->caption = '';
        if ( $this->_getParam('caption') != '' )
            $this->view->caption = $this->_getParam( 'caption' );

    }

    public function getDatesAction()
    {
        $strDtMin = '';
        $strDtMax = '';
        if ( is_array( $this->view->arrDates )) {
            foreach( $this->view->arrDates as $strDt => $nQuantity ) {
                if ( $strDtMax == '' || $strDt > $strDtMax ) $strDtMax = $strDt;
                if ( $strDtMin == '' || $strDt < $strDtMin ) $strDtMin = $strDt;
            }
        }
        $this->view->dtMin = $strDtMin;
        $this->view->dtMax = $strDtMax;
    }

    public function changeAction()
    {
        $this->init();

        $strIdentity = $this->_model->getIdentityName();
        $paramID    = $this->_getParam($strIdentity);
        $paramField = $this->_getParam('field');
        $paramValue = $this->_getParam('value');

        if (!$paramID){
            throw new App_Exception('Parameter ' . $this->getClassName()
                    . ' ' . $strIdentity . ' should be defined');
        }

        $arrID = $paramID;
        if (!is_array($paramID)){
            $arrID = explode(',', $paramID);
            if (count($arrID) > 0){
                $paramID = $arrID;
            }
        }

        $listObjects = $this->_model->find($arrID);
        foreach ($listObjects as $object){
            $object->$paramField = $paramValue;
            $object->save();
        }
        $this->view->listObjects = $listObjects;
        $this->view->return = '';
        if ( $this->_getParam('return') != '' )
            $this->view->return = $this->_getParam( 'return' );

    }

    public function deleteAction()
    {
        $this->init();
        
    	$paramID = $this->_getParam($this->_model->getIdentityName());
        if ( $paramID === NULL ) {
            throw new App_Exception('Param ID should be defined for deleteAction');
        }

        if (!is_array($paramID)){
            $arrID = explode(',', $paramID);
            if (count($arrID) > 0){
                $paramID = $arrID;
            }
        }

        $listObjects = $this->_model->find($paramID);
        foreach ($listObjects as $object){
            $object->delete();
        }
        $this->view->listObjects = $listObjects;
        $this->view->return = '';
        if ( $this->_getParam('return') != '' )
            $this->view->return = $this->_getParam( 'return' );
    }

    protected function _eliminatePost()
    {
        $strUrl = $_SERVER['REQUEST_URI'];
        if ( strstr( $strUrl, '?' ) )
            $strUrl .= '&rnd='.mt_rand(0, 10000);
        else
            $strUrl .= '?rnd='.mt_rand(0, 10000);
        header( 'Location: '.$strUrl );
        die;
    } 

	/** dummy index Action */
    public function indexAction()
    {
    }
    /**
     * 
     * @return string
     */

    protected function getSortableField()
    {
        throw new App_Exception( 'getSortableField must be overloaded' );
    }
      
    public function sortOrderAction()
    {
        $strSortableField = $this->getSortableField();
        
        $strIds = $this->_getParam('ids');
        $arrIds = explode( ",", $strIds );
        if ( count( $arrIds ) == 0 )
            throw new App_Exception( "ids are not provided" );
        
        $strClass = $this->getClassName();
        $nIterator = 0;
        foreach ( $arrIds as $nId )  {
            $objRow = $strClass::Table()->find( $nId )->current();
            $objRow->$strSortableField = $nIterator ++;
            $objRow->save( false );
        }
    }
}
