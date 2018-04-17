<?php

if (!defined('IN_PHPBB'))
{
	exit;
}

/*
_sms

user_id							mediumint(8)
user_phone						varchar(20)
user_phone_status				tinyint(3)
user_phone_activate_code		varchar(12)
user_phone_activate_changes		smallint(5)
user_phone_activate_attempts	smallint(5)
user_phone_activate_start		int(11)
user_phone_cost					float
*/

define('SMS_ACTIVATION_CODE_LENGHT', 6);
define('SMS_ACTIVATION_MAX_CHANGES', 5);
define('SMS_ACTIVATION_MAX_ATTEMPTS', 5);
define('SMS_PHONE_STATUS_NOT_ACTIVE', 0);
define('SMS_PHONE_STATUS_ACTIVE', 1);

define("SMS_HTTPS_LOGIN", "spmgn"); //Ваш логин для HTTPS-протокола
define("SMS_HTTPS_PASSWORD", ",fh,fhbcs"); //Ваш пароль для HTTPS-протокола
define("SMS_HTTPS_ADDRESS", "https://transport.sms-pager.com:7214/"); //HTTPS-Адрес, к которому будут обращаться скрипты. Со слэшем на конце.
//define("SMS_HTTPS_ADDRESS", "https://lcab.sms-uslugi.ru/"); //HTTPS-Адрес, к которому будут обращаться скрипты. Со слэшем на конце.
define("SMS_HTTP_ADDRESS", "http://sms-uslugi.ru/API/XML/"); //HTTPS-Адрес, к которому будут обращаться скрипты. Со слэшем на конце.
define("SMS_HTTPS_CHARSET", "utf-8"); //кодировка ваших скриптов. cp1251 - для Windows-1251, либо же utf-8 для, сообственно - utf-8 :)
define("SMS_HTTPS_METHOD", "curl"); //метод, которым отправляется запрос (curl)
define("SMS_USE_HTTPS", 1); //1 - использовать HTTPS-адрес, 0 - HTTP

