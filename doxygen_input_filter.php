<?php

$contents = file_get_contents($argv[1]);

// Support class properties 
// <https://stackoverflow.com/questions/4325224/doxygen-how-to-describe-class-member-variables-in-php/8472180#8472180>
$regexp = '#\@var\s+([^\s]+)([^/]+)/\s+(var|public|protected|private)\s+(\$[^\s;=]+)#';
$replac = '${2} */ ${3} ${1} ${4}';
$contents = preg_replace($regexp, $replac, $contents);

// Work around cms.php issues
if (basename($argv[1]) === "cms.php") {
    $contents = preg_replace('/^(?:if|foreach|switch)(?:[^}]+){/m', "{", $contents);
}

echo $contents;
