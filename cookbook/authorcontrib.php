<?php if (!defined('PmWiki')) exit();
/*
   This script produces an "Author Contributions" page with a name
   of Profiles.<author's name>-Contrib for each author that has
   a page in the Profiles group.

   Contributions by authors without profile pages are placed in
   a single Profiles.Other-Contrib page.

   Find pages created by this script by searching for "name=*-Contrib"
   (without the quotes).
*/
SDV($RecipeInfo['AuthorContribution']['Version'], '2007-09-16');

if (!($action == 'edit' || $action == 'comment')) return;

## Configurable settings
SDV($AuthorContribAuthor,
  '* [[{$Group}.{$Name}]] ([[({$Group}.{$Name}?action=)diff]])'
  .'  . . . $CurrentTime - [=$ChangeSummary=]');
SDV($AuthorContribOther,
  '* [[{$Group}.{$Name}]] ([[({$Group}.{$Name}?action=)diff]])'
  .'  . . . $CurrentTime $[by] $AuthorLink: [=$ChangeSummary=]');
SDV($AuthorContribAuthorPage, '$AuthorPage-Contrib');
SDV($AuthorContribOtherPage, '$AuthorGroup.Other-Contrib');

@include_once("$FarmD/scripts/author.php");

if (PageExists($AuthorPage)) {
  $RecentChangesFmt[$AuthorContribAuthorPage] = $AuthorContribAuthor;
} else {
  $RecentChangesFmt[$AuthorContribOtherPage] = $AuthorContribOther;
}
