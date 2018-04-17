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
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* get_rep_power()
* get_poster_info()
* get_rep_img()
*/

function get_rep_power($user_posts, $user_regdate, $user_reputation, $user_group_id)
{
	global $config, $db;
	$now = time();

	$user_power = 0;

	if ($config['rp_min_posts'] && ($user_posts >= $config['rp_min_posts']))
	{
		$user_power = 1;
		if ($config['rp_total_posts'])
		{
			$user_power += intval($user_posts / $config['rp_total_posts']);
		}

		if ($config['rp_membership_days'])
		{
			$user_power += intval(intval(($now - $user_regdate) / 86400) / $config['rp_membership_days']);
		}

		if ($config['rp_power_rep_point'])
		{
			$user_power += intval($user_reputation / $config['rp_power_rep_point']);
		}

		if ($config['rp_max_power'] && ($user_power > $config['rp_max_power']))
		{
			$user_power = $config['rp_max_power'];
		}
	}

	$sql = 'SELECT group_reputation_power
		FROM ' . GROUPS_TABLE;
	$result = $db->sql_query($sql);
	$group_power = (int) $db->sql_fetchfield('group_reputation_power');
	$db->sql_freeresult($result);

	if ($group_power)
	{
		$user_power = $group_power;
	}

	return $user_power;
}

function get_poster_info($post_id)
{
	global $db, $user;

	$sql = 'SELECT p.forum_id, u.*
		FROM ' . POSTS_TABLE . ' p
		LEFT JOIN ' . USERS_TABLE . ' u ON (u.user_id = p.poster_id)
		WHERE p.post_id = ' . $post_id;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$row)
	{
		$user->add_lang('posting');
		trigger_error('NO_POST');;
	}

	return $row;
}

function get_rep_img($points, $username)
{
	global $config, $db, $user;
	$user->add_lang('mods/reputation_mod');
	$user_rank = get_rep_rank($points, $username);

	$block_img = '<img src="images/reputation/neutral.gif" title="' . $user_rank . '" />';
	if ($points > 0)
	{
		$block_img = '<img src="images/reputation/pos.gif" title="' . $user_rank . '" />';
	}

	else if ($points < 0)
	{
		$block_img = '<img src="images/reputation/neg.gif" title="' . $user_rank . '" />';
	}

	$repeat = (intval($points / $config['rp_block_per_points']));
	if ($repeat > $config['rp_max_blocks'])
	{
		$repeat = ($config['rp_max_blocks'] - 1);
	}

	return ($repeat > 0) ? str_repeat($block_img, $repeat) . $block_img : $block_img;
}

function get_rep_rank($points, $username)
{
	global $db;
	$rank = array();
	$sql = 'SELECT *
		FROM ' . REPUTATIONS_RANKS_TABLE . '
		ORDER BY rank_points DESC';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$rank[] = $row;
	}
	$db->sql_freeresult($result);

	$user_rank = '';
	foreach ($rank as $ranks)
	{
		if ($points >= $ranks['rank_points'])
		{
			$user_rank = $ranks['rank_title'];
			break;
		}
	}

	$token = '{USERNAME}';
	return str_replace('{USERNAME}', $username, $user_rank);
}

?>