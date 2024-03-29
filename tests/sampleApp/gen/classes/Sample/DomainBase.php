<?php 

/**
 * @property Sample_ComponentsAccessor $c Convenient access to application components
 */
abstract class Sample_DomainBase extends Ac_Application {

    protected $componentAliases = [
        'people' => 'Sample_Person_Mapper',
        'perks' => 'Sample_Perk_Mapper',
        'personAlbums' => 'Sample_Person_Album_Mapper',
        'personPhotos' => 'Sample_Person_Photo_Mapper',
        'personPosts' => 'Sample_Person_Post_Mapper',
        'publish' => 'Sample_Publish_ImplMapper',
        'relationTypes' => 'Sample_Relation_Type_Mapper',
        'relations' => 'Sample_Relation_Mapper',
        'religion' => 'Sample_Religion_Mapper',
        'shopCategories' => 'Sample_Shop_Category_Mapper',
        'shopClassifier' => 'Sample_Shop_Classifier_Mapper',
        'shopClassifierType' => 'Sample_Shop_Classifier_Type_Mapper',
        'shopProductExtraCodes' => 'Sample_Shop_Product_Extra_Code_ImplMapper',
        'shopProductNotes' => 'Sample_Shop_Product_Note_ImplMapper',
        'shopProducts' => 'Sample_Shop_Product_Mapper',
        'shopSpecComputer' => 'Sample_Shop_Spec_Computer_ImplMapper',
        'shopSpecFood' => 'Sample_Shop_Spec_Food_ImplMapper',
        'shopSpecLaptop' => 'Sample_Shop_Spec_Laptop_ImplMapper',
        'shopSpecMonitor' => 'Sample_Shop_Spec_Monitor_ImplMapper',
        'shopSpecs' => 'Sample_Shop_Spec_Mapper',
        'tags' => 'Sample_Tag_Mapper',
        'treeAdjacent' => 'Sample_Tree_Adjacent_Mapper',
        'treeCombos' => 'Sample_Tree_Combo_Mapper',
        'treeRecords' => 'Sample_Tree_Record_Mapper',
    ];

    protected function doOnInitialize() {
        parent::doOnInitialize();
    }


    protected function doGetMapperPrototypes() {
        return [
            'Sample_Person_Mapper' => [
                'class' => 'Sample_Person_Mapper',
            ],
            'Sample_Perk_Mapper' => [
                'class' => 'Sample_Perk_Mapper',
            ],
            'Sample_Person_Album_Mapper' => [
                'class' => 'Sample_Person_Album_Mapper',
            ],
            'Sample_Person_Photo_Mapper' => [
                'class' => 'Sample_Person_Photo_Mapper',
            ],
            'Sample_Person_Post_Mapper' => [
                'class' => 'Sample_Person_Post_Mapper',
            ],
            'Sample_Publish_ImplMapper' => [
                'class' => 'Sample_Publish_ImplMapper',
            ],
            'Sample_Relation_Type_Mapper' => [
                'class' => 'Sample_Relation_Type_Mapper',
            ],
            'Sample_Relation_Mapper' => [
                'class' => 'Sample_Relation_Mapper',
            ],
            'Sample_Religion_Mapper' => [
                'class' => 'Sample_Religion_Mapper',
            ],
            'Sample_Shop_Category_Mapper' => [
                'class' => 'Sample_Shop_Category_Mapper',
            ],
            'Sample_Shop_Classifier_Mapper' => [
                'class' => 'Sample_Shop_Classifier_Mapper',
            ],
            'Sample_Shop_Classifier_Type_Mapper' => [
                'class' => 'Sample_Shop_Classifier_Type_Mapper',
            ],
            'Sample_Shop_Product_Extra_Code_ImplMapper' => [
                'class' => 'Sample_Shop_Product_Extra_Code_ImplMapper',
            ],
            'Sample_Shop_Product_Note_ImplMapper' => [
                'class' => 'Sample_Shop_Product_Note_ImplMapper',
            ],
            'Sample_Shop_Product_Mapper' => [
                'class' => 'Sample_Shop_Product_Mapper',
            ],
            'Sample_Shop_Spec_Computer_ImplMapper' => [
                'class' => 'Sample_Shop_Spec_Computer_ImplMapper',
            ],
            'Sample_Shop_Spec_Food_ImplMapper' => [
                'class' => 'Sample_Shop_Spec_Food_ImplMapper',
            ],
            'Sample_Shop_Spec_Laptop_ImplMapper' => [
                'class' => 'Sample_Shop_Spec_Laptop_ImplMapper',
            ],
            'Sample_Shop_Spec_Monitor_ImplMapper' => [
                'class' => 'Sample_Shop_Spec_Monitor_ImplMapper',
            ],
            'Sample_Shop_Spec_Mapper' => [
                'class' => 'Sample_Shop_Spec_Mapper',
            ],
            'Sample_Tag_Mapper' => [
                'class' => 'Sample_Tag_Mapper',
            ],
            'Sample_Tree_Adjacent_Mapper' => [
                'class' => 'Sample_Tree_Adjacent_Mapper',
            ],
            'Sample_Tree_Combo_Mapper' => [
                'class' => 'Sample_Tree_Combo_Mapper',
            ],
            'Sample_Tree_Record_Mapper' => [
                'class' => 'Sample_Tree_Record_Mapper',
            ],
        ];
    }
    
