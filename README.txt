===========================================================
 @CMSIMPLE_XH_VERSION@
 released @CMSIMPLE_XH_DATE@
===========================================================

 1. PREPARATION / SERVER TEST
 
 Upload the file "reqcheck.php" to the directory,
 in which your CMSimple_XH installation should take place.
 
 Call this file with a browser:
 http(s)://example.com[/subdirectory]/reqcheck.php
 
 When errors or problems are reported
 these must be eliminated.
 
 Only when everything is GREEN (so everything is in order)
 you can start the installation after you have deleted
 the file "reqcheck.php".

===========================================================

 2. INSTALLATION

 Extract the ZIP archive from the download.
 Now upload all files from the folder "cmsimplexh/"
 to your web server.
 
 On some servers, write permissions must be explicitly
 assigned for some files, see also:
 https://wiki.cmsimple-xh.org/doku.php/installation
 
 Detailed UPDATE instructions
 are available in the CMSimple_XH forum:
 https://www.cmsimpleforum.com/viewtopic.php?f=16&t=4895

===========================================================

 3. CHANGE DEFAULT PASSWORD
 
 The default password for this installation is:
 "test" (without quotation marks)
 
 The default password must now be changed immediately!
 You have 5 minutes to do this after the first page view.
 During this time, only the password can be changed,
 all other settings cannot be saved.
 Proceed as follows:
 
 Log in with 'test'! You will be redirected automatically:
 Settings > Password
 You can now enter your own password here.

 IMPORTANT NOTE
 =================
 Please do NOT change the default password with
 a text editor directly in config.php, as it only
 contains the encrypted password.
 Change the default password immediately
 after the first login ONLINE!
 (Login with the default password "test")
 This is the safest working method.
 
 To edit the CMSimple_XH system files you should only
 use an editor (e.g. notepad++) which recognizes the
 coding "utf-8 without BOM" (Byte Order Mark),
 opens and saves the files in this way.
 
 If the system files are stored in a different
 encoding than "utf-8 without BOM", serious problems
 can occur with various CMSimple_XH functions.
 
 PASSWORD FORGOTTEN
 If you have forgotten your password, you can restore
 the default password "test".
 Enter (offline) the following in the file
 "config.php" under $cf['security']['password']=
 \$2y\$10\$TtMCJlxEv6D27BngvfdNrewGqIx2R0aPCHORruqpe63LQpz7.E9Gq
 
 Then upload the file "config.php" to the server again.
 Then you can log back in with the default password "test".

===========================================================

 Software Description:
 =====================
 CMSimple_XH is a fast, small, easy to use and
 easy to install modular Content Management System (CMS),
 which does not require a database. CMSimple_XH stores the
 content of all pages in a single HTML file.
 It is free open source software under the GPL3 license.
 