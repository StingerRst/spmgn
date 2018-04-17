<?php
/** 
*
* @package Multiple Newsletters Add On
* @version $Id: umil_auto_newsletter.php, v 1.011 2011/08/01 Martin Truckenbrodt Exp$
* @copyright (c) 2009 phpBB Group 
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

$mod_name = 'MULTIPLE_NEWSLETTERS_ADD_ON';

$version_config_name = 'mnewsletter_version';

$language_file = 'mods/umil_auto_newsletter';

$options = array(
);

$versions = array(
	// Version 1.0.2
	'1.0.2'	=> array(
		'table_add' => array(
			array(NEWSLETTER_TABLE, array(
					'COLUMNS'	=> array(
						'newsletter_id'					=> array('UINT', NULL, 'auto_increment'),
						'left_id'						=> array('UINT', 0),
						'right_id'						=> array('UINT', 0),
						'newsletter_name'				=> array('VCHAR', ''),
						'newsletter_desc'				=> array('TEXT', ''),
						'newsletter_desc_bitfield'		=> array('VCHAR', ''),
						'newsletter_desc_options'		=> array('INT:11', 5),
						'newsletter_desc_uid'			=> array('VCHAR:8', ''),
						'newsletter_type'				=> array('TINT:2', 0),
						'newsletter_lang'				=> array('VCHAR:30', ''),
						'newsletter_footer'				=> array('TEXT', ''),
						'newsletter_footer_bitfield'	=> array('VCHAR', ''),
						'newsletter_footer_options'		=> array('INT:11', 5),
						'newsletter_footer_uid'			=> array('VCHAR:8', ''),
						'newsletter_css'				=> array('VCHAR', ''),
						'newsletter_public'				=> array('TINT:2', 0),
					),
					'PRIMARY_KEY'	=> array('newsletter_id'),
					'KEYS'		=> array(
						'left_right_id'		=> array('INDEX', array('left_id', 'right_id')),
					),
				),
			),
			array(NEWSLETTER_EMAIL_TABLE, array(
					'COLUMNS'	=> array(
						'email_id'				=> array('UINT', NULL, 'auto_increment'),
						'newsletter_id'			=> array('UINT', 0),
						'email_subject'			=> array('VCHAR', ''),
						'email_text'			=> array('TEXT', ''),
						'email_text_bitfield'	=> array('VCHAR', ''),
						'email_text_options'	=> array('INT:11', 7),
						'email_text_uid'		=> array('VCHAR:8', ''),
						'email_timestamp'		=> array('TIMESTAMP', 0),
						'user_id'				=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> array('email_id'),
				),
			),
			array(NEWSLETTER_GROUPS_TABLE, array(
					'COLUMNS'	=> array(
						'newsletter_id'	=> array('UINT', 0),
						'group_id'		=> array('UINT', 0),
					),
					'newsletter_id'		=> array('newsletter_id'),
				),
			),
			array(NEWSLETTER_USERS_TABLE, array(
					'COLUMNS'	=> array(
						'newsletter_id'	=> array('UINT', 0),
						'user_id'		=> array('UINT', 0),
					),
					'newsletter_id'		=> array('newsletter_id'),
				),
			),
		),
		'config_add' => array(
			array('allow_newsletter_bbcode', 1, false),
			array('allow_newsletter_img', 1, false),
			array('allow_newsletter_links', 1, false),
			array('overwrite_allow_massemail', 1, false),
		),
		'table_column_add' => array(
			array(LOG_TABLE, 'newsletter_id', array('UINT', 0)),
			array(LOG_TABLE, 'email_id', array('UINT', '0')),
			array(USERS_TABLE, 'user_email_html', array('TINT:1', 1)),
		),
		'permission_add' => array(
			array('a_newsletter', true),
			array('n_leader', true),
		),
		'module_add' => array(
			array('acp', 'ACP_BOARD_CONFIGURATION', array(
					'module_basename'		=> 'board',
					'modes'					=> array('newsletter'),
				),
			),
			array('acp', 'ACP_CAT_DOT_MODS', 'ACP_NEWSLETTER'),
			array('acp', 'ACP_NEWSLETTER', array(
					'module_basename'		=> 'newsletter',
					'modes'					=> array('manage'),
				),
			),
			array('acp', 'ACP_NEWSLETTER', array(
					'module_basename'		=> 'newsletter_perm',
					'modes'					=> array('permissions'),
				),
			),
			array('mcp', 'MCP_LOGS', array(
					'module_basename'		=> 'logs',
					'modes'					=> array('email_logs'),
				),
			),
			array('ucp', '', 'UCP_NEWSLETTER'),
			array('ucp', 'UCP_NEWSLETTER', array(
					'module_basename'		=> 'newsletter',
					'modes'					=> array('preferences', 'newsletters'),
				),
			),
		),
	),
	// Version 1.0.4
	'1.0.4' => array(
		'cache_purge' => array('imageset', 'template', 'theme', 'auth'),
	),
	// Version 1.0.5
	'1.0.5' => array(
		'custom'	=> array(
			'fix_table_indexes',
		),
	),
	// Version 1.0.6
	'1.0.6' => array(
		'config_remove' => array(
			array('allow_newsletter_img'),
		),
		'permission_remove' => array(
			array('n_leader', true),
		),
	),
	// Version 1.0.7
	'1.0.7' => array(
		'permission_add' => array(
			array('n_leader', true),
		),
	),
	// Version 1.0.8
	'1.0.8' => array(
	),
	// Version 1.0.9
	'1.0.9' => array(
	),
	// Version 1.0.10
	'1.0.10' => array(
	),
	// Version 1.0.11
	'1.0.11' => array(
	),
	// Version 1.0.12
	'1.0.12' => array(
		'cache_purge' => array('', 'template'),
	),
);

function fix_table_indexes($action, $version)
{
	global $umil;
	// Only when updating
	if ($action == 'update' && $version == '1.0.5')
	{
		// remove PRIMARY KEYs
		$umil->db->sql_query('ALTER TABLE ' . NEWSLETTER_GROUPS_TABLE . ' DROP PRIMARY KEY');
		$umil->db->sql_query('ALTER TABLE ' . NEWSLETTER_USERS_TABLE . ' DROP PRIMARY KEY');
		// add indexes
		$umil->table_index_add(array(
			array(NEWSLETTER_GROUPS_TABLE, 'newsletter_id', 'newsletter_id'),
			array(NEWSLETTER_USERS_TABLE, 'newsletter_id', 'newsletter_id'),
		));
	}
}

// Include the UMIF Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

?>