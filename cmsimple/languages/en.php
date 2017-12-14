<?php

$tx['site']['title']="English Site Title";
$tx['subsite']['template']="";

$tx['meta']['keywords']="Enter list of comma separated keywords here";
$tx['meta']['description']="Enter website description for search engine results here";

$tx['locale']['all']="";

$tx['template']['text1']="Text 1 for templates requiring this text";
$tx['template']['text2']="Text 2 for templates requiring this text";
$tx['template']['text3']="Text 3 for templates requiring this text";

$tx['urichar']['new']="";
$tx['urichar']['org']="";

$tx['action']['advanced_hide']="Less &hellip;";
$tx['action']['advanced_show']="More &hellip;";
$tx['action']['backup']="backup";
$tx['action']['cancel']="Cancel";
$tx['action']['delete']="delete";
$tx['action']['download']="download";
$tx['action']['edit']="edit";
$tx['action']['empty']="empty";
$tx['action']['ok']="OK";
$tx['action']['restore']="restore";
$tx['action']['save']="save";
$tx['action']['upload']="upload";
$tx['action']['view']="view";

$tx['editmenu']['backups']="Backups";
$tx['editmenu']['change_password']="Password";
$tx['editmenu']['configuration']="Configuration";
$tx['editmenu']['downloads']="Downloads";
$tx['editmenu']['edit']="Edit mode";
$tx['editmenu']['files']="Files";
$tx['editmenu']['help']="Help";
$tx['editmenu']['images']="Images";
$tx['editmenu']['language']="Language";
$tx['editmenu']['log']="Log file";
$tx['editmenu']['logout']="Logout";
$tx['editmenu']['media']="Media";
$tx['editmenu']['normal']="View mode";
$tx['editmenu']['pagedata']="Page Data";
$tx['editmenu']['pagemanager']="Pages";
$tx['editmenu']['plugins']="Plugins";
$tx['editmenu']['settings']="Settings";
$tx['editmenu']['stylesheet']="Stylesheet";
$tx['editmenu']['sysinfo']="Info";
$tx['editmenu']['template']="Template";
$tx['editmenu']['userfiles']="Userfiles";
$tx['editmenu']['validate']="Validate links";

$tx['error']['401']="Error 401: Unauthorized";
$tx['error']['403']="Error 403: Forbidden";
$tx['error']['404']="Error 404: Not found";
$tx['error']['alreadyexists']="Already exists";
$tx['error']['badrequest']="Bad request. Please <a href=\".\">try again</a>.";
$tx['error']['cntdelete']="Could not delete";
$tx['error']['cntlocateheading']="No page selected";
$tx['error']['cntopen']="Could not open";
$tx['error']['cntsave']="Could not save";
$tx['error']['cntwriteto']="Could not write to";
$tx['error']['fatal']="A fatal error occurred. Enable <a href=\"http://www.cmsimple-xh.org/wiki/doku.php/troubleshooting#debug_mode\" target=\"_blank\">debug mode</a> for further information.";
$tx['error']['noeditor']="External editor \"%s\" missing!";
$tx['error']['nofilebrowser']="External filebrowser \"%s\" missing!";
$tx['error']['nopagemanager']="External pagemanager \"%s\" missing!";
$tx['error']['headers']="Cannot modify header information - headers already sent (output started at {location})";
$tx['error']['missing']="Missing";
$tx['error']['nocookies']="Please enable Cookies!";
$tx['error']['nojs']="Please enable Javascript!";
$tx['error']['notreadable']="Not readable";
$tx['error']['notwritable']="Not writeable";
$tx['error']['plugincall']="Function %s() is not defined!";
$tx['error']['server']="Server error: %s";
$tx['error']['undefined']="Undefined";

