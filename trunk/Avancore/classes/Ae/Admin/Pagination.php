<?php

Ae_Dispatcher::loadClass('Ae_Legacy_Controller');

class Ae_Admin_Pagination extends Ae_Legacy_Controller {
    
    // ------------------------------ configuration variables ---------------------------------
    
    /**
     * Whether parameter values should be stored in the state
     *
     * @var bool
     */
    var $storeParamsInState = true;
    
    /**
     * Step between numbers of records to show (for example, if $limitStep = 50, the list will be 50, 100, 150...)
     *
     * @var int
     */
    
    var $limitStep = 50;      
    
    /**
     * Max number of records to show in "select record count" list 
     *
     * @var int
     */
    var $maxRecords = 500; 
    
    /**
     * Max number of pages to show. False = show all pages.
     *
     * @var int|false
     */
    var $maxPages = false;
    
    /**
     * Number of total records. Should be set!
     *
     * @var int
     */
    var $totalRecords = false;
    
    /**
     * Default value of recordsPerPage
     *
     * @var int
     */
    var $recordsPerPage = 50;
    
    var $pagesPerSuperPage = 10;

    /**
     * Enter description here...
     *
     * @var unknown_type
     */
    var $allowMoreRecordsPerPage = false;
    
    /**
     * If number of records from the request is not in the list (is not divisible by $this->limitStep), use it 
     * (or, otherwise, round it to nearest allowed value)
     *
     * @var bool
     */
    var $allowRandomRecordsPerPage = false;
    
    var $allowSelectRecordsPerPage = true;
    
    var $showTotals = true;

    var $putUrlsToLinks = false;
    
    var $showFormTag = false;
    
    // ------------------------------ request variables ---------------------------------
    
    /**
     * Records per page value from the request
     *
     * @var int
     */
    var $_currentRecordsPerPage = false;
    
    /**
     * Offset value from the request
     *
     * @var int
     */
    var $_currentOffset = false;
    
    /**
     * Number of page value from the request
     *
     * @var int
     */
    var $_currentPageNo = false;
    
    var $_templateClass = 'Ae_Admin_Template_Pagination';
    
    /**
     * @var Ae_Admin_Template_Pagination
     */
    var $_template = false;
    
    // ------------------------------ interface methods ---------------------------------
    
    /**
     * Returns number of records per page that is got from the request
     * @return int
     */
    function getNumberOfRecordsPerPage() {
        if ($this->_currentRecordsPerPage === false) {
            $rpp = false;
            if ($this->allowSelectRecordsPerPage) { 
                $src = $this->storeParamsInState? $this->_rqWithState : $this->_rqData;
                if (isset($src['recordsPerPage'])) $rpp = $this->_filterRecordsPerPage($src['recordsPerPage']);
            }
            if ($rpp === false) $rpp = $this->recordsPerPage; 
            $this->_currentRecordsPerPage = $rpp;
            if ($this->storeParamsInState) $this->_context->setStateVariable('recordsPerPage', $rpp);
        }
        return $this->_currentRecordsPerPage;
    }
    
    /**
     * Returns actual offset (currentPage()*numberOfRecordsPerPage())
     * @return int
     */
    function getOffset() {
        return $this->getPageNo()*$this->getNumberOfRecordsPerPage();
    }
    
    /**
     * Returns actual number of page
     * @return int
     */
    function getPageNo() {
        $this->bindFromRequest();
        if ($this->_currentPageNo === false) {
            $src = $this->storeParamsInState? $this->_rqWithState : $this->_rqData;
            $v = false;
            if (isset($src['pageNo']) && is_numeric($src['pageNo']) && intval($src['pageNo']) >= 0 && intval($src['pageNo']) <= $this->_getNumPages())
                $v = $src['pageNo'];  
            if ($v === false) $v = 0; 
            $this->_currentPageNo = $v;
            if ($this->_currentPageNo > $this->_getNumPages()) $this->_currentPageNo = $this->_getNumPages();
            if ($this->storeParamsInState) $this->_context->setStateVariable('pageNo', $v);
        }
        return $this->_currentPageNo;
    }
    
    function getSuperPageNo() {
        if (!$this->pagesPerSuperPage) return 0;
        $res = floor ($this->getPageNo() / $this->pagesPerSuperPage);
        return $res; 
    }
    
    function getSuperPagesCount() {
        if (!$this->pagesPerSuperPage) return 1;
        $res = ceil($this->_getNumPages() / $this->pagesPerSuperPage);
        return $res;
    }
    
    function rowNumber($i) {
        return $this->getOffset() + $i + 1;
    }
    
    // ------------------------------ template methods ---------------------------------

