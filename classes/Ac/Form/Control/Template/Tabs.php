<?php

class Ac_Form_Control_Template_Tabs extends Ac_Form_Control_Template {
    
    function showTabs (Ac_Form_Control_Tabs $tabs) {
        $controls = $tabs->getOrderedDisplayChildren();
        $this->addCssLib('{AC}/vendor/tabcontent.css', false);
        $this->addJsLib('{AC}/vendor/tabcontent.js', false);
        $ctx = $tabs->getContext();
        $tcId = $ctx->mapIdentifier('tabs');
        $tcVar = $tcId.'_o';
        $h = $this->getHtmlHelper();
        $initialTab = $tabs->initialTab;
        $idx = 0;
        $initialIndex = false;
?>
        <div <?php echo Ac_Util::mkAttribs($tabs->getHtmlAttribs()); ?>>
            <ul id="<?php $this->d($tcId); ?>" class="shadetabs">
<?php           foreach (array_keys($controls) as $i) { 
                    $smId = $ctx->mapIdentifier('tab_'.$controls[$i]->name); 
                    if ($idx === $initialTab || $controls[$i]->name == $initialTab) {
                        $initialIndex = $idx;
                    }
                    $idx++;
                    
?>

                    <li><a rel="<?php $this->d($smId); ?>" <?php $this->attribs($controls[$i]->getHeaderAttribs()); ?>>
<?php                   if (strlen($controls[$i]->getDescription())) { ?>
                        <?php $this->_showCaptionWithDescription($controls[$i]); ?>
<?php                   } else { ?>                                            
                        <?php $this->d($controls[$i]->getCaption()); ?>
<?php                   } ?>

                    </a></li>
<?php           } ?>
            
            </ul>
        </div>
        
        <div class='tabContainer'>
<?php foreach (array_keys($controls) as $i) { 
    $smId = $ctx->mapIdentifier('tab_'.$controls[$i]->name); 
    
?>
    
        <div id="<?php $this->d($smId); ?>" <?php $this->attribs($controls[$i]->getSheetAttribs()); ?>>
            <?php echo $controls[$i]->fetchPresentation(true); ?>
        </div>
<?php } ?>

        </div>
        
        <script type="text/javascript">
            
            var <?php echo $tcVar; ?> = new ddtabcontent(<?php echo $h->jsQuote($tcId); ?>);
            <?php echo $tcVar; ?>.setpersist(true);
            <?php echo $tcVar; ?>.setselectedClassTarget("link");
            <?php echo $tcVar; ?>.init();
            <?php if ($initialIndex !== false) echo "{$tcVar}.expandtab({$tcVar}.tabs[{$initialIndex}]);"; ?> 
        </script>
        
<?php
    }
    
}

