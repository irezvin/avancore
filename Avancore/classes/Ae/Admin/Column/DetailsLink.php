<?php

class Ae_Admin_Column_DetailsLink extends Ae_Admin_Column {
     
     /**
      * @var Ae_Legacy_Controller_Context
      */
     var $_context = false;
     
     var $_idPrefix = false;
    
     var $_recordKeys = array();

     function doShowHeader() {
         $this->_context = & $this->manager->getContext();
         $this->_idPrefix = $this->_context->mapIdentifier('_details_link_');
         parent::doShowHeader();
     }
     
     function doShowCell(& $record, $rowNo) {
         $u = & $this->manager->getDetailsUrl($record); 
         $this->_recordKeys[$rowNo] = $this->manager->getStrPk($record);
         ?>
            <a href="<?php echo htmlspecialchars($u->toString()); ?>" id="<?php echo $this->_idPrefix.'_'.$rowNo; ?>"><?php parent::doShowCell($record, $rowNo); ?></a>
         <?php
     }
     
     function showHint() {
        
        $tpl = & $this->manager->getTemplate();
        $jsHelper = & $tpl->getHtmlHelper();
     ?>
     
        <script type="text/javascript">
            var _c = <?php echo $this->manager->getJsListControllerRef() ?>;
<?php foreach ($this->_recordKeys as $i => $key) { ?>
            _c.getRecord(<?php echo $i; ?>)
                .observe([_c.EditRecord], {element: '<?php echo $this->_idPrefix.'_'.$i; ?>'});
<?php } ?>
            delete c;
        </script>
     <?php
       
     }
}

?>