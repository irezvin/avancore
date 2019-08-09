<?php

interface Ac_I_Result_AfterWrite extends Ac_I_StringObject {
    
    function render(Ac_Result_Stage_Write $stage);
    
}