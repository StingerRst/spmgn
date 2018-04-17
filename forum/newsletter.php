<?php
/**
*
* @package acp
* @version $Id: newsletter.php,v 0.1 2008/06/04 ksar $
* @copyright (c) 2007 
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
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);

$mail_id	= request_var('mail', 0);

$user->setup('mods/email_html');

// is the user logged in?
if (!$user->data['is_registered'])
{
    if ($user->data['is_bot'])
    {
        // the user is a bot, send them back to home base...
        redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
    }
    // the user is not logged in, give them a chance to login here...
    login_box('', 'LOGIN');
} 
if ($mail_id){

	$sql = 'SELECT * FROM ' . MAIL_TABLE . " WHERE DISPLAY_IT = 1 AND id = $mail_id";
	$result = $db->sql_query_limit($sql, 1);
	
						
	if ($result){
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		$poster_id = $row['user_id'];
		
		$sql = 'SELECT * FROM ' . USERS_TABLE . " WHERE user_id = $poster_id";
		$result = $db->sql_query_limit($sql, 1);
		$row_u = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		get_user_rank($row_u['user_rank'], $row_u['user_posts'], $user_cache[$poster_id]['rank_title'], $user_cache[$poster_id]['rank_image'], $user_cache[$poster_id]['rank_image_src']);

		$template->assign_vars(array(
			'S_MAIL_VIEW'				=> true,
			'MAIL_SUBJECT'				=> htmlspecialchars_decode(str_replace("{USERNAME}", $user->data['username'], $row['subject'])),
			'MAIL_MESSAGE'				=> htmlspecialchars_decode(str_replace("{USERNAME}", $user->data['username'], $row['message'])),
			'MAIL_AUTHOR_FULL'			=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], $user->lang['GUEST']),
			'MAIL_DATE'					=> $user->format_date($row['time']),
			'MAILER_AVATAR'				=> get_user_avatar($row_u['user_avatar'], $row_u['user_avatar_type'], $row_u['user_avatar_width'], $row_u['user_avatar_height']),
			'RANK_TITLE'				=> $user_cache[$poster_id]['rank_title'],
			'RANK_IMG'					=> $user_cache[$poster_id]['rank_image'],
			));
	}else{
	
		$template->assign_vars(array(
			'S_MAIL_VIEW'				=> false));
	}
	
	$template->assign_block_vars('navlinks', array(
		'S_IS_CAT'		=> false,
		'S_IS_LINK'		=> false,
		'S_IS_POST'		=> false,
		'FORUM_NAME'	=> $user->lang['TITLE'],
		'FORUM_ID'		=> '',
		'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}newsletter.$phpEx")
	));
	
	$template->assign_block_vars('navlinks', array(
		'S_IS_CAT'		=> false,
		'S_IS_LINK'		=> false,
		'S_IS_POST'		=> true,
		'FORUM_NAME'	=> htmlspecialchars_decode(str_replace("{USERNAME}", $user->data['username'], $row['subject'])),
		'FORUM_ID'		=> '',
		'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}newsletter.$phpEx",'mail=' . $mail_id )
	));

}else{
	$sql = 'SELECT * FROM ' . MAIL_TABLE . " WHERE DISPLAY_IT=1 ORDER BY id DESC";
	$result = $db->sql_query($sql);
		
							
	if ($result){
		$rows = $db->sql_fetchrowset($result);
		$count = 0;
		foreach ($rows as $row){
			$template->assign_block_vars('mailrow', array(
				'MAIL_ID'	    => $row['id'],
				'USERNAME_FULL'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], $user->lang['GUEST']),
				'SUBJECT'		=> htmlspecialchars_decode(str_replace("{USERNAME}", $user->data['username'], $row['subject'])),
				'TIME'			=> $user->format_date($row['time']),
				'U_VIEW'		=> append_sid("{$phpbb_root_path}newsletter.$phpEx", "mail=".$row['id']),
				'S_ROW_COUNT'	=> $count
			));
			$count++;
		}
	}

	$template->assign_block_vars('navlinks', array(
		'S_IS_CAT'		=> false,
		'S_IS_LINK'		=> false,
		'S_IS_POST'		=> false,
		'FORUM_NAME'	=> $user->lang['TITLE'],
		'FORUM_ID'		=> '',
		'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}newsletter.$phpEx")
	));
}

page_header($user->lang['PAGE_TITLE']);

$template->set_filenames(array(
	'body' => 'newsletter_body.html')
);

page_footer();


?>