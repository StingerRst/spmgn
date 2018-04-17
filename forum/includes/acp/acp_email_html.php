<?php
/**
*
* @package acp
* @version $Id: acp_email_html.php,v 0.1 2008/06/04 ksar $
* @copyright (c) 2008
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
* @package acp
*/
class acp_email_html
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix;

		$user->add_lang('acp/email_html');
		$this->tpl_name = 'acp_email_html';
		$this->page_title = 'ACP_MASS_EMAIL_HTML';

		$form_key = 'acp_email_html';
		add_form_key($form_key);

		// Set some vars
		$submit = (isset($_POST['submit'])) ? true : false;
		$error = array();

		$usernames	= request_var('usernames', '', true);
		$group_id	= request_var('g', 0);
		$subject	= utf8_normalize_nfc(request_var('subject', '', true));
		$message	= utf8_normalize_nfc(request_var('message', '', true));
		
		switch ($mode){
		
			// View the sent emails
			case 'view':
			
				$action = request_var('action', '');
				$display_it = request_var('display', 0);
				$mail_id = request_var('mail', 0);
			
				switch ($action){
				
					case 'display':
						$sql_ary = array(
							'display_it'			=> $display_it,
						);
		
						$sql = 'UPDATE ' . MAIL_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . " WHERE id = $mail_id";
						$result = $db->sql_query($sql);
						
						$message = ($display_it == '1') ? $user->lang['DISPLAY_MAIL_SUCCES'] : $user->lang['HIDE_MAIL_SUCCES'];
						trigger_error($message . adm_back_link($this->u_action));
					break;
						
					case 'delete':
						$sql = 'DELETE FROM ' . MAIL_TABLE . " WHERE id = $mail_id";
						$result = $db->sql_query($sql);
						
						$message = $user->lang['DELETE_MAIL_SUCCES'];
						trigger_error($message . adm_back_link($this->u_action));
					break;
				
					case 'view':
						$sql = 'SELECT * FROM ' . MAIL_TABLE . " WHERE id = $mail_id";
						$result = $db->sql_query_limit($sql, 1);
						
						if ($result){
							$row = $db->sql_fetchrow($result);
							$db->sql_freeresult($result);
								
							$template->assign_vars(array(
								'S_SEND'				=> false,
								'S_GEN'					=> false,
								'MAIL_ID'	    => $row['id'],
								'USERNAME_FULL'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], $user->lang['GUEST']),
								'SUBJECT'		=> htmlspecialchars_decode($row['subject']),
								'MESSAGE'		=> htmlspecialchars_decode($row['message']),
								'TIME'			=> $user->format_date($row['time']),
								'DISPLAY_IT'	=> ($row['display_it']) ? true : false,
								'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;mail=' . $row['id'],
								'U_BACK'		=> $this->u_action,)
								);
							
						}else{
							$message = $user->lang['ID_NOT_EXIST'];
							trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
						}
					break;
					
					default:
						$sql = 'SELECT * FROM ' . MAIL_TABLE . " ORDER BY id DESC";
						$result = $db->sql_query($sql);
						
						if ($result){
							$rows = $db->sql_fetchrowset($result);
							foreach ($rows as $row){
								$template->assign_block_vars('mailrow', array(
									'MAIL_ID'	    => $row['id'],
									'USERNAME_FULL'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], $user->lang['GUEST']),
									'SUBJECT'		=> htmlspecialchars_decode($row['subject']),
									'TIME'			=> $user->format_date($row['time']),
									'DISPLAY_IT'	=> ($row['display_it']) ? true : false,
									'U_VIEW'		=> $this->u_action . '&amp;action=view&amp;mail=' . $row['id'],
									'U_DISPLAY'		=> $this->u_action . '&amp;action=display&amp;mail=' . $row['id'] . '&amp;display='. (($row['display_it']) ? '0' : '1'),
									'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;mail=' . $row['id']
								));
							}
						}
					
						$template->assign_vars(array(
								'S_SEND'				=> false,
								'S_GEN'					=> true,)
							);
					break;
				
				}
					
			break;
			
			// Create a new email
			case 'email':

				// Do the job ...
				if ($submit)
				{
					// Error checking needs to go here ... if no subject and/or no message then skip
					// over the send and return to the form
					$use_queue		= (isset($_POST['send_immediately'])) ? false : true;
					$priority		= request_var('mail_priority_flag', MAIL_NORMAL_PRIORITY);
					$display_it		= (isset($_POST['display_it'])) ? true : false;

					if (!check_form_key($form_key))
					{
						$error[] = $user->lang['FORM_INVALID'];
					}

					if (!$subject)
					{
						$error[] = $user->lang['NO_EMAIL_SUBJECT'];
					}

					if (!$message)
					{
						$error[] = $user->lang['NO_EMAIL_MESSAGE'];
					}

					if (!sizeof($error))
					{
						if ($usernames)
						{
							// If giving usernames the admin is able to email inactive users too...
							$sql = 'SELECT username, user_email, user_jabber, user_notify_type, user_lang
								FROM ' . USERS_TABLE . '
								WHERE ' . $db->sql_in_set('username_clean', array_map('utf8_clean_string', explode("\n", $usernames))) . '
									AND user_allow_massemail = 1
								ORDER BY user_lang, user_notify_type'; // , SUBSTRING(user_email FROM INSTR(user_email, '@'))
						}
						else
						{
							if ($group_id)
							{
								$sql = 'SELECT u.user_email, u.username, u.username_clean, u.user_lang, u.user_jabber, u.user_notify_type
									FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . ' ug
									WHERE ug.group_id = ' . $group_id . '
										AND ug.user_pending = 0
										AND u.user_id = ug.user_id
										AND u.user_allow_massemail = 1
										AND u.user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')
									ORDER BY u.user_lang, u.user_notify_type';
							}
							else
							{
								$sql = 'SELECT username, username_clean, user_email, user_jabber, user_notify_type, user_lang
									FROM ' . USERS_TABLE . '
									WHERE user_allow_massemail = 1
										AND user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')
									ORDER BY user_lang, user_notify_type';
							}
						}
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);

						if (!$row)
						{
							$db->sql_freeresult($result);
							trigger_error($user->lang['NO_USER'] . adm_back_link($this->u_action), E_USER_WARNING);
						}
			
						$j = 0;

						// Send with BCC, no more than 50 recipients for one mail (to not exceed the limit)
						$max_chunk_size = 50;
						$email_list = array();
						$old_lang = $row['user_lang'];
						$old_notify_type = $row['user_notify_type'];

						do
						{
							if (($row['user_notify_type'] == NOTIFY_EMAIL && $row['user_email']) ||
								($row['user_notify_type'] == NOTIFY_IM && $row['user_jabber']) ||
								($row['user_notify_type'] == NOTIFY_BOTH && $row['user_email'] && $row['user_jabber']))
							{
								$email_list[$j]['lang']		= $row['user_lang'];
								$email_list[$j]['method']	= $row['user_notify_type'];
								$email_list[$j]['email']	= $row['user_email'];
								$email_list[$j]['name']		= $row['username'];
								$email_list[$j]['jabber']	= $row['user_jabber'];
								$j++;
							}
						}
						while ($row = $db->sql_fetchrow($result));
						$db->sql_freeresult($result);

						// Send the messages
						include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
						include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
						$messenger = new messenger($use_queue);

						$errored = false;

						for ($i = 0, $size = sizeof($email_list); $i < $size; $i++)
						{
							$used_lang = $email_list[$i]['lang'];
							$used_method = $email_list[$i]['method'];

							$email_row = $email_list[$i];

							$messenger->{'to'}($email_row['email'], $email_row['name']);
							$messenger->im($email_row['jabber'], $email_row['name']);

							$messenger->set_mail_html(true);
							$messenger->template('admin_send_email_html', $used_lang);

							$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
							$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
							$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
							$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);
					
							$messenger->subject(htmlspecialchars_decode(str_replace("{USERNAME}", $email_row['name'], $subject)));
							$messenger->set_mail_priority($priority);
				
							$messenger->assign_vars(array(
								'MESSAGE'		=> htmlspecialchars_decode(str_replace("{USERNAME}", $email_row['name'], $message)))
							);
			
							if (!($messenger->send($used_method)))
							{
								$errored = true;
							}
						}
						unset($email_list);

						$messenger->save_queue();

						if ($usernames)
						{
							$usernames = explode("\n", $usernames);
							add_log('admin', 'LOG_MASS_EMAIL', implode(', ', utf8_normalize_nfc($usernames)));
						}
						else
						{
							if ($group_id)
							{
								$group_name = get_group_name($group_id);
							}
							else
							{
								// Not great but the logging routine doesn't cope well with localising on the fly
								$group_name = $user->lang['ALL_USERS'];
							}

							add_log('admin', 'LOG_MASS_EMAIL', $group_name);
						}

						if (!$errored)
						{
							$sql_ary = array(
								'user_id'			=> $user->data['user_id'],
								'username'			=> $user->data['username'],
								'user_colour'		=> $user->data['user_colour'],
								'subject'			=> $subject,
								'message'			=> $message,
								'display_it'		=> $display_it,
								'time'				=> time(),
							);
							
							$sql = 'INSERT INTO ' . MAIL_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
							$result = $db->sql_query($sql);
							
							$message = ($use_queue) ? $user->lang['EMAIL_SENT_QUEUE'] : $user->lang['EMAIL_SENT'];
							trigger_error($message . adm_back_link($this->u_action));
						}
						else
						{
							$message = sprintf($user->lang['EMAIL_SEND_ERROR'], '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", 'i=logs&amp;mode=critical') . '">', '</a>');
							trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
						}
					}
				}

				// Exclude bots and guests...
				$sql = 'SELECT group_id
					FROM ' . GROUPS_TABLE . "
					WHERE group_name IN ('BOTS', 'GUESTS')";
				$result = $db->sql_query($sql);

				$exclude = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$exclude[] = $row['group_id'];
				}
				$db->sql_freeresult($result);

				$select_list = '<option value="0"' . ((!$group_id) ? ' selected="selected"' : '') . '>' . $user->lang['ALL_USERS'] . '</option>';
				$select_list .= group_select_options($group_id, $exclude);
				
				$s_priority_options = '<option value="' . MAIL_LOW_PRIORITY . '">' . $user->lang['MAIL_LOW_PRIORITY'] . '</option>';
				$s_priority_options .= '<option value="' . MAIL_NORMAL_PRIORITY . '" selected="selected">' . $user->lang['MAIL_NORMAL_PRIORITY'] . '</option>';
				$s_priority_options .= '<option value="' . MAIL_HIGH_PRIORITY . '">' . $user->lang['MAIL_HIGH_PRIORITY'] . '</option>';

				$template->assign_vars(array(
					'S_SEND'				=> true,
					'S_WARNING'				=> (sizeof($error)) ? true : false,
					'WARNING_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '',
					'U_ACTION'				=> $this->u_action,
					'S_GROUP_OPTIONS'		=> $select_list,
					'USERNAMES'				=> $usernames,
					'U_FIND_USERNAME'		=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=acp_email&amp;field=usernames'),
					'SUBJECT'				=> $subject,
					'MESSAGE'				=> $message,
					'S_PRIORITY_OPTIONS'	=> $s_priority_options)
				);
			break;
		}
	}
}

?>