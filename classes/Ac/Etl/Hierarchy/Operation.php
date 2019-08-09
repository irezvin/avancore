<?php

/**
 *  
 * At the moment of operation execution, item names should be
 * already resolved if $itemCategoryTargetName is set.
 * 
 * At most six tables are used:
 * 
 * -    ITEM-TO-CATEGORY IMPORT table that contains category names 
 *      of items (if there is m-to-n relationship between items 
 *      and categories, it should be separate table)
 * 
 *      Property: $itemCategoryImportId
 * 
 *      Optional; if omitted, item-to-category mappings won't be 
 *      set both internally and externally; only categories will be
 *      created
 * 
 *      Columns: 
 *      -   $itemCategoryNameCol, (defaults to 'categoryName')
 *      -   $itemCategoryIdCol    (defaults to 'categoryId')
 *      -   $itemIdCol  (no default; required if table is specified)
 *              An array may be provided (referenceImportId, referenceIdCol)
 *              to join itemCategoryImportTable with e xternal table using lineNo
 *              columns and take referenceIdCol value as itemIdCol from there
 * 
 * -    ITEMS-TO-CATEGORY TARGET table that will contain item-to-category
 *      relations
 *      
 *      Property: $itemCategoryTargetName
 *      Optional. If omitted, item-to-category mappings won't be updated
 *  
 *      Columns:
 *      -   $targetCategoryIdCol <- values updated from $itemCategoryIdCol - required if table is used
 *      -   $targetItemIdCol <- values updated from $itemIdCol - required if table is used
 * 
 * -    CATEGORY LIST IMPORT table that contains category names
 *      required
 *      Property: $categoryListImportId
 * 
 *      Required
 * 
 *      Columns:
 *      -   $categoryImportNameCol, (defaults to 'title')
 *      -   $categoryImportIdCol, (defaults to 'classifierId')
 * 
 * -    CATEGORY LIST TARGET table that will contain created categories
 * 
 *      Required
 * 
 *      Property: $categoryListTargetName
 *      
 *      Columns:
 *      -   $categoryNameCol
 *      -   $categoryIdCol
 *      
 *      Property $categoryColMap allows to map category import columns
 * 
 * -    CATEGORY-TO-CATEGORY IMPORT table that
 *      contains category relationships
 *      
 *      Optional; defaults to CATEGORY LIST IMPORT table.
 *      If set to NULL, parent-to-child relationships won't be created.
 * 
 *      Property: $ccImportId
 * 
 *      Columns:
 *      -   $ccImportCategoryNameCol (def.: 'categoryName' or $categoryImportNameCol)
 *      -   $ccImportParentNameCol (def.: 'parentName')
 *      -   $ccImportCategoryIdCol (def.: 'categoryId' or $categoryImportIdCol)
 *      -   $ccImportParentIdCol (def.: 'parentId')
 * 
 * -    CATEGORY-TO-CATEGORY TARGET table that will contain
 *      item-to-category relationships
 * 
 *      Optional; defaults to CATEGORY TARGET; if explicitly set to NULL, category-to-category
 *      relationships won't be created (this feature may be used to create plain classifiers).
 *      
 *      Property: $ccTargetName
 * 
 *      Columns:
 *      -   $ccTargetParentIdCol
 *      -   $ccTargetCategoryIdCol (defaults to $categoryIdCol when table is same as category target)
 * 
 *      If same as CATEGORY TARGET, won't have own Writer and will
 *      be updated along with CATEGORY TARGET table
 * 
 */

class Ac_Etl_Hierarchy_Operation extends Ac_Etl_Operation {    
    
    protected $itemCategoryImportId = false;

    protected $itemCategoryNameCol = 'categoryName';

    protected $itemCategoryIdCol = 'categoryId';

    protected $itemIdCol = false;

    protected $categoryListImportId = false;

    protected $categoryImportNameCol = 'title';

    protected $categoryImportIdCol = 'classifierId';

    protected $ccImportId = false;

    protected $ccImportCategoryNameCol = false;

    protected $ccImportCategoryIdCol = false;

    protected $ccImportParentNameCol = 'parentName';
    
    protected $ccImportParentIdCol = 'parentId';

    protected $categoryListTargetName = false;

    protected $categoryNameCol = false;

    protected $categoryIdCol = false;

    protected $categoryColMap = array();

    protected $itemCategoryTargetName = false;

    protected $targetCategoryIdCol = false;

    protected $targetItemIdCol = false;

