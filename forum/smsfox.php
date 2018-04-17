<?php
/*
 * Library for SMSFox API
 * Author: SMSFox
 * Site: www.smsfox.ru
 */

define('SMS_USERNAME', 'demo');
define('SMS_PASSWORD', 'demo');


class SMSFox {
    function __construct() {
        $this->pattern = '/^\+?([87](?!95[4-9]|99\d|907|94[^0]|812[^9]|336)([34]\d|9[^7]|8[13]|7[07])\d{8}|855\d{8}|[12456]\d{9,13}|500[56]\d{4}|376\d{6}|8[68]\d{10,11}|8[14]\d{10}|82\d{9,10}|852\d{8}|90\d{10}|96(0[79]|170|13)\d{6}|96[23]\d{9}|964\d{10}|96(5[69]|89)\d{7}|96(65|77)\d{8}|92[023]\d{9}|91[1879]\d{9}|9[34]7\d{8}|959\d{7}|989\d{9}|97\d{8,12}|99[^45]\d{7,11}|994\d{9}|9955\d{8}|380[34569]\d{8}|38[15]\d{9}|375[234]\d{8}|372\d{7,8}|37[0-4]\d{8}|37[6-9]\d{7,11}|30[69]\d{9}|34[67]\d{8}|3[123569]\d{8,12}|38[1679]\d{8}|382\d{8,9})$/is';

        $this->zone1 = '/^\+?(79|73|74|78)/is';
        $this->zone2 = '/^\+?(1441|1876|93|376|355|297|244|994|374|973|375|880|975|673|257|501|229|243|235|53|357|242|269|45|253|251|20|240|298|220|241|350|299|504|354|62|91|996|965|254|266|352|218|370|222|261|960|853|382|377|230|60|356|265|976|505|687|968|92|595|63|974|966|248|597|94|963|232|255|676|216|992|66|1649|256|598|971|998|678|967|260|263)/is';
        $this->zone3 = '/^\+?(1808|1246|1868|1345|213|1268|1809|1829|1849|1767|591|226|32|267|238|855|236|86|506|237|682|503|358|594|679|995|597|1473|233|30|224|852|509|972|964|98|962|81|82|76|77|423|856|231|212|52|389|373|223|599|227|977|234|51|507|48|675|250|249|65|381|46|378|1869|1784|41|386|1758|90|993|228|886|380|44|58|1)/is';
        $this->zone5 = '/^\+?(1787|1939|1242|54|55|359|57|56|385|420|372|593|502|36|353|39|961|371|47|264|64|677|27|221|34|84|421)/is';
    }

    function send($phones='', $msg='', $sms_id=FALSE, $send=TRUE, $sender='') {
        if ($phones and $msg) {
            $count = $this->getCount($msg);

            $phones = $this->checkPhones($phones, $count);

            if ($send) {
                $result = $this->request("http://smsfox.ru/ru/api/?login=".SMS_USERNAME."&pwd=".SMS_PASSWORD."&phones={$phones['phones_valid']}&id={$sms_id}&sender=".urlencode($sender)."&msg=".urlencode($msg));
            } else {
                $result = 0;
            }

            return array(
                'phones_valid' => $phones['phones_valid'],
                'phones_fail' => $phones['phones_fail'],
                'count' => $count,
                'cost' => $phones['cost'],
                'status' => (int)$result);
        }

        return FALSE;
    }

    function getCount($msg) {
        $len = iconv_strlen($msg, 'UTF-8');

        if (mb_strlen($msg) == $len) {
            $sms_size = array(160, 304, 456, 608, 760, 800);
        } else {
            $sms_size = array(70, 132, 198, 264, 330, 396, 462, 528, 594, 660, 726, 792, 800);
        }

        $count = 1;
        foreach ($sms_size as $key => $value) {
            if ($len > $value) $count++;
        }

        return $count;
    }

