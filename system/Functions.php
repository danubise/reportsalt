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
function replaceInvoiceData($operatordata, $maindata){

    $page1 = file_get_contents('/var/www/html/report/application/views/admin/report/page1.php');

    $page1 = str_replace("billigperiod",$maindata['bperiodtext'],$page1);
    $page1 = str_replace("traffictotal",$maindata['timeminut'],$page1);
    //$page1 = str_replace("traffictotal",round($maindata['time']/60,2),$page1);
    $page1 = str_replace("costtotal",round($maindata['cost'],2),$page1);
    $page1 = str_replace("duedate",$maindata['duedatetext'],$page1);
    $page1 = str_replace("currentdate",date("d.m.Y",strtotime($maindata['date'])),$page1);
    if($operatordata['company'] == "Vivaldi Bulgary" && $maindata['cost'] > 0  ){
        $bulgaryaVAT = "<tr style=\"height:19.533333333333px;\">
        <td style=\"font-family:Cambria;text-align:right;font-size:10px;background-color:#ffffff;color:#000000;font-weight:bold;min-width:50px\">
            <nobr>&nbsp;</nobr>
        </td>
        <td style=\"font-family:Cambria;text-align:right;font-size:10px;background-color:#ffffff;color:#000000;font-weight:bold;min-width:50px\">
            <nobr>&nbsp;</nobr>
        </td>
        <td style=\"font-family:Cambria;text-align:left;font-size:10px;background-color:#ffffff;color:#000000;font-weight:bold;min-width:50px\">
            <nobr>VAT%:</nobr>
        </td>
        <td style=\"font-family:Cambria;text-align:right;font-size:10px;background-color:#ffffff;color:#000000;font-weight:bold;border-left-color:#333333;border-top-color:#333333;border-bottom-color:#333333;min-width:50px\">
            <nobr>&nbsp;</nobr>
        </td>
        <td style=\"font-family:Cambria;text-align:right;font-size:10px;background-color:#ffffff;color:#000000;font-weight:bold;border-left-color:#333333;border-top-color:#333333;border-bottom-color:#333333;min-width:50px\">
            <nobr>0</nobr>
        </td>
    </tr>";
        $page1 = str_replace("invoceid", $maindata['secondID'], $page1);
        $page1 = str_replace("bulgaryaVAT", $bulgaryaVAT, $page1);
    }else {
        $page1 = str_replace("invoceid", $maindata['invoiceid'], $page1);
        $page1 = str_replace("bulgaryaVAT", "", $page1);
    }
    $page1 = str_replace("operatorname",$maindata['operatorname'],$page1);
    $page1 = str_replace("operatoraddress",$operatordata['address'],$page1);
    return $page1;

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
$companydata['address'][1] = "&nbsp;";
$companydata['address'][2] = "Registration number: SL 20672382";
$companydata['address'][3] = "Suite";
$companydata['address'][4] = "260, 2323 - 32 A venue N.E.,";
$companydata['address'][5] = "Calgary, Alberta T2E 623, Canada";
$companydata['address'][6] = "&nbsp;";
$companydata['address'][7] = "tel.+15875578990, +7(84";
$companydata['address'][8] = "52) 67-45-96, fax +7(8452) 67-45-96";

$companydata['address'][9] = "www.vivalditele.com";
$companydata['address'][10] = "";
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
$companydata['address'][1] = "Unique";
$companydata['address'][2] = "Identification Code: 205227648";
$companydata['address'][3] = "&nbsp;";
$companydata['address'][4] = "&nbsp;";
$companydata['address'][5] = "VAT: BG205227648";

$companydata['address'][6] = "&nbsp;";
$companydata['address'][7] = "2 Golash street, 1000 ";
$companydata['address'][8] = "District of Slatina, Sofia, Bulgaria";
$companydata['address'][9] = "Web site: www.vivalditele.com";
$companydata['address'][10] = "Tel.+15875578990";

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
    $htmldata =str_replace("address00", $companyData['address'][0], $htmldata);
    $htmldata =str_replace("address01", $companyData['address'][1], $htmldata);
    $htmldata =str_replace("address02", $companyData['address'][2], $htmldata);
    $htmldata =str_replace("address03", $companyData['address'][3], $htmldata);
    $htmldata =str_replace("address04", $companyData['address'][4], $htmldata);
    $htmldata =str_replace("address05", $companyData['address'][5], $htmldata);
    $htmldata =str_replace("address06", $companyData['address'][6], $htmldata);
    $htmldata =str_replace("address07", $companyData['address'][7], $htmldata);
    $htmldata =str_replace("address08", $companyData['address'][8], $htmldata);
    $htmldata =str_replace("address09", $companyData['address'][9], $htmldata);
    $htmldata =str_replace("address10", $companyData['address'][10], $htmldata);
return $htmldata;

}