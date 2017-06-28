Class Autoloading
=================

[TOC]

As of CMSimple_XH 1.7.0 the core uses [class
autoloading](http://php.net/manual/en/language.oop5.autoload.php), and the
autoloader (`XH_autoload()`) can also be used by plugins. It works very similar
to [PSR-0](http://www.php-fig.org/psr/psr-0/) , but an adjustment has been made
to better suite the tradionational folder structure of CMSimple_XH, namely that
the `<Vendor Name>` has actually to be the `<Plugin Name>` (or `XH` in case of
core classes), which maps to the `classes/` folder of the plugin.

Example {#example}
==================

Let's consider a fictious plugin named Foo with the following filesystem
structure (irrelevant files and folders ommitted for brevity):

````
foo/
    classes/
        bar/
            Baz.php
        Qux.php
````

This plugin declares two classes, namely `Baz` and `Qux`. To refer to these
classes you can use the fully qualified class name, i.e. `\Foo\Bar\Baz` and
`\Foo\Qux`, respectively, or any short form which resolves to these fully
qualified class names.

Underscores in Namespaces and Class Names {#underscores}
========================================================

Note that the class loader also supports old style pseudo namespacing with
underscores (`_`), which are treated identical to proper namespace separators,
and that the autoloader creates `class_alias()`es. So with regard to the example
Foo plugin above, you can also use `Foo_Bar_Baz` and `Foo_Qux` as class names.
This is meant for backward compatibility purposes, most notably for core classes
which didn't use namespaces in CMSimple_XH 1.6. For new developments proper
namespacing is recommended.

Case {#case}
============

To avoid issues regarding the case (in)sensitivity of different file systems,
you have to write all class names exactly like the name of the file they're
declared in, even though class names are case insensitive in PHP. The names of
Namespaces, however, are mapped to the lower case variant, so all subfolders of
`classes/` should be in lower case.
