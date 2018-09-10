<?php
/**
 * Created by Unix develop team.
 * User: vlad
 * Date: 19.02.15
 * Time: 22:14
 */

function printarray($out) {
    echo"<pre>";
    print_r($out);
    echo"</pre>";
}

function CBR_XML_Daily_Ru() {
    $json_daily_file = __DIR__.'/daily.json';
    if (!is_file($json_daily_file) || filemtime($json_daily_file) < time() - 3600) {
        if ($json_daily = file_get_contents('https://www.cbr-xml-daily.ru/daily_json.js')) {
            file_put_contents($json_daily_file, $json_daily);
        }
    }

    return json_decode(file_get_contents($json_daily_file));
}

function baseurl($url = '') {
    global $core_dir;
    return 'http://'.$_SERVER['HTTP_HOST'].'/'.(($core_dir)? $core_dir : '').$url;
}

function check_controller($controller) {
    if(file_exists(controllers.$controller.EXT)) {
        require_once controllers.$controller.EXT;
        return true;
    }
    return false;
}

function connect_mysql() {
    global $_config;
    if(empty($_config['mysql']['user']) or empty($_config['mysql']['password'])) {
        return false;
    }
    $connect = new db($_config['mysql']['host'],$_config['mysql']['user'],$_config['mysql']['password'],$_config['mysql']['base']);
    $connect->set_charset("utf8");
    return $connect;
}

function &get_instance() {
    return Core::get_instance();
}

function get_month() {
	return array(
		'Январь',
		'Февраль',
		'Март',
		'Апрель',
		'Май',
		'Июнь',
		'Июль',
		'Август',
		'Сентябрь',
		'Октябрь',
		'Ноябрь',
		'Декабрь'
	);
}

function get_alias($route) {
    if(!empty($route)) {
        global $Core;
        if(isset($Core->config->route->{$route})) {
            return $Core->config->route->{$route};
        }
    }
    return false;
}
function cp1251_to_utf8($s)
{
    if ((mb_detect_encoding($s,'UTF-8,CP1251')) == "WINDOWS-1251")
    {
        $c209 = chr(209); $c208 = chr(208); $c129 = chr(129);
        for($i=0; $i<strlen($s); $i++)
        {
            $c=ord($s[$i]);
            if ($c>=192 and $c<=239) $t.=$c208.chr($c-48);
            elseif ($c>239) $t.=$c209.chr($c-112);
            elseif ($c==184) $t.=$c209.$c209;
            elseif ($c==168)    $t.=$c208.$c129;
            else $t.=$s[$i];
        }
        return $t;
    }
    else
    {
        return $s;
    }
}

function utf8_to_cp1251($s)
{
    if ((mb_detect_encoding($s,'UTF-8,CP1251')) == "UTF-8")
    {
        for ($c=0;$c<strlen($s);$c++)
        {
            $i=ord($s[$c]);
            if ($i<=127) $out.=$s[$c];
            if ($byte2)
            {
                $new_c2=($c1&3)*64+($i&63);
                $new_c1=($c1>>2)&5;
                $new_i=$new_c1*256+$new_c2;
                if ($new_i==1025)
                {
                    $out_i=168;
                } else {
                    if ($new_i==1105)
                    {
                        $out_i=184;
                    } else {
                        $out_i=$new_i-848;
                    }
                }
                $out.=chr($out_i);
                $byte2=false;
            }
            if (($i>>5)==6)
            {
                $c1=$i;
                $byte2=true;
            }
        }
        return $out;
    }
    else
    {
        return $s;
    }
}
function logger($data,$id=""){
    $file="/var/log/asterisk/agi.log";
    $td=date('Y-m-d H:i:s');
    $scriptname="class_dm.php";

    $head="$td $scriptname $id ";
    $data=$head.$data;
    $data=str_replace("\n","\n".$head,$data);
    $data=trim($data)."\n";
    file_put_contents($file, $data, FILE_APPEND );
}