class sms {
	public static function generateActivationCode() {
		$ret = '';
		$chrs = '1234567890';
		
		for ($i = 0; $i < SMS_ACTIVATION_CODE_LENGHT; $i++) {
			$ret .= $chrs[mt_rand(0, strlen($chrs) - 1)];
		}
		
		return $ret;
	}
/*
	false	— ошибка
	-1		— номер уже активирован для этого пользователя
	-2		— номер активирован другим пользователем
	-3		— больше чем SMS_ACTIVATION_MAX_CHANGES отправок сообщения за сутки
	-4		— ошибка отправки
	-7		— «Неверный формат номера телефона.»
	-8		— «Сообщение на указанный номер не может быть доставлено.»
*/
	public static function initActivation($user_id, $user_phone) {
		if ($user_id == ANONYMOUS) return false;
		global $db, $config;
		
		if (strlen($user_phone) > 20) return false;

		$time = time();
		$sql = 'SELECT t1.*, phone_used FROM (SELECT 0 as __num, S1.* FROM ' . SMS_TABLE . ' as S1 WHERE S1.user_id = ' . (int)$user_id . ') as t1
				LEFT JOIN
					(SELECT count(*) as phone_used, 0 as __num
					FROM ' . SMS_TABLE . '
					WHERE user_phone = \'' . $db->sql_escape($user_phone) . '\' AND user_phone_status = ' . SMS_PHONE_STATUS_ACTIVE . ') as t2
				ON t1.__num = t2.__num';
		$result = $db->sql_query($sql);
		$user_info = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		$_code = self::generateActivationCode();
		if ($user_info) {
			if ($user_id != $user_info['user_id']) return false;
			if ($user_phone == $user_info['user_phone'] && $user_info['user_phone_status'] == SMS_PHONE_STATUS_ACTIVE) return -1;
			if ((int)$user_info['phone_used'] > 0) return -2;
			if ($user_info['user_phone_activate_changes'] >= SMS_ACTIVATION_MAX_CHANGES && ((int)$user_info['user_phone_activate_start'] + 24 * 60 * 60 > $time)) return -3;
		}
		
		if ($config['sms_activation']) {
			$return = Transport::send(array('text' => 'Activation code: ' . $_code), array($user_phone));
			$result = $return['code'];
			
			
			if ($result > 1) { //Error
				switch ($result) {
					case 500: //Недостаточно переданных параметров
					case 501: //Неверная пара логин/пароль
					case 502: //Превышен размер smsid. Максимальный размер 21 символ
					case 503: //Неверный формат datetime. Верный: yyyy-mm-dd HH:MM:SS.
						return -4;
						break;
					case 504: //Недопустимое значение Адреса отправителя
						return -7;
						break;
					default:
						if ($result > 700) { //Ошибка парсера XML документа (х – цифры 0..9)
							return -4;
						}
						break;
				}
			}
		}
		
		if ($user_info) {
			$sql_ary = array(
				'user_phone'					=> $db->sql_escape($user_phone),
				'user_phone_status'				=> ($config['sms_activation']) ? SMS_PHONE_STATUS_NOT_ACTIVE : SMS_PHONE_STATUS_ACTIVE,
				'user_phone_activate_code'		=> ($config['sms_activation']) ? $_code : 0,
				'user_phone_activate_changes'	=> ((int)$user_info['user_phone_activate_start'] + 24 * 60 * 60 < $time) ? 0 : $user_info['user_phone_activate_changes'] + 1,
				'user_phone_activate_attempts'	=> 0,
				'user_phone_activate_start'		=> ((int)$user_info['user_phone_activate_start'] + 24 * 60 * 60 < $time) ? $time : $user_info['user_phone_activate_start'],
			);
			
			$sql = 'UPDATE ' . SMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE user_id = ' . (int)$user_id;
			$db->sql_query($sql);
		} else {
			$sql_ary = array(
				'user_id'						=> (int)$user_id,
				'user_phone'					=> $db->sql_escape($user_phone),
				'user_phone_status'				=> ($config['sms_activation']) ? SMS_PHONE_STATUS_NOT_ACTIVE : SMS_PHONE_STATUS_ACTIVE,
				'user_phone_activate_code'		=> ($config['sms_activation']) ? $_code : 0,
				'user_phone_activate_changes'	=> 0,
				'user_phone_activate_attempts'	=> 0,
				'user_phone_activate_start'		=> $time,
			);
			
			$sql = 'INSERT INTO ' . SMS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		}
		return true;
	}
	public static function deletePhone($user_id) {
		if ($user_id == ANONYMOUS) return false;
		global $db;
		
		$sql = 'UPDATE ' . SMS_TABLE . ' SET user_phone = "", user_phone_status = ' . SMS_PHONE_STATUS_NOT_ACTIVE . ' WHERE user_id = ' . (int)$user_id;
		$db->sql_query($sql);
	}
/*
	false	— ошибка
	-1		— номер уже активирован для этого пользователя
	-2		— номер активирован другим пользователем
	-5		— превышение попыток для текущего ключа (SMS_ACTIVATION_MAX_ATTEMPTS)
	-6		— неверный ключ
*/
	public static function activate($user_id, $acode) {
		if ($user_id == ANONYMOUS) return false;
		global $db;
		
		$time = time();
		$sql = 'SELECT t1.*, phone_used FROM (SELECT 0 as __num, S1.* FROM ' . SMS_TABLE . ' as S1 WHERE user_id = ' . (int)$user_id . ') as t1
				LEFT JOIN
					(SELECT count(*) as phone_used, 0 as __num
					FROM ' . SMS_TABLE . '
					WHERE user_phone = (SELECT user_phone FROM ' . SMS_TABLE . ' WHERE user_id = ' . (int)$user_id . ') AND user_phone_status = ' . SMS_PHONE_STATUS_ACTIVE . ') as t2
				ON t1.__num = t2.__num';
		$result = $db->sql_query($sql);
		$user_info = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		if ($user_info) {
			if ($user_id != $user_info['user_id']) return false;
			if ($user_info['user_phone_status'] == SMS_PHONE_STATUS_ACTIVE) return -1;
			if ((int)$user_info['phone_used'] > 0) return -2;
			if ($user_info['user_phone_activate_attempts'] >= SMS_ACTIVATION_MAX_ATTEMPTS) return -5;
			
			if ($user_info['user_phone_activate_code'] == $acode) {
				$db->sql_query('UPDATE ' . SMS_TABLE . ' SET user_phone_status = ' . SMS_PHONE_STATUS_ACTIVE . ' WHERE user_id = ' . (int)$user_info['user_id']);
			} else {
				$db->sql_query('UPDATE ' . SMS_TABLE . ' SET user_phone_activate_attempts = user_phone_activate_attempts + 1 WHERE user_id = ' . (int)$user_info['user_id']);
				return -6;	
			}
		} else {
			return false;
		}
		
		return true;
	}
	
	public static function sendMessage($user_ids, $message) {
		global $db;
		
		$users_info = array(); $phones = array();
		$result = $db->sql_query('SELECT * FROM ' . SMS_TABLE . ' WHERE ' . $db->sql_in_set('user_id', (is_array($user_ids)) ? $user_ids : array($user_ids)));
		while ($row = $db->sql_fetchrow($result))
		{
			$users_info[] = $row;
			if (!empty($row['user_phone']) && $row['user_phone_status'] == SMS_PHONE_STATUS_ACTIVE)
			{
				$phones[] = $row['user_phone'];
			}
		}
		$db->sql_freeresult($result);
		
		if (sizeof($phones)) {
			$return = Transport::send(array('text' => $message), $phones);
			$result = $return['code'];
		} else {
			$result = false;
		}
		
		return $result;
	}
}

class Transport{
	///Проверка баланса
	public function balance(){
		return self::get( self::request("balance"), "account" );
	}
	public function reports($start = "0000-00-00", $stop = "0000-00-00"){
		$result = self::request("report", array("start" => $start, "stop" => $stop));
		if (self::get($result, "code") != 1){
			$return =  array("code" => self::get($result, "code"), "descr" => self::get($result, "descr"));
		}
		else{
			$return =  array(
				"code" => self::get($result, "code"), 
				"descr" => self::get($result, "descr"),
			);
			if (isset($result['sms'])) $return["sms"] = $result['sms'];
		}
		return $return;
	}
	public function detailReport($smsid){
		$result = self::request("report", array("smsid" => $smsid));
		if (self::get($result, "code") != 1){
			$return =  array("code" => self::get($result, "code"), "descr" => self::get($result, "descr"));
		}
		else{
			$detail = $result["detail"][0];
			$return =  array(
				"code" => self::get($result, "code"), 
				"descr" => self::get($result, "descr"),
				"delivered" => $detail['delivered'],
				"notDelivered" => $detail['notDelivered'],
				"waiting" => $detail['waiting'],
				"enqueued" => $detail['enqueued']
			);
			if (isset($result['sms'])) $return["sms"] = $result['sms'][0];
		}
		return $return;
	}
	
