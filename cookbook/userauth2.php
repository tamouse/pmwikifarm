<?php if (!defined('PmWiki')) exit();

/*
  UserAuth2 (Version 2.2.3) - A user-based authorization and authentication module.

  Copyright 2007+ Thomas Pitschel, pmwiki (at) sigproc (dot) de
 
  (Files UserSessionVars.php and userauth2-admintool.php adapted from userauth recipe 
   by James McDuffie et al., see www.pmwiki.org/wiki/Cookbook/UserAuth)

  This is a authentication module for the pmwiki engine that supports user-based
  permission granting via a typical web interface and passwords/cookie based
  authentication. Main features are:

   * definition of user groups
   * ip address based silent permission granting
   * setting login restrictions based on ip address
   * implicit capability of delegating permission granting (via hierarchically structuring
     the users)
   * caching of all permission queries 
   * cookie based authentication   
   * LDAP support
   * transparent functioning with SSL-based securing of the wiki using the 
     http://www.pmwiki.org/wiki/Cookbook/SwitchToSSLMode recipe

  Detailed instructions on installation and usage can be found at 
  www.pmwiki.org/wiki/Cookbook/UserAuth2

  Bug reports and similar development related suggestions or questions should go to
  www.pmwiki.org/wiki/Cookbook/UserAuth2Devel, unless otherwise said at Cookbook.UserAuth2.

  ThomasP, March 2007.

  Note that if the line after this paragraph does not contain a comment "// COMMENTED VERSION"
  you have got the version of the script where the one-line comments and some debugging code 
  have been stripped. For debugging or if you intend to modify the code for public, get a full 
  version from www.pmwiki.org/wiki/Cookbook/UserAuth2.
  // COMMENTED VERSION

  -- License --

  GNU General Public License. 

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

*/

define(USERAUTH_VERSION, '2.2');  // compatibility
define(USER_AUTH_VERSION, '2.2'); // compatibility
define(USERAUTH2_VERSION, '2.2');

$RecipeInfo['UserAuth2']['Version'] = '2010-03-03';
$RecipeInfo['UserAuth2']['Author'] = 'ThomasP';

/*
  List of the main public functions to be called by application code:

  function UserAuth($pagename, $level, $authprompt=true) // for backward compatibility
  function UserAuth2($pagename, $level, $authprompt=true)
  function TryAccessingPage($pagename, $level)

  // The following ones are almost independent of the session/request state (up to username and ip)
  // and are thus outsourced into userauth2/userauth2-permchecklib.php.

  function HasCurrentUserPerm($page, $level)
  function HasCurrentUserPermForAction($page, $action) // wrapper employing $HandleAuth to resolve action to level
  function CheckUserPerms($user, $page, $level, $groupaction = false)
  function CheckUserPermsForAction($user, $page, $action, $groupaction = false) // again wrapper of same kind
  function mayCurrAdminActOnPermHolder($action, $admin_action, $tool_username, $groupaction = false)
*/

//==================================================
//======== Default/initial settings ================

SDV($UA2SessionSavePathDir,      "cookbook/userauth2/session_data"); 
SDV($UA2EnforceFixedClientIp,    true);     // Ensures that over the course of a session the client ip must not change
SDV($UA2SiteIdentifier,          __FILE__); // If deploying MANY sites on ONE server sharing ONE COMMON session 
                                            // save path and all using THIS auth module, this should be set to different 
                                            // values, otherwise cross-use of authentication status could be possible.
                                            // Using the file location as characteristic should be enough since different 
                                            // pmwikis on one server usually dont share the cookbook dir.
SDV($UA2SessionMaxInactivityTime, 2*60*60); // In seconds, default 2 hours.
SDV($UA2SessionMaxLifeTime,      24*60*60); // In seconds, default 1 day. 
  // Both limits are enforced by this script, no PHP interna are relied upon. However, make sure that
  // session.gc_maxlifetime and session.cache_expire are honoured, see #013, otherwise your session may die earlier.
  // See comments at http://hk2.php.net/manual/en/ref.session.php for details.
SDV($UA2AllowPostCompletion,     true);     // if enabled, posts that hit a session expiration are completed after relogin
                                            // (note this drills a small hole into the session expiration principle)
SDV($UA2EnableBruteForceProtect, true);     // if enabled, rejects login attempts after incurring a certain number
                                            // of failed logins attempts; see userauth2-bruteforce.php for more.
SDV($FailedLoginsLogDir,          "cookbook/userauth2/failed_login_attempts");
SDV($FailedLoginsLimitUser,       100);
SDV($FailedLoginsTimeframeUser,   30*86400); // in secs; default: 30 days
SDV($FailedLoginsLimitIp,         100);
SDV($FailedLoginsTimeframeIp,     30*86400);
SDV($LoginAttemptLimitReachedFmt, "$[The maximum number of failed login attempts has been reached. Please contact the site admin if you would like to continue.]");

SDV($UA2AfterSILoginRedirectTo,  ''); // default '' = previous content page
SDV($UA2AfterLogoutRedirectTo,   $LoginPage);
SDV($UA2AdminLoginFromIpsOnly,   ''); // default '' = no restriction

