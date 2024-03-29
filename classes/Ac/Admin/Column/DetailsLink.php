<?php

class Ac_Admin_Column_DetailsLink extends Ac_Admin_Column {
     
     /**
      * @var Ac_Controller_Context
      */
     var $_context = false;
     
     var $_idPrefix = false;
    
     var $_recordIdentifiers = array();

     function doShowHeader() {
         $this->_context = $this->manager->getContext();
         $this->_idPrefix = $this->_context->mapIdentifier('_details_link_');
         parent::doShowHeader();
     }
     
     function doShowCell($record, $rowNo) {
         $u = $this->manager->getDetailsUrl($record); 
         $this->_recordIdentifiers[$rowNo] = $this->manager->getIdentifierOf($record);
         ?>
            <a href="<?php echo htmlspecialchars($u->toString()); ?>" id="<?php echo $this->_idPrefix.'_'.$rowNo; ?>"><?php parent::doShowCell($record, $rowNo); ?></a>
         <?php
     }
     
     function showHint() {
        
        $tpl = $this->manager->getTemplate();
        $jsHelper = $tpl->getHtmlHelper();
     ?>
     
        <script type="text/javascript">
            var _c = <?php echo $this->manager->getJsListControllerRef() ?>;
<?php foreach ($this->_recordIdentifiers as $i => $id) { ?>
            _c.getRecord(<?php echo $i; ?>)
                .observe([AvanControllers.ListController.EditRecord], {element: '<?php echo $this->_idPrefix.'_'.$i; ?>'});
<?php } ?>
            delete c;
        </script>
     <?php
       
     }
}

