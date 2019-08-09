<?php

interface Ac_Etl_I_BatchCallback {
    
    function modifyRecords (array & $records, Ac_Etl_Operation & $operation);
    
}