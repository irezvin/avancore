<?php 

/**
 * @property Child_ComponentsAccessor $c Convenient access to application components
 */
abstract class Child_DomainBase extends Sample {

    protected $componentAliases = [
        'people' => 'Child_Person_Mapper',
        'perks' => 'Child_Perk_Mapper',
        'personAlbums' => 'Child_Person_Album_Mapper',
        'personPhotos' => 'Child_Person_Photo_Mapper',
        'personPosts' => 'Child_Person_Post_Mapper',
        'publish' => 'Child_Publish_ImplMapper',
        'relationTypes' => 'Child_Relation_Type_Mapper',
        'relations' => 'Child_Relation_Mapper',
        'religion' => 'Child_Religion_Mapper',
        'shopCategories' => 'Child_Shop_Category_Mapper',
        'shopClassifier' => 'Child_Shop_Classifier_Mapper',
        'shopClassifierType' => 'Child_Shop_Classifier_Type_Mapper',
        'shopProductExtraCodes' => 'Child_Shop_Product_Extra_Code_ImplMapper',
        'shopProductNotes' => 'Child_Shop_Product_Note_ImplMapper',
        'shopProducts' => 'Child_Shop_Product_Mapper',
        'shopSpecComputer' => 'Child_Shop_Spec_Computer_ImplMapper',
        'shopSpecFood' => 'Child_Shop_Spec_Food_ImplMapper',
        'shopSpecLaptop' => 'Child_Shop_Spec_Laptop_ImplMapper',
        'shopSpecMonitor' => 'Child_Shop_Spec_Monitor_ImplMapper',
        'shopSpecs' => 'Child_Shop_Spec_Mapper',
        'tags' => 'Child_Tag_Mapper',
        'treeAdjacent' => 'Child_Tree_Adjacent_Mapper',
        'treeCombos' => 'Child_Tree_Combo_Mapper',
        'treeRecords' => 'Child_Tree_Record_Mapper',
    ];

    protected function doOnInitialize() {
        parent::doOnInitialize();
 
        $this->setMapperAliases([
    'Sample_Person_Mapper' => 'Child_Person_Mapper',
    'Sample_Perk_Mapper' => 'Child_Perk_Mapper',
    'Sample_Person_Album_Mapper' => 'Child_Person_Album_Mapper',
    'Sample_Person_Photo_Mapper' => 'Child_Person_Photo_Mapper',
    'Sample_Person_Post_Mapper' => 'Child_Person_Post_Mapper',
    'Sample_Publish_ImplMapper' => 'Child_Publish_ImplMapper',
    'Sample_Relation_Type_Mapper' => 'Child_Relation_Type_Mapper',
    'Sample_Relation_Mapper' => 'Child_Relation_Mapper',
    'Sample_Religion_Mapper' => 'Child_Religion_Mapper',
    'Sample_Shop_Category_Mapper' => 'Child_Shop_Category_Mapper',
    'Sample_Shop_Classifier_Mapper' => 'Child_Shop_Classifier_Mapper',
    'Sample_Shop_Classifier_Type_Mapper' => 'Child_Shop_Classifier_Type_Mapper',
    'Sample_Shop_Product_Extra_Code_ImplMapper' => 'Child_Shop_Product_Extra_Code_ImplMapper',
    'Sample_Shop_Product_Note_ImplMapper' => 'Child_Shop_Product_Note_ImplMapper',
    'Sample_Shop_Product_Mapper' => 'Child_Shop_Product_Mapper',
    'Sample_Shop_Spec_Computer_ImplMapper' => 'Child_Shop_Spec_Computer_ImplMapper',
    'Sample_Shop_Spec_Food_ImplMapper' => 'Child_Shop_Spec_Food_ImplMapper',
    'Sample_Shop_Spec_Laptop_ImplMapper' => 'Child_Shop_Spec_Laptop_ImplMapper',
    'Sample_Shop_Spec_Monitor_ImplMapper' => 'Child_Shop_Spec_Monitor_ImplMapper',
    'Sample_Shop_Spec_Mapper' => 'Child_Shop_Spec_Mapper',
    'Sample_Tag_Mapper' => 'Child_Tag_Mapper',
    'Sample_Tree_Adjacent_Mapper' => 'Child_Tree_Adjacent_Mapper',
    'Sample_Tree_Combo_Mapper' => 'Child_Tree_Combo_Mapper',
    'Sample_Tree_Record_Mapper' => 'Child_Tree_Record_Mapper',
], true);
    }


