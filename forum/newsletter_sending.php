<?php
/**
*
* @package phpBB3
* @version $Id: newsletter_sending.php 1.007 2011/04/05 Martin Truckenbrodt $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
include($phpbb_root_path . 'includes/newsletter/constants.'.$phpEx);
include($phpbb_root_path . 'includes/newsletter/functions.'.$phpEx);
include($phpbb_root_path . 'includes/newsletter/functions_admin.'.$phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('mods/newsletter', 'posting'));

$template->set_filenames(array(
	'body' => 'newsletter_sending.html')
);

// Grab only parameters needed here
$server_url = generate_board_url();

$start			= request_var('start', 0);
$action			= request_var('action', array('' => 0));
$action			= key($action);
$update			= (isset($_POST['update'])) ? true : false;
$newsletter_id	= request_var('n', 0);
$email_id		= request_var('e', 0);

// Check permissions
if ($user->data['is_bot'])
{
	redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
}

$bbcode_status	= ($config['allow_newsletter_bbcode']) ? true : false;
$url_status		= ($config['allow_newsletter_links']) ? true : false;

$lastclick	= request_var('lastclick', 0);

$submit		= (isset($_POST['send'])) ? true : false;
$delete		= (isset($_POST['delete'])) ? true : false;
$cancel		= (isset($_POST['cancel'])) ? true : false;

$mode		= request_var('mode', '');

$error = $email_data = array();
$current_time = time();

// Was cancel pressed? If so then redirect to the appropriate page
if ($cancel || ($current_time - $lastclick < 2 && $submit))
{
	$n = ($newsletter_id) ? 'n=' . $newsletter_id . '&amp;' : '';
	$redirect = ($email_id) ? append_sid("{$phpbb_root_path}newsletter_email.$phpEx", $n . 'e=' . $email_id) : (($newsletter_id) ? append_sid("{$phpbb_root_path}newsletter_archive.$phpEx", 'n=' . $newsletter_id) : append_sid("{$phpbb_root_path}newsletter.$phpEx"));
	redirect($redirect);
}

if (in_array($mode, array('send', 'edit', 'delete')) && !$newsletter_id)
{
	trigger_error('NO_NEWSLETTER');
}

// We need to know some basic information in all cases before we do anything.
switch ($mode)
{
	case 'send':
		$sql = 'SELECT *
			FROM ' . NEWSLETTER_TABLE . '
			WHERE newsletter_id = ' . (int) $newsletter_id;

	break;

	case 'edit':
	case 'delete':
		if (!$email_id)
		{
			trigger_error('NO_NEWSLETTER_EMAIL');
		}

		// Force newsletter id
		$sql = 'SELECT newsletter_id
			FROM ' . NEWSLETTER_EMAIL_TABLE . '
			WHERE email_id = ' . (int) $email_id;
		$result = $db->sql_query($sql);
		$n_id = (int) $db->sql_fetchfield('newsletter_id');
		$db->sql_freeresult($result);

		$newsletter_id = (!$n_id) ? $newsletter_id : $n_id;

		$sql = 'SELECT n.*, e.*, u.username, u.username_clean, u.user_sig, u.user_sig_bbcode_uid, u.user_sig_bbcode_bitfield
			FROM ' . NEWSLETTER_EMAIL_TABLE . ' e, ' . NEWSLETTER_TABLE . ' n, ' . USERS_TABLE . ' u
			WHERE e.email_id = ' . (int) $email_id . '
				AND u.user_id = e.user_id
				AND (n.newsletter_id = e.newsletter_id
					OR n.newsletter_id = ' . (int) $newsletter_id . ')';
	break;

	default:
		$sql = '';

	break;
}

if (!$sql)
{
	trigger_error('NO_NEWSLETTER_EMAIL_MODE');
}

$result = $db->sql_query($sql);
$email_data = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if (!$email_data && $mode !== 'delete')
{
	trigger_error('NO_NEWSLETTER');
}

// Is the user able to read within this newsletter?
if (!newsletter_is_viewable($user->data['user_id'], $newsletter_id, $email_data['newsletter_public']))
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		trigger_error('NEWSLETTER_NOT_ALLOWED');
	}

	login_box('', $user->lang['LOGIN']);
}

// Use post_row values in favor of submitted ones...
$newsletter_id	= (!empty($email_data['newsletter_id'])) ? (int) $email_data['newsletter_id'] : (int) $newsletter_id;
$email_id	= (!empty($email_data['email_id'])) ? (int) $email_data['email_id'] : (int) $email_id;

// Permission to do the action asked?
$is_authed = false;

switch ($mode)
{
	case 'send':
	case 'edit':
	case 'delete':
		if (!newsletter_leader_status($user->data['user_id'], $newsletter_id))
		{
			trigger_error('NEWSLETTER_NOT_LEADER');
		}
	break;
}

// Handle delete mode...
if ($mode == 'delete')
{
	delete_newsletter_email($email_id, $newsletter_id, $user->data['user_id']);

	return;
}

// Set some default variables
$uninit = array('email_subject' => '', 'email_text' => '', 'email_timestamp' => $current_time, 'user_id' => $user->data['user_id']);

foreach ($uninit as $var_name => $default_value)
{
	if (!isset($email_data[$var_name]))
	{
		$email_data[$var_name] = $default_value;
	}
}
unset($uninit);

$newsletter_lang = array();
$newsletter_lang['lang_local_name'] = '';
if ($email_data['newsletter_lang'] != '')
{
	$sql = 'SELECT lang_local_name
		FROM ' . LANG_TABLE . "
		WHERE lang_iso = '" . $email_data['newsletter_lang'] . "'";
	$result = $db->sql_query($sql);
	$newsletter_lang = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
}

if ($submit)
{
	// check form
	if (!check_form_key('mods/newsletter'))
	{
		$error[] = $user->lang['FORM_INVALID'];
	}

	$data = array(
		'email_subject'			=> (empty($email_data['email_subject'])) ? utf8_normalize_nfc(request_var('subject', '', true)) : $email_data['email_subject'],
		'email_id'				=> (int) $email_id,
		'newsletter_id'			=> (int) $newsletter_id,
		'user_id'				=> (isset($email_data['user_id'])) ? (int) $email_data['user_id'] : $user->data['user_id'],
		'email_timestamp'		=> (isset($email_data['email_timestamp'])) ? (int) $email_data['email_timestamp'] : $current_time,
		'email_text'			=> utf8_normalize_nfc(request_var('message', '', true)),
	);

	$data['email_text_uid'] = $data['email_text_bitfield'] = $data['email_text_options'] = '';
	generate_text_for_storage($data['email_text'], $data['email_text_uid'], $data['email_text_bitfield'], $data['email_text_options'], $bbcode_status, $url_status, false);

	$message = '';
	$errored = false;

	if ($mode == 'send')
	{
		//first save to database
		$sql = 'INSERT INTO ' . NEWSLETTER_EMAIL_TABLE . ' ' . $db->sql_build_array('INSERT', $data);
		$db->sql_query($sql);

		//second prepare the e-mails

		if ($email_data['newsletter_css'])
		{
			$css = $email_data['newsletter_css'];
		}
		else
		{
			$css = '';
			$sql = 'SELECT theme_id, theme_path FROM ' . STYLES_THEME_TABLE;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$theme_id = $row['theme_id'];
				$theme_path[$theme_id] = $row['theme_path'];
			}

			$sql = 'SELECT style_id, theme_id FROM ' . STYLES_TABLE . ' WHERE style_active = 1';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$style_id = $row['style_id'];
				$style_theme_id = $row['theme_id'];
				$style_theme_path[$style_id] = $theme_path[$style_theme_id];
			}
		}

		// Parse the message, subject and newsletter name
		$newsletter_name = htmlspecialchars_decode($email_data['newsletter_name']);
		$email_subject = htmlspecialchars_decode($data['email_subject']);

		$message = generate_text_for_display($data['email_text'], $data['email_text_uid'], $data['email_text_bitfield'], $data['email_text_options']);
		$message = bbcode_nl2br($message);
		$message_html = htmlspecialchars_decode($message);
		$message_plain = text2plain($message_html, false, true);

		$footer = generate_text_for_display($email_data['newsletter_footer'], $email_data['newsletter_footer_uid'], $email_data['newsletter_footer_bitfield'], $email_data['newsletter_footer_options']);
		$footer = bbcode_nl2br($footer);
		$footer_html = htmlspecialchars_decode($footer);
		$footer_plain = text2plain($footer_html, false, true);

		$email_sender_html = get_username_string('full', $user->data['user_id'], $user->data['username'], $user->data['user_colour'], false, $server_url . '/memberlist.' . $phpEx . '?mode=viewprofile');
		$email_sender_plain = $user->data['username'] . ' - ' . $server_url . '/memberlist.' . $phpEx . '?mode=viewprofile&u=' . $user->data['user_id'];
		$email_sender_sig_html = generate_text_for_display($user->data['user_sig'], $user->data['user_sig_bbcode_uid'], $user->data['user_sig_bbcode_bitfield'], 7);
		$email_sender_sig_plain = text2plain($email_sender_sig_html, false, true);

		$board_contact_html = htmlspecialchars_decode($config['board_contact']);
		$board_contact_plain = strip_tags($config['board_contact']);
		$board_email_sig_html = nl2br($config['board_email_sig']);
		$board_email_sig_plain = nl2br($config['board_email_sig']);

		// get the welcomes
		$sql = 'SELECT lang_iso FROM ' . LANG_TABLE;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if (file_exists($phpbb_root_path . 'language/' . $row['lang_iso'] . '/mods/newsletter.' . $phpEx))
			{
				include($phpbb_root_path . 'language/' . $row['lang_iso'] . '/mods/newsletter.' . $phpEx);
				$lang_welcome[$row['lang_iso']] = $lang['NEWSLETTER_WELCOME'];
			}
			else
			{
				$lang_welcome[$row['lang_iso']] = $user->lang['NEWSLETTER_WELCOME'];
			}
		}
		$db->sql_freeresult($result);

		//users
		$sql_array = array(
			'SELECT' => 'u.user_id',
			'FROM' => array(
				USERS_TABLE => 'u',
			),
			'WHERE' => 'u.user_type = ' . USER_NORMAL,
		);

		if ($email_data['newsletter_type'] == NEWSLETTER_GROUP)
		{
			$sql_array['LEFT_JOIN'] = array(
				array(
					'FROM'	=> array(USER_GROUP_TABLE => 'ug'),
					'ON'	=> 'u.user_id = ug.user_id'
				),
				array(
					'FROM'	=> array(NEWSLETTER_GROUPS_TABLE => 'ng'),
					'ON'	=> 'ug.group_id = ng.group_id'
				),
				array(
					'FROM'	=> array(NEWSLETTER_TABLE => 'n'),
					'ON'	=> 'ng.newsletter_id = n.newsletter_id'
				)
			);
			if ($sql_array['WHERE'])
			{
				$sql_array['WHERE'] .= ' AND ';
			}
			$sql_array['WHERE'] .= 'n.newsletter_id = ' . $email_data['newsletter_id'];
		}
		else if ($email_data['newsletter_type'] == NEWSLETTER_OPTIONAL)
		{
			$sql_array['LEFT_JOIN'] = array(
				array(
					'FROM'	=> array(NEWSLETTER_USERS_TABLE => 'nu'),
					'ON'	=> 'u.user_id = nu.user_id'
				),
				array(
					'FROM'	=> array(NEWSLETTER_TABLE => 'n'),
					'ON'	=> 'nu.newsletter_id = n.newsletter_id'
				)
			);
			if ($sql_array['WHERE'])
			{
				$sql_array['WHERE'] .= ' AND ';
			}
			$sql_array['WHERE'] .= 'n.newsletter_id = ' . $email_data['newsletter_id'];
		}

		//newsletter language
		if ($email_data['newsletter_lang'] != '')
		{
			if ($email_data['newsletter_type'] == NEWSLETTER_BOARD)
			{
				$sql_array['FROM'][NEWSLETTER_TABLE] = 'n';
			}
			if ($sql_array['WHERE'])
			{
				$sql_array['WHERE'] .= ' AND ';
			}
			$sql_array['WHERE'] .= 'n.newsletter_lang = u.user_lang';
		}

		//overwrite user_allow_massemail ?
		if (!$config['overwrite_allow_massemail'])
		{
			if ($sql_array['WHERE'])
			{
				$sql_array['WHERE'] .= ' AND ';
			}
			$sql_array['WHERE'] .= "u.user_allow_massemail = '1'";
		}

		$user_ids = array();

		$sql = $db->sql_build_query('SELECT', $sql_array);

		$result = $db->sql_query($sql);

		while($row = $db->sql_fetchrow($result))
		{
			$user_ids[] = $row['user_id'];
		}

		$db->sql_freeresult($result);

		//leaders (moderators), administrators and founders
		$sql_array = array(
			'SELECT' => 'u.user_id',
			'FROM' => array(USERS_TABLE => 'u'),
			'WHERE' => '((au.forum_id = ' . $email_data['newsletter_id'] . " AND ao.auth_option = 'n_leader') OR ao.auth_option = 'a_newsletter' AND u.user_type = " . USER_NORMAL . ') OR u.user_type = ' . USER_FOUNDER,
			'LEFT_JOIN' => array(
				array(
					'FROM'	=> array(ACL_USERS_TABLE => 'au'),
					'ON'	=> 'u.user_id = au.user_id'
				),
				array(
					'FROM'	=> array(ACL_OPTIONS_TABLE => 'ao'),
					'ON'	=> 'au.auth_option_id = ao.auth_option_id'
				)
			),
		);

		$sql = $db->sql_build_query('SELECT_DISTINCT', $sql_array);

		$result = $db->sql_query($sql);

		while($row = $db->sql_fetchrow($result))
		{
			$user_ids[] = $row['user_id'];
		}

		$db->sql_freeresult($result);

		$sql = 'SELECT COUNT(user_id) AS users_count
			FROM ' . USERS_TABLE . '
			WHERE ' . $db->sql_in_set('user_id', $user_ids);
		$result = $db->sql_query($sql);
		$users_count= (int) $db->sql_fetchfield('users_count');
		$db->sql_freeresult($result);

		$sql_array = array(
			'SELECT' => 'u.user_id, u.user_type, u.username, u.username_clean, u.user_jabber, u.user_notify_type, u.user_style, u.user_lang, u.user_email, u.user_email_html',
			'FROM' => array(
				USERS_TABLE => 'u'),
			'WHERE' => $db->sql_in_set('u.user_id', $user_ids)
		);

		$sql = $db->sql_build_query('SELECT', $sql_array);

		$result = $db->sql_query($sql);

		if (!$result)
		{
			$db->sql_freeresult($result);
			trigger_error($user->lang['NO_USER']);
		}

		// Send the messages
		if (!function_exists('messenger'))
		{
			include($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
		}
		if (!function_exists('group_memberships'))
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}

		$messenger = new messenger(true);

		while ($row = $db->sql_fetchrow($result))
		{

			if (($row['user_notify_type'] == NOTIFY_EMAIL && $row['user_email']) ||
				($row['user_notify_type'] == NOTIFY_IM && $row['user_jabber']) ||
				($row['user_notify_type'] == NOTIFY_BOTH && $row['user_email'] && $row['user_jabber']))
			{
				if ($row['user_email_html'])
				{
					$email_tpl = 'newsletter_html';
					$messenger->set_mail_html(true);
				}
				else
				{
					$email_tpl = 'newsletter_plain';
					$messenger->set_mail_html(false);
				}

				if (!$css)
				{
					$user_style = $row['user_style'];
					$css_message = $server_url . "/styles/" . $style_theme_path[$user_style] . "/theme/stylesheet.css";
					$css_message_email = $server_url . "/styles/" . $style_theme_path[$user_style] . "/theme/email.css";
				}
				else
				{
					$css_message = $css;
					$css_message_email = '';
				}

				$messenger->template($email_tpl, $row['user_lang']);

				$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
				$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
				$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
				$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);
				$messenger->to($row['user_email'], $row['username']);
				$messenger->im($row['user_jabber'], $row['username']);
				$messenger->replyto($user->data['user_email']);

				$messenger->subject($email_subject);
				$messenger->set_mail_priority(MAIL_NORMAL_PRIORITY);

				$messenger->assign_vars(array(
					'USERNAME'					=> htmlspecialchars_decode($row['username']),
					'L_NEWSLETTER_WELCOME'		=> $lang_welcome[$row['user_lang']],
					'NAME'						=> $newsletter_name,
					'SUBJECT'					=> $email_subject,
					'MESSAGE'					=> ($row['user_email_html']) ? $message_html : $message_plain,
					'FOOTER'					=> ($row['user_email_html']) ? $footer_html : $footer_plain,
					'U_CSS'						=> $css_message,
					'U_CSS_EMAIL'				=> $css_message_email,
					'EMAIL_SENDER'				=> ($row['user_email_html']) ? $email_sender_html : $email_sender_plain,
					'EMAIL_SENDER_SIGNATURE'	=> ($row['user_email_html']) ? $email_sender_sig_html : $email_sender_sig_plain,
					'BOARD_CONTACT'				=> ($row['user_email_html']) ? $board_contact_html : $board_contact_plain,
					'BOARD_SIGNATURE'			=> ($row['user_email_html']) ? $board_email_sig_html : $board_email_sig_plain,
				));

				if (!($messenger->send($row['user_notify_type'])))
				{
					$errored = true;
				}

			}

		}

		$db->sql_freeresult($result);

		$messenger->save_queue();

		$sql = 'SELECT email_id FROM ' . NEWSLETTER_EMAIL_TABLE . '
			WHERE newsletter_id = ' . $newsletter_id . '
			ORDER BY email_id DESC';
		$result = $db->sql_query_limit($sql, 1, 0);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		$email_id = $row['email_id'];

		add_log('mod', $newsletter_id, $row['email_id'], 'LOG_NEWSLETTER_EMAIL_SENT_INFO', $email_subject);
	}
	else if ($mode == 'edit')
	{
		$sql = 'UPDATE ' . NEWSLETTER_EMAIL_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $data) . '
			WHERE email_id = ' . $email_id;
		$db->sql_query($sql);

		add_log('mod', $newsletter_id, $email_id, 'LOG_NEWSLETTER_EMAIL_EDIT', $email_data['email_subject']);
	}

	$redirect_url = append_sid("{$phpbb_root_path}newsletter_email.$phpEx", "n=$newsletter_id&amp;e=$email_id");

	meta_refresh(7, $redirect_url);

	if (!$errored)
	{
		$message = (($mode == 'edit') ? $user->lang['NEWSLETTER_EMAIL_EDITED'] : sprintf($user->lang['NEWSLETTER_EMAIL_SENT_INFO'], $users_count)) . '<br /><br />';
		$message .= sprintf((($mode == 'edit') ? $user->lang['RETURN_NEWSLETTER_EMAIL_EDITED'] : $user->lang['RETURN_NEWSLETTER_EMAIL_SENT']), '<a href="' . $redirect_url . '">', '</a>');
		$message .= '<br /><br />' . sprintf($user->lang['RETURN_NEWSLETTER'], '<a href="' . append_sid("{$phpbb_root_path}newsletter_archive.$phpEx", "n=$newsletter_id") . '">', '</a>');
	}
	else
	{
		$message = $user->lang['NEWSLETTER_EMAIL_SEND_ERROR'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . append_sid("{$phpbb_root_path}newsletter_archive.$phpEx", 'n=' . $data['newsletter_id']) . '">', '</a>');
	}
	trigger_error($message);
}

// Page title & action URL, include session_id for security purpose
$s_action = append_sid("{$phpbb_root_path}newsletter_sending.$phpEx", "mode=$mode&amp;n=$newsletter_id", true, $user->session_id);
$s_action .= ($email_id && $mode != 'delete') ? "&amp;e=$email_id" : '';

switch ($mode)
{
	case 'send':
		$page_title = $user->lang['NEWSLETTER_EMAIL_SEND'];
	break;

	case 'delete':
	case 'edit':
		$page_title = $user->lang['NEWSLETTER_EMAIL_EDIT'];
	break;
}

if ($mode =='edit')
{
	decode_message($email_data['email_text'], $email_data['email_text_uid']);
}

$s_hidden_fields = '';
$s_hidden_fields .= '<input type="hidden" name="lastclick" value="' . $current_time . '" />';

add_form_key('newsletter_sending');

$in_groups = '';
$groups = array();

if ($email_data['newsletter_type'] == NEWSLETTER_GROUP)
{
	$sql = "SELECT group_id FROM " . NEWSLETTER_GROUPS_TABLE . " WHERE newsletter_id = " . (int) $newsletter_id;
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$groups[] = $row['group_id'];
	}
	$db->sql_freeresult($result);
	if (sizeof($groups))
	{
		$sql = "SELECT group_id, group_name FROM " . GROUPS_TABLE . " WHERE " . $db->sql_in_set('group_id', $groups);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$in_groups .= "<a href=" . append_sid("memberlist.$phpEx", "mode=group&g=" . $row['group_id']) . ">" . $row['group_name'] . "</a>&nbsp;&nbsp;";
		}
		$db->sql_freeresult($result);
	}
	else
	{
		$in_groups = $user->lang['NEWSLETTER_NO_GROUPS'];
	}
}

// Start assigning vars for main posting page ...
$template->assign_vars(array(
	'L_POST_A'				=> $page_title,
	'NEWSLETTER_NAME'		=> $email_data['newsletter_name'],
	'NEWSLETTER_LANG'		=> $newsletter_lang['lang_local_name'],
	'NEWSLETTER_GROUPS'		=> $in_groups,
	'EMAIL_SUBJECT'			=> $email_data['email_subject'],
	'EMAIL_MESSAGE'			=> $email_data['email_text'],
	'BBCODE_STATUS'			=> ($bbcode_status) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>'),
	'URL_STATUS'			=> ($url_status) ? $user->lang['URL_IS_ON'] : $user->lang['URL_IS_OFF'],
	'S_BBCODE_ALLOWED'		=> $bbcode_status,
	'S_LINKS_ALLOWED'		=> $url_status,
	'S_NEWSLETTER_BOARD'	=> ($email_data['newsletter_type'] == NEWSLETTER_BOARD) ? true : false,
	'S_NEWSLETTER_GROUP'	=> ($email_data['newsletter_type'] == NEWSLETTER_GROUP) ? true : false,
	'S_NEWSLETTER_OPTIONAL'	=> ($email_data['newsletter_type'] == NEWSLETTER_OPTIONAL) ? true : false,
	'S_NEWSLETTER_LANG'		=> ($email_data['newsletter_lang']) ? true : false,
	'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
	'MOD_FOOTER'			=> sprintf($user->lang['NEWSLETTER_MOD'], strval($config['mnewsletter_version'])),
	'S_EDIT'				=> ($mode == 'edit') ? true : false
));

// Output page ...
$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'	=> $user->lang['NEWSLETTER'],
	'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}newsletter.$phpEx")
));

$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'	=> $email_data['newsletter_name'],
	'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}newsletter_archive.$phpEx", "n=$newsletter_id")
));

page_header($page_title);

page_footer();

?>