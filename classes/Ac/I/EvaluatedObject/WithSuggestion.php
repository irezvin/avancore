<?php

/**
 * EvaluatedObject that is capable of suggesting 'better' evaluator instead of currently used.
 * This is a feature to optimize generic Evaluation algorhythms with better ones for concrete
 * sub-classes of EvaluatedObject implementations. For example, SefUrl may have SefProductUrl
 * subclass that creates SefProductUrl evaluator to acquire necessary product data 
 * in one pass.
 */
interface Ac_I_EvaluatedObject_WithSuggestion extends Ac_I_EvaluatedObject {
    
    function suggestEvaluatorPrototype (Ac_Evaluator $basicEvaluator);
    
}