$tx['filetype']['backup']="backup";
$tx['filetype']['config']="configuration";
$tx['filetype']['content']="content file";
$tx['filetype']['execute']="execute";
$tx['filetype']['file']="file";
$tx['filetype']['folder']="folder";
$tx['filetype']['language']="language file";
$tx['filetype']['log']="log";
$tx['filetype']['stylesheet']="stylesheet";
$tx['filetype']['template']="template";

$tx['help']['downloads_maxsize']="Maximum size of uploaded files in Byte. This must neither exceed the limit set for upload_max_filesize nor post_max_size in the PHP configuration.";
$tx['help']['editmenu_scroll']="Whether the admin menu shall scroll with your webpage. Not checked = fixed admin menu.";
$tx['help']['editmenu_external']="If you want to use an external admin menu, install it as a plugin and enter its function name here.";
$tx['help']['editor_height']="Integer or JavaScript expression returning an integer for editor height in pixels.";
$tx['help']['editor_external']="Enter here the name of the wanted editor, which has to be installed as a plugin. There is no internal editor.";
$tx['help']['filebrowser_external']="If you want to use an external file browser, e.g. hi_kcfinder, install the plugin and enter its name here.";
$tx['help']['functions_file']="Please do not change";
$tx['help']['meta_author']="(Optional) Enter here for the benefit of search engines the name of the author of your pages.";
$tx['help']['backup_numberoffiles']="After each logout a backup of the content file is generated. Enter the number of such files which the system automatically keeps.";

$tx['help']['show_hidden_path_locator']="Whether the path of the hidden page is shown in the locator.";
$tx['help']['show_hidden_pages_search']="Whether hidden pages are shown in the results of the internal search function.";
$tx['help']['show_hidden_pages_sitemap']="Whether hidden pages are shown in the sitemap.";
$tx['help']['show_hidden_pages_toc']="Whether hidden pages are shown in the toc (navigation menu), if they are called (for example called by link).";

$tx['help']['images_maxsize']="Maximum size of uploaded images in Byte. This must neither exceed the limit set for upload_max_filesize nor post_max_size in the PHP configuration.";
$tx['help']['language_default']="The primary language of your site";
$tx['help']['locator_show_homepage']="Whether the locator starts with a link to the first page (homepage) or not.";
$tx['help']['mailform_captcha']="Whether a CAPTCHA shall be used in the mailform to prevent SPAM-mails.";
$tx['help']['mailform_email']="The mailform will only be enabled when an email address is entered here.";
$tx['help']['mailform_lf_only']="If sending of mails doesn't properly work with the default, try enabling this option.";
$tx['help']['menu_color']="Not used by CMSimple_XH core";
$tx['help']['menu_highlightcolor']="Not used by CMSimple_XH core";
$tx['help']['menu_sdoc']="Leave it empty or enter \"parent\", which gives the class \"sdocs\" to higher level navigation links when lower pages of that branch are selected.";

$tx['help']['meta_robots']="Default setting for all pages of your site. \"index,follow\" tells robots to list the present page in the seach index and to follow all links of the page.\"noindex,nofollow\" prevents this.";

$tx['help']['pagemanager_external']="If you want to use an external page manager, install the plugin and enter its name here";
$tx['help']['plugins_disabled']="A comma separated list of plugins which shall not be loaded. <strong>Caveat: if any of these plugins is actually in use on the site, you may not be able to access the site anymore, and would have to fix this option via FTP!</strong>";
$tx['help']['plugins_hidden']="A comma separated list of plugins which shall not be shown in the admin menu.";
$tx['help']['plugins_folder']="Please do not change";
$tx['help']['security_password']="Password of the site and all secondary language pages";
$tx['help']['security_email']="The email address for the password forgotten functionality. It is preferable to use an address that is not publicly known.";
$tx['help']['security_frame_options']="Whether pages of your site are allowed to be displayed in frames: \"DENY\" means never, \"SAMEORIGIN\" means only on pages from the same domain. Leave empty to allow framing, what is, however, not recommended for security reasons.";
$tx['help']['site_template']="Default template of the site";
$tx['help']['site_timezone']="Usually no entry necessary. Starting from PHP 5.1.0  a <a href=\"http://www.php.net/manual/en/timezones.php\">time zone</a> can be entered to override your server's setting (see http://www.php.net/manual/en/timezones.php).";
$tx['help']['site_compat']="Whether the website needs functions that have been removed in CMSimple_XH 1.7.";
$tx['help']['title_format']="The way the title of a page of your site (&lt;title&gt;) is shown in the tab of your browser.";
$tx['help']['uri_seperator']="The character which separates names of pages and sub pages in the URL.";
$tx['help']['uri_word_separator']="The character which separates words in the URL.";
$tx['help']['uri_length']="The URLs of the pages will be truncated at this length. This might change in a future release, so it's best to use shorter page headings (e.g. by using Page&rarr;Alternative heading).";

