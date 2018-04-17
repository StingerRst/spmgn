<?php
/** MOD Forum Statistics
*
* @package phpBB3
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
* @description : functions required by fs.php and its modules
* @package phpBB3
*/


/*function get_time_string --- returns the formatted time string like 3 months 20 days etc.
*@param $old_time : the old timestamp
*/
function get_time_string($old_time, $current = 0)
{
	global $user;
	$old_time_ary = getdate($old_time);	
	$current_time_ary = array();
	$diff_ary = array(
		'seconds' 			=> 0,
		'minutes'			=> 0,
		'hours'				=> 0,
		'days'				=> 0,
		'months'			=> 0,
		'years'				=> 0
	);
	$decrement_ary = array(
		'seconds' 			=> false,
		'minutes'			=> false,
		'hours'				=> false,
		'days'				=> false,
		'months'			=> false,
		'years'				=> false
	);
	if (!$current)
	{
		$current = time();
	}
	$temp_time_ary = $old_time_ary;
	if ($current) //do subtraction and get the difference
	{
		$current_time_ary = getdate($current);
		
		//do seconds
		$diff_ary['seconds'] = $current_time_ary['seconds'] - $old_time_ary['seconds'];
		if ($diff_ary['seconds'] < 0)
		{
			$diff_ary['seconds'] = 60 + $diff_ary['seconds'];
			$decrement_ary['minutes'] = true;
		}
		
		//do minutes
		$diff_ary['minutes'] = $current_time_ary['minutes'] - $old_time_ary['minutes'];
		if ($decrement_ary['minutes'])
		{
			$diff_ary['minutes']--;
		}
		if ($diff_ary['minutes'] < 0)
		{
			$diff_ary['minutes'] = 60 + $diff_ary['minutes'];
			$decrement_ary['hours'] = true;
		}
		
		//do hours
		$diff_ary['hours'] = $current_time_ary['hours'] - $old_time_ary['hours'];
		if ($decrement_ary['hours'])
		{
			$diff_ary['hours']--;
		}
		if ($diff_ary['hours'] < 0)
		{
			$diff_ary['hours'] = 24 + $diff_ary['hours'];
			$decrement_ary['days'] = true;
		}
		
		//do days
		$diff_ary['days'] = $current_time_ary['mday'] - $old_time_ary['mday'];
		if ($decrement_ary['days'])
		{
			$diff_ary['days']--;
		}
		if ($diff_ary['days'] < 0)
		{
			$diff_ary['days'] = 30 + $diff_ary['days'];
			$decrement_ary['months'] = true;
		}
		
		//do months
		$diff_ary['months'] = $current_time_ary['mon'] - $old_time_ary['mon'];
		if ($decrement_ary['months'])
		{
			$diff_ary['months']--;
		}
		if ($diff_ary['months'] < 0)
		{
			$diff_ary['months'] = 12 + $diff_ary['months'];
			$decrement_ary['years'] = true;
		}
		
		//do years
		$diff_ary['years'] = $current_time_ary['year'] - $old_time_ary['year'];
		if ($decrement_ary['years'])
		{
			$diff_ary['years']--;
		}
		
	}
	$result = '';	
	$result .= ($diff_ary['years']) ? $diff_ary['years'] . ' ' . (($diff_ary['years'] > 1) ? $user->lang['YEARS'] . ' ' : $user->lang['YEAR'] . ' ') : '';
	$result .= ($diff_ary['months']) ? $diff_ary['months'] . ' ' . (($diff_ary['months'] > 1) ? $user->lang['MONTHS'] . ' ' : $user->lang['MONTH'] . ' ') : '';
	$result .= ($diff_ary['days']) ? $diff_ary['days'] . ' ' . (($diff_ary['days'] > 1) ? $user->lang['DAYS'] . ' ' : $user->lang['DAY'] . ' ') : '';
	$result .= ($diff_ary['hours']) ? $diff_ary['hours'] . ' ' . (($diff_ary['hours'] > 1) ? $user->lang['HOURS'] . ' ' : $user->lang['HOUR'] . ' ') : '';
	$result .= ($diff_ary['minutes']) ? $diff_ary['minutes'] . ' ' . (($diff_ary['minutes'] > 1) ? $user->lang['MINUTES'] . ' ' : $user->lang['MINUTE'] . ' ') : '';
	$result .= ($diff_ary['seconds']) ? $diff_ary['seconds'] . ' ' . (($diff_ary['seconds'] > 1) ? $user->lang['SECONDS'] . ' ' : $user->lang['SECOND'] . ' ') : '';
	
	return $result;
}

/* function get_forum_count --- returns the count of categories or subforums under the given parent
*@param $forum_type : the type of forum
*@param $parent_id : the forum_id of the parent, for future use
*/
function get_forum_count($forum_type = FORUM_POST, $parent_id = 0)
{
	global $db;
	$count = 0;	

	$sql = 'SELECT COUNT(*) AS forum_count
				FROM ' . FORUMS_TABLE . '
				WHERE forum_type = ' . $forum_type;
	
	$result = $db->sql_query($sql);
	$count = $db->sql_fetchfield('forum_count');
	$db->sql_freeresult($result);
	return $count;
}

