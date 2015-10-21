<?php

interface Ac_I_ObjectsCollection {

    const ACTUALIZE_NO_SUCH_OBJECT = 0;
    const ACTUALIZE_SAME = 1;
    const ACTUALIZE_ID_CHANGED = 2;
    const ACTUALIZE_REMOVED = -1;
    
    const CONFLICT_IGNORE_NEW = 0;
    const CONFLICT_REMOVE_OLD = 1;
    const CONFLICT_THROW = 2;
    
    function registerOrActualizeObject ($object, & $actualizeResult = Ac_I_ObjectsCollection::ACTUALIZE_NO_SUCH_OBJECT);
    
    function unregisterObject ($object);
    
    function unregisterAllObjects();
    
    function actualizeRegisteredObject ($object);
    
    function findRegisteredObject ($object);
    
    function getRegisteredObjects ($identifiers = false);
    
    function getIdentifierOfObject ($object);
    
    function setCollectionConflictMode ($collectionConflictMode);
    
    function getCollectionConflictMode ();
    
}