    /**
     * @return Sample_Person_Mapper 
     */
    function getSamplePersonMapper() {
        return $this->getMapper('Sample_Person_Mapper');
    }
    
    /**
     * @return Sample_Perk_Mapper 
     */
    function getSamplePerkMapper() {
        return $this->getMapper('Sample_Perk_Mapper');
    }
    
    /**
     * @return Sample_Person_Album_Mapper 
     */
    function getSamplePersonAlbumMapper() {
        return $this->getMapper('Sample_Person_Album_Mapper');
    }
    
    /**
     * @return Sample_Person_Photo_Mapper 
     */
    function getSamplePersonPhotoMapper() {
        return $this->getMapper('Sample_Person_Photo_Mapper');
    }
    
    /**
     * @return Sample_Person_Post_Mapper 
     */
    function getSamplePersonPostMapper() {
        return $this->getMapper('Sample_Person_Post_Mapper');
    }
    
    /**
     * @return Sample_Publish_ImplMapper 
     */
    function getSamplePublishImplMapper() {
        return $this->getMapper('Sample_Publish_ImplMapper');
    }
    
    /**
     * @return Sample_Relation_Type_Mapper 
     */
    function getSampleRelationTypeMapper() {
        return $this->getMapper('Sample_Relation_Type_Mapper');
    }
    
    /**
     * @return Sample_Relation_Mapper 
     */
    function getSampleRelationMapper() {
        return $this->getMapper('Sample_Relation_Mapper');
    }
    
    /**
     * @return Sample_Religion_Mapper 
     */
    function getSampleReligionMapper() {
        return $this->getMapper('Sample_Religion_Mapper');
    }
    
    /**
     * @return Sample_Shop_Category_Mapper 
     */
    function getSampleShopCategoryMapper() {
        return $this->getMapper('Sample_Shop_Category_Mapper');
    }
    
    /**
     * @return Sample_Shop_Classifier_Mapper 
     */
    function getSampleShopClassifierMapper() {
        return $this->getMapper('Sample_Shop_Classifier_Mapper');
    }
    
    /**
     * @return Sample_Shop_Classifier_Type_Mapper 
     */
    function getSampleShopClassifierTypeMapper() {
        return $this->getMapper('Sample_Shop_Classifier_Type_Mapper');
    }
    
    /**
     * @return Sample_Shop_Product_Extra_Code_ImplMapper 
     */
    function getSampleShopProductExtraCodeImplMapper() {
        return $this->getMapper('Sample_Shop_Product_Extra_Code_ImplMapper');
    }
    
