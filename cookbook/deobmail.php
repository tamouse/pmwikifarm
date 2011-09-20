<?php if (!defined('PmWiki')) exit();
/**
	E-mail de-obfuscator for PmWiki
	Written by (c) Petko Yotov 2009

	This script is POSTCARDWARE, if you like it or use it,
	please send me a postcard. Details at
	http://galleries.accent-bg.com/Cookbook/Postcard

	This text is written for PmWiki; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published
	by the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version. See pmwiki.php for full details
	and lack of warranty.

	Copyright 2009 Petko Yotov http://5ko.fr
*/
# Version date
$RecipeInfo['DeObMail']['Version'] = '20090824';

$LinkFunctions['mailto:'] = 'DeObfuscateLinkIMap';
$IMapLinkFmt['mailto:'] = "<span class='_deob' title=\"\$LinkAlt\"><span class='_t'>\$LinkText</span> -&gt; <span class='_m'>\$LinkUrl</span></span>";

SDVA($DeObClass, array('.' => '_d', '@' => '_a'));
SDVA($DeObMail, array(
	'.' => ' [period] ',
	'@' => ' [snail] ',
	'javascript'=> <<<EOF
<script type="text/javascript"><!--
/*
  DeObMail() e-mail de-obfuscator
  Created for PmWiki in 2009 by Petko Yotov http://5ko.fr
*/

function DeObMail()
{
	var tags=document.getElementsByTagName("span");
	for(var i=0; i<tags.length; i++)
	{
		if(tags[i].className!='_deob') continue;

		var spans = tags[i].getElementsByTagName("span");
		var url='';
		var txt='';
		
		for(var j =0; j<spans.length; j++)
		{
			if(spans[j].className=='_t')
				txt = DeobMailFix(spans[j].innerHTML);
			else if(spans[j].className=='_m')
				url = DeobMailFix(spans[j].innerHTML);
		}
		var html = "<a href='"+url+"' class='mail'>"+txt+"</a>";
		tags[i].innerHTML = html;
	}
}
function DeobMailFix(t)
{
	t = t.replace( /<span class=(['"]?)_d\\1>[^<]+<\\/span>/ig, '.');
	t = t.replace( /<span class=(['"]?)_a\\1>[^<]+<\\/span>/ig, '@');
	return t;
}
DeObMail();
//--></script>
EOF
));
function DeObfuscateLinkIMap($pagename,$imap,$path,$title,$txt,$fmt=NULL)
{
	global $FmtV, $IMap, $IMapLinkFmt, $DeObMail;
	$FmtV['$LinkUrl'] = obfuscate_email(PUE(str_replace('$1',$path,$IMap[$imap])));
	$FmtV['$LinkText'] = obfuscate_email( preg_replace('/^mailto:/i', '', $txt));
	$FmtV['$LinkAlt'] = str_replace(array('"',"'"),array('&#34;','&#39;'),obfuscate_email($title, 0));
	return str_replace(array_keys($FmtV),array_values($FmtV), $IMapLinkFmt['mailto:']);
}
function obfuscate_email($x, $wrap=1)
{
	global $DeObMail, $DeObClass, $DeObCustom, $HTMLFooterFmt;
	if(isset($DeObCustom) )
		$x = str_replace(array_keys($DeObCustom), array_values($DeObCustom), $x );
	foreach($DeObClass as $k=>$v)
		$x = preg_replace("/(\\w)".preg_quote($k)."(\\w)/",
		($wrap?
				"$1<span class='$v'>{$DeObMail[$k]}</span>$2"
				: "$1{$DeObMail[$k]}$2")
				, $x);
	$HTMLFooterFmt['DeObMail'] = $DeObMail['javascript'];
	return $x;
}
