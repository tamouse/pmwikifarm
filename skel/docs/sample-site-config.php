<?php if (!defined('PmWiki')) exit();
/**
 *
 * sample-site-config - sample site-wide configuration file for pmwiki farm
 *
 * @author Tamara Temple <tamara@tamaratemple.com>
 * @since 2011/10/21
 * @version Time-stamp: <2011-10-21 10:55:40 tamara>
 * @copyright (c) 2011 Tamara Temple Web Development
 * @license GPLv3
 *
 */

// This is a sample pmwiki farm site wide config.php file.  To use
// this file, copy it to $FarmD/local/config.php, then edit it for
// whatever customizations you want. Look in particular at
// <http://www.pmwiki.org/wiki/Cookbook/WikiFarms> for more
// information. This farm configuration is of a different type than
// standard, as it lets you keep all the field data separate from the
// web server path, and only the barest of things are in the web
// server path. Also, the web server paths do no need to be siblings
// as in the standard wiki farm implementation, they can be in
// completely separate directory paths.
//
// Also, be sure to take a look at
// <http://www.pmwiki.org/wiki/Cookbook> for more details on the types
// of customizations that can be added to PmWiki.

// Be careful about setting site-wide configurations for various
// things, as you don't necessarily want to impose restrictions on any
// given wiki field. Let the wiki field's owner decide how best to
// configure things.

// Look at pmwiki's distributed config sample in
// docs/sample-config.php for more ideas about what can be configured
// either globally or locally.


