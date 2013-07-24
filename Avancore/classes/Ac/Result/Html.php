<?php

/**
 * @property Ac_Result_Placeholder $doctype Doctype string
 * @property Ac_Result_Placeholder $htmlAttribs Attributes of &lt;html&gt; tag
 * @property Ac_Result_Placeholder $title Part of HTML page' title
 * @property Ac_Result_Placeholder $headTags Miscellaneous HTML between TITLE and ASSETS parts
 * @property Ac_Result_Placeholder $assets HTML page assets (JS and CSS libraries)
 * @property Ac_Result_Placeholder $headScripts Javascript code to include in the HEAD
 * @property Ac_Result_Placeholder $bodyAttribs Attributes of &lt;body&gt; tag
 * @property Ac_Result_Placeholder $initScripts Javascript code to put before closing &lt;/body&gt; tag
 * @property Ac_Result_Placeholder $comments Text to include in HTML comments below closing &lt;/html&gt; tag
 */
class Ac_Result_Html extends Ac_Result_Http_Abstract {
    
    const DOCTYPE_TRANSITIONAL = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    const DOCTYPE_STRICT = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
    const DOCTYPE_HTML5 = '<!DOCTYPE html>';
    const DOCTYPE_NONE = "";
    
    function doGetDefaultPlaceholders() {
        return Ac_Util::m(
            parent::doGetDefaultPlaceholders(), array(
                'doctype' => array(
                    'class' => 'Ac_Result_Placeholder_Single',
                    'template' => array(
                        'class' => 'Ac_Result_Placeholder_Template',
                        'prefix' => "",
                        'suffix' => "\n"
                    ),
                ),
                'htmlAttribs' => array(
                    'class' => 'Ac_Result_Placeholder',
                    'template' => 'Ac_Result_Placeholder_Template_Attribs',
                ),
                'title' => array(
                    'class' => 'Ac_Result_Placeholder',
                    'template' => array(
                        'class' => 'Ac_Result_Placeholder_Template',
                        'prefix' => "\n    <title>",
                        'glue' => ' - ',
                        'suffix' => "</title>\n",
                    ),
                ),
                'headTags' => array(
                    'class' => 'Ac_Result_Placeholder_Unique',
                    'template' => 'Ac_Result_Placeholder_Template_InHtml',
                ),
                'assets' => array(
                    'class' => 'Ac_Result_Placeholder_Unique',
                    'template' => 'Ac_Result_Placeholder_Template_Assets',
                ),
                'headScripts' => array(
                    'class' => 'Ac_Result_Placeholder',
                    'template' => 'Ac_Result_Placeholder_Template_Scripts',
                ),
                'bodyAttribs' => array(
                    'class' => 'Ac_Result_Placeholder',
                    'template' => 'Ac_Result_Placeholder_Template_Attribs',
                ),
                'initScripts' => array(
                    'class' => 'Ac_Result_Placeholder',
                    'template' => 'Ac_Result_Placeholder_Template_Scripts',
                ),
                'comments' => array(
                    'class' => 'Ac_Result_Placeholder',
                    'template' => array(
                        'class' => 'Ac_Result_Placeholder_Template',
                        'glue' => "\n\n    ",
                        'prefix' => "\n\n<!--\n\n    ",
                        'suffix' => "\n\n-->"
                    ),
                )
            )
        );
    }
    
}