    function doPopulateTemplate() {
        $this->_template->populate($this, $this->_context, $this->_getNumPages(), $this->_getRecordsPerPageValues());
    }
    
    function doPopulateResponse() {
        //$tpl = & $this->_template;
        //$resp = & $this->_response;
        //$resp->content = $tpl->fetch();
    }
    
    function show() {
        $resp = & $this->getResponse();
        $tpl = & $this->getTemplate();
        $resp->content = $tpl->show();
        echo $resp->content;
    }
    
    // ------------------------------ supplementary methods ---------------------------------

    function _filterRecordsPerPage($val) {
        $d = $val;
        if (is_numeric($d) && intval($d) > 0) {
            $d = intval($d);
            if (!$this->allowRandomRecordsPerPage) {
                if ($this->limitStep <= 0) $this->limitStep = 50;
                if ($d > $this->maxRecords) $d = $this->maxRecords;
                else $d = intval($d / $this->limitStep) * $this->limitStep;
            }
            $res = $d;
        } else {
            $res = false;
        }
        return $res;
    }
    
    function _getNumPages() {
        if ($this->limitStep <= 0) $this->limitStep = 50;
        if ($this->maxRecords <= 0) $this->maxRecords = 1000;
        $res = (int) ceil($this->totalRecords / $this->getNumberOfRecordsPerPage());
        return $res;
    }
    
    
    function _getRecordsPerPageValues() {
        $res = array();
        if ($this->limitStep <= 0) $this->limitStep = 50;
        for ($n = $this->limitStep; $n < $this->maxRecords; $n+= $this->limitStep) $res[$n] = $n;
        return $res; 
    }

    function orderUpIcon( $i, $condition=true, $task='orderup', $alt='Move up' ) {
        if (($i > 0 || ($i+$this->limitstart > 0)) && $condition) {
            return '<a href="#reorder" onClick="return listItemTask(\'cb'.$i.'\',\''.$task.'\')" title="'.$alt.'">
                <img src="images/uparrow.png" width="12" height="12" border="0" alt="'.$alt.'" />
            </a>';
        } else {
            return '&nbsp;';
        }
    }
/**
* @param int The row index
* @param int The number of items in the list
* @param string The task to fire
* @param string The alt text for the icon
* @return string
*/
    function orderDownIcon( $i, $n, $condition=true, $task='orderdown', $alt='Move down' ) {
        if (($i < $n-1 || $i+$this->limitstart < $this->total-1) && $condition) {
            return '<a href="#reorder" onClick="return listItemTask(\'cb'.$i.'\',\''.$task.'\')" title="'.$alt.'">
                <img src="images/downarrow.png" width="12" height="12" border="0" alt="'.$alt.'" />
            </a>';
        } else {
            return '&nbsp;';
        }
    }

    /**
     * @param int The row index
     * @param string The task to fire
     * @param string The alt text for the icon
     * @return string
     */
    function orderUpIcon2( $id, $order, $condition=true, $task='orderup', $alt='#' ) {
        // handling of default value
        if ($alt = '#') {
            $alt = 'Move up';
        }

        if ($order == 0) {
            $img = 'uparrow0.png';
            $show = true;
        } else if ($order < 0) {
            $img = 'uparrow-1.png';
            $show = true;
        } else {
            $img = 'uparrow.png';
            $show = true;
        };
        if ($show) {
            $output = '<a href="#ordering" onClick="listItemTask(\'cb'.$id.'\',\'orderup\')" title="'. $alt .'">';
            $output .= '<img src="images/' . $img . '" width="12" height="12" border="0" alt="'. $alt .'" title="'. $alt .'" /></a>';

            return $output;
        } else {
            return '&nbsp;';
        }
    }

    /**
     * @param int The row index
     * @param int The number of items in the list
     * @param string The task to fire
     * @param string The alt text for the icon
     * @return string
     */
    function orderDownIcon2( $id, $order, $condition=true, $task='orderdown', $alt='#' ) {
        // handling of default value
        if ($alt = '#') {
            $alt = 'Move down';
        }

        if ($order == 0) {
            $img = 'downarrow0.png';
            $show = true;
        } else if ($order < 0) {
            $img = 'downarrow-1.png';
            $show = true;
        } else {
            $img = 'downarrow.png';
            $show = true;
        };
        if ($show) {
            $output = '<a href="#ordering" onClick="listItemTask(\'cb'.$id.'\',\'orderdown\')" title="'. $alt .'">';
            $output .= '<img src="images/' . $img . '" width="12" height="12" border="0" alt="'. $alt .'" title="'. $alt .'" /></a>';

            return $output;
        } else {
            return '&nbsp;';
        }
    }
    
    
}

?>