    protected function doGetMapperPrototypes() {
        return [
            'Child_Person_Mapper' => [
                'class' => 'Child_Person_Mapper',
            ],
            'Child_Perk_Mapper' => [
                'class' => 'Child_Perk_Mapper',
            ],
            'Child_Person_Album_Mapper' => [
                'class' => 'Child_Person_Album_Mapper',
            ],
            'Child_Person_Photo_Mapper' => [
                'class' => 'Child_Person_Photo_Mapper',
            ],
            'Child_Person_Post_Mapper' => [
                'class' => 'Child_Person_Post_Mapper',
            ],
            'Child_Publish_ImplMapper' => [
                'class' => 'Child_Publish_ImplMapper',
            ],
            'Child_Relation_Type_Mapper' => [
                'class' => 'Child_Relation_Type_Mapper',
            ],
            'Child_Relation_Mapper' => [
                'class' => 'Child_Relation_Mapper',
            ],
            'Child_Religion_Mapper' => [
                'class' => 'Child_Religion_Mapper',
            ],
            'Child_Shop_Category_Mapper' => [
                'class' => 'Child_Shop_Category_Mapper',
            ],
            'Child_Shop_Classifier_Mapper' => [
                'class' => 'Child_Shop_Classifier_Mapper',
            ],
            'Child_Shop_Classifier_Type_Mapper' => [
                'class' => 'Child_Shop_Classifier_Type_Mapper',
            ],
            'Child_Shop_Product_Extra_Code_ImplMapper' => [
                'class' => 'Child_Shop_Product_Extra_Code_ImplMapper',
            ],
            'Child_Shop_Product_Note_ImplMapper' => [
                'class' => 'Child_Shop_Product_Note_ImplMapper',
            ],
            'Child_Shop_Product_Mapper' => [
                'class' => 'Child_Shop_Product_Mapper',
            ],
            'Child_Shop_Spec_Computer_ImplMapper' => [
                'class' => 'Child_Shop_Spec_Computer_ImplMapper',
            ],
            'Child_Shop_Spec_Food_ImplMapper' => [
                'class' => 'Child_Shop_Spec_Food_ImplMapper',
            ],
            'Child_Shop_Spec_Laptop_ImplMapper' => [
                'class' => 'Child_Shop_Spec_Laptop_ImplMapper',
            ],
            'Child_Shop_Spec_Monitor_ImplMapper' => [
                'class' => 'Child_Shop_Spec_Monitor_ImplMapper',
            ],
            'Child_Shop_Spec_Mapper' => [
                'class' => 'Child_Shop_Spec_Mapper',
            ],
            'Child_Tag_Mapper' => [
                'class' => 'Child_Tag_Mapper',
            ],
            'Child_Tree_Adjacent_Mapper' => [
                'class' => 'Child_Tree_Adjacent_Mapper',
            ],
            'Child_Tree_Combo_Mapper' => [
                'class' => 'Child_Tree_Combo_Mapper',
            ],
            'Child_Tree_Record_Mapper' => [
                'class' => 'Child_Tree_Record_Mapper',
            ],
        ];
    }
    
    /**
     * @return Child_Person_Mapper 
     */
    function getChildPersonMapper() {
        return $this->getMapper('Child_Person_Mapper');
    }
    
    /**
     * @return Child_Perk_Mapper 
     */
    function getChildPerkMapper() {
        return $this->getMapper('Child_Perk_Mapper');
    }
    
    /**
     * @return Child_Person_Album_Mapper 
     */
    function getChildPersonAlbumMapper() {
        return $this->getMapper('Child_Person_Album_Mapper');
    }
    
    /**
     * @return Child_Person_Photo_Mapper 
     */
    function getChildPersonPhotoMapper() {
        return $this->getMapper('Child_Person_Photo_Mapper');
    }
    
    /**
     * @return Child_Person_Post_Mapper 
     */
    function getChildPersonPostMapper() {
        return $this->getMapper('Child_Person_Post_Mapper');
    }
    
    /**
     * @return Child_Publish_ImplMapper 
     */
    function getChildPublishImplMapper() {
        return $this->getMapper('Child_Publish_ImplMapper');
    }
    
    /**
     * @return Child_Relation_Type_Mapper 
     */
    function getChildRelationTypeMapper() {
        return $this->getMapper('Child_Relation_Type_Mapper');
    }
    