$tx['help']['folders_content']="The folder where the contents are stored (content.htm etc.)";
$tx['help']['folders_userfiles']="The base folder of all userfiles.";
$tx['help']['folders_downloads']="A subfolder of userfiles.";
$tx['help']['folders_images']="A subfolder of userfiles.";
$tx['help']['folders_media']="A subfolder of userfiles.";

$tx['help']['format_date']="The date format if ext/intl is available; otherwise the date/time format falls back to <code>\$tx['lastupdate']['format']</code>.";
$tx['help']['format_time']="The time format if ext/intl is available; otherwise the date/time format falls back to <code>\$tx['lastupdate']['format']</code>.";

$tx['label']['empty']="- EMPTY -";

$tx['languagemenu']['text']="select language: ";

$tx['lastupdate']['dateformat']="F d, Y, H:i";
$tx['lastupdate']['text']="Last update";

$tx['link']['check']="Please check: ";
$tx['link']['check_errors']="Problems encountered: ";
$tx['link']['check_ok']="No errors found";
$tx['link']['checked_1']="%d link has been checked. ";
$tx['link']['checked_2_4']="%d links have been checked. ";
$tx['link']['checked_5']="%d links have been checked. ";
$tx['link']['checking']="Link check in progress...";
$tx['link']['email']="Is this email address valid and still in use?";
$tx['link']['error']="Error: ";
$tx['link']['errors']="Errors: ";
$tx['link']['ext_error_domain']="faulty external Link, domain not reachable.";
$tx['link']['ext_error_page']="faulty external Link, page not reachable.";
$tx['link']['hints']="Hints:";
$tx['link']['int_error']="faulty internal Link, page does not exist.";
$tx['link']['int_error_fragment']="faulty internal Link, anchor does not exist.";
$tx['link']['link']="Link: ";
$tx['link']['linked_page']="Link target: ";
$tx['link']['page']="Page: ";
$tx['link']['redirect']="The targetted page redirects to another location. Please check it and update your link.";
$tx['link']['returned_status']="Returned http status code: ";
$tx['link']['unknown']="Unknown problem, please check this link.";

$tx['locator']['home']="Home";
$tx['locator']['text']="You are here: ";

$tx['log']['dateformat']="Y-m-d H:i:s";
$tx['log']['loggedin']="logged in";
$tx['log']['timestamp']="timestamp";
$tx['log']['type']="type";
$tx['log']['module']="module";
$tx['log']['category']="category";
$tx['log']['description']="description";

$tx['login']['back']="Back";
$tx['login']['failure']="You have entered a wrong password!";
$tx['login']['loggedout']="You have been logged out";
$tx['login']['warning']="Site administration. Please enter password.";

$tx['mailform']['captcha']="Please enter this number (spam prevention)";
$tx['mailform']['captchafalse']="Please enter anti-spam code";
$tx['mailform']['message']="Message";
$tx['mailform']['mustwritemessage']="No message has been entered";
$tx['mailform']['notaccepted']="Please fill in the required fields";
$tx['mailform']['notsend']="The message could not be sent";
$tx['mailform']['send']="The message has been sent";
$tx['mailform']['sendbutton']="Send";
$tx['mailform']['sender']="Your email (required): ";
$tx['mailform']['sendername']="Your name: ";
$tx['mailform']['senderphone']="Your phone number: ";
$tx['mailform']['subject']="Subject (required): ";
$tx['mailform']['subject_default']="Mailform on %s";