    protected $ccTargetName = false;

    protected $ccTargetParentIdCol = false;

    protected $ccTargetCategoryIdCol = false;


    
    protected $iItemCategoryImportId = false;

    protected $iItemCategoryNameCol = false;

    protected $iItemCategoryIdCol = false;

    protected $iItemIdCol = false;

    protected $iCategoryListImportId = false;

    protected $iCategoryImportNameCol = false;

    protected $iCategoryImportIdCol = false;

    protected $iCcImportId = false;

    protected $iCcImportCategoryNameCol = false;

    protected $iCcImportCategoryIdCol = false;

    protected $iCcImportParentNameCol = false;

    protected $iCcImportParentIdCol = false;

    protected $iCategoryListTargetName = false;

    protected $iCategoryNameCol = false;

    protected $iCategoryIdCol = false;

    protected $iCategoryColMap = false;

    protected $iItemCategoryTargetName = false;

    protected $iTargetCategoryIdCol = false;

    protected $iTargetItemIdCol = false;

    protected $iCcTargetName = false;

    protected $iCcTargetParentIdCol = false;

    protected $iCcTargetCategoryIdCol = false;

    
    
    protected $iDontCreateParentToChildRelations = false;
    protected $iDontImportItemToCategoryRelations = false;
    protected $iCcImportTableIsCategoryList = false;
    protected $iCcDestTableIsCategoryList = false;
    
    
    protected $innerOperationPrototypes = array();
    
    
    protected $intPropsCalculated = false;
    
    // Example prototype for Ac_Etl_Hierarchy_Operation: just copy-and-change    
    static $examplePrototype = array( 
        
        'class' => 'Ac_Etl_Hierarchy_Operation',

        //  Categories:

            // import table: [required]

            'categoryListImportId' => false, // Table ID
            'categoryImportNameCol' => 'title', // Column with category names
            'categoryImportIdCol' => 'classifierId', // Column with category titles

            // destination table: [required]

            'categoryListTargetName' => false, // Table SQL name [required]
            'categoryNameCol' => false, // Column with category names [required]
            'categoryIdCol' => false, // Column with category IDs [required]

        // Item-to-category mapping:

            // internal import table: [optional]

            'itemCategoryImportId' => false, // Table ID
            'itemCategoryNameCol' => 'categoryName', // Column with category names
            'itemCategoryIdCol' => 'categoryId', // Column with category IDs
            'itemIdCol' => false, // Column with item IDs

            // destination table: [optional]

            'itemCategoryTargetName' => false, // Table SQL name
            'targetCategoryIdCol' => false,  // Column with category IDs
            'targetItemIdCol' => false, // Column with item IDs

        // Category-to-category mapping:

            // internal import table [optional]

            'ccImportId' => false, // Table ID
            'ccImportCategoryIdCol' => false, // Column with category IDs
            'ccImportCategoryNameCol' => false, // Column with category names
            'ccImportParentIdCol' => 'parentId', // Column with parent IDs
            'ccImportParentNameCol' => 'parentName', // Column with parent name

            'categoryColMap' => array(), // Additional columns' map

            // destination table:

            'ccTargetName' => false, // Target SQL name
            'ccTargetCategoryIdCol' => false, // Column with category IDs
            'ccTargetParentIdCol' => false, // Column with parent IDs
    );
      
    
    
    static $intPropMap = false;
    
    /**
     * Returns 'internal' to 'published' property mappings
     * @return array('iItemCategoryImportId' => 'itemCategoryImportId' and so on...)
     */
    protected static function getIntPropMap() {
        
        if (self::$intPropMap === false) {
            self::$intPropMap = array();
            foreach (array_keys($cv = get_class_vars('Ac_Etl_Hierarchy_Operation')) as $varName) {
                if (array_key_exists($pv = 'i'.ucfirst($varName), $cv)) {
                    self::$intPropMap[$pv] = $varName;
                }
            }
        }
        return self::$intPropMap;
    }
    
    protected function resetIntProperties() {
        $this->intPropsCalculated = false;
        foreach (array_keys(self::getIntPropMap) as $k) $this->$k = false;
    }
    