    /**
     * @return Sample_Shop_Product_Note_ImplMapper 
     */
    function getSampleShopProductNoteImplMapper() {
        return $this->getMapper('Sample_Shop_Product_Note_ImplMapper');
    }
    
    /**
     * @return Sample_Shop_Product_Mapper 
     */
    function getSampleShopProductMapper() {
        return $this->getMapper('Sample_Shop_Product_Mapper');
    }
    
    /**
     * @return Sample_Shop_Spec_Computer_ImplMapper 
     */
    function getSampleShopSpecComputerImplMapper() {
        return $this->getMapper('Sample_Shop_Spec_Computer_ImplMapper');
    }
    
    /**
     * @return Sample_Shop_Spec_Food_ImplMapper 
     */
    function getSampleShopSpecFoodImplMapper() {
        return $this->getMapper('Sample_Shop_Spec_Food_ImplMapper');
    }
    
    /**
     * @return Sample_Shop_Spec_Laptop_ImplMapper 
     */
    function getSampleShopSpecLaptopImplMapper() {
        return $this->getMapper('Sample_Shop_Spec_Laptop_ImplMapper');
    }
    
    /**
     * @return Sample_Shop_Spec_Monitor_ImplMapper 
     */
    function getSampleShopSpecMonitorImplMapper() {
        return $this->getMapper('Sample_Shop_Spec_Monitor_ImplMapper');
    }
    
    /**
     * @return Sample_Shop_Spec_Mapper 
     */
    function getSampleShopSpecMapper() {
        return $this->getMapper('Sample_Shop_Spec_Mapper');
    }
    
    /**
     * @return Sample_Tag_Mapper 
     */
    function getSampleTagMapper() {
        return $this->getMapper('Sample_Tag_Mapper');
    }
    
    /**
     * @return Sample_Tree_Adjacent_Mapper 
     */
    function getSampleTreeAdjacentMapper() {
        return $this->getMapper('Sample_Tree_Adjacent_Mapper');
    }
    
    /**
     * @return Sample_Tree_Combo_Mapper 
     */
    function getSampleTreeComboMapper() {
        return $this->getMapper('Sample_Tree_Combo_Mapper');
    }
    
