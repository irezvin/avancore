<?php

// we need to know which slot renderers does Result have 
// otherwise we won't be able to propagate all results before write-out
// (see Ac_Result_Stage_Write)

interface Ac_I_Result_SlotRenderer extends Ac_I_Result_AfterWrite {
    
    function getSlotId();
    
}