SDV($UA2_CheckIpRangeUponLogin,  true);
SDV($UA2AllowCookieLogin,        false);
SDV($UA2CookiePrefix,            ''); 
SDV($UA2CookieExpireTime,        60*60*24*30); // cookie default expiration in seconds
SDV($UA2LoginLockMsgFmt,         ''); // If set to a non-empty string, the module will block any login attempts
                                      // and display the string as message instead. For system maintainance

SDV($UA2AllowMultipleGranters,   false); // note that the disabling is honoured only on the UI level
SDV($UA2PermEditLockTimeout,     60*60*12); // time after which an edit lock on permission tables
                                            // is released forcefully when requested by someone else, 12 hours
SDV($UA2PermUpdateTimestampFile, "cookbook/userauth2/lastPermUpdateTimestamp"); 
  // contains the unix time the permissions were changed the last time

//>>>>>>>>>>> Here come the options that are relevant to the (deep core) perm checking mechanism...

SDV($UA2EnablePermCaching,       true); // might be useful to set to false during debugging, otherwise you can 
SDV($UA2MaxPermRecordCacheSize,  20);   // think long about why your code changes have no effect
SDV($UA2MaxPermQueryCacheSize,   100);

SDV($UA2UserPermDir,             "cookbook/userauth2/userperms"); 
SDV($UA2GroupPermDir,            "cookbook/userauth2/groupperms"); 
SDV($UA2ProfileDir,              "cookbook/userauth2/profiles");
SDV($UA2IpRangesDir,             "cookbook/userauth2/ipranges");  

SDV($GuestUsergrp,               "GuestUsers");
SDV($LoggedInUsergrp,            "LoggedInUsers");
SDV($UA2LoggedInUsersReplacements, 'replaceAuthIdInRecord'); // must be empty string or function (&$permrecord, $username)

SDV($LoginPage,                  "Site.Login");    // These two variables must use dots even when 
SDV($HomePage,                   "Main.HomePage"); // using enabled path info (CleanUrls).
   $LoginPage = str_replace('/', '.', $LoginPage); // This line might be removed in later versions.

// add some userauth specific pmwiki actions and levels

#SDVA($HandleAuth,               ...); // directly in userauth2-permchecklib.php
#SDVA($UA2AlwaysAllowedLevels,   ...); // dto
#SDVA($UA2LevelToAbbr,           ...); // dto
#SDVA($UA2PageRelatedLevelAbbr,  ...); // dto

//<<<<<<<<<<< ... and here is their end.

// Note the difference between (pmwiki) actions and userauth2 specific admin actions 
// (which are all subsumed under pmwiki action 'admin').
SDVA($AdminActionsArr, array(
 // admin_action
  'report',
  'adduser',
  'addgroup',
  'edituser',
  'editgroup',
  'deluser',
  'delgroup',
  'setipperms'
));

SDV($TempUnavailFmt,              "$[Server temporarily unavailable.]");
SDV($AuthRequiredFmt,             "$[Authentication required.]");
SDV($LackProperAbilitiesFmt,      "$[Insufficient privileges to perform action.]");
SDV($NoUsernameFmt,               "$[No username was provided.]");
SDV($InvalidUsernameFmt,          "$[Invalid username was provided.]");
SDV($WrongUserFmt,                "$[Wrong password supplied.]"); // hide reason
SDV($WrongPasswordFmt,            "$[Wrong password supplied.]");
SDV($WrongIpRangeFmt,             "$[Wrong password supplied.]"); // hide reason
SDV($UnableToLoadPermRecordFmt,   $TempUnavailFmt);
SDV($UnableToLoadProfileFmt,      $TempUnavailFmt);
SDV($UnableToSetSessionCookieFmt, '$[Unable to set session cookie.]');
SDV($SessionInconsistencyFmt,     "$[Session client coupling inconsistent (IP address changed?). Stopping for security reasons.]");
SDV($UA2SessionExpired1Fmt,       "$[Session has expired due to maximal life time or inactivity. Please log in again.]");
SDV($UA2SessionExpired2Fmt,       "$[Session has expired. Please log in again to complete the request.]");

$HandleActions['logout']       = 'HandleLogout';
$HandleActions['login']        = 'HandleLogin';
$HandleActions['pwchange']     = 'HandlePasswordChange'; // function defined in userauth2-pwchange.php

SDVA($UA2AuthFunctions, array(
  'ldap' => 'ua2AuthUserLDAP',
));

function appendToUA2ErrorLog($msg) {
  global $UA2ErrorLogLastMsg, $UA2ErrorLog;

  if ($msg != $UA2ErrorLogLastMsg) { // includes ('' != false) yielding false
    $UA2ErrorLogLastMsg = $msg;
    $UA2ErrorLog .= $msg;
  }
}

function flushUA2ErrorLog() {
  global $UA2ErrorLog;

  if ((strlen($UA2ErrorLog) > 0) && function_exists(debugMsg))
    debugMsg('USAU', $UA2ErrorLog);

  $UA2ErrorLog = '';
  // UA2ErrorLogLastMsg can stay set
}