    protected function calcIntProperties($recalc = false) {
        
        if (!$recalc && $this->intPropsCalculated) return;
        $this->intPropsCalculated = true;
        
        // basically copy all published properties to internal ones
        foreach (self::getIntPropMap() as $k => $v) $this->$k = $this->$v;
        
        // check item-to-category import table and properties
        if (strlen($this->iItemCategoryImportId)) {
            if (!strlen($this->iItemCategoryNameCol)) 
                throw new Ac_E_Etl("itemCategoryNameCol is required when itemCategoryImportId is provided");
            if (!strlen($this->iItemCategoryIdCol)) 
                throw new Ac_E_Etl("itemCategoryIdCol is required when itemCategoryImportId is provided");
            if (is_array($this->iItemIdCol) && count($this->iItemIdCol) != 2) {
                throw new Ac_E_Etl("If array is provided as itemIdCol, it should contain exactly two elements");
            } elseif (!is_array($this->iItemIdCol)) {
                if (!strlen($this->iItemIdCol)) 
                    throw new Ac_E_Etl("itemIdCol is required when itemCategoryImportId is provided");
            }
        } else {
            $this->iDontImportItemToCategoryRelationships = true;
        }
        
        // check category list import table and properties
        if (!strlen($this->iCategoryListImportId)) {
            throw new Ac_E_Etl("categoryListImportId is required");
            if (!strlen($this->iCategoryImportNameCol)) 
                throw new Ac_E_Etl("categoryImportNameCol is required");
            if (!strlen($this->iCategoryImportIdCol)) 
                throw new Ac_E_Etl("categoryImportIdCol is required");
        }

        // check category parent-to-child import table
        if ($this->iCcImportId === null) {
            $this->iDontCreateParentToChildRelations = true;
        } else {
            if ($this->iCcImportId === false) {
                if (!is_null($this->iCcTargetName)) {
                    $this->iCcImportId = $this->iCategoryListImportId;
                }
            }
            if ($this->iCcImportId === $this->iCategoryListImportId) {
                $this->iCcImportTableIsCategoryList = true;
                if ($this->iCcImportCategoryNameCol === false) $this->iCcImportCategoryNameCol = $this->iCategoryImportNameCol;
                if ($this->iCcImportCategoryIdCol === false) $this->iCcImportCategoryIdCol = $this->iCategoryImportIdCol;
            } else {
                if ($this->iCcImportCategoryNameCol === false) $this->iCcImportCategoryNameCol = 'categoryName';
                if ($this->iCcImportCategoryIdCol === false) $this->iCcImportCategoryIdCol = 'categoryId';
            }
            if (strlen($this->iCcImportId)) {
                if (!strlen($this->iCcImportParentIdCol))
                    throw new Ac_E_Etl("ccImportParentIdCol is required when ccImportId is provided");
                if (!strlen($this->iCcImportParentNameCol))
                    throw new Ac_E_Etl("ccImportParentNameCol is required when ccImportId is provided");            
            }
        }

        // check category target table
        if (!strlen($this->iCategoryListTargetName)) {
            throw new Ac_E_Etl("categoryListTargetName is required");
        } else {
            if (!strlen($this->iCategoryNameCol))
                throw new Ac_E_Etl("categoryNameCol is required");
            if (!strlen($this->iCategoryIdCol))
                throw new Ac_E_Etl("categoryIdCol is required");
        }

        // check category parent-to-child target table
        if ($this->iDontCreateParentToChildRelations && strlen($this->iCcTargetName)) {
            throw new Ac_E_Etl("Inconsistent configuration: categoryParentToChildImport table is explicitly set to NULL, but ccTargetName is provided");
        }
        if ($this->iCcTargetName === null) {
            $this->iDontCreateParentToChildRelations = true;
            if (strlen($this->ccImportId)) {
                throw new Ac_E_Etl("Inconsistent configuration: categoryParentToChildImport table is provided, but ccTargetName is explicitly set to NULL");
            } else {
                $this->iCcImportId = null;
            }
        } elseif ($this->iCcTargetName === false) {
            $this->iCcTargetName = $this->iCategoryListTargetName;
        }
        
        if ($this->iCcTargetName == $this->iCategoryListTargetName) {
            if ($this->iCcTargetCategoryIdCol === false) {
                $this->iCcTargetCategoryIdCol = $this->iCategoryIdCol;
            }
            $this->iCcDestTableIsCategoryList = true;
        }
        if (strlen($this->iCcTargetName)) {
            if (!strlen($this->iCcTargetParentIdCol))
                throw new Ac_E_Etl("ccTargetParentIdCol is required when ccTargetName is provided");
            if (!strlen($this->iCcTargetCategoryIdCol))
                throw new Ac_E_Etl("ccTargetCategoryIdCol is required when ccTargetName is provided");
        }
        
        if (strlen($this->iItemCategoryTargetName)) {
            if (!strlen($this->iItemCategoryImportId)) {
                throw new Ac_E_Etl("Inconsistent configuration: itemCategoryImportId is not provided (source of item-to-category mappings), but itemCategoryTargetName is not empty");
            }
            if (!strlen($this->iTargetCategoryIdCol))
                throw new Ac_E_Etl("targetCategoryIdCol is required when itemCategoryTargetName is provided");
            if (!strlen($this->iTargetItemIdCol))
                throw new Ac_E_Etl("targetItemIdCol is required when itemCategoryTargetName is provided");
        }
    }
    
