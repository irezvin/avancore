<?php

class Ac_Admin_Column_Checked extends Ac_Admin_Column {
     
     var $toggleAllId = 'tgA';
     
     var $togglePrefix = 'tg';
     
     var $keyParamName = 'keys';
    
     var $_keyParamName = false;

     /**
      * @var Ac_Legacy_Controller_Context_Http
      */
     var $_context = false;
    
     var $_recordKeys = array();
     
     function doShowHeader() {
         $this->_context = $this->manager->getContext();
         ?><input type="checkbox" class='checkbox' id="<?php echo $this->_context->mapIdentifier($this->toggleAllId); ?>" /><?php
     }
     
     function doShowCell($record, $rowNo) {
         $this->_recordKeys[] = $key = $this->manager->getStrPk($record);
         ?>
            <input type="checkbox" class='checkbox' id="<?php echo $this->_context->mapIdentifier($this->togglePrefix.$key); ?>" name="<?php echo $this->_context->mapParam($this->keyParamName) ?>[]" value="<?php echo htmlspecialchars($key); ?>" />
         <?php
     }
     
     function showHint() {
        $tpl = $this->manager->getTemplate();
        $jsHelper = $tpl->getHtmlHelper();
     ?>
     
        <script type="text/javascript">
            var _c = <?php echo $this->manager->getJsListControllerRef() ?>; 
<?php foreach ($this->_recordKeys as $i => $key) { ?>
            _c.getRecord(<?php echo $i; ?>).observe([AvanControllers.ListController.ToggleSelected, AvanControllers.ListController.ShowSelected], {element: <?php echo $jsHelper->jsQuote($this->_context->mapIdentifier($this->togglePrefix.$key)); ?>});
<?php } ?>
            _c.observe([AvanControllers.ListController.ToggleAllSelected, AvanControllers.ListController.ShowAllSelected], {element: <?php echo $jsHelper->jsQuote($this->_context->mapIdentifier($this->toggleAllId)); ?>});
            delete _c;
        </script>
     <?php
     }
}

