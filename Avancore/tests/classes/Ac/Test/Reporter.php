<?php

class Ac_Test_Reporter extends HtmlReporter {
    
    function paintHeader($test_name) {
        $this->sendNoCacheHeaders();
        print "<h1>$test_name</h1>\n";
        flush();
    }

    /**
     *    Paints a PHP error.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintError($message) {
        parent::paintError($message);
        print "<span class=\"fail\">Exception</span>: ";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print implode(" -&gt; ", $breadcrumb);
        print " -&gt; <strong>" . $this->htmlEntities($message) . "</strong><br />\n";
        $e = new Exception;
        echo ''.nl2br($e->getTraceAsString());
    }

    /**
     *    Paints a PHP exception.
     *    @param Exception $exception        Exception to display.
     *    @access public
     */
    function paintException($exception) {
        parent::paintException($exception);
        print "<span class=\"fail\">Exception</span>: ";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print implode(" -&gt; ", $breadcrumb);
        echo ''.nl2br($exception->getTraceAsString());
    }
    
    
}