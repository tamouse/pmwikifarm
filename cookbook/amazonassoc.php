<?php if (!defined('PmWiki')) exit ();

/*  copyright 2007 Benoit Dutilleul (benoit.dutilleul@gmail.com) 
    This file is distributed under the terms of the GNU General Public 
    License as published by the Free Software Foundation; either 
    version 2 of the License, or (at your option) any later version.  

	Modified 2011-06-26 Tamara Temple (tamara@tamaratemple.com) to
	include more markup for showing the product image only.

    This module enables embedding of Amazon.com widgets into pmwiki pages. 

	Usage:
		(:amazonpl ASSOCID ASIN:)
		(:amazonplimg ASSOCID ASIN:)
		
	Where:
		ASSOCID = Amazon Associates ID
		ASIN = Amazon ASIN
*/

#Markup('amazonpl', '<img', '/\\(:amazonpl (\\d+) (\\d+):\\)/e', #"ShowAmazonProductLink('$1','$2')");

Markup('amazonplimg', '<img', '/\\(:amazonplimg (.*?) (.*?):\\)/e', "ShowAmazonProductLinkImageOnly('$1','$2')");
Markup('amazonpl', '<img', '/\\(:amazonpl (.*?) (.*?):\\)/e', "ShowAmazonProductLink('$1','$2')");

function ShowAmazonProductLinkImageOnly($assoid,$asin) {
		$out = '<a class="external" href="http://www.amazon.com/gp/product/1580081304/ref=as_li_ss_il?ie=UTF8&tag='.$assoid;
		$out .= '&linkCode=as2&camp=217145&creative=399377&creativeASIN='.$asin;
		$out .= '"><img border="0"';
		$out .= 'src="http://ws.assoc-amazon.com/widgets/q?_encoding=UTF8&Format=_SL110_&ASIN='.$asin;
		$out .= '&MarketPlace=US&ID=AsinImage&WS=1&tag='.$assoid;
		$out .= '&ServiceVersion=20070822" ></a>';
		$out .= '<img src="http://www.assoc-amazon.com/e/ir?t='.$assoid;
		$out .= '&l=as2&o=1&a='.$asin;
		$out .= '&camp=217145&creative=399377" width="1" height="1" border="0" alt="" style="border:none !important; margin:0px !important;" />';
        return Keep($out);
}

function ShowAmazonProductLink($assoid,$asin)
{
	$out = '<iframe src="http://rcm.amazon.com/e/cm?lt1=_blank&bc1=000000&IS2=1&bg1=FFFFFF&fc1=000000&lc1=0000FF&t='.$assoid;
	$out .= '&o=1&p=8&l=as4&m=amazon&f=ifr&ref=ss_til&asins='.$asin;
	$out .= '" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>';
	return Keep($out);
	
}