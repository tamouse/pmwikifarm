<?php if (!defined('PmWiki')) exit();
##
##  setworkdir - set the working directory for each wikifield
##
##  Created by Tamara Temple on 2011-09-20.
##  Copyright (c) 2011 Tamara Temple Web Development. All rights reserved.
##

# $WikiFieldName is set in each field's local/config.php before this script is called.

$WorkDir = "$FarmD/var/$WikiFieldName/wiki.d/";
$LastModFile = "$WorkDir.lastmod";
$WikiDir = new PageStore("$WorkDir\$FullName", 1);
$WikiLibDirs = array(
	&$WikiDir,
	new PageStore("$FarmD/wikilib.d/\$FullName")
	);
