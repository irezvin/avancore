<?php

class Ac_Test_Tr extends Ac_Test_Base {
    
    /**
     * @return Ac_Form
     */
    function createTestForm1(array $prototypeExtra = array()) {
        $f1 = new Ac_Form(
            null,
            Ac_Util::m(array(
                'controls' => array(
                    'tabs' => array(
                        'class' => 'Ac_Form_Control_Tabs',
                        'caption' => '',
                        'model' => null,
                        'controls' => array(
                            'general' => array(
                            ),
                            'intimate' => array(
                            ),
                        ),
                    ),
                    'name' => array(
                        'class' => 'Ac_Form_Control_Text',
                        'displayParent' => '/tabs/general',
                    ),
                    'gender' => array(
                        'class' => 'Ac_Form_Control_List',
                        'displayParent' => '../tabs/intimate',
                    ),
                    'birthDate' => array(
                        'class' => 'Ac_Form_Control_Date',
                        'displayParent' => '/tabs/general',
                    ),
                    'religionId' => array(
                        'class' => 'Ac_Form_Control_List',
                        'displayParent' => '../tabs/intimate',
                        'displayOrder' => 2,
                    ),
                    'isSingle' => array(
                        'class' => 'Ac_Form_Control_Toggle',
                        'displayParent' => '../tabs/intimate',
                        'displayOrder' => 1,
                    ),
                    'submit' => array(
                        'class' => 'Ac_Form_Control_Button',
                    ),
                )
            ), $prototypeExtra)
        );
        return $f1;
    }

    function dumpControl(Ac_Form_Control $control) {
        return get_class($control).'#'.$control->name;
    }
    
    function _testFormTr() {
        $tf1 = $this->createTestForm1();
        $trs = new Tr_Scanner(new Tr_Forms_Scanner);
        $root = $trs->scan($tf1);
        $iter = new RecursiveIteratorIterator($root->createSuperNode(), RecursiveIteratorIterator::SELF_FIRST);
        
        $trControls = array();
        foreach ($iter as $foo) $trControls[] = $this->dumpControl($foo->getObject());
        
        $children = array_merge(array($tf1), $tf1->findControlsRecursive());
        
        $foundControls = array();
        foreach ($children as $control) {
            $foundControls[] = $this->dumpControl($control);
        }
        
        if (!$this->assertTrue(
            count($trControls) == count($foundControls) && !array_diff($trControls, $foundControls)
            , "Ensure all controls are found by Tr_Forms_Scanner"
        )) {
            var_dump("Tr:", $trControls, "Found:", $foundControls);
        }
        
        
        $table = new Tr_Class_Table();
        $table->addEntry($rootClass = new Tr_Class_Entry(array(
            'key' => Tr_Class_Entry::ENTRY_ROOT,
        )));
        $table->addEntry($compositeClass = new Tr_Class_Entry(array(
            'key' => 'Ac_Form_Control_Composite',
        )));
        $table->addEntry($controlClass = new Tr_Class_Entry(array(
            'key' => 'Ac_Form_Control',
            'resultProbePrototype' => array(
                '__construct' => array('acceptedSourceClasses' => array('Tr_Forms_Result')),
                'probes' => array(
                    'hasContainer' => array('class' => 'Tr_Forms_ResultProbe_HasContainer'),
                ),
            ),
            'objectProbePrototype' => array(
                '__construct' => array('acceptedSourceClasses' => array('Ac_Form_Control')),
                'probes' => array(
                    'hasContainer' => array('class' => 'Tr_Forms_ObjectProbe_HasContainer'),
                ),
            ),
            'resultProviderPrototype' => array(
                'class' => 'Tr_Forms_ResultProvider',
            ),
        )));
        $plan = new Tr_Plan($root, $table);
        $this->assertEqual($table->findEntry('Ac_Form'), $compositeClass);
        $this->assertEqual($table->findEntry('Ac_Form_Control_Text'), $controlClass);
        
        $items = $root->findNodesByObjectProps(array('name' => 'name'));
        $this->assertTrue(count($items) == 1);
        
        $plan->beginStage(0);
        $plan->execute();

        /*foreach ($root->findNodesByObjectProps(array()) as $node) {
            $dom = $node->getResult()->getDomNode();
            if ($dom) var_dump(Ac_Util::typeClass($node)."\n".$dom->ownerDocument->saveHTML($dom));
                else var_dump($node.': not found');
        }*/
        /*var_dump($dom->ownerDocument->saveHTML($dom->parentNode));
        var_dump($dom->ownerDocument->saveHTML($dom->parentNode->parentNode));*/
    }
    
}