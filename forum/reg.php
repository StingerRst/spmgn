<?php
if (!defined('_JEXEC'))
{
    // Initialize Joomla framework
    define('_JEXEC', 1);
}

@ini_set('zend.ze1_compatibility_mode', '0');
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('JPATH_BASE', '/var/www/html/spmgn.ru/forum/..' );//this is when we are in the root
define( 'DS', DIRECTORY_SEPARATOR );
 
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
require_once ( JPATH_BASE .DS.'components'.DS.'com_ulogin'.DS.'controller.php' );
 
$mainframe =& JFactory::getApplication('site');
$mainframe->initialise();

$log='/var/www/html/spmgn.ru/forum/logs/debug.log';
$a=array ("45","dfgh","yyy");
error_log (var_export ($a,true)."\n",3,$log);



echo ('end');



/**
 * Регистрация пользователя в БД
 * @return mixed
 */
function regUser($u_data){
	
	$users_model = $this->getModel('Registration');

	//$u_data = $this->u_data;

	$login = $this->generateNickname($u_data['first_name'],$u_data['last_name'],$u_data['nickname'],$u_data['bdate']);

	$CMSuser = array(
		'name' => $login,
		'username' => $login,
		'email' => $u_data['email'],
		'verified_email' => isset($u_data["verified_email"]) ? $u_data["verified_email"] : -1,
	);

	jimport('joomla.application.component.helper');
	$groupId = JComponentHelper::getParams('com_ulogin')->get('group');

	if (!empty($groupId)) {
		$groups[] = JComponentHelper::getParams('com_users')->get('new_usertype');
		$groups[] = $groupId;
		$CMSuser['groups'] = $groups;
	}

	$res = $users_model->register($CMSuser);

	if (intval($res) > 0) {

		if (JComponentHelper::isEnabled('com_k2' , true)) {

			JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'tables');
			$row = JTable::getInstance('K2User', 'Table');

			$row->set('url', isset( $u_data["profile"] ) ? $u_data["profile"] : '');

			if ( isset( $u_data['sex'] ) ) {
				if ( $u_data['sex'] == 1 ) {
					$row->set('gender', 'f');
				} elseif ( $u_data['sex'] == 2 ) {
					$row->set('gender', 'm');
				}
			}

			$file_url = ( !empty( $u_data['photo_big'] ) )
				? $u_data['photo_big']
				: ( !empty( $u_data['photo'] )  ? $u_data['photo'] : '' );

			$row->set('image', $this->model->uploadAvatarK2($file_url));

			$row->store();
		}

		return $res;

	} elseif ($res === 'adminactivate') {
		$this->sendMessage (array(
			'title' => "",
			'msg' => JText::_('COM_USERS_REGISTRATION_COMPLETE_VERIFY'),
			'type' => 'warning'
		));
		return false;
	} elseif ($res === 'useractivate') {
		$this->sendMessage (array(
			'title' => "",
			'msg' => JText::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE'),
			'type' => 'warning'
		));
		return false;
	} else {
		$this->sendMessage (array(
			'title' => "Ошибка при регистрации.",
			'msg' => "Произошла ошибка при регистрации пользователя.",
			'type' => 'error'
		));
		return false;
	}
}
?>