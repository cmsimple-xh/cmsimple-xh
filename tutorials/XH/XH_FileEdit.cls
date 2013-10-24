<refentry id="{@id}">
    <refnamediv>
        <refname>The FileEdit Class Hierarchy</refname>
    </refnamediv>
    {@toc}
    <refsect1 id="{@id intro}">
        <title>Introduction</title>
        <para>
            The FileEdit class hierarchie is responsible for the handling of the
            editing of files in the back-end. Its implementation makes heavy use
            of the
            {@link http://en.wikipedia.org/wiki/Template_method_pattern template method pattern}.
            The two main branches are TextFileEdit and ArrayFileEdit.
        </para>
        <refsect2 id="{@id textfileedit}">
            <title>TextFileEdit</title>
            <para>
                The predefined concrete subclasses of TextFileEdit offer a
                simple textarea to edit the content of a text file.
            </para>
        </refsect2>
        <refsect2 id="{@id arrayfileedit}">
            <title>ArrayFileEdit</title>
            <para>
                This branch offers editing of files storing data in an array
                structure. The predefined subclasses handle two- dimensional PHP
                arrays, which are used to store the configuration options and
                language strings of the core and plugins. The generated forms
                group the options in categories. The options can have the
                following types, which can be specified in a file
                metaconfig.php.
            </para>
            <itemizedlist>
                <listitem>
                    <emphasis>string</emphasis>:
                    a rather short text (represented as text input).
                </listitem>
                <listitem>
                    <emphasis>text</emphasis>:
                    a text of arbitrary length (represented as textarea).
                </listitem>
                <listitem>
                    <emphasis>bool</emphasis>:
                    a boolean value (represented as checkbox).
                </listitem>
                <listitem>
                    <emphasis>enum</emphasis>:
                    one of several fixed values (represented as selectlist).
                </listitem>
                <listitem>
                    <emphasis>function</emphasis>:
                    one of several dynamic values (represented as selectlist).
                </listitem>
                <listitem>
                    <emphasis>hidden</emphasis>:
                    a hidden text field.
                </listitem>
                <listitem>
                    <emphasis>random</emphasis>:
                    a hidden random value that is regenerated on each save.
                </listitem>
                <listitem>
                    <emphasis>password</emphasis>:
                    a password hash (represented as button which opens a dialog,
                    where the old password has to be confirmed to set the new
                    password).
                </listitem>
            </itemizedlist>
        </refsect2>
    </refsect1>
    <refsect1 id="{@id usage}">
        <title>Usage</title>
        <para>
            To display the edit form ::form() has to be called; to handle the
            form submission ::submit() has to be called. For instance:
            <programlisting><![CDATA[
require_once $pth['folder']['classes'] . 'FileEdit.php';

$editor = new XH_CoreConfigFileEdit();
if ($save) {
    $o .= $editor->submit();
} else {
    $o .= $editor->form();
}
            ]]></programlisting>
        </para>
        <para>
            By means of extending an appropriate class of the hierarchy, it is
            possible to make other files even in other formats editable online.
            An example:
        </para>
        <programlisting><![CDATA[
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
        ]]></programlisting>
    </refsect1>
</refentry>