$tx['menu']['login']="Login";
$tx['menu']['mailform']="Mailform";
$tx['menu']['print']="Print view";
$tx['menu']['sitemap']="Sitemap";
$tx['menu']['tab_main']="Main Settings";
$tx['menu']['tab_css']="Stylesheet";
$tx['menu']['tab_config']="Config";
$tx['menu']['tab_language']="Language";
$tx['menu']['tab_help']="Help";

$tx['message']['backedup']="The content has been successfully backed up.";
$tx['message']['debug_mode']="Debug-Mode is enabled!";
$tx['message']['emptied']="The content has been successfully emptied.";
$tx['message']['pd_success']="Page data successfully saved. Some settings may only become effective after page refresh or browsing to another page.";
$tx['message']['pd_fail']="The page data could not be saved. Please try again.";
$tx['message']['restored']="The backup has been successfully restored.";
$tx['message']['saved']="%s successfully saved.";

$tx['navigator']['next']="next »";
$tx['navigator']['previous']="« prev";
$tx['navigator']['top']="top";

$tx['pagedata']['deleted_1']="%s page data field has been deleted.";
$tx['pagedata']['deleted_2_4']="%s page data fields have been deleted.";
$tx['pagedata']['deleted_5']="%s page data fields have been deleted.";
$tx['pagedata']['fail']="Could not save \"%s\"!";
$tx['pagedata']['info']="The following page data fields are <em>currently</em> unused, and may be deleted.";
$tx['pagedata']['nothing']="No action necessary.";
$tx['pagedata']['ok']="No superfluous fields detected in page data.";

$tx['password']['confirmation']="Confirmation";
$tx['password']['fields_missing']="Fill out all fields.";
$tx['password']['invalid']="New password must consist of ASCII characters only.";
$tx['password']['mismatch']="New password and its confirmation do not match.";
$tx['password']['new']="New password";
$tx['password']['old']="Old password";
$tx['password']['score']="Password score: %s";
$tx['password']['wrong']="Old password is wrong.";

$tx['password_forgotten']['email1_sent']="An email has been sent to the configured address with a link to reset the password. This link is valid for 1-2 hours.";
$tx['password_forgotten']['email1_text']="You have requested to reset your password. Click the following link to reset your password:";
$tx['password_forgotten']['email2_sent']="The password has been reset. An email with the new password has been sent to the configured address.";
$tx['password_forgotten']['email2_text']="Your password has been reset. Your new password is:";
$tx['password_forgotten']['request']="Confirm the configured email address to request instructions to reset the password.";

$tx['result']['created']="created";
$tx['result']['deleted']="deleted";

$tx['search']['button']="Search";
$tx['search']['found_1']="\"%s\" was found in one page:";
$tx['search']['found_2-4']="\"%s\" was found in %d pages:";
$tx['search']['found_5']="\"%s\" was found in %d pages:";
$tx['search']['label']="Search terms";
$tx['search']['notfound']="\"%s\" was not found.";
$tx['search']['result']="Result of your search";

$tx['settings']['backup']="Backup";
$tx['settings']['backupexplain1']="On logout content is backed up and the oldest backup file will be deleted.";
$tx['settings']['backupexplain2']="Backup file names start with date and time of backup as: YYYYMMDD_HHMMSS";
$tx['settings']['backupsuffix']="Enter a filename (only a-z, 0-9, minus and underscore; at most 20 characters):";
$tx['settings']['ftp']="Use FTP for remote file management";
$tx['settings']['more']="More";
$tx['settings']['systemfiles']="System files";
$tx['settings']['warning']="Only change settings when you understand the effect your changes will have!";

