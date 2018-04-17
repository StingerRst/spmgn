<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
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
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

if ($user->data['user_id'] == ANONYMOUS) login_box('', $user->lang['LOGIN']);

// Request variables
$view = request_var('view', 'day');
$selectday = request_var('selectday', date('d-m-Y'));

$day = date('d', strtotime($selectday));
$month = date('m', strtotime($selectday));
$year = date('Y', strtotime($selectday));

$day_of_week = date('w', strtotime($selectday));

$month_names=array(
	'01' => array (
		'month_name' => 'Январь',
	  'tinimonth' => 'янв',
	  'months' => 'января',
	),
	'02' => array (
	  	'month_name' => 'Февраль',
	  	'tinimonth' => 'фев',
	  	'months' => 'февраля',
	),
	'03' => array (
	  	'month_name' => 'Март',
	  	'tinimonth' => 'мар',
	  	'months' => 'марта',
	),
	'04' => array (
	  	'month_name' => 'Апрель',
	  	'tinimonth' => 	'апр',
	  	'months' => 'апреля',
	),
	'05' => array (
	  	'month_name' => 'Май',
	  	'tinimonth' => 'май',
	  	'months' => 'мая',
	),
	'06' => array (
	  	'month_name' => 'Июнь',
	  	'tinimonth' => 'июн',
	  	'months' => 'июня',
	),
	'07' => array (
	  	'month_name' => 'Июль',
	  	'tinimonth' => 'июл',
	  	'months' => 'Июля',
	),
	'08' => array (
	  	'month_name' => 'Август',
	  	'tinimonth' => 'авг',
	  	'months' => 'августа',
	),
	'09' => array (
	  	'month_name' => 'Сентябрь',
	  	'tinimonth' => 'сен',
	  	'months' => 'сентября',
	),
	'10' => array (
	  	'month_name' => 'Октябрь',
	  	'tinimonth' => 'окт',
	  	'months' => 'Октября',
	),
	'11' => array (
	  	'month_name' => 'Ноябрь',
	  	'tinimonth' => 'ноя',
	  	'months' => 'ноября',
	),
	'12' => array (
		'month_name' => 'Декабрь',
	  'tinimonth' => 'дек',
	  'months' => 'декабря',
	),
);

$template->assign_vars (array(
	'MONTH'	=> $month_names[$month]['month_name'],
	'U_EVENTS_CALENDAR'	=>	'event_calendar.php',
	'VIEW'	=>	$view,
	'SELECTDAY'	=> $selectday,
	'DAY'		=>	$day,
	'YEAR'	=>	$year,
	'NEXT_MONTH'	=>	($month<12)?('1-'.($month+1).'-'.$year):($day.'-01-'.($year+1)),
	'PREV_MONTH'	=>	($month>1)?('1-'.($month-1).'-'.$year):($day.'-12-'.($year-1)),
));
$template->assign_vars (array(
	'NEW_EVENT_IMG' => './images/events/button_new_ev.gif',
	'S_EVENT_ACTION' => 'events.php',
));

	$sql = 'SELECT * 
		FROM ' . USER_GROUP_TABLE . '
		WHERE user_id = ' . $user->data['user_id']. '
		AND (group_id = 5 or 8)';
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result); 
/*
if (is_array($row)){
$template->assign_vars (array(
	'CALENDAR_VIEW_OPTIONS' => ($auth->acl_get('a_')||$user->data['is_organizer'])?true:false,
));
}
*/
// Make calendar
$template->assign_vars (array(
	'CALENDAR_VIEW_OPTIONS' => 1,
));

$calendar_body='';
$dayofmonth=date('t', strtotime($selectday));
// вычисляем день недели первого числа выбраного месяца
$d0 = array('1', date('m',strtotime($selectday)), date('Y',strtotime($selectday)));
$d1 = implode("-", $d0);
$d2 = date('w',strtotime($d1));
if ($d2==0) { // Если воскресенье, то корректируем номер дня недели
	$d2=7;
}

// вычисляем кол-во дней для календаря

$dayofmonthw=$dayofmonth+ $d2-1;
$weekcount=$dayofmonthw/7;
//$weekcount=($weekcount>4)?5:4;   эта строка уже не нужна
$day_count=1;

