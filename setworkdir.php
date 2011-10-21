<?php if (!defined('PmWiki') || !isset($FarmD) ||
!isset($WikiFieldName)) exit();
/**
 * setworkdir - set the working directory for each wikifield
 *
 * @author Tamara Temple <tamara@tamaratemple.com>
 * @created 2011-09-20
 * @version Time-stamp: <2011-10-21 12:51:22 tamara>
 * @copyright (c) 2011 Tamara Temple Web Development.
 * @license GPLv3
 */

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
	new PageStore("$FarmD/wikilib.d/\$FullName")
	);
