<?php

interface Ac_I_Param {

    function getId();
    
    function getPath();
    
    function getDestPath();

    function hasValue();
    
    function getValue();
    
    function getErrors();

}