/* function get_polls_count --- returns the count of polls in the give forum
*@param $forum_id : forum_id, for future use
*/
function get_polls_count($type = '', $forum_id = 0)
{
	global $db, $user;
	$count = 0;

	switch ($type)
	{
		case 'open':
			$sql = 'SELECT COUNT(topic_id) AS polls_count
						FROM ' . TOPICS_TABLE . '
						WHERE poll_start > 0
							AND poll_start + poll_length < ' . time();
		break;
		
		case 'votes':
			$sql = 'SELECT COUNT(*) AS polls_count
						FROM ' . POLL_VOTES_TABLE;						
		break;
		
		case 'voted':
			$sql = 'SELECT COUNT(DISTINCT topic_id) AS polls_count
						FROM ' . POLL_VOTES_TABLE . '
						WHERE vote_user_id = ' . $user->data['user_id'];
		break;
		
		default:
			$sql = 'SELECT COUNT(DISTINCT topic_id) AS polls_count
				FROM ' . POLL_OPTIONS_TABLE;
	}			
	$result = $db->sql_query($sql);
	$count = (int) $db->sql_fetchfield('polls_count');
	$db->sql_freeresult($result);
	
	return $count;
}

/*function get_tpoic_types_count ----- returns an array containing the number of topics of various types
*@param $forum_id : forum_id, for future use
*/
function get_topic_types_count($forum_id = 0)
{
	global $db, $config;
	
	$temp_counts = array(0,0,0,0);
	
	$where_forum_sql = ($forum_id) ? ' WHERE forum_id = ' . $forum_id : '';
	
	//first get counts by topic_type field
	$sql = 'SELECT topic_type, COUNT(topic_id) as topics_count
				FROM ' . TOPICS_TABLE . $where_forum_sql . '
				GROUP BY topic_type';
	$result = $db->sql_query($sql);
	while ($temp_ary = $db->sql_fetchrow($result))
	{
		$temp_counts[$temp_ary['topic_type']] = $temp_ary['topics_count'];
	}
	$db->sql_freeresult($result);
	
	//now get the count of unapproved topics
	$sql = 'SELECT COUNT(topic_id) as topics_count
				FROM ' . TOPICS_TABLE . $where_forum_sql;
	$sql .= ($where_forum_sql) ? ' AND topic_approved = 0' : ' WHERE topic_approved = 0';
				 
	$result = $db->sql_query($sql);
	$unapproved_topic_count = $db->sql_fetchfield('topics_count');
	$db->sql_freeresult($result);
	
	//now get the count of unapproved posts
	$sql = 'SELECT COUNT(post_id) as posts_count
				FROM ' . POSTS_TABLE . $where_forum_sql;
	$sql .= ($where_forum_sql) ? ' AND post_approved = 0' : ' WHERE post_approved = 0';
				 
	$result = $db->sql_query($sql);
	$unapproved_post_count = $db->sql_fetchfield('posts_count');
	$db->sql_freeresult($result);
	
	$counts = array(
		'global'				=> $temp_counts[POST_GLOBAL],
		'announce'			=> $temp_counts[POST_ANNOUNCE],
		'sticky'				=> $temp_counts[POST_STICKY],
		'normal'				=> $temp_counts[POST_NORMAL],
		'unapproved'			=> (int) $unapproved_topic_count,		
		'unapproved_posts'	=> (int) $unapproved_post_count,
	);
	return $counts;
}

// function get_user_count_data --- returns the active user count, inactive users, male, female users
function get_user_count_data()
{
	global $db, $config;
	//get active users
	$cutoff_days = 30;
	$current_time = getdate(time());
	$cutoff_time = mktime(0, 0, 0, $current_time['mon'], $current_time['mday'] - $cutoff_days, $current_time['year']);
	$sql = 'SELECT COUNT(user_id) as user_count
				FROM ' . USERS_TABLE . '
				WHERE user_lastvisit > ' . $cutoff_time . '
					AND user_type IN ( ' . USER_NORMAL . ', ' . USER_FOUNDER . ' )';
	$result = $db->sql_query($sql);
	$active_user_count = (int) $db->sql_fetchfield('user_count');
	$db->sql_freeresult($result);
	
	//get bots count
	$sql = 'SELECT COUNT(bot_id) as bot_count
				FROM ' . BOTS_TABLE;
	$result = $db->sql_query($sql);
	$bot_count = (int) $db->sql_fetchfield('bot_count');
	$db->sql_freeresult($result);
	
	//get visited bot count
	$sql = 'SELECT COUNT(bot_id) as bot_count
				FROM ' . BOTS_TABLE . ' b INNER JOIN ' . USERS_TABLE . ' u ON b.user_id = u.user_id 
				WHERE u.user_lastvisit > 0';
	$result = $db->sql_query($sql);
	$visited_bot_count = (int) $db->sql_fetchfield('bot_count');
	$db->sql_freeresult($result);
	
	$return_ary = array(
		'active'				=> $active_user_count,
		'inactive' 			=> $config['num_users'] - $active_user_count,
		'registered_bots'	=> $bot_count,
		'visited_bots'		=> $visited_bot_count,
	);
	return $return_ary;
}

/*function get_topic_type_count --- returns the count of the given topic type in the given forum
@param $topic_type : the type of topic
@param $forum_id : forum_id, for future use
*/
function get_topic_type_count($topic_type = POST_NORMAL, $forum_id = 0)
{
	global $db;
	$count = 0;
	$sql = 'SELECT COUNT(topic_id) AS count FROM ' . TOPICS_TABLE . '
				WHERE topic_type = ' . $topic_type;
	$result = $db->sql_query($sql);
	$count = $db->sql_fetchfield('count');
	$db->sql_freeresult($result);
	return $count;
}

//function get_topic_views_count --- returns the total topic views
function get_topic_views_count()
{
	global $db;
	$count = 0;
	$sql = 'SELECT SUM(topic_views) AS count FROM ' . TOPICS_TABLE . ' 
				WHERE topic_approved = 1';
	$result = $db->sql_query($sql);
	$count = (int) $db->sql_fetchfield('count');
	$db->sql_freeresult($result);
	return $count;
}