    function setInnerOperationPrototypes(array $innerOperationPrototypes) {
        $this->innerOperationPrototypes = $innerOperationPrototypes;
    }

    protected function checkSettings() {
        if (!strlen($this->catListImportId)) 
            throw new Exception("catListImportId not provided");
    }
    
    protected function getDefaultInnerOperationPrototypes() {
        
        $this->calcIntProperties();
        
        $categoryWriterPrototype = array(
            'class' => 'Ac_Etl_Operation_Write',
            'statusColName' => 'importStatus',
            'problemsColName' => 'problems',
            'tableId' => $this->iCategoryListImportId,
            'targetSqlName' => $this->iCategoryListTargetName,
            'nameMap' => array($this->iCategoryImportNameCol => $this->iCategoryNameCol),
            'keyMap' => array($this->iCategoryImportIdCol => $this->iCategoryIdCol),
        );     
        
        if ($this->iCategoryColMap)
            $categoryWriterPrototype['contentMap'] = $this->iCategoryColMap;

        $res = array();
        
        if ($this->iDontCreateParentToChildRelations || $this->iCcImportTableIsCategoryList) {
            $res['categoryWriter'] = $categoryWriterPrototype;
        } else {
            $res['creator'] = array(
                'class' => 'Ac_Etl_Operation_Copy',
                'tableId' => $this->iCcImportId,
                'targetTableId' => $this->iCategoryListImportId,
                'distinct' => true,
                'ignoreLineNumbers' => true,
                'colMatches' => array($this->iCategoryImportNameCol => $this->iCcImportCategoryNameCol),
                'cleanTargetTable' => false,
                'handleExisting' => Ac_Etl_Operation_Copy::handleExistingUpdate,
                'forwardKeys' => array($this->iCategoryImportNameCol => $this->iCcImportCategoryNameCol),
                'innerOperations' => array(
                    'updater' => $categoryWriterPrototype,
                ),
                'reverseKeys' => array($this->iCcImportCategoryNameCol => $this->iCategoryImportNameCol,),
                'reverseMatches' => array(
                    $this->iCcImportCategoryIdCol => $this->iCategoryImportIdCol
                ),
            );
        }
        
        if (!$this->iDontCreateParentToChildRelations) {
            
            $res['parentsResolver'] = array(
                'class' => 'Ac_Etl_Operation_Write',
                'tableId' => $this->iCcImportId,
                'targetSqlName' => $this->iCategoryListTargetName,
                'nameMap' => array($this->iCcImportParentNameCol => $this->iCategoryNameCol),
                'keyMap' => array($this->iCcImportParentIdCol => $this->iCategoryIdCol),
                'createAllowed' => false,
            );
            
            $res['parentsWriter'] = array(
                'class' => 'Ac_Etl_Operation_Write',
                'tableId' => $this->iCcImportId,
                'targetSqlName' => $this->iCcTargetName,
                'nameMap' => array($this->iCcImportCategoryIdCol => $this->iCcTargetCategoryIdCol),
                'contentMap' => array(
                    $this->iCcImportParentIdCol => $this->iCcTargetParentIdCol,
                ),
                'updateNulls' => true, // important one
            );
            
        }
        
        if (strlen($this->iItemCategoryImportId)) {
        
            // write back category IDs to item-to-category import table
            
            $res['itemsCategoryCopier'] = array(
                'class' => 'Ac_Etl_Operation_Copy',
                'targetTableId' => $this->iCategoryListImportId,
                'tableId' => $this->iItemCategoryImportId,
                'reverseKeys' => array($this->iItemCategoryNameCol => $this->iCategoryImportNameCol),
                'reverseMatches' => array($this->iItemCategoryIdCol => $this->iCategoryImportIdCol),
            );
        
        }
        
        if (strlen($this->iItemCategoryTargetName)) {
            
            $res['itemCategoryWriter'] = array(
                'class' => 'Ac_Etl_Operation_Write',
                'tableId' => $this->iItemCategoryImportId,
                'targetSqlName' => $this->iItemCategoryTargetName,
                'insertIgnore' => true,
                'nameMap' => array($this->iItemCategoryIdCol  => $this->iTargetCategoryIdCol),
            );
            
            if (is_array($this->iItemIdCol)) {
                list($refImportId, $refIdCol) = $this->iItemIdCol;
                $tbl = $this->import->getTable($refImportId);
                $joinMap = $this->mkMap(array('importId' => 'importId', 'lineNo' => 'lineNo'), 'items', 't');
                if (is_array($tbl->restriction)) {
                    $joinMap->union($this->mkMap($tbl->restriction, 'items', false)->rightAreValues());
                }
                $res['itemCategoryWriter']['selectPrototype'] = array(
                    'tables' => array(
                        'items' => array(
                            'name' => $tbl->tableName('string'),
                            'joinsAlias' => 't',
                            'joinType' => 'INNER JOIN',
                            'joinsOn' => $joinMap->eq(' = ', ' AND '),
                        ),
                    ),
                    'usedAliases' => array('items'),
                );
                $res['itemCategoryWriter']['nameMap'][] = 
                    array(new Ac_Sql_Expression('items.'.$refIdCol), $this->iTargetItemIdCol);
            } else {
                $res['itemCategoryWriter']['nameMap'][$this->iItemIdCol] = $this->iTargetItemIdCol;
            }
            
        }
        
        //ini_set('xdebug.var_display_max_depth', 10);
        //var_dump($res);
        
        return $res;
    }
    