function getCurrClientIp() { return $_SERVER["REMOTE_ADDR"]; }
  // wrapped for testing and other purposes

include_once("userauth2/userauth2-permchecklib.php");

// point the pmwiki permission checking mechanism to us:
$DefaultAuthFunction = $AuthFunction;
$AuthFunction = "UserAuth2";

// This makes sure that we can browse multiple sites on one server with one browser:
// (since the browsers obviously not honour the path parameter in cookies, rather use this trick)
ini_set('session.name', 'PHPSESSID' . strtoupper(md5($UA2SiteIdentifier)));
ini_set('session.save_path', $UA2SessionSavePathDir);
mkdirp($UA2SessionSavePathDir); // making sure that dir exist

// #013: increase PHP-builtin session timeouts
ini_set('session.cache_expire', max($UA2SessionMaxLifeTime/60, ini_get('session.cache_expire')));
ini_set('session.gc_maxlifetime', max($UA2SessionMaxLifeTime, ini_get('session.gc_maxlifetime')));
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 1); // [no need to let PHP behave deterministic here - we do it ourself (see #014)]
                                  // do be deterministic, just to give better debugging conditions

session_start(); // Note that it seems to be necessary to call session_start globally and before any occurence of
                 // (or reference to?) the _SESSION array. (Unfortunately this was undocumented.)
if(!isset($UserInstanceVars)) {
  require_once("userauth2/UserSessionVars.php");
  $UserInstanceVars = new UserSessionVars();
  if (!$UA2EnablePermCaching)
    $UserInstanceVars->clearPermCache(); // remove remains in old sessions if we have just switched off caching
}

//foreach ($_POST as $key => $value) {
//  debugMsg('', "POST start: $key => $value");
//}
//foreach ($_SESSION as $key => $value) {
//  debugMsg('', "SESSION start: $key => $value");
//}

function ua2OnSessionHijack($msg) { // needed some lines below
  global $UserInstanceVars, $SessionInconsistencyFmt;

  appendToUA2ErrorLog($msg . "\n");
  flushUA2ErrorLog();
  // Do not destroy session here, to leave the rightful owner undisturbed!
  // Rather, just do an id regenerate (without session file deletion!) such that we can safely proceed
  // to the login page (where the session with the then current id gets killed -- which is then not bad):
  session_regenerate_id(); // in PHP5 this would be session_regenerate_id(false);
  $UserInstanceVars->ClearInstanceVariables(); 
    // reset auth status for the new session (and no data carry-over!); this possibilizes also ip update later
  RedirectToLoginPage($SessionInconsistencyFmt);
}

// check whether client has ip address as memorized on session creation, to prevent malicious reuse/handover:
if (!isset($_SESSION['remote_addr'])) 
  $_SESSION['remote_addr'] =  getCurrClientIp();
else
  if ($UA2EnforceFixedClientIp && (getCurrClientIp() != $_SESSION['remote_addr'])) {
    ua2OnSessionHijack("Warning: Client ip mismatched memorized ip. Enforced session id renewal and auth reset.");
  }

// Check whether this session indeed belongs to our site: (important if we run this script on many sites of
// the same server, see www.php.net "session", comment gordon_e_rouse at ... dot com dot au, 29-Mar-2007 11:06.)
if (!isset($_SESSION['site_identifier']))
  $_SESSION['site_identifier'] = $UA2SiteIdentifier; 
else
  if ($UA2SiteIdentifier != $_SESSION['site_identifier']) {
    ua2OnSessionHijack("Warning: Site id mismatched memorized id. Enforced session id renewal and auth reset.");
  }

// do session expiration by hand since the underlying implementation won't produce deterministic behaviour(*): #014
// ((*) Reason: www.php.net "session" shanemayer42 at ... dot com 20-Aug-2000 10:11)
$ua2Now = time();
if ((isset($_SESSION['lastrevivaltime']) &&  // inactivity time check
     ($ua2Now > $_SESSION['lastrevivaltime'] + $UA2SessionMaxInactivityTime)
    ) || 
    (isset($_SESSION['firststarttime']) &&   // max life time check
     ($ua2Now > $_SESSION['firststarttime'] + $UA2SessionMaxLifeTime)
    )
   ) 
{ 
  if ($UserInstanceVars->isAuthenticated()) {
    session_destroy(); session_start(); // (execute this only here, otherwise wouldn't know the login status anymore)
    $UserInstanceVars->setTargetUrl(getPageRequestUrl());
    $UserInstanceVars->setAuthMsg($UA2SessionExpired1Fmt);
    if (@$_POST['action'] && $UA2AllowPostCompletion) {
      $UserInstanceVars->setAuthMsg($UA2SessionExpired2Fmt);
      foreach($_POST as $k => $v) $_SESSION['POST_data'][$k] = $v;
    }
  } else {
    session_destroy(); session_start(); 
  }
}
// update times:
$_SESSION['lastrevivaltime'] = $ua2Now;
if (!isset($_SESSION['firststarttime'])) $_SESSION['firststarttime'] = $ua2Now;