	//отправка смс
	//params = array (text => , source =>, datetime => , action =>, onlydelivery =>, smsid =>)
	public function send($params = array(), $phones = array()){
		if (!isset($params["action"])) $params["action"] = "send";
		$someXML = "";
		foreach ($phones as $phone){
			if (is_array($phone)){
				if (isset($phone["number"])){
					$someXML .= "<to number='".$phone['number']."'>";
					if (isset($phone["text"])){
						$someXML .= self::getConvertedString($phone["text"]);
					}
					$someXML .= "</to>";
				}
			}
			else{
				$someXML .= "<to number='$phone'></to>";
			}
		}
		$result = self::request("send", $params, $someXML);
		if (self::get($result, "code") != 1){
			$return =  array("code" => self::get($result, "code"), "descr" => self::get($result, "descr"));
		}
		else{
			$return = array(
				"code" => 1,
				"descr" => self::get($result, "descr"),
				"datetime" => self::get($result, "datetime"),
				"action" => self::get($result, "action"),
				"allRecivers" => self::get($result, "allRecivers"),
				"colSendAbonent" => self::get($result, "colSendAbonent"),
				"colNonSendAbonent" => self::get($result, "colNonSendAbonent"),
				"priceOfSending" => self::get($result, "priceOfSending"),
				"colsmsOfSending" => self::get($result, "colsmsOfSending"),
				"price" => self::get($result,"price"),
				"smsid" => self::get($result,"smsid"),
			);
		}
		return $return;
		
	}
	public function get($responce, $key){
		if (isset($responce[$key], $responce[$key][0], $responce[$key][0][0])) return $responce[$key][0][0];
		return false;
	}
	public function parseXML($xml){
		if (function_exists("simplexml_load_string"))
			return self::XMLToArray($xml);
		else 
			return $xml;
	}
	public function request($action,$params = array(),$someXML = ""){
		$xml = self::makeXML($params,$someXML);
		if (SMS_HTTPS_METHOD == "curl"){
			return self::parseXML( self::request_curl($action,$xml) );
		}
		self::error("В настройках указан неизвестный метод запроса - '".SMS_HTTPS_METHOD."'");
	}
	public function request_curl($action,$xml){
        if (SMS_USE_HTTPS == 1)
            $address = SMS_HTTPS_ADDRESS.$action.".xml";
        else
            $address = SMS_HTTP_ADDRESS.$action.".php";
		$ch = curl_init($address);
		curl_setopt($ch, CURLOPT_URL, $address);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	public function makeXML($params,$someXML = ""){
		$xml = "<?xml version='1.0' encoding='UTF-8'?>
		<data>
			<login>".SMS_HTTPS_LOGIN."</login>
			<password>".SMS_HTTPS_PASSWORD."</password>
			";
		foreach ($params as $key => $value){
			$value = self::getConvertedString($value);
			$xml .= "<$key>$value</$key>";
		}
		$xml .= "$someXML
		</data>";
		return $xml;
	}
	public function getConvertedString($value, $from = false){
		if (SMS_HTTPS_CHARSET != "utf-8") {
			if (function_exists("iconv")){
				if (!$from)
					return iconv(SMS_HTTPS_CHARSET,"utf-8",$value);
				else 
					return iconv("utf-8",SMS_HTTPS_CHARSET,$value);
			}
			else
				self::error("Не удается перекодировать переданные параметры в кодировку utf-8 - отсутствует функция iconv");
		}
		return $value;
	}
	public function error($text){
		die($text);
	}	
	public function XMLToArray($xml){
        if (!strlen($xml)) {
            $descr = "Не удалось получить ответ от сервера!";
            if (SMS_USE_HTTPS == 1){
                $descr .= " Возможно конфигурация вашего сервера не позволяет отправлять HTTPS-запросы. Попробуйте установить значение SMS_USE_HTTPS = 0 в файле config.php";
            }
            return array("code" => 0, "descr" => $descr);
        }
		$xml = simplexml_load_string($xml);
		
		$return = array();
		foreach($xml->children() as $child)
	  	{
	  		$return[$child->getName()][] = self::makeAssoc((array)$child);
		}
		$return = self::convertArrayCharset($return);
		return $return;
	}
	public function convertArrayCharset($return){
		foreach ($return as $key => $value){
			if (is_array($value)) $return[$key] = self::convertArrayCharset($return[$key]);
			else $return[$key] = self::getConvertedString($value, true);
		}
		return $return;
	}
	public function makeAssoc($array){
		if (is_array($array))
			foreach ($array as $key => $value){
				if (is_object($value)) {
					$newValue = array();
					foreach($value->children() as $child)
				  	{
				  		$newValue[] = (string)$child;
					}
					$array[$key] = $newValue;
				}
			}
		else $array = (string)$array;
		
		return $array;
	}
}

?>