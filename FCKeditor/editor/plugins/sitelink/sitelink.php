<?php
session_start();

    /*
    ============================================================
    Cross-Language SiteLinks for CMSimple with FCKeditor
    ============================================================
    Version:    3.0
    Released:   2009-12-06
    Copyright:  Holger Irmler & Martin Damken
    Website:	http://HolgerIrmler.de/playground
    
    Credits:	This script based on Klaus Treichlers
				"Little Hack", the first implementation
				of CMSimple-Sitelinks in FCKeditor
				and the Sitelik-Plugin by Klaus Treichler
				http://www.treichler.at
    ============================================================
    */
?>
     
<html>
	<head>
		<title>Sitelink</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="robots" content="noindex, nofollow" />
		<script src="../../dialog/common/fck_dialog_common.js" type="text/javascript"></script>
		<script src="sitelink.js" type="text/javascript"></script>
		<style type="text/css">
      body {
        overflow: auto;
      }
		</style>
	</head>
	<body>
	
<?php
	     
    // make the script compatible for users with PHP < 5, taken from php.net manual
     
    if (!function_exists('scandir')) {
        function scandir($dir, $sortorder = 0) {
            if (is_dir($dir) && $dirlist = @opendir($dir)) {
                while (($file = readdir($dirlist)) !== false) {
                    $files[] = $file;
                }
                closedir($dirlist);
                ($sortorder == 0) ? asort($files) :
                 rsort($files);
                return $files;
            }
             else return false;
        }
    }

    // get the variables set in ./cmsimple/fckeditor.php
    // and some stuff from cmsimple configuration
    
    include '../../../../cmsimple/config.php';
    $ml = $cf['menu']['levels'];
    $sep = $cf['uri']['seperator'];
    $lang_default = $cf['language']['default'];
    $lang_active = $_SESSION["lang_active"];
    
     
    //now get the content-folders in all languages
     
    $start_folder = '../../../../';
    $scan_result = scandir($start_folder);
    $dir_list = array();
     
    /*
    * Look for the active language and put it's folder as the first key
    * to the array with the language foldernames.
    * The active language schould be always on top of the link list
    */
     
    $lang_active == $lang_default ? $first_value = "content" : $first_value = $lang_active;
    $dir_list[] = $first_value;
     
    foreach($scan_result as $key => $value) {
         
        /*
        * .. and now we look for other language folders,
        * if so, put them to the array
        */
         
        if (is_dir("$start_folder/$value") == true && $value != '.' && $value != '..' && strlen($value) == 2 && $value != $first_value || ($value == 'content' && $value != $first_value)) {
            $dir_list[] = $value; // Fill the array with all language folders
        }
    }
     
     
    //Here goes the real thing...
     
    foreach($dir_list as $key => $value) {
         
        //get the content file
        $value == "content" ? $c = file_get_contents($start_folder . $value . "/content.htm") :
         $c = file_get_contents($start_folder . $value . "/content/content.htm");
         
        //get the path to the flag image
        $value == "content" ? $flag_file = "../../../../images/flags/" . $lang_default . ".gif" :
         $flag_file = "../../../../images/flags/" . $value . ".gif";
         
        //look for the link prefix
        if ($value == "content") $value = $lang_default;
        if ($value == $lang_active) {
            $lng_pref = "?";
        } elseif ($value != $lang_default && $lang_active == $lang_default) {
            $lng_pref = "./" . $value . "/?";
        } elseif ($value == $lang_default && $lang_active != $lang_default) {
            $lng_pref = "../?";
        } else {
             $lng_pref = "../" . $value . "/?";
        }
         
         
        // write some information where the link goes to,
        // if more than one language was found
        $count = (count($dir_list));
        if ($count > 1) {
            $output .= '<img src="../../../../images/flags/' . $lang_active . '.gif" / >&nbsp;&nbsp;<span style="font-size: 20px;">&rarr;</span>&nbsp;&nbsp;<img src="' . $flag_file . '" / ><br />';
        }
         
         
        /*
        * Most of the following code was written by Klaus Treichler
        * http://www.treichler.at
        *
        * Maybe some day somebody write a smart and compact function for this job...
        * ... Maybe! ;-)
        */
         
        // load some stuff we need from CMSimple configuration
        
        /* 
        include '../../../../cmsimple/config.php';
        $ml = $cf['menu']['levels'];
        $sep = $cf['uri']['seperator'];
        */
        
		/*
        switch ($ml) {
             
            case "1" :
            preg_match_all("@<h1>[^<]*</h1>|<H1>[^<]*</H1>@", $c, $header);
            break;
             
            case "2" :
            preg_match_all("@<h1>(.*)</h1>|<H1>(.*)</H1>|<h2>(.*)</h2>|<H2>(.*)</H2>@", $c, $header);
            break;
             
            case "3" :
            preg_match_all("@<h1>(.*)</h1>|<H1>(.*)</H1>|<h2>(.*)</h2>|<H2>(.*)</H2>|<h3>(.*)</h3>|<H3>(.*)</H3>@", $c, $header);
            break;
             
            case "4" :
            preg_match_all("@<h1>(.*)</h1>|<H1>(.*)</H1>|<h2>(.*)</h2>|<H2>(.*)</H2>|<h3>(.*)</h3>|<H3>(.*)</H3>|<h4>(.*)</h4>|<H4>(.*)</H4>@", $c, $header);
            break;
             
            default:
            preg_match_all("@<h1>(.*)</h1>|<H1>(.*)</H1>|<h2>(.*)</h2>|<H2>(.*)</H2>|<h3>(.*)</h3>|<H3>(.*)</H3>|<h4>(.*)</h4>|<H4>(.*)</H4>@", $c, $header);
            break;
        }
         
        for($i = 0; $i < count($header[0]); $i++) {
            if (strpos($header[0][$i], "<h1>") !== false || strpos($header[0][$i], "<H1>") !== false) {
                $link_length = ((int)strlen($header[0][$i]))-9;
                $link_text = substr($header[0][$i], 4, $link_length);
                $link1 = rawurlencode(str_replace(" ", "_", $link_text));
                $output .= "<a style=\"text-decoration: none;\" href=\"#\" onclick=\"if(Ok('".$lng_pref.$link1."','".$link_text."')) parent.CloseDialog(); return false;\">".$link_text."</a><br />";
            }
             
            if (strpos($header[0][$i], "<h2>") !== false || strpos($header[0][$i], "<H2>") !== false) {
                $link_length = ((int)strlen($header[0][$i]))-9;
                $link_text = substr($header[0][$i], 4, $link_length);
                $link2 = $link1 . $sep . rawurlencode(str_replace(" ", "_", $link_text));
                $output .= "<a style=\"text-decoration: none; margin-left: 20px;\" href=\"#\" onclick=\"if(Ok('".$lng_pref.$link2."','".$link_text."')) parent.CloseDialog(); return false;\">".$link_text."</a><br />";
            }
             
            if (strpos($header[0][$i], "<h3>") !== false || strpos($header[0][$i], "<H3>") !== false) {
                $link_length = ((int)strlen($header[0][$i]))-9;
                $link_text = substr($header[0][$i], 4, $link_length);
                $link3 = $link2 . $sep . rawurlencode(str_replace(" ", "_", $link_text));
                $output .= "<a style=\"text-decoration: none; margin-left: 40px;\" href=\"#\" onclick=\"if(Ok('".$lng_pref.$link3."','".$link_text."')) parent.CloseDialog(); return false;\">".$link_text."</a><br />";
            }
             
            if (strpos($header[0][$i], "<h4>") !== false || strpos($header[0][$i], "<H4>") !== false) {
                $link_length = ((int)strlen($header[0][$i]))-9;
                $link_text = substr($header[0][$i], 4, $link_length);
                $link4 = $link3 . $sep . rawurlencode(str_replace(" ", "_", $link_text));
                $output .= "<a style=\"text-decoration: none; margin-left: 60px;\" href=\"#\" onclick=\"if(Ok('".$lng_pref.$link4."','".$link_text."')) parent.CloseDialog(); return false;\">".$link_text."</a><br />";
            }
        }
		*/
		
		    
    preg_match_all("/<h([1-".$ml."])[^>]*>(.*)<\/h[1-".$ml."]>/i", $c, $headings);
                $sl_ancestors = array();
                $sl_levels = $headings[1];
                $sl_link_texts = $headings[2];
                $i = 0;
                foreach($sl_link_texts as $heading){
                    $sl_margin = (int)$sl_levels[$i] * 20 - 20;
                    $sl_link_text = trim(strip_tags($heading));
                    $sl_url = rawurlencode(str_replace(" ", "_", $sl_link_text));
                    $sl_ancestors[$sl_levels[$i]] = $sl_url;
                    $sl_myself = array_slice($sl_ancestors, 0, $sl_levels[$i]);
                    $sl_link = $lng_pref . implode($sep, $sl_myself);
                    $output .= "<a style=\"text-decoration: none; margin-left:"
                            .$sl_margin."px\" href=\"#\" onclick=\"if(Ok('".$sl_link."','".addslashes($sl_link_text)."')) parent.CloseDialog(); return false;\">"
                            .$sl_link_text."</a><br />";
                    $i++;
                }
      
        $output .= "<br />";
    }

    echo $output;

?>


	</body>
</html>