<?php

class Ac_Admin_Template extends Ac_Template_Html {
    /**
     * @var Ac_Admin_Manager
     */
    var $manager = false;
    var $activeSubManagerId = false;
    
    /**
     * @var Ac_Controller_Context_Http
     */
    var $context = false;
    var $inForm = false;
    var $formId = false;
    var $managerId = false;
    var $requestMethod = 'post';
    /**
     * Name of manager action parameter in URL or form input
     *
     * @var string
     */
    var $actionParam = 'action';
    var $processingParam = 'processing';
    var $recordKeyParam = 'key';
    
    /**
     * @var Ac_Admin_Action[]
     */
    var $actions = array();
    
    var $htmlIdsPrefix = false;
    
    var $assets = array();
    
    var $_showTableMtime = false;
    
    /**
     * @var Ac_Url
     */
    var $url = false;
    
    /**
     * @var Ac_Admin_Manager_Processing
     */
    var $processing = false;
    
    /**
     * @var Ac_Controller_Response
     */
    var $processingResponse = false;
    
    /**
     * Populates template vars using info from the manager and its context
     *
     * @param Ac_Admin_Manager $manager
     */
    function setManager($manager) {
        $this->manager = $manager;
        $context = $manager->getContext();
        if ($context->isInForm) {
            $this->formId = $context->isInForm;
            $this->requestMethod = $context->requestMethod;
        } else {
            $this->formId = 'managerForm';
            $this->requestMethod = 'post'; 
        }
        $this->url = $context->getUrl();
        $this->actionParam = $context->mapParam($this->manager->_methodParamName);
        $this->processingParam = $context->mapParam('processing');
        $this->recordKeyParam = $context->mapParam('recordKey');
        $this->htmlIdsPrefix = $context->mapIdentifier('_');
        $this->actions = array();
        foreach ($this->manager->listActions(true) as $a) $this->actions[$a] = $this->manager->getAction($a);
    }
    
    function showToolbar() {
        $this->addJsLib('{AC}/vendor/overlib_mini.js');
        $showOwnToolbarHeader = !$this->manager->getConfigService()->showToolbarHeader($this->manager->toolbarHeader);
        
        if (count($this->actions)) {
?>

    <table class='actions'>
        <tr>
            <td class="beforeActions<?php if ($showOwnToolbarHeader) echo " withHeader"; ?>">
<?php           if ($showOwnToolbarHeader) { ?>
                <h1><?php echo $this->manager->toolbarHeader; ?></h1>
<?php           } else { ?>
                &nbsp;
<?php           } ?>                
            </td>
<?php foreach(array_keys($this->actions) as $a) { $act = $this->actions[$a]; ?>
            <td id="<?php echo $this->htmlIdsPrefix ?>_action_td_<?php echo $a; ?>" >
                <img id="<?php echo $this->htmlIdsPrefix ?>_action_img_<?php echo $a; ?>" />
                <div><a href="#" id="<?php echo $this->htmlIdsPrefix ?>_action_a_<?php echo $a; ?>">&nbsp;</a></div>
            </td>                
<?php } ?>

        </tr>
    </table>            
            
<?php
        }
    }
    
    function showCreateManagerController() {
        $mgrCon = $this->manager->getJsManagerControllerRef();
        $ctx = $this->manager->getContext();
        $h = $this->getHtmlHelper();
        $sd = $this->manager->getStateData();        
?>

    <input type="hidden" name="<?php $this->d($ctx->mapParam($this->manager->_methodParamName)); ?>" id="<?php $this->d($aId = $ctx->mapIdentifier($this->manager->_methodParamName)); ?>" value="<?php if (isset($sd[$this->manager->_methodParamName])) $this->d($sd[$this->manager->_methodParamName]); ?>" />
    <input type="hidden" name="<?php $this->d($ctx->mapParam($this->manager->getProcessingParamName())); ?>" id="<?php $this->d($pId = $this->manager->getProcessingParamName(true)); ?>" value="<?php if (isset($sd['processing'])) $this->d($sd['processing']); ?>" />
    <script type="text/javascript"><!--
    
        <?php echo $mgrCon ?> = new AvanControllers.ManagerController({
            managerActionElement: <?php $this->d($h->jsQuote($aId), true) ?>,
            managerProcessingElement: <?php $this->d($h->jsQuote($pId), true) ?>,
            processingParamsPrefix: <?php $this->d($h->jsQuote($this->manager->getProcessingParamsParamName()), true); ?>, 
            managerParamsPrefix: <?php $this->d($h->jsQuote($this->manager->getManagerParamsParamName()), true); ?>,
            containerElementId: <?php $this->d($h->jsQuote($this->manager->getContext()->mapIdentifier('_container')), true); ?>
        });
    // -->
    </script>
<?php
    
    }
    
