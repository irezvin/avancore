<?php

class Etl_Test_Class_Importer extends Ac_Etl_Import {

    function __construct() {
        
        parent::__construct();
        
        $this->setLogger(new Ac_Etl_Logger_Collector);
        
        $this->setTables(array(
            'categories' => array(
                'sqlTableName' => '#__test_categories_import',
            ),
            'categoryNames' => array(
                'sqlTableName' => '#__test_classifiers_import',
                'restriction' => array(
                    'classifierType' => 'category',
                ),
            ),
            'types' => array(
                'sqlTableName' => '#__test_classifiers_import',
                'restriction' => array(
                    'classifierType' => 'type',
                ),
            ),
            'itemCategories' => array(
                'sqlTableName' => '#__test_item_categories_import',
            ),
            'items' => array(
                'sqlTableName' => '#__test_items_import',
            ),
        ));
        
        $this->addLoader(array(
            'id' => 'items',
            'columns' => array(
                'name' => array(
                    'destTableId' => 'items',
                ),
                'description' => array(
                    'destTableId' => 'items',
                ),
                'imageUrl' => array(
                    'destColName' => 'pictureUrl',
                    'destTableId' => 'items',
                ),
                'type' => array(
                    'destTableId' => 'items',
                ),
                'relatedText1' => array(
                    'destTableId' => 'items',
                ),
                /*'categories' => array(
                    'class' => 'Test_CategoriesColumn',
                    'destTableId' => 'items',
                ),*/
                'categories' => array(
                    'class' => 'Ac_Etl_Hierarchy_Column',
                    'destTableId' => 'items',
                    'itemToCatTableId' => 'itemCategories',
                    'parentChildTableId' => 'categories',
                    'itemCatNameCol' => 'categoryName',
                    'listSeparator' => ';',
                ),
            ))
        );
        
        $this->addLoader(array(
            'id' => 'types',
            'columns' => array(
                'name' => array('destTableId' => 'types', 'destColName' => 'title'),
                'description' => array('destTableId' => 'types'),
            ),
        ));
        
        // Type writer
        $this->setOperations(array(
            'typeImporter' => array(
                'class' => 'Ac_Etl_Operation_Copy',
                'tableId' => 'items',
                'targetTableId' => 'types',
                'distinct' => true,
                'ignoreLineNumbers' => true,
                'colMatches' => array('title' => 'type'),
                'cleanTargetTable' => true,
                'selectPrototype' => array(
                    'where' => array(
                        'c' => "LENGTH(t.type) > 0",
                    ),
                ),
                'innerOperations' => array(
                    'updater' => array(
                        'class' => 'Ac_Etl_Operation_Write',
                        'statusColName' => 'importStatus',
                        'problemsColName' => 'problems',
                        'tableId' => 'types',
                        'targetSqlName' => '#__test_types',
                        'nameMap' => array('title' => 'name'),
                        'keyMap' => array('classifierId' => 'id'),
                    ),
                ),
                'reverseKeys' => array(
                    'type' => 'title',
                ),
                'reverseMatches' => array(
                    'typeId' => 'classifierId',
                ),
            ),
            
            'categoryOperation' => array(

                'class' => 'Ac_Etl_Hierarchy_Operation',

                //  Categories:

                    // import table: [required]

                    'categoryListImportId' => 'categoryNames', // Table ID

                    // destination table: [required]

                    'categoryListTargetName' => '#__test_categories', // Table SQL name [required]
                    'categoryNameCol' => 'name', // Column with category names [required]
                    'categoryIdCol' => 'id', // Column with category IDs [required]

                // Item-to-category mapping:

                    // internal import table: [optional]

                    'itemCategoryImportId' => 'itemCategories', // Table ID
                    //'itemCategoryNameCol' => 'categoryName', // Column with category names
                    //'itemCategoryIdCol' => 'categoryId', // Column with category IDs
                    'itemIdCol' => array('items', 'itemId'), // Column with item IDs

                    // destination table: [optional]

                    'itemCategoryTargetName' => '#__test_item_categories', // Table SQL name
                    'targetCategoryIdCol' => 'categoryId',  // Column with category IDs
                    'targetItemIdCol' => 'itemId', // Column with item IDs

                // Category-to-category mapping:

                    // internal import table [optional]

                    'ccImportId' => 'categories', // Table ID
                    //'ccImportCategoryIdCol' => 'categoryId', // Column with category IDs
                    //'ccImportCategoryNameCol' => 'categoryName', // Column with category names
                    //'ccImportParentIdCol' => 'parentId', // Column with parent IDs
                    //'ccImportParentNameCol' => 'parentName', // Column with parent name

                    'categoryColMap' => array(), // Additional columns' map

                    // destination table:

                    //'ccTargetName' => '#__test_categories', // Target SQL name
                    //'ccTargetCategoryIdCol' => 'id', // Column with category IDs
                    'ccTargetParentIdCol' => 'parentId', // Column with parent IDs
                
            ),
            
        ));
        
        
    }
    
}