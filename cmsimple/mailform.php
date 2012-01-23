<?php

/* utf8-marker = äöü */
/*
  ======================================
  CMSimple_XH 1.5.1
  2012-01-03
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
2011-08-30  captcha on/off + change in error messages by svasti, code improvement by cmb
2010-06-12  Bob for XH 1.2 : Mail header subject localized
2009-09-18  GE for CMSimple_XH
2008-11-19  JB for 32SE added captcha, senders phone and name
*/

if (preg_match('/mailform.php/i',sv('PHP_SELF')))die('Access Denied');

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
        $e .= '<li style="font-size: 118%;">' . $tx['mailform']['captchafalse'];
    }
    if ($mailform == '')
    {
        $e .= '<li style="font-size: 118%;">' . $tx['mailform']['mustwritemessage'];
    }
    if (!(preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[_a-z0-9-]+(\.[_a-z0-9-]+)*(\.([a-z]{2,4}))+$/i", $sender)))
    {
        $e .= '<li style="font-size: 118%;">' . $tx['mailform']['notaccepted'];
    }
    if (!$e
        && !(@mail_utf8($cf['mailform']['email'],
           $tx['menu']['mailform'] . ' ' . sv('SERVER_NAME'),
           $msg, "From: " . stsl($sender) . "\r\n" . "X-Remote: " . sv('REMOTE_ADDR') . "\r\n")))
    {
        $e .= '<li style="font-size: 118%;">' . $tx['mailform']['notsend'];
    }
    else
    {
        $t = '<p style="font-size: 118%; text-align: center;">' . $tx['mailform']['send'] . '</p>';
    }
}

if ($t == '' || $e != '')
{
//	if (@$tx['mailform']['message'] != '')$o .= '<p>'.$tx['mailform']['message'].'</p>';

// JB+ add captcha
	srand((double)microtime()*1000000);
	$random=rand(10000,99999);

	$o .= '<form action="'.$sn.'" method="post">';

	$o .= tag('input type="hidden" name="function" value="mailform"');

    if (trim($cf['mailform']['captcha']) == 'true')
    {
        $o .= tag('input type="hidden" name="getlast" value="'.$random.'"');
    }
	$o .= tag('input type="hidden" name="action" value="send"');

// fields before textarea 
	$o .= '<div style="width:200px; margin: 0 0 8px 0;">'.$tx['mailform']['sendername'].': '
	   .  tag('input type="text" class="text" size="35" name="sendername" value="'
       .  htmlspecialchars(stsl($sendername)).'"')
	   .  '</div>'
	   .  '<div style="width:200px; margin: 0 0 8px 0;">'.$tx['mailform']['senderphone'].': '
	   .  tag('input type="text" class="text"  size="35"name="senderphone" value="'
       .  htmlspecialchars(stsl($senderphone)).'"').'</div>'
	   .  '<div style="width:200px; margin: 0 0 20px 0;">'
       .  $tx['mailform']['sender'].': '
       .  tag('input type="text" class="text"  size="35" name="sender" value="'
       .  htmlspecialchars(stsl($sender)).'"').'</div>';

// textarea
	$o .= '<textarea style="border-color:#6a6a6a !important; border-width:1px;" rows="12" cols="40" name="mailform">';
	if ($mailform != 'true') $o .= htmlspecialchars(stsl($mailform));
	$o .= '</textarea>';

// captcha
    if (trim($cf['mailform']['captcha']) == 'true')
    {
    	$o .= '<div class="captcha" style="margin: 10px 0;">'
           .  tag('input style="float:left" type="text" name="cap" class="captchainput"')
           .  '<div style="float:left; color: #fff; text-decoration: none; background-color: #000; padding: 2px 5px; margin: 0px 4px;">'
           .  $random . '</div><div style="float:left; margin-left:5px; margin-top:2px;">'
           .  $tx['mailform']['captcha'] .'</div></div>';
    }

// sendbutton
	$o .= '<div style="clear:both; padding: 8px 0;">'
       .  tag('input type="submit" class="submit" value="'
       .  $tx['mailform']['sendbutton'] . '"') . '</div>';
	$o .= '</form>';

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