for ($i = 0; $i < $weekcount; $i++) {
	$calendar_body.='<tr>';
	for($j = 0; $j < 7; $j++) {
		if ($day_count>$dayofmonth) break;
		$dayofweek = date('w', mktime(0, 0, 0, $month, $day_count, $year));
	  $dayofweek = $dayofweek - 1;
	  if($dayofweek == -1) $dayofweek = 6;
	  if($dayofweek == $j) {
	  	  $selected='';
	  	  $isever=0;
	  	  $undone=0;
				if (($day_count == $day) || ($day_count>($day-($day_of_week)) && $day_count<=($day+(7-$day_of_week)) && $view=='week')) $selected='select_day';
				if ($day_count.$month.$year == date('dmY', strtotime("now"))) $selected.=' today';
	  	  $sql = 'SELECT count(*)
				FROM ' . EVENTS_TABLE . '
				JOIN ' . EVENTS_TO_TABLE . '
				ON ' . EVENTS_TABLE . '.event_id = ' . EVENTS_TO_TABLE . '.event_id 
				WHERE ' . EVENTS_TO_TABLE . '.user_id = '.$user->data['user_id'].'
				AND DATE(' . EVENTS_TABLE . '.event_end) <= "'.date("Y-m-d").'"
				AND DATE(' . EVENTS_TABLE . '.event_begin) <= "'.$year."-".$month."-".$day_count.'"
				AND DATE(' . EVENTS_TABLE . '.event_end) >= "'.$year."-".$month."-".$day_count.'"';
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				if ($row['count(*)']) {
					$selected.=' isever';
					$isever=$row['count(*)'];
				}
				$sql = 'SELECT count(*)
				FROM ' . EVENTS_TABLE . '
				JOIN ' . EVENTS_TO_TABLE . '
				ON ' . EVENTS_TABLE . '.event_id = ' . EVENTS_TO_TABLE . '.event_id 
				WHERE ' . EVENTS_TO_TABLE . '.user_id = '.$user->data['user_id'].'
				AND ' . EVENTS_TO_TABLE . '.event_done = 0
				AND DATE(' . EVENTS_TABLE . '.event_begin) <= "'.$year."-".$month."-".$day_count.'"
				AND DATE(' . EVENTS_TABLE . '.event_end) >= "'.$year."-".$month."-".$day_count.'"';
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
	  		if ($row['count(*)']) {
					$selected.=' isev';
					$undone=$row['count(*)'];
				}
	  		$calendar_body.='<td class="days '.$selected.'" onclick=top.location.href="event_calendar.php?view='.$view.'&selectday='.$day_count.'-'.$month.'-'.$year.'"; title="Невыполненные:'.$undone.'  Истекшие:'.$isever.'">'.$day_count.'</td>';
      	$day_count++;
    	} else {
	      $calendar_body.='<td>&nbsp;</td>';
		}
	}
	$calendar_body.='</tr>';
}

