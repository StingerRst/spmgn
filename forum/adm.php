<?php
/**
*
* @author alightner
*
* @package phpBB Calendar
* @version CVS/SVN: $Id: $
* @copyright (c) 2009 alightner
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true); // we tell the page that it is going to be using phpBB, this is important.
$phpbb_root_path = './'; // See phpbb_root_path documentation
$phpEx = substr(strrchr(__FILE__, '.'), 1); // Set the File extension for page-wide usage.
include($phpbb_root_path . 'common.' . $phpEx); // include the common.php file, this is important, especially for database connects.
//include($phpbb_root_path . 'includes/functions_calendar.' . $phpEx); // contains the functions that "do the work".

// Start session management -- This will begin the session for the user browsing this page.
$user->session_begin();
$auth->acl($user->data);

// Language file (see documentation related to language files)
$user->setup('adm');

// If users such as bots don't have permission to view any events
// you don't want them wasting time in the calendar...
// Is the user able to view ANY events?
if (!$auth->acl_get('a_'))
{
	trigger_error('NO_ADMIN');
}

if( !$user->data['is_bot'] && $user->data['user_id'] != ANONYMOUS )
{
}
if (strpos($_SERVER['HTTP_REFERER'],'spmgn.ru/index.php')){
	$template->assign_var('IFRAME', 1);
}

if (isset($_POST['cmd'])){
	if ($_POST['cmd']=='creatd'){
	  	$sql = 'SELECT *
			FROM ' . DOGS_TABLE . ' WHERE user_id = '.$_POST['id'];
		$result=$db->sql_query($sql);
      	if (!($row = $db->sql_fetchrow($result))) {
		$sql = 'INSERT INTO '.DOGS_TABLE.'
				(user_id) values ('.$_POST['id'].')';
		}
	}else{
		if ($_POST['cmd']=='yes') {
			$pending=0;
			$group=8;
			$color="0066FF";
			$user_rank=3;
		}
		if ($_POST['cmd']=='not') {
			$pending=2;
			$group=2;
			$color="";
			$user_rank=0;
		}
		$sql = 'UPDATE '.USER_GROUP_TABLE.'
				SET user_pending='.$pending.'
				WHERE user_id='.$_POST['id'].' AND group_id=8';
		$db->sql_query($sql);
		$sql = 'UPDATE '.USERS_TABLE.'
				SET group_id="'.$group.'"
				, user_colour="'.$color.'"
				, user_rank="'.$user_rank.'"
				WHERE user_id='.$_POST['id'];
		$db->sql_query($sql);
		
		$auth->acl_clear_prefetch(array($_POST['id']));
		//group_update_listings(8);

	}
	$result=$db->sql_query($sql);
} 

/**
* All of your coding will be here, setting up vars, database selects, inserts, etc...
*/
$id = request_var('i',0);
$mode = request_var('mode', '');


$tabs = '<ul>
	<li'.(($id==3)?' class="activetab"':'').'><a href="'.append_sid("{$phpbb_root_path}adm.$phpEx",'i=3').'"><span>Организаторы</span></a></li>
	<li'.(($id==0)?' class="activetab"':'').'><a href="'.append_sid("{$phpbb_root_path}adm.$phpEx").'"><span>Новые закупки и брони</span></a></li>
	<li'.(($id==1)?' class="activetab"':'').'><a href="'.append_sid("{$phpbb_root_path}adm.$phpEx",'i=1').'"><span>Текущие закупки</span></a></li>
	<li'.(($id==2)?' class="activetab"':'').'><a href="'.append_sid("{$phpbb_root_path}adm.$phpEx",'i=2').'"><span>Настройки</span></a></li>
	<li'.(($id==4)?' class="activetab"':'').'><a href="'.append_sid("{$phpbb_root_path}adm.$phpEx",'i=4').'"><span>Бренды</span></a></li>
	<li'.(($id==5)?' class="activetab"':'').'><a href="'.append_sid("{$phpbb_root_path}adm.$phpEx",'i=5').'"><span>Тест</span></a></li>
</ul>';

switch( $id )
{
	case 5:
		$file = 'adm_purchases2';
		break;
	case 3:
		$file = 'adm_org';
		break;
	case 2:
		$file = 'adm_nstr';
		break;
	case 0:
		$file = 'adm_reservs';
		break;
	case 4:
		$file = 'adm_brend';
		break;
	case 1:
		switch( $mode )
		{
		case "edit":
			$file = 'adm_catalog_edit';
			$edit = 1;
			break;
		case "view":
			$file = 'adm_catalog_edit';
			$edit = 0;
			break;
		case "otchet":
			$file = 'adm_otchet';
			break;
		default:
			$file = 'adm_purchases';
		}
		
		break;
	default:
		// Set the filename of the template you want to use for this file.
		$template->set_filenames(array(
			'body' => $template_body) // template file name -- See Templates Documentation
		);
	
}

if ($file)
	require_once ('./includes/adm/'.$file.'.'.$phpEx);

$template->assign_vars(array(
		'TABS' 		=> $tabs,
		)
	);


// Output the page
page_header($user->lang['PAGE_TITLE']); // Page title, this language variable should be defined in the language file you setup at the top of this page.



// Finish the script, display the page
page_footer();


?>
