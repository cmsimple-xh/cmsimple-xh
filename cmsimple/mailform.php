<?php

/* utf8-marker = äöü */
/*
  ======================================
  CMSimple_XH 1.5.2
  2012-02-20
  based on CMSimple version 3.3 - December 31. 2009
  For changelog, downloads and information please see http://www.cmsimple-xh.com
  ======================================
  -- COPYRIGHT INFORMATION START --
  Based on CMSimple version 3.3 - December 31. 2009
  Small - simple - smart
  © 1999-2009 Peter Andreas Harteg - peter@harteg.dk

  This file is part of CMSimple_XH
  For licence see notice in /cmsimple/cms.php
  -- COPYRIGHT INFORMATION END --
  ======================================
 */


/* 
History:
2012-02-23  AG do some code cleanup
2012-02-18  GE removed inline css to core.css, added outer div with id for better styling by template
2011-08-30  captcha on/off + change in error messages by svasti, code improvement by cmb
2010-06-12  Bob for XH 1.2 : Mail header subject localized
2009-09-18  GE for CMSimple_XH
2008-11-19  JB for 32SE added captcha, senders phone and name
*/
$evaluate = preg_match('/mailform.php/i', sv('PHP_SELF'));
if ($evaluate) {
    die('Access Denied');
}

$o .= '<div id="cmsimple_mailform">' . "\n";
$title = $tx['title'][$f];
$o .= '<h1>' . $title . '</h1>' . "\n";
initvar('sendername');
initvar('senderphone');
initvar('sender');
initvar('getlast');
initvar('cap');
initvar('mailform');

$t = '';

if ($action == 'send') {
    $msg = $tx['mailform']['sendername'] . ": " . stsl($sendername) . "\n";
    $msg .= $tx['mailform']['senderphone'] . ": " . stsl($senderphone) . "\n";
    $msg .= stsl($mailform);

    // echo ($msg);
    $evalute = $getlast != $cap && trim($cf['mailform']['captcha']);
    if ($evalute) {
        $e .= '<li>' . $tx['mailform']['captchafalse'] . '</li>';
    }
    $evaluate = $mailform == '';
    if ($evaluate) {
        $e .= '<li>' . $tx['mailform']['mustwritemessage'] . '</li>';
    }
    $evaluate = !(preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[_a-z0-9-]+(\.[_a-z0-9-]+)*(\.([a-z]{2,4}))+$/i", $sender));
    if ($evaluate) {
        $e .= '<li>' . $tx['mailform']['notaccepted'] . '</li>';
    }

    $to = $cf['mailform']['email'];
    $subject = $tx['menu']['mailform'] . ' ' . sv('SERVER_NAME');
    $header = "From: " . stsl($sender) . "\r\n";
    $header .= "X-Remote: " . sv('REMOTE_ADDR') . "\r\n";
    $evaluate = !$e && !(mail_utf8($to, $subject, $msg, $header));
    if ($evaluate) {
        $e .= '<li>' . $tx['mailform']['notsend'] . '</li>';
    } else {
        $t = '<p>' . $tx['mailform']['send'] . '</p>';
    }
}

if (!($t == '' || $e != '')) {
    $o .= $t;
} else {
    /**
     * JB+ add captcha
     */
    srand((double)microtime() * 1000000);
    $random = rand(10000, 99999);
    $o .= '<form action="' . $sn . '" method="post">' . "\n";
    $o .= tag('input type="hidden" name="function" value="mailform"') . "\n";

    $evaluate = trim($cf['mailform']['captcha']) == 'true';
    if ($evaluate) {
        $o .= tag('input type="hidden" name="getlast" value="' . $random . '"') . "\n";
    }
    $o .= tag('input type="hidden" name="action" value="send"') . "\n";

    /**
     * fields before textarea
     */
    $sendername = htmlspecialchars(stsl($sendername));
    $senderphone = htmlspecialchars(stsl($senderphone));
    $sender = htmlspecialchars(stsl($sender));
    $o .= '<div>' . "\n";
    $o .= $tx['mailform']['sendername'] . ': ' . tag('br') . "\n";
    $o .= tag('input type="text" class="text" size="35" name="sendername" value="' . $sendername . '"') . "\n";
    $o .= '</div>' . "\n";
    $o .= '<div>' . "\n";
    $o .= $tx['mailform']['senderphone'] . ': ' . tag('br') . "\n";
    $o .= tag('input type="text" class="text" size="35" name="senderphone" value="' . $senderphone . '"') . "\n";
    $o .= '</div>' . "\n";
    $o .= '<div>' . "\n";
    $o .= $tx['mailform']['sender'] . ': ' . tag('br') . "\n";
    $o .= tag('input type="text" class="text" size="35" name="sender" value="' . $sender . '"') . "\n";
    $o .= '</div>' . "\n";
    $o .= tag('br') . "\n";

    /**
     * textarea
     */
    $o .= '<textarea rows="12" cols="40" name="mailform">' . "\n";
    $evaluate = $mailform == 'true';
    if ($evaluate) {
        $o .= htmlspecialchars(stsl($mailform)) . "\n";
    }
    $o .= '</textarea>' . "\n";

    /**
     * captcha
     */
    $evaluate = trim($cf['mailform']['captcha']) == 'true';
    if ($evaluate) {
        $o .= '<p>' . $tx['mailform']['captcha'] . '</p>' . "\n";
        $o .= tag('input type="text" name="cap" class="captchainput"') . "\n";
        $o .= '<span class="captcha_code">' . "\n";
        $o .= $random . '</span>' . "\n";
    }

    /**
     * sendbutton
     */
    $o .= '<div style="clear: both;">' . "\n";
    $o .= tag('input type="submit" class="submit" value="' . $tx['mailform']['sendbutton'] . '"') . "\n";
    $o .= '</div>' . "\n";
    $o .= '</form>' . "\n";
    $o .= '</div>' . "\n";
}

function mail_utf8($to, $subject = '(No Subject)', $message = '', $header = '') {
    $preheader = 'MIME-Version: 1.0' . "\r\n";
    $preheader .= 'Content-type: text/plain; charset=UTF-8' . "\r\n";
    $header = $preheader . $header;
    $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $result = mail($to, $subject, $message, $header);
    return $result;
}

?>
