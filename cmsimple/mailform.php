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
2012-02-18  GE removed inline css to core.css, added outer div with id for better styling by template
2011-08-30  captcha on/off + change in error messages by svasti, code improvement by cmb
2010-06-12  Bob for XH 1.2 : Mail header subject localized
2009-09-18  GE for CMSimple_XH
2008-11-19  JB for 32SE added captcha, senders phone and name
*/

if (preg_match('/mailform.php/i',sv('PHP_SELF')))die('Access Denied');

$o .= "\n" . '<div id="cmsimple_mailform">' .  "\n";
$title = $tx['title'][$f];
$o .= '<h1>' . $title . '</h1>';
initvar('sendername');
initvar('senderphone');
initvar('sender');
initvar('getlast');
initvar('cap');
initvar('mailform');

$t = '';

if ($action == 'send')
{
	$msg = ($tx['mailform']['sendername'] . ": "
	. stsl($sendername) . "\n"
	. $tx['mailform']['senderphone'] . ": "
	. stsl($senderphone) . "\n\n" . stsl($mailform));

    // echo ($msg);
	if ($getlast != $cap && trim($cf['mailform']['captcha']) == 'true')
	{
		$e .= '<li>' . $tx['mailform']['captchafalse'] . '</li>';
	}
	if ($mailform == '')
	{
		$e .= '<li>' . $tx['mailform']['mustwritemessage'] . '</li>';
	}
	if (!(preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[_a-z0-9-]+(\.[_a-z0-9-]+)*(\.([a-z]{2,4}))+$/i", $sender)))
	{
		$e .= '<li>' . $tx['mailform']['notaccepted'] . '</li>';
	}
	if (!$e && !(@mail_utf8($cf['mailform']['email'], $tx['menu']['mailform'] . ' ' . sv('SERVER_NAME'), $msg, "From: " . stsl($sender) . "\r\n" . "X-Remote: " . sv('REMOTE_ADDR') . "\r\n")))
	{
		$e .= '<li>' . $tx['mailform']['notsend'] . '</li>';
	}
	else
	{
		$t = '<p>' . $tx['mailform']['send'] . '</p>';
	}
}

if ($t == '' || $e != '')
{
    //	if (@$tx['mailform']['message'] != '')$o .= '<p>'.$tx['mailform']['message'].'</p>';

    /**
     * JB+ add captcha
     */
	srand((double)microtime()*1000000);
	$random=rand(10000,99999);

	$o .= '<form action="'.$sn.'" method="post">' . "\n";

	$o .= tag('input type="hidden" name="function" value="mailform"') . "\n";

	if (trim($cf['mailform']['captcha']) == 'true')
	{
		$o .= tag('input type="hidden" name="getlast" value="'.$random.'"') . "\n";
	}
	$o .= tag('input type="hidden" name="action" value="send"') . "\n";

    /**
     * fields before textarea
     */
	$o .= '<div>' . "\n" . $tx['mailform']['sendername'].': ' . tag('br') . "\n"
	.  tag('input type="text" class="text" size="35" name="sendername" value="'
	.  htmlspecialchars(stsl($sendername)).'"') . "\n"
	.  '</div>' . "\n"
	.  '<div>' . "\n" . $tx['mailform']['senderphone'].': ' . tag('br') . "\n"
	.  tag('input type="text" class="text" size="35"name="senderphone" value="'
	.  htmlspecialchars(stsl($senderphone)).'"') . "\n"
	. '</div>' . "\n"
	.  '<div>' . "\n" .  $tx['mailform']['sender'].': ' . tag('br') . "\n"
	.  tag('input type="text" class="text" size="35" name="sender" value="'
	.  htmlspecialchars(stsl($sender)).'"') . "\n"
	. '</div>' . "\n" . tag('br') . "\n";

    /**
     * textarea
     */
	$o .= '<textarea rows="12" cols="40" name="mailform">' . "\n";
	if ($mailform != 'true') $o .= htmlspecialchars(stsl($mailform)) . "\n";
	$o .= '</textarea>' . "\n";

    /**
     * captcha
     */
    if (trim($cf['mailform']['captcha']) == 'true')
	{
		$o .= '<p>' .  $tx['mailform']['captcha'] . '</p>' . "\n"
		. tag('input type="text" name="cap" class="captchainput"') . "\n"
		.  '<span class="captcha_code">' . "\n"
		.  $random . '</span>' . "\n";
    }

    /**
     * sendbutton
     */
	$o .= '<div style="clear: both;">' . "\n"
	.  tag('input type="submit" class="submit" value="'
	.  $tx['mailform']['sendbutton'] . '"') . "\n" . '</div>' . "\n";
	$o .= '</form>' . "\n" . '</div>' . "\n";

}
else $o .= $t;

function mail_utf8($to, $subject = '(No Subject)', $message = '', $header = '')
{
	$header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
	if(mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header))
	{
		return true;
	}
	return false;
}
?>