/*function get_posters_count -- returns the number of users who have posted (post_count > 0)*/
function get_posters_count()
{
	global $db;
	$count = 0;
	$sql = 'SELECT COUNT(user_id) AS count FROM ' . USERS_TABLE . '
				WHERE user_posts > 0';
	$result = $db->sql_query($sql);
	$count = (int) $db->sql_fetchfield('count');
	$db->sql_freeresult($result);
	return $count;
}

/*function get_formatted_filesize --- returns formatted filesize with B/KB/MB
@param $size - the filesize in bytes
*
function get_formatted_filesize($size)
{
	global $user;
	return ($size >= 1073741824) ? sprintf('%.2f' . $user->lang['GB'], ($size / 1073741824)) : (($size >= 1048576) ? sprintf('%.2f ' . $user->lang['MB'], ($size / 1048576)) : (($size >= 1024) ? sprintf('%.2f ' . $user->lang['KB'], ($size / 1024)) : sprintf('%.2f ' . $user->lang['BYTES'], $size)));
}

/*function get_top_forums ---- returns the top $limit_count number of forums based on and sorted by the given criteria
@param $limit_count : the maximum top records to be retrieved
@param $criteria : the criteria to sort (like topics, sticky, posts, polls)
@param $order : the sort order ('ASC' or 'DESC')
*/
function get_top_forums($limit_count = 10, $criteria = 'topics', $order = 'DESC', $no_forum_ary = array())
{
	global $db;
	$return_ary = array();
	
	$forum_sql = '';
	if (sizeof($no_forum_ary))
	{
		$forum_sql = ' AND ' . $db->sql_in_set('f.forum_id', $no_forum_ary, true);
	}
	
	switch ($criteria)
	{
		case 'topics':
			$sql = 'SELECT f.forum_id AS f_id, f.forum_name AS f_name, COUNT(t.topic_id) AS count
						FROM ' . FORUMS_TABLE . ' f, ' . TOPICS_TABLE . ' t 						
						WHERE t.forum_id = f.forum_id
							AND t.topic_approved = 1' . $forum_sql . '
						GROUP BY f_id  
						ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($temp_row = $db->sql_fetchrow($result))
			{
				$return_ary[] = $temp_row;
			}			
			$db->sql_freeresult($result);
			
		break;
		
		case 'posts':
			$sql = 'SELECT p.forum_id AS f_id, f.forum_name AS f_name, COUNT(p.post_id) AS count 
						FROM ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . ' f  
						WHERE p.forum_id = f.forum_id
							AND p.post_approved = 1' . $forum_sql . '
						GROUP BY f_id  
						ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($temp_row = $db->sql_fetchrow($result))
			{
				$return_ary[] = $temp_row;
			}			
			$db->sql_freeresult($result);
			
		break;
		
		case 'polls':
			$sql = 'SELECT po.topic_id AS t_id,  f.forum_id AS f_id, f.forum_name AS f_name, COUNT(DISTINCT po.topic_id) AS count 
				FROM ' . POLL_OPTIONS_TABLE . ' po, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f 
				WHERE po.topic_id = t.topic_id 
					AND t.forum_id = f.forum_id' . $forum_sql . '
				GROUP BY f_id 
				ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($temp_row = $db->sql_fetchrow($result))
			{
				$return_ary[] = $temp_row;
			}			
			$db->sql_freeresult($result);
			
		break;
		
		case 'sticky':
			$sql = 'SELECT COUNT(t.topic_id) AS count, f.forum_id AS f_id, f.forum_name AS f_name 
				FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f 
				WHERE t.topic_type = ' . POST_STICKY . '
					AND t.forum_id = f.forum_id' . $forum_sql . '
				GROUP BY f_id 
				ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($temp_row = $db->sql_fetchrow($result))
			{
				$return_ary[] = $temp_row;
			}			
			$db->sql_freeresult($result);
			
		break;
		
		case 'participation':
			$sql = 'SELECT COUNT(DISTINCT p.poster_id) AS count, f.forum_id AS f_id, f.forum_name AS f_name 
				FROM ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . ' f 
				WHERE p.forum_id = f.forum_id 
					AND p.post_approved = 1' . $forum_sql . '
				GROUP BY f_id 
				ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($temp_row = $db->sql_fetchrow($result))
			{
				$return_ary[] = $temp_row;
			}
			$db->sql_freeresult($result);
			
		break;
		
		default:
	}
	
	return $return_ary;
}

/*function make_select_box --- creates a select box
@param $options --- array containing options in ($key => $value) ~ ($option => $option_lang) 
@param $selected --- the selected option
@param $select_identifier --- the name for the <select>
@param $label_prompt --- the label to be shown for the select box
@param $submit_prompt --- the text shown for the submit button
@param $action_url --- the url for the action attribute of form
*/
function make_select_box($options, $selected, $select_identifier, $label_prompt, $submit_prompt = 'submit', $action_url = '')
{
	$return_str = $temp_str = '';
	
	foreach ($options as $option => $option_lang)
	{
		if ($option != $selected)
		{
			$temp_str .= '<option value="' . $option . "\">$option_lang</option>";
		}
		else {
			$temp_str .= '<option value="' . $option . '" selected="selected">' . $option_lang . '</option>';
		}
	}
	
	$submit_prompt = ucfirst($submit_prompt);
	
	if ($options)
	{
		$return_str = '<fieldset><label for="' . $select_identifier . '">' . $label_prompt . ': </label><select name=' . $select_identifier . ' id="' . $select_identifier . '">' . $temp_str . '</select> <input class="button2" type="submit" value="' . $submit_prompt . '" /></fieldset>';
	}
	
	return $return_str;
}

