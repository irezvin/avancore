<?php

interface Ac_I_ModelAssociation {
    
    function setId($id);

    function getId();
    
    function beforeSave($object, & $errors);
    
    function afterSave($object, & $errors);
    
}