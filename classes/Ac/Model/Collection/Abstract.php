<?php

/**
 * @property Ac_Application $application
 * @method Ac_Application getApplication()
 * @method void setApplication(Ac_Application $application)
 */
abstract class Ac_Model_Collection_Abstract extends Ac_Prototyped implements Iterator {

    use Ac_Compat_Overloader;
    
    protected static $_compat_application = 'app';
    protected static $_compat_setApplication = 'setApp';
    protected static $_compat_getApplication = 'getApp';
    
    /**
     * @var bool
     */
    protected $isOpen = false;
    
    /**
     * whether to automatically open(), if necessary,
     * on getCount(), fetchItem() or fetchGroup()
     * @var bool
     */
    protected $autoOpen = true;

    /**
     * @var int
     */
    protected $limit = false;

    /**
     * @var int
     */
    protected $offset = false;

    /**
     * @var int
     */
    protected $groupSize = false;

    /**
     * @var bool
     */
    protected $cleanGroupOnAdvance = false;
    
    protected $count = false;
    
    /**
     * @var array Used to hold the last loaded group of loaded items (only when $cleanGroupOnAdvance !== 0)
     */
    protected $currentGroup = array();
    
    /**
     * @var int Number of items already returned to the application
     */
    protected $extIndex = 0;
    
    /**
     * @var int Number of items already loaded by the collection ("current" position)
     */
    protected $intIndex = 0;
    
    /**
     * @var mixed Holds last retrieved item (returned by last getNextItem() call)
     */
    protected $currentItem = false;
    
    /**
     * @var mixed Holds the key of last retrieved item (by default it is $index)
     */
    protected $currentKey = false;
    
    /**
     * @var bool Whether last item was already fetched (all subsequent calls will return 0)
     */
    protected $stop = false;
    
    /**
     * @param bool $isOpen
     */
    function setIsOpen($isOpen) {
        if ($isOpen !== ($oldIsOpen = $this->isOpen)) {
            if (!$this->isOpen) {
                $this->resetState();
            }
            $this->isOpen = $isOpen;
        }
    }

    /**
     * @return bool
     */
    function getIsOpen() {
        return $this->isOpen;
    }

    /**
     * Sets whether to automatically open(), if necessary,
     * on getCount(), fetchItem() or fetchGroup()
     * @param bool $autoOpen
     */
    function setAutoOpen($autoOpen) {
        $this->autoOpen = $autoOpen;
    }

    /**
     * Returns whether to automatically open(), if necessary,
     * on getCount(), fetchItem() or fetchGroup()
     * @return bool
     */
    function getAutoOpen() {
        return $this->autoOpen;
    }

    /**
     * @deprecated
     * use Ac_Model_Collection::getCount()
     */
    function countRecords() {
        return $this->getCount();
    }
    
