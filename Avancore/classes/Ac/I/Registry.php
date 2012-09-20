<?php

interface Ac_I_Registry extends Ac_I_Mergeable {
    
    function listRegistry($keyOrPath = null, $_ = null);
    
    function hasRegistry($keyOrPath, $_ = null);
    
    function deleteRegistry($keyOrPath, $_ = null);
    
    function getRegistry($keyOrPath = null, $_ = null);
    
    function setRegistry($value, $keyOrPath = null, $_ = null);
    
    function addRegistry($value, $keyOrPath = null, $_ = null);
    
    function exportRegistry($recursive = false, $keyOrPath = null, $_ = null);
    
    function mergeRegistry($value, $preserveExistingValues = false, $keyOrPath = null, $_ = null);
    
}