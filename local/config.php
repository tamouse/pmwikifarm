<?php if (!defined('PmWiki')) exit();
##
##  config - common configuration file for wikifields
##
##  Created by Tamara Temple on 2011-09-20.
##  Copyright (c) 2011 Tamara Temple Web Development. All rights reserved.
##

## Unicode (UTF-8) allows the display of all languages and all alphabets.
## Highly recommended for new wikis.
include_once("$FarmD/scripts/xlpage-utf-8.php");

include_once("$FarmD/local/guibuttons.php");


$EnableWSPre = 1;   # lines beginning with space are preformatted (default)
$EnableUpload = 1;
$SpaceWikiWords = 1;                     # turn on WikiWord spacing
$EnableWikiWords = 1;                      # enable WikiWord links

##  The refcount.php script enables ?action=refcount, which helps to
##  find missing and orphaned pages.  See PmWiki.RefCount.
if ($action == 'refcount') include_once("$FarmD/scripts/refcount.php");

##  The feeds.php script enables ?action=rss, ?action=atom, ?action=rdf,
##  and ?action=dc, for generation of syndication feeds in various formats.
if (($action == 'rss') ||
	($action == 'atom') ||
	($action == 'dc') ||
	($action == 'rdf'))  include_once("$FarmD/scripts/feeds.php");

$AutoCreate['/^Category\\./'] = array('ctime' => $Now);

include_once("$FarmD/scripts/urlapprove.php");