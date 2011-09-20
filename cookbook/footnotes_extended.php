<?php if (!defined('PmWiki')) exit();

/*
  
  FootnotesExtended (version 1.1) - another footnotes markup for pmwiki. 
 
  Copyright 2007 Thomas Pitschel, pmwiki (at) sigproc (dot) de

  This is a footnote markup that allows the footnote text to be included 
  later in the wiki source instead of right at the place where the anchor 
  occurs. This is practical for large footnote texts or multiple references 
  to one note. 

  Example usage:

    In 1951 a small core[^#countries^] of the countries of today's European 
    Union decided to found the Montanunion, which gave them each other 
    access to their iron and coal markets without incurring customs.

    [^#countries France, Netherlands, Belgium, Germany, Italy.^]

    ----
    [^#^]

  With the markup [^#myanchor^] an anchor is placed in the text pointing to 
  a text which is defined with [^#myanchor My text comes here.^] and which 
  is later displayed as part of the list generated by [^#^]. As anchor name 
  every string not containing a space will be acceptable.

  Linkage of both footnote and reference of this note is similar to wikipedia's.

  An inline footnote is still possible by including [^#foo My text^] (defining 
  an anchor "foo") or [^My text.^] (without defining an anchor) directly in the
  text.

  For latex compatibility, also %[^#^] instead of [^#^] can be used. (The
  \footnote{...} would have to be defined elsewhere, e.g. Cookbook.LinuxTex, 
  or as below (commented).)

  Jul 2007, ThP

  Changelog: 1.0 - initial release
             1.1 - added mod to correct the wrong display of quotes as escaped
                   double quotes

*/

define(FOOTNOTES_EXTENDED, '1.0'); // major version count only

$RecipeInfo['FootnotesExtended']['Version'] = '2007-09-24';

Markup('%[^#^]','directives','/\\%\\[\\^#\\^\\]/e',"FTNlatexAssembledFootnotes()");
// latex compatible
Markup('[^#^]','>%[^#^]','/\\[\\^#\\^\\]/e',"FTNassembledFootnotes()"); 
Markup('[^#ref','inline','/\\[\\^#([^\ ]+?)\\^\\]/e',"FTNfootnoteReference('$1')"); 
Markup('[^#defsWA','>[^#ref','/\\[\\^#([^\ ]+?) (.*?)\\^\\]/e',"FTNfootnoteText('$1', '$2')"); 
Markup('[^defsWo','>[^#defsWA','/\\[\\^([^#].*?)\\^\\]/e',"FTNfootnoteText('', '$1')"); 
//Markup('\footnote','inline','/\\\\footnote\\{(.*?)\\}/e',"FTNfootnoteText('', '$1')"); 

$FTNrefCounter = 1;
$FTNrefAnchorCounterMapping = array();

function FTNfootnoteReference($refAnchor) {
  global $FTNrefCounter, $FTNrefAnchorCounterMapping;

  if (!isset($FTNrefAnchorCounterMapping[$refAnchor])) {
    $displayedAnchor = $FTNrefCounter;
    $FTNrefAnchorCounterMapping[$refAnchor] = $FTNrefCounter;
    $FTNrefCounter += 1;
  } else {
    $displayedAnchor = $FTNrefAnchorCounterMapping[$refAnchor];
  }
    
  return Keep("<sup id='_ref-$displayedAnchor'><small><a href='#_note-$displayedAnchor'>$displayedAnchor</a></small></sup>");
}

function FTNfootnoteText($refAnchor, $footnoteText) {
  global $FTNrefCounter, $FTNrefAnchorCounterMapping, $FTNfootnoteTexts;

  $footnoteText = str_replace("\\\"", "\"", $footnoteText);

  if ($refAnchor == '')
    $refAnchor = "xxxxxxxxx$FTNrefCounter"; // internal use only
 
  $FTNfootnoteTexts[$refAnchor] = $footnoteText;
  
  if (!isset($FTNrefAnchorCounterMapping[$refAnchor])) { 
    return FTNfootnoteReference($refAnchor);
  }

  return '';
}

function FTNassembledFootnotes() {
  global $FTNrefAnchorCounterMapping, $FTNfootnoteTexts;

  $res = array();

/*
  $res[] = "<ol class='references'>";
  foreach($FTNrefAnchorCounterMapping as $anchor => $count) {
    if (!isset($FTNfootnoteTexts[$anchor])) {
      $text = "Warning: Footnote '$anchor' referenced but not defined.";
    } else {
      $text = $FTNfootnoteTexts[$anchor];
    }
    $res[] = "<li id='_note-$count'><a href='#_ref-$count'>^</a> $text</li>";
  }
  $res[] = "</ol>";
*/
  foreach($FTNrefAnchorCounterMapping as $anchor => $count) {
    if (!isset($FTNfootnoteTexts[$anchor])) {
      $text = "Warning: Footnote '$anchor' referenced but not defined.";
    } else {
      $text = $FTNfootnoteTexts[$anchor];
    }
    $res[] = "<sup id='_note-$count'><a href='#_ref-$count'>$count</a></sup> <small>$text</small> <p>";
    //$res[] = "<a id='_note-$count' href='#_ref-$count'>$count</a>. $text";
  }

/*
  $res=array();
  foreach($FTNfootnoteTexts as $anchor => $text) {
    $res[] = "$anchor => $text <p>";
  }
*/

  return Keep(implode("\n", $res));
}

function FTNlatexAssembledFootnotes() {
  return Keep("<hr>\n") . FTNassembledFootnotes();
}