    function getCalculatedProperty($propName) {
        $pm = array_flip(self::getIntPropMap());
        $this->calcIntProperties();
        if (isset($pm[$propName])) {
            $foo = $pm[$propName];
            $res = $this->$foo;
        } else {
            $res = Ac_Accessor::getObjectProperty($this, $propName);
        }
        return $res;
    }
    
    function getInnerOperationPrototypes($withDefaults = false) {
        $res = $this->innerOperationPrototypes;
        if ($withDefaults) {
            $res = Ac_Util::m($this->getDefaultInnerOperationPrototypes(), $res);
        }
        return $res;
    }
    
    protected function createInnerOperations() {
        $prototypes = $this->getInnerOperationPrototypes(true);
        $res = Ac_Prototyped::factoryCollection($prototypes, 'Ac_Etl_Operation', array('import' => $this->import, 'parentOperation' => $this, 'parentOperationRelation' => 'inner'), 'id', true, true);
        return $res;
    }
    
    protected function doProcess() {
        $res = true;
        foreach ($this->createInnerOperations() as $i => $p) {
            if (!$p->process()) {
                $res = false;
                break;
            }
        }
        return $res;
    }

    function setItemCategoryImportId($itemCategoryImportId) {
        $this->itemCategoryImportId = $itemCategoryImportId;
        $this->intPropsCalculated = false;
    }

    function getItemCategoryImportId() {
        return $this->itemCategoryImportId;
    }

    function setItemCategoryNameCol($itemCategoryNameCol) {
        $this->itemCategoryNameCol = $itemCategoryNameCol;
        $this->intPropsCalculated = false;        
    }

    function getItemCategoryNameCol() {
        return $this->itemCategoryNameCol;
    }

    function setItemCategoryIdCol($itemCategoryIdCol) {
        $this->itemCategoryIdCol = $itemCategoryIdCol;
        $this->intPropsCalculated = false;        
    }

    function getItemCategoryIdCol() {
        return $this->itemCategoryIdCol;
    }

    function setItemIdCol($itemIdCol) {
        $this->itemIdCol = $itemIdCol;
        $this->intPropsCalculated = false;        
    }

    function getItemIdCol() {
        return $this->itemIdCol;
    }

    function setCategoryListImportId($categoryListImportId) {
        $this->categoryListImportId = $categoryListImportId;
        $this->intPropsCalculated = false;        
    }

    function getCategoryListImportId() {
        return $this->categoryListImportId;
    }

    function setCategoryImportNameCol($categoryImportNameCol) {
        $this->categoryImportNameCol = $categoryImportNameCol;
        $this->intPropsCalculated = false;        
    }

    function getCategoryImportNameCol() {
        return $this->categoryImportNameCol;
    }

