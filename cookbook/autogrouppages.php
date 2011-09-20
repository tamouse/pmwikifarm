<?php if (!defined('PmWiki')) exit();
##
##  autogrouppages - automatically populate a new group with default pages
##
##  Created by Tamara Temple on 2011-09-19.
##  Copyright (c) 2011 Tamara Temple Web Development. All rights reserved.
##

$TemplateGroup = 'GroupTemplates';

# This is all in service of getting ready to restructure my wiki by putting
# some things in different groups: computers, recipes, consulting, etc.,
# so I also want to prepare for that with the following recipe to set up
# new groups with a given set of predefined pages. For that I need the
# http://www.pmwiki.org/wiki/Cookbook/AutoGroupPages recipe as well.
function AutoGroupPages($pagename, &$page, &$new) {
  global $IsPagePosted, $GroupPagesFmt;
  if (!$IsPagePosted) return;
  SDV($AutoGroupPagesFmt, array(
				'{$Group}.HomePage' => $TemplateGroup.'.HomePage',
				'{$Group}.GroupHeader' => $TemplateGroup.'.GroupHeader',
				'{$Group}.GroupFooter' => $TemplateGroup.'.GroupFooter',
				'{$Group}.GroupAttributes' => $TemplateGroup.'.GroupAttributes',
				'{$Group}.NewPageTemplate' => $TemplateGroup.'.NewPageTemplate'));

  foreach($AutoGroupPagesFmt as $n => $t) {
    $n = FmtPageName($n, $pagename);
    $t = FmtPageName($t, $pagename);
    if (!PageExists($n) && $n != $pagename) {
      WritePage($n, ReadPage($t));
    }
  }
}

$EditFunctions[] = 'AutoGroupPages';
