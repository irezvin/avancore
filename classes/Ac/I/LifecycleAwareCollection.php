<?php

interface Ac_I_LifecycleAwareCollection extends Ac_I_ObjectsCollection {
    
    const STAGE_CREATED = 'STAGE_CREATED';
    
    const STAGE_LOADED = 'STAGE_LOADED';
    
    const STAGE_SAVED = 'STAGE_SAVED';
    
    const STAGE_DELETED = 'STAGE_DELETED';
    
    const STAGE_REVERTED = 'STAGE_REVERTED';
    
    function notifyCollectionObjectStage($object, $stage);
    
}