    function setCategoryImportIdCol($categoryImportIdCol) {
        $this->categoryImportIdCol = $categoryImportIdCol;
        $this->intPropsCalculated = false;        
    }

    function getCategoryImportIdCol() {
        return $this->categoryImportIdCol;
    }

    function setCcImportId($ccImportId) {
        $this->ccImportId = $ccImportId;
        $this->intPropsCalculated = false;        
    }

    function getCcImportId() {
        return $this->ccImportId;
    }

    function setCcImportCategoryNameCol($ccImportCategoryNameCol) {
        $this->ccImportCategoryNameCol = $ccImportCategoryNameCol;
        $this->intPropsCalculated = false;        
    }

    function getCcImportCategoryNameCol() {
        return $this->ccImportCategoryNameCol;
    }

    function setCcImportCategoryIdCol($ccImportCategoryIdCol) {
        $this->ccImportCategoryIdCol = $ccImportCategoryIdCol;
        $this->intPropsCalculated = false;        
    }

    function getCcImportCategoryIdCol() {
        return $this->ccImportCategoryIdCol;
    }

    function setCcImportParentNameCol($ccImportParentNameCol) {
        $this->ccImportParentNameCol = $ccImportParentNameCol;
        $this->intPropsCalculated = false;        
    }

    function getCcImportParentNameCol() {
        return $this->ccImportParentNameCol;
    }

    function setCcImportParentIdCol($ccImportParentIdCol) {
        $this->ccImportParentIdCol = $ccImportParentIdCol;
        $this->intPropsCalculated = false;        
    }

    function getCcImportParentIdCol() {
        return $this->ccImportParentIdCol;
    }

    function setItemCategoryTargetName($itemCategoryTargetName) {
        $this->itemCategoryTargetName = $itemCategoryTargetName;
        $this->intPropsCalculated = false;        
    }

    function getItemCategoryTargetName() {
        return $this->itemCategoryTargetName;
    }

    function setTargetCategoryIdCol($targetCategoryIdCol) {
        $this->targetCategoryIdCol = $targetCategoryIdCol;
        $this->intPropsCalculated = false;        
    }

    function getTargetCategoryIdCol() {
        return $this->targetCategoryIdCol;
    }

    function setTargetItemIdCol($targetItemIdCol) {
        $this->targetItemIdCol = $targetItemIdCol;
        $this->intPropsCalculated = false;        
    }

    function getTargetItemIdCol() {
        return $this->targetItemIdCol;
    }

    function setCategoryListTargetName($categoryListTargetName) {
        $this->categoryListTargetName = $categoryListTargetName;
        $this->intPropsCalculated = false;        
    }

    function getCategoryListTargetName() {
        return $this->categoryListTargetName;
    }

    function setCategoryNameCol($categoryNameCol) {
        $this->categoryNameCol = $categoryNameCol;
        $this->intPropsCalculated = false;        
    }

    function getCategoryNameCol() {
        return $this->categoryNameCol;
    }

    function setCategoryIdCol($categoryIdCol) {
        $this->categoryIdCol = $categoryIdCol;
        $this->intPropsCalculated = false;        
    }

    function getCategoryIdCol() {
        return $this->categoryIdCol;
    }

    function setCategoryColMap($categoryColMap) {
        $this->categoryColMap = $categoryColMap;
        $this->intPropsCalculated = false;        
    }

    function getCategoryColMap() {
        return $this->categoryColMap;
    }

    function setCcTargetName($ccTargetName) {
        $this->ccTargetName = $ccTargetName;
        $this->intPropsCalculated = false;        
    }

    function getCcTargetName() {
        return $this->ccTargetName;
    }

    function setCcTargetParentIdCol($ccTargetParentIdCol) {
        $this->ccTargetParentIdCol = $ccTargetParentIdCol;
        $this->intPropsCalculated = false;        
    }

    function getCcTargetParentIdCol() {
        return $this->ccTargetParentIdCol;
    }

    function setCcTargetCategoryIdCol($ccTargetCategoryIdCol) {
        $this->ccTargetCategoryIdCol = $ccTargetCategoryIdCol;
        $this->intPropsCalculated = false;        
    }

    function getCcTargetCategoryIdCol() {
        return $this->ccTargetCategoryIdCol;
    }

    function setTableId($tableId) {
        if (strlen($tableId)) 
            trigger_error ("\$tableId is not used in Ac_Etl_Hierarchy_Operation", E_USER_WARNING);
    }
    
}