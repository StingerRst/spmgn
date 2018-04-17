<?php
/**
*
* @package Super Quick Reply
* @version 1.0.0 of 05.04.2009
* @copyright (c) By Shapoval Andrey Vladimirovich (AllCity) ~ http://allcity.net.ru/
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE (IN_PHPBB)!
*/
if (!defined('IN_PHPBB'))
{
  exit;
}

/**
* DO NOT CHANGE ANYTHING BELOW THIS LINE (The exception SETTINGS)!
*/
class SuperQuickReply
{
/**
* @param int $topic_id
* @param int $forum_id
* @param array $topic_data
**/
function SuperQuickReply($topic_id, $forum_id, &$topic_data)
  {
    global $db, $template, $user, $config, $auth, $phpbb_root_path, $phpEx;

      // SETTINGS
      $active = true; // Enable Super Quick Reply?
      $color_nick = true; // Enable Colorize nicknames in Super Quick Reply?
      $enable_bbcode_box = true; // Enable BBCodes box in Super Quick Reply?
      $enable_smile_box = true; // Enable Smilse box in Super Quick Reply?
      $custom_bbcodes = false; // Enable custom BBCodes in Super Quick Reply?
      $quote = true; // Enable quote in Super Quick Reply?

    // Check of this user can post reply in topic...
    if (!$active || !$auth->acl_get('f_reply', $forum_id) || $user->data['is_bot'] || $topic_data['topic_status'] == ITEM_LOCKED && !$auth->acl_get('m_lock', $forum_id))
    {
      return;
    }

    // Language file
    $user->add_lang('posting');
    
    // Page title and action URL, include session_id for security purpose
    $s_action = append_sid("{$phpbb_root_path}posting.$phpEx", false, true, $user->session_id);
    add_form_key('posting');
    
    // Hidden fields
    $s_hidden_fields = array(
      'mode' => 'reply',
      'f' => $forum_id,
      't' => $topic_id,
      'icon' => 0,
      'lastclick' => time(),
      'topic_cur_post_id' => $topic_data['topic_last_post_id']
    );
    
    //-- mod: Prime Anti-bot ----------------------------------------------------//
    if (!empty($config['prime_captcha_post']) && !$user->data['is_registered'])
    {
      if (!class_exists('prime_captcha'))
      {
        include($phpbb_root_path.'includes/prime_captcha.'.$phpEx);
      }
      $prime_captcha->handle_captcha();
      $s_hidden_fields = $s_hidden_fields + (isset($prime_captcha->fields) ? $prime_captcha->fields : array());
    }
    //-- end: Prime Anti-bot ----------------------------------------------------//
    
    // Confirmation code handling (stolen from posting.php)
    if ($config['enable_post_confirm'] && !$user->data['is_registered'])
    {
      // Show confirm image
      $sql = 'DELETE FROM '.CONFIRM_TABLE."
      WHERE session_id = '".$db->sql_escape($user->session_id)."'
      AND confirm_type = ".CONFIRM_POST;
      $db->sql_query($sql);

      // Generate code
      $code = gen_rand_string(mt_rand(5, 8));
      $confirm_id = md5(unique_id($user->ip));
      $seed = hexdec(substr(unique_id(), 4, 10));

      // compute $seed % 0x7fffffff
      $seed -= 0x7fffffff * floor($seed / 0x7fffffff);

      $sql = 'INSERT INTO '.CONFIRM_TABLE.' '.$db->sql_build_array('INSERT', array(
        'confirm_id' => (string) $confirm_id,
        'session_id' => (string) $user->session_id,
        'confirm_type' => (int) CONFIRM_POST,
        'code' => (string) $code,
        'seed' => (int) $seed)
      );
      $db->sql_query($sql);

      $template->assign_vars(array(
        'S_CONFIRM_CODE' => true,
        'CONFIRM_ID' => $confirm_id,
        'CONFIRM_IMAGE' => '<img src="'.append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=confirm&amp;id='.$confirm_id.'&amp;type='.CONFIRM_POST).'" alt="" title="" />',
        'L_POST_CONFIRM_EXPLAIN' => sprintf($user->lang['POST_CONFIRM_EXPLAIN'], '<a href="mailto:'.htmlspecialchars($config['board_contact']).'">', '</a>')
      ));
    }

    // HTML, BBCode, Smilies, Images and Flash status (stolen from posting.php)
    $bbcode_status = ($enable_bbcode_box && $config['allow_bbcode'] && $auth->acl_get('f_bbcode', $forum_id)) ? true : false;
    $smilies_status = ($enable_smile_box && $config['allow_smilies'] && $auth->acl_get('f_smilies', $forum_id)) ? true : false;
    $img_status = ($bbcode_status && $auth->acl_get('f_img', $forum_id)) ? true : false;
    $url_status = ($config['allow_post_links']) ? true : false;
    $flash_status = ($bbcode_status && $auth->acl_get('f_flash', $forum_id) && $config['allow_post_flash']) ? true : false;
    $quote_status = ($auth->acl_get('f_reply', $forum_id)) ? true : false;
    $topic_lock = (isset($_POST['lock_topic'])) ? true : false;

    // Build custom bbcodes array
    if($bbcode_status && $custom_bbcodes)
    {
      display_custom_bbcodes();
    }

    // Generate smiley listing
    if($smilies_status)
    {
      include $phpbb_root_path.'includes/functions_posting.'.$phpEx;
      generate_smilies('inline', $forum_id);
    }

    // Notify topic check
    $notify_checked = ($config['allow_topic_notify'] && $user->data['is_registered']);
    if ($notify_checked)
      {
      // If user does not subscribe by default, then check if they've subscribed to the topic
      if (!($notify_checked = $user->data['user_notify']))
      {
        $sql = 'SELECT topic_id
        FROM '.TOPICS_WATCH_TABLE.'
        WHERE topic_id = '.$topic_id.'
        AND user_id = '.$user->data['user_id'];
        $result = $db->sql_query($sql);
        $notify_checked = (int) $db->sql_fetchfield('topic_id');
        $db->sql_freeresult($result);
      }
    }

    // Send vars to template
    $template->assign_vars(array(
    // Global form
    'S_SQR_ACTIVE' => $active,
    'S_SQR_POST_ACTION' => $s_action,
    'S_SQR_SUBJECT' => ((strpos($topic_data['topic_title'], 'Re: ') !== 0) ? 'Re: ' : '').censor_text($topic_data['topic_title']),
    'S_SQR_HIDDEN_FIELDS' => build_hidden_fields($s_hidden_fields),
    'S_SQR_COLOR_NICK' => $color_nick,
    'S_SQR_QUOTE' => ($quote && $bbcode_status && $quote_status) ? true: false,

    // CHECKED
    'S_NOTIFY_CHECKED' => ($notify_checked) ? ' checked="checked"' : '',
    'S_SIGNATURE_CHECKED' => (($config['allow_sig'] && $user->optionget('attachsig')) ? true: false) ? ' checked="checked"' : '',
    'S_BBCODE_CHECKED' => (($config['allow_bbcode'] && $user->optionget('bbcode')) ? false : true) ? ' checked="checked"' : '',
    'S_SMILIES_CHECKED' => (($config['allow_smilies'] && $user->optionget('smilies')) ? false : true) ? ' checked="checked"' : '',
    'S_LOCK_TOPIC_CHECKED' => ((isset($topic_lock) && $topic_lock) ? $topic_lock : (($topic_data['topic_status'] == ITEM_LOCKED) ? 1 : 0)) ? ' checked="checked"' : '',
    'S_MAGIC_URL_CHECKED' => (!(isset($_POST['disable_magic_url'])) ? 0 : 1) ? ' checked="checked"' : '',

    // IF TAMPLATE ZONE
    'S_BBCODE_ALLOWED' => $bbcode_status,
    'S_SMILIES_ALLOWED' => $smilies_status,
    'S_LINKS_ALLOWED' => $url_status,
    'S_SIG_ALLOWED' => ($auth->acl_get('f_sigs', $forum_id) && $config['allow_sig'] && $user->data['is_registered']) ? true : false,
    'S_NOTIFY_ALLOWED' => ($user->data['is_registered'] && $config['allow_topic_notify'] && $config['email_enable']),
    'S_LOCK_TOPIC_ALLOWED' => (($auth->acl_get('m_lock', $forum_id) || ($auth->acl_get('f_user_lock', $forum_id) && $user->data['is_registered'] && !empty($topic_data['topic_poster']) && $user->data['user_id'] == $topic_data['topic_poster'] && $topic_data['topic_status'] == ITEM_UNLOCKED))) ? true : false,

    // For posting_buttons.html
    'S_BBCODE_IMG' => $img_status,
    'S_BBCODE_URL' => $url_status,
    'S_BBCODE_FLASH' => $flash_status,
    'S_BBCODE_QUOTE' => $quote_status,
    'S_EDIT_DRAFT' => false
    ));
  }
}
/*
* FILE OF Super Quick Reply END.
**/
?>