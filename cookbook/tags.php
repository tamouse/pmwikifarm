<?php if (!defined('PmWiki')) exit();
/*  Copyright 2005 Michael  Vonrueden (mail@michael-vonrueden.de)
    This file is tags.php; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  

    This script enables tagged sites like in flickr. Insert tags into the wikis 
    with this markup:
    (:tags keyword, Keyword, etc. :)
    Retrieve all Tags with the markup
    (:listtags:)
    
    The function HandleTags will generate Temporary Sites in the style of 
    Tag.Keyword

    To use this script, simply copy it into the cookbook/ directory
    and add the following line to config.php (or a per-page/per-group
    customization file). include_once('cookbook/tags.php');

	Changes 

	Aug, 31th 2005 V1.0 Initial Development

*/

$HandleActions+=array('tags'=>"HandleTags");
$tags_prefix="Tags";
Markup("tags", "directives", '/\\(:tags\\s(.*?):\\)/ei', "Tagger('$1')");
Markup("listtags", "directives", '/\\(:listtags:\\)/ei', "ListTags()");



function Tagger($i) {
  global $action;
  $tags = explode(",",$i);
  $output ="<div class='tags'>";
  foreach ($tags as $tag)
  {
  	$tag=trim($tag);
  	$output=$output.'<a href="'.$ScriptUrl.'?action=tags&amp;tag='.$tag.'">'.$tag.'</a>, ';
  	}
 
  return $output."</div>";
}

function HandleTags()
{
	global $tags_prefix;	
	$taggedPages;
	$tag = $_GET["tag"];
	$pagelist = ListPages();
	foreach ($pagelist as $pagename)
	{
		$page=ReadPage($pagename, READPAGE_CURRENT);
		if (preg_match('/\\(:tags\\s.*?'.$tag.'.*?:\\)/i',$page['text'])) 
		{
			$name=explode(".",$page['name']);
			$taggedPages=$taggedPages.'*[['.$name[1].'->'.$pagename.']] ';
			$taggedPages=$taggedPages." \n";
		}
	}
	$text="Sites that are tagged with: @@".$tag."@@ \n\n";
	$page = array("text"=>$text.$taggedPages);
	$sitename=$tags_prefix.".".ucfirst(str_replace(" ","",$tag));
	WritePage($sitename,$page);
	Redirect($sitename);
	
	
	
}

function ListTags()
{
	$tags;
	$pagelist = ListPages();
	foreach ($pagelist as $pagename)
	{
		$page=ReadPage($pagename, READPAGE_CURRENT);
		$matched_tags=preg_match('/\\(:tags\\s(.*?):\\)/ei',$page['text'], $matches);
		{
			$rawtags= explode(",",substr($matches[0],6,-2));
			foreach($rawtags as $value)
			$tags[ucfirst(trim($value))]+=1;
			
		}
	}
	$output;
	ksort($tags); // sort the tags
	foreach ($tags as $tag=>$value)
	{
		if($tag!="0" && !empty($tag))
		$output=$output.'<span style="background-color:lightwhite;font-size:'.($value+10).'px;font-weight:'.($value+500).'">
						 <a href="'.$ScriptUrl.'?action=tags&amp;tag='.$tag.'">'.$tag.'</a></span> &nbsp;&nbsp;';
	}
	
	return $output;
}
