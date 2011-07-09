<?php
/* utf8-marker = äöüß */
/*
CMSimple_XH 1.4.1
2011-01-18
For changelog, downloads and information please see http://www.cmsimple-xh.com
======================================
-- COPYRIGHT INFORMATION START --
based on CMSimple version 3.3 - December 31. 2009
Small - simple - smart
@ 1999-2009 Peter Andreas Harteg - peter@harteg.dk
-- COPYRIGHT INFORMATION END --

This file is part of CMSimple
For licence see notice in /cmsimple/cms.php and http://www.cmsimple.org/?Licence
======================================
History:
2010-06-12  Bob for XH 1.2 : Mail header subject localized
2009-09-18  GE for CMSimple_XH
2008-11-19  JB for 32SE added captcha, senders phone and name
*/

if (preg_match('/mailform.php/i',sv('PHP_SELF')))die('Access Denied');

$title = $tx['title'][$f];
$o .= '<h1>'.$title.'</h1>';
initvar('sendername');
initvar('senderphone');
initvar('sender');
initvar('getlast');
initvar('cap');
initvar('mailform');

$t = '';

if ($action == 'send') {
    $msg = ($tx['mailform']['sendername'] . ": " . stsl($sendername) . "\n" . $tx['mailform']['senderphone'] . ": " . stsl($senderphone) . "\n\n" . stsl($mailform));

// echo ($msg);
    if ($getlast != $cap) {
        $e .= '<li style="font-size: 118%;">' . $tx['mailform']['notaccepted'];
    } else if ($mailform == '') {
        $e .= '<li>' . $tx['error']['mustwritemes'];
    } else if (!(preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[_a-z0-9-]+(\.[_a-z0-9-]+)*(\.([a-z]{2,4}))+$/i", $sender))
        )$e .= '<li>' . $tx['mailform']['notaccepted'];
    else if (!(@mail_utf8($cf['mailform']['email'], $tx['menu']['mailform'] . ' ' . sv('SERVER_NAME'), $msg, "From: " . stsl($sender) . "\r\n" . "X-Remote: " . sv('REMOTE_ADDR') . "\r\n"))) {
        $e .= '<li style="font-size: 118%;">' . $tx['mailform']['notsend'];
    }
    else
        $t = '<p style="font-size: 118%; text-align: center;">' . $tx['mailform']['send'] . '</p>';
}

if ($t == '' || $e != '') {
//	if (@$tx['mailform']['message'] != '')$o .= '<p>'.$tx['mailform']['message'].'</p>';

// JB+ add captcha
	srand((double)microtime()*1000000);
	$random=rand(10000,99999);

	$o .= '<form action="'.$sn.'" method="post">';

	$o .=tag('input type="hidden" name="function" value="mailform"')
	.tag('input type="hidden" name="getlast" value="'.$random.'"')
	.tag('input type="hidden" name="action" value="send"')

// fields before textarea 
	.'<div style="width:200px; margin: 0 0 8px 0;">'.$tx['mailform']['sendername'].': '
	.tag('input type="text" class="text" size="35" name="sendername" value="'.htmlspecialchars(stripslashes($sendername)).'"')
	.'</div>'
	.'<div style="width:200px; margin: 0 0 8px 0;">'.$tx['mailform']['senderphone'].': '
	.tag('input type="text" class="text"  size="35"name="senderphone" value="'.htmlspecialchars(stripslashes($senderphone)).'"').'</div>'

	.'<div style="width:200px; margin: 0 0 20px 0;">'.$tx['mailform']['sender'].': '.tag('input type="text" class="text"  size="35" name="sender" value="'.htmlspecialchars(stripslashes($sender)).'"').'</div>';

// textarea
	$o.='<textarea style="border-color:#6a6a6a !important; border-width:1px;" rows="12" cols="40" name="mailform">';
	if ($mailform != 'true')$o .= htmlspecialchars(stsl($mailform));
	$o .= '</textarea>';

// captcha
	$o.='<div class="captcha" style="margin: 10px 0;">'.tag('input style="float:left" type="text" name="cap" class="captchainput" maxlength="5"').'<div style="float:left; color: #FFFFFF; text-decoration: none; background-color: #000000; padding: 0 5px; margin: 0px 4px;">
	'.$random.'</div><div style="float:left; margin-left:5px; margin-top:2px;">'. $tx['mailform']['captcha'] .'</div></div>';

// sendbutton
	$o.='<div style="clear:both; padding: 8px 0;">'.tag('input type="submit" class="submit" value="'.$tx['mailform']['sendbutton'].'"').'</div>';
	$o.='</form>';

}
else $o .= $t;

function mail_utf8($to, $subject = '(No Subject)', $message = '', $header = '') {
    $header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
    if(mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header)) {
        return true;
    }
    return false;
}
?>