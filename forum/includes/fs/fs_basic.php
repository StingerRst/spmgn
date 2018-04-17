<?php
/**
*
* @package fs
* @author : TheUniqueTiger (Nayan Ghosh)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* fs_basic
* Displays basic statistics - submodules : basic, advanced
*
* @package fs
*/
class fs_basic
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $phpbb_root_path, $phpEx;

		$user->add_lang('fs');
		
		switch ($mode)
		{
			case 'basic':
				//get and display the basic statistics
				$total_posts = $config['num_posts'];
				$total_topics = $config['num_topics'];
				$total_users = $config['num_users'];
				$total_attachments = $config['num_files'];
				$board_start_date = $user->format_date($config['board_startdate']);
				
				//get board age in days
				$boarddays = (time() - $config['board_startdate']) / 86400;
				//averages
				$posts_per_day = sprintf('%.2f', $total_posts / $boarddays);
				$topics_per_day = sprintf('%.2f', $total_topics / $boarddays);
				$users_per_day = sprintf('%.2f', $total_users / $boarddays);
				$files_per_day = sprintf('%.2f', $total_attachments / $boarddays);
				
				
				$total_forum_cat = get_forum_count(FORUM_CAT);
				$total_forum_post = get_forum_count();
				$total_forum_link = get_forum_count(FORUM_LINK);
				$total_forums = $total_forum_cat + $total_forum_post + $total_forum_link;				
				$total_polls = get_polls_count();
				
				$topic_types_count = get_topic_types_count();
				
				$users_count_data = get_user_count_data();
				
				$template->assign_vars(array(
					'TOTAL_POSTS'					=> $total_posts,
					'TOTAL_TOPICS'					=> $total_topics,
					'TOTAL_USERS'					=> $total_users,
					'TOTAL_FORUM_CAT'			=> $total_forum_cat,
					'TOTAL_FORUM_POST'		=> $total_forum_post,
					'TOTAL_FORUM_LINK'		=> $total_forum_link,
					'TOTAL_FORUMS'			=> $total_forums,
					'TOTAL_ATTACHMENTS'		=> $total_attachments,
					'TOTAL_POLLS'			=> $total_polls,
					'TOPIC_TYPES_GLOBAL'	=> $topic_types_count['global'],
					'TOPIC_TYPES_ANNOUNCE'  => $topic_types_count['announce'],
					'TOPIC_TYPES_STICKY'		=> $topic_types_count['sticky'],
					'TOPIC_TYPES_NORMAL'		=> $topic_types_count['normal'],
					'TOPIC_TYPES_UNAPPROVED'	=> $topic_types_count['unapproved'],
					'UNAPPROVED_POSTS'		=> $topic_types_count['unapproved_posts'],
					'USERS_INACTIVE'		=> $users_count_data['inactive'],
					'USERS_ACTIVE'			=> $users_count_data['active'],
					'USERS_ACTIVE_EXPLAIN'	=> sprintf($user->lang['USERS_ACTIVE_EXPLAIN'], 30), //replace 30 by configurable number if you wish
					'USERS_INACTIVE_EXPLAIN'	=> sprintf($user->lang['USERS_INACTIVE_EXPLAIN'], 30), //same comment as above
					'TOTAL_BOTS'			=> $users_count_data['registered_bots'],
					'VISITED_BOTS'			=> $users_count_data['visited_bots'],
					'AVG_POSTS_PER_DAY'		=> $posts_per_day,
					'AVG_TOPICS_PER_DAY'	=> $topics_per_day,
					'AVG_USERS_PER_DAY'	=> $users_per_day,
					'AVG_FILES_PER_DAY'		=> $files_per_day,
					'MOST_ONLINE'			=> $config['record_online_users'],
					'MOST_ONLINE_DATE'		=> $user->format_date($config['record_online_date']),
				));
				
			break;
			
			case 'advanced':
				//get and display advanced statistics
				include ("{$phpbb_root_path}includes/functions_admin.$phpEx"); //for database size
				
				$board_start_date = $user->format_date($config['board_startdate']);
				
				//get attachments info
				$attachments = array(
					'total_files'			=> $config['num_files'],
					'total_size'			=> $config['upload_dir_size'],
				);
				//get avatars info
				$avatars = array(
					'total_files'			=> 0,
					'total_size'			=> 0,
				);
				if ($avatar_dir = @opendir($phpbb_root_path . $config['avatar_path']))
				{
					while (($file = readdir($avatar_dir)) !== false)
					{
						if ($file[0] != '.' && $file != 'CVS' && strpos($file, 'index.') === false)
						{
							$avatars['total_size'] += filesize($phpbb_root_path . $config['avatar_path'] . '/' . $file);
							$avatars['total_files']++;
						}
					}
					@closedir($avatar_dir);
				}
				//get cached files data
				$cached_files = array(
					'total_files'			=> 0,
					'total_size'			=> 0,
				);
				$cache_path = 'cache';
				if ($cache_dir = @opendir($phpbb_root_path . $cache_path))
				{
					while (($file = readdir($cache_dir)) !== false)
					{
						if ($file[0] != '.' && $file != 'CVS' && strpos($file, 'index.') === false)
						{
							$cached_files['total_size'] += filesize($phpbb_root_path . $cache_path . '/' . $file);
							$cached_files['total_files']++;
						}
					}
					@closedir($cache_dir);
				}
				
				//get info about installed components
				//styles
				$styles = array();
				$sql = 'SELECT style_name, style_copyright FROM ' . STYLES_TABLE;
				$result = $db->sql_query($sql);
				while ($style_row = $db->sql_fetchrow($result))
				{
					$styles[] = $style_row;
				}
				$db->sql_freeresult($result);
				foreach ($styles as $current_style)
				{
					$template->assign_block_vars('stylerow', array(
						'STYLE_NAME'			=> $current_style['style_name'],
						'STYLE_COPYRIGHT'			=> $current_style['style_copyright'],
					));
				}
				//imagesets
				$imagesets = array();
				$sql = 'SELECT imageset_name, imageset_copyright FROM ' . STYLES_IMAGESET_TABLE;
				$result = $db->sql_query($sql);
				while ($imageset_row = $db->sql_fetchrow($result))
				{
					$imagesets[] = $imageset_row;
				}
				$db->sql_freeresult($result);
				foreach ($imagesets as $current_imageset)
				{
					$template->assign_block_vars('imagesetrow', array(
						'IMAGESET_NAME'			=> $current_imageset['imageset_name'],
						'IMAGESET_COPYRIGHT'			=> $current_imageset['imageset_copyright'],
					));
				}
				//templates
				$templates = array();
				$sql = 'SELECT template_name, template_copyright FROM ' . STYLES_TEMPLATE_TABLE;
				$result = $db->sql_query($sql);
				while ($template_row = $db->sql_fetchrow($result))
				{
					$templates[] = $template_row;
				}
				$db->sql_freeresult($result);
				foreach ($templates as $current_template)
				{
					$template->assign_block_vars('templaterow', array(
						'TEMPLATE_NAME'			=> $current_template['template_name'],
						'TEMPLATE_COPYRIGHT'			=> $current_template['template_copyright'],
					));
				}
				//themes
				$themes = array();
				$sql = 'SELECT theme_name, theme_copyright FROM ' . STYLES_THEME_TABLE;
				$result = $db->sql_query($sql);
				while ($theme_row = $db->sql_fetchrow($result))
				{
					$themes[] = $theme_row;
				}
				$db->sql_freeresult($result);
				foreach ($themes as $current_theme)
				{
					$template->assign_block_vars('themerow', array(
						'THEME_NAME'			=> $current_theme['theme_name'],
						'THEME_COPYRIGHT'			=> $current_theme['theme_copyright'],
					));
				}
				
				//lang packs
				$lang_packs = array();
				$sql = 'SELECT lang_local_name, lang_iso, lang_author FROM ' . LANG_TABLE;
				$result = $db->sql_query($sql);
				while ($lang_row = $db->sql_fetchrow($result))
				{
					$lang_packs[] = $lang_row;
				}
				$db->sql_freeresult($result);
				foreach ($lang_packs as $current_lang)
				{
					$template->assign_block_vars('langrow', array(
						'LANG_NAME'			=> $current_lang['lang_local_name'],
						'LANG_ISO'			=> $current_lang['lang_iso'],
						'LANG_AUTHOR'		=> $current_lang['lang_author'],
					));
				}
				
				$template->assign_vars(array(
					'START_DATE'					=> $board_start_date,
					'BOARD_AGE'						=> get_time_string($config['board_startdate']),
					'BOARD_VERSION'					=> $config['version'],
					'GZIP_COMPRESSION'				=> ($config['gzip_compress']) ? $user->lang['ON'] : $user->lang['OFF'],
					'DATABASE_INFO'				=> $db->sql_server_info(),
					'DATABASE_SIZE'				=> get_database_size(),
					'ATTACHMENTS_TOTAL'		=> $attachments['total_files'],
					'ATTACHMENTS_SIZE'			=> get_formatted_filesize($attachments['total_size']),
					'AVATARS_TOTAL'		=> $avatars['total_files'],
					'AVATARS_SIZE'			=> get_formatted_filesize($avatars['total_size']),
					'CACHED_FILES_TOTAL'	=> $cached_files['total_files'],
					'CACHED_FILES_SIZE'		=> get_formatted_filesize($cached_files['total_size']),
					'S_STYLES'				=> ($styles) ? true : false,
					'S_IMAGESETS'			=> ($imagesets) ? true : false,
					'S_TEMPLATES'			=> ($templates) ? true : false,
					'S_THEMES'				=> ($themes) ? true : false,
					'S_LANG_PACKS'			=> ($lang_packs) ? true : false,
				));
			break;
			
			default:
		}
		
		$template->assign_vars(array(
			'L_TITLE'	=> $user->lang['FS_BASIC_' . strtoupper($mode)],			
			'S_FS_ACTION'		=> $this->u_action,
			'AS_ON'				=> sprintf($user->lang['AS_ON'], $user->format_date(time())),
		));
		
		$this->tpl_name = 'fs_basic_' . $mode;
		$this->page_title = $user->lang['STATISTICS'] . ' &bull; ' . $user->lang[strtoupper($this->tpl_name)];
		
	}
}
?>