<?php if (!defined('PmWiki')) exit();
/*  Copyright 2009 Randy Brown based on Patrick R. Michaud's wsplus recipe 
   This file is callout.php; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version. 

*/

SDV($RecipeInfo['Callout']['Version'], '2010-01-02');
$FmtPV['$CalloutVersion'] = "'{$RecipeInfo["Callout"]["Version"]}'";

SDV($CalloutUrl, 
  (substr(__FILE__, 0, strlen($FarmD)) == $FarmD) 
  ?  '$FarmPubDirUrl/callout' : '$PubDirUrl/callout');

##  Add in CSS styles.  
SDV($HTMLHeaderFmt['callout'], "
  <link rel='stylesheet' href='$CalloutUrl/callout.css' 
    type='text/css' />
");

SDVA($WikiStyle['attachment'], array('class'=>'round lrindent attachment'));
SDVA($WikiStyle['builtincallouts'], array('class'=>'round lrindent builtincallouts'));
SDVA($WikiStyle['callout01'], array('class'=>'round lrindent callout01'));
SDVA($WikiStyle['callout02'], array('class'=>'round lrindent callout02'));
SDVA($WikiStyle['callout03'], array('class'=>'round lrindent callout03'));
SDVA($WikiStyle['callout04'], array('class'=>'round lrindent callout04'));
SDVA($WikiStyle['callout05'], array('class'=>'round lrindent callout05'));
SDVA($WikiStyle['callout06'], array('class'=>'round lrindent callout06'));
SDVA($WikiStyle['callout07'], array('class'=>'round lrindent callout07'));
SDVA($WikiStyle['callout08'], array('class'=>'round lrindent callout08'));
SDVA($WikiStyle['callout09'], array('class'=>'round lrindent callout09'));
SDVA($WikiStyle['callout10'], array('class'=>'round lrindent callout10'));
SDVA($WikiStyle['checkblack'], array('class'=>'round lrindent checkblack'));
SDVA($WikiStyle['checkblue'], array('class'=>'round lrindent checkblue'));
SDVA($WikiStyle['checkgreen'], array('class'=>'round lrindent checkgreen'));
SDVA($WikiStyle['checkred'], array('class'=>'round lrindent checkred'));
SDVA($WikiStyle['checkyellow'], array('class'=>'round lrindent checkyellow'));
SDVA($WikiStyle['conflict'], array('class'=>'round lrindent conflict'));
SDVA($WikiStyle['copythis'], array('class'=>'round lrindent copythis'));
SDVA($WikiStyle['goal'], array('class'=>'round lrindent goal'));
SDVA($WikiStyle['important'], array('class'=>'round lrindent important'));
SDVA($WikiStyle['legal'], array('class'=>'round lrindent legal'));
SDVA($WikiStyle['money'], array('class'=>'round lrindent money'));
SDVA($WikiStyle['nutshell'], array('class'=>'round lrindent nutshell'));
SDVA($WikiStyle['pattern'], array('class'=>'round lrindent pattern'));
SDVA($WikiStyle['popout'], array('class'=>'round lrindent popout'));
SDVA($WikiStyle['query'], array('class'=>'round lrindent query'));
SDVA($WikiStyle['reality'], array('class'=>'round lrindent reality'));
SDVA($WikiStyle['resource'], array('class'=>'round lrindent resource'));
SDVA($WikiStyle['reminder'], array('class'=>'round lrindent reminder'));
SDVA($WikiStyle['rss'], array('class'=>'round lrindent rss'));
SDVA($WikiStyle['seeabove'], array('class'=>'round lrindent seeabove'));
SDVA($WikiStyle['seebelow'], array('class'=>'round lrindent seebelow'));
SDVA($WikiStyle['seeleft'], array('class'=>'round lrindent seeleft'));
SDVA($WikiStyle['seeright'], array('class'=>'round lrindent seeright'));
SDVA($WikiStyle['tip'], array('class'=>'round lrindent tip'));
SDVA($WikiStyle['thumbsdown'], array('class'=>'round lrindent thumbsdown'));
SDVA($WikiStyle['thumbsneutral'], array('class'=>'round lrindent thumbsneutral'));
SDVA($WikiStyle['thumbsup'], array('class'=>'round lrindent thumbsup'));
SDVA($WikiStyle['tool'], array('class'=>'round lrindent tool'));
SDVA($WikiStyle['uneven'], array('class'=>'round lrindent uneven'));
SDVA($WikiStyle['warning'], array('class'=>'round lrindent warning'));