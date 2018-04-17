<?php 
/** 
*
* @package Multiple Newsletters Add On
* @version $newsletter_id: newsletter_archive.php, v 1.005 2011/08/01 Martin Truckenbrodt Exp$
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
$start = request_var('start', 0);

if (!$newsletter_id)
{
	trigger_error('NO_NEWSLETTER');
}

$sql = 'SELECT newsletter_name, newsletter_public FROM ' . NEWSLETTER_TABLE . ' 
	WHERE newsletter_id = ' . (int) $newsletter_id;
$result = $db->sql_query($sql);
$newsletter_row = $db->sql_fetchrow($result);
$db->sql_freeresult($result);
if (!$newsletter_row)
{
	trigger_error('NO_NEWSLETTER');
}

if (!newsletter_is_viewable($user->data['user_id'], $newsletter_id, $newsletter_row['newsletter_public']))
{
	trigger_error('NEWSLETTER_NOT_ALLOWED');
}

$sql = 'SELECT COUNT(email_id) AS num_emails
	FROM ' . NEWSLETTER_EMAIL_TABLE . '
	WHERE newsletter_id = ' . (int) $newsletter_id;
$result = $db->sql_query($sql);
$emails_count = (int) $db->sql_fetchfield('num_emails');
$db->sql_freeresult($result);

// Make sure $start is set to the last page if it exceeds the amount
if ($start < 0 || $start > $emails_count)
{
	$start = ($start < 0) ? 0 : floor(($emails_count - 1) / $config['topics_per_page']) * $config['topics_per_page'];
}

// If the user is trying to reach the second half of the topic, fetch it starting from the end
$sql_limit = $config['topics_per_page'];

if ($start > $emails_count / 2)
{
	if ($start + $config['topics_per_page'] > $emails_count)
	{
		$sql_limit = min($config['topics_per_page'], max(1, $emails_count - $start));
	}
	$sql_sort_order = 'ASC';
	$sql_start = max(0, $emails_count - $sql_limit - $start);
}
else
{
	$sql_sort_order = 'DESC';
	$sql_start = $start;
}

$sql = 'SELECT email_id, email_subject, email_timestamp, user_id FROM ' . NEWSLETTER_EMAIL_TABLE .'
	WHERE newsletter_id = ' . (int) $newsletter_id . '
	ORDER BY email_id ' . $sql_sort_order;
$result = $db->sql_query_limit($sql, $sql_limit, $sql_start);

while ($email_row = $db->sql_fetchrow($result))
{
	if ($email_row['email_timestamp'] > $user->data['user_lastvisit'])
	{
		$folder_img = 'topic_unread';
		$folder_image = 'topic_unread';
		$folder_alt = 'NEWSLETTER_NEW_EMAIL';
	}
	else
	{
		$folder_img = 'topic_read';
		$folder_image = 'topic_read';
		$folder_alt = 'NEWSLETTER_OLD_EMAIL';
	}
	$template->assign_block_vars('emaillist', array(
		'DATE'					=> $user->format_date($email_row['email_timestamp']),
		'SUBJECT'				=> $email_row['email_subject'],
		'EMAIL_FOLDER_IMG'		=> $user->img($folder_img, $folder_alt),
		'EMAIL_FOLDER_IMG_SRC'	=> $user->img($folder_image, $folder_alt, false, '', 'src'),
		'EMAIL_FOLDER_IMG_ALT'	=> isset($user->lang[$folder_alt]) ? $user->lang[$folder_alt] : '',
		'U_EMAIL'				=> append_sid("newsletter_email.$phpEx", "n=" . $newsletter_id . "&amp;e=" . $email_row['email_id']),
		'EMAIL_SENDER'			=> build_email_sender($email_row['email_id'], $email_row['user_id']),
	));
}
$db->sql_freeresult($result);

$template->assign_vars(array(
	'NEWSLETTER_NAME'		=> $newsletter_row['newsletter_name'],
	'U_NEWSLETTER_ARCHIVE'	=> append_sid("{$phpbb_root_path}newsletter_archive.$phpEx", "n=$newsletter_id"),
	'EMAIL_IMG'				=> $user->img('button_email_new', $user->lang['NEWSLETTER_EMAIL_SEND']),
	'EMAIL_NEW_IMG'			=> $user->img('topic_unread', 'NEWSLETTER_NEW_EMAIL'),
	'EMAIL_OLD_IMG'			=> $user->img('topic_read', 'NEWSLETTER_OLD_EMAIL'),
	'U_EMAIL_SEND'			=> (newsletter_leader_status($user->data['user_id'], $newsletter_id)) ? append_sid("{$phpbb_root_path}newsletter_sending.$phpEx", "mode=send&amp;n=$newsletter_id", true, $user->session_id) : '',
	'PAGINATION'			=> generate_pagination(append_sid("{$phpbb_root_path}newsletter_archive.$phpEx", "n=$newsletter_id"), $emails_count, $config['topics_per_page'], $start),
	'PAGE_NUMBER'			=> on_page($emails_count, $config['topics_per_page'], $start),
	'TOTAL_EMAILS'			=> ($emails_count == 1) ? $user->lang['VIEW_NEWSLETTER_EMAIL'] : sprintf($user->lang['VIEW_NEWSLETTER_EMAILS'], $emails_count),
	'MOD_FOOTER'			=> sprintf($user->lang['NEWSLETTER_MOD'], strval($config['mnewsletter_version'])),
	'S_EMAILLIST'			=> ($emails_count > 0) ? true : false,
));

$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'	=> $user->lang['NEWSLETTER'],
	'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}newsletter.$phpEx")
));

$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'	=> $newsletter_row['newsletter_name'],
	'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}newsletter_archive.$phpEx", "n=$newsletter_id")
));

page_header($user->lang['NEWSLETTER_ARCHIVE'] . " - " . $newsletter_row['newsletter_name']);

$template->set_filenames(array(
	'body' => 'newsletter_archive.html')
);

page_footer();

?>