/*function get_top_topics ---- returns the top $limit_count number of topics based on and sorted by the given criteria
@param $limit_count : the maximum top records to be retrieved
@param $criteria : the criteria to sort (like posts etc)
@param $order : the sort order ('ASC' or 'DESC')
*/
function get_top_topics($limit_count = 10, $criteria = 'posts', $order = 'DESC', $no_forum_ary = array())
{
	global $db;
	$return_ary = array();
	
	$forum_sql = '';
	if (sizeof($no_forum_ary))
	{
		$forum_sql = ' AND ' . $db->sql_in_set('f.forum_id', $no_forum_ary, true);
	}	
	
	switch ($criteria)
	{
		case 'posts':
			$sql = 'SELECT f.forum_id AS f_id, f.forum_name AS f_name, (t.topic_replies_real + 1) AS count, t.topic_id AS t_id, t.topic_title AS t_title 
						FROM ' . FORUMS_TABLE . ' f, ' . TOPICS_TABLE . ' t 						
						WHERE t.forum_id = f.forum_id
							AND t.topic_approved = 1' . $forum_sql . '
						ORDER BY count ' . $order;			
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($temp_row = $db->sql_fetchrow($result))
			{
				$return_ary[] = $temp_row;
			}			
			$db->sql_freeresult($result);			
		break;
		
		case 'views':
			$sql = 'SELECT f.forum_id AS f_id, f.forum_name AS f_name, t.topic_views AS count, t.topic_id AS t_id, t.topic_title AS t_title 
						FROM ' . FORUMS_TABLE . ' f, ' . TOPICS_TABLE . ' t 						
						WHERE t.forum_id = f.forum_id
							AND t.topic_approved = 1' . $forum_sql . '						
						ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($temp_row = $db->sql_fetchrow($result))
			{
				$return_ary[] = $temp_row;
			}			
			$db->sql_freeresult($result);			
		break;
		
		case 'participation':
			$sql = 'SELECT COUNT(DISTINCT p.poster_id) AS count, t.topic_id as t_id, t.topic_title as t_title, f.forum_id AS f_id, f.forum_name AS f_name 
				FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f 
				WHERE p.topic_id = t.topic_id
					AND t.forum_id = f.forum_id
					AND p.post_approved = 1' . $forum_sql . '
				GROUP BY t_id 
				ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($temp_row = $db->sql_fetchrow($result))
			{
				$return_ary[] = $temp_row;
			}			
			$db->sql_freeresult($result);			
		break;
		
		case 'attachments':
			$sql = 'SELECT COUNT(at.attach_id) AS count, at.topic_id as t_id, t.topic_title as t_title, f.forum_id AS f_id, f.forum_name AS f_name 
				FROM ' . ATTACHMENTS_TABLE . ' at, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f 
				WHERE at.topic_id = t.topic_id
					AND t.forum_id = f.forum_id
					AND t.topic_approved = 1' . $forum_sql . '
				GROUP BY t_id 
				ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($temp_row = $db->sql_fetchrow($result))
			{
				$return_ary[] = $temp_row;
			}			
			$db->sql_freeresult($result);			
		break;
		
		default:
	}
	
	return $return_ary;
}

/*function get_top_users ---- returns the top $limit_count number of users based on and sorted by the given criteria
@param $limit_count : the maximum top records to be retrieved
@param $criteria : the criteria to sort (like posts etc)
@param $order : the sort order ('ASC' or 'DESC')
*/
function get_top_users($limit_count = 10, $criteria = 'posts', $order = 'DESC', $recent_posts_limit_days = 10)
{
	global $db;
	$return_ary = array();
	switch ($criteria)
	{
		case 'posts':
			$sql = 'SELECT user_id AS u_id, username, user_colour AS u_colour, user_posts AS count
						FROM ' . USERS_TABLE . '
						WHERE user_posts > 1 
						ORDER BY user_posts ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($current_user = $db->sql_fetchrow($result))
			{
				$return_ary[] = $current_user;
			}
			$db->sql_freeresult($result);
		break;
		
		case 'topics':
			$sql = 'SELECT u.user_id AS u_id, u.username AS username, u.user_colour AS u_colour, COUNT(t.topic_id) AS count
						FROM ' . TOPICS_TABLE. ' t, ' . USERS_TABLE . ' u 
						WHERE t.topic_approved = 1 
							AND t.topic_poster = u.user_id 
						GROUP BY u_id
						ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($current_user = $db->sql_fetchrow($result))
			{
				$return_ary[] = $current_user;
			}
			$db->sql_freeresult($result);
		break;
		
		case 'recent_posts':
			$sql = 'SELECT p.poster_id AS u_id, u.username AS username, u.user_colour AS u_colour, COUNT(p.post_id) AS count
						FROM ' . POSTS_TABLE. ' p, ' . USERS_TABLE . ' u 
						WHERE p.post_approved = 1 
							AND p.poster_id = u.user_id 
							AND p.post_time > ' . (time() - $recent_posts_limit_days * 86400) . ' 
						GROUP BY u_id
						ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($current_user = $db->sql_fetchrow($result))
			{
				$return_ary[] = $current_user;
			}
			$db->sql_freeresult($result);
		break;
		
		case 'attachments':
			$sql = 'SELECT u.user_id AS u_id, u.username, u.user_colour AS u_colour, COUNT(at.attach_id) AS count
						FROM ' . ATTACHMENTS_TABLE . ' at, ' . USERS_TABLE . ' u 
						WHERE at.poster_id = u.user_id
						GROUP BY u_id
						ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($current_user = $db->sql_fetchrow($result))
			{
				$return_ary[] = $current_user;
			}
			$db->sql_freeresult($result);
		break;
		
		default:
	}
	return $return_ary;
}