// Revive possible posted data: since on session expiration we wander through a chain of 
// different page requests and redirections, use some conditions to make sure we revive the
// posted data only when we are sure we are at our aim.
if (isset($_SESSION['POST_data']) &&
    isContentPage($pagename) && ($action == @$_SESSION['POST_data']['action']) &&
    $UserInstanceVars->isAuthenticated()) {
  foreach($_SESSION['POST_data'] as $k => $v) $_POST[$k] = $v;
  unset($_SESSION['POST_data']);
}

// set some variables for pmwiki use:
$IsUserLoggedIn = $UserInstanceVars->isAuthenticated();
if ($UserInstanceVars->isAuthenticated()) {
  $AuthId = $UserInstanceVars->GetUsername();
  $Author = $UserInstanceVars->GetUsername();
    // It would be tempting to set $Author to the full name (meanwhile: description), but that 
    // may contain spaces etc. and could get bad beyond our userauth code.
}

//Redirect exit handler: 
if (!isset($exitHandler)) $exitHandler = '';
$UA2PreviousExitHandler = $exitHandler;
$exitHandler = "UA2myExitHandler";
  // Exit handler functionality will only show up sometimes unless all exits throughout pmwiki are
  // replaced by '{ global $exitHandler; $exitHandler(); }'. It is only needed for statistics though.

function UA2myExitHandler() {
  global $THP_SESSION;
  global $UA2PreviousExitHandler;
  global $UA2StatUncachedRecordLoadsCount, $UA2StatUncachedPermQueryCount;

  appendToUA2ErrorLog("Current cache utilization: " .
                      count($_SESSION['permqueries']) . " perm queries, " .
                      count($_SESSION['userpermrecords']) . " user recs, ".
                      count($_SESSION['grouppermrecords']) . " group recs, " .
                      count($_SESSION['iprangerecords']) . " ip range recs.\n");
  appendToUA2ErrorLog("In total " . $UA2StatUncachedRecordLoadsCount . " uncached perm record loads, " .
                      $UA2StatUncachedPermQueryCount . " uncached perm queries.\n");

  flushUA2ErrorLog();

  // call original exit function:
  if ((strlen($UA2PreviousExitHandler) > 0) && function_exists($UA2PreviousExitHandler))
    $UA2PreviousExitHandler();
  else
    exit;
}

function ua2AssemblePageToReturn($pagename) {
  $page = ReadPage($pagename);
  if (!$page) { return false; }
  if (HasCurrentUserPerm($pagename, 'read')) {
    $page['=auth']['read'] = 1;
  }
  $page['=passwd']['read'] = 1;
  return $page;
}

//================================================================
//======== Main entry point for pmwiki permission checking =======
//
// The process of accessing a page access is broken down into permission
// checking (functions that come later) and action taking like redirection
// etc. (done in the immediately following functions).

// First some channeling however for combatibility:
function UserAuth($pagename, $level, $authprompt=true) { 
  return UserAuth2($pagename, $level, $authprompt); 
}

function UserAuth2($pagename, $level, $authprompt=true) {
  // If called with $authprompt==true, the function is expected
  // to produce either the specified page to the client or 
  // print the login page. If called with $authprompt==false, the
  // caller just wants to know whether the current user has the
  // permission to access the specified page at the given level.
  // Still, the pmwiki engine demands the page being returned on 
  // success, since it needs the time stamps and other fields.

  // The functions below always expect $pagename to be normalized, i.e.
  // of form Group.Page, so do some replacement first:
  $pagename = str_replace('/', '.', $pagename);

  if ($authprompt)
    return TryAccessingPage($pagename, $level);
  else {
    if (HasCurrentUserPerm($pagename, $level)) {
      return ua2AssemblePageToReturn($pagename);
    }
  }
  return false;
}

function TryAccessingPage($pagename, $level) {
  // In this function the redirection logic is concentrated for the
  // cases of insufficient or wrong authentication. It returns
  // the desired page on success or appropriate auth dialogs otherwise.
  global $UserInstanceVars, $LackProperAbilitiesFmt, $AuthRequiredFmt;

  //appendToUA2ErrorLog("Someone trying to access page $pagename at level $level.\n");
  //appendToUA2ErrorLog("$pagename is a content page: " . answer(isContentPage($pagename)) . "\n");
  if (isContentPage($pagename)) {
    $UserInstanceVars->clearTargetUrl();
    // remember the (last step of the) trail of content pages the client is going: 
    $UserInstanceVars->setPrevContentPage($pagename);
  }
  if (HasCurrentUserPerm($pagename, $level)) {
    //appendToUA2ErrorLog("Access to $pagename at level $level granted.\n");
    // Return page:
    return ua2AssemblePageToReturn($pagename);
  } else {
    //appendToUA2ErrorLog("Access to $pagename at level $level NOT granted.\n");
    if ($UserInstanceVars->isAuthenticated()) {
      PrintEmbeddedPageAndExit( $pagename, $LackProperAbilitiesFmt );
    } else {
      // Set redirection memory:
      $UserInstanceVars->setTargetUrl(getPageRequestUrl());

      // Send to login page...
      if ($UserInstanceVars->isAuthMsgSet()) // ... with auth message if already present, ...
        RedirectToLoginPage($UserInstanceVars->getAuthMsg());
 
      // ... or with standard message
      RedirectToLoginPage($AuthRequiredFmt); // won't return
    }
  }
  return false; // never reached
}

