<?php

if (!defined('AE_ADMIN_PAGINATION_PREV_N_PAGES')) define ('AE_ADMIN_PAGINATION_PREV_N_PAGES', '&lt;&lt; Previous %d pages');
if (!defined('AE_ADMIN_PAGINATION_NEXT_N_PAGES')) define ('AE_ADMIN_PAGINATION_NEXT_N_PAGES', 'Next %d pages &gt;&gt;');
if (!defined('AE_ADMIN_PAGINATION_PREV_PAGE')) define ('AE_ADMIN_PAGINATION_PREV_PAGE', '&lt; Previous page ');
if (!defined('AE_ADMIN_PAGINATION_NEXT_PAGE')) define ('AE_ADMIN_PAGINATION_NEXT_PAGE', 'Next page &gt;');
if (!defined('AE_ADMIN_PAGINATION_SHOWING')) define ('AE_ADMIN_PAGINATION_SHOWING', 'Showing');
if (!defined('AE_ADMIN_PAGINATION_OF')) define ('AE_ADMIN_PAGINATION_OF', 'of');
if (!defined('AE_ADMIN_PAGINATION_SHOW_QTY')) define ('AE_ADMIN_PAGINATION_SHOW_QTY', 'Show #');
if (!defined('AE_ADMIN_PAGINATION_RECORDS')) define ('AE_ADMIN_PAGINATION_RECORDS', 'records');

Ae_Dispatcher::loadClass('Ae_Template_Html');

class Ae_Admin_Template_Pagination extends Ae_Template_Html {

    var $showFormTag = false;
    
    var $formMethod = '';
    
    /**
     * @var Ae_Url
     */
    var $formUrl = false;
    
    var $formId = 'pager';
    
    var $pageParamName = 'pageNo';
    
    var $limitParamName = 'recordsPerPage';
    
    var $numPages = false;
    
    var $currPage = false;
    
    var $totalRecords = false;
    
    var $limit = 50;
    
    var $limitsList = array(5 => 5, 25 => 25, 50 => 50, 100 => 100, 150 => 150, 200 => 200);
    
    var $dummyLimitCaption = false;
    
    var $showLimits = true;
    
    var $superPageNo = false;
    
    var $pagesPerSuperPage = 10;
    
    var $superPagesCount = false;
    
    var $showPrevPageLink = true;
    
    var $showNextPageLink = true;
    
    var $showFirstPageLink = true;
    
    var $showLastPageLink = true;
    
    var $showTotals = true;
    
    /**
     * @var Ae_Url
     */
    var $baseUrl = false;
    
    /**
     * Populates template with given Pagination controller
     *
     * @param Ae_Admin_Pagination $pagination
     * @param Ae_Legacy_Controller_Context_Http $context
     * @param array $limitsList
     */
    function populate(& $pagination, & $context, $numPages, $limitsList) {
        $this->formId = strlen($context->isInForm)? $context->isInForm : $context->mapIdentifier('form');
        $this->formUrl = & $context->getUrl();
        $this->pageParamName = $context->mapParam('pageNo');
        $this->limitParamName = $context->mapParam('recordsPerPage');
        $this->numPages = $numPages;
        if ($limitsList) $this->limitsList = $limitsList;
        $this->currPage = $pagination->getPageNo();
        $this->totalRecords = $pagination->totalRecords;
        $this->limit = $pagination->getNumberOfRecordsPerPage();
        $this->showLimits = $pagination->allowSelectRecordsPerPage; 
        $this->pagesPerSuperPage = $pagination->pagesPerSuperPage? $pagination->pagesPerSuperPage : $this->numPages;
        $this->superPageNo = $pagination->getSuperPageNo();
        $this->superPagesCount = $pagination->getSuperPagesCount();
        $this->showTotals = $pagination->showTotals;
        $this->showFormTag = $pagination->showFormTag;
        if ($pagination->putUrlsToLinks) {
            $context = & $pagination->getContext();
            $otherContext = & $context->cloneObject();
            $otherContext->setState(false);
            $otherContext->stateIsExternal = true;
            $otherContext->_url = false;
            $this->baseUrl = & $otherContext->getUrl();
        }
    }
    
    function _showPageLink($caption, $enabled, $pageNo = false, $url = false, $escapeCaption = false, $selected = false) {
        $attribs = array();
        if ($selected) $attribs['class'] = 'selected';
        if ($enabled) {
            $formName = $this->formId;
            $pageParamName = $this->pageParamName;
            if (!$pageNo) $pageNo = 0;
            if ($url !== false) {
                if ($url == true) {
                    $myUrl = & $this->baseUrl->cloneObject();
                    //var_dump($myUrl->query);
                    Ae_Util::setArrayByPath($myUrl->query, Ae_Util::pathToArray($this->pageParamName), $pageNo, true);
                } else {
                    Ae_Dispatcher::loadClass('Ae_Url');
                    $myUrl = new Ae_Url($url);
                }
                $href = $myUrl->toString();
            } else $href = '#';
            $attribs['href'] = $href;
            if ($pageNo !== false && $formName !== false && !$url) {
                $attribs['onclick'] = "document['{$formName}']['{$pageParamName}'].value='{$pageNo}'; document['{$formName}'].submit(); return false;";
            }
            $preTag = '<a '.$this->attribs($attribs, true).'>';
            $postTag = '</a>';
        } else {
            $preTag = '<span '.$this->attribs($attribs, true).'>';
            $postTag = '</span>';
        }
        $this->d($preTag, true);
        $this->d($caption, !$escapeCaption);
        $this->d($postTag, true);
    }
    