/* function get_total_members --- gets the total member count and the group membership counts.
@param $return_total --- the byref var for returning the total member count
@param $groups_data --- contains data about the groups
*/
function get_total_members(&$return_total, $groups_data)
{
	global $db;
	$return_total = 0;
	$member_counts = array();
	
	//populate the $return_ary with basic group_ids
	foreach ($groups_data as $group_row)
	{		
		$member_counts[$group_row['group_id']] = array();		
	}
	
	//get all the users and increment the group counter	
	$sql = 'SELECT COUNT(u.user_id) AS count, g.group_id AS g_id, g.group_name AS g_name 
				FROM ' . USERS_TABLE . ' u, ' . GROUPS_TABLE . ' g 
				WHERE u.group_id = g.group_id
					AND u.user_id <> ' . ANONYMOUS . '
				GROUP BY g_id';
	$result = $db->sql_query($sql);
	while ($current_group = $db->sql_fetchrow($result))
	{
		$member_counts[$current_group['g_id']] = $current_group['count'];
		$return_total += $current_group['count'];		
	}
	$db->sql_freeresult($result);
		
	return $member_counts;
}

/*function get_online_data --- returns the groupwise count of users online
@param $userss_ary --- array containing the online users data
@param $total_count --- the total count of users online
@param $total_hidden --- the total count of hidden users
*/
function get_online_data($groups_data, &$total_online, &$total_hidden, &$total_guests)
{
	global $db, $config;
	$total_online = $total_hidden = 0;
	$online_data = array();
	
	//create the array structure for $online_data grouped by groups
	foreach ($groups_data as $current_group)
	{
		$online_data[$current_group['group_id']] = array();		
	}
	
	$sql = 'SELECT s.session_user_id AS user_id, u.username AS username, u.user_colour AS user_colour, g.group_id AS group_id, g.group_name AS group_name, g.group_colour AS group_colour, s.session_viewonline AS viewonline 
			FROM ' . SESSIONS_TABLE . ' s, ' . USERS_TABLE . ' u, ' . GROUPS_TABLE . ' g 
			WHERE s.session_user_id = u.user_id 
				AND s.session_time >= ' . (time() - $config['load_online_time'] * 60) . ' 
				AND u.group_id = g.group_id
			ORDER BY u.username_clean ASC';
			
	$result = $db->sql_query($sql);
	while ($user_data = $db->sql_fetchrow($result))
	{
		$total_online++;
		if ($user_data['user_id'] != ANONYMOUS) //we won't add users to the online data if ANONYMOUS... we'll simply show the total guest count retrieved earlier with another function
		{
			if ($user_data['viewonline'])
			{
				
				$online_data[$user_data['group_id']][] = $user_data;
			}
			else
			{
				$total_hidden++;
			}
		}
		else
		{
			$total_guests++;
			$online_data[$user_data['group_id']][] = $total_guests;			
		}
	}
	return $online_data;
}

//function get_groups_data --- returns data about all the groups in the database
function get_groups_data()
{
	global $db;
	$return_ary = array();
	$sql = 'SELECT * FROM ' . GROUPS_TABLE . '
				WHERE group_legend = 1
					OR ' . $db->sql_in_set('group_name', array('BOTS', 'REGISTERED', 'GUESTS')) . ' 
				ORDER BY group_name ASC';
	$result = $db->sql_query($sql);
	while ($group_row = $db->sql_fetchrow($result))
	{
		$return_ary[] = $group_row;
	}
	$db->sql_freeresult($result);
	return $return_ary;
}

//function get_num_recent_posts --- returns the number of recent posts from last $limit_days days
function get_num_recent_posts($limit_days = 10)
{
	global $db;	
	$sql = 'SELECT COUNT(post_id) AS count FROM ' . POSTS_TABLE . ' 
				WHERE post_time > ' . (time() - $limit_days * 86400);
	$result = $db->sql_query($sql);
	$count = (int) $db->sql_fetchfield('count');
	$db->sql_freeresult($result);
	return $count;
}

//function get_orphan_attachments_count --- returns the number of orphan attachments
function get_orphan_attachments_count()
{
	global $db;	
	$sql = 'SELECT COUNT(attach_id) AS total_orphan
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE is_orphan = 1
					AND filetime < ' . (time() - 3*60*60);
	$result = $db->sql_query($sql);
	$count = (int) $db->sql_fetchfield('count');
	$db->sql_freeresult($result);
	return $count;
}

