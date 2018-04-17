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
/**
if ( !$user->data['is_organizer'] )
{
	trigger_error( 'NOT_AUTHORISED' );
}

/**
* All of your coding will be here, setting up vars, database selects, inserts, etc...
*/
$id = request_var('i',0);
$idu = request_var('u',0);
$mode = request_var('mode', '');

if (($idu)and(!$auth->acl_get('a_')))
{
	trigger_error('Доступ закрыт');
}
if ($idu==0) $idu=$user->data['user_id'];

$template_body = "dog.html";

	$sql = 'SELECT * FROM 
		'. DOGS_TABLE .' 
		WHERE user_id = '.$idu;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);

if (!$row)
	trigger_error( 'Договор не найден' );

if (($_POST['username'])and($row['dg_activ']=='0')){
	$parm['name'] = $_POST['username'];
	$parm['pasp'] = $_POST['userpas'];
	$parm['adrs'] = $_POST['useradr'];
	$parm['phon'] = $_POST['userphone'];
	$parm['pho2'] = $_POST['userphone2'];
	$parm = serialize($parm);
	$sql = "UPDATE  ". DOGS_TABLE ." SET
		dg_param='".$parm."', dg_date='".time()."', dg_activ=1
		WHERE dg_id = ".$row['dg_id'];
	$result = $db->sql_query($sql);
}

	$sql = 'SELECT * FROM 
		'. DOGS_TABLE .' 
		WHERE user_id = '.$idu;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);


	if ($row['dg_activ']==1){
		$date = unserialize($row['dg_param']);
		$date['date'] = date("\"d\" F Yг.",$row['dg_date']);
	}else{
		$date['name'] = utf8_normalize_nfc(request_var('realname', $user->data['user_realname'], true));
		$date['pasp'] = utf8_normalize_nfc(request_var('pasportdata_number', $user->data['user_pasportdata_number'], true));
		$date['adrs'] = utf8_normalize_nfc(request_var('pasportdata_addres', $user->data['user_pasportdata_addres'], true));
		$date['phon'] = utf8_normalize_nfc(request_var('phone', $user->data['user_phone'], true));
		$date['pho2'] = '';
		$date['date'] = date("\"d\" F Yг.");
	}
switch( $id )
{
//	case 0:
//		$file = 'dog_main';
//		break;
	case 1:
		$template->set_filenames(array(
			'body' => 'dog1.html') // template file name -- See Templates Documentation
		);
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
		'ID'		  				=> $row['dg_id'],
		'ACTIV'		  				=> $row['dg_activ'],
		'DATA'		  				=> $date['date'],
		'REALNAME'  				=> $date['name'],
		'PHONE'     				=> $date['phon'],
		'PHONE2'     				=> $date['pho2'],
		'PASPORTDATA_NUMBER' 		=> $date['pasp'],
//		'PASPORTDATA_ISSUEDBY' 		=> utf8_normalize_nfc(request_var('pasportdata_issuedby', $user->data['user_pasportdata_issuedby'], true)),
//		'PASPORTDATA_ISSUEDDATE' 	=> utf8_normalize_nfc(request_var('pasportdata_issueddate', $user->data['user_pasportdata_issueddate'], true)),
		'PASPORTDATA_ADDRES'    	=> $date['adrs']
	));
// Output the page
if (request_var('view','')=='print'){
	$template->assign_vars(array(
		'ACTIV'		  				=> 2
	));
	$template->set_filenames(array(
		'body' => 'dog_print.html')); 
	
}
page_header($user->lang['PAGE_TITLE']); // Page title, this language variable should be defined in the language file you setup at the top of this page.



// Finish the script, display the page
page_footer();


?>