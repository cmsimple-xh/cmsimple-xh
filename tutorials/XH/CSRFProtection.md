CSRF Protection
===============

[TOC]

According to [Wikipedia](http://en.wikipedia.org/wiki/Cross-site_request_forgery):
> CSRF (Cross-site request forgery) is a type of malicious exploit
> of a website whereby unauthorized commands are transmitted from a user
> that the website trusts.

Regarding CMSimple_XH this user is typically the administrator, who
could be tricked to trigger a forged HTML request while being logged in
to his website. This way an attacker can potentially do anything the
admin is allowed to do, e.g. changing the configuration settings and
modifying the template. While the risk of a CSRF attack may be low for a
CMSimple_XH website, the severity would be very high, so it is
reasonable to take precautions.

The CSRF protection of CMSimple_XH is based on randomly created
128bit values (aka. tokens) which are placed on each respective form and
are stored in the user's session data. On form submission the tokens are
compared, and if they are not equal, the form submission is rejected.
The tokens are renewed for each session, so an attacker who wants to
forge a form has to *guess* the currently expected
token, what makes the success of an attack highly unlikely.

For now, the CSRF protection functionality is made available as a
global object, @ref $_XH_csrfProtection; this is
quite likely to change in a future version.

Usage {#usage}
=====

Every form which has to be protected against CSRF attacks has to
be extended by a hidden input element which can be inserted by calling
`XH::CSRFProtection::tokenInput()`, e.g.:

````
<form action="..." method="post">
<!-- content and input elements of the form -->
<?php echo $_XH_csrfProtection->tokenInput();?>
</form>
````

On form submission the tokens have to be checked by calling
`XH::CSRFProtection::check()`. If the
tokens do not match, script execution will be immediately terminated
with an appropriate message. Giving a clear indication of the error is
reasonable in this case, as the message will not be seen by the
attacker, but by the administrator, who can easily conclude that
somebody attempted a CSRF attack against his website. An example of the
check:

````
if (isset($_POST['my_form'])) {
    $_XH_csrfProtection->check();
    // processing form submission
}
````

Basically that's all. The details of creating new CSRF tokens when
required and storing the latest token in the session are handled by
CMSimple_XH. Everything is supposed to work fine, even if multiple forms
with CSRF protection will be emitted for a single document (aka.
CMSimple_XH page).

The complete CSRF protection is already in place for the core of
CMSimple_XH and the administration forms of plugins, which are handled
by `plugin_admin_common()`. Other forms
require to add CSRF protection in the way described above.

Stronger Protection {#stronger-protection}
===================

While a common token for each session gives reasonable protection
againgst CSRF attacks, a new token for each request is even more secure.
However, the latter has several limitations regarding Ajax, separate
windows and multiple browser tabs, as these might renew the token despite
the old token still being present on forms which should be submittable.
Therefore we have decided to stick with the more foolproof and flexible
approach to renew the token once per session.

The `XH::CSRFProtection` class, however,
allows you to handle your own CSRF token and to choose whether this is
renewed once per session of per request. To do so create your own
instance of the class and pass it an own key name and whether you want a
new token for each request. In addition to the handling explained above,
one has to call `XH::CSRFProtection::store()`
to store the CSRF token in the session variable at the end of the
request. An outline:

````
require_once $pth['folder']['classes'] . 'CSRFProtection.php';
$myCsrfProtection = new XH_CSRFProtection('my_csrf_key', true);
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
````

External Scripts {#external-scripts}
================

Sometimes a plugin requests an "external" script, i.e. does
not request an index.php of the CMSimple_XH installation. In this
case you can't use the `XH::CSRFProtection`
class at all, because this class depends on variables, constants
etc. which will be set up by CMSimple_XH. You might get the class to
work properly, but the definition of the class might change in the
next version. So you're better off to implement your own CSRF
protection in this case.
