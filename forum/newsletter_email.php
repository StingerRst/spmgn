<?php 
/** 
*
* @package Multiple Newsletters Add On
* @version $Id: newsletter_email.php, v 1.007 2011/08/01 Martin Truckenbrodt Exp$
* @copyright (c) 2009 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);

include($phpbb_root_path . 'includes/newsletter/constants.'.$phpEx); 
include($phpbb_root_path . 'includes/newsletter/functions.'.$phpEx); 

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/newsletter');

$newsletter_id = request_var('n', 0);
$email_id = request_var('e', 0);
$view = request_var('view', '');

if ($view == 'next' || $view == 'previous')
{
	$sql_condition = ($view == 'next') ? '>' : '<';
	$sql_ordering = ' ORDER BY email_id ' . (($view == 'next') ? 'ASC' : 'DESC');

	$sql = 'SELECT email_id FROM ' . NEWSLETTER_EMAIL_TABLE . '
		WHERE email_id ' . $sql_condition . ' ' . (int) $email_id . '
		AND newsletter_id = ' . (int) $newsletter_id . $sql_ordering;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	if (!$row)
	{
		// OK, the topic doesn't exist. This error message is not helpful, but technically correct.
		trigger_error(($view == 'next') ? 'NEWSLETTER_OLD_EMAILS' : 'NEWSLETTER_NEW_EMAILS');
	}
	else
	{
		$email_id = (int) $row['email_id'];
	}
}

$sql = 'SELECT * FROM ' . NEWSLETTER_EMAIL_TABLE . '
	WHERE email_id =' . (int) $email_id;
$result = $db->sql_query($sql);
$email_row = $db->sql_fetchrow($result);

if (!$email_row)
{
	trigger_error('NO_NEWSLETTER_EMAIL');
}

if (!$newsletter_id)
{
	$sql = 'SELECT newsletter_id FROM ' . NEWSLETTER_EMAIL_TABLE . '
		WHERE email_id = ' . (int) $email_id;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	if (!$row)
	{
		trigger_error('NO_EMAIL_NEWSLETTER');
	}
	else
	{
		$newsletter_id = (int) $row['newsletter_id'];
	}
}

$sql = 'SELECT newsletter_name, newsletter_public, newsletter_footer, newsletter_footer_bitfield, newsletter_footer_uid, newsletter_footer_options FROM ' . NEWSLETTER_TABLE . '
	WHERE newsletter_id = ' . (int) $newsletter_id;
$result = $db->sql_query($sql);
$newsletter_row = $db->sql_fetchrow($result);

if (!$newsletter_row)
{
	trigger_error('NO_NEWSLETTER');
}

if (!newsletter_is_viewable($user->data['user_id'], $newsletter_id, $newsletter_row['newsletter_public']))
{
	trigger_error('NEWSLETTER_NOT_ALLOWED');
}

$template->assign_vars(array(
	'DATE'						=> $user->format_date($email_row['email_timestamp']),
	'SUBJECT'					=> $email_row['email_subject'],
	'TEXT'						=> generate_text_for_display($email_row['email_text'], $email_row['email_text_uid'], $email_row['email_text_bitfield'], $email_row['email_text_options']),
	'NEWSLETTER_NAME'			=> $newsletter_row['newsletter_name'],
	'NEWSLETTER_FOOTER'			=> generate_text_for_display($newsletter_row['newsletter_footer'], $newsletter_row['newsletter_footer_uid'], $newsletter_row['newsletter_footer_bitfield'], $newsletter_row['newsletter_footer_options']),
	'USERNAME'					=> $user->data['username'],
	'U_NEWSLETTER_HTML'			=> append_sid("{$phpbb_root_path}newsletter_view.$phpEx", "mode=html&amp;n=$newsletter_id&amp;e=$email_id"),
	'U_NEWSLETTER_PLAIN'		=> append_sid("{$phpbb_root_path}newsletter_view.$phpEx", "mode=plain&amp;n=$newsletter_id&amp;e=$email_id"),
	'U_NEWSLETTER_EMAIL'		=> append_sid("{$phpbb_root_path}newsletter_email.$phpEx", "n=$newsletter_id&amp;e=$email_id"),
	'EMAIL_SENDER'				=> build_email_sender($email_id, $email_row['user_id']),
	'EMAIL_SENDER_SIGNATURE'	=> build_email_sender_signature($email_id, $email_row['user_id']),
	'BOARD_SIGNATURE'			=> nl2br($config['board_email_sig']),
	'EDIT_IMG' 					=> $user->img('icon_post_edit', 'NEWSLETTER_EMAIL_EDIT'),
	'DELETE_IMG' 				=> $user->img('icon_post_delete', 'NEWSLETTER_EMAIL_DELETE'),
	'U_EMAIL_EDIT'				=> (newsletter_leader_status($user->data['user_id'], $newsletter_id)) ? append_sid("{$phpbb_root_path}newsletter_sending.$phpEx", "mode=edit&amp;n=$newsletter_id&amp;e=$email_id", true, $user->session_id) : '',
	'U_EMAIL_DELETE'			=> (newsletter_leader_status($user->data['user_id'], $newsletter_id)) ? append_sid("{$phpbb_root_path}newsletter_sending.$phpEx", "mode=delete&amp;n=$newsletter_id&amp;e=$email_id", true, $user->session_id) : '',
	'U_VIEW_OLDER_EMAIL'		=> append_sid("{$phpbb_root_path}newsletter_email.$phpEx", "n=$newsletter_id&amp;e=$email_id&amp;view=previous"),
	'U_VIEW_NEWER_EMAIL'		=> append_sid("{$phpbb_root_path}newsletter_email.$phpEx", "n=$newsletter_id&amp;e=$email_id&amp;view=next"),
	'MOD_FOOTER'				=> sprintf($user->lang['NEWSLETTER_MOD'], strval($config['mnewsletter_version']))
));

$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'	=> $user->lang['NEWSLETTER'],
	'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}newsletter.$phpEx")
));

$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'	=> $newsletter_row['newsletter_name'],
	'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}newsletter_archive.$phpEx", "n=$newsletter_id")
));

$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'	=> $email_row['email_subject'],
	'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}newsletter_email.$phpEx", "n=$newsletter_id&amp;e=$email_id")
));

page_header($user->lang['NEWSLETTER_EMAIL'] . " - " . $email_row['email_subject']);

$template->set_filenames(array(
	'body' => 'newsletter_email.html')
);

page_footer();

?>