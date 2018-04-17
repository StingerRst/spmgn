<?php
/**
 * @package     Joomla.Tutorials
 * @subpackage  Module
 * @copyright   (C) 2012 http://jomla-code.ru
 * @license     License GNU General Public License version 2 or later; see LICENSE.txt
 */ 
 
// No direct access.
defined('_JEXEC') or die('(@)|(@)');
echo ('hello_helper.php');

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
$phpbb_root_path = dirname(__FILE__).'/';
echo ($phpbb_root_path . 'common.' . $phpEx);

include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

class modHelloworldHelper
{
}
?>