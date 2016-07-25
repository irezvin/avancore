<?php

class Ac_Etl_Concrete_Virtuemart extends Ac_Etl_Import {
    
    protected $defaultVendorId = false;

    protected $defaultManufacturerCategoryId = false;

    protected $productCurrency = false;
    
    function __construct() {
        
        parent::__construct();
        
        $this->setTables(array(
            /*'categories' => array(
                'sqlTableName' => '#__test_categories_import',
            ),*/
            
            'categoryNames' => array(
                'sqlTableName' => '#__vm_classifiers',
                'restriction' => array(
                    'classifierType' => 'category',
                ),
            ),
            
            'customNames' => array(
                'sqlTableName' => '#__vm_classifiers',
                'restriction' => array(
                    'classifierType' => 'custom',
                ),
            ),
            
            'productImages' => array(
                'sqlTableName' => '#__vm_classifiers',
                'restriction' => array(
                    'classifierType' => 'productImage',
                ),
            ),
            
            'manufacturers' => array(
                'sqlTableName' => '#__vm_classifiers',
                'restriction' => array(
                    'classifierType' => 'manufacturer',
                ),
            ),
            
            'products' => array(
                'sqlTableName' => '#__vm_products',
            ),
            
            'productCustoms' => array(
                'sqlTableName' => '#__vm_relations',
                'restriction' => array(
                    'type' => 'customs',
                ),
            ),
            'productCategories' => array(
                'sqlTableName' => '#__vm_relations',
                'restriction' => array(
                    'type' => 'categories',
                ),
            ),
            'productManufacturers' => array(
                'sqlTableName' => '#__vm_relations',
                'restriction' => array(
                    'type' => 'manufacturers',
                ),
            ),
            'categoryParents' => array(
                'sqlTableName' => '#__vm_relations',
                'restriction' => array(
                    'type' => 'categories',
                ),
            ),
        ));
        
        $this->addColumns(array(
            'description' => array(
                'destTableId' => 'products',
                'destColName' => 'product_name',
            ),
            'name' => array(
                'destTableId' => 'products',
                'destColName' => 'product_name',
            ),
            'sku' => array(
                'destTableId' => 'products',
                'destColName' => 'product_sku',
            ),
            
            'img' => array(
                'destTableId' => 'products',
                'destColName' => 'pictureUrl',
                'param' => array(
                    'filters' => array(
                        'f1' => array(
                            'class' => 'Ac_Param_Decorator',
                            'decorator' => array(
                                'class' => 'Ac_Decorator_Callback',
                                'callback' => 'basename'
                            ),
                        ),
                    ),
                ),
            ),
            
            'package' => array(
                'destTableId' => 'productCustoms',
                'destColName' => 'value',
                'destTableExtra' => array('rightName' => 'Количество в упаковке'),
                'add' => true,
            ),
            'chamfer' => array(
                'destTableId' => 'productCustoms',
                'destColName' => 'value',
                'destTableExtra' => array('rightName' => 'Фаска'),
                'add' => true,
            ),
            'style' => array(
                'destTableId' => 'productCustoms',
                'destColName' => 'value',
                'destTableExtra' => array('rightName' => 'Стиль'),
                'add' => true,
            ),
            'size_custom' => array(
                'destTableId' => 'productCustoms',
                'destColName' => 'value',
                'srcColName' => 'size',
                'destTableExtra' => array('rightName' => 'Размер'),
                'add' => true,
            ),
            'design_custom' => array(
                'destTableId' => 'productCustoms',
                'destColName' => 'value',
                'srcColName' => 'size',
                'destTableExtra' => array('rightName' => 'Дизайн'),
                'add' => true,
            ),
            'country' => array(
                'destTableId' => 'productCustoms',
                'destColName' => 'value',
                'destTableExtra' => array('rightName' => 'Страна'),
                'add' => true,
            ),
            'thickness' => array(
                'destTableId' => 'productCustoms',
                'destColName' => 'value',
                'destTableExtra' => array('rightName' => 'Толщина'),
                'add' => true,
            ),
            
            
            'style2products' => array(
                'srcColName' => 'style',
                'destTableId' => 'products',
                'destColName' => 'style',
            ),
            'size' => array(
                'class' => 'Ac_Etl_Column_Regex',
                'regex' => '/([0-9]+)[\sxх]+([0-9]+)[\sxх]+([0-9]+)\s*(\w+)/u',
                'destTableId' => 'products',
                'matchToColumnMap' => array(
                    1 => 'product_length',
                    2 => 'product_width',
                    3 => 'product_height',
                    4 => 'product_lwh_uom',
                ),
            ),
            'price' => array(
                'class' => 'Ac_Etl_Column_Regex',
                'regex' => '/([0-9\.]*[0-9]+)\s*(\w+)/u',
                'destTableId' => 'products',
                'matchToColumnMap' => array(
                    1 => 'product_price',
                    2 => 'product_currency',
                ),
                'matchDecorators' => array(
                    2 => array(
                        'class' => 'Ac_Decorator_Map',
                        'map' => array(
                            'грн' => 199,
                            'р' => 131,
                            'руб' => 131,
                        ),
                    ),
                )
            ),
            'package' => array(
                'class' => 'Ac_Etl_Column_Regex',
                'regex' => '/([0-9\.]*[0-9]+)\s*(\w+)/u',
                'destTableId' => 'products',
                'matchToColumnMap' => array(
                    1 => 'product_packaging',
                    2 => 'product_unit',
                ),
            ),
            'manufacturer' => array(
                'destTableId' => 'products',
                'destColName' => 'manufacturerName',
            ),
            
            'categoryMaker' => array(
                'class' => 'Ac_Etl_Column_Joiner',
                'destTableId' => 'products',
                'colList' => array('manufacturerName', 'style'),
                'glue' => '/',
                'destColName' => 'categoryPath',
            ),
            
            'categoryPath' => array(
                'class' => 'Ac_Etl_Hierarchy_Column',
                'destTableId' => 'products',
                'getValueFromDestColumn' => true,
                'leaveOriginalValue' => true,
                'itemToCatTableId' => 'products',
                'parentChildTableId' => 'categoryParents',
                'catNameCol' => 'leftName',
                'parentNameCol' => 'rightName',
                'itemCatNameCol' => 'categoryName',
            ),
            
            
            /*'categoryName' => array(
                'srcColName' => 'style',
                'destTableId' => 'products',
                'destColName' => 'categoryName',
            ),*/
            /**/
            
        ));
        
        $this->setOperations(array(
            
            'skuDeduplicator' => array(
                'class' => 'Ac_Etl_Operation_Query',
                'sql' => "
                    UPDATE
                        [[importDb]].#__vm_products prod
                    LEFT JOIN (
                        SELECT p.id, p.product_sku, p.importId AS importId, COUNT(DISTINCT p1.id) as p1count
                        FROM [[importDb]].#__vm_products p 
                        INNER JOIN [[importDb]].#__vm_products p1 on p1.importId = p.importId and p1.product_sku = p.product_sku and p1.id < p.id
                        WHERE p1.importId = {{importId}}
                        GROUP BY p.id
                    ) AS psku
                    ON prod.id = psku.id
                    AND prod.importId = psku.importId

                    SET prod.product_sku_unique = CONCAT(prod.product_sku, IFNULL(CONCAT('-', psku.p1count), ''))

                    WHERE prod.importId = {{importId}} AND ISNULL(prod.product_sku_unique)
                ",
            ),
            
            /*'pathCreator' => array(
                'class' => 'Ac_Etl_Operation_Query',
                'sql' => "
                    UPDATE
                        [[importDb]].#__vm_products prod
                    SET categoryName = CONCAT(prod.manufacturerName, '/', prod.style)
                    WHERE prod.importId = {{importId}}
                ",
            ),*/
            
            'productsWriter' => array(
                'class' => 'Ac_Etl_Operation_Writer',
                'tableId' => 'products',
                'targetSqlName' => 'jos_virtuemart_products',
                /*'selectPrototype' => array(
                    'tables' => array(
                        'enGb' => array(
                            'name' => 'jos_virtuemart_products_en_gb',
                            'joinsAlias' => 't',
                            'joinOn' => 't.virtuemart_product_id = '
                        ),
                    ),
                ),*/
                'nameMap' => array(
                    'product_sku_unique' => 'product_sku'
                ),
                'contentMap' => array(
                    'product_length' => 'product_length',
                    'product_width' => 'product_width',
                    'product_height' => 'product_height',
                    'product_lwh_uom' => 'product_lwh_uom',
                    'product_packaging' => 'product_packaging',
                    'product_unit' => 'product_unit',
                ),
                'defaults' => array(
                    'virtuemart_vendor_id' => 1,
                    
                    'product_weight' => 0,
                    'product_weight_uom' => 'KG',
                    'product_length' => 0,
                    'product_width' => 0,
                    'product_height' => 0,
                    'product_lwh_uom' => 'мм',
                    'product_packaging' => 0,
                    'product_unit' => 'кв.м.',
                    'product_url' => '',
                    'product_in_stock' => 999,
                    'product_ordered' => 0,
                    'low_stock_notification' => 5,
                    'product_available_date' => new Ac_Sql_Expression("DATE_FORMAT(NOW(), '%Y-%m-%d 00:00:00')"),
                    'product_availability' => '',
                    'product_special' => 0,
                    'product_sales' => 0,
                    'product_params' => 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|',
                    'hits' => 0,
                    'intnotes' => '',
                    'metarobot' => '',
                    'metaauthor' => '',
                    'layout' => '',
                    'published' => 1,
                     
                    
                ),
                'keyMap' => array('virtuemart_product_id' => 'virtuemart_product_id'),
            ),
            'productDescriptionsWriter' => array(
                'class' => 'Ac_Etl_Operation_Writer',
                'tableId' => 'products',
                'targetSqlName' => 'jos_virtuemart_products_en_gb',
                'nameMap' => array('virtuemart_product_id' => 'virtuemart_product_id'),
                'contentMap' => array(
                    'product_name' => 'product_name',
                    'product_s_desc' => 'product_s_desc',
                    'product_desc' => 'product_desc',
                    array(new Ac_Sql_Expression("IFNULL(t.slug, REPLACE(t.product_sku_unique,' ', '-'))"), 'slug'),
                ),
                'defaults' => array(
                    'product_s_desc' => '',
                    'product_desc' => '',
                    'product_name' => new Ac_Sql_Expression("IFNULL(t.product_sku, '')"),
                ),
            ),
            
            'pricesWriter' => array(
                'class' => 'Ac_Etl_Operation_Writer',
                'tableId' => 'products',
                'targetSqlName' => 'jos_virtuemart_product_prices',
                'nameMap' => array('virtuemart_product_id' => 'virtuemart_product_id'),
                'selectPrototype' => array(
                    'where' => array(
                        'hasPrice' => 't.product_price IS NOT NULL',
                    )
                ),
                'contentMap' => array(
                    'product_price' => 'product_price',
                    'product_currency' => 'product_currency',
                ),
                'defaults' => array(
                    'override' => 0,
                    'product_override_price' => 0,
                    'product_price_vdate' => '0000-00-00 00:00:00',
                    'product_price_edate' => '0000-00-00 00:00:00',
                    'price_quantity_start' => 0,
                    'price_quantity_end' => 0,
                ),
            ),
            
            // ---- manufacturers ---
            
            'manufacturerNamesCreator' => array(
                'class' => 'Ac_Etl_Operation_Copier',
                'tableId' => 'products',
                'targetTableId' => 'manufacturers',
                'ignoreLineNumbers' => true,
                'colMatches' => array('title' => 'manufacturerName'),
                'selectPrototype' => array(
                    'where' => array(
                        'c' => "LENGTH(t.manufacturerName) > 0",
                    ),
                ),
                'innerOperations' => array(
                    'updater' => array(
                        'class' => 'Ac_Etl_Operation_Writer',
                        'tableId' => 'manufacturers',
                        'statusColName' => 'importStatus',
                        'targetSqlName' => 'jos_virtuemart_manufacturers_en_gb',
                        'nameMap' => array('title' => 'mf_name'),
                        'defaults' => array('slug' => new Ac_Sql_Expression('LCASE(t.title)')),
                        'keyMap' => array('classifierId' => 'virtuemart_manufacturer_id'),
                    ),
                ),
                'reverseKeys' => array(
                    'manufacturerName' => 'title',
                ),
                'reverseMatches' => array(
                    'manufacturerId' => 'classifierId',
                ),
            ),
                            
            'manufacturersCreator' => array(
                'class' => 'Ac_Etl_Operation_Writer',
                'tableId' => 'manufacturers',
                'targetSqlName' => 'jos_virtuemart_manufacturers',
                'nameMap' => array('classifierId' => 'virtuemart_manufacturer_id'),
                /*'selectPrototype' => array(
                    'where' => array(
                        'ofCreatedMfs' => "t.importStatus = 'created'",
                    )
                ),*/
                'defaults' => array(
                    'virtuemart_manufacturercategories_id' => 1,
                    'hits' => 0,
                    'published' => 1,
                ),
            ),
                            
            'productManufacturers' => array(
                'class' => 'Ac_Etl_Operation_Writer',
                'tableId' => 'products',
                'targetSqlName' => 'jos_virtuemart_product_manufacturers',
                'nameMap' => array('manufacturerId' => 'virtuemart_manufacturer_id', 'virtuemart_product_id' => 'virtuemart_product_id'),
            ),

            // --- categories ---
            
            
            'categoryNamesCreator' => array(
                'class' => 'Ac_Etl_Operation_Copier',
                'tableId' => 'categoryParents',
                'targetTableId' => 'categoryNames',
                'ignoreLineNumbers' => true,
                'colMatches' => array('title' => 'leftName'),
                'selectPrototype' => array(
                    'where' => array(
                        'c' => "LENGTH(t.leftName) > 0",
                    ),
                ),
                'innerOperations' => array(
                    'updater' => array(
                        'class' => 'Ac_Etl_Operation_Writer',
                        'tableId' => 'categoryNames',
                        'statusColName' => 'importStatus',
                        'targetSqlName' => 'jos_virtuemart_categories_en_gb',
                        'nameMap' => array('title' => 'category_name'),
                        'defaults' => array('slug' => new Ac_Sql_Expression('LCASE(t.title)')),
                        'keyMap' => array('classifierId' => 'virtuemart_category_id'),
                    ),
                ),
                
                'reverseKeys' => array(
                    'leftName' => 'title',
                ),
                'reverseMatches' => array(
                    'leftId' => 'classifierId',
                ),
            ),
            
            'categoriesCreator' => array(
                'class' => 'Ac_Etl_Operation_Writer',
                'tableId' => 'categoryNames',
                'targetSqlName' => 'jos_virtuemart_categories',
                'nameMap' => array('classifierId' => 'virtuemart_category_id'),
                /*'selectPrototype' => array(
                    'where' => array(
                        'ofCreatedMfs' => "t.importStatus = 'created'",
                    )
                ),*/
                'defaults' => array(
                    'virtuemart_vendor_id' => 1,
                ),
            ),
            
            'categoryParentIds' => array(
                'class' => 'Ac_Etl_Operation_Copier',
                'tableId' => 'categoryParents',
                'targetTableId' => 'categoryNames',
                'reverseKeys' => array('rightName' => 'title'),
                'reverseMatches' => array('rightId' => 'classifierId'),
            ),
            
            'categoryParents' => array(
                'class' => 'Ac_Etl_Operation_Writer',
                'tableId' => 'categoryParents',
                'targetSqlName' => 'jos_virtuemart_category_categories',
                'nameMap' => array('leftId' => 'category_child_id'),
                'contentMap' => array('rightId' => 'category_parent_id'),
                'defaults' => array('category_parent_id' => new Ac_Sql_Expression('0')),
                'insertIgnore' => true,
            ),
            
            'productCategoryIds' => array(
                'class' => 'Ac_Etl_Operation_Copier',
                'tableId' => 'products',
                'targetTableId' => 'categoryParents',
                'reverseKeys' => array('categoryName' => 'leftName'),
                'reverseMatches' => array('categoryId' => 'leftId'),
            ),
                            
            'productCategories' => array(
                'class' => 'Ac_Etl_Operation_Writer',
                'tableId' => 'products',
                'targetSqlName' => 'jos_virtuemart_product_categories',
                'nameMap' => array('categoryId' => 'virtuemart_category_id', 'virtuemart_product_id' => 'virtuemart_product_id'),
            ),
            
            
            'pictureCreator' => array(
                'class' => 'Ac_Etl_Operation_Writer',
                'tableId' => 'products',
                'targetSqlName' => 'jos_virtuemart_medias',
                'nameMap' => array(array(new Ac_Sql_Value('product'), 'file_type'), 'pictureUrl' => 'file_title'),
                'keyMap' => array('pictureId' => 'virtuemart_media_id'),
                'selectPrototype' => array(
                    'where' => array(
                        'c' => 'LENGTH(t.pictureUrl) > 0',
                    ),
                ),
                'contentMap' => array(
                    array(new Ac_Sql_Expression("CONCAT('images/stories/virtuemart/product/', t.pictureUrl)"), 'file_url'),
                    array(new Ac_Sql_Expression("CONCAT(IFNULL(t.manufacturerName, ''), ' ', IFNULL(t.product_name, ''))"), 'file_meta'),
                    array(new Ac_Sql_Expression("CONCAT('images/stories/virtuemart/product/resized/', REPLACE(t.pictureUrl, '.jpg', '90x90.jpg'))"), 'file_url_thumb'),
                ),
                'defaults' => array(
                    'virtuemart_vendor_id' => 1,
                    'file_description' => '',
                    'file_mimetype' => 'image/jpeg',
                    'file_is_product_image' => 0,
                    'file_is_downloadable' => 0,
                    'file_is_forSale' => 0,
                    'file_params' => '',
                    'shared' => 0,
                    'published' => 1,
                ),
            ),
            
            'productMedias' => array(
                'class' => 'Ac_Etl_Operation_Writer',
                'tableId' => 'products',
                'targetSqlName' => 'jos_virtuemart_product_medias',
                'nameMap' => array('pictureId' => 'virtuemart_media_id', 'virtuemart_product_id' => 'virtuemart_product_id'),
                'defaults' => array(
                    'ordering' => 0,
                ),
            ),
            
            'customsCreator' => array(
                'class' => 'Ac_Etl_Operation_Copier',
                'tableId' => 'productCustoms',
                'targetTableId' => 'customNames',
                'ignoreLineNumbers' => true,
                'colMatches' => array('title' => 'rightName'),
                'innerOperations' => array(
                    'updater' => array(
                        'class' => 'Ac_Etl_Operation_Writer',
                        'tableId' => 'customNames',
                        'statusColName' => 'importStatus',
                        'targetSqlName' => 'jos_virtuemart_customs',
                        'nameMap' => array('title' => 'custom_title'),
                        'defaults' => array(
                            'virtuemart_vendor_id' => 1,
                            'custom_jplugin_id' => 0,
                            'custom_element' => 0,
                            'admin_only' => 0,
                            'custom_tip' => '',
                            'custom_value' => '',
                            'custom_field_desc' => '',
                            'field_type' => 'S',
                            'is_list' => 0,
                            'is_hidden' => 0,
                            'is_cart_attribute' => 0,
                            'layout_pos' => '',
                            'custom_params' => null,
                            'shared' => 0,
                            'published' => 1,
                            'created_on' => new Ac_Sql_Value('NOW()'),
                        ),
                        'keyMap' => array('classifierId' => 'virtuemart_custom_id'),
                    ),
                ),
                
                'reverseKeys' => array(
                    'rightName' => 'title',
                ),
                'reverseMatches' => array(
                    'rightId' => 'classifierId',
                ),
            ),

            // customsCreator will be used instead
            
            /*'customsResolver' => array(
                'class' => 'Ac_Etl_Operation_Writer',
                'tableId' => 'productCustoms',
                'statusColName' => 'importStatus',
                'targetSqlName' => 'jos_virtuemart_customs',
                'nameMap' => array('rightName' => 'custom_title'),
                'allowCreate' => false,
                'keyMap' => array('rightId' => 'virtuemart_custom_id'),
            ),*/
            
            'customsWriter' => array(
                'class' => 'Ac_Etl_Operation_Writer',
                'tableId' => 'productCustoms',
                'targetSqlName' => 'jos_virtuemart_product_customfields',
                'selectPrototype' => array(
                    'tables' => array(
                        'products' => array(
                            'name' => '#__vm_products',
                            'joinsAlias' => 't',
                            'joinsOn' => 't.importId = products.importId and t.lineNo = products.lineNo'
                        ),
                    ),
                    'where' => array(
                        'hasCustomValue' => new Ac_Sql_Expression('LENGTH(t.value) > 0'),
                    ),
                    'usedAliases' => array('products'),
                ),
                'nameMap' => array(
                    'rightId' => 'virtuemart_custom_id', 
                    array(new Ac_Sql_Expression('products.virtuemart_product_id'), 'virtuemart_product_id')),
                'contentMap' => array('value' => 'custom_value'),
                'defaults' => array('custom_price' => 0),
            ),
            
        ));
        
    }

    function setDefaultVendorId($defaultVendorId) {
        
        $this->defaultVendorId = $defaultVendorId;
        
        $def = $this->getOperation('productsWriter')->getDefaults();
        if (strlen($this->defaultVendorId))
            $def['virtuemart_vendor_id'] = $this->defaultVendorId;
        else 
            unset($def['virtuemart_vendor_id']);
        $this->getOperation('productsWriter')->setDefaults($def);
        
    }

    function getDefaultVendorId() {
        return $this->defaultVendorId;
    }

    function setDefaultManufacturerCategoryId($defaultManufacturerCategoryId) {
        $this->defaultManufacturerCategoryId = $defaultManufacturerCategoryId;
    }

    function getDefaultManufacturerCategoryId() {
        return $this->defaultManufacturerCategoryId;
    }

    function setProductCurrency($productCurrency) {
        $this->productCurrency = $productCurrency;
    }

    function getProductCurrency() {
        return $this->productCurrency;
    }    
    
}