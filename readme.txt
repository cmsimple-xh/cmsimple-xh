======================================
CMSimple_XH 1.3
22-11-2010
based on CMSimple version 3.3 - December 31. 2009
======================================

Description:
    CMSimple_XH is a easy-to-use an easy-to-install
    Content Management System (CMS) without the need for a database.
    CMSimple_XH stores all the content in one single html-file (content.htm).
    The headings H1-H3 are used to split the content.htm into single pages,
    where also the menu is made of. By standard the menu contains three levels.
    The headings H4-H6 can be used for structurize the content within the single
    pages.

Changes in this version:
    - added hungarian language
    - added sk fckeditor language
    - nice looking backend, especially edit view of pluginloader files
    - optimized and styled notices and warnings
    - "outsourcing" of language-dependent config from the language files to the
      new langconfig files
    - sysinfo-, phpinfo- and help-page
    - using core.css, removed many inline- and html-styles
    - inserted many "\n" in adm.php and index.php of Pluginloader for better
      readable sourcecode
    - moved $tx variables for site title, description, keywords and template
      texts to langconfig.php's
    - renamed that $tx-variables to $txc-variables ("c" for config)

Upgrade instructions: (Not for new installations)
    1. Back-up your old site.
    2. Download the upgrade package (Upgrade only possible if your current version
       is 1.2)
    3. Copy the contents of your download to the root folder of your website.
       Overwrite existing files if needed.
    4. Go to your website, log in, go to settings and change to language files to
       fit your needs. This because the update overwrites them.
    5. That's all!