function getPageRequestUrl() {
  return ua2GetProtocol() . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function ua2GetProtocol() {
  // Extra gimmick for choosing the right protocol for assembling the redirection url.
  // Is necessary when we start to handle SSL connections where not the whole site is
  // piped through the https protocol.

  // Use the protocol we are just using to access the wiki:
  if (@$_SERVER['HTTPS'] && ($_SERVER['HTTPS'] != 'off')) return 'https';
  return 'http';
}

//================================================================
//======== Permission checking ===================================
//
// Done in userauth2/userauth2-permchecklib.php

//================================================================
//======== Cookie key handling ===================================

function storeCookieKey($user, $cookiekey, $keycreatetime) {
  $profile = loadUserProfile($user);
  if (!$profile) return false; // fail silently if profile could not be loaded
  $profile['cookiekey'] = $cookiekey;
  $profile['cookiekeycreatetime'] = $keycreatetime;
  return saveUserProfile($user, $profile);
}

function getCookieKey($user, $max_age) { 
  // Returns false if the cookie key could not be loaded or has expired, otherwise
  // the cookie key.
  $profile = loadUserProfile($user);
  if (!$profile) return false; 
  if ($profile['cookiekeycreatetime'] + $max_age < time()) return false;
  return $profile['cookiekey'];
}

//==============================================================
//======== Output related functions ============================

function GetUserLoginForm($no_title = true) {
  global $pagename, 
         $AuthId,
         $WikiTitle,
         $UserInstanceVars,
         $UA2AllowCookieLogin, 
         $UA2CookieExpireTime;

  $login_form = '';

  if (!$UserInstanceVars->isAuthenticated()) {

    if (!$no_title) {
      $login_form .= "<h1>$[Login to] $WikiTitle</h1>";
    }

    if (strlen($UserInstanceVars->getAuthMsg()) > 0) {
      $login_form .= $UserInstanceVars->getAuthMsg() . "<br>&nbsp;<p>\n";
      $UserInstanceVars->setAuthMsg('');
    }
    
    $actionUrl = $_SERVER['REQUEST_URI'] . '?action=login';
    $actionUrl = str_replace("'", "", $actionUrl); // sanitize
    $login_form .=
      "<form name='authform' action='$actionUrl' method='post'>
       <table class='userauthtable' style='padding:0px; margin:0px'>

       <tr>
           <td>$[Username]:</td>
           <td><input tabindex='1' name='username' class='userauthinput' value='' /></td>
       </tr>

       <tr>
           <td>$[Password]:</td>
           <td><input tabindex='2' name='password' class='userauthinput' type='password' value='' /></td>
       </tr>";

    // setting table style paddings and margins to zero has the effect that the table will
    // actually be left-justified !!! (otherwise this is forgotten by the browser)

    if($UA2AllowCookieLogin) {
      $login_form .= "
       <tr height=40px>
           <td>$[Keep me logged in<br> on this browser]:</td>
           <td><input tabindex='3' name='persistent' class='userauthinput' type='checkbox' value='1' /> &nbsp; ($[for] " .
            $UA2CookieExpireTime/86400 . " $[days])</td>
       </tr>";
    }

    $login_form .= "
       <tr height=35px>
           <td align=left><input tabindex='3' class='userauthbutton' type='submit' value='$[Login]' /></td>
           <td>&nbsp;</td>
       </tr>
       </table>
       </form>
       <script language='javascript' type='text/javascript'><!--
          document.authform.username.focus() //--></script>";

  } else {
    $login_form =
      "$[Logged in as]: " . $AuthId . "<br><br>
       <a href=\"" . $_SERVER['REQUEST_URI'] . "?action=logout\">$[Logout]</a>";

  }

  return FmtPageName($login_form, $pagename);
}

function PrintEmbeddedPageAndExit($pagename, $message) {
  global $PageStartFmt, $PageEndFmt;

  $page = ReadPage($pagename);
  PCache($pagename, $page);

  $AuthEmbeddedFmt = array( &$PageStartFmt,
                            $message,
                            &$PageEndFmt);

  PrintFmt($pagename, $AuthEmbeddedFmt);
  global $exitHandler; $exitHandler();
}

//=============================================================
//======= LDAP Authentication =================================

// The following function was adapted from scripts/authuser.php AuthUserLDAP (Patrick Michaud).
// See http://httpd.apache.org/docs/2.0/mod/mod_auth_ldap.html (The Authentication Phase)
// and http://httpd.apache.org/docs/2.0/mod/mod_auth_ldap.html#authldapurl
// for documentation. (Following that authentication process.)
function ua2AuthUserLDAP($id, $pw, $AuthLDAPURL) {
  global $AuthLDAPBindDN, $AuthLDAPBindPassword;
  if (!$pw) return false;
  if (!function_exists('ldap_connect'))
    Abort('authuser: LDAP authentication requires PHP ldap functions','ldapfn');
  $ldap = $AuthLDAPURL;
  if (!preg_match('!(ldaps?://[^/]+)/(.*)$!', $ldap, $match)) return false;
  ##  connect to the LDAP server
  list($z, $url, $path) = $match;
  $conn = ldap_connect($url);
  ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
  ##  For Active Directory, don't specify a path and we simply
  ##  attempt to bind with the username and password directly
  if (!$path && @ldap_bind($conn, $id, $pw)) { ldap_close($conn); return true; }
  ##  Otherwise, we use Apache-style urls for LDAP authentication
  ##  Split the path into its search components, according to
  ##  ldap://host:port/basedn?attribute?scope?filter
  list($basedn, $attr, $scope, $filter) = explode('?', $path);
  if (!$attr) $attr = 'uid';
  if (!$scope) $scope = 'one';
  if (!$filter) $filter = '(objectClass=*)';
  $binddn = @$AuthLDAPBindDN;
  $bindpw = @$AuthLDAPBindPassword;
  if (ldap_bind($conn, $binddn, $bindpw)) {
    ##  Search for the appropriate uid
    $fn = ($scope == 'sub') ? 'ldap_search' : 'ldap_list';
    $sr = $fn($conn, $basedn, "(&$filter($attr=$id))", array($attr));
    $x = ldap_get_entries($conn, $sr);
    ##  If we find a unique id, bind to it for success
    if ($x['count'] == 1) {
      $dn = $x[0]['dn'];
      if (@ldap_bind($conn, $dn, $pw)) { ldap_close($conn); return true; }
    }
  }
  ldap_close($conn);
  return false;
}

//=============================================================
//======= Authentication handling =============================

function doPasswordsMatch($provided_passwd, $stored_passwd) {
  // checks whether the password given as input by the user ($provided_passwd)
  // matches the stored one.
  if (($provided_passwd === '') && ($stored_passwd === '')) return true; // empty password
  return crypt($provided_passwd, $stored_passwd) == $stored_passwd;
}

function checkPasswordForUser($alleged_user, $alleged_passwd) {
  // $alleged_user must be a valid user name string.
  // If the stored password is accessible, this function returns 'success' if
  // the passwords matched. If the stored password is not accessible
  // this function returns a corresponding error message.
  global $WrongPasswordFmt,
         $UnableToLoadProfileFmt,
	 $AuthUser, $UA2AuthFunctions;

  SDV($AuthUser, array());

  // Strategy: check first all means configured in AuthUser, and if nothing helps, fall back
  // to checking the password stored in the ua2 user profile.
  foreach(@$AuthUser as $method => $pmtrs) {
    $UA2AuthFunc = @$UA2AuthFunctions[$method];
    if (!$UA2AuthFunc || !function_exists($UA2AuthFunc)) continue;
    if ($UA2AuthFunc($alleged_user, $alleged_passwd, $pmtrs)) return 'success';
    if ($pmtrs['exclusive']) return $WrongPasswordFmt; // do not continue checking if the current method has exclusive flag
  }

  // Method "profile": if (AuthMethod == profile) { ...
  $alleged_profile = loadUserProfile($alleged_user);
  if (! $alleged_profile) {
    appendToUA2ErrorLog("Could not check password since unable to load profile for $alleged_user.\n");
    return $UnableToLoadProfileFmt;
  }

  // Add other authentication methods here: if (AuthMethod == whatever) { ...

  if (!doPasswordsMatch($alleged_passwd, $alleged_profile['password']))
    return $WrongPasswordFmt;

  return 'success';
}

function setPasswordForUser($user, $new_password) {
  global $ProblemsSavingSettingsFmt;

  // if method==profile
  $profile = loadUserProfile($user);
  if ($profile) { // profile will exist since we just checked the old password against it
    if (strlen($new_password) == 0)
      $profile['password'] = '';
    else 
      $profile['password'] = crypt($new_password);
    if (saveUserProfile($user, $profile))
      return 'success';
  }
  return $ProblemsSavingSettingsFmt;
}

function my_session_regenerate_id($carryOverData = false) {
  // Adapted from post on www.php.net session_regenerate_id() by Nicholas 03/06/2005.
  // Deletes old session file, thus making the old session id completely invalid.
  // Pass $carryOverData == true if you want to retain the data in the session. 
  session_start();
  $old_sessid = session_id();
  session_regenerate_id();
  $new_sessid = session_id();
  session_id($old_sessid);
  session_destroy();

  //If you don't copy the $_SESSION array, you won't be able to use the data associated with the old session id.
  $old_session = $_SESSION;
  session_id($new_sessid);
  session_start();
  if ($carryOverData)
    $_SESSION = $old_session;

  $ua2Now = time();
  $_SESSION['firststarttime'] = $ua2Now;
  $_SESSION['lastrevivaltime'] = $ua2Now;

  return true;
}

function HandleLogin($pagename) {
  global $THP_SESSION;
  global $UserInstanceVars, 
         $UA2LoginLockMsgFmt,
         $UA2EnableBruteForceProtect,
         $LoginAttemptLimitReachedFmt,
         $UA2_CheckIpRangeUponLogin,
         $UA2AllowCookieLogin,
         $UA2AdminLoginFromIpsOnly,
         $WrongIpRangeFmt,
         $WrongUserFmt,
         $UnableToLoadPermRecordFmt,
         $UnableToSetSessionCookieFmt;

  // all these redirects implicitly exit the php code
 
  if (strlen($UA2LoginLockMsgFmt) > 0)
    RedirectToLoginPage($UA2LoginLockMsgFmt);

  if (!isset($_REQUEST['username'])) 
    RedirectToLoginPage($NoUsernameFmt);

  $alleged_user    = $_REQUEST['username'];
  $provided_passwd = $_REQUEST['password'];
  //$THP_SESSION['alleged_username'] = $alleged_user; // might be useful to keep

  if (! isValidUserString($alleged_user))
    RedirectToLoginPage($InvalidUsernameFmt);

  if ($UA2EnableBruteForceProtect) {
    include_once("cookbook/userauth2/userauth2-bruteforce.php");
    if (!ua2MayAttemptToLogin($alleged_user, getCurrClientIp(), time())) {
      appendToUA2ErrorLog("Login attempt limit reached by " . getCurrClientIp() . " using username '$alleged_user'.\n");
      RedirectToLoginPage($LoginAttemptLimitReachedFmt, $alleged_user);
    }
  }

  appendToUA2ErrorLog("Handle login for alleged user '$alleged_user' from " . getCurrClientIp() . " ...\n");

  // In the following, everywhere RedirectToLoginPage carries a second argument, a failed login attempt 
  // is logged: if the user does not exist, if password wrong, or if login not allowed from the client's ip.

  if (! doesUserExist($alleged_user))
    RedirectToLoginPage($WrongUserFmt, $alleged_user);

  // user does indeed exists, so proceed to password checking etc.:

  // first check password 
  $check_res = checkPasswordForUser($alleged_user, $provided_passwd);
  if ($check_res != 'success') {
    RedirectToLoginPage($check_res, $alleged_user);
  }

  // second check ip range 
  if ($UA2_CheckIpRangeUponLogin) {
    if ($alleged_user != 'admin') { // using the perm record if not admin
      $alleged_permrecord = $UserInstanceVars->getPermHolderRecord($alleged_user, false, false, false);
      if (! $alleged_permrecord)
        RedirectToLoginPage($UnableToLoadPermRecordFmt);

      if (!empty($alleged_permrecord['loginFromIpsOnly'])) // check only if set and non-empty, see #100
        if (! ipMatchesIpRangeArr($alleged_permrecord['loginFromIpsOnly'], getCurrClientIp()))
          RedirectToLoginPage($WrongIpRangeFmt, $alleged_user);
    } else { // or using the special variable for admin
      if (strlen($UA2AdminLoginFromIpsOnly) > 0)
        if (! ipMatchesIpRangeArr($UA2AdminLoginFromIpsOnly, getCurrClientIp()))
          RedirectToLoginPage($WrongIpRangeFmt, $alleged_user);
    }
  }
  
  // check was successful, so try session regeneration:
  //old: if (thp_session_regenerate($THP_SESSION, true)) { // try to regenerate as authenticated
  if (my_session_regenerate_id(true)) { // generate new session id and destroy old one, but carry over data
    // if ok, then finish off
    $UserInstanceVars->SetUsername($alleged_user); // = "is authenticated"
    appendToUA2ErrorLog("User '$alleged_user' has logged in.\n");
    $UserInstanceVars->clearPermQueryCache(); // make really really sure we work with new permissions

    if($UA2AllowCookieLogin && @$_REQUEST['persistent']) {
      $UserInstanceVars->setAuthCookie();
      appendToUA2ErrorLog("Authentication cookie has been set for user $alleged_user.\n");
    } else
      $UserInstanceVars->clearAuthCookie();

    RedirectOnSuccessfulLogin();
  } else {
    RedirectToLoginPage($UnableToSetSessionCookieFmt);
  }
}

function RedirectOnSuccessfulLogin() {
  // This function is just to wrap the redirection operations
  global $UserInstanceVars, $UA2AfterSILoginRedirectTo, $HomePage;

  // if the user had some target in mind, then redirect to that now ("finally"):
  if (($target_url_loc = $UserInstanceVars->getTargetUrl())) { // this is an assignment
    $UserInstanceVars->clearTargetUrl();
    RedirectToURL($target_url_loc); // exits with calling the pmwiki exit handler
  }

  // otherwise redirect to the by-option specified page:
  if (strlen($UA2AfterSILoginRedirectTo) > 0) { 
    // do some possibly fancy redirect (depending on the user name)
    Redirect(str_replace('{$AuthId}', $UserInstanceVars->GetUsername(), 
                         $UA2AfterSILoginRedirectTo));
  } 

  if (($prev_contentpage = $UserInstanceVars->getPrevContentPage())) { // again
    Redirect($prev_contentpage); // exits with calling the pmwiki exit handler
  }

  // otherwise try the home page
  if (HasCurrentUserPerm($HomePage, 'read'))
    Redirect($HomePage); // exits with calling the pmwiki exit handler
  else 
    PrintEmbeddedPageAndExit('Please make sure at least the home page is readable for a logged ' . 
                             'in user, or set $UA2AfterSILoginRedirectTo appropriately.'); // needs no i18n
}

function HandleLogout($pagename) {
  global $THP_SESSION;
  global $UserInstanceVars, 
         $UA2AfterLogoutRedirectTo;

  appendToUA2ErrorLog("User '" . $UserInstanceVars->GetUsername() . "' has logged out.\n");

  // clear authentication cookie:
  $UserInstanceVars->clearAuthCookie();

  // clear UserAuth instance variables:
  $UserInstanceVars->ClearInstanceVariables();
  //my_session_regenerate_id(false); 
  session_destroy();
  //thp_session_destroy($THP_SESSION);

  // redirect to by-option specified page if available:
  if (strlen($UA2AfterLogoutRedirectTo) > 0) {
    Redirect($UA2AfterLogoutRedirectTo); // exits with calling the pmwiki exit handler
  }

  // otherwise go to login page:
  Redirect($LoginPage); // exits with calling the pmwiki exit handler
}

//=============================================================
//======= Redirection helpers =================================

function RedirectToLoginPage($msg, $logFailedLoginAttempt = false) {
  // If $logFailedLoginAttempt is not false, it is expected to be the username used for logging
  // in, indicating a failed login attempt which gets logged.

  global $THP_SESSION;  
  global $LoginPage, $UserInstanceVars, $UA2EnableBruteForceProtect; 

  if (($logFailedLoginAttempt !== false) && $UA2EnableBruteForceProtect)
    ua2LogFailedLoginAttempt($logFailedLoginAttempt, getCurrClientIp(), time());

  $UserInstanceVars->setAuthMsg($msg);
  Redirect($LoginPage); // exits with calling the pmwiki exit handler
}

function RedirectToURL($url) {
  global $RedirectDelay;
  clearstatcache();

  header("Location: $url");
  header("Content-type: text/html");
  print("<html><head>
    <meta http-equiv='Refresh' Content='$RedirectDelay; URL=$url'>
    <title>Redirect</title></head><body></body></html>");

  global $exitHandler; $exitHandler(); // like exit;
}

//=============================================================
//======= Markup related functions ============================

// conditional variables that can be used in a wiki page
$Conditions['loggedin']="\$GLOBALS['IsUserLoggedIn']";
$Conditions['memberOf']="IsCurrentUserGroupMember(\$condparm)";
$Conditions['member']="IsCurrentUserGroupMemberOld(\$condparm)"; // for backward compatibility
$Conditions['ipMatches'] = 'currClientIpMatchesRange($condparm)';
// The markup of the loginform has to come after UserInstanceVars has been instantiated, and all the current
// writes to SESSION['auth_message'] have taken place, since the GetUserLoginForm() call is already made 
// now, not as usual when assembling the wiki output.
// (The "if" makes sure that the (:loginform:) markup gets only interpreted for the designated login page;
//  the slashByDot replacement ensures this works for enabled path info (CleanUrls) also.)
if (str_replace('/', '.', $pagename) == $LoginPage) 
  Markup("loginform", "_end", "/\\(:loginform:\\)/", GetUserLoginForm());

//=============================================================
//======= Stuff================================================
function answer($x) { return ($x ? "yes" : "no"); }

function containsChar($needleChr, $haystack) {
  return strpos($haystack, $needleChr) !== false;
}

function isSecure($str) {
  if (containsChar('<', $str)) return false;
  if (containsChar('>', $str)) return false;
  if (containsChar('&', $str)) return false;
  return true;
}

//================================================================
//======== Operations executed effectively once after install ====

mkdirp($UA2UserPermDir); // making sure that dirs exist
mkdirp($UA2GroupPermDir);
mkdirp($UA2ProfileDir);
mkdirp($UA2IpRangesDir);
if (!loadUserProfile('admin')) { // making sure that admin profile exist
  $prof = newEmptyProfile();     // (will have an empty password)
  saveUserProfile('admin', $prof);
}
if (!loadUserPermRecord($GuestUsergrp, true)) { 
  $rec = newEmptyPermRecord('admin', true);
  $mynewpermtable = array();
  $mynewpermtable[] = 'rd_*.*';          // on initial start, have a read-all for guest users
  $mynewpermtable[] = '-rd_SiteAdmin.*'; // but take out SiteAdmin group
  $rec['perms']['admin'] = $mynewpermtable;
  saveUserPermRecord($GuestUsergrp, $rec, true);
}

//================================================================
//======== admin tool and passwd change inclusions ===============

if ($action == 'admin') // conditionally include scripts if needed
  require_once("userauth2/userauth2-admintool.php");
if ($action == 'pwchange') {
  require_once("userauth2/userauth2-pwchange.php");
}