/*function get_top_attachments ---- returns the top $limit_count number of attachments based on and sorted by the given criteria
@param $limit_count : the maximum top records to be retrieved
@param $criteria : the criteria to sort (like posts etc)
@param $order : the sort order ('ASC' or 'DESC')
*/
function get_top_attachments($limit_count = 10, $criteria = 'posts', $order = 'DESC', $no_forum_ary = array())
{
	global $db;
	$return_ary = array();
	
	$forum_sql = '';
	if (sizeof($no_forum_ary))
	{
		$forum_sql = ' AND ' . $db->sql_in_set('f.forum_id', $no_forum_ary, true);
	}
	
	switch ($criteria)
	{
		case 'recent':
			$sql = 'SELECT at.attach_id AS attach_id, at.post_msg_id AS p_id, p.topic_id AS t_id, p.post_subject AS p_subject, f.forum_id AS f_id, f.forum_name AS f_name, at.filesize AS filesize, at.real_filename AS filename, at.filetime AS filetime
						FROM ' . ATTACHMENTS_TABLE . ' at, ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . ' f 
						WHERE at.is_orphan = 0							
							AND at.post_msg_id = p.post_id
							AND p.forum_id = f.forum_id' . $forum_sql . '
						ORDER BY filetime ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($current_attach = $db->sql_fetchrow($result))
			{
				$return_ary[] = $current_attach;
			}
			$db->sql_freeresult($result);
		break;
		
		case 'filetype':
			$sql = 'SELECT COUNT(attach_id) AS count, extension, mimetype 
						FROM ' . ATTACHMENTS_TABLE . ' 
						WHERE is_orphan = 0
						GROUP BY extension
						ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($current_attach = $db->sql_fetchrow($result))
			{
				$return_ary[] = $current_attach;
			}
			$db->sql_freeresult($result);
		break;
		
		case 'filesize':
			$sql = 'SELECT at.attach_id AS attach_id, at.post_msg_id AS p_id, p.topic_id AS t_id, p.post_subject AS p_subject, f.forum_id AS f_id, f.forum_name AS f_name, at.filesize AS filesize, at.real_filename AS filename, at.filetime AS filetime
						FROM ' . ATTACHMENTS_TABLE . ' at, ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . ' f 
						WHERE at.is_orphan = 0							
							AND at.post_msg_id = p.post_id
							AND p.forum_id = f.forum_id' . $forum_sql . '
						ORDER BY filetime ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($current_attach = $db->sql_fetchrow($result))
			{
				$return_ary[] = $current_attach;
			}
			$db->sql_freeresult($result);
		break;
		
		case 'download':
			$sql = 'SELECT at.attach_id AS attach_id, at.post_msg_id AS p_id, p.topic_id AS t_id, p.post_subject AS p_subject, f.forum_id AS f_id, f.forum_name AS f_name, at.filesize AS filesize, at.real_filename AS filename, at.filetime AS filetime, at.download_count AS count
						FROM ' . ATTACHMENTS_TABLE . ' at, ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . ' f 
						WHERE at.is_orphan = 0							
							AND at.post_msg_id = p.post_id
							AND p.forum_id = f.forum_id' . $forum_sql . '
						ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($current_attach = $db->sql_fetchrow($result))
			{
				$return_ary[] = $current_attach;
			}
			$db->sql_freeresult($result);
		break;
		
		default:
	}
	return $return_ary;
}

//function get_total_attach_downloads --- returns the total download count of all attachments
function get_total_attach_downloads($option = '')
{
	global $db;
	switch ($option)
	{
		case 'total_size':
			$sql = 'SELECT SUM(download_count * filesize) AS count FROM ' . ATTACHMENTS_TABLE;
			$result = $db->sql_query($sql);
			$count = $db->sql_fetchfield('count');
			$db->sql_freeresult($result);
			return $count;
		break;
		
		case 'total_count':
		default:
			$sql = 'SELECT SUM(download_count) AS count FROM ' . ATTACHMENTS_TABLE;
			$result = $db->sql_query($sql);
			$count = $db->sql_fetchfield('count');
			$db->sql_freeresult($result);
			return $count;
	}
}

//function get_orphan_attachments_size --- returns the total size of all orphan attachments
function get_orphan_attachments_size()
{
	global $db;
	$sql = 'SELECT SUM(filesize) AS total_size 
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE is_orphan = 1';
	$result = $db->sql_query($sql);
	$count = (int) $db->sql_fetchfield('total_size');
	$db->sql_freeresult($result);
	return $count;
}

/*function get_top_polls ---- returns the top $limit_count number of polls based on and sorted by the given criteria
@param $limit_count : the maximum top records to be retrieved
@param $criteria : the criteria to sort (like posts etc)
@param $order : the sort order ('ASC' or 'DESC')
*/
function get_top_polls($limit_count = 10, $criteria = 'votes', $order = 'DESC', $no_forum_ary = array())
{
	global $db;
	$return_ary = array();
	
	$forum_sql = '';
	if (sizeof($no_forum_ary))
	{
		$forum_sql = ' AND ' . $db->sql_in_set('f.forum_id', $no_forum_ary, true);
	}
	
	switch ($criteria)
	{
		case 'votes':
			$sql = 'SELECT COUNT(po.poll_option_id) AS count, po.topic_id AS t_id, t.topic_title AS t_title, f.forum_id AS f_id, f.forum_name AS f_name, t.poll_title AS poll_title
					FROM ' . POLL_VOTES_TABLE . ' po, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f
					WHERE po.topic_id = t.topic_id
						AND t.forum_id = f.forum_id' . $forum_sql . '
					GROUP BY t_id
					ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($current_poll = $db->sql_fetchrow($result))
			{
				$return_ary[] = $current_poll;
			}
		break;
		
		case 'recent':
			$sql = 'SELECT t.topic_id AS t_id, t.topic_title AS t_title, f.forum_id AS f_id, f.forum_name AS f_name, t.poll_title AS poll_title, t.poll_start AS poll_start
					FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f
					WHERE t.poll_start > 0
						AND t.forum_id = f.forum_id' . $forum_sql . '
					GROUP BY t_id
					ORDER BY poll_start ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($current_poll = $db->sql_fetchrow($result))
			{
				$return_ary[] = $current_poll;
			}
		break;
		
		default:
	}
	
	return $return_ary;
}

/* function get_poll_options --- returns the poll options for the given topics
@param topic_ids : array containing the topic ids
@return value : array([topic_id_1] => array(option_1_text, option_2_text...), ...)
*/
function get_poll_options($topic_ids)
{
	global $db;
	$return_ary = array();
	if (!$topic_ids)
	{
		return;
	}
	//setup the return array
	foreach ($topic_ids as $topic_id)
	{
		$return_ary[] = array($topic_id => array());
	}
	//get the poll option texts
	$sql = 'SELECT poll_option_text, poll_option_id, topic_id FROM ' . POLL_OPTIONS_TABLE . '
				WHERE ' . $db->sql_in_set('topic_id', $topic_ids);				
	$result = $db->sql_query($sql);
	while ($current_option = $db->sql_fetchrow($result))
	{
		$return_ary[$current_option['topic_id']][] = '(' . $current_option['poll_option_id'] . ') ' . $current_option['poll_option_text'];
	}
	$db->sql_freeresult($result);
	return $return_ary;
}

