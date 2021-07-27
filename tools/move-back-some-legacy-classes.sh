#!/bin/bash
pushd ..
if [ ! -d 'classes/Ac/Controller' ]; then mkdir -p classes/Ac/Controller; fi
if [ ! -d 'classes/Ac/Controller/Response' ]; then mkdir -p classes/Ac/Controller/Response; fi
if [ ! -d 'classes/Ac/Controller/Context' ]; then mkdir -p classes/Ac/Controller/Context; fi
if [ ! -d 'classes/Ac/Controller/Output' ]; then mkdir -p classes/Ac/Controller/Output; fi
if [ ! -d 'classes/Ac' ]; then mkdir -p classes/Ac; fi
if [ ! -d 'classes/Ac/Template/Helper' ]; then mkdir -p classes/Ac/Template/Helper; fi
if [ ! -d 'classes/Ac/Template' ]; then mkdir -p classes/Ac/Template; fi
git mv 'obsolete/Ac/Legacy/Controller/Exception.php' 'classes/Ac/Controller/Exception.php'
git mv 'obsolete/Ac/Legacy/Controller/Response.php' 'classes/Ac/Controller/Response.php'
git mv 'obsolete/Ac/Legacy/Controller/Response/Json.php' 'classes/Ac/Controller/Response/Json.php'
git mv 'obsolete/Ac/Legacy/Controller/Response/JsonPart.php' 'classes/Ac/Controller/Response/JsonPart.php'
git mv 'obsolete/Ac/Legacy/Controller/Response/Global.php' 'classes/Ac/Controller/Response/Global.php'
git mv 'obsolete/Ac/Legacy/Controller/Response/Html.php' 'classes/Ac/Controller/Response/Html.php'
git mv 'obsolete/Ac/Legacy/Controller/Response/Http.php' 'classes/Ac/Controller/Response/Http.php'
git mv 'obsolete/Ac/Legacy/Controller/Response/Part.php' 'classes/Ac/Controller/Response/Part.php'
git mv 'obsolete/Ac/Legacy/Controller/Context/Http.php' 'classes/Ac/Controller/Context/Http.php'
git mv 'obsolete/Ac/Legacy/Controller/Context.php' 'classes/Ac/Controller/Context.php'
git mv 'obsolete/Ac/Legacy/Output/Joomla3.php' 'classes/Ac/Controller/Output/Joomla3.php'
git mv 'obsolete/Ac/Legacy/Output/Debug.php' 'classes/Ac/Controller/Output/Debug.php'
git mv 'obsolete/Ac/Legacy/Output/Joomla.php' 'classes/Ac/Controller/Output/Joomla.php'
git mv 'obsolete/Ac/Legacy/Output/Native.php' 'classes/Ac/Controller/Output/Native.php'
git mv 'obsolete/Ac/Legacy/Output/Joomla15.php' 'classes/Ac/Controller/Output/Joomla15.php'
git mv 'obsolete/Ac/Legacy/Controller.php' 'classes/Ac/Controller.php'
git mv 'obsolete/Ac/Legacy/Output.php' 'classes/Ac/Output.php'
git mv 'obsolete/Ac/Legacy/Template/Helper/Html.php' 'classes/Ac/Template/Helper/Html.php'
git mv 'obsolete/Ac/Legacy/Template/HtmlPage.php' 'classes/Ac/Template/HtmlPage.php'
git mv 'obsolete/Ac/Legacy/Template/Html.php' 'classes/Ac/Template/Html.php'
git mv 'obsolete/Ac/Legacy/Template/JoomlaPage.php' 'classes/Ac/Template/JoomlaPage.php'
git mv 'obsolete/Ac/Legacy/Template/Helper.php' 'classes/Ac/Template/Helper.php'
git mv 'obsolete/Ac/Legacy/Template.php' 'classes/Ac/Template.php'
popd
find ../classes ../tests ../obsolete -type f -name '*.php' -print0 | xargs -0 sed -rf 'move-back-some-legacy-classes.sed' -i

