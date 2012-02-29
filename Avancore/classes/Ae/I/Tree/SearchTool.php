<?php

/**
 * @author nivzer
 */

/**
 * Used to implement search-by-autocomplete and format tree nodes in Ae_Legacy_Controller_Std_Tree
 */
interface Ae_I_Tree_SearchTool {
    
    function findTreeItems($substring, $limit = 20);

    function hiliteCallback($args);
    
    function hilite ($foo, $text);
    
    function formatSearchResults($records, $substring);
    
    function handleAutocompleteDataRequest($autocomplete, $params);
    
    function handleAutocompleteItemSelected($autocomplete, $params);
    
    function handleOnCreateTreeNode($tree, $params);
    
}