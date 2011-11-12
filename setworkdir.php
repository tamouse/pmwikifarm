<?php if (!defined('PmWiki')) exit();
/**
 * setworkdir - set the working directory for each wikifield
 *
 * @author Tamara Temple <tamara@tamaratemple.com>
 * @created 2011-09-20
 * @version Time-stamp: <2011-11-12 07:17:50 tamara>
 * @copyright (c) 2011 Tamara Temple Web Development.
 * @license GPLv3
 */

if (!isset($WikiFieldName)) {
    echo "NOT SET UP CORRECTLY!!!\n";
    echo "FarmD=[$FarmD]\n";
    echo "WikiFieldName=[$WikiFieldName]\n";
    exit();
  }

/**
 * $FarmD is set in the index.php file in each wiki field's document root 
 *
 * $WikiFieldName is set in each field's local/config.php before this
 * script is called. 
 */

$FieldD = "$FarmD/var/$WikiFieldName";
$WorkDir = "$FieldD/wiki.d/";
$LastModFile = "$WorkDir.lastmod";
$WikiDir = new PageStore("$WorkDir\$FullName", 1);
$WikiLibDirs =
  array(
	&$WikiDir,
	new PageStore("$FarmD/pmwiki-latest/wikilib.d/\$FullName")
	);