//now this function is NOT required
/*function get_periodic_stats --- returns the periodic stats for the given mode (daily / monthly) in an array
@param $time_offsets --- array containing the time_offsets for start of each period
@param $component --- the component (like user_reg, posts, topics)
@param $periodic --- daily / monthly
@param $time_limit --- the upper limit of time for entries to be returned , kept for future, not implemented now
*/
/*
function get_periodic_stats($time_offsets, $component = 'topics', $periodic = 'daily', $time_limit = 0)
{
	global $db;
	$return_ary = array();
	$time_start = $time_offsets[1]; //the time_offsets array always has the first index 1
	
	//we return the data in an array with the same keys as the time offsets, this makes it compatible and easier to interpret & use in the caller function
	
	switch ($component)
	{
		case 'topics':
			$sql = 'SELECT topic_time AS time FROM ' . TOPICS_TABLE .'
						WHERE topic_time >= ' . $time_start . '
							AND topic_time < ' . $time_limit . '
							AND topic_approved = 1'; //don't sort here as we will later put the counts in the proper place in the array
		break;
		
		case 'posts':
			$sql = 'SELECT post_time AS time FROM ' . POSTS_TABLE .'
						WHERE post_time >= ' . $time_start . '
							AND post_time < ' . $time_limit . '
							AND post_approved = 1';
		break;
		
		case 'user_reg':
			$sql = 'SELECT user_regdate AS time FROM ' . USERS_TABLE . '
						WHERE user_regdate >= ' . $time_start . '
							AND user_regdate < ' . $time_limit . '
							AND (user_type = ' . USER_NORMAL . '
								OR user_type = ' . USER_FOUNDER . ')';
		break;
		
		default:
	}
	
	$result = $db->sql_query($sql);
	
	//incrementing the appropriate array element can be done in two ways... one by using the date() method and the other as below...
	//this is faster than using the date() function, thats what I think!
	
	//increment counters for the matching time_offsets
	//we take advantage of the fact that the returned data is actually sorted in the ascending order without explicit sort order in sql
	//prevent comparing each value to increment counters, instead store the current index, if value is > than next offset then update the current index
	$current_index = 1;
	while ($current_row = $db->sql_fetchrow($result))
	{
		//we calculate the first index so that subsequent entries require only a single check instead of checking each time_offset
		if ($current_row['time'] >= $time_offsets[$current_index + 1])
		{
			while (isset($time_offsets[$current_index + 1]) && $current_row['time'] >= $time_offsets[$current_index + 1])
			{
				$current_index++;
			}
		}
		$return_ary[$current_index]++;
	}
	$db->sql_freeresult($result);
	return $return_ary;
}
*/
//this function is NOT needed
/*function get_ranks --- returns all the ranks (specified all, special, non-special)
@param --- type : (all, special, non-special)
*/
/*function get_ranks($type = 'all')
{
	global $db;
	$return_ary = array();
	switch ($type)
	{
		case 'special':
			$sql_rank = ' rank_special = 1';
		break;
		
		case 'non-special':
			$sql_rank = ' rank_special = 0';
		break;
		
		case 'all':
		default:		
			$sql_rank = '';
	}
	$sql = 'SELECT * FROM ' . RANKS_TABLE . (($sql_rank) ? ' WHERE' . $sql_rank : '') . ' ORDER BY rank_min DESC';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$return_ary[$row['rank_id']] = $row;
	}
	$db->sql_freeresult($result);
	return $return_ary;
}
*/
/*function get_users_ranks_count --- returns the count of users belonging to different ranks
@param --- type : (all, special, non-special)
*/
function get_users_ranks_count($type = 'non-special')
{
	global $db;
	$return_ary = array();
	switch ($type)
	{
		case 'special':
			$sql_rank = ' AND r.rank_special = 1';
		break;
		
		case 'non-special':
			$sql_rank = ' AND r.rank_special = 0';
		break;
		
		case 'all':
		default:		
			$sql_rank = '';
	}
	$sql = 'SELECT user_posts
				FROM ' . USERS_TABLE . '
				WHERE user_type = ' . USER_NORMAL;
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$return_ary[] = $row;
	}
	$db->sql_freeresult($result);
	return $return_ary;
}

/*function get_formatted_tz --- returns the formatted tz --> 5.50 => + 5:30
@param tz_decimal : the tz in decimal
*/
/*function get_formatted_tz($tz_decimal = 0)
{
	$time = explode('.', $tz_decimal, 2);
	$hour = $time[0];	
	$minutes = $time[1];
	$minutes = 0.60 * $minutes;
	if ($hour == 0 && $minutes == 0)
	{
		return;
	}
	else
	{
		return (($hour < 0) ? ' - ' . substr($hour, 1) : ' + ' . $hour) . (($minutes > 0) ? ':' . str_pad($minutes, 2, STR_PAD_LEFT) : '');
	}
}*/

/*function get_custom_profile_fields --- gets the custom profile field of certain types, and which are active and viewable
returns an array containing each profile field
*/
function get_custom_profile_fields()
{
	global $db, $user;
	$return_ary = array();
	$select_field_types = array(FIELD_STRING, FIELD_BOOL, FIELD_DROPDOWN); //only these types will be selected
	//get all the fields and its language data from 2 custom profile fields tables comparing lang with the user lang
	$sql = 'SELECT pf.*, pl.*
		FROM ' . PROFILE_FIELDS_TABLE . ' pf, ' . PROFILE_LANG_TABLE . ' pl, ' . LANG_TABLE . ' l 
		WHERE ' . $db->sql_in_set('pf.field_type', $select_field_types) . '
			AND (pf.field_active = 1 AND pf.field_hide = 0 AND pf.field_no_view = 0 AND pf.field_fs_show = 1) 
			AND pf.field_id = pl.field_id
			AND pl.lang_id = l.lang_id
			AND l.lang_iso = "' . $user->data['user_lang'] . '"
			ORDER BY field_order ASC';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$return_ary[] = $row;
	}
	$db->sql_freeresult($result);
	return $return_ary;
}

