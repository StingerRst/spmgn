<?php
/**
*
* @package ucp
* @version $Id$
* @copyright (c) 2005 phpBB Group
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
* ucp_sms
* @package ucp
*/
class ucp_sms
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $phpbb_root_path, $phpEx;

		$submit	= (isset($_POST['submit'])) ? true : false;
		$s_hidden_fields = '';
		
		switch ($mode)
		{
			case 'compose':
			default:
				if ($submit)
				{
					$error = array();
					$add = request_var('add', '', true);
					$users = array_map('trim', array_map('utf8_clean_string', explode("\n", $add)));
					$message = htmlspecialchars_decode(utf8_normalize_nfc(request_var('message', '', true)));
					
					if (!$message)
					{
						$error[] = $user->lang['SMS_EMPTY_MESSAGE'];
					}
					
					if (sizeof($users))
					{
						$sql = 'SELECT user_id, user_type
							FROM ' . USERS_TABLE . '
							WHERE ' . $db->sql_in_set('username_clean', $users) . '
								AND user_type <> ' . USER_INACTIVE;
						$result = $db->sql_query($sql);

						$user_id_ary = array();
						while ($row = $db->sql_fetchrow($result))
						{
							if ($row['user_id'] != ANONYMOUS && $row['user_type'] != USER_IGNORE)
							{
								$user_id_ary[] = $row['user_id'];
							}
						}
						$db->sql_freeresult($result);

						if (!sizeof($error))
						{
							if (sizeof($user_id_ary))
							{
								include_once($phpbb_root_path . 'includes/functions_sms.' . $phpEx);
								$user_id_ary = $auth->acl_get_list($user_id_ary, array('u_receive_sms'), false); 
								$user_id_ary = (!empty($user_id_ary[0]['u_receive_sms'])) ? $user_id_ary[0]['u_receive_sms'] : array();
								
								$result = sms::sendMessage($user_id_ary, $message);
								
								meta_refresh(3, $this->u_action);
								$message = $user->lang['SMS_SENDED'] . '<br />' . implode('<br />', $error) . ((sizeof($error)) ? '<br />' : '') . '<br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
								trigger_error($message);
							}
							else
							{
								$error[] = $user->lang['USER_NOT_FOUND_OR_INACTIVE'];
							}
						}
					}
					else
					{
						$error[] = $user->lang['USER_NOT_FOUND_OR_INACTIVE'];
					}
					$template->assign_vars(array(
						'ERROR'				=> implode('<br />', $error),
						'MESSAGE'			=> $message,
						'USERS'				=> $add,
					));
				}
				break;
		}

		$template->assign_vars(array(
			'U_FIND_USERNAME'		=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=ucp&amp;field=add'),
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
			'S_UCP_ACTION'			=> $this->u_action
		));

		$this->tpl_name = 'ucp_sms';
		$this->page_title = 'UCP_SMS';
	}
}

?>