$template->assign_vars(array(
	'CALENDAR_BODY'	=> $calendar_body
));

			$sql = 'SELECT * 
				FROM ' . EVENTS_TABLE . '
				JOIN ' . EVENTS_TO_TABLE . '
				ON ' . EVENTS_TABLE . '.event_id = ' . EVENTS_TO_TABLE . '.event_id 
				WHERE ' . EVENTS_TO_TABLE . '.user_id = '.$user->data['user_id'].'
				AND ' . EVENTS_TO_TABLE . '.event_done is NULL
				AND DATE(' . EVENTS_TABLE . '.event_end) <= "'.date("Y-m-d H:i:s").'"';
			$result = $db->sql_query($sql);
			while($row = $db->sql_fetchrow($result)) {
					$db->sql_query('UPDATE ' . EVENTS_TO_TABLE . '
					  SET event_done = 0
						WHERE user_id = ' . $user->data['user_id'] . '
						AND event_id = ' . $row['event_id']);
			}

// Make events tape && mesage block
switch ($view) {
		case 'day' :
			$template->assign_vars(array(
				'SUBSECTION_DAY'	=>	'active-subsection',
				'SELECT_FILTER'		=>	'События на '.$day.' '.$month_names[$month]['months'],
			));
			$events_tape='<span class="corners-top"><span></span></span><div id="messages_tape"><table width="100%" cellspacing="0"><tr>';
			$message_block='<span id="messages">';
			for ($i=0; $i<24; $i++){
				if ($i==date("H"))
					$events_tape.='<td  style="border: 0.2em solid #1C89EF;">'.$i.'ч.</td>';
				else
					$events_tape.='<td  style="border-bottom: 0.2em solid #1C89EF;">'.$i.'ч.</td>';
			}
			
			$sql = 'SELECT * 
				FROM ' . EVENTS_TABLE . '
				JOIN ' . EVENTS_TO_TABLE . '
				ON ' . EVENTS_TABLE . '.event_id = ' . EVENTS_TO_TABLE . '.event_id 
				JOIN ' . EVENTS_TYPE_TABLE . '
				ON ' . EVENTS_TABLE . '.event_type_id = ' . EVENTS_TYPE_TABLE . '.event_type_id 
				JOIN ' . USERS_TABLE . '
				ON ' . EVENTS_TABLE . '.author_id = ' . USERS_TABLE . '.user_id 
				WHERE ' . EVENTS_TO_TABLE . '.user_id = '.$user->data['user_id'].'
				AND DATE(' . EVENTS_TABLE . '.event_begin) <= "'.$year."-".$month."-".$day.'"
				AND DATE(' . EVENTS_TABLE . '.event_end) >= "'.$year."-".$month."-".$day.'"';
			$result = $db->sql_query($sql);
			while($row = $db->sql_fetchrow($result)) {
					$message = $row['event_text'];
					$message_parser = new parse_message($message);
					$message_parser->parse(true, true, true);
					$message_parser->format_display(true, true, true, true);
					$message = (string) $message_parser->message;
					
					if (($user->data['is_organizer'])||($auth->acl_get('a_'))){
						$message .= '<br/><br/><div>';
						$sql2 = 'SELECT * 
							FROM ' . EVENTS_TO_TABLE . '
							JOIN ' . USERS_TABLE . '
							ON ' . EVENTS_TO_TABLE . '.user_id = ' . USERS_TABLE . '.user_id 
							WHERE ' . EVENTS_TO_TABLE . '.event_id = ' . $row['event_id'] . '
							AND ' . EVENTS_TO_TABLE . '.user_id <> '.$user->data['user_id'];
						$result2 = $db->sql_query($sql2);
						while($row2 = $db->sql_fetchrow($result2)) {
							$message .= '<div style="float:left;"><nobr><span style="background-image: url(&quot;./images/events/'.$row['event_type_id'].'_'.(($row2['event_new']==1)?'color':$row2['event_done']).'.png&quot;); background-repeat: no-repeat;height:27px;padding:5px 0 5px 30px;margin: 10px;">';
							$message .= '<a href="memberlist.php?do_=188&amp;mode=viewprofile&amp;u='.$row2['user_id'].'">'.$row2['username'].'</a>';
							$message .= '</span></nobr></div>';
						}
						$message .= '</div><br/>';
					}
					// Events tape make	
					$events_tape.='<tr title="'.$row['event_type_label'].'. '.$row['event_subject'].'">';
					$fl_beg_cell=false;
					for ($i=0; $i<24; $i++){
						if (strtotime("+".$i." hour 59 minute 59 second",strtotime($selectday)) >= strtotime($row['event_begin']) && strtotime("+".$i." hour ",strtotime($selectday)) <= strtotime($row['event_end'])) {
						if (!$fl_beg_cell){
								$events_tape.='<td background="./images/events/'.$row['event_type_id'].'_tape_bg.png"><img src="./images/events/'.$row['event_type_id'].'_tape.png" /></td>';
								$fl_beg_cell = true;
							} else  $events_tape.='<td background="./images/events/'.$row['event_type_id'].'_tape_bg.png"></td>';
						}
					else $events_tape.='<td>&nbsp;</td>';
					}
					$events_tape.='</tr>';
					
					//Message block make
					$message_block.='
					<div  id="event_'.$row['event_id'].'">
					<li class="row bg1" style="display:block">
					<div><dl style="background-image: url(&quot;./images/events/'.$row['event_type_id'].'_'.(($row['event_new']==1)?'color':$row['event_done']).'.png&quot;); background-repeat: no-repeat;" class="icon">
					<dt style="width: 80%;"><a class="topictitle" href="javascript: event_showhide('.$row['event_id'].');">'.$row['event_type_label'].' '.$row['event_subject'].'</a><br />
					<a href="memberlist.php?do_=188&amp;mode=viewprofile&amp;u='.$row['user_id'].'">'.$row['username'].'</a>
					» '.date("H:i d", strtotime($row['event_begin'])).' '.$month_names[date("m", strtotime($row['event_begin']))]['months'].' - '.date("H:i d", strtotime($row['event_end'])).' '.$month_names[date("m", strtotime($row['event_end']))]['months'].'</dt>
					<dd class="mark"><input type="checkbox" id="checkbox_" name="event_ids[]" value="'.$row['event_id'].'"/></dd></dl>
					</div></li>
					<li class="row bg1" style="display:none">
					<div>
					<dl style="background-image: url(&quot;./images/events/'.$row['event_type_id'].'_'.(($row['event_new']==1)?'color':$row['event_done']).'.png&quot;); background-repeat: no-repeat;" class="icon">
					<dt style="width: 80%;"><div style="width: 100%;" class="postbody">
					<h3><a class="topictitle" href="javascript: event_showhide('.$row['event_id'].');">'.$row['event_type_label'].' '.$row['event_subject'].'</a></h3>
					<p class="author"><b>Отправитель:</b> <a href="memberlist.php?do_=188&amp;mode=viewprofile&amp;u='.$row['user_id'].'">'.$row['username'].'</a><br />
					<b>Отправлено:</b> '.date("H:i d", strtotime($row['event_time'])).' '.$month_names[date("m", strtotime($row['event_time']))]['months'].'<br /><br />
					<b>Срок действия:</b> '.date("H:i d", strtotime($row['event_begin'])).' '.$month_names[date("m", strtotime($row['event_begin']))]['months'].' - '.date("H:i d", strtotime($row['event_end'])).' '.$month_names[date("m", strtotime($row['event_end']))]['months'].'<br /></p>
					<div class="content">'.$message;
					if ($user->data['group_id']==4 || $row['user_id'] == $user->data['user_id'])
					$message_block.='
					</div></div></dt>';
//					<br><a href="events.php?mode=edit&event_id='.$row['event_id'].'">Редактирование события</a></div></div></dt>';
					$message_block.='
					<dd class="mark"><input type="checkbox" id="checkbox_" name="event_ids[]" value="'.$row['event_id'].'"></dd></dl></div>
					</li>
					</div>';
					
					// Delete new status event
					$db->sql_query('UPDATE ' . EVENTS_TO_TABLE . '
					  SET event_new = 0, event_done = NULL
						WHERE user_id = ' . $user->data['user_id'] . '
						AND event_id = ' . $row['event_id'].'
						AND event_new = 1');
			}
			
			$events_tape.='</tr></table></div><span class="corners-bottom"><span></span></span>';
			$template->assign_vars(array(
				'MESSAGE_BLOCK' => $message_block,
			));
			$template->assign_vars(array(
				'EVENTS_TAPE' => $events_tape,
			));
		break;
		case 'week' :
			$begin_of_week=($day_of_week==1)?strtotime($selectday):strtotime("last Monday",strtotime($selectday));
			$end_of_week=($day_of_week==7)?strtotime($selectday):strtotime("next Sunday",strtotime($selectday));
			$template->assign_vars(array(
				'SUBSECTION_WEEK'	=>	'active-subsection',
				'SELECT_FILTER'		=>	'События на неделю '.date("d", $begin_of_week).' '.$month_names[date("m", $begin_of_week)]['months'].' - '.date("d", $end_of_week).' '.$month_names[date("m", $end_of_week)]['months'],
			));
			$events_tape='<span class="corners-top"><span></span></span><div id="messages_tape"><table width="100%" cellspacing="0"><tr>';
			for ($i=0; $i<7; $i++){			
				if (date("dmY", strtotime("now")) == date("dmY", strtotime("+".$i." days", $begin_of_week)))
					$events_tape.='<td  style="border: 0.2em solid #1C89EF;">'.date("d", strtotime("+".($i+1)." days", $begin_of_week)).' '.$month_names[date("m", strtotime("+".$i." days", $begin_of_week))]['tinimonth'].'</td>';
				else
					$events_tape.='<td  style="border-bottom: 0.2em solid #1C89EF;">'.date("d", strtotime("+".$i." days", $begin_of_week)).' '.$month_names[date("m", strtotime("+".$i." days", $begin_of_week))]['tinimonth'].'</td>';
			}
			$events_tape.='</tr>';
			$template->assign_vars(array(
			'EVENTS_TAPE' => $events_tape,
			));
			
			$sql = 'SELECT * 
				FROM ' . EVENTS_TABLE . '
				JOIN ' . EVENTS_TO_TABLE . '
				ON ' . EVENTS_TABLE . '.event_id = ' . EVENTS_TO_TABLE . '.event_id 
				JOIN ' . EVENTS_TYPE_TABLE . '
				ON ' . EVENTS_TABLE . '.event_type_id = ' . EVENTS_TYPE_TABLE . '.event_type_id 
				JOIN ' . USERS_TABLE . '
				ON ' . EVENTS_TABLE . '.author_id = ' . USERS_TABLE . '.user_id 
				WHERE ' . EVENTS_TO_TABLE . '.user_id = '.$user->data['user_id'].'
				AND DATE(' . EVENTS_TABLE . '.event_begin) <= "'.date("Y-m-d", $end_of_week).'"
				AND DATE(' . EVENTS_TABLE . '.event_end) >= "'.date("Y-m-d", $begin_of_week).'"';
			$result = $db->sql_query($sql);
			while($row = $db->sql_fetchrow($result)) {
					$message = $row['event_text'];
					$message_parser = new parse_message($message);
					$message_parser->parse(true, true, true);
					$message_parser->format_display(true, true, true, true);
					$message = (string) $message_parser->message;
					
					if (($user->data['is_organizer'])||($auth->acl_get('a_'))){
						$message .= '<br/><br/><div>';
						$sql2 = 'SELECT * 
							FROM ' . EVENTS_TO_TABLE . '
							JOIN ' . USERS_TABLE . '
							ON ' . EVENTS_TO_TABLE . '.user_id = ' . USERS_TABLE . '.user_id 
							WHERE ' . EVENTS_TO_TABLE . '.event_id = ' . $row['event_id'] . '
							AND ' . EVENTS_TO_TABLE . '.user_id <> '.$user->data['user_id'];
						$result2 = $db->sql_query($sql2);
						while($row2 = $db->sql_fetchrow($result2)) {
							$message .= '<div style="float:left;"><nobr><span style="background-image: url(&quot;./images/events/'.$row['event_type_id'].'_'.(($row2['event_new']==1)?'color':$row2['event_done']).'.png&quot;); background-repeat: no-repeat;height:27px;padding:5px 0 5px 30px;margin: 10px;">';
							$message .= '<a href="memberlist.php?do_=188&amp;mode=viewprofile&amp;u='.$row2['user_id'].'">'.$row2['username'].'</a>';
							$message .= '</span></nobr></div>';
						}
						$message .= '</div><br/>';
					}
					// Events tape make	
					$events_tape.='<tr title="'.$row['event_type_label'].'. '.$row['event_subject'].'">';
					$fl_beg_cell=false;
					for ($i=0; $i<7; $i++){
						if (strtotime("+".($i+1)." days", $begin_of_week) >= (strtotime($row['event_begin'])+1) && strtotime("+".$i." days", $begin_of_week) <= strtotime($row['event_end'])) {
						if (!$fl_beg_cell){
								$events_tape.='<td background="./images/events/'.$row['event_type_id'].'_tape_bg.png"><img src="./images/events/'.$row['event_type_id'].'_tape.png" /></td>';
								$fl_beg_cell = true;
							} else  $events_tape.='<td background="./images/events/'.$row['event_type_id'].'_tape_bg.png"></td>';
						}
					else $events_tape.='<td>&nbsp;</td>';
					}
					$events_tape.='</tr>';
					
					//Message block make
					$message_block.='
					<div  id="event_'.$row['event_id'].'">
					<li class="row bg1" style="display:block">
					<div><dl style="background-image: url(&quot;./images/events/'.$row['event_type_id'].'_'.(($row['event_new']==1)?'color':$row['event_done']).'.png&quot;); background-repeat: no-repeat;" class="icon">
					<dt style="width: 80%;"><a class="topictitle" href="javascript: event_showhide('.$row['event_id'].');">'.$row['event_type_label'].' '.$row['event_subject'].'</a><br />
					<a href="memberlist.php?do_=188&amp;mode=viewprofile&amp;u='.$row['user_id'].'">'.$row['username'].'</a>
					» '.date("H:i d", strtotime($row['event_begin'])).' '.$month_names[date("m", strtotime($row['event_begin']))]['months'].' - '.date("H:i d", strtotime($row['event_end'])).' '.$month_names[date("m", strtotime($row['event_end']))]['months'].'</dt>
					<dd class="mark"><input type="checkbox" id="checkbox_" name="event_ids[]" value="'.$row['event_id'].'"></dd></dl>
					</div></li>
					<li class="row bg1" style="display:none">
					<div>
					<dl style="background-image: url(&quot;./images/events/'.$row['event_type_id'].'_'.(($row['event_new']==1)?'color':$row['event_done']).'.png&quot;); background-repeat: no-repeat;" class="icon">
					<dt style="width: 80%;"><div style="width: 100%;" class="postbody">
					<h3><a class="topictitle" href="javascript: event_showhide('.$row['event_id'].');">'.$row['event_type_label'].' '.$row['event_subject'].'</a></h3>
					<p class="author"><b>Отправитель:</b> <a href="memberlist.php?do_=188&amp;mode=viewprofile&amp;u='.$row['user_id'].'">'.$row['username'].'</a><br />
					<b>Отправлено:</b> '.date("H:i d", strtotime($row['event_time'])).' '.$month_names[date("m", strtotime($row['event_time']))]['months'].'<br /><br />
					<b>Срок действия:</b> '.date("H:i d", strtotime($row['event_begin'])).' '.$month_names[date("m", strtotime($row['event_begin']))]['months'].' - '.date("H:i d", strtotime($row['event_end'])).' '.$month_names[date("m", strtotime($row['event_end']))]['months'].'<br /></p>
					<div class="content">'.$message.'</div></div></dt>
					<dd class="mark"><input type="checkbox" id="checkbox_" name="event_ids[]" value="'.$row['event_id'].'"></dd></dl></div>
					</li>
					</div>';
					
					// Delete new status event
					$db->sql_query('UPDATE ' . EVENTS_TO_TABLE . '
					  SET event_new = 0
						WHERE user_id = ' . $user->data['user_id'] . '
						AND event_id = ' . $row['event_id']);
			}
			
			$events_tape.='</tr></table></div><span class="corners-bottom"><span></span></span>';
			$template->assign_vars(array(
				'MESSAGE_BLOCK' => $message_block,
			));
			$template->assign_vars(array(
				'EVENTS_TAPE' => $events_tape,
			));
		break;
		case 'all' :
				$template->assign_vars(array(
				'SUBSECTION_ALL'	=>	'active-subsection',
				'SELECT_FILTER'		=>	'Все события',
			));
			$events_tape = '';
			$sql = 'SELECT * 
				FROM ' . EVENTS_TABLE . '
				JOIN ' . EVENTS_TO_TABLE . '
				ON ' . EVENTS_TABLE . '.event_id = ' . EVENTS_TO_TABLE . '.event_id 
				JOIN ' . EVENTS_TYPE_TABLE . '
				ON ' . EVENTS_TABLE . '.event_type_id = ' . EVENTS_TYPE_TABLE . '.event_type_id 
				JOIN ' . USERS_TABLE . '
				ON ' . EVENTS_TABLE . '.author_id = ' . USERS_TABLE . '.user_id 
				WHERE ' . EVENTS_TO_TABLE . '.user_id = '.$user->data['user_id'];
			$result = $db->sql_query($sql);
			while($row = $db->sql_fetchrow($result)) {
					$message = $row['event_text'];
					$message_parser = new parse_message($message);
					$message_parser->parse(true, true, true);
					$message_parser->format_display(true, true, true, true);
					$message = (string) $message_parser->message;
					unset($message_parser);
					if (($user->data['is_organizer'])||($auth->acl_get('a_'))){
						$message .= '<br/><br/><div>';
						$sql2 = 'SELECT * 
							FROM ' . EVENTS_TO_TABLE . '
							JOIN ' . USERS_TABLE . '
							ON ' . EVENTS_TO_TABLE . '.user_id = ' . USERS_TABLE . '.user_id 
							WHERE ' . EVENTS_TO_TABLE . '.event_id = ' . $row['event_id'] . '
							AND ' . EVENTS_TO_TABLE . '.user_id <> '.$user->data['user_id'];
						$result2 = $db->sql_query($sql2);
						while($row2 = $db->sql_fetchrow($result2)) {
							$message .= '<div style="float:left;"><nobr><span style="background-image: url(&quot;./images/events/'.$row['event_type_id'].'_'.(($row2['event_new']==1)?'color':$row2['event_done']).'.png&quot;); background-repeat: no-repeat;height:27px;padding:5px 0 5px 30px;margin: 10px;">';
							$message .= '<a href="memberlist.php?do_=188&amp;mode=viewprofile&amp;u='.$row2['user_id'].'">'.$row2['username'].'</a>';
							$message .= '</span></nobr></div>';
						}
						$message .= '</div><br/>';
					}
					//Message block make
					$message_block.='
					<div  id="event_'.$row['event_id'].'">
					<li class="row bg1" style="display:block">
					<div><dl style="background-image: url(&quot;./images/events/'.$row['event_type_id'].'_'.(($row['event_new']==1)?'color':$row['event_done']).'.png&quot;); background-repeat: no-repeat;" class="icon">
					<dt style="width: 80%;"><a class="topictitle" href="javascript: event_showhide('.$row['event_id'].');">'.$row['event_type_label'].' '.$row['event_subject'].'</a><br />
					<a href="memberlist.php?do_=188&amp;mode=viewprofile&amp;u='.$row['user_id'].'">'.$row['username'].'</a>
					» '.date("H:i d", strtotime($row['event_begin'])).' '.$month_names[date("m", strtotime($row['event_begin']))]['months'].' - '.date("H:i d", strtotime($row['event_end'])).' '.$month_names[date("m", strtotime($row['event_end']))]['months'].'</dt>
					<dd class="mark"><input type="checkbox" id="checkbox_" name="event_ids[]" value="'.$row['event_id'].'"/></dd></dl>
					</div></li>
					<li class="row bg1" style="display:none">
					<div>
					<dl style="background-image: url(&quot;./images/events/'.$row['event_type_id'].'_'.(($row['event_new']==1)?'color':$row['event_done']).'.png&quot;); background-repeat: no-repeat;" class="icon">
					<dt style="width: 80%;"><div style="width: 100%;" class="postbody">
					<h3><a class="topictitle" href="javascript: event_showhide('.$row['event_id'].');">'.$row['event_type_label'].' '.$row['event_subject'].'</a></h3>
					<p class="author"><b>Отправитель:</b> <a href="memberlist.php?do_=188&amp;mode=viewprofile&amp;u='.$row['user_id'].'">'.$row['username'].'</a><br />
					<b>Отправлено:</b> '.date("H:i d", strtotime($row['event_time'])).' '.$month_names[date("m", strtotime($row['event_time']))]['months'].'<br /><br />
					<b>Срок действия:</b> '.date("H:i d", strtotime($row['event_begin'])).' '.$month_names[date("m", strtotime($row['event_begin']))]['months'].' - '.date("H:i d", strtotime($row['event_end'])).' '.$month_names[date("m", strtotime($row['event_end']))]['months'].'<br /></p>
					<div class="content">'.$message.'</div></div></dt>
					<dd class="mark"><input type="checkbox" id="checkbox_" name="event_ids[]" value="'.$row['event_id'].'"></dd></dl></div>
					</li>
					</div>';
					
					// Delete new status event
					$db->sql_query('UPDATE ' . EVENTS_TO_TABLE . '
					  SET event_new = 0
						WHERE user_id = ' . $user->data['user_id'] . '
						AND event_id = ' . $row['event_id']);
				}
			$template->assign_vars(array(
				'MESSAGE_BLOCK' => $message_block,
			));
			$template->assign_vars(array(
				'EVENTS_TAPE' => $events_tape,
			));
		break;
		case 'allnew' :
				$template->assign_vars(array(
				'SUBSECTION_ALLNEW'	=>	'active-subsection',
				'SELECT_FILTER'		=>	'Новые события',
			));
			$events_tape = '';
			$sql = 'SELECT * 
				FROM ' . EVENTS_TABLE . '
				JOIN ' . EVENTS_TO_TABLE . '
				ON ' . EVENTS_TABLE . '.event_id = ' . EVENTS_TO_TABLE . '.event_id 
				JOIN ' . EVENTS_TYPE_TABLE . '
				ON ' . EVENTS_TABLE . '.event_type_id = ' . EVENTS_TYPE_TABLE . '.event_type_id 
				JOIN ' . USERS_TABLE . '
				ON ' . EVENTS_TABLE . '.author_id = ' . USERS_TABLE . '.user_id 
				WHERE ' . EVENTS_TO_TABLE . '.user_id = '.$user->data['user_id'].'
				AND event_new = 1';
			$result = $db->sql_query($sql);
			if ($db->sql_affectedrows()) {
				while($row = $db->sql_fetchrow($result)) {
					$message = $row['event_text'];
					$message_parser = new parse_message($message);
					$message_parser->parse(true, true, true);
					$message_parser->format_display(true, true, true, true);
					$message = (string) $message_parser->message;
					unset($message_parser);
					if (($user->data['is_organizer'])||($auth->acl_get('a_'))){
						$message .= '<br/><br/><div>';
						$sql2 = 'SELECT * 
							FROM ' . EVENTS_TO_TABLE . '
							JOIN ' . USERS_TABLE . '
							ON ' . EVENTS_TO_TABLE . '.user_id = ' . USERS_TABLE . '.user_id 
							WHERE ' . EVENTS_TO_TABLE . '.event_id = ' . $row['event_id'] . '
							AND ' . EVENTS_TO_TABLE . '.user_id <> '.$user->data['user_id'];
						$result2 = $db->sql_query($sql2);
						while($row2 = $db->sql_fetchrow($result2)) {
							$message .= '<div style="float:left;"><nobr><span style="background-image: url(&quot;./images/events/'.$row['event_type_id'].'_'.(($row2['event_new']==1)?'color':$row2['event_done']).'.png&quot;); background-repeat: no-repeat;height:27px;padding:5px 0 5px 30px;margin: 10px;">';
							$message .= '<a href="memberlist.php?do_=188&amp;mode=viewprofile&amp;u='.$row2['user_id'].'">'.$row2['username'].'</a>';
							$message .= '</span></nobr></div>';
						}
						$message .= '</div><br/>';
					}
				  //Message block make
					$message_block.='
					<div  id="event_'.$row['event_id'].'">
					<li class="row bg1" style="display:block">
					<div><dl style="background-image: url(&quot;./images/events/'.$row['event_type_id'].'_color.png&quot;); background-repeat: no-repeat;" class="icon">
					<dt style="width: 80%;"><a class="topictitle" href="javascript: event_showhide('.$row['event_id'].');">'.$row['event_type_label'].' '.$row['event_subject'].'</a><br />
					<a href="memberlist.php?do_=188&amp;mode=viewprofile&amp;u='.$row['user_id'].'">'.$row['username'].'</a>
					» '.date("H:i d", strtotime($row['event_begin'])).' '.$month_names[date("m", strtotime($row['event_begin']))]['months'].' - '.date("H:i d", strtotime($row['event_end'])).' '.$month_names[date("m", strtotime($row['event_end']))]['months'].'</dt>
					<dd class="mark"><input type="checkbox" id="'.$row['event_id'].'"name="event_ids[]" /></dd></dl>
					</div></li>
					<li class="row bg1" style="display:none">
					<div>
					<dl style="background-image: url(&quot;./images/events/'.$row['event_type_id'].'_color.png&quot;); background-repeat: no-repeat;" class="icon">
					<dt style="width: 80%;"><div style="width: 100%;" class="postbody">
					<h3><a class="topictitle" href="javascript: event_showhide('.$row['event_id'].');">'.$row['event_type_label'].' '.$row['event_subject'].'</a></h3>
					<p class="author"><b>Отправитель:</b> <a href="memberlist.php?do_=188&amp;mode=viewprofile&amp;u='.$row['user_id'].'">'.$row['username'].'</a><br />
					<b>Отправлено:</b> '.date("H:i d", strtotime($row['event_time'])).' '.$month_names[date("m", strtotime($row['event_time']))]['months'].'<br /><br />
					<b>Срок действия:</b> '.date("H:i d", strtotime($row['event_begin'])).' '.$month_names[date("m", strtotime($row['event_begin']))]['months'].' - '.date("H:i d", strtotime($row['event_end'])).' '.$month_names[date("m", strtotime($row['event_end']))]['months'].'<br /></p>
					<div class="content">'.$message.'</div></div></dt>
					<dd class="mark"><input type="checkbox" id="'.$row['event_id'].'"name="event_ids[]" /></dd></dl></div>
					</li>
					</div>';
					
					// Delete new status event
					$db->sql_query('UPDATE ' . EVENTS_TO_TABLE . '
					  SET event_new = 0
						WHERE user_id = ' . $user->data['user_id'] . '
						AND event_id = ' . $row['event_id']);
				}
			}
			else $message_block = '<div>Новых сообытий нет!</div>';
			$template->assign_vars(array(
				'MESSAGE_BLOCK' => $message_block,
			));
			$template->assign_vars(array(
				'EVENTS_TAPE' => $events_tape,
			));
		break;
}



page_header("Календарь событий", false);

if (strpos($_SERVER['HTTP_REFERER'],'spmgn.ru/index.php')){
	$template->assign_var('IFRAME', 1);
}
$template->set_filenames(array(
	'body' => 'event_calendar.html'));
	
page_footer();
?>