/*function get_profile_field_data --- gets the data for a given custom profile field, The result varies depending on the field type. However the return value is always an array
@param : $profile_field : an array containing the data for the profile field whose data is to be retrieved
@param : $total_values_set : the total values that have been set
@param : $limit_count : the number of top entries to be returned
@param : $total_groups : the number of groups that have been retrieved
*/
function get_profile_field_data($profile_field, &$total_values_set, $limit_count = 0, &$total_groups = 0)
{
	global $db, $user;
	$return_ary = array();
	$field_identifier = 'pf_' . $profile_field['field_ident'];
	
	switch ($profile_field['field_type'])
	{
		case FIELD_STRING:
			//if this is a single line string, we have to treat this like a city or country name... here we have to get the results in groups after trim and lcase
			$sql = 'SELECT COUNT(user_id) AS count, TRIM(LCASE(' . $field_identifier . ')) AS value
				FROM ' . PROFILE_FIELDS_DATA_TABLE . '
				WHERE ' . $field_identifier . ' <> "" 
				GROUP BY value
				ORDER BY count DESC';
			$result = $db->sql_query($sql); //don't limit here as we need the total count too!
			while ($row = $db->sql_fetchrow($result))
			{
				$return_ary[] = $row;
				$total_values_set += $row['count'];
				$total_groups++;
			}
			$db->sql_freeresult($result);
			
			if ($limit_count)
			{
				$return_ary = array_slice($return_ary, 0, $limit_count, true);
			}
		
		break;
		
		case FIELD_BOOL:
		case FIELD_DROPDOWN:	//we treat both as same as they have options
			//here we cannot use count directly as it doesn't return the zero values. So we have to get all the data and later count them
			$exclude_option_id = ($profile_field['field_type'] == FIELD_DROPDOWN) ? $profile_field['field_novalue'] - 1 : ''; //this value has to be excluded as it means an unselected value			
			//get the options first... we do this since we are not sure they would be retrieved by going through the data table in case no one selected this option
			
			
			$sql = 'SELECT pfl.lang_value, pfl.option_id 
				FROM ' . PROFILE_FIELDS_LANG_TABLE . ' pfl, ' . LANG_TABLE . ' l 
				WHERE pfl.field_id = ' . $profile_field['field_id'] . 
				(($exclude_option_id != '') ? ' AND pfl.option_id <> ' . $exclude_option_id : '') . '
					AND pfl.lang_id = l.lang_id
					AND l.lang_iso = "' . $user->data['user_lang'] . '"					
				ORDER BY pfl.option_id ASC';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{				
				$options[$row['option_id'] + 1]['lang_value'] = $row['lang_value'];
				$options[$row['option_id'] + 1]['count'] = 0;
				$options[$row['option_id'] + 1]['option_id'] = $row['option_id'] + 1;
			}
			$db->sql_freeresult($result);
			
			//now get the counts
			$sql = 'SELECT COUNT(user_id) AS count, ' . $field_identifier . '
				FROM ' . PROFILE_FIELDS_DATA_TABLE . '
				WHERE ' . $field_identifier . ' <> 0 ' . 
				(($exclude_option_id != '') ? ' AND ' . $field_identifier . ' <> ' . $exclude_option_id : '') . '
				GROUP BY ' . $field_identifier . '
				ORDER BY count DESC';
			
			$result = $db->sql_query($sql); //don't limit count here as we need the total too!
			while ($row = $db->sql_fetchrow($result))
			{				
				$options[$row[$field_identifier]]['count'] = $row['count']; //here $field_identifier is actually (option_id + 1)
				$total_values_set += $row['count'];
				$total_groups++;
			}
			$db->sql_freeresult($result);		
			
			if ($profile_field['field_type'] == FIELD_DROPDOWN)
			{				
				//only for dropdown... if we have to get only the top x results, remove the unwanted results				
			
				//we have to use multisort... for this purpose we have to split our temp_return_ary into two arrays for lang_value and count
				$lang_value_ary = $counts_ary = $option_ids_ary = array();
				foreach ($options as $option_id => $data)
				{
					$lang_value_ary[] = $data['lang_value'];
					$counts_ary[] = $data['count'];
					$option_ids_ary[] = $option_id;
				}			
				//now use multisort
				array_multisort($counts_ary, SORT_DESC, $option_ids_ary, SORT_ASC, $lang_value_ary, SORT_ASC);
				//now populate the temp_return_ary with the new values, break when we find a zero count, or reaches the limit
				$temp_return_ary = array();				
				for ($i = 0; isset($lang_value_ary[$i]); $i++)
				{
					if ($counts_ary[$i] == 0 || ($limit_count && $i == $limit_count))
					{
						break;
					}
					$temp_return_ary[$i] = array(
						'lang_value' 	=> $lang_value_ary[$i],
						'count'			=> $counts_ary[$i],
						'option_id'		=> $option_ids_ary[$i],
					);
				}
			}
			
			if ($profile_field['field_type'] == FIELD_BOOL)
			{
				$return_ary = $options;
			}
			else // for dropdown only
			{
				$return_ary = $temp_return_ary;
			}
			unset($options);			
		break;		
		
		default:
	}
	
	return $return_ary;
}
?>