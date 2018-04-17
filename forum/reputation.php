<?php
/**
*
* @author idiotnesia pungkerz@gmail.com - http://www.phpbbindonesia.com
*
* @package phpBB3
* @version 0.2.0a
* @copyright (c) 2008 phpbbindonesia.com
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
include($phpbb_root_path . 'includes/functions_reputation.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/reputation_mod');

// Define initial vars
$mode			= request_var('mode', 'positive');
$post_id		= request_var('p', 0);
$submit			= (isset($_POST['submit'])) ? true : false;
$cancel			= (isset($_POST['cancel'])) ? true : false;
$message		= request_var('message', '', true);
$point			= request_var('point', 1);
$error			= '';

if ($cancel)
{
	$redirect = append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $post_id) . '#p' . $post_id;
	redirect($redirect);
}

if (!$config['rp_enable'])
{
	trigger_error($user->lang['RP_DISABLED'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $post_id)  . '#p' . $post_id . '">', '</a>'));
}

if (!$user->data['is_registered'])
{
	login_box();
}

if (!$auth->acl_get('u_rp_give'))
{
	trigger_error($user->lang['RP_USER_DISABLED'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $post_id)  . '#p' . $post_id . '">', '</a>'));
}

// get post info
$poster = get_poster_info($post_id);

// some validation
if ($config['rp_forum_exclusions'])
{
	$forum_exc = explode(',', $config['rp_forum_exclusions']);
	if (in_array($poster['forum_id'], $forum_exc))
	{
		trigger_error('RP_FORUM_DISABLED');
	}
}

if ($poster['user_id'] == $user->data['user_id'])
{
	trigger_error($user->lang['RP_SELF'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $post_id)  . '#p' . $post_id . '">', '</a>'));
}	

if ($poster['user_hide_reputation'])
{
	trigger_error('RP_USER_SELF_DISABLED');
}

$sql = 'SELECT rep_post_id
	FROM ' . REPUTATIONS_TABLE . '
	WHERE rep_from = ' . $user->data['user_id'] . '
		AND rep_post_id = ' . $post_id;
$result = $db->sql_query($sql);
$rep_post_id = $db->sql_fetchfield('rep_post_id');
$db->sql_freeresult($result);

if ($rep_post_id)
{
	trigger_error($user->lang['RP_SAME_POST'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $post_id)  . '#p' . $post_id . '">', '</a>'));
}

$now = time();
$limit = $config['rp_user_spread'];

$sql = 'SELECT *
	FROM ' . REPUTATIONS_TABLE . '
	WHERE rep_from = ' . $user->data['user_id'] . '
	ORDER BY rep_id DESC';
$result = $db->sql_query_limit($sql, $limit);

while ($row = $db->sql_fetchrow($result))
{
	if ($config['rp_user_spread'] && !$auth->acl_get('u_rp_ignore') && ($row['rep_to'] == $poster['user_id']))
	{
		trigger_error($user->lang['RP_USER_SPREAD_FIRST'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $post_id)  . '#p' . $post_id . '">', '</a>'));
	}
			
	if ($config['rp_time_limitation'] && !$auth->acl_get('u_rp_ignore') && (($now - $row['rep_time']) < ($config['rp_time_limitation'] * 3600)))
	{
		trigger_error($user->lang['RP_TIMES_LIMIT'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $post_id)  . '#p' . $post_id . '">', '</a>'));
	}
}
$db->sql_freeresult($result);

if ($submit && $config['rp_comment_max_chars'] && (strlen($message) > $config['rp_comment_max_chars']))
{
	$error = sprintf($user->lang['RP_TOO_LONG_COMMENT'], strlen($message), $config['rp_comment_max_chars']);
}

if ($submit && $config['rp_force_comment'] && ((utf8_clean_string($message) === '')))
{
	$error = $user->lang['RP_NO_COMMENT'];
}

//get user power
$user_power = get_rep_power($user->data['user_posts'], $user->data['user_regdate'], $user->data['user_reputation'], $user->data['group_id']);

if ($config['rp_disable_comment'])
{
	$point = ($mode == 'negative') ? -1 : 1;
}

$add_point = $user_power * $point;

$form_name = 'reputation';
add_form_key($form_name);

if ($submit && !check_form_key($form_name))
{
	trigger_error($user->lang['FORM_INVALID']);
}

if ($config['rp_disable_comment'] || ($submit && !$error)) 
{

	$text = utf8_normalize_nfc($message);
	$uid = $bitfield = $options = ''; // will be modified by generate_text_for_storage
	$allow_bbcode = $allow_urls = $allow_smilies = true;
	generate_text_for_storage($text, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);

	$sql_ary = array(
		'rep_from'			=> $user->data['user_id'],
		'rep_to'			=> $poster['user_id'],
		'rep_post_id'		=> $post_id,
		'rep_point'			=> $add_point,
		'rep_time'			=> $now,
		'bbcode_bitfield'	=> $bitfield,
		'bbcode_uid'		=> $uid,
		'enable_bbcode'     => $allow_bbcode,
		'enable_urls'       => $allow_urls,
		'enable_smilies'    => $allow_smilies,
		'rep_comment'		=> $text
	);

	$db->sql_query('INSERT INTO ' . REPUTATIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

	$sql = 'UPDATE ' . USERS_TABLE . '
		SET user_reputation = user_reputation + ' . $add_point . '
		WHERE user_id = ' . $poster['user_id'];
	$db->sql_query($sql);
	

	$redirect = append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $post_id . '#p' . $post_id);
	meta_refresh(3, $redirect);
	trigger_error($user->lang['RP_SENT'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>'));
	
	trigger_error($message);
}


$template->assign_vars(array(
	'POSITIVE'		=> ($mode == 'positive' && $point == 1) ? true : false,
	'ERROR'			=> ($error) ? $error : '',
	'COMMENT'		=> $message,
	'U_POST_ACTION'	=> append_sid("{$phpbb_root_path}reputation.$phpEx", 'p=' . $post_id),)
);

page_header($user->lang['RP_TITLE']); 

if (strpos($_SERVER['HTTP_REFERER'],'spmgn.ru/index.php')){
	$template->assign_var('IFRAME', 1);
}
$template->set_filenames(array(
	'body' => 'reputation_body.html')
);

page_footer();
?>