function companyDivision($operatordata){
    $companydata = array();

    //Dollar by default
    if (trim($operatordata['currency']) == "" ){
        $operatordata['currency'] = "USD";
    }
    $companydata['currency']= $operatordata['currency'];

if($operatordata['company'] == "Vivaldi Canada"){
/*
Registration number: SL 20672382
Suite 260, 2323 - 32 A venue N.E.,Calgary, Alberta T2E 623, Canada
tel.+15875578990, +7(8452) 67-45-96, fax +7(8452) 67-45-96
www.vivalditele.com
*/

$companydata['address'][0] = "Vivaldi Telecom LP";
$companydata['address'][1] = "Registration number: SL 20672382";
$companydata['address'][2] = "Suite";
$companydata['address'][3] = "260, 2323 - 32 A venue N.E.,";
$companydata['address'][4] = "Calgary, Alberta T2E 623, Canada";
$companydata['address'][5] = "tel.+15875578990, +7(8452) 67-45-96, fax +7(8452) 67-45-96";
$companydata['address'][6] = "www.vivalditele.com";
$companydata['bankDetails'][1] =
"Beneficiary’s name: VIVALDI TELECOM LP<br/>
Beneficiary’s Address: Suite 260, 2323 - 32 A venue N.E., Calgary, Alberta T2E 623, Canada";

    if($operatordata['currency'] == "USD"){

$companydata['bankDetails'][2] =
"BANK NAME: Bank of Cyprus Public Company Ltd.<br/>
BANK ADDRESS: 121-123 Arch. Makariou III Ave., 3021, Limassol,Cyprus<br/>
BENEFICIARY`S BANK SWIFT: BCYPCY2N<br/>
BENEFICIARY`S ACCOUNT Number (IBAN) : CY12002001950000357027353628 (USD)";
    }else if($operatordata['currency'] == "RUB"){
        $accountnumberiban = "CY12002001950000357027353628 (USD)";
    }else {
        $accountnumberiban = "CY43002001950000357027353555 (EURO)";

$companydata['bankDetails'][2] =
"BANK NAME: Bank of Cyprus Public Company Ltd.<br/>
BANK ADDRESS: 121-123 Arch. Makariou III Ave., 3021, Limassol,Cyprus<br/>
BENEFICIARY`S BANK SWIFT: BCYPCY2N<br/>
BENEFICIARY`S ACCOUNT Number (IBAN) : CY43002001950000357027353555 (EURO)";
    }
}else{
$companydata['address'][0] = "VIVALDI TELECOM LTD";
$companydata['address'][1] = "Unique Identification Code:<br>BG205227648";
$companydata['address'][2] = "&nbsp;";
$companydata['address'][3] = "2 Golash street, 1000 District";
$companydata['address'][4] = "of Slatina, Sofia, Bulgaria";
$companydata['address'][5] = "Tel.+15875578990";
$companydata['address'][6] = "Web site: www.vivalditele.com";
$companydata['bankDetails'][1] =
"Beneficiary’s name:  VIVALDI TELECOM LTD<br/>
Beneficiary’s Address: 2 Golash street, 1000 District of Slatina, Sofia, Bulgaria";
/*
VIVALDI TELECOM LTD
Unique Identification Сode: BG205227648
2 Golash street, 1000 District of Slatina, Sofia, Bulgaria
Tel.+15875578990
Web site: www.vivalditele.com
*/
    if(trim($operatordata['currency']) == "" || $operatordata['currency'] == "USD"){
        $operatordata['currency']="USD";
        $accountnumberiban = "CY12002001950000357027353628 (USD)";



$companydata['bankDetails'][2] =
"BANK NAME: UNICREDIT BULBANK AD<br/>
BANK ADDRESS: 7 SVETA NEDELYA SQUARE 1000 SOFIA BULGARIA<br/>
BENEFICIARY`S BANK SWIFT: UNCRBGSF<br/>
BENEFICIARY`S ACCOUNT Number(IBAN) : BG34UNCR70001523342166 (USD)";


    }else {
        $accountnumberiban = "CY43002001950000357027353555 (EURO)";

$companydata['bankDetails'][2] =
"BANK NAME: UNICREDIT BULBANK AD<br/>
BANK ADDRESS: 7 SVETA NEDELYA SQUARE 1000 SOFIA BULGARIA<br/>
BENEFICIARY`S BANK SWIFT: UNCRBGSF<br/>
BENEFICIARY`S ACCOUNT Number(IBAN) : BG73UNCR70001523342240 (EURO)";
    }
}

//printarray($companydata);
//die;
return $companydata;
}

function replaceTemplate($htmldata, $operatordata){
    $companyData = companyDivision($operatordata);
    $htmldata =str_replace("currency", $companyData['currency'], $htmldata);
    $htmldata =str_replace("bankDetails1", $companyData['bankDetails'][1], $htmldata);
    $htmldata =str_replace("bankDetails2", $companyData['bankDetails'][2], $htmldata);
    $htmldata =str_replace("address0", $companyData['address'][0], $htmldata);
    $htmldata =str_replace("address1", $companyData['address'][1], $htmldata);
    $htmldata =str_replace("address2", $companyData['address'][2], $htmldata);
    $htmldata =str_replace("address3", $companyData['address'][3], $htmldata);
    $htmldata =str_replace("address4", $companyData['address'][4], $htmldata);
    $htmldata =str_replace("address5", $companyData['address'][5], $htmldata);
    $htmldata =str_replace("address6", $companyData['address'][6], $htmldata);
return $htmldata;

}