    /**
     * @return Child_Relation_Mapper 
     */
    function getChildRelationMapper() {
        return $this->getMapper('Child_Relation_Mapper');
    }
    
    /**
     * @return Child_Religion_Mapper 
     */
    function getChildReligionMapper() {
        return $this->getMapper('Child_Religion_Mapper');
    }
    
    /**
     * @return Child_Shop_Category_Mapper 
     */
    function getChildShopCategoryMapper() {
        return $this->getMapper('Child_Shop_Category_Mapper');
    }
    
    /**
     * @return Child_Shop_Classifier_Mapper 
     */
    function getChildShopClassifierMapper() {
        return $this->getMapper('Child_Shop_Classifier_Mapper');
    }
    
    /**
     * @return Child_Shop_Classifier_Type_Mapper 
     */
    function getChildShopClassifierTypeMapper() {
        return $this->getMapper('Child_Shop_Classifier_Type_Mapper');
    }
    
    /**
     * @return Child_Shop_Product_Extra_Code_ImplMapper 
     */
    function getChildShopProductExtraCodeImplMapper() {
        return $this->getMapper('Child_Shop_Product_Extra_Code_ImplMapper');
    }
    
    /**
     * @return Child_Shop_Product_Note_ImplMapper 
     */
    function getChildShopProductNoteImplMapper() {
        return $this->getMapper('Child_Shop_Product_Note_ImplMapper');
    }
    
    /**
     * @return Child_Shop_Product_Mapper 
     */
    function getChildShopProductMapper() {
        return $this->getMapper('Child_Shop_Product_Mapper');
    }
    
    /**
     * @return Child_Shop_Spec_Computer_ImplMapper 
     */
    function getChildShopSpecComputerImplMapper() {
        return $this->getMapper('Child_Shop_Spec_Computer_ImplMapper');
    }
    
    /**
     * @return Child_Shop_Spec_Food_ImplMapper 
     */
    function getChildShopSpecFoodImplMapper() {
        return $this->getMapper('Child_Shop_Spec_Food_ImplMapper');
    }
    
    /**
     * @return Child_Shop_Spec_Laptop_ImplMapper 
     */
    function getChildShopSpecLaptopImplMapper() {
        return $this->getMapper('Child_Shop_Spec_Laptop_ImplMapper');
    }
    
    /**
     * @return Child_Shop_Spec_Monitor_ImplMapper 
     */
    function getChildShopSpecMonitorImplMapper() {
        return $this->getMapper('Child_Shop_Spec_Monitor_ImplMapper');
    }
    
    /**
     * @return Child_Shop_Spec_Mapper 
     */
    function getChildShopSpecMapper() {
        return $this->getMapper('Child_Shop_Spec_Mapper');
    }
    
    /**
     * @return Child_Tag_Mapper 
     */
    function getChildTagMapper() {
        return $this->getMapper('Child_Tag_Mapper');
    }
    
    /**
     * @return Child_Tree_Adjacent_Mapper 
     */
    function getChildTreeAdjacentMapper() {
        return $this->getMapper('Child_Tree_Adjacent_Mapper');
    }
    
    /**
     * @return Child_Tree_Combo_Mapper 
     */
    function getChildTreeComboMapper() {
        return $this->getMapper('Child_Tree_Combo_Mapper');
    }
    
    /**
     * @return Child_Tree_Record_Mapper 
     */
    function getChildTreeRecordMapper() {
        return $this->getMapper('Child_Tree_Record_Mapper');
    }
    
    /**
     * @return Child_Person_Mapper 
     */
    function getSamplePersonMapper() {
        return $this->getChildPersonMapper();
    }
    
    /**
     * @return Child_Perk_Mapper 
     */
    function getSamplePerkMapper() {
        return $this->getChildPerkMapper();
    }
    
    /**
     * @return Child_Person_Album_Mapper 
     */
    function getSamplePersonAlbumMapper() {
        return $this->getChildPersonAlbumMapper();
    }
    
    /**
     * @return Child_Person_Photo_Mapper 
     */
    function getSamplePersonPhotoMapper() {
        return $this->getChildPersonPhotoMapper();
    }
    
    /**
     * @return Child_Person_Post_Mapper 
     */
    function getSamplePersonPostMapper() {
        return $this->getChildPersonPostMapper();
    }
    
    /**
     * @return Child_Publish_ImplMapper 
     */
    function getSamplePublishImplMapper() {
        return $this->getChildPublishImplMapper();
    }
    
