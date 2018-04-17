<?php 
/** 
*
* @package Multiple Newsletters Add On
* @version $Id: newsletter_view.php, v 1.001 2011/03/25 Martin Truckenbrodt Exp$
* @copyright (c) 2009 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);

include($phpbb_root_path . 'includes/newsletter/constants.'.$phpEx); 
include($phpbb_root_path . 'includes/newsletter/functions.'.$phpEx); 

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/newsletter');

$server_url = generate_board_url();

$email_id		= request_var('e', 0);
$newsletter_id	= request_var('n', 0);
$mode			= request_var('mode', 'html');

$sql = 'SELECT newsletter_id, email_subject, email_text, email_text_bitfield, email_text_uid, email_text_options, user_id FROM ' . NEWSLETTER_EMAIL_TABLE . '
	WHERE email_id =' . (int) $email_id;
$result = $db->sql_query($sql);
$email_row = $db->sql_fetchrow($result);

if (!$newsletter_id)
{
	if (!$email_row['newsletter_id'])
	{
		trigger_error('NO_NEWSLETTER');
	}
	else
	{
		$newsletter_id = (int) $email_row['newsletter_id'];
	}
}

$sql = 'SELECT newsletter_name, newsletter_public, newsletter_css, newsletter_footer, newsletter_footer_bitfield, newsletter_footer_uid, newsletter_footer_options FROM ' . NEWSLETTER_TABLE . '
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

if ($mode == 'html')
{
	if (!$newsletter_row['newsletter_css'])
	{
		$sql = 'SELECT theme_id FROM ' . STYLES_TABLE . '
			WHERE style_id = ' . (int) $user->data['user_style'];
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$sql = 'SELECT theme_path FROM ' . STYLES_THEME_TABLE . '
			WHERE theme_id = ' . (int) $row['theme_id'];
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$newsletter_row['newsletter_css'] = "<link rel='stylesheet' href='" . $server_url . "/styles/" . $row['theme_path'] . "/theme/stylesheet.css ' />
	<link rel='stylesheet' href='". $server_url . "/styles/" . $row['theme_path'] . "/theme/email.css ' />";
	}
	else
	{
		$newsletter_row['newsletter_css'] = "<link rel='stylesheet' href='" . $newsletter_row['newsletter_css'] . "' />";
	}
	$template->assign_vars(array(
		'NEWSLETTER_CSS'			=> $newsletter_row['newsletter_css'],
		'EMAIL_TEXT'				=> generate_text_for_display($email_row['email_text'], $email_row['email_text_uid'], $email_row['email_text_bitfield'], $email_row['email_text_options']),
		'EMAIL_FOOTER'				=> generate_text_for_display($newsletter_row['newsletter_footer'], $newsletter_row['newsletter_footer_uid'], $newsletter_row['newsletter_footer_bitfield'], $newsletter_row['newsletter_footer_options']),
		'EMAIL_SENDER'				=> build_email_sender($email_id, $email_row['user_id']),
		'EMAIL_SENDER_SIGANTURE'	=> build_email_sender_signature($email_id, $email_row['user_id']),
		'BOARD_EMAIL_SIGNATUE'		=> nl2br($config['board_email_sig']),
	));
}
else
{
	$template->assign_vars(array(
		'EMAIL_TEXT'				=> htmlspecialchars_decode(text2plain(generate_text_for_display($email_row['email_text'], $email_row['email_text_uid'], $email_row['email_text_bitfield'], $email_row['email_text_options']), true, true)),
		'EMAIL_FOOTER'				=> htmlspecialchars_decode(text2plain(generate_text_for_display($newsletter_row['newsletter_footer'], $newsletter_row['newsletter_footer_uid'], $newsletter_row['newsletter_footer_bitfield'], $newsletter_row['newsletter_footer_options']), true, true)),
		'EMAIL_SENDER'				=> htmlspecialchars_decode(text2plain(build_email_sender_plain($email_id, $email_row['user_id']), true, false)),
		'EMAIL_SENDER_SIGANTURE'	=> htmlspecialchars_decode(text2plain(build_email_sender_signature($email_id, $email_row['user_id']), true, true)),
		'BOARD_EMAIL_SIGNATUE'		=> htmlspecialchars_decode(text2plain($config['board_email_sig'], true, false)),
	));
}

$template->assign_vars(array(
	'NEWSLETTER_NAME'			=> $newsletter_row['newsletter_name'],
	'EMAIL_SUBJECT'				=> $email_row['email_subject'],
	'USERNAME'					=> $user->data['username'],
));

page_header($user->lang['NEWSLETTER_VIEW'] . " - " . $email_row['email_subject']);

if ($mode == 'html')
{
	$template->set_filenames(array(
		'body' => 'newsletter_view_html.html')
	);
}
else
{
	$template->set_filenames(array(
		'body' => 'newsletter_view_plain.html')
	);
}

page_footer();

?>
