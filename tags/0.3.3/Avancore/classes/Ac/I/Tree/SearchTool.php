<?php

/**
 * Used to implement search-by-autocomplete and format tree nodes in Ac_Legacy_Controller_Std_Tree
 */
interface Ac_I_Tree_SearchTool {
    
    function findTreeItems($substring, $limit = 20);

    function hiliteCallback($args);
    
    function hilite ($foo, $text);
    
    function formatSearchResults($records, $substring);
    
    function handleAutocompleteDataRequest($autocomplete, $params);
    
    function handleAutocompleteItemSelected($autocomplete, $params);
    
    function handleOnCreateTreeNode($tree, $params);
    
}