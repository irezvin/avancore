<?php

class Ac_Admin_Feature_FacetedSearch_Template extends Ac_Form_Control_Template {
    
    function showFacetedSearch (Ac_Admin_Feature_FacetedSearch_Control $control) {
        
        $response = Ac_Legacy_Controller_Response_Global::r();
?>
        <div class="facetContainer">
            <?php echo $control->facetSet->getView()->renderSet($response); ?>
        </div>
<?php
        
    }
    
}