    /**
     * @return Child_Relation_Type_Mapper 
     */
    function getSampleRelationTypeMapper() {
        return $this->getChildRelationTypeMapper();
    }
    
    /**
     * @return Child_Relation_Mapper 
     */
    function getSampleRelationMapper() {
        return $this->getChildRelationMapper();
    }
    
    /**
     * @return Child_Religion_Mapper 
     */
    function getSampleReligionMapper() {
        return $this->getChildReligionMapper();
    }
    
    /**
     * @return Child_Shop_Category_Mapper 
     */
    function getSampleShopCategoryMapper() {
        return $this->getChildShopCategoryMapper();
    }
    
    /**
     * @return Child_Shop_Classifier_Mapper 
     */
    function getSampleShopClassifierMapper() {
        return $this->getChildShopClassifierMapper();
    }
    
    /**
     * @return Child_Shop_Classifier_Type_Mapper 
     */
    function getSampleShopClassifierTypeMapper() {
        return $this->getChildShopClassifierTypeMapper();
    }
    
    /**
     * @return Child_Shop_Product_Extra_Code_ImplMapper 
     */
    function getSampleShopProductExtraCodeImplMapper() {
        return $this->getChildShopProductExtraCodeImplMapper();
    }
    
    /**
     * @return Child_Shop_Product_Note_ImplMapper 
     */
    function getSampleShopProductNoteImplMapper() {
        return $this->getChildShopProductNoteImplMapper();
    }
    
    /**
     * @return Child_Shop_Product_Mapper 
     */
    function getSampleShopProductMapper() {
        return $this->getChildShopProductMapper();
    }
    
    /**
     * @return Child_Shop_Spec_Computer_ImplMapper 
     */
    function getSampleShopSpecComputerImplMapper() {
        return $this->getChildShopSpecComputerImplMapper();
    }
    
    /**
     * @return Child_Shop_Spec_Food_ImplMapper 
     */
    function getSampleShopSpecFoodImplMapper() {
        return $this->getChildShopSpecFoodImplMapper();
    }
    
    /**
     * @return Child_Shop_Spec_Laptop_ImplMapper 
     */
    function getSampleShopSpecLaptopImplMapper() {
        return $this->getChildShopSpecLaptopImplMapper();
    }
    
    /**
     * @return Child_Shop_Spec_Monitor_ImplMapper 
     */
    function getSampleShopSpecMonitorImplMapper() {
        return $this->getChildShopSpecMonitorImplMapper();
    }
    
    /**
     * @return Child_Shop_Spec_Mapper 
     */
    function getSampleShopSpecMapper() {
        return $this->getChildShopSpecMapper();
    }
    
    /**
     * @return Child_Tag_Mapper 
     */
    function getSampleTagMapper() {
        return $this->getChildTagMapper();
    }
    
    /**
     * @return Child_Tree_Adjacent_Mapper 
     */
    function getSampleTreeAdjacentMapper() {
        return $this->getChildTreeAdjacentMapper();
    }
    
    /**
     * @return Child_Tree_Combo_Mapper 
     */
    function getSampleTreeComboMapper() {
        return $this->getChildTreeComboMapper();
    }
    
