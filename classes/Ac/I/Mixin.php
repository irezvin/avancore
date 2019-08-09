<?php

/**
 * 
 */

/**
 * Interface for classes whose instances can be 'mixed' with Ac_I_Mixables'
 * Inspired by koowa framework (which probably borrowed it from somewhere else).
 */
interface Ac_I_Mixin extends Ac_I_WithMethods {
    
    /**
     * List all mixables that are included into the mix
     * 
     * @param string|FALSE $className Include only mixables of specified class
     * @return array list of local mixable' IDs (keys of getMixables() array)
     * @see getMixables
     */
    function listMixables($className = false);
    
    /**
     * Adds a mixable into the mix or replaces it with another one.
     * 
     * $mixable->registerMixin($this) will be called.
     * 
     * @param Ac_I_Mixable $mixable mixable instance to add
     * 
     * @param string|bool $id Local id (key of the array) of the added mixable (FALSE means use 
     *                        $mixable->getId(); if mixable does not provide an ID, nearest numeric 
     *                        key will be used)
     * 
     * @param bool $canReplace Don't throw an Ac_E_InvalidCall if \$id is already used; replace 
     *                          an existing mixable instead.
     * 
     * @throws  Ac_E_InvalidCall
     * 
     * @return string ID that is assigned to a mixable
     */
    function addMixable(Ac_I_Mixable $mixable, $id = false, $canReplace = false);
    
    /**
     * Returns a mixable from the mix by the specified key
     * 
     * @param string $id local ID of the mixable
     * @param bool $dontThrow Return NULL instead of throwing an exception
     * @throws Ac_E_InvalidCall
     * @return Ac_I_Mixable NULL will be returned if mixable isn't found and $dontThrow == true
     */
    function getMixable($id, $dontThrow = false);
    
    /**
     * Deletes a mixable form the mix
     * 
     * @param string $id ID of the mixable
     * @param bool $dontThrow Don't throw an exception if no such \$id in the mix
     * @throws Ac_E_InvalidCall
     * @return bool TRUE if a mixable was deleted, FALSE otherwise
     */
    function deleteMixable($id, $dontThrow = false);
    
    /**
     * Returns all mixables or mixables of a specified class or interface
     * 
     * @param string|bool $className Class or interface name; FALSE to return all mixables
     * @return array ($id => $Ac_I_Mixable)
     */
    function getMixables($className = false);
    
    /**
     * Sets mixables. Items in \$mixables array may be prototypes; Ac_Prototyped::factory will be used.
     * 
     * Numeric key in array means that \$id must be taken using $mixable->getMixableId().
     * $mixable->unregisterMixin($this) will be called for every mixable that is removed from current 
     *                                    mixable collection (for all mixables if $addToExisting = true,
     *                                    and for mixables with matching with non-numeric keys 
     *                                    if $addToExisting = false)
     * 
     * $mixable->registerMixin($this) will be called for every item of an array.
     * 
     * @param array $mixables [$id => %Ac_I_Mixable]
     * @param bool $addToExisting Don't clear current \$mixables and add or overwrite new ones instead.
     *                            When $addToExisting = true, mixables with non-numeric $IDs will overwrite
     *                            mixables with marching keys.
     * 
     */
    function setMixables(array $mixables, $addToExisting = false);
    
    /**
     * Returns list of "core" mixables that cannot be deleted or replaced
     * 
     * @return array
     */
    function getCoreMixables();
    
}