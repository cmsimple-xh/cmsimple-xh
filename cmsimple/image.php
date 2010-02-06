<?php
/*
======================================
CMSimple version 3.3 - December 31. 2009
Small - simple - smart
© 1999-2009 Peter Andreas Harteg - peter@harteg.dk

This file is part of CMSimple
For licence see notice in /cmsimple/cms.php and http://www.cmsimple.org/?Licence
======================================
*/
ob_start();
header('Content-Type: image/gif');
header('Content-Disposition: inline; filename='.$image.'.gif');
header("Last-Modified: " . gmdate("D, d M Y H:i:s", mktime (0, 0, 0, 1, 1, 2000)) . " GMT");
header("Expires: Mon, 26 Jul 2040 05:00:00 GMT");
header("Cache-Control: max-age=10000000, s-maxage=1000000, proxy-revalidate, must-revalidate");
echo hex2bin(image($image));
header('Content-Length: '.ob_get_length());
ob_end_flush();
exit;

function hex2bin($s) {
	$bin = '';
	for($i = 0; $i < strlen($s); $i += 2) {
		$bin .= chr(hexdec(substr($s, $i, 2)));
	}
	 return $bin;
}

function image($image) {
	global $cf;
	if ($image == 'doc' || $image == 'docs' || $image == 'sdoc' || $image == 'sdocs') {
		$tmp = '4749463839610c000f00800000';
		if ($image == 'sdoc' || $image == 'sdocs') $tmp .= $cf['menu']['highlightcolor'];
		else $tmp .= $cf['menu']['color'];
		$tmp .= 'ffffff21f90401000001002c000000000c000f000002';
		if ($image == 'doc' || $image == 'sdoc')return $tmp.'1e8c8f07cbac69c083328626eb85dbe20e225ad6809a382523e670ee0b1f05003b';
		else return $tmp.'208c8f06cb7c90d48bc1c176ab8ef9369d7160f57594297ad8b2b508a4ac2c9514003b';
	} else {
		$tmp = '47494638396117001600b30000000000800000008000808000000080800080008080c0c0c0808080ff000000ff00ffff000000ffff00ff00ffffffffff21f90401000007002c00000000170016000004';
		if ($image == 'bold')return $tmp.'51f0c949ab7de09dcdbbf748f68d63a891e866a6e9ca926e07cc34508a5e5d7f3137cb36100e48149e80badbd1a70b1a3f3f266f28ad767a9b66f44acd36952f5837ac1a93b1e48309c16ebbdf70ad7c1e01003b';
		if ($image == 'copy')return $tmp.'69f0c949ab7de09dcdbbf748f68d63a86d40aa929cc96553ca1e2e4a65005b1f384efc4090c8462114099d5d2f659c18911b65c5987a00a15215a0398d0e79538cd6ea3df1b4db0bf6fba18ed7e6b6baccbadae9331d3b2f8cf3937b7f65088485868788688a8b8a1100003b';
		if ($image == 'createlink')return $tmp.'8df0c949ab7de09dcdbbf748f68d63a87548aa92878922062700a4bb219a26ec7329b60886864164ec101f5b9043d8197ba0df922720189ab4e829c8385005c42c4a5a7cee0c0c31474928521904a496032802ee00025e6ddbe46939221a627d7e077b8887373f741821742659858a887b8a2d8c740781399798271e96966b992c3e0f2aa9aaab2a95aeaf781100003b';
		if ($image == 'cut')return $tmp.'60f0c949ab7de09dcdbbf748f68d63a88d00977e26b91e6fd7a26a29a229107333bdf337dc6fd3a3918a1e0280700c7e988428cbc98942374c19f590bd7241dbee770c3c75c4dcacd67c5637d9a4777c7e40d2d788bc7ecfefebfe808182801100003b';
		if ($image == 'h1')return $tmp.'57f0c949ab7de09dcdbbf748f68d63a891e866a6e97a001fccc99c4bcf1ba083e21b77b783ed87ebd874c85db1d60bfa96aa2631c73b396fc1a1075ba56e815d16c925fe90cbc61e3a7d5a331f88b87c4eaf27eff8bc2e02003b';
		if ($image == 'h2')return $tmp.'54f0c949ab7de09dcdbbf748f68d63a891e866a6e97a001fbc0174e7ca9d8ce3773c733cd1cbe7099e684864d126c40173a02671183d129d9cde925afd6d934616ca25fe90cbcc133abdae22def0b87c0eaedbeb11003b';
		if ($image == 'h3')return $tmp.'59f0c949ab7de09dcdbbf748f68d63a891e866a6e97a001fbc0174e7ca9d8ce377ecaba2d72fc7b9d18e354e1278c229873d8f73560c4e85d26af3b7d352b3b3e5c1c52a05cb1f32da765e7bddec07624eafdbefc8bc7e4f8b00003b';
		if ($image == 'h4')return $tmp.'58f0c949ab7de09dcdbbf748f68d63a891e866a6e97a001fecc9874b73f74bdb7107efa2576f23039e00c8249288e3f0663aa5ece91bd68239215485b536b9472f13cc42b9ca9f33baa35e93ddec0c624eafdbefd2bc3e1f01003b';
		if ($image == 'h5')return $tmp.'57f0c949ab7de09dcdbbf748f68d63a891e866a6e97a001ffc02b47cb83627e71c1e6f3c95e8f5237e70b524b0d6f17982366707da1b529fd5d3d516b516673917ab341c1fcbe6263a2d3cb1b388b87c4eaf27eff83c2d02003b';
		if ($image == 'h6')return $tmp.'5af0c949ab7de09dcdbbf748f68d63a891e866a6e97a001fbc01b47cb83627e71c1ecf25d1eb37fce06ac859ade3f3ec743d21af68b3353b4fa04a4aac464fd32a8d2964054f6663394d46b3bf6f500641afdbef78a47ecfa74500003b';
		if ($image == 'html')return $tmp.'5af0c949ab7de09dcdbbf748f68d63a891e866a6e9ca922bb0c9407dc8f3cdc5b97ed3b49d0847cc05718758ada83b0a354c66f3e98b7282aa614f8a4d6aabbd26cef5fa90cb9d333a7b5a53dd6fb8f781a8dbeff8fc72cfeff723003b';
		if ($image == 'iimage')return $tmp.'60f0c949ab7de09dcdbbf748b601646996a07800c3d3beae0ba4da8a9d25468f4366b1b34ea896c1e5829c212f067b2137ca95f1b4fb583fd1abf690dd5aa3539c50e51d8d6be5d5394de592bd28e81bbe4e63e776f703c1effbff806182271100003b';
		if ($image == 'ilink')return $tmp.'60f0c949ab7de09dcdbbf748d601247094e6176a9ba961efa98a326acbddda622107ac298eae662be18434d94ba40922592d23ea731852af4facb69adcce58c5b0d3ea1d77b766a8f6c655af49596f2b77966fc8f67b06c1effbff806282611100003b';
		if ($image == 'indent')return $tmp.'58f0c949ab7de09dcdbbf748f68d63a89100b9996a7bb01c20a7732dafa24bc21bd1d935dca9e7db0067422261a97be588311be879281aa51d5e334b0d1e6fcee196a3f5a676d4715875468bd55a780641afdbeff8affe1801003b';
		if ($image == 'insertorderedlist')return $tmp.'52f0c949ab7de09dcdbbf748f68d63a891e86676440a8a1bd16e406ddbdccacdae0a1f329e4bd7fb106545df6947bbdd723f60f27044168953e5cedac3f29c4f2d2b599d62b3677306c16ebbdf70b07c6e8b00003b';
		if ($image == 'insertunorderedlist')return $tmp.'50f0c949ab7de09dcdbbf748f68d63a891e86672449b76ebd6121b60df37171ff3ab8bbe1230081af682bb236ef93bf15cc821b1392d3a8fbe247489a3caa0596975573d90c719847acd6ebbb9f0f82d02003b';
		if ($image == 'italic')return $tmp.'4cf0c949ab7de09dcdbbf748f68d63a891e866a607009462eac2274bdbe43abaf34df6bedd2b17fb00839ee347971ca2981de5b2b8e1f19e541c07aa5565bb5c7006412e9bcf68ab7acd764500003b';
		if ($image == 'justifycenter')return $tmp.'4bf0c949ab7de09dcdbbf748f68d63a891e866a6e9ba01702ccb9ccb966237cf607e7fb69d1050f3fd3ab6d79088341e55be250f7a7a16abca610f6b4d769d4f6f3883289bcfe8b4741d8b00003b';
		if ($image == 'justifyleft')return $tmp.'4af0c949ab7de09dcdbbf748f68d63a891e866a6e9ba01702ccb9ccb96e23bd39f7d83b9c36e08a8057f1d1b11863b2193c1e5cce87caaa2cba6155ab5fabc47f003412e9bcf68a93a1601003b';
		if ($image == 'justifyright')return $tmp.'4af0c949ab7de09dcdbbf748f68d63a891e866a6e9ba01702ccb9ccb96e237cf757e7fb69d10d03bfd40bece10563c027d4b9e2ae93cd894cb6655bbb5529dd7aa09412e9bcfe8a83a1601003b';
		if ($image == 'layout')return $tmp.'67f0c949ab7de09dcdbbf748c6011e401e27ba85da96aa6a7ab26377def041bb36efafa21fca24ebec72b022e7f8c20999269f12d8fa5841419793448ceab2382792c913bfc8b1df195c561bd9e9dcba1a762fb3c351f35dbdfa590881828384855d878889261100003b';
		if ($image == 'outdent')return $tmp.'58f0c949ab7de09dcdbbf748f68d63a89100b9996a7bb01c20a7732dafa24bc21bd1d935dce940f0c580b757aec8d42987c59f0d94eb49831d9e335b3d049149ed5678a5a576ddf15375460fd56bb81041afdbef78b01e1901003b';
		if ($image == 'p')return $tmp.'55f0c949ab7de09dcdbbf748f68d63a891e866a6e9fa01700c83a2276f405973727ef8b413eff6d309714460c7d5491a4731dc93e45372982fe9076bd30651d529d4bbdcb1b6e673f9a8be6610f0b87c4eefd9eff608003b';
		if ($image == 'paste')return $tmp.'7ef0c949ab7de09dcdbbf748f68d63a88d400a9066a7a6cbf2ae5cbb01c810ca80ac0fb483ed00d00539c5504d74cbcd54cee589f8cb59abc121ae4ae87a095926f54aa874a5c82ba24c291336daeac0dd7d78e1e2ad956dc64f933f745e767e2e2f7c6d85245f8c8a248f426290259293201908999a9b9c9d4f9fa02a1100003b';
		if ($image == 'save')return $tmp.'68f0c949ab7de09dcdbbf748f68d63a86d40aaae2b6772c00090f3bcbda84ca7ae98db9f18107710d274375f111964128d23e8e13990b152d5a13256ed7ab3bdd3d27a9552c9aa5f189665a99362eef53d5516cb6e3869bfe6ef897e1f26088485868788788a651100003b';
		if ($image == 'selectall')return $tmp.'67f0c949ab7de09dcdbbf748f68d63a801c081aadbeaae262ba73347c774deee3c9ebaad9f6d1363e96a3d91917794f954bf2834a9215989ca0e4a28eb14b5cb2ee73be4d2bc591b508cad82cd1ff20e0e4acfa72bb4fb5ab2f3f50881828384855b878889891100003b';
		if ($image == 'space')return '47494638396103001600800000000000c0c0c021f90401000001002c00000000030016000002068c8fa9cbed5f003b';
		if ($image == 'underline')return $tmp.'5af0c949ab7de09dcdbbf748f68d63a891e866a6e9ca926e07ccc041d75d2ce336cfe99c59d0a7127d841b64cee8512a7fccddf0034cf29ec5531380388488dee88e5612bfa0dae18dbc4c9fd16faaf96d42d8eff8bc7ecdeff323003b';
		if ($image == 'unlink')return $tmp.'96f0c949ab7de09dcdbbf7487624c967925ba89125e2be1d9b8a2389189c006cf2acf1248d60a8ebf952081663c910188f0704a39618329e50e9c14922b076209a74ca55823baa14b3c93230ce9c7494c06475111ff901b0fc7e01707a7b2c1a0f847b71344009808d5f88518a8400211c93508f8d9a8f912b28888522869f699f1b9a7b8e259d26ad610f2fb1b2b32f9ab6b7b8801100003b';
		if ($image == 'redo')return $tmp.'54f0c949ab7de09dcdbbf748f68d63a891e866a6e9ca92eefbc52a6113adc8213867fb1d5acf83fbed749b21d1a8421e94be5bd0c95b4a8f271f42469361bd3327f8d00597bd676e06c16ebbdf70807c4eafdbe711003b';
		if ($image == 'undo')return $tmp.'56f0c949ab7de09dcdbbf748f68d63a891e866a6e9ca92eefbc523611308287204dff3390ecd76f8798c8721b1d6512e8fcddde6f69c0655d21632999509bb5eee29fc1d93c5e7721afb40b8dff0b81c40afdbeff87a04003b';
		if ($image == 'inserthorizontalrule')return $tmp.'55f0c949ab7de09dcdbbf748f68d63a891e86676480a8aead1ca486dcf2a4ce3ee1acf37dbebe42ae98a430e60c96c3639be8373ca841e9156a20c2bd3b580c11acb2b4646bfaa70923bd6b2a3ecee234caf53eff808003b';
		if ($image == 'removeformat')return $tmp.'68f0c949ab7de09dcdbbf748f68d63a891e866a6e98a86408c209c3b028776e2aa78e7c047a8e6f3e07439c0b077ea1c83ca19b1b979eaa23ca633989c65b53da3f763e32195df29277d88916cb098ec556491ebf68e2dafc7f30f263382838485717288898a721100003b';
	}
}
?>