<?php

class Ac_Test_Decorators extends Ac_Test_Base {
    
    function testModelDepth() {
        
        $model1 = new stdClass();
        $model1->title = 'Model 1';
        $model2 = new stdClass();
        $model2->title = 'Model 2';
        
        Ac_Decorator::pushModel($model1);
        Ac_Decorator::pushModel($model2);
        $deco = new Ac_Decorator_Model_Template(['placeholders' => ['title' => 'title'], 'template' => '{{_value_}}: {{title}}']);
        $deco2 = new Ac_Decorator_Model_Template(['modelDepth' => 1, 'placeholders' => ['title' => 'title'], 'template' => '{{_value_}}: {{title}}']);
        $deco2 = new Ac_Decorator_Model_Template(['modelDepth' => 99, 'placeholders' => ['title' => 'title'], 'template' => '{{_value_}}: {{title}}']);
        $this->assertEqual($deco('Level 0 decorator'), 'Level 0 decorator: Model 2', 'Zero depth: top model');
        $this->assertEqual($deco2('Level 1 decorator'), 'Level 1 decorator: Model 1', 'Depth 1: prev model');
        $this->assertEqual($deco2('Level 99 decorator'), 'Level 99 decorator: Model 1', 'Depth exceeds stack size: bottom model');
        Ac_Decorator::popModel();
        Ac_Decorator::popModel();
        
        
    }
    
}
