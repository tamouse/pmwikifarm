<?php if (!defined('PmWiki')) exit();
/**
 * config - local configuration file for a wikifield
 *
 * @author Tamara Temple <tamara@tamaratemple.com>
 * @created 2011-09-20
 * @version Time-stamp: <2011-10-21 08:43:56 tamara>
 * @copyright 2011 Tamara Temple Web Development
 * @license GPLv3
 */

/**
 * Set local wiki values
 *
 * These are replaced when the newwiki.sh script is run
 */
$WikiFieldName = '@WIKIFIELDNAME@';
$WikiTitle = '@WIKITITLE@';
$Skin = '@SKIN@';

include_once("$FarmD/local/setworkdir.php"); 	// determine working paths (uses $WikiFieldName)
include_once("$FarmD/local/config.php");	// site-wide configuration settings
include_once("$FieldD/local/pw.php");		// local passwords kept separate local config file

/**
 * If you want to use WikiFieldName and/or WikiTitle in your wiki
 * pages, then uncomment the following two lines.
 *
 * note: see
 * <http://www.pmwiki.org/wiki/PmWiki/OtherVariables#FmtPV> for an
 * explanation about setting FmtPV values. Also, check out
 * <http://www.pmwiki.org/wiki/Cookbook/MoreCustomPageVariables> for 
 * some more nifty page variables you can set.
 */
// $FmtPV['WikiFieldName'] = '$GLOBALS["WikiFieldName"]';
// $FmtPV['WikiTitle'] = '$GLOBALS["WikiTitle"]';
