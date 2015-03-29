<?php 

abstract class Child_DomainBase extends Sample {

    protected function doOnInitialize() {
        parent::doOnInitialize();
 
        $this->setMapperAliases(array ( 'Sample_Person_Mapper' => 'Child_Person_Mapper', 'Sample_Perk_Mapper' => 'Child_Perk_Mapper', 'Sample_Person_Album_Mapper' => 'Child_Person_Album_Mapper', 'Sample_Person_Photo_Mapper' => 'Child_Person_Photo_Mapper', 'Sample_Person_Post_Mapper' => 'Child_Person_Post_Mapper', 'Sample_Publish_ImplMapper' => 'Child_Publish_ImplMapper', 'Sample_Relation_Type_Mapper' => 'Child_Relation_Type_Mapper', 'Sample_Relation_Mapper' => 'Child_Relation_Mapper', 'Sample_Religion_Mapper' => 'Child_Religion_Mapper', 'Sample_Shop_Category_Mapper' => 'Child_Shop_Category_Mapper', 'Sample_Shop_Product_Extra_Code_ImplMapper' => 'Child_Shop_Product_Extra_Code_ImplMapper', 'Sample_Shop_Product_Note_ImplMapper' => 'Child_Shop_Product_Note_ImplMapper', 'Sample_Shop_Product_Mapper' => 'Child_Shop_Product_Mapper', 'Sample_Tag_Mapper' => 'Child_Tag_Mapper', 'Sample_Tree_Adjacent_Mapper' => 'Child_Tree_Adjacent_Mapper', 'Sample_Tree_Combo_Mapper' => 'Child_Tree_Combo_Mapper', 'Sample_Tree_Record_Mapper' => 'Child_Tree_Record_Mapper', ), true);
    }


    protected function doGetMapperPrototypes() {
        return array (
            'Child_Person_Mapper' => array (
                'class' => 'Child_Person_Mapper',
            ),
            'Child_Perk_Mapper' => array (
                'class' => 'Child_Perk_Mapper',
            ),
            'Child_Person_Album_Mapper' => array (
                'class' => 'Child_Person_Album_Mapper',
            ),
            'Child_Person_Photo_Mapper' => array (
                'class' => 'Child_Person_Photo_Mapper',
            ),
            'Child_Person_Post_Mapper' => array (
                'class' => 'Child_Person_Post_Mapper',
            ),
            'Child_Publish_ImplMapper' => array (
                'class' => 'Child_Publish_ImplMapper',
            ),
            'Child_Relation_Type_Mapper' => array (
                'class' => 'Child_Relation_Type_Mapper',
            ),
            'Child_Relation_Mapper' => array (
                'class' => 'Child_Relation_Mapper',
            ),
            'Child_Religion_Mapper' => array (
                'class' => 'Child_Religion_Mapper',
            ),
            'Child_Shop_Category_Mapper' => array (
                'class' => 'Child_Shop_Category_Mapper',
            ),
            'Child_Shop_Product_Extra_Code_ImplMapper' => array (
                'class' => 'Child_Shop_Product_Extra_Code_ImplMapper',
            ),
            'Child_Shop_Product_Note_ImplMapper' => array (
                'class' => 'Child_Shop_Product_Note_ImplMapper',
            ),
            'Child_Shop_Product_Mapper' => array (
                'class' => 'Child_Shop_Product_Mapper',
            ),
            'Child_Tag_Mapper' => array (
                'class' => 'Child_Tag_Mapper',
            ),
            'Child_Tree_Adjacent_Mapper' => array (
                'class' => 'Child_Tree_Adjacent_Mapper',
            ),
            'Child_Tree_Combo_Mapper' => array (
                'class' => 'Child_Tree_Combo_Mapper',
            ),
            'Child_Tree_Record_Mapper' => array (
                'class' => 'Child_Tree_Record_Mapper',
            ),
        );
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
    function createChildPerson () {
        return $this->getMapper('Child_Person_Mapper')->createRecord();
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
    function createChildPerk () {
        return $this->getMapper('Child_Perk_Mapper')->createRecord();
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
    function createChildPersonAlbum () {
        return $this->getMapper('Child_Person_Album_Mapper')->createRecord();
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
    function createChildPersonPhoto () {
        return $this->getMapper('Child_Person_Photo_Mapper')->createRecord();
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
    function createChildPersonPost () {
        return $this->getMapper('Child_Person_Post_Mapper')->createRecord();
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
    function createChildPublish () {
        return $this->getMapper('Child_Publish_ImplMapper')->createRecord();
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
    function createChildRelationType () {
        return $this->getMapper('Child_Relation_Type_Mapper')->createRecord();
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
    function createChildRelation () {
        return $this->getMapper('Child_Relation_Mapper')->createRecord();
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
    function createChildReligion () {
        return $this->getMapper('Child_Religion_Mapper')->createRecord();
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
    function createChildShopCategory () {
        return $this->getMapper('Child_Shop_Category_Mapper')->createRecord();
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
    function createChildShopProductExtraCode () {
        return $this->getMapper('Child_Shop_Product_Extra_Code_ImplMapper')->createRecord();
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
    function createChildShopProductNote () {
        return $this->getMapper('Child_Shop_Product_Note_ImplMapper')->createRecord();
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
    function createChildShopProduct () {
        return $this->getMapper('Child_Shop_Product_Mapper')->createRecord();
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
    function createChildTag () {
        return $this->getMapper('Child_Tag_Mapper')->createRecord();
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
    function createChildTreeAdjacent () {
        return $this->getMapper('Child_Tree_Adjacent_Mapper')->createRecord();
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
    function createChildTreeCombo () {
        return $this->getMapper('Child_Tree_Combo_Mapper')->createRecord();
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
    function createChildTreeRecord () {
        return $this->getMapper('Child_Tree_Record_Mapper')->createRecord();
    }
    

}
