<?php
/**
 * Returns a customized language menu (flags, shortname (2 characters eg. "EN"), longname, a combination of these or dropdownmenu (dd_...)
 */
function languagemenu_custom()
{
	global $pth, $cf, $sl, $tpl_tx; // expanded with $tpl_tx

	$r = XH_secondLanguages();
	array_unshift($r, $cf['language']['default']);
	$i = array_search($sl, $r);
	unset($r[$i]);

	$langNames = explode(';', $cf['language']['2nd_lang_names']);
	foreach ($langNames as $value) {
		$langName[substr($value, 0, 2)] = substr($value, 3);
	}

	$t = '';
	$t_longname = '';
	$t_flagsandlongname = '';
	foreach ($r as $lang) {
		$url = $pth['folder']['base']
			. ($lang == $cf['language']['default'] ? '' : $lang . '/');
		$img = $pth['folder']['templateflags'] . $lang . '.gif';
		if (!file_exists($img)) {
			$img = $pth['folder']['flags'] . $lang . '.gif';
		}

		$title = isset($langName[$lang])
			? $langName[$lang]
			: $lang;
		
		$el = file_exists($img)
			? '<img src="' . $img . '" alt="' . $title . '" title="'
				. $title . '" class="flag">'
			: $title;
		
		$el_short_long = file_exists($img)
			? '<img src="' . $img . '" alt="' . $title . '">'
			: $title;
		
		if (isset($cf['language']['menu'])
		&& $cf['language']['menu'] == 'flags'
		) {
		$t .= '<a href="' . $url . '">' . $el . '</a>';
		} elseif (isset($cf['language']['menu'])
		&& $cf['language']['menu'] == 'shortname'
		) {
		$t .= '<a class="shortname"href="' . $url . '" title="'. $title . '"><span>' . $lang . '</span></a>';
		} elseif (isset($cf['language']['menu'])
		&& $cf['language']['menu'] == 'longname'
		) {
		$t .= '<a class="longname" href="' . $url . '"><span>' . $title . '</span></a>';
		} elseif (isset($cf['language']['menu'])
		&& $cf['language']['menu'] == 'dd_longname'
		) {
		$t_longname .= '<a class="longname" href="' . $url . '"><span>' . $title . '</span></a>';
		$t = '<details><summary>' . $tpl_tx['text']['language_select'] . '</summary><div class="dd">' . $t_longname . '</div></details>';
		} elseif (isset($cf['language']['menu'])
		&& $cf['language']['menu'] == 'flagsandshortname'
		) {
		$t .= '<a class="flagsandshortname" href="' . $url . '" title="'. $title . '">' . $el_short_long . '<span>' . $lang . '</span></a>';
		} elseif (isset($cf['language']['menu'])
		&& $cf['language']['menu'] == 'flagsandlongname'
		) {
		$t .= '<a class="flagsandlongname" href="' . $url . '">' . $el_short_long . '<span>' . $title . '</span></a>';
		} elseif (isset($cf['language']['menu'])
		&& $cf['language']['menu'] == 'dd_flagsandlongname'
		) {
		$t_flagsandlongname .= '<a class="flagsandlongname" href="' . $url . '">' . $el_short_long . '<span>' . $title . '</span></a>';
		$t = '<details><summary>' . $tpl_tx['text']['language_select'] . '</summary><div class="dd">' . $t_flagsandlongname . '</div></details>';
		}
		
	}
	return $t;
}
?>
