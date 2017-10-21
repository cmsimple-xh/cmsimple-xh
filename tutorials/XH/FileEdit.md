The FileEdit Class Hierarchy
============================

[TOC]

Introduction {#intro}
=====================

The FileEdit class hierarchie is responsible for the handling of the
editing of files in the back-end. Its implementation makes heavy use
of the [template method pattern](http://en.wikipedia.org/wiki/Template_method_pattern).
The two main branches are TextFileEdit and ArrayFileEdit.

TextFileEdit {#textfileedit}
----------------------------

The predefined concrete subclasses of TextFileEdit offer a
simple textarea to edit the content of a text file.

ArrayFileEdit {#arrayfileedit}
------------------------------

This branch offers editing of files storing data in an array
structure. The predefined subclasses handle two- dimensional PHP
arrays, which are used to store the configuration options and
language strings of the core and plugins. The generated forms
group the options in categories. The options can have the
following types, which can be specified in a file
metaconfig.php.

* `string`: a rather short text (represented as text input)
* `text`: a text of arbitrary length (represented as textarea)
* `bool`: a boolean value (represented as checkbox)
* `enum`: one of several fixed values (represented as selectlist)
* `xenum`: a text with suggestions of several fixed values (represented as text input with datalist)
* `function`: one of several dynamic values (represented as selectlist)
* `xfunction`: a text with suggestions of several dynamic values (represented as text input with datalist)
* `hidden`: a hidden text field
* `random`: a hidden random value that is regenerated on each save

As of CMSimple_XH 1.7.0 it is also possible to mark configuration as advanced
options by prepending a `+`, for instance `+bool` or `+string`. Such options are
only displayed if the user presses the `More …` button in the configuration.

Usage {#usage}
==============

To display the edit form ::form() has to be called; to handle the
form submission ::submit() has to be called. For instance:

````{.php}
require_once $pth['folder']['classes'] . 'FileEdit.php';

$editor = new XH_CoreConfigFileEdit();
if ($save) {
    $o .= $editor->submit();
} else {
    $o .= $editor->form();
}
````

By means of extending an appropriate class of the hierarchy, it is
possible to make other files even in other formats editable online.
An example:

````{.php}
require_once $pth['folder']['classes'] . 'FileEdit.php';

class MyTextFileEdit extends XH_TextFileEdit
{
    function MyTextFileEdit()
    {
        $this->filename = 'path/of/the/file';
        $this->params = array('what' => 'my_file', 'action' => 'save');
        $this->redir = "?what=my_file&action=edit";
        $this->textareaName = 'my_textarea';
        parent::XH_TextFileEdit();
    }
}

if (isset($_REQUEST['what']) && $_REQUEST['what'] == 'my_file') {
    $fileEditor = new MyTextFileEdit();
    if ($_REQUEST['action'] == 'save') {
        $o .= $fileEditor->submit();
    } else {
        $o .= $fileEditor->form();
    }
}
````
