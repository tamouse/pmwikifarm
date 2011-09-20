<?php if (!defined('PmWiki')) exit();
##
##  skin - php functions for pmwiki skin myskin
##
##  Created by Tamara Temple on 2011-09-18.
##  Copyright (c) 2011 Tamara Temple Web Development.
##  License: GPLv3
##

/**
 * Define the HTML Title in a way that can be suppressed with (:notitle:)
 * as in http://www.pmwiki.org/wiki/Cookbook/SkinGuidelines, section Page Title
 */
global $HTMLTitle, $WikiTitle;
$HTMLTitle = $WikiTitle.' &raquo; '.PageVar($pagename, '$Titlespaced');

## Markup (:notitle:)
Markup('notitle','directives','/\\(:notitle:\\)/e', 
  "NoTitle2(\$pagename)");
function NoTitle2($pagename) {
  global  $HTMLTitle, $WikiTitle;
  SetTmplDisplay('PageTitleFmt', 0);
  $HTMLTitle = $WikiTitle;
}

/**
 * Define the skin's css stylesheet
 */
global $HTMLHeaderFmt;
$HTMLHeaderFmt['skin'] =
  "  <link rel='stylesheet' href='\$SkinDirUrl/skin.css' type='text/css' />\n  ";

/**
 * Set the page logo for this site
 */
global $PageLogoUrl;
## $PageLogoUrl is the URL for a logo image
$PageLogoUrl = "$SkinDirUrl/fractalbanner2.png";

/**
 * Set up to have a default group header and footer. See http://www.pmwiki.org/wiki/Cookbook/AllGroupHeader
 */
global $GroupHeaderFmt, $GroupFooterFmt;
$GroupHeaderFmt =
  '(:include {$Group}.GroupHeader {$SiteGroup}.SiteHeader:)(:nl:)';
$GroupFooterFmt =
  '(:include {$Group}.GroupFooter {$SiteGroup}.SiteFooter:)(:nl:)';