$tx['submenu']['heading']="Submenu";

$tx['syscheck']['access_protected']="'%s' is access protected";
$tx['syscheck']['bom']="there is no <a href=\"http://www.cmsimple-xh.org/wiki/doku.php/utf8#what_s_a_bom\" target=\"_blank\">BOM</a>";
$tx['syscheck']['cookie_lifetime']="session.cookie_lifetime is 0";
$tx['syscheck']['extension']="extension '%s' is loaded";
$tx['syscheck']['fail']="failure";
$tx['syscheck']['fsockopen']="function fsockopen is available";
$tx['syscheck']['locale_available']="locale '%s' is available";
$tx['syscheck']['locale_default']="default locale is in use";
$tx['syscheck']['magic_quotes']="magic_quotes_runtime is off";
$tx['syscheck']['message']="Checking that %1\$s … %2\$s";
$tx['syscheck']['password']="non-default password is set";
$tx['syscheck']['phpversion']="PHP version ≥ %s";
$tx['syscheck']['safe_mode']="safe_mode is off";
$tx['syscheck']['success']="okay";
$tx['syscheck']['timezone']="time zone is valid";
$tx['syscheck']['title']="System check";
$tx['syscheck']['use_only_cookies']="session.use_only_cookies is on";
$tx['syscheck']['use_trans_sid']="session.use_trans_sid is off";
$tx['syscheck']['warning']="warning";
$tx['syscheck']['writable']="'%s' is writable";

$tx['sysinfo']['helplinks']="Info and Help Links";
$tx['sysinfo']['php_version']="PHP-Version";
$tx['sysinfo']['phpinfo_hint']="(opens in a new window or tab)";
$tx['sysinfo']['phpinfo_link']="PHP Info &raquo;";
$tx['sysinfo']['plugins']="Installed Plugins";
$tx['sysinfo']['version']="Installed CMSimple Version";
$tx['sysinfo']['unknown']="Webserver could not be determined";
$tx['sysinfo']['webserver']="Webserver";

$tx['template']['active']="Active Template: ";
$tx['template']['default']="default template";

$tx['title']['bad_request']="Bad request";
$tx['title']['change_password']="Change Password";
$tx['title']['cms']="Content Management System";
$tx['title']['downloads']="Downloads";
$tx['title']['images']="Images";
$tx['title']['log']="Log File";
$tx['title']['mailform']="Mailform";
$tx['title']['media']="Mediafiles";
$tx['title']['xh_pagedata']="Page Data Cleanup";
$tx['title']['password_forgotten']="Password forgotten";
$tx['title']['phpinfo']="PHP Info";
$tx['title']['plugins']="Plugins";
$tx['title']['search']="Search";
$tx['title']['settings']="Settings";
$tx['title']['sitemap']="Sitemap";
$tx['title']['sysinfo']="System Info";
$tx['title']['templates']="Templates";
$tx['title']['userfiles']="Userfiles";
$tx['title']['validate']="Validate links";
$tx['title']['xh_backups']="Backup";

$tx['toc']['dupl']="DUPLICATE PAGE NAME";
$tx['toc']['empty']="EMPTY PAGE NAME";
$tx['toc']['missing']="MISSING PAGE NAME";
$tx['toc']['newpage']="NEW PAGE";

$tx['uri']['toolong']="According to Settings&rarr;CMS&rarr;Uri&rarr;Length the URL is too long:";

$tx['validate']['extfail']="EXTERNAL LINK FAILED";
$tx['validate']['extok']="EXTERNAL LINK OK";
$tx['validate']['intfail']="INTERNAL LINK FAILED";
$tx['validate']['intfilok']="INTERNAL LINK TO FILE OK";
$tx['validate']['intok']="INTERNAL LINK OK";
$tx['validate']['mailto']="MAILTO LINK";
$tx['validate']['notxt']="NO TEXT IN LINK";
