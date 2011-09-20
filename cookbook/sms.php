<?php  if (!defined('PmWiki')) exit();
/**
 * sms.php - send a short message for debugging purposes
 *
 * @author Tamara Temple tamara@tamaratemple.com
 * @version $Id$
 * @copyright Tamara Temple Development, 13 June, 2011
 * @package debug
 **/

/**
 * sms - function to send a debug message to the error log and output
 *
 * @param string $msg - message to send
 * @param multi $var - variable to display
 * @param string $file - name of file where sms is called from, use __FILE__
 * @param string $line - line number where sms is called, use __LINE__
 * @return void
 * @author Tamara Temple
 **/
function sms($msg,$var='',$file='',$line='')
{
	global $MessagesFmt;
	$prefix = basename($file).'@'.$line.' ';
	error_log($prefix.$msg.PHP_EOL);
	$MessagesFmt[]= '<br />SMS Message: '.$prefix.$msg;
	if (!empty($var)) {
		$var = htmlentities(print_r($var,true));
		$MessagesFmt[] = "<pre>$var</pre>".PHP_EOL; # make output safe for browser
		error_log($prefix.$var.PHP_EOL);
	}
}
