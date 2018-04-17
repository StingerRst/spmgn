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
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);

$user->setup('viewforum');

// If users such as bots don't have permission to view any events
// you don't want them wasting time in the calendar...
// Is the user able to view ANY events?
/*if ( !$auth->acl_get('u_calendar_view_events') )
{
	trigger_error( 'NO_AUTH_OPERATION' );
}
*/
if( !$user->data['is_bot'] && $user->data['user_id'] != ANONYMOUS )
{
}
if ( !$user->data['is_organizer'] )
{
	trigger_error( 'NOT_AUTHORISED' );
}

/**
* All of your coding will be here, setting up vars, database selects, inserts, etc...
*/
$id = request_var('i',0);
$mode = request_var('mode', '');

switch( $mode )
{
	case "start":
		$rid = request_var('rid',0);
		$db->sql_query('UPDATE ' . RESERVS_TABLE . '
			SET status = 2
			WHERE reserv_id = ' . $rid);
		redirect(append_sid("{$phpbb_root_path}org_test.$phpEx",'i=1'));
	break;
	default:
		$template_body = "org_test.html";
	

}

$tabs = '<ul>
	<li'.(($id==0)?' class="activetab"':'').'><a href="'.append_sid("{$phpbb_root_path}org_test.$phpEx",'i=0').'"><span>Новые закупки и брони</span></a></li>
	<li'.(($id==1)?' class="activetab"':'').'><a href="'.append_sid("{$phpbb_root_path}org_test.$phpEx",'i=1').'"><span>Текущие закупки</span></a></li>
	<li'.(($id==2)?' class="activetab"':'').'><a href="'.append_sid("{$phpbb_root_path}viewtopic.$phpEx",'f=6&t=632').'"><span>Тех.Поддержка</span></a></li>
	<li'.(($id==3)?' class="activetab"':'').'><a href="'.append_sid("{$phpbb_root_path}viewforum.$phpEx",'f=3').'"><span>ОРГ-форум</span></a></li>
</ul>';
if (strpos($_SERVER['HTTP_REFERER'],'spmgn.ru/index.php')){
	$template->assign_var('IFRAME', 1);
}

switch( $id )
{
	case 0:
		$file = 'org_reservs';
		break;
	case 1:
		switch( $mode )
		{
		case "edit":
			$file = 'org_catalog_edit';
			$edit = 1;
			break;
		case "view":
			$file = 'org_catalog_edit';
			$edit = 0;
			break;
		case "otchet":
			$file = 'org_otchet_test';
			break;
		case "otchet2":
			$file = 'org_otchet2';
			break;
		default:
			$file = 'org_purchases_test';
		}
		
		break;
	default:
		// Set the filename of the template you want to use for this file.
		$template->set_filenames(array(
			'body' => $template_body) // template file name -- See Templates Documentation
		);
	
}

if ($file)
	require_once ('./includes/org/'.$file.'.'.$phpEx);

$template->assign_vars(array(
		'TABS' 		=> $tabs,
		)
	);


// Output the page
page_header($user->lang['PAGE_TITLE']); // Page title, this language variable should be defined in the language file you setup at the top of this page.



// Finish the script, display the page
page_footer();


?>