    function checkPhones($phones, $count) {
        if (!is_array($phones)) $phones = explode(',', $phones);

        $valid = array();
        $fail = array();
        $cost = 0;

        foreach ($phones as $key => $phone) {
            $phone = trim($phone);

            if (preg_match($this->pattern, $phone)) {
                $valid[$key] = $phone;
                $cost += $this->getCost($phone) * $count;

            } else {
                $fail[$key] = $phone;
            }
        }

        return array(
                'phones_valid' => implode(',', $valid),
                'phones_fail' => implode(',', $fail),
                'cost' => $cost);
    }

    function getCost($phone) {
        $patterns = array(
                1 => $this->zone1,
                2 => $this->zone2,
                3 => $this->zone3,
                5 => $this->zone5
        );

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $phone)) return $key;
        }

        return 7;
    }

    function request($url='') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}

/* ### Example #1:
 * 
 * $smsfox = new SMSFox();
 * 
 * $result = $smsfox->send($phones='79123456789', $msg='Hello! I am text of sms message!');
 * 
 * print_r($result);
 * 
 * > array(
 * >     'phones_valid' => '79123456789',
 * >     'phones_fail' => '',
 * >     'count' => 1,
 * >     'cost' => 1,
 * >     'status' => 1);
 * 
 * 
 * 
 * ### Example #2:
 * 
 * $smsfox = new SMSFox();
 * 
 * $result = $smsfox->send($phones='79123456789, 380671234567, 987lol654321', $msg='Hello! I am long text of sms message!...(text over 140 characters)');
 * 
 * print_r($result);
 * 
 * > array(
 * >    'phones_valid' => '79123456789, 380671234567',
 * >    'phones_fail' => '987lol654321',
 * >    'count' => 2,
 * >    'cost' => 6,
 * >    'status' => 1);
 * 
 * 
 * 
 * ### Example #3 (use methods "getCount", "checkPhones", "getCost"):
 * 
 * $smsfox = new SMSFox();
 * 
 * 
 * // getCount() - return count SMS in text
 * $result1 = $smsfox->getCount('Hello! I am text of one sms message!');
 * 
 * print_r($result);
 * 
 * > 1
 * 
 * 
 * // checkPhones() - return array clean phones
 * $result2 = $smsfox->checkPhones('79123456789, 380671234567, 987lol654321', $result1); // $result1 as count SMS for calculate the cost
 * 
 * print_r($result2);
 * 
 * > array(
 * >    'phones_valid' => '79123456789, 380671234567',
 * >    'phones_fail' => '987lol654321',
 * >    'cost' => 3);
 * 
 * 
 * // getCost() - return cost SMS in points for the phone number
 * $result3 = $smsfox->getCost('79123456789'); // Russian provider
 * 
 * print_r($result3);
 * 
 * > 1
 * 
 * $result3 = $smsfox->getCost('380671234567'); // Ukraine or other provider
 * 
 * print_r($result3);
 * 
 * > 2
 * 
 * 
 * 
 * ### Example #4:
 * 
 * $smsfox = new SMSFox();
 * 
 * $result = $smsfox->send($phones='79123456789', $msg='Hello! I am text of sms message!', $id=0, $send=FALSE); // send = FALSE
 * 
 * print_r($result);
 * 
 * > array(
 * >     'phones_valid' => '79123456789',
 * >     'phones_fail' => '',
 * >     'count' => 1,
 * >     'cost' => 1,
 * >     'status' => 0); // status == 0 (SMS not sent)
 * 
 * 
 * 
 * ### Example #5 (substitution Sender ID)
 * 
 * $smsfox = new SMSFox();
 * 
 * $result = $smsfox->send($phones='79123456789', $msg='Hello! I am text of sms message!', $id=0, $send=TRUE, $sender='Anonim');
 * 
 * print_r($result);
 * 
 * > array(
 * >     'phones_valid' => '79123456789',
 * >     'phones_fail' => '',
 * >     'count' => 1,
 * >     'cost' => 1,
 * >     'status' => 1);
 * 
 * 
 * if $result['status'] != 1 SMS Send Fail, detail http://smsfox.ru/api/info/
 * 
 * 
 */