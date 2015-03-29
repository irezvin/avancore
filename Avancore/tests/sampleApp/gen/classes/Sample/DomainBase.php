<?php 

abstract class Sample_DomainBase extends Ac_Application {

    protected function doOnInitialize() {
        parent::doOnInitialize();
    }


    protected function doGetMapperPrototypes() {
        return array (
            'Sample_Person_Mapper' => array (
                'class' => 'Sample_Person_Mapper',
            ),
            'Sample_Perk_Mapper' => array (
                'class' => 'Sample_Perk_Mapper',
            ),
            'Sample_Person_Album_Mapper' => array (
                'class' => 'Sample_Person_Album_Mapper',
            ),
            'Sample_Person_Photo_Mapper' => array (
                'class' => 'Sample_Person_Photo_Mapper',
            ),
            'Sample_Person_Post_Mapper' => array (
                'class' => 'Sample_Person_Post_Mapper',
            ),
            'Sample_Publish_ImplMapper' => array (
                'class' => 'Sample_Publish_ImplMapper',
            ),
            'Sample_Relation_Type_Mapper' => array (
                'class' => 'Sample_Relation_Type_Mapper',
            ),
            'Sample_Relation_Mapper' => array (
                'class' => 'Sample_Relation_Mapper',
            ),
            'Sample_Religion_Mapper' => array (
                'class' => 'Sample_Religion_Mapper',
            ),
            'Sample_Shop_Category_Mapper' => array (
                'class' => 'Sample_Shop_Category_Mapper',
            ),
            'Sample_Shop_Product_Extra_Code_ImplMapper' => array (
                'class' => 'Sample_Shop_Product_Extra_Code_ImplMapper',
            ),
            'Sample_Shop_Product_Note_ImplMapper' => array (
                'class' => 'Sample_Shop_Product_Note_ImplMapper',
            ),
            'Sample_Shop_Product_Mapper' => array (
                'class' => 'Sample_Shop_Product_Mapper',
            ),
            'Sample_Tag_Mapper' => array (
                'class' => 'Sample_Tag_Mapper',
            ),
            'Sample_Tree_Adjacent_Mapper' => array (
                'class' => 'Sample_Tree_Adjacent_Mapper',
            ),
            'Sample_Tree_Combo_Mapper' => array (
                'class' => 'Sample_Tree_Combo_Mapper',
            ),
            'Sample_Tree_Record_Mapper' => array (
                'class' => 'Sample_Tree_Record_Mapper',
            ),
        );
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
    function createSamplePerson () {
        return $this->getMapper('Sample_Person_Mapper')->createRecord();
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
    function createSamplePerk () {
        return $this->getMapper('Sample_Perk_Mapper')->createRecord();
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
    function createSamplePersonAlbum () {
        return $this->getMapper('Sample_Person_Album_Mapper')->createRecord();
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
    function createSamplePersonPhoto () {
        return $this->getMapper('Sample_Person_Photo_Mapper')->createRecord();
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
    function createSamplePersonPost () {
        return $this->getMapper('Sample_Person_Post_Mapper')->createRecord();
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
    function createSamplePublish () {
        return $this->getMapper('Sample_Publish_ImplMapper')->createRecord();
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
    function createSampleRelationType () {
        return $this->getMapper('Sample_Relation_Type_Mapper')->createRecord();
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
    function createSampleRelation () {
        return $this->getMapper('Sample_Relation_Mapper')->createRecord();
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
    function createSampleReligion () {
        return $this->getMapper('Sample_Religion_Mapper')->createRecord();
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
    function createSampleShopCategory () {
        return $this->getMapper('Sample_Shop_Category_Mapper')->createRecord();
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
    function createSampleShopProductExtraCode () {
        return $this->getMapper('Sample_Shop_Product_Extra_Code_ImplMapper')->createRecord();
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
    function createSampleShopProductNote () {
        return $this->getMapper('Sample_Shop_Product_Note_ImplMapper')->createRecord();
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
    function createSampleShopProduct () {
        return $this->getMapper('Sample_Shop_Product_Mapper')->createRecord();
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
    function createSampleTag () {
        return $this->getMapper('Sample_Tag_Mapper')->createRecord();
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
    function createSampleTreeAdjacent () {
        return $this->getMapper('Sample_Tree_Adjacent_Mapper')->createRecord();
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
    function createSampleTreeCombo () {
        return $this->getMapper('Sample_Tree_Combo_Mapper')->createRecord();
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
    function createSampleTreeRecord () {
        return $this->getMapper('Sample_Tree_Record_Mapper')->createRecord();
    }
    

}
