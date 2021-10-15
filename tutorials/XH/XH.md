Developer Documentation for CMSimple_XH {#mainpage}
=======================================

This documentation is meant for core and plugin developers as well
as template designers. It documents the core and the standard
plugins which are not developed externally (currently Filebrowser,
Meta_tags and Page_params).
        
System Architecture {#mainpage_architecture}
-------------------

All requests to the website are directed to index.php in the root
folder of the installation or to index.php in a language folder.
These index.php files are just thin wrappers for including
{@link cms.php} which defines variables and constants and
includes necessary files according to the individual request.

Plugins {#mainpage_plugins}
-------

Plugins are handled by the "plugin loader". This term stems from
the past, where it was an external component that had to be
installed to be able to use plugins. CMSimple_XH integrated the
plugin loader to the default distribution and extended it to
cater for the new page data functionality. Since CMSimple_XH 1.6
the plugin loader has been merged into the core; nonetheless it
seems to be reasonable to speak of the "plugin loader" to refer
to the functionality regarding the loading of plugins and
editing of plugin files.

The plugin loader includes files of plugins in several stages,
where the respective files of all plugins are included:

* required classes
* configuration and language files
* index.php files
* admin.php files

Each stage processes the plugins in alphabetical order (before
CMSimple_XH 1.6 the order was undetermined). However, it is not
recommended to rely on this loading order and to name a plugin
respectively (e.g. "zzz"). If you have to do something after all
plugins have been loaded, use {@link XH_afterPluginLoading} to
register an appropriate callback.

More developer information about plugins can be found in the
[Wiki](http://www.cmsimple-xh.org/wiki/doku.php/developers_manual).

Templates {#mainpage_templates}
---------

At the end of usual page requests the file template.htm of the
active template is included. That is an HTML file with embedded
PHP, the so-called template tags, which are defined in
{@link tplfuncs.php}.

More developer information about templates can be found in the
[Wiki](http://www.cmsimple-xh.org/wiki/doku.php/developers_manual).

API {#mainpage_api}
---

An important part of the API of CMSimple_XH consists of global
variables which are documented in {@link cms.php}. Not all global
variables are part of the public API, only those tagged as public.
Furthermore many of the public variables should be treated as
read-only or read-write as documented.

\warning
All parameter names of functions and methods are subject to change,
and none of the CMSimple\_XH APIs should be called using named arguments for now.
