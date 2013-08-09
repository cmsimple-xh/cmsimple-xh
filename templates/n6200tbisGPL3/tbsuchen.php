<?php
/*
This file is part of a template, which was created by Torsten Behrens.
Take a modern CMSimple XH version. www.cmsimple-xh.org www.cmsimple.name www.cmsimple.me www.cmsimple.eu

Version 08.08.2013. Update for jQuery4CMSimple.
##################################################################################
# Dies ist ein GPL3 Template von Torsten Behrens.                                #
# Torsten Behrens                                                                #
# Dorfstraße 2                                                                   #
# D-24619 Tarbek                                                                 #
# USt.ID-Nr. DE214080613                                                         #
# http://torsten-behrens.de                                                      #
# http://tbis.info                                                               #
# http://tbis.net                                                                #
# http://cmsimple-templates.de                                                   #
# http://cmsimple-templates.com                                                  #
##################################################################################
*/
?>
<?php
function tbsuchen() {
    global $sn, $tx;
    return '<form class="tbisgpl3-search" action="' . $sn . '" method="post">' . "\n" . "\n" . tag('input type="text" class="text" name="search" size="12"') . "\n" . tag('input type="hidden" name="function" value="search"') . "\n" . ' ' . tag('input type="submit" class="submit" value="' . $tx['search']['button'] . '"') . "\n" . "\n" . '</form>' . "\n";
}

?>