    /**
     * @return Sample_Tree_Record_Mapper 
     */
    function getSampleTreeRecordMapper() {
        return $this->getMapper('Sample_Tree_Record_Mapper');
    }
    
 
    /**
     * @return Sample_Person 
     */
    static function Sample_Person ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Person 
     */
    function createSamplePerson (array $defaults = []) {
        return $this->getMapper('Sample_Person_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Perk 
     */
    static function Sample_Perk ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Perk 
     */
    function createSamplePerk (array $defaults = []) {
        return $this->getMapper('Sample_Perk_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Person_Album 
     */
    static function Sample_Person_Album ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Person_Album 
     */
    function createSamplePersonAlbum (array $defaults = []) {
        return $this->getMapper('Sample_Person_Album_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Person_Photo 
     */
    static function Sample_Person_Photo ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Person_Photo 
     */
    function createSamplePersonPhoto (array $defaults = []) {
        return $this->getMapper('Sample_Person_Photo_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Person_Post 
     */
    static function Sample_Person_Post ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Person_Post 
     */
    function createSamplePersonPost (array $defaults = []) {
        return $this->getMapper('Sample_Person_Post_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Publish 
     */
    static function Sample_Publish ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Publish 
     */
    function createSamplePublish (array $defaults = []) {
        return $this->getMapper('Sample_Publish_ImplMapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Relation_Type 
     */
    static function Sample_Relation_Type ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Relation_Type 
     */
    function createSampleRelationType (array $defaults = []) {
        return $this->getMapper('Sample_Relation_Type_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Relation 
     */
    static function Sample_Relation ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Relation 
     */
    function createSampleRelation (array $defaults = []) {
        return $this->getMapper('Sample_Relation_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Religion 
     */
    static function Sample_Religion ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Religion 
     */
    function createSampleReligion (array $defaults = []) {
        return $this->getMapper('Sample_Religion_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Shop_Category 
     */
    static function Sample_Shop_Category ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Shop_Category 
     */
    function createSampleShopCategory (array $defaults = []) {
        return $this->getMapper('Sample_Shop_Category_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Shop_Classifier 
     */
    static function Sample_Shop_Classifier ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Shop_Classifier 
     */
    function createSampleShopClassifier (array $defaults = []) {
        return $this->getMapper('Sample_Shop_Classifier_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Shop_Classifier_Type 
     */
    static function Sample_Shop_Classifier_Type ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Shop_Classifier_Type 
     */
    function createSampleShopClassifierType (array $defaults = []) {
        return $this->getMapper('Sample_Shop_Classifier_Type_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Shop_Product_Extra_Code 
     */
    static function Sample_Shop_Product_Extra_Code ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Shop_Product_Extra_Code 
     */
    function createSampleShopProductExtraCode (array $defaults = []) {
        return $this->getMapper('Sample_Shop_Product_Extra_Code_ImplMapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Shop_Product_Note 
     */
    static function Sample_Shop_Product_Note ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Shop_Product_Note 
     */
    function createSampleShopProductNote (array $defaults = []) {
        return $this->getMapper('Sample_Shop_Product_Note_ImplMapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Shop_Product 
     */
    static function Sample_Shop_Product ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Shop_Product 
     */
    function createSampleShopProduct (array $defaults = []) {
        return $this->getMapper('Sample_Shop_Product_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Shop_Spec_Computer 
     */
    static function Sample_Shop_Spec_Computer ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Shop_Spec_Computer 
     */
    function createSampleShopSpecComputer (array $defaults = []) {
        return $this->getMapper('Sample_Shop_Spec_Computer_ImplMapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Shop_Spec_Food 
     */
    static function Sample_Shop_Spec_Food ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Shop_Spec_Food 
     */
    function createSampleShopSpecFood (array $defaults = []) {
        return $this->getMapper('Sample_Shop_Spec_Food_ImplMapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Shop_Spec_Laptop 
     */
    static function Sample_Shop_Spec_Laptop ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Shop_Spec_Laptop 
     */
    function createSampleShopSpecLaptop (array $defaults = []) {
        return $this->getMapper('Sample_Shop_Spec_Laptop_ImplMapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Shop_Spec_Monitor 
     */
    static function Sample_Shop_Spec_Monitor ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Shop_Spec_Monitor 
     */
    function createSampleShopSpecMonitor (array $defaults = []) {
        return $this->getMapper('Sample_Shop_Spec_Monitor_ImplMapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Shop_Spec 
     */
    static function Sample_Shop_Spec ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Shop_Spec 
     */
    function createSampleShopSpec (array $defaults = []) {
        return $this->getMapper('Sample_Shop_Spec_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Tag 
     */
    static function Sample_Tag ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Tag 
     */
    function createSampleTag (array $defaults = []) {
        return $this->getMapper('Sample_Tag_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Tree_Adjacent 
     */
    static function Sample_Tree_Adjacent ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Tree_Adjacent 
     */
    function createSampleTreeAdjacent (array $defaults = []) {
        return $this->getMapper('Sample_Tree_Adjacent_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Tree_Combo 
     */
    static function Sample_Tree_Combo ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Tree_Combo 
     */
    function createSampleTreeCombo (array $defaults = []) {
        return $this->getMapper('Sample_Tree_Combo_Mapper')->createRecord(false, $defaults);
    }
    
 
    /**
     * @return Sample_Tree_Record 
     */
    static function Sample_Tree_Record ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Tree_Record 
     */
    function createSampleTreeRecord (array $defaults = []) {
        return $this->getMapper('Sample_Tree_Record_Mapper')->createRecord(false, $defaults);
    }
    

    protected function doGetComponentPrototypes() {
        $res = parent::doGetComponentPrototypes();
        $res[self::CORE_COMPONENT_COMPONENTS_ACCESSOR] = ['class' => 'Sample_ComponentsAccessor'];
        return $res;
    }
    
}