    function showDefault() {
        //foreach (array('numPages', 'limitsList', 'currPage', 'totalRecords', 'limit') as $v) var_dump($v, $this->$v);
        
        $fid = $this->formId;
        $limit = $this->limit;
        //var_dump($this->numPages, $this->superPageNo, $this->pagesPerSuperPage, $this->superPagesCount);
        if ($this->superPagesCount > 1) {
            $hasSuperPages = true;
            $minPage = $this->superPageNo*$this->pagesPerSuperPage;
            $maxPage = min(($this->superPageNo + 1)*($this->pagesPerSuperPage), $this->numPages) - 1;
        } else {
            $hasSuperPages = false;
            $minPage = 0;
            $maxPage = $this->numPages - 1;
        }
        
        $firstPageLinkActive = $prevPageLinkActive = $this->currPage > 0;
        $nextPageLinkActive = $lastPageLinkActive =  $this->currPage < $this->numPages - 1;
        $prevSuperPageLinkActive = $hasSuperPages && $this->superPageNo > 0;
        $nextSuperPageLinkActive = $hasSuperPages && $this->superPageNo < $this->superPagesCount - 1;
        $prevSuperPagePageNo = max(0, $this->currPage - $this->pagesPerSuperPage); 
        $nextSuperPagePageNo = min($this->numPages - 1, $this->currPage + $this->pagesPerSuperPage);
        
?>
    <div class='pagination'>
        <?php if ($this->showFormTag) { $u = & $this->formUrl(); ?>
            <form method="<?php $this->d($this->context->requestMethod); ?>" action="<?php $this->d($u->toString(false)); ?>" name="<?php $this->d($fid); ?>">
        <?php } ?>
            <input type='hidden' name='<?php $this->d($this->pageParamName); ?>' value='<?php $this->d($this->currPage); ?>' />
            <?php if ($this->numPages > 1) { ?>
            <div class='pagesList'>
            <?php if ($prevSuperPageLinkActive) { $this->_showPageLink(sprintf(AE_ADMIN_PAGINATION_PREV_N_PAGES, $this->pagesPerSuperPage), $prevSuperPageLinkActive, $prevSuperPagePageNo, $this->baseUrl? true: false); echo " &#149; "; }  ?>
            <?php $this->_showPageLink(AE_ADMIN_PAGINATION_PREV_PAGE, $prevPageLinkActive, $this->currPage - 1, $this->baseUrl? true: false); echo " &#149; "; ?>
            <?php for($i = $minPage; $i <= $maxPage; $i++ ) { ?>
                <!-- 
                <a href="#" <?php if ($this->currPage == $i) { ?>class='selected' <?php } ?>onclick="document['<?php $this->d($fid) ?>']['<?php echo $this->pageParamName; ?>'].value='<?php $this->d($i); ?>'; document['<?php $this->d($fid) ?>'].submit(); return false;"><?php $this->d($i + 1); ?></a>
                 -->
<?php       $this->_showPageLink($i + 1, $this->currPage != $i, $i, $this->baseUrl? true: false, false, $this->currPage == $i); ?>                
            <?php } ?>
            <?php echo " &#149; "; $this->_showPageLink(AE_ADMIN_PAGINATION_NEXT_PAGE, $nextPageLinkActive, $this->currPage + 1, $this->baseUrl? true: false); ?>
            <?php if ($nextSuperPageLinkActive) {  echo " &#149; "; $this->_showPageLink(sprintf(AE_ADMIN_PAGINATION_NEXT_N_PAGES, $this->pagesPerSuperPage), $nextSuperPageLinkActive, $nextSuperPagePageNo, $this->baseUrl? true: false); } ?>
            </div>
            <?php } ?>
<?php       if ($this->showLimits || $this->showTotals) { ?> 
            <div class='position'>
                <?php echo AE_ADMIN_PAGINATION_SHOWING; ?> <?php $this->d($this->currPage*$limit + 1) ?> &ndash; <?php $this->d(min(($this->currPage + 1)*$limit + 1, $this->totalRecords)); ?> <?php echo AE_ADMIN_PAGINATION_OF; ?> <?php $this->d($this->totalRecords); ?>
<?php       if ($this->showLimits) { ?>                
                <?php echo AE_ADMIN_PAGINATION_SHOW_QTY; ?> 
                <select name='<?php $this->d($this->limitParamName); ?>' onchange="document['<?php $this->d($fid) ?>'].submit();">
                    <?php if ($this->dummyLimitCaption !== false) { ?><option><?php $this->d($this->dummyLimitCaption); ?></option>
                    
                    <?php } ?>
                    <?php foreach ($this->limitsList as $val => $capt) { ?>
                        <option value="<?php $this->d($val); ?>" <?php if ($val == $this->limit) { ?>selected="selected"<?php } ?>><?php $this->d($capt); ?></option>
                        
                    <?php } ?>
                </select>
    <?php        } ?>                
                <?php echo AE_ADMIN_PAGINATION_RECORDS; ?>
            </div>
<?php       } ?>             
        <?php if ($this->showFormTag) { ?>
            </form>
        <?php } ?>
    </div>
    
<?php
    }
    
}

?>