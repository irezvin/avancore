<?php

interface Ac_I_CollectionAwareObject {
    
    function getRegisteredObjectCollections();

    function isObjectCollectionRegistered(Ac_I_ObjectsCollection $collection);
    
    function notifyUnregisteredFromCollection(Ac_I_ObjectsCollection $collection);
    
    function notifyRegisteredInCollection(Ac_I_ObjectsCollection $collection);
    
}