    function showJsBindings() {
        
        $mgrCon = $this->manager->getJsManagerControllerRef();
        $h = $this->getHtmlHelper();
        $ctx = $this->manager->getContext();
?>

        <script type="text/javascript">
        <!--
        var _m = <?php echo $mgrCon ?>;
<?php if ($fn = $this->manager->getManagerFormName()) { ?>

        _m.formElement = <?php echo $h->jsQuote($fn); ?>;
        
<?php } ?>
<?php        
        if (count($this->actions)) {
            $actionsJson = array();
            $hlp = $this->getHtmlHelper();
            foreach (array_keys($this->actions) as $a) {
                $actionsJson[] = $this->actions[$a]->getJson();
            }
            $act = $this->manager->getJsActionsControllerRef();
?>
        var _a = <?php echo $act; ?> = new AvanControllers.ActionsController( {
            actions: <?php echo $hlp->toJson($actionsJson, 14, 4, true, false); ?>
        } );
<?php foreach(array_keys($this->actions) as $a) { $act = $this->actions[$a]; ?>
        
        _a.getAction('<?php echo $a; ?>')
                .observe([AvanControllers.ActionsController.Click, AvanControllers.ActionsController.ShowCaption, AvanControllers.ActionsController.ShowHint], {element: '<?php echo $this->htmlIdsPrefix ?>_action_a_<?php echo $a; ?>'})
                .observe([AvanControllers.ActionsController.Click, AvanControllers.ActionsController.ShowImage, AvanControllers.ActionsController.ShowHint], {element: '<?php echo $this->htmlIdsPrefix ?>_action_img_<?php echo $a; ?>'})
                .observe([AvanControllers.ActionsController.EnabledDisabled, AvanControllers.ActionsController.Click], {element: '<?php echo $this->htmlIdsPrefix ?>_action_td_<?php echo $a; ?>'});
<?php } ?>

<?php if ($this->manager->isList()) { ?>
        
        <?php $this->d($this->manager->getJsListControllerRef(), true); ?>.setActionsController(_a);
<?php } ?>
        
        _m.setActionsController(_a);
        
<?php if ($form = $this->manager->getForm()) { $fcr = $this->manager->getJsFormControllerRef(); ?>
        
        var _f = <?php echo $fcr; ?> = new AvanControllers.FormController();
        _m.setFormController(_f);
<?php   if (($r = $this->manager->getRecord()) && $r->isPersistent()) { ?>
        _f.addRecords(<?php echo new Ac_Js_Val(new Ac_Js_Hash(array(array('key' => $this->manager->getIdentifierOf($r))))); ?>);
<?php   } ?>
<?php $this->d($this->manager->getJsFormControllerRef(), true); ?>.setActionsController(_a);
        
<?php if (in_array('cancel', array_keys($this->actions))) { ?>
        
<?php /* ?>
		_a.getAction('cancel').observe(_a.Click, {element: <?php echo $hlp->jsQuote($ctx->mapIdentifier('formContainer')); ?>, eventName: 'dblclick'});
<?php */ ?>		
        delete _f;
        
<?php } ?>

<?php } ?>
        
<?php } ?>
        delete _a;
        delete _m;

        //-->
        </script>
<?php
    }
    
    function showFilterForm() {
        $f = $this->manager->getFilterForm();
?>
    <div class='filters'>
<?php
        $r = $this->manager->_getFilterFormResponse();
        echo $r->content;
        $this->htmlResponse->mergeWithResponse($r);
?>
        <div class='clr'>&nbsp;</div>   
    </div>
<?php        
    }        
    
    function showManagerList() {
        $table = $this->manager->getTable();
        $pager = $this->manager->getPagination();
        $filterForm = $this->manager->getFilterForm();
?>
    <script type='text/javascript'>
        <?php $this->d($this->manager->getJsManagerControllerRef(), true); ?>.setListController( 
            <?php $this->d($this->manager->getJsListControllerRef(), true); ?> = new window.AvanControllers.ListController({selectedClass: 'selected'})
        );
    </script>
    <div>
        <?php if ($filterForm) $this->showFilterForm(); ?>
        <?php $table->show(); ?>
        <?php $pager->show(); ?>
    </div>
    <?php $table->showHints(); ?>
<?php
    }
    
    function showManagerDetails() {
        $ctx = $this->manager->getContext();
?>   
    <div class='details' id='<?php echo $ctx->mapIdentifier('formContainer') ?>'>   
    <?php $this->showFormContent(); ?>   
    
    <?php if ($this->manager->listSubManagers()) $this->_showSubManagers(); ?>
    
    </div>
    
    
<?php
    }
    
    function showFormContent() {
        $r = $this->manager->_getFormResponse();
        echo $r->content;
        $this->htmlResponse->mergeWithResponse($r);   
    }
            
