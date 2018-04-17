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

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/reputation_mod');

// Define initial vars
$id			= request_var('id', 0);
$rep_id		= request_var('rep_id', 0);
$mode		= request_var('mode', '');
$start		= request_var('start', 0);

$cancel		= (isset($_POST['cancel'])) ? true : false;

if ($cancel)
{
	$redirect = append_sid("{$phpbb_root_path}viewreputation.$phpEx", 'id=' . $id);
	redirect($redirect);
}

if (!$user->data['is_registered'])
{
	login_box();
}

$forum_ary = array_unique(array_keys($auth->acl_getf('!f_read', true)));
if(sizeof($forum_ary))
{
	$forum_sql = ' AND ' . $db->sql_in_set('p.forum_id', $forum_ary, true);
}

else
{
	$forum_sql ='';
}

$sql = 'SELECT COUNT(r.rep_id) AS total
	FROM ' . REPUTATIONS_TABLE . ' r, ' . POSTS_TABLE . ' p
	WHERE r.rep_to = ' . $id . 
		$forum_sql . '
		AND r.rep_post_id = p.post_id' ;
$result = $db->sql_query($sql);
$total = (int) $db->sql_fetchfield('total');
$db->sql_freeresult($result);


$sql_array = array(
	'SELECT'	=> 'p.post_id, p.post_subject, p.forum_id, u.username, u.user_id, u.user_colour, r.*',

	'FROM'		=> array(
		POSTS_TABLE			=> 'p',
		REPUTATIONS_TABLE	=> 'r',
		USERS_TABLE			=> 'u'
				
	),

	'WHERE'		=> 'r.rep_to =' . $id . '
		AND u.user_id = r.rep_from
		AND r.rep_post_id = p.post_id'
		. $forum_sql,

	'ORDER_BY'	=> 'r.rep_id DESC'
);

$sql = $db->sql_build_query('SELECT', $sql_array);	
$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);
while ($row = $db->sql_fetchrow($result))
{
	$row['bbcode_options'] = (($row['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) +
	(($row['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + 
	(($row['enable_urls']) ? OPTION_FLAG_LINKS : 0);
	
	$point_img = '<img src="' . $phpbb_root_path . 'images/reputation/neutral.gif" alt="" title="' . $row['rep_point'] . '" />';
	
	if ($row['rep_point'] < 0)
	{
		$point_img = '<img src="' . $phpbb_root_path . 'images/reputation/neg.gif" alt="" title="' . $row['rep_point'] . '" />';
	}
	
	else if ($row['rep_point'] > 0)
	{
		$point_img = '<img src="' . $phpbb_root_path . 'images/reputation/pos.gif" alt="" title="' . $row['rep_point'] . '" />';
	}
	
	
	$template->assign_block_vars('reputation', array(
		'FROM'			=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], $row['username']),
		'POINT'			=> $row['rep_point'],
		'U_DELETE'		=> append_sid("{$phpbb_root_path}viewreputation.$phpEx", 'mode=delete&amp;rep_id=' . $row['rep_id']),
		'POINT_IMG'		=> $point_img,
		'TIME'			=> $user->format_date($row['rep_time']),		
		'POST_SUBJECT'	=> censor_text($row['post_subject']),
		'U_POST'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $row['forum_id'] . '&amp;p=' . $row['post_id']) . '#p' . $row['post_id'],
		'COMMENT'		=> generate_text_for_display($row['rep_comment'], $row['bbcode_uid'], $row['bbcode_bitfield'], $row['bbcode_options']))
	);
}
$db->sql_freeresult($result);

if ($mode == 'delete') 
{
	if (!$auth->acl_get('m_rp_moderate'))
	{
		trigger_error('NO_ADMIN');
	}
	
	$sql = 'SELECT rep_to, rep_point
		FROM ' . REPUTATIONS_TABLE . "
		WHERE rep_id = $rep_id";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	
	if (!$row)
	{
		die();
	}
	if (confirm_box(true))
	{
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_reputation = user_reputation - ' . $row['rep_point'] . '
			WHERE user_id = ' . $row['rep_to'];
		$db->sql_query($sql);
		
		$sql = 'DELETE FROM ' . REPUTATIONS_TABLE . '
			WHERE rep_id = ' . $rep_id;
		$db->sql_query($sql);
		
		$redirect = append_sid("{$phpbb_root_path}viewreputation.$phpEx", 'id=' . $row['rep_to']);
		meta_refresh(3, $redirect);
		trigger_error('success');
	}
	else
	{
		confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
			'id'			=> $row['rep_to'],
			'rep_id'		=> $rep_id))
		);
	}
}

$sql = 'SELECT username, user_reputation
	FROM ' . USERS_TABLE . '
	WHERE user_id = ' . $id;
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

$template->assign_vars(array(
	'USERNAME'		=> $row['username'],
	'U_USER'		=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $id),
	'TOTAL_POINTS'	=> $row['user_reputation'],
	'S_IS_POPUP'	=> ($mode == 'popup') ? true : false,
	'S_MODERATE'	=> ($auth->acl_get('m_rp_moderate')) ? true : false,
	'S_ON_PAGE'		=> on_page($total, $config['topics_per_page'], $start),
	'PAGINATION'	=> generate_pagination(append_sid("{$phpbb_root_path}viewreputation.$phpEx", "id=$id"), $total, $config['topics_per_page'], $start, true),
	'TOTAL'			=> $total,
	'U_VIEW_REP' 		=> append_sid("{$phpbb_root_path}viewreputation.$phpEx", 'id=' . $id),
));		
		
page_header($user->lang['RP_TITLE']); 

$template->set_filenames(array(
	'body' => 'viewreputation_body.html')
);

page_footer();

?>