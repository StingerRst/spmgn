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
//catalog

		$sql = 'SELECT catalog_id,catalog_properties
			FROM '. CATALOGS_TABLE;
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result)){

			$urls= unserialize($row['catalog_properties']);

			if (is_array($urls)){
			foreach ($urls as $k=>$v){
				$iv='';
				foreach ($v as $vv)
				$iv.=$vv.';';
				$var[trim(str_replace(array('\\"','\\\'','\'','"'), '`',$k))]=trim(str_replace(array('\\"','\\\'','\'','"'), '',$iv));
			}}
			else
				$var='';
			$sql2 = 'UPDATE '. CATALOGS_TABLE .' SET
				catalog_properties = \''. serialize($var) .'\'
				WHERE catalog_id = '. $row['catalog_id'];
			$result2 = $db->sql_query($sql2);
		}

//lot

		$sql = 'SELECT lot_id,lot_properties
			FROM '. LOTS_TABLE;
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result)){

			$urls= unserialize($row['lot_properties']);

			if (is_array($urls)){
			foreach ($urls as $k=>$v){
				$iv='';
				foreach ($v as $vv)
				$iv.=$vv.';';
				$var[trim(str_replace(array('\\"','\\\'','\'','"'), '`',$k))]=trim(str_replace(array('\\"','\\\'','\'','"'), '',$iv));
			}}
			else
				$var='';
			$sql2 = 'UPDATE '. LOTS_TABLE .' SET
				lot_properties = \''. serialize($var) .'\'
				WHERE lot_id = '. $row['lot_id'];
			$result2 = $db->sql_query($sql2);
		}

		$sql = 'SELECT lot_id,lot_bundle
			FROM '. LOTS_TABLE;
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result)){

			$urls= unserialize($row['lot_bundle']);

			if (is_array($urls)){
			foreach ($urls as $k=>$v){
				$iv='';
				foreach ($v as $vv)
				$iv.=$vv.';';
				$var[trim(str_replace(array('\\"','\\\'','\'','"'), '`',$k))]=trim(str_replace(array('\\"','\\\'','\'','"'), '',$iv));
			}}
			else
				$var='';
			$sql2 = 'UPDATE '. LOTS_TABLE .' SET
				lot_bundle = \''. serialize($var) .'\'
				WHERE lot_id = '. $row['lot_id'];
			$result2 = $db->sql_query($sql2);
		}

		$sql = 'SELECT lot_id, lot_name
			FROM '. LOTS_TABLE.'
			WHERE lot_name LIKE \'%\\\\\\\\\"%\'';
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result)){
			$name=str_replace(array('\"'), '"',$row['lot_name']);
			$sql2 = 'UPDATE '. LOTS_TABLE .' SET
				lot_name = \''. $db->sql_escape($name) .'\'
				WHERE lot_id = '. $row['lot_id'];
			$result2 = $db->sql_query($sql2);
		}

echo "end.";


?>