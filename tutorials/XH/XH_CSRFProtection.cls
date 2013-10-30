<refentry id="{@id}">
    <refnamediv>
        <refname>CSRF Protection</refname>
    </refnamediv>
    {@toc}
    <refsect1 id="{@id intro}">
        <title>Overview</title>
        <para>CSRF (Cross-site request forgery) is a type of malicious exploit
        of a website whereby unauthorized commands are transmitted from a user
        that the website trusts.[{@link http://en.wikipedia.org/wiki/Cross-site_request_forgery Wikipedia}]
        Regarding CMSimple_XH this user is typically the administrator, who
        could be tricked to trigger a forged HTML request while being logged in
        to his website. This way an attacker can potentially do anything the
        admin is allowed to do, e.g. changing the configuration settings and
        modifying the template. While the risk of a CSRF attack may be low for a
        CMSimple_XH website, the severity would be very high, so it is
        reasonable to take precautions.</para>
        <para>The CSRF protection of CMSimple_XH is based on randomly created
        128bit values (aka. tokens) which are placed on each respective form and
        are stored in the user's session data. On form submission the tokens are
        compared, and if they are not equal, the form submission is rejected.
        The tokens are renewed for each relevant request, so an attacker who
        wants to forge a form has to <emphasis>guess</emphasis> the currently expected
        token, what makes the success of an attack highly unlikely.</para>
        <para>For now, the CSRF protection functionality is made available as a
        global object, <literal>{@link $_XH_csrfProtection}</literal>; this is
        quite likely to change in a future version.</para>
    </refsect1>
    <refsect1 id="{@id usage}">
        <title>Usage</title>
        <para>Every form which has to be protected against CSRF attacks has to
        be extended by a hidden input element which can be inserted by calling
        <literal>{@link XH_CSRFProtection::tokenInput()
        $_XH_csrfProtection->tokenInput()}</literal>, e.g.:</para>
        <programlisting><![CDATA[
<form action="..." method="post">
<!-- content and input elements of the form -->
<?php echo $_XH_csrfProtection->tokenInput();?>
</form>]]>
        </programlisting>
        <para>On form submission the tokens have to be checked by calling
        <literal>{@link XH_CSRFProtection::check() $_XH_csrfProtection->check()}</literal>. If the
        tokens do not match, script execution will be immediately terminated
        with an appropriate message. Giving a clear indication of the error is
        reasonable in this case, as the message will not be seen by the
        attacker, but by the administrator, who can easily conclude that
        somebody attempted a CSRF attack against his website. An example of the
        check:</para>
        <programlisting><![CDATA[
if (isset($_POST['my_form'])) {
    $_XH_csrfProtection->check();
    // processing form submission
}]]>
        </programlisting>
        <para>Basically that's all. The details of creating new CSRF tokens when
        required and storing the latest token in the session are handled by
        CMSimple_XH. Everything is supposed to work fine, even if multiple forms
        with CSRF protection will be emitted for a single document (aka.
        CMSimple_XH page).</para>
        <para>The complete CSRF protection is already in place for the core of
        CMSimple_XH and the administration forms of plugins, which are handled
        by <literal>{@link plugin_admin_common()}</literal>. Other forms
        require to add CSRF protection in the way described above.</para>
    </refsect1>
    <refsect1 id="{@id special cases}">
        <title>Special Cases</title>
        <refsect2 id="{@id ajax}">
            <title>AJAX</title>
            <para>AJAX requests which will respond with an (X)HTML fragment,
            which is built with a call to <literal>{@link XH_CSRFProtection::tokenInput()
            $_XH_csrfProtection->tokenInput()}</literal>, that will be inserted
            in the document (for instance via the innerHTML property of an
            element) will not work. Usually one can work around this limitation
            by reusing the original form instead of replacing it. Alternatively,
            it is possible to treat this situation as for separate
            windows.</para>
        </refsect2>
        <refsect2 id="{@id separate-windows}">
            <title>Separate Windows</title>
            <para>Sometimes a plugin wants deliver an (X)HTML document in a
            secondary browser window, while the primary window still should be
            usable. An example is a filebrowser which allows to select an image
            from a popup window to an editor in the main window. In this case
            <literal>{@link XH_CSRFProtection::tokenInput()
            $_XH_csrfProtection->tokenInput()}</literal> must not be called when
            creating the (X)HTML for the secondary window, as that would
            invalidate the form in the primary window.</para>
            <para>In this case one may create his own <literal>{@link XH_CSRFProtection}
            </literal> object and specify an own key name to
            avoid clashes with <literal>{@link $_XH_csrfProtection}</literal>.
            Additionally, one has to call <literal>{@link XH_CSRFProtection::store()}
            </literal> to store the CSRF token in the
            session variable. An outline:</para>
            <programlisting><![CDATA[
require_once $pth['folder']['classes'] . 'CSRFProtection.php';
$myCsrfProtection = new XH_CSRFProtection('my_csrf_key');
if (!isset($_POST[...])) {
    echo '<form ...>';
    // emit form input elements
    echo $myCsrfProtection->tokenInput();
    echo '</form>';
    $myCsrfProtection->store();
} else {
    $myCsrfProtection->check();
    // processing of form submission
}
]]>
            </programlisting>
        </refsect2>
        <refsect2 id="{@id external-scripts}">
            <title>External Scripts</title>
            <para>Sometimes a plugin requests an "external" script, i.e. does
            not request an index.php of the CMSimple_XH installation. In this
            case you can't use the <literal>{@link XH_CSRFProtection}</literal>
            class at all, because this class depends on variables, constants
            etc. which will be set up by CMSimple_XH. You might get the class to
            work properly, but the definition of the class might change in the
            next version. So you're better off to implement your own CSRF
            protection in this case.</para>
        </refsect2>
    </refsect1>
</refentry>