    /**
     * @return Child_Tree_Record_Mapper 
     */
    function getSampleTreeRecordMapper() {
        return $this->getChildTreeRecordMapper();
    }
    
 
    /**
     * @return Child_Person 
     */
    static function Child_Person ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Person 
     */
    function createChildPerson (array $defaults = []) {
        return $this->getMapper('Child_Person_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Perk 
     */
    static function Child_Perk ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Perk 
     */
    function createChildPerk (array $defaults = []) {
        return $this->getMapper('Child_Perk_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Person_Album 
     */
    static function Child_Person_Album ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Person_Album 
     */
    function createChildPersonAlbum (array $defaults = []) {
        return $this->getMapper('Child_Person_Album_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Person_Photo 
     */
    static function Child_Person_Photo ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Person_Photo 
     */
    function createChildPersonPhoto (array $defaults = []) {
        return $this->getMapper('Child_Person_Photo_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Person_Post 
     */
    static function Child_Person_Post ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Person_Post 
     */
    function createChildPersonPost (array $defaults = []) {
        return $this->getMapper('Child_Person_Post_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Publish 
     */
    static function Child_Publish ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Publish 
     */
    function createChildPublish (array $defaults = []) {
        return $this->getMapper('Child_Publish_ImplMapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Relation_Type 
     */
    static function Child_Relation_Type ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Relation_Type 
     */
    function createChildRelationType (array $defaults = []) {
        return $this->getMapper('Child_Relation_Type_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Relation 
     */
    static function Child_Relation ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Relation 
     */
    function createChildRelation (array $defaults = []) {
        return $this->getMapper('Child_Relation_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Religion 
     */
    static function Child_Religion ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Religion 
     */
    function createChildReligion (array $defaults = []) {
        return $this->getMapper('Child_Religion_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Shop_Category 
     */
    static function Child_Shop_Category ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Shop_Category 
     */
    function createChildShopCategory (array $defaults = []) {
        return $this->getMapper('Child_Shop_Category_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Shop_Classifier 
     */
    static function Child_Shop_Classifier ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Shop_Classifier 
     */
    function createChildShopClassifier (array $defaults = []) {
        return $this->getMapper('Child_Shop_Classifier_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Shop_Classifier_Type 
     */
    static function Child_Shop_Classifier_Type ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Shop_Classifier_Type 
     */
    function createChildShopClassifierType (array $defaults = []) {
        return $this->getMapper('Child_Shop_Classifier_Type_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Shop_Product_Extra_Code 
     */
    static function Child_Shop_Product_Extra_Code ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Shop_Product_Extra_Code 
     */
    function createChildShopProductExtraCode (array $defaults = []) {
        return $this->getMapper('Child_Shop_Product_Extra_Code_ImplMapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Shop_Product_Note 
     */
    static function Child_Shop_Product_Note ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Shop_Product_Note 
     */
    function createChildShopProductNote (array $defaults = []) {
        return $this->getMapper('Child_Shop_Product_Note_ImplMapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Shop_Product 
     */
    static function Child_Shop_Product ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Shop_Product 
     */
    function createChildShopProduct (array $defaults = []) {
        return $this->getMapper('Child_Shop_Product_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Shop_Spec_Computer 
     */
    static function Child_Shop_Spec_Computer ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Shop_Spec_Computer 
     */
    function createChildShopSpecComputer (array $defaults = []) {
        return $this->getMapper('Child_Shop_Spec_Computer_ImplMapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Shop_Spec_Food 
     */
    static function Child_Shop_Spec_Food ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Shop_Spec_Food 
     */
    function createChildShopSpecFood (array $defaults = []) {
        return $this->getMapper('Child_Shop_Spec_Food_ImplMapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Shop_Spec_Laptop 
     */
    static function Child_Shop_Spec_Laptop ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Shop_Spec_Laptop 
     */
    function createChildShopSpecLaptop (array $defaults = []) {
        return $this->getMapper('Child_Shop_Spec_Laptop_ImplMapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Shop_Spec_Monitor 
     */
    static function Child_Shop_Spec_Monitor ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Shop_Spec_Monitor 
     */
    function createChildShopSpecMonitor (array $defaults = []) {
        return $this->getMapper('Child_Shop_Spec_Monitor_ImplMapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Shop_Spec 
     */
    static function Child_Shop_Spec ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Shop_Spec 
     */
    function createChildShopSpec (array $defaults = []) {
        return $this->getMapper('Child_Shop_Spec_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Tag 
     */
    static function Child_Tag ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Tag 
     */
    function createChildTag (array $defaults = []) {
        return $this->getMapper('Child_Tag_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Tree_Adjacent 
     */
    static function Child_Tree_Adjacent ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Tree_Adjacent 
     */
    function createChildTreeAdjacent (array $defaults = []) {
        return $this->getMapper('Child_Tree_Adjacent_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Tree_Combo 
     */
    static function Child_Tree_Combo ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Tree_Combo 
     */
    function createChildTreeCombo (array $defaults = []) {
        return $this->getMapper('Child_Tree_Combo_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Child_Tree_Record 
     */
    static function Child_Tree_Record ($object = null) {
        return $object;
    }
    
    /**
     * @return Child_Tree_Record 
     */
    function createChildTreeRecord (array $defaults = []) {
        return $this->getMapper('Child_Tree_Record_Mapper')->createRecord(false, $defaults);
    }
    

    protected function doGetComponentPrototypes() {
        $res = parent::doGetComponentPrototypes();
        $res[self::CORE_COMPONENT_COMPONENTS_ACCESSOR] = ['class' => 'Child_ComponentsAccessor'];
        return $res;
    }
    
}
