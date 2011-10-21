<?php if (!defined('PmWiki')) exit();
/**
 *  sample-local-config
 *
 * @author Tamara Temple <tamara@tamaratemple.com>
 * @since 2011/10/21
 * @version Time-stamp: <2011-10-21 12:40:21 tamara>
 * @copyright (c) Tamara Temple Web Development
 * @license GPLv3
 *
 */

/**
 * newwiki.sh makes a copy of this file in the field's local
 * directory, substituting values where appropriate.
 */

include_once("$FarmD/local/config.php"); // site-wide configuration settings

/**
 * The following variables should be set after running newwiki.sh and
 * answering the prompts correctly.
 */

$WikiFieldName = '@WIKIFIELDNAME@';
$WikiTitle = '@WIKITITLE@';
$Skin = '@SKIN@';

include_once("$FarmD/setworkdir.php");	// determine working paths

/**
 * Further local configuration should be in the following file, which
 * is copied from the $FarmD/docs/sample-config.php file.
 */
include_once("local-config.php");