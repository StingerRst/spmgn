<?php
/**
*
* @author MarkDHamill (Mark D Hamill) mark@phpbbservices.com
* @package umil
* @version $Id digests_install.php 2.2.8 2010-04-01 00:00:00GMT MarkDHamill $
* @copyright (c) 2010 Mark D Hamill
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('UMIL_AUTO', true);
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('mods/ucp_digests'));

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

/*
* The name of the config variable which will hold the currently installed version
* You do not need to set this yourself, UMIL will handle setting and updating the version itself.
*/
$version_config_name = 'digests_version';

$language_file = 'mods/ucp_digests';

// The name of the mod to be displayed during installation.
$mod_name = $user->lang['DIGEST_PHPBB_DIGESTS'];

/*
* The language file which will be included when installing
* Language entries that should exist in the language file for UMIL (replace $mod_name with the mod's name you set to $mod_name above)
* $mod_name
* 'INSTALL_' . $mod_name
* 'INSTALL_' . $mod_name . '_CONFIRM'
* 'UPDATE_' . $mod_name
* 'UPDATE_' . $mod_name . '_CONFIRM'
* 'UNINSTALL_' . $mod_name
* 'UNINSTALL_' . $mod_name . '_CONFIRM'
*/

/*
* Options to display to the user (this is purely optional, if you do not need the options you do not have to set up this variable at all)
* Uses the acp_board style of outputting information, with some extras (such as the 'default' and 'select_user' options)
*/
/*$options = array(
	'test_username'	=> array('lang' => 'TEST_USERNAME', 'type' => 'text:40:255', 'explain' => true, 'default' => $user->data['username'], 'select_user' => true),
	'test_boolean'	=> array('lang' => 'TEST_BOOLEAN', 'type' => 'radio:yes_no', 'default' => true),
);*/

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
	// Version 2.2.7
	'2.2.7' => array(
					 
		// Add Digest configuration variables
		'config_add' => array(
			array('digests_custom_stylesheet_path', 'prosilver/theme/digest_stylesheet.css'),
			array('digests_digests_title', $mod_name),
			array('digests_enable_auto_subscriptions', 0),
			array('digests_enable_custom_stylesheets', 0),
			array('digests_enable_log', 0),
			array('digests_from_email_name', ''),
			array('digests_from_email_address', ''),
			array('digests_host', 'phpbbservices.com'),
			array('digests_key_value', ''),
			array('digests_max_items', 0),
			array('digests_page_url', 'http://phpbbservices.com/digests/'),
			array('digests_reply_to_email_address', ''),
			array('digests_require_key', 0),
			array('digests_show_output', 1),
			array('digests_user_check_all_forums', 1),
			array('digests_user_digest_filter_type', 'ALL'),
			array('digests_user_digest_format', 'HTML'),
			array('digests_user_digest_max_display_words', 0),
			array('digests_user_digest_max_posts', 0),
			array('digests_user_digest_min_words', 0),
			array('digests_user_digest_new_posts_only', 0),
			array('digests_user_digest_pm_mark_read', 0),
			array('digests_user_digest_remove_foes', 0),
			array('digests_user_digest_reset_lastvisit', 0),
			array('digests_user_digest_send_hour_gmt', -1),
			array('digests_user_digest_send_on_no_posts', 0),
			array('digests_user_digest_show_mine', 1),
			array('digests_user_digest_show_pms', 1),
			array('digests_user_digest_sortby', 'board'),
			array('digests_user_digest_type', 'DAY'),
			array('digests_weekly_digest_day', 0),
		),

		// Add the digest subscribed forums table
		'table_add' => array(
			array(DIGESTS_SUBSCRIBED_FORUMS_TABLE, array(
					'COLUMNS'		=> array(
						'user_id' => array('UINT', 0),
						'forum_id' => array('UINT', 0),
					),
					'PRIMARY_KEY'	=> array('user_id', 'forum_id'),
				),
			),
		),

		// Add digest columns to the phpbb_users table
		'table_column_add' => array(
			array(USERS_TABLE, 'user_digest_filter_type', array('VCHAR:3', 'ALL')),
			array(USERS_TABLE, 'user_digest_format', array('VCHAR:4', 'HTML')),
			array(USERS_TABLE, 'user_digest_max_display_words', array('UINT', 0)),
			array(USERS_TABLE, 'user_digest_max_posts', array('UINT', 0)),
			array(USERS_TABLE, 'user_digest_min_words', array('UINT', 0)),
			array(USERS_TABLE, 'user_digest_new_posts_only', array('TINT:4', 0)),
			array(USERS_TABLE, 'user_digest_pm_mark_read', array('TINT:4', 0)),
			array(USERS_TABLE, 'user_digest_remove_foes', array('TINT:4', 0)),
			array(USERS_TABLE, 'user_digest_reset_lastvisit', array('TINT:4', 1)),
			array(USERS_TABLE, 'user_digest_send_hour_gmt', array('DECIMAL', '0.00')),
			array(USERS_TABLE, 'user_digest_send_on_no_posts', array('TINT:4', 0)),
			array(USERS_TABLE, 'user_digest_show_mine', array('TINT:4', 1)),
			array(USERS_TABLE, 'user_digest_show_pms', array('TINT:4', 1)),
			array(USERS_TABLE, 'user_digest_sortby', array('VCHAR:13', 'board')),
			array(USERS_TABLE, 'user_digest_type', array('VCHAR:4', 'NONE')),
		),
		
		// Add Digest ACP and UCP modules
		'module_add' => array(
							  
			// Add the ACP Digest Settings Category, placed under the General Tab
			array('acp', 'ACP_CAT_GENERAL', 'ACP_DIGEST_SETTINGS'),
			
			// Add the Digests Category
			array('ucp', 0, 'UCP_DIGESTS'),

			// Add the ACP General Settings and ACP User Default Settings modules to the Digest Settings category in the ACP.
			array('acp', 'ACP_DIGEST_SETTINGS', array(
					'module_basename'   => 'board',
					'module_langname'   => 'ACP_DIGEST_GENERAL_SETTINGS',
					'module_mode'       => 'digest_general',
					'module_auth'       => 'acl_a_board',
				),
			),
			array('acp', 'ACP_DIGEST_SETTINGS', array(
					'module_basename'   => 'board',
					'module_langname'   => 'ACP_DIGEST_USER_DEFAULT_SETTINGS',
					'module_mode'       => 'digest_user_defaults',
					'module_auth'       => 'acl_a_board',
				),
			),
			
			// Add the four UCP digest modules
			array('ucp', 'UCP_DIGESTS', array(
					'module_basename'   => 'digests',
					'module_langname'   => 'UCP_DIGESTS_BASICS',
					'module_mode'       => 'basics',
					'module_auth'       => '',
				),
			),
			array('ucp', 'UCP_DIGESTS', array(
					'module_basename'   => 'digests',
					'module_langname'   => 'UCP_DIGESTS_FORUMS_SELECTION',
					'module_mode'       => 'forums_selection',
					'module_auth'       => '',
				),
			),
			array('ucp', 'UCP_DIGESTS', array(
					'module_basename'   => 'digests',
					'module_langname'   => 'UCP_DIGESTS_POST_FILTERS',
					'module_mode'       => 'post_filters',
					'module_auth'       => '',
				),
			),
			array('ucp', 'UCP_DIGESTS', array(
					'module_basename'   => 'digests',
					'module_langname'   => 'UCP_DIGESTS_ADDITIONAL_CRITERIA',
					'module_mode'       => 'additional_criteria',
					'module_auth'       => '',
				),
			),
		),
		
    ),
	
	// Version 2.2.8
	'2.2.8'	=> array(

		// Change a configuration variable
		'config_update' => array(
			array('digests_user_digest_max_display_words', -1),
		),
		
		// Add Digest configuration variables
		'config_add' => array(
			array('digests_enabled', 1),
			array('digests_subscribe_unsubscribers', 0),
		),

		// Add digest columns to the phpbb_users table
		'table_column_add' => array(
			array(USERS_TABLE, 'user_digest_has_unsubscribed', array('TINT:4', 0)),
			array(USERS_TABLE, 'user_digest_no_post_text', array('TINT:4', 0)),
		),
		
		// Update the data type for user_digest_max_display_words so it can hold -1 value
		'table_column_update' => array(
			array(USERS_TABLE, 'user_digest_max_display_words', array('INT:4', 0)),
		),
		
	),

	// Version 2.2.9
	'2.2.9'	=> array(

	),
	
	// Version 2.2.10
	'2.2.10'	=> array(

	),

	// Version 2.2.11
	'2.2.11'	=> array(

	),

	// Version 2.2.12
	'2.2.12'	=> array(
						 
		// Add Digest configuration variables
		'config_add' => array(
			array('digests_block_images', 0),
		),

		// Add digest columns to the phpbb_users table
		'table_column_add' => array(
			array(USERS_TABLE, 'user_digest_attachments', array('TINT:4', 1)),
			array(USERS_TABLE, 'user_digest_block_images', array('TINT:4', 0)),
		),

	),

	// Version 2.2.13
	'2.2.13'	=> array(
						 
		// Add Digest configuration variables
		'config_add' => array(
			array('digests_override_max_execution_time', 0),
			array('digests_max_execution_time', 60),
			array('digests_mailed_successfully', 1),
			array('digests_mailed_date', 0),
			array('digests_user_digest_block_images', 0),
			array('digests_user_digest_attachments', 1),
			array('digests_user_digest_toc', 0),
		),

		// Add digest columns to the phpbb_users table
		'table_column_add' => array(
			array(USERS_TABLE, 'user_digest_toc', array('TINT:4', 0)),
		),

	),

	// Version 2.2.14
	'2.2.14'	=> array(
						 
		// Remove Digest configuration variables
		'config_remove' => array(
			'digests_override_max_execution_time',
			'digests_max_execution_time',
		),

	),

	// Version 2.2.15
	'2.2.15'	=> array(

	),

	// Version 2.2.16
	'2.2.16'	=> array(
						 
		// Remove Digest configuration variables
		'config_remove' => array(
			'digests_subscribe_unsubscribers',
		),

		// Add Digest configuration variables
		'config_add' => array(
			array('digests_users_per_page', 50),
		),

		'module_add' => array(
							  
			// Add the ACP Edit Subscribers module
			array('acp', 'ACP_DIGEST_SETTINGS', array(
					'module_basename'   => 'board',
					'module_langname'   => 'ACP_DIGEST_EDIT_SUBSCRIBERS',
					'module_mode'       => 'digest_edit_subscribers',
					'module_auth'       => 'acl_a_board',
				),
			),
		),
	),

	// Version 2.2.17
	'2.2.17'	=> array(
						 
		// Remove Digest configuration variables
		'config_remove' => array(
			'digests_mailed_date',
			'digests_mailed_successfully',
		),

	),

	// Version 2.2.18
	'2.2.18'	=> array(
						 
		// Add Digest configuration variables
		'config_add' => array(
			array('digests_override_queue', 0),
			array('digests_registration_field', 0),
			array('digests_user_digest_no_post_text', 0),
			array('digests_user_digest_registration', 0),
		),

	),

	// Version 2.2.19
	'2.2.19'	=> array(
						 
	),

	// Version 2.2.20
	'2.2.20'	=> array(
		// Add digest columns to the phpbb_users table
		'table_column_add' => array(
			array(USERS_TABLE, 'user_digest_last_sent', array('UINT:11', 0)),
		),
		// Add Digest configuration variables
		'config_add' => array(
			array('digests_show_email', 0),
		),
		// Let's not making the queue the default. Use can be problematic.
		'config_update' => array(
			array('digests_override_queue', 1),
		),
						 
	),

	// Version 2.2.21
	'2.2.21'	=> array(
						 
	),

	// Version 2.2.22
	'2.2.22'	=> array(
		'module_add' => array(
							  
			// Add the ACP Balance Load module
			array('acp', 'ACP_DIGEST_SETTINGS', array(
					'module_basename'   => 'board',
					'module_langname'   => 'ACP_DIGEST_BALANCE_LOAD',
					'module_mode'       => 'digest_balance_load',
					'module_auth'       => 'acl_a_board',
				),
			),
		),
	),

);

// Clear the cache and templates
$cache_purge = array('', 'template');      

// Include the UMIL Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

?>