    function showManagerWrapper($innerHtml, $isPart = false, $params = array()) {
        // TODO: FIX this one because there is no more Adapter here in Ac0.3!
        
        $this->addAssetLibs(
            $this->controller->getConfigService()->getManagerJsAssets()
        );
        
        if ($this->assets) {
            $this->addAssetLibs(
                $this->assets
            );
        }
        
        
        if ($isPart) $innerHtml = $this->fetch($innerHtml, $params);
        
        $context = $this->manager->getContext();
        $formName = $this->manager->getManagerFormName();
        if (!$context->isInForm) {
            
            $url = $this->manager->getManagerUrl();
        
?>
    <form action="<?php $this->d($url->toString(false)); ?>" method="<?php $this->d($context->requestMethod); ?>" id="<?php $this->d($formName); ?>" name="<?php $this->d($formName); ?>" enctype="multipart/form-data" >
        
        <?php $this->d($url->getHidden(), true); ?>
<?php   } else {
            
            // TODO: fix this ugly crap!
            $stateData = $this->manager->getStateData();
            
            // on some occasions leads to duplication of submitted values
            unset($stateData['form']);
            
            unset($stateData[$this->manager->_methodParamName]);
            $adp = $this->manager->_context->getDataPath();
            echo $foo = Ac_Url::queryToHidden($stateData, /*Ac_Util::arrayToPath($adp)*/ $adp );
        } ?>

    <div class='manager' data-managerid='<?php $this->d($this->manager->getJsManagerControllerRef(true)); ?>' id='<?php $this->d($this->manager->getContext()->mapIdentifier('_container')); ?>'>
        <div class='managerBookmark' id='<?php $this->d($this->manager->getContext()->mapIdentifier('_container')); ?>_bookmark'></div>
<?php if ($this->manager->separateToolbar) ob_start(); ?>    
<?php   $this->manager->toolbarHeader = $this->manager->isForm()? $this->manager->getFormTitle() : $this->manager->getPluralCaption(); ?>
<?php   $this->showToolbar(); ?>
<?php if ($this->manager->separateToolbar) { $this->manager->toolbarContent = ob_get_clean(); } ?>    
    <?php $this->showCreateManagerController(); ?>
    <?php $this->d($innerHtml, true); ?>
    <?php $this->showJsBindings(); ?>
    </div>
    
    <?php if ($this->_showTableMtime && ($m = $this->manager->getMapper())) var_dump(date("Y-m-d H:i:s", $m->getMtime())); ?>
    
<?php if (!$context->isInForm) { ?>
    
    </form>
<?php   }       
    }
    
    function _showSubManagers() {
        $this->addCssLib('{AC}/vendor/tabcontent.css', false);
        $this->addJsLib('{AC}/vendor/tabcontent.js', false);
        $ctx = $this->manager->getContext();
        $tcId = $ctx->mapIdentifier('smTabs');
        $tcVar = $tcId.'_o';
        $h = $this->getHtmlHelper();
        $currTab = $ctx->getData('tab');
        
?>

        <div class='subManagers'>
        <ul id="<?php $this->d($tcId); ?>" class="shadetabs">
<?php foreach ($this->manager->listSubManagers() as $id) { 
        $smId = $ctx->mapIdentifier('smTab_'.$id); 
        $sm = $this->manager->getSubManager($id);
        $resp = $sm->getResponse();
        $cls = '';
        if ($sm->lastRecordErrors) {
            if (!$currTab) $currTab = $id;
            $cls = ' class="withErrors"';
        }
?>
    
        <li<?php echo $cls; ?>><a href="#" rel="<?php $this->d($smId); ?>"><?php $this->d($sm->getPluralCaption()); ?></a></li>
<?php } ?>
        </ul>

        <div class='tabContainer'>
<?php foreach ($this->manager->listSubManagers() as $id) { 
    $smId = $ctx->mapIdentifier('smTab_'.$id); 
    $sm = $this->manager->getSubManager($id);
    $resp = $sm->getResponse();
    
?>
    
        <div id="<?php $this->d($smId); ?>" class="tabcontent">
            <div class='subManagerTab'>
            <?php echo $resp->content; ?>
            </div>
        </div>
<?php } ?>

        </div>
        
        </div>

        <script type="text/javascript">
            
            var <?php echo $tcVar; ?> = new ddtabcontent(<?php echo $h->jsQuote($tcId); ?>);
            <?php echo $tcVar; ?>.setpersist(true);
            <?php echo $tcVar; ?>.setselectedClassTarget("link");
            <?php echo $tcVar; ?>.init();
<?php       if (strlen($currTab)) { ?> 
            <?php echo "{$tcVar}.expandtab({$tcVar}.tabs[{$currTab}]);" ?>
<?php       } ?>
                
        </script>

<?php
        
    }
    
    function showProcessing() {
        ob_start();
        $this->htmlResponse->mergeWithResponse($this->processingResponse);
?>
        <div class="processing">
            <?php echo $this->processingResponse->content; ?>
        </div>
<?php        
        $this->showManagerWrapper(ob_get_clean());
    }
    
}