    /**
     * Retrieves count of all available items (if supported by the storage) 
     * disregarding $offset and $limit values. 
     * 
     * Caches the result between open() calls.
     * 
     * @return int
     */
    function getCount() { 
        if (!$this->isOpen) {
            if ($this->autoOpen) $this->setIsOpen(true);
            else throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."(): need to open() first");
        }
        if ($this->count === false) {
            $this->count = $this->doCount();
        }
        return $this->count;
    }

    /**
     * Sets size of the "group" - set of items that will be simultaneously loaded from the items provider.
     * Provider will receive actual offset and limit values depending on the group size and current group offset.
     * Use fetchGroup() to load an array with the whole group. $groupSize of 0 (default) means 
     * fetchGroup() will load all records specified by $limit and $offset parameters.
     * 
     * If $cleanGroupOnAdvance is set, cleanup code will be appled when next group is fetched.
     * 
     * @param int $groupSize
     */
    function setGroupSize($groupSize) {
        if ($groupSize !== ($oldGroupSize = $this->groupSize)) {
            if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."() while isOpen(). close() first");
            $this->groupSize = (int) $groupSize;
            if ($this->groupSize < 0) $this->groupSize = 0;
        }
    }

    /**
     * Returns size of the "group" - set of items that will be simultaneously loaded from the items provider.
     * Provider will receive actual offset and limit values depending on the group size and current group offset.
     * Use fetchGroup() to load an array with the whole group. $groupSize of 0 (default) means 
     * fetchGroup() will load all records specified by $limit and $offset parameters.
     * 
     * If $cleanGroupOnAdvance is set, cleanup code will be appled when next group is fetched.
     * 
     * @return int
     */
    function getGroupSize() {
        return $this->groupSize;
    }

    /**
     * Sets number of items to be loaded and returned. The total number of returned items will never exceed $limit.
     * $limit of 0 means no limit is applied.
     * 
     * @param int $limit
     */
    function setLimit($limit) {
        if ($limit !== ($oldLimit = $this->limit)) {
            if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."() while isOpen(). close() first");
            $this->limit = (int) $limit;
            if ($this->limit < 0) $this->limit = 0;
        }
    }

    /**
     * Returns number of items to be loaded and returned. The total number of returned items will never exceed $limit.
     * $limit of 0 means no limit is applied.
     * 
     * @return int
     */
    function getLimit() {
        return $this->limit;
    }

    /**
     * Offset 
     * @param int $offset
     */
    function setOffset($offset) {
        if ($offset !== ($oldOffset = $this->offset)) {
            if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."() while isOpen(). close() first");
            $this->offset = (int) $offset;
            if ($this->offset < 0) $this->offset = 0;
        }
    }

    /**
     * @return int
     */
    function getOffset() {
        return $this->offset;
    }
    
    /**
     * One-call method to set both offset and limit (ac3.3 compatibility)
     * @deprecated since 0.3.4. Use setLimit() and setOffset() instead
     */
    function setLimits($offset = false, $count = false) {
        $this->setOffset($offset);
        $this->setLimit($count);
    }

    /**
     * Sets whether last loaded group should be "disposed" on advance to the next group.
     * To dispose the group, implementation-specific routine is invoked (i.e. cleanupMembers() is called on Ac_Model_Object).
     * Cleanup code is called either on next fetchGroup() call, or on close() call.
     * 
     * @param bool $cleanGroupOnAdvance
     */
    function setCleanGroupOnAdvance($cleanGroupOnAdvance) {
        if ($cleanGroupOnAdvance !== ($oldCleanGroupOnAdvance = $this->cleanGroupOnAdvance)) {
            if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."() while isOpen(). close() first");
            $this->cleanGroupOnAdvance = $cleanGroupOnAdvance;
        }
    }

    /**
     * Returns whether last loaded group should be "disposed" on advance to the next group.
     * To dispose the group, implementation-specific routine is invoked (i.e. cleanupMembers() is called on Ac_Model_Object).
     * Cleanup code is called either on next fetchGroup() call, or on close() call.
     * 
     * @return bool
     */
    function getCleanGroupOnAdvance() {
        return $this->cleanGroupOnAdvance;
    }
    
    function isOpen() {
        return $this->isOpen;
    }
    
    function close() {
        $this->setIsOpen(false);
    }
    
    function open() {
        $this->setIsOpen(true);
    }
    
    function reopen() {
        if ($this->isOpen) $this->setIsOpen(false);
        $this->setIsOpen(true);
    }
    
    /**
     * Returns next group of items (of $groupSize length) and advances internal pointer to the beginning of next group.
     * If $groupSize == 0, will return all items with respect to $limit and $offset.
     * 
     * If part of the current group was already fetched with fetchItem(), fetchGroup() will return the remaining part 
     * of the current group, and only subsequent fetchGroup() call will return the next group.
     * 
     * Will return empty group (array with no elements) if no more groups are left.
     * 
     * @see Ac_Model_Collection_Abstract::fetchGroup
     * 
     * @return array
     */
    function fetchGroup() {
        if (!$this->isOpen) {
            if ($this->autoOpen) $this->setIsOpen(true);
            else throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."(): need to open() first");
        }
        if ($this->extIndex < $this->intIndex) {
            $delta = $this->intIndex - $this->extIndex;
            $res = array_slice($this->currentGroup, -$delta);
        } elseif ($this->stop) { 
            $res = array();
        } else {
            $this->fetchCurrentGroup();
            $res = $this->currentGroup;
        }
        if (($n = count($res))) {
            $this->extIndex = $this->intIndex;
            $this->currentItem = $res[$n - 1];
            $this->currentKey = $this->doCalcItemKey($res[$n - 1], $this->extIndex - 1);
        }
        return $res;
    }

    /**
     * @deprecated Use Ac_Model_Collection_Abstract::fetchItem
     */
    function fetchNext() {
        return $this->fetchItem();
    }
    
    /**
     * Returns next item in the list and advances internal pointer.
     * If collection isn't open, will open() it automatically.
     * If $groupSize is 0, will fetch all items (with regard to $limit and $offset) into memory before iterating.
     * If $groupSize is non-zero, will fetch items in blocks of $groupSize and iterate over the block, then fetch
     * new block in the end of previous block.
     * 
     * @see Ac_Model_Collection_Abstract::fetchItem
     */
    function fetchItem() {
        if (!$this->isOpen) {
            if ($this->autoOpen) $this->setIsOpen(true);
            else throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."(): need to open() first");
        }
        if ($this->extIndex < $this->intIndex) {
            $n = count($this->currentGroup);
            $key = $n - ($this->intIndex - $this->extIndex);
            $res = $this->currentGroup[$key];
        } else {
            if (!$this->stop || $this->currentGroup) $this->fetchCurrentGroup();
            if ($this->currentGroup) {
                $res = $this->currentGroup[0];
            } else {
                $res = false;
                $this->currentItem = false;
                $this->currentKey = false;
            }
        }
        if ($res !== false) {
            $this->extIndex++;
            $this->currentItem = $res;
            $this->currentKey = $this->doCalcItemKey($res, $this->extIndex - 1);
        }
        return $res;
    }
    
    /**
     * Alias of fetchItem() (for compatibility with legacy Collection)
     * @deprecated
     */
    function getNext() {
        return $this->fetchItem();
    }
    
    /**
     * Alias of fetchGroup() (for compatibility with legacy Collection)
     * @deprecated
     * @return array
     */
    function getRecords() {
        return $this->fetchGroup();
    }
    
    /**
     * @return bool
     */
    function isEof() {
        return $this->stop && $this->extIndex >= $this->intIndex;
    }
    
    /**
     * Resets internal state on open() call
     */
    protected function resetState() {
        if ($this->cleanGroupOnAdvance && $this->currentGroup) 
            $this->cleanCurrentGroup();
        $this->stop = false;
        $this->count = false;
        $this->currentItem = $this->currentKey = false;
        $this->extIndex = 0;
        $this->intIndex = 0;
    }
    
    protected function cleanCurrentGroup() {
        $this->currentGroup = array();
    }
    
    protected function calcCurrentOffsetAndLimit() {
        if ($this->groupSize) {
            $realOffset = floor($this->intIndex / $this->groupSize) * $this->groupSize;
            $realLimit = $this->groupSize;
            if ($this->limit) {
                $isLastGroup = $this->limit - $this->intIndex <= $this->groupSize;
                if ($isLastGroup) {
                    $realLimit = $this->limit - $this->intIndex;
                }
            }
        } else {
            $realOffset = $this->offset;
            $realLimit = $this->limit;
        }
        return array($realOffset, $realLimit);
    }
    
    protected function fetchCurrentGroup() {
        if ($this->currentGroup && $this->cleanGroupOnAdvance) {
            $this->cleanCurrentGroup();
            $this->currentGroup = array();
        }
        if (!$this->stop) {
            $this->currentGroup = array();
            // check if we are off limits
            if ($this->limit && $this->intIndex >= $this->limit) {
                $this->stop();
                $this->currentGroup = array();
            } else {
                list($offset, $limit) = $this->calcCurrentOffsetAndLimit();
                $this->currentGroup = $this->doFetchGroup($offset, $limit);
                $len = count($this->currentGroup);
                $this->intIndex += $len;
                // retrieved less then asked, nothing, all items, or we already have whole number of needed items?
                if ($len < $limit || !$len || !$limit || $this->limit && $this->intIndex >= $this->limit) {
                    $this->stop();
                }
            }
        } else {
            $this->currentGroup = array();
        }
        return $this->currentGroup;
    }
    
    protected function stop() {
        $this->stop = true;
    }
    
    /**
     * @return mixed
     */
    protected function doCalcItemKey($item, $index) {
        return $index;
    }

    /**
     * @return array
     */
    abstract protected function doFetchGroup($offset, $length);
    
    /**
     * @return int
     */
    abstract protected function doCount();
    
    // ------------------------------ Iterator support ------------------------------
    
    function current() {
        return $this->currentItem;
    }
    
    function key() {
        return $this->currentKey;
    }
    
    function next() {
        $this->fetchItem();
    }    
    
    function rewind() {
        $this->setIsOpen(false);
        $this->setIsOpen(true);
        $this->fetchItem();
    }
    
    function valid() {
        return $this->currentKey !== false;
    }
    
}
