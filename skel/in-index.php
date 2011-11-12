<?php
/**
 * index - local starting page for wikifield wikis
 *
 * @author Tamara Temple tamara@tamaratemple.com
 * @created 2011-09-20
 * @version Time-stamp: <2011-11-12 04:52:16 tamara>
 * @copyright 2011 Tamara Temple Web Development
 * @license GPLv3
 **/

/**
 * The index file is really the only file that needs to be in the
 * local wiki's server-accessible space. All other files and such are
 * stored in the $FarmD/var/$WikiFieldName folder. The $FarmD global
 * must be set here, though, in order for the index.php page to be
 * able to find the pmwiki.php script. When creating a new field, the
 * index.php file in the new wiki field can be symlinked to this file
 * in the skeleton.
 */



/**
 * This must be configured once per wiki farm installation. If you've
 * run the install.sh script in the wiki farm installation, this will
 * be set for you.
 */
$FarmD = "@PATHTOWIKIFARM@";

/**
 * Start pmwiki
 */
require_once("$FarmD/pmwiki-latest/pmwiki.php");
