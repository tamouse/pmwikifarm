<?php if (!defined('PmWiki')) exit();
/**
 * pw - configure passwords
 *
 * @author Tamara Temple <tamara@tamaratemple.com>
 * @created 2011-09-20
 * @version Time-stamp: <2011-10-21 08:38:30 tamara>
 * @copyright (c) 2011 Tamara Temple Web Development.
 * @license GPLv3
 *
 */


// Set default passwords for admin and upload functions, change the
// secret word to your own on the following 2 lines:
$DefaultPasswords['admin'] = crypt('secret');
$DefaultPasswords['upload'] = crypt('upload');

// You can set passwords for any action in PmWiki
// $DefaultPasswords['edit'] = crypt('edit');
// $DefaultPasswords['source'] = crypt('source');

// Alternatively, you can let other actions handle authorization for a
// given action:
//$HandleAuth['source'] = 'edit'; // let's users who can edit pages view
				// the source 

/**
 * This is a good place to set up your user authorization and access.
 *
 * The typical method is to use the SiteAdmin.AuthUser page to create
 * users and groups, which are then used in setting the attributes for
 * various pages and groups. See
 * <http://www.pmwiki.org/wiki/PmWiki/Passwords> page for more
 * information on setting this up.
 */
// include_once("$FarmD/cookbook/authuser.php");

/**
 * Another option is to use the UserAuth2 recipe found at
 * <http://www.pmwiki.org/wiki/Cookbook/UserAuth2>. If you use this
 * method, you have to use the recipe inside your local wikifield
 * cookbook rather than have it in the global farm as it creates files
 * in that directory that are unique to your wiki.
 */

/*  Setup from http://www.pmwiki.org/wiki/Cookbook/UserAuth2 */
//$HomePage  = "Main.HomePage";       // should use dots (instead of slashes) even when using
//$LoginPage = "Site.Login";          // CleanUrls ($EnablePathInfo=1)

/**
 * This function is copied from the userauth2/userauth-admintool.php source.
 *  Unfortunately, as written, the function doesn't work correctly, as it does not
 *  permit the user acess to their actual profile page if the user
 *  name begins with a lower case letter, which is the Proper Noun
 *  based version of thier user name. This function below enforces the Proper Noun
 *  form, giving permissions to the right user.
 *
 * In addition, the function creates the user's Profile page if it
 * doesn't exist already. This is very useful if you're using the
 * AuthorContrib recipe so if they log in, their changes will get
 * attributed in the correct page.
 */
//$OnCreateUserFunc = 'UA2onCreateGrantProfilesAccess_fixed'; /*  CHANGED 2011-06-30 added to get user profiles working? */
//function UA2onCreateGrantProfilesAccess_fixed(&$permrecord, &$profile, $curr_admin, $created_user, $groupaction = false) {
//  if (!$groupaction) {
//    $UsersGroup = "Profiles";
//    $created_user = ucfirst($created_user);
//    $ProfilePage = "$UsersGroup.$created_user";
//    $permrecord['perms']['admin'] =
//      array("xx_$ProfilePage"); // grant all permissions to user on
//				// their profile page
//    if (!PageExists($ProfilePage)) {
//      // Create the Profiles.User page
//      WritePage($ProfilesPage, ReadPage("$UsersGroup.Template"));
//    }
//  }
//}
//require_once ("$FieldD/cookbook/userauth2.php"); // pull from the
						 // field instead of
						 // from the farm
