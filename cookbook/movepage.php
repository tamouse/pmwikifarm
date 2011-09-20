<?php if (!defined('PmWiki')) exit();

/*	=== MovePage ===
 *	Copyright 2009 Eemeli Aro <eemeli@gmail.com>
 *
 *	Move and copy wiki pages
 *
 *	Developed and tested using PmWiki 2.2.x
 *
 *	To use, add the following to a configuration file:

  		if (($action=='copy') || ($action=='move')) include_once("$FarmD/cookbook/movepage.php");

 *	For more information, please see the online documentation at
 *		http://www.pmwiki.org/wiki/Cookbook/MovePage
 *
 *	This program is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 2 of the License, or
 *	(at your option) any later version.
 */

$RecipeInfo['MovePage']['Version'] = '2009-08-17';

SDVA($HandleActions, array('copy' => 'HandleMovePage', 'move' => 'HandleMovePage'));
SDVA($HandleAuth, array('copy' => 'edit', 'move' => 'edit'));

SDVA($ActionTitleFmt, array('copy' => '| $[Copy/move]', 'move' => '| $[Copy/move]'));

function MovePage($pagename, &$tgtname, &$page, $auth) {
	global $action, $FmtPV, $ChangeSummary, $Now, $MovePageFmt;

	if (preg_match('/[\\x80-\\xbf]/', $tgtname)) $tgtname = utf8_decode($tgtname);
	$tgtname = MakePageName($pagename, $tgtname);
	if (empty($tgtname)) return 'invalid target';
	$FmtPV['$MoveTargetName'] = "'$tgtname'";
	if (PageExists($tgtname)) return "target page ($tgtname) exists";

	$tgt = RetrieveAuthPage($tgtname, $auth, FALSE);
	if (!$tgt) return "cannot read target location ($tgtname); insufficient authority?";

	if (!empty($_REQUEST['copy'])) $mp_action = 'copy';
	else if (!empty($_REQUEST['move'])) $mp_action = 'move';
	else $mp_action = $action;

	$new = $page;
	$new['csum'] = $ChangeSummary = FmtPageName($MovePageFmt["$mp_action-csum"], $pagename);
	if ($ChangeSummary) $new["csum:$Now"] = $ChangeSummary;
	if (!UpdatePage($tgtname, $page, $new))
		return "error writing page ($tgtname)";

	if ($mp_action=='move') {
		$new = $page;
		$new['text'] = FmtPageName($MovePageFmt['old-text'], $pagename);
		$new['csum'] = $ChangeSummary = FmtPageName($MovePageFmt['old-csum'], $pagename);
		if ($ChangeSummary) $new["csum:$Now"] = $ChangeSummary;
		if (!UpdatePage($pagename, $page, $new))
			return "target ($tgtname) written ok, error writing to source page ($pagename)";
	}

	return FALSE;
}

function HandleMovePage($pagename, $auth='edit') {
	global
		$PageStartFmt, $PageEndFmt, $ActionTitleFmt, $action, $FmtPV,
		$Now, $ChangeSummary, $MessagesFmt, $MovePageFmt;
	if (isset($_REQUEST['cancel'])) Redirect($pagename);
	Lock(2);
	if (!PageExists($pagename)) Abort("MovePage: source page ($pagename) doesn't exist");
	$page = RetrieveAuthPage($pagename, $auth, TRUE);
	if (!$page) Abort("MovePage: cannot read source ($pagename)");

	SDVA($MovePageFmt, array(
		'copy-csum' => 'Page copied to {$MoveTargetName} from {$FullName}',
		'move-csum' => 'Page moved to {$MoveTargetName} from {$FullName}',
		'old-csum' => 'Page moved to {$MoveTargetName}',
		'old-text' => '(:redirect {$MoveTargetName}:)',
		'form' => array("<div id='wikimove'>
			<h2 class='wikiaction'>$[Copy/move] {\$FullName} $[to:]</h2>
			<form method='post' rel='nofollow' action='\$PageUrl?action=move'>
			<input type='hidden' name='n' value='\$FullName' />
			<input type='text' name='to' value='\$MoveTargetName' />
			<input type='submit' name='copy' value='$[Copy]' />
			<input type='submit' name='move' value='$[Move]' />
			<input type='submit' name='cancel' value='$[Cancel]' />",
			'markup:(:messages:)',
			"\n</form></div>"),
		'print' => array(&$PageStartFmt, &$MovePageFmt['form'], &$PageEndFmt)
	));
	$FmtPV['$MoveTargetName'] = "'$pagename'";

	if (empty($_POST['to'])) {
		Lock(0);
		PrintFmt($pagename, $MovePageFmt['print']);
		return;
	}

	$tgtname = $_POST['to'];
	$status = MovePage($pagename, $tgtname, $page, $auth);
	Lock(0);
	if ($status) {
		$MessagesFmt[] = "<div class='wikimessage'>MovePage: $status</div>";
		PrintFmt($pagename, $MovePageFmt['print']);
	} else
		Redirect($tgtname);
}

