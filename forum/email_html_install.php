<?php
/**
 *
 * @author ksar (ksar) ksar.ksar@gmail.com
 * @version $Id$
 * @copyright (c) 2010 ksar
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
 * @ignore
 */
define('UMIL_AUTO', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

define('IN_PHPBB', true);
include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup();

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// The name of the mod to be displayed during installation.
$mod_name = 'Email HTML';

/*
* The name of the config variable which will hold the currently installed version
* UMIL will handle checking, setting, and updating the version itself.
*/
$version_config_name = 'email_html_version';


// The language file which will be included when installing
//$language_file = 'mods/info_acp_email_html_mod';


/*
* Optionally we may specify our own logo image to show in the upper corner instead of the default logo.
* $phpbb_root_path will get prepended to the path specified
* Image height should be 50px to prevent cut-off or stretching.
*/
//$logo_img = 'styles/prosilver/imageset/site_logo.gif';

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/
$versions = array(
	'0.6.0' => array(

		'table_add' => array(
			array('phpbb_mail', array(
				'COLUMNS'   => array(
					'id'		=> array('UINT:11', NULL, 'auto_increment'),
					'user_id'	=> array('UINT:11', 0),
					'username'	=> array('VCHAR:100', ''),
					'user_colour'	=> array('VCHAR:6', ''),
					'subject'    => array('MTEXT_UNI', ''),
					'message'    => array('MTEXT_UNI', ''),
					'display_it'  => array('UINT:4', 0),
					'time'        => array('UINT:11', 0),
				),

				'KEYS'		=> array(
					'id' => array('INDEX', array('id')),
				),
			)),

		),

		'module_add' => array(
			array('acp', 27,'ACP_MASS_EMAIL_HTML'),
			array('acp', 'ACP_MASS_EMAIL_HTML',
				array('module_basename'	=> 'email_html'),
			),
		),

	),
	
	// Version 0.6.1
	'0.6.1' => array(
	// Nothing changed in this version.
	),
	
	// Version 0.6.2
	'0.6.2' => array(
	// Nothing changed in this version.
	),
	
	// Version 0.6.3
	'0.6.3' => array(
	// Nothing changed in this version.
	),
);

// Include the UMIL Auto file, it handles the rest
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);
?>