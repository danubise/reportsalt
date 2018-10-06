<?php
/**
 * Created by PhpStorm.
 * User: slava
 * Date: 18.10.15
 * Time: 22:23
 */
//function exception_error_handler($errno, $errstr, $errfile, $errline ) {
//    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
//}
//set_error_handler("exception_error_handler");

include ("/var/www/html/report/application/controllers/admin/dompdf/dompdf_config.inc.php");
require_once ("/var/www/html/report/application/libs/PHPMailer-master/class.phpmailer.php");
class Report extends Core_controller {

    public function __construct() {
        parent::__construct();
    }
    public function confirm($code){
        //echo "****".$code;
        //$code=mysql_escape_string($code);
        if(trim($code) != ""){
        	$this->db->update('b_invoicemain',"confirm,1","`conformid`='".$code."'");
        	echo "ok";
        }else{
        	echo "did not receive confirmation code";
        }
    }

    public function index() {
        $this->view(
            array(
                'module' => 'Авторизация'
            )
        );
    }

    public function getCurrency() {
        $data = CBR_XML_Daily_Ru();
        $this->db->insert("b_currency", array(
            "date" => date("Y-m-d",time()),
            "currency" => "USD",
            "price" => $data->Valute->USD->Value
        ));
        $this->db->insert("b_currency", array(
            "date" => date("Y-m-d",time()),
            "currency" =>"EUR",
            "price" => $data->Valute->EUR->Value
        ));

        //echo "Обменный курс USD по ЦБ РФ на сегодня: {$data->Valute->USD->Value}";
    }

    public function login() {
        if($this->user_model->auth($_POST['login'],$_POST['pass'])) {

        }
        header("Location: ".baseurl(''));
    }
    public function autosendinvoice(){
        $invoicetosend =$this->db->select("`invoiceid`,`cost` from `b_invoicemain` WHERE `send`<>'1' AND `cost`>'0' GROUP BY `invoiceid` ");
        
        printarray($invoicetosend);

        if(is_array($invoicetosend)) {
            foreach ($invoicetosend as $invoice) {
                echo "Operate with invoice ".$invoice['invoiceid']."<br>";
                if($invoice['cost']>0) {
                    echo "Send invoice " . $invoice['invoiceid'] . "<br>";
                    $this->sendinvoice($invoice['invoiceid']);
                    echo "Done invoice " . $invoice['invoiceid'] . "<br>";
                }else{
                    echo "Cost is 0 , not send <br>\n";
                }
            }
            echo "Send done";
        }else{
            echo "No invoice for sending";
        }

    }
    public  function sendinvoice($invoceid=""){
        //отрпавка счетов получателям
        if($invoceid==""){
            return false;
        }
        //найти айди оператора по номеру счета
        $sqlquery="`operatorid` FROM  `b_invoicemain` WHERE `invoiceid`='".$invoceid."'";
        //echo $sqlquery;
        //die;
        $operatorid=$this->db->select($sqlquery , 0);
        //printarray($operatorid);
        //die;
        if($operatorid){
            $operatordetail=$this->db->select("* from `b_operators` WHERE `id`='".$operatorid."'",0);
            //printarray($operatordetail);
            //die;
            /*
             * Array
(
    [id] => 1450
    [name] => Novik - Omega
    [address] => Адресс оператора
    [bperiod] => month
    [payment] => 8
    [manager] => манагер
    [mail] => slava@markaz.uz
    [billoperate] => 2015-10-01
)
             */

            if(is_array($operatordetail)){
            echo "Document formatting .... ";
                $invoice=$this->invoicetopdf($invoceid);
                //die;
                /*
maindata
(
[id] => 58
[invoiceid] => 140
[date] => 2015-10-01
[operatorid] => 1450
[operatorname] => Novik - Omega
[bperiodtext] => 01-30 Sep 2015
[duedatetext] => 09 Oct 2015
[balans] => 0
[realdatefrom] => 2015-09-01
[realdateto] => 2015-09-30
[cost] => 329.93297
[time] => 56722
)           */
                $mail['theme']=" Invoice from Vivaldi Telecom LP for the period ".$invoice['maindata']['bperiodtext'];
                $mail['body']="Dear Partner!

Please find attached our invoice for the period ".$invoice['maindata']['bperiodtext']."

Please take the confirm.

Please confirm receipt of this Invoice by pushing the following link

http://95.141.192.26/report/report/confirm/".$invoice['confirmcode']."
--

Financial Department

Vivaldi Telecom

tel/fax:+44 131 6081121

Please find attached our invoice.

Period ".$invoice['maindata']['bperiodtext'].".";
//printarray($report['data']);
                //    die;

                $mail['from']="invoices@vivalditele.com";
                $mail['to']=$operatordetail['mail'];

                $email = new PHPMailer();
                $email->From      = $mail['from'];
                $email->FromName  = $mail['from'];
                $email->Subject   = $mail['theme'];
                $email->Body      = $mail['body'];
                $mails=explode(",",$operatordetail['mail']);
                foreach($mails as $emailaddress) {
                    $email->AddAddress($emailaddress);
                }

                $invocename="invoce".str_replace(" ","-",$invoice['maindata']['bperiodtext']).".pdf";
                $email->AddAttachment( $invoice['pdffilename'] , $invocename );

                if(!$email->Send())
                {
                    echo "Message could not be sent. <p>";
                    echo "Mailer Error: " . $email->ErrorInfo;
                    $update = array(
                        "send" => "2",
                        "sentdetail" => "Mailer Error: " . $email->ErrorInfo
                    );
                    $this->db->update("b_invoicemain",$update, "`invoiceid` = '" . $invoice['maindata']['invoiceid'] . "'");
                    echo "Message has not sent";
                    //die;
                }else {
                    $update = array(
                        "send" => "1",
                        "sentdetail" => "Message has been sent successful to ". $operatordetail['mail']
                    );
                    $this->db->update("b_invoicemain", $update, "`invoiceid` = '" . $invoice['maindata']['invoiceid'] . "'");
                    echo "Message has been sent ";
                    echo $operatordetail['mail'] . " sent <br>";
                }
            }else{
                return false;
            }



        }else{
            return false;
        }
    }
    public function invoicetopdf($invoceid=""){
        if($invoceid==""){
            return false;
        }
        echo "DOM";
        $dompdf=new DOMPDF();
        //die;
        /*
 maindata
(
[id] => 58
    [invoiceid] => 140
    [date] => 2015-10-01
    [operatorid] => 1450
    [operatorname] => Novik - Omega
    [bperiodtext] => 01-30 Sep 2015
    [duedatetext] => 09 Oct 2015
    [balans] => 0
    [realdatefrom] => 2015-09-01
    [realdateto] => 2015-09-30
    [cost] => 329.93297
    [time] => 56722
)           */
        $invoice=$this->htmlformat($invoceid);
       $dompdf->load_html($invoice['html']);

        //echo $invoice['html'];
        //die;
        //echo $_dompdf_warnings;
        $dompdf->render();
                echo "render";

        exec("mkdir /var/www/html/report/application/cloud/".$invoice['maindata']['operatorid']);
        $invoice['pdffilename']="/var/www/html/report/application/cloud/".$invoice['maindata']['operatorid']."/".$invoice['maindata']['date']."-".microtime(true).".pdf";
        file_put_contents( $invoice['pdffilename'], $dompdf->output());
        unset($dompdf);
        $invoice['confirmcode']=md5($invoice['pdffilename']);
        $update= array(
            'conformid' => $invoice['confirmcode'],
            'pdffilename' =>$invoice['pdffilename'] );
        $this->db->update('b_invoicemain',$update,"`invoiceid` = '".$invoice['maindata']['invoiceid']."'");
        //echo $this->db->query->last;
        //die;
        //echo "invoicetopdf";
        //printarray($invoice);
        return $invoice;
    }
    public function htmlformat($invoceid=""){
        if($invoceid==""){
            return false;
        }
        $maindata=$this->db->select("
            `id`,`invoiceid`,`date`,`operatorid`,`operatorname`,`bperiodtext`,`duedatetext`,`balans`,`realdatefrom`,`realdateto`,
            SUM(`cost`) AS `cost`, SUM(`time`) AS `time`, SUM(`timeminut`) AS `timeminut`
FROM  `b_invoicemain` WHERE `invoiceid`='".$invoceid."'" , 0);
        $operatordata=$this->db->select("* from `b_operators` WHERE `id`='".$maindata['operatorid']."'",0);

        /*
 maindata
(
[id] => 58
    [invoiceid] => 140
    [date] => 2015-10-01
    [operatorid] => 1450
    [operatorname] => Novik - Omega
    [bperiodtext] => 01-30 Sep 2015
    [duedatetext] => 09 Oct 2015
    [balans] => 0
    [realdatefrom] => 2015-09-01
    [realdateto] => 2015-09-30
    [cost] => 329.93297
    [time] => 56722
)         */
        //echo $this->db->query->last;
        // printarray($maindata);
        $detaildata=$this->db->select("`dest_code`,`dest`,SUM(`time`) AS `time`,SUM(`cost`) AS `cost` from `b_invoicedetail` WHERE `invoiceid`='".$invoceid."' GROUP BY `dest_code` ");
        // echo $this->db->query->last;
        //printarray($detaildata);
        /*Array
        (
            [0] => Array
            (
                [dest_code] => 20640
            [dest] => Engels
            [time] => 56722
            [cost] => 329.93297
        )

)*/
        // die;
        $head="<html><head><meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" /></head><body>";
        $end="</body></html>";

        $page1 = file_get_contents('/var/www/html/report/application/views/admin/report/page1.php');

        $page1 = str_replace("billigperiod",$maindata['bperiodtext'],$page1);
        $page1 = str_replace("traffictotal",$maindata['timeminut'],$page1);
        //$page1 = str_replace("traffictotal",round($maindata['time']/60,2),$page1);
        $page1 = str_replace("costtotal",round($maindata['cost'],2),$page1);
        $page1 = str_replace("duedate",$maindata['duedatetext'],$page1);
        $page1 = str_replace("currentdate",date("d.m.Y",strtotime($maindata['date'])),$page1);
        $page1 = str_replace("invoceid",$maindata['invoiceid'],$page1);
        $page1 = str_replace("operatorname",$maindata['operatorname'],$page1);
        $page1 = str_replace("operatoraddress",$operatordata['address'],$page1);


        $detailtable['head'] = <<< 'ENDHTMLHEAD'
<hr>
<table class="" cellspacing=0 border=1>
    <tbody>
    <tr style="height:17px;">
        <td style="background-color:#a0e0e0;border:1px solid;border-left-color:#333333;border-right-color:#333333;border-top-color:#333333;border-bottom-color:#333333;min-width:50px">
            <nobr>Destination</nobr>
        </td>
        <td style="background-color:#a0e0e0;border:1px solid;border-left-color:#333333;border-right-color:#333333;border-top-color:#333333;border-bottom-color:#333333;min-width:50px">
            <nobr>Duration,sec</nobr>
        </td>
        <td style="background-color:#a0e0e0;border:1px solid;border-left-color:#333333;border-right-color:#333333;border-top-color:#333333;border-bottom-color:#333333;min-width:50px">
            <nobr>Amount, USD</nobr>
        </td>
    </tr>
ENDHTMLHEAD;
        foreach($detaildata as $report) {
            $detailtable['body'] .= "
    <tr style=\"height:34px;\">
        <td style=\"min-width:50px\">
            <nobr>".$report['dest']."&nbsp;</nobr>
        </td>
        <td style=\"min-width:50px\">
            <nobr>".$this->sectotime($report['time'])."&nbsp;[".$report['time']."]&nbsp;</nobr>
        </td>
        <td style=\"min-width:50px\">
            <nobr>".$report['cost']."</nobr>
        </td>
    </tr>";


        }
        $detailtable['end']="
    <tr style=\"height:17px;\">
        <td style=\"background-color:#a0e0e0;min-width:50px\">
            <nobr>Total:</nobr>
        </td>
        <td style=\"text-align:right;background-color:#a0e0e0;border:1px solid;border-left-color:#333333;border-right-color:#333333;border-top-color:#333333;border-bottom-color:#333333;min-width:50px\">
            <nobr>".$this->sectotime($maindata['time'])."&nbsp;[".$maindata['timeminut']."]&nbsp;</nobr>
        </td>
        <td style=\"text-align:right;background-color:#a0e0e0;border:1px solid;border-left-color:#333333;border-right-color:#333333;border-top-color:#333333;border-bottom-color:#333333;min-width:50px\">
            <nobr>".$maindata['cost']."</nobr>
        </td>
    </tr>
    </tbody>
</table>

";
        $html['maindata']=$maindata;
        $html['html']=$head.$page1.$detailtable['head'].$detailtable['body'].$detailtable['end'].$end;
        $html['html'] = replaceTemplate($html['html'] , $operatordata);
        return $html;
    }
    private function billingdate($ardate){
        $adateto=explode(".",$ardate['dateto']);
        $adatefrom=explode(".",$ardate['datefrom']);
        //echo printarray($adatefrom).printarray($adateto);
        $operationdate=array();
        $to=$adateto[1];
        if($adateto[2]>$adatefrom[2]) $to=$adateto[1]+12;
        //echo "from =".$adatefrom[1]."  to=".$to."<br>";
        for($i=$adatefrom[1]; $i<=$to;$i++){
           // echo "8888 ".$i."<br>";
            if($i!=$adatefrom[1] && $i!=$adateto[1]){
                if($i>12){
                    $m=$i-12;
                    $y=$adatefrom[2]+1;
                }else{
                    $m=$i;
                    $y=$adatefrom[2];
                }
                $operationdate[$i]['begin']="1.".$m.".".$y;
                $t1=new DateTime($operationdate[$i]['begin']);
                $operationdate[$i]['end']=$t1->format('t').".".$m.".".$y;

            }elseif($i==$adatefrom[1]){
                $operationdate[$i]['begin']=$adatefrom[0].".".$adatefrom[1].".".$adatefrom[2];

                if($adatefrom[1]==$adateto[1]) {
                    $lastday = $adateto[0];
                }
                else{
                    $t1=new DateTime($operationdate[$i]['begin']);
                    $lastday=$t1->format('t');
                }

                $operationdate[$i]['end']=$lastday.".".$adatefrom[1].".".$adatefrom[2];
            }else{
                $datenew=$adateto[2]."-".$i."-".$adateto[0];
                $operationdate[$i]['begin']="1.".$adateto[1].".".$adateto[2];
                $operationdate[$i]['end']=$adateto[0].".".$adateto[1].".".$adateto[2];
            }
            //printarray($ardate);
            $operationdate['period'][$i]['begin']=date("d.m.Y",strtotime( $operationdate[$i]['begin']));
            $operationdate['period'][$i]['end']=date("d.m.Y",strtotime( $operationdate[$i]['end']));
            $operationdate['billperiod']=date("d",strtotime( $ardate['datefrom']))."-".date("d M Y",strtotime( $operationdate[$i]['end']));
            //$operationdate['billperiod']=date("d",strtotime( $operationdate[$i]['begin']))."-".date("d M Y",strtotime( $operationdate[$i]['end']));
            // echo "****".;
            $operationdate['date']=date("d M Y",strtotime($ardate['dateto']." + 1 day"));
            $operationdate['duedate']=date("d M Y",strtotime($operationdate['date'].$ardate['duedate']." day"));
            //printarray($operationdate);
            //die;

        }
        return $operationdate;
    }
    public function autobillingid($id="", $billoperate="", $invoceid=""){
		//autobillingid/2143/2017-05-22/4698
		/*
DELETE FROM  `b_invoicemain` WHERE  `invoiceid` LIKE  '4858';
DELETE FROM `callwaytest`.`b_invoicedetail` WHERE  `invoiceid` LIKE  '4858';
	*/
        if($id==""){
            echo "No operator id";
            die;
        }
        if($billoperate=="") {
            $billoperate = date("Y-m-d", time()); //ткущая дата формирования отчета
        }
        //$billoperate ="2015-10-26"; //12 19 26
        echo $billoperate."<br>";
        $weekday=date("N", strtotime($billoperate));
        $config=array();
        $config['date']=$billoperate;
        $config['invoice_id_edit'] = $invoceid;
	echo "Week day is -:".$weekday;
        if($weekday==1){
            echo "week";
            $operators=$this->db->select("* FROM  `b_operators` WHERE `bperiod`='week' and `id`='".$id."'");
            echo $this->db->query->last;
            $config['operators']=$operators;
            $this->autoformat($config);
        }
        $dayofmanth=date("j", strtotime($billoperate));
        if($dayofmanth==1){
            echo "month and halfmonth";
            $operators=$this->db->select("* FROM  `b_operators` WHERE  (`bperiod`='month' OR `bperiod`='halfmonth')  and `id`='".$id."'");
            echo $this->db->query->last;
            $config['operators']=$operators;
            $this->autoformat($config);
            echo "end halfmonth";
        }
        if($dayofmanth==16){
            echo "halfmonth";
            $operators=$this->db->select("* FROM  `b_operators` WHERE  `bperiod`='halfmonth' and `id`='".$id."'");
            echo $this->db->query->last;
            $config['operators']=$operators;
            $this->autoformat($config);
        }
    }
    public function autobilling(){

        $billoperate= date("Y-m-d",time()); //ткущая дата формирования отчета
        #$billoperate ="2017-09-04"; //12 19 26
        echo $billoperate."<br>";
        $weekday=date("N", strtotime($billoperate));
        $config=array();
        $config['date']=$billoperate;
        if($weekday==1){
            echo "week";
            $operators=$this->db->select("* FROM  `b_operators` WHERE `disable`=0 AND `bperiod`='week'");
            echo $this->db->query->last;
            $config['operators']=$operators;
            $this->autoformat($config);
        }
        $dayofmanth=date("j", strtotime($billoperate));
        if($dayofmanth==1){
            echo "month and halfmonth";
            $operators=$this->db->select("* FROM  `b_operators` WHERE  `disable`=0 AND `bperiod`='month' OR `bperiod`='halfmonth'");
            echo $this->db->query->last;
            $config['operators']=$operators;
            $this->autoformat($config);
            echo "end halfmonth";
        }
        if($dayofmanth==16){
            echo "halfmonth";
            $operators=$this->db->select("* FROM  `b_operators` WHERE `disable`=0 AND `bperiod`='halfmonth'");
            echo $this->db->query->last;
            $config['operators']=$operators;
            $this->autoformat($config);
        }
    }
    public function autoformat($queryconfig=""){

        echo "autoformat";
        //$billoperate= date("Y-m-d",time()); //ткущая дата формирования отчета
        //$billoperate ="2015-10-01";

        //$operators =$this->db->select("* FROM  `b_operators` WHERE `billoperate`='".$billoperate."'");
        //echo $this->db->query->last;
        $operators=$queryconfig['operators'];
        printarray($operators);

        if(is_array($operators)){
            foreach($operators as $key=>$operator) {
                echo "make billing for ".$operator['id']."  ".$operator['name'];
                $config['date']=$queryconfig['date'];
                switch ($operator['bperiod']) {
                    case "week":
                        $config['datefrom'] = date("d.m.Y", strtotime($queryconfig['date']." - 7 days"));
                        $config['dateto']=date("d.m.Y", strtotime($queryconfig['date']." - 1 days"));
                        $config['nextbiloperate']=date("Y-m-d", strtotime($queryconfig['date']."+ 7 day"));
                    break;
                    case "halfmonth":
                        if(date("d",strtotime($queryconfig['date']))>1){
                            //если первая половина месяца
                            $config['datefrom']= date("1.m.Y", strtotime($queryconfig['date']));
                            $config['dateto']=date("d.m.Y", strtotime($queryconfig['date']." - 1 day"));
                            $config['nextbiloperate']=date("Y-m-01", strtotime($queryconfig['date']."+ 1 month"));
                        }else{
                            $config['datefrom']= date("16.m.Y", strtotime($queryconfig['date']." - 1 day"));
                            $config['dateto']=date("t.m.Y", strtotime($queryconfig['date']." - 1 day"));
                            $config['nextbiloperate']=date("Y-m-16", strtotime($queryconfig['date']));
                        }
                        break;
                    case "month":
                        $config['datefrom']= date("1.m.Y", strtotime($queryconfig['date']." - 1 day"));
                        $config['dateto']=date("t.m.Y", strtotime($queryconfig['date']." - 1 day"));
                        $config['nextbiloperate']=date("Y-m-01", strtotime($queryconfig['date']."+ 1 month"));
                        break;
                }
                $operator['config']=$config;
                $operator['invoice_id_edit']=$queryconfig['invoice_id_edit'];
                printarray($operator);
                echo "----------------Total----";
		$reportdata="";
                do {
               	try{
			 $reportdata=$this->getreportdata($operator);
                } catch (Exception $e) {
	                 $attempts++;
                         echo "Retry logic !!!!!!!!!!!!!!!!!!!!!!!!!".$attempts;
                         sleep(1);
                         continue;
                }
                break;
                } while($attempts < $NUM_OF_ATTEMPTS);

#                $reportdata=$this->getreportdata($operator);


            }


        }

    }
    private function getreportdata($operator){

        $operationdate= $this->billingdate(array(
            'dateto'=>$operator['config']['dateto'],
            'datefrom'=>$operator['config']['datefrom'],
            'duedate'=>$operator['payment']
        ));
        echo "OPERATION DATE";
        printarray($operator);
        printarray($operationdate);

        if (trim($operator['invoice_id_edit']) != "") {
            echo "will use exist invoice id - '" . $operator['invoice_id_edit'] . "'";
            $operator['invoiceid'] = $operator['invoice_id_edit'];
        } else {
            $this->db->delete("from `b_sequense`");
            $this->db->query("INSERT INTO  `callwaytest`.`b_sequense` ( `id`) VALUES (NULL);");
            $operator['invoiceid'] = $this->db->select("`id` from `b_sequense`", 0);
        }

        echo "  ".$operator['id']."GETBILLING";
        //return;
        $serverip = "95.141.192.5:8080";
        $user = "aconn";
        $password = "AhW2po1c";
        $summ = array();
        //$url = "http://" . $serverip . "/bgbilling/executer?user=aconn&pswd=AhW2po1c&module=voiceip&pageSize=100&mask=&contentType=xml&cid=22&pageIndex=1&unit=1&action=LoginsAmount&date2=21.09.2015&mid=4&date1=12.09.2015&";

        //$url="http://".$serverip."/bgbilling/executer?BGBillingSecret=AhW2po1c&module=voiceip&dateto=21.09.2015&action=GetLogins&mid=4&datefrom=12.09.2015&cid=22&";
        //$url1 = "http://" . $serverip . "/bgbilling/executer?user=" . $user . "&pswd=" . $password . "&module=voiceip&dateto=" . $dateto . "&action=GetLogins&mid=4&datefrom=" . $datefrom . "&cid=" . $_POST['clientid'] . "&";
        //$url2="http://" . $serverip . "/bgbilling/executer?user=" . $user . "&pswd=" .$password . "&module=voiceip&direct=1&mid=4&pageSize=100&date2=".$_POST['dateto']."&date1=1".$_POST['datefrom']."&nofree=1&unit=1&pageIndex=1&action=LoginDirect&id=14%2C74%2C428%2C651%2C1040%2C1049%2C1324&contentType=xml&cid=22&mask=&order=&";
        //$url = "http://" . $serverip . "/bgbilling/executer?user=" . $user . "&pswd=" .$password . "&module=voiceip&pageSize=10000&mask=&contentType=xml&cid=".$_POST['clientid']."&pageIndex=1&id=1&unit=1&action=LoginSessions&date2=".$_POST['dateto']."&mid=4&date1=".$_POST['datefrom']."&";
        $mysqlqueuename='reportdetail'.$operator['id'];
        $this->db->insert_queue($mysqlqueuename, 'b_invoicedetail', array('operatorid','invoiceid','dest','dest_code','time','month','cost','begin','end','part'));
        $part=0;
        foreach($operationdate['period'] as $key=>$date) {
            $part++;
            $url1 = "http://" . $serverip . "/bgbilling/executer?user=" . $user . "&pswd=" . $password . "&module=voiceip&dateto=" . $date['end'] . "&action=GetLogins&mid=4&datefrom=" . $date['begin'] . "&cid=" . $operator['id'] . "&";
            echo $url1 . "<br>";
            //запрос списка логинов
			$NUM_OF_ATTEMPTS = 5;
			$attempts = 0;

			do {

				try
				{
            $data = simplexml_load_string(file_get_contents($url1));
					} catch (Exception $e) {
					$attempts++;
					echo "Retry logic !!!!!!!!!!!!!!!!!!!!!!!!!".$attempts;
					sleep(1);
					continue;
				}

				break;

			} while($attempts < $NUM_OF_ATTEMPTS);
            $json = json_encode($data);
            $array = json_decode($json, TRUE);
			
            $report1['log']['url1'][$key]['url']=$url1;
            $report1['log']['url1'][$key]['date']=$date;
            $report1['log']['url1'][$key]['answer1']=$array;

          printarray($array);

            if ($array['@attributes']['status'] != "ok") {
                $this->view(
                    array(
                        'view' => 'report/index',
                        'var' => array(
                            "clientid" => $_POST['clientid'],
                            "error" => $array['@attributes']
                        )
                    )
                );
                return;
            }

            //получение оплаты для поля $invoicetovivaldi
            $invoicetovivaldiurl = "http://" . $serverip . "/bgbilling/executer?user=" . $user . "&pswd=" . $password . "&module=voiceip&direct=2&mid=4&pageSize=2000&date2=" . $date['end'] . "&unit=1&pageIndex=1&action=LoginsAmount&date1=" . $date['begin'] . "&contentType=xml&cid=" . $operator['id'] . "&mask=&";
            //http://95.141.192.5:8080/bgbilling/executer?user=aconn&pswd=AhW2po1c&module=voiceip&direct=2&mid=4&pageSize=25&date2=30.10.2015&date1=20.10.2015&unit=1&pageIndex=1&action=LoginsAmount&contentType=xml&cid=22&mask=&
            echo $invoicetovivaldiurl . "<br>";
            //запрос списка логинов
			$NUM_OF_ATTEMPTS = 5;
			$attempts = 0;

			do {

				try
				{
            $invoicetovivaldidata = simplexml_load_string(file_get_contents($invoicetovivaldiurl));
			} catch (Exception $e) {
					$attempts++;
					echo "Retry logic !!!!!!!!!!!!!!!!!!!!!!!!!".$attempts;
					sleep(1);
					continue;
				}

				break;

			} while($attempts < $NUM_OF_ATTEMPTS);
			
            $invoicetovivaldijson = json_encode($invoicetovivaldidata);
            $invoicetovivaldiarray = json_decode($invoicetovivaldijson, TRUE);
            $report1['log']['invoicetovivaldi'][$key]['url']=$invoicetovivaldiurl;
            $report1['log']['invoicetovivaldi'][$key]['date']=$date;
            $report1['log']['invoicetovivaldi'][$key]['answer1']=$invoicetovivaldiarray;
           printarray($invoicetovivaldiarray);
            /*
             * Array
(
    [@attributes] => Array
        (
            [status] => ok
            [xslt] => voiceip_login_amount.xsl
        )

    [table] => Array
        (
            [@attributes] => Array
                (
                    [comment] => Вх\Исх
                    [contractTitle] => Novik - Omega
                    [date1] => 26.10.2015
                    [date2] => 31.10.2015
                    [reportTitle] => Наработка по логинам VoiceIP
                )

            [data] => Array
                (
                    [@attributes] => Array
                        (
                            [count_amount] => 2991
                            [money_amount] => -110.68
                            [round_time_amount] => 27:32:00 [99120] [1652]
                            [time_amount] => 27:32:00 [99120] [1652]
                        )
             */
            $invoicetovivaldi= $invoicetovivaldiarray['table']['data']['@attributes']['money_amount'];
            //die;



            $aliase = "";
            $k1 = true;
            //printarray($array);
            //die;
            $url_tovivaldi = "http://" . $serverip . "/bgbilling/executer?user=" . $user . "&pswd=" . $password . "&module=contract&action=ContractPayments&date2=" . $date['end'] . "&date1=" . $date['begin'] . "&cid=" . $operator['id'] . "&";
            echo $url_tovivaldi."<br>";
            //http://95.141.192.5:8080/bgbilling/executer?user=aconn&pswd=AhW2po1c&module=contract&action=ContractPayments&date2=31.10.2015&date1=01.10.2014&cid=22&
			$NUM_OF_ATTEMPTS = 5;
			$attempts = 0;

			do {

				try
				{
            $datatovivaldi = simplexml_load_string(file_get_contents($url_tovivaldi));
            } catch (Exception $e) {
					$attempts++;
					echo "Retry logic !!!!!!!!!!!!!!!!!!!!!!!!!".$attempts;
					sleep(1);
					continue;
				}

				break;

			} while($attempts < $NUM_OF_ATTEMPTS);
			
			$jsontovivaldi = json_encode($datatovivaldi);
            $arraytovivaldi = json_decode($jsontovivaldi, TRUE);
            $report1['log']['tovivaldi'][$key]['url']=$url_tovivaldi;
            $report1['log']['tovivaldi'][$key]['date']=$datatovivaldi;
            $report1['log']['tovivaldi'][$key]['answer1']=$arraytovivaldi;
            printarray($arraytovivaldi);
            $paytovivaldi=$arraytovivaldi['table']['@attributes']['summa'];
            /*
             * Array
(
    [@attributes] => Array
        (
            [status] => ok
        )

    [table] => Array
        (
            [@attributes] => Array
                (
                    [summa] => 0.00
                )

            [data] => Array
                (
                )

        )

)

             */
            echo "Запрос оплаты партнерам<br>";
            //http://95.141.192.5:8080/bgbilling/executer?user=aconn&pswd=AhW2po1c&module=contract&action=ContractCharges&date2=30.11.2015&date1=01.08.2015&cid=22&
            $url_topartner = "http://" . $serverip . "/bgbilling/executer?user=" . $user . "&pswd=" . $password . "&module=contract&action=ContractCharges&date2=" . $date['end'] . "&date1=" . $date['begin'] . "&cid=" . $operator['id'] . "&";
            echo $url_topartner."<br>";
            //http://95.141.192.5:8080/bgbilling/executer?user=aconn&pswd=AhW2po1c&module=contract&action=ContractPayments&date2=31.10.2015&date1=01.10.2014&cid=22&
			$NUM_OF_ATTEMPTS = 5;
			$attempts = 0;

			do {

				try
				{
            $datatopartner = simplexml_load_string(file_get_contents($url_topartner));
					} catch (Exception $e) {
					$attempts++;
					echo "Retry logic !!!!!!!!!!!!!!!!!!!!!!!!!".$attempts;
					sleep(1);
					continue;
				}

				break;

			} while($attempts < $NUM_OF_ATTEMPTS);
            $test="<data status=\"ok\">
<table summa=\"2075.00\">
<data>
<row f0=\"1066\" f1=\"0\" f2=\"30.09.2015\" f3=\"1974.00\" f4=\"Расход\" f5=\"\" f6=\"17.10.2015 12:49:25\" f7=\"Малышева Дарья\"/>
<row f0=\"1089\" f1=\"0\" f2=\"22.10.2015\" f3=\"15.00\" f4=\"Расход\" f5=\"\" f6=\"22.10.2015 15:06:56\" f7=\"Matutin Alexey\"/>
<row f0=\"1090\" f1=\"0\" f2=\"22.10.2015\" f3=\"86.00\" f4=\"Расход\" f5=\"\" f6=\"22.10.2015 15:07:08\" f7=\"Matutin Alexey\"/>
</data>
</table>
</data>";
           // $datatopartner = simplexml_load_string($test);
            $jsontopartner = json_encode($datatopartner);
            $arraytopartner = json_decode($jsontopartner, TRUE);
            $report1['log']['tovivaldi'][$key]['url']=$url_topartner;
            $report1['log']['tovivaldi'][$key]['date']=$datatopartner;
            $report1['log']['tovivaldi'][$key]['answer1']=$arraytopartner;
            printarray($arraytopartner);
            $paytopartner=0;
            if(isset($arraytopartner['table']['data']['row'])) {
                foreach ($arraytopartner['table']['data']['row'] as $row){
                    if(isset($row['@attributes'])){
                       // echo strtolower($row['@attributes']['f4']). " ".strtoupper($row['@attributes']['f4'])."<br>";
                        if($row['@attributes']['f4']=="Расход" || $row['@attributes']['f4']=="расход"){
                            echo "Fount to partner data ".$row['@attributes']['f4']." value ".$row['@attributes']['f3']."<br>";
                            $paytopartner+=$row['@attributes']['f3'];
                        }
                    }
                }
                }
            /*
             * Array
(
    [@attributes] => Array
        (
            [status] => ok
        )

    [table] => Array
        (
            [@attributes] => Array
                (
                    [summa] => 0.00
                )

            [data] => Array
                (
                )

        )

)
             */
            //echo  $paytopartner;
           // die;
            $paytopartner=$arraytopartner['table']['@attributes']['summa'];
            //die;
            /*
             * Array
(
    [@attributes] => Array
        (
            [status] => ok
        )

    [logins] => Array
        (
            [item] => Array
                (
                    [@attributes] => Array
                        (
                            [date1] => 23.12.2014
                            [date2] =>
                            [id] => 1120
                            [title] => 2225[ 148.251.120.21 ]
                        )

                )

        )

)
             */

            foreach ($array['logins']['item'] as $key=>$value) {
                //if(isset($))
                echo $key ."<br>";

                printarray($value);
                if(isset($value['id'])){
                    $aliase .= $value['id'];
                }
                if ($k1) {
                    $aliase .= $value['@attributes']['id'];
                    $k1 = false;
                } else {
                    $aliase .= "," . $value['@attributes']['id'];
                }
            }

            //echo $aliase . "<br>";
            $url2 = "http://" . $serverip . "/bgbilling/executer?user=" . $user . "&pswd=" . $password . "&module=voiceip&direct=1&mid=4&pageSize=2000&date2=" .  $date['end'] . "&date1=" .  $date['begin']  . "&nofree=1&unit=1&pageIndex=1&action=LoginDirect&id=" . urlencode($aliase) . "&contentType=xml&cid=".$operator['id']."&mask=&order=&";
            //запрос детализации по логинам
            echo "запрос детализации по логинам<br>";
            echo $url2 . "<br>";
            echo "===============================";
            //die;
            $ctx = stream_context_create(array('http'=>
                array(
                    'timeout' => 1200,  //1200 Seconds is 20 Minutes
                )
            ));
			$NUM_OF_ATTEMPTS = 5;
			$attempts = 0;

			do {

				try
				{
            $data = simplexml_load_string(file_get_contents($url2, false, $ctx));
				} catch (Exception $e) {
				$attempts++;
				echo "Retry logic !!!!!!!!!!!!!!!!!!!!!!!!!".$attempts;
				sleep(1);
				continue;
			}

			break;

		} while($attempts < $NUM_OF_ATTEMPTS);
            $json = json_encode($data);
            $array = json_decode($json, TRUE);
            $report1['log']['url2'][$key]['url']=$url1;
            // $report['log']['url2'][$key]['date']=$date;
            $report1['log']['url2'][$key]['answer1']=$array;
            //$data = array();

            if (isset($array['table']['data']['row'])) {
                $routedata=array();
                echo "<br> ---------- ADD queue <br>";
                $this->db->insert_queue('main'.$operator['id'], 'b_invoicemain',array('invoiceid','date','cost','time','timeminut','timetext','confirmation','log','datefrom','dateto','operatorid','month','operatorname','manager','bperiod','weekid','send','realdatefrom','realdateto','bperiodtext','duedatetext','conformid','part','tovivaldi','topartner','invoicetovivaldi','endbalans','balans'));
                $totalcost=0;
                $totaltime=0;
                foreach ($array['table']['data']['row'] as $aa) {

                    /*
                     *         (
            [acd] => -1;00:01:57 [117]
            [asr] => -1;100%
            [cost] => 0.03841
            [count] => 1
            [dest] => Uzbekistan Beeline
            [dest_code] => 33508
            [round_time] => 00:01:57 [117]
            [time] => 00:01:57 [117]
        )
                     */
                    if(isset($aa['@attributes'])) {
                        $a1 = $aa['@attributes'];
                    }else{
                        $a1=$aa;
                    }
                    $routedata['operatorid']=$operator['id'];
                    $routedata['invoiceid']=$operator['invoiceid'];

                    $routedata['dest']=$this->db->real_escape_string($a1['dest']); //mysql_escape_string($a1['dest'] );
                    $routedata['dest_code']=$a1['dest_code'];
                    //поменял время time на round_time
                    $r = $this->valuedecode(trim($a1['round_time']));
                    $routedata['time']=$r['sec'];
                    $routedata['month']=$key;
                    $routedata['cost']=$a1['cost'];
                    $routedata['begin']=$date['begin'];
                    $routedata['end']=$date['end'];
                    $routedata['part']=$part;
                    $totalcost+=$routedata['cost'];
                    $totaltime+=$routedata['time'];


                    $this->db->insert_queue_add($mysqlqueuename, $routedata);
                    printarray($routedata);



                }

                // array('invoiceid','date','cost','time','confirmation','log','datefrom','dateto','operatorid','month','operatorname'));
                /*
                 *  [count_amount] => 8484
                            [money_amount] => 815.64
                            [round_time_amount] => 199:46:10 [719170] [12313]
                            [time_amount] => 199:46:10 [719170] [11986]
                 */
                $round_time_amount = $this->timedivide($array['table']['data']['@attributes']['round_time_amount']);
                $time_amount = $this->timedivide($array['table']['data']['@attributes']['time_amount']);
                $balans=$this->db->select("`endbalans` FROM `b_invoicemain` WHERE `operatorid`='".$operator['id']."' ORDER BY `date` DESC LIMIT 1",0);
                echo $this->db->query->last."<br>";
                echo "Found balals from last invoice ".$balans;
                if($balans){
                    echo "true ".$balans;
                }else
                {
                    $balans=$this->db->select("`balans` FROM `b_operators` WHERE `id`='".$operator['id']."'",0);
                    echo $this->db->query->last."<br>";
                    echo "balans from main tables ".$balans."<br>";
                }
                //die;
                $endbalans=$balans+$invoicetovivaldi+$array['table']['data']['@attributes']['money_amount']-$paytovivaldi+$paytopartner;

                $maindata = array(
                    'invoiceid' => $operator['invoiceid'],
                    'date'=> $operator['config']['date'],
                    //'cost'=>$totalcost,
                    'cost'=>$array['table']['data']['@attributes']['money_amount'],
                    //'time'=>$totaltime,
                    'time'=>$round_time_amount['sec'],
                    'timeminut'=>$round_time_amount['minute'],
                    'timetext'=> $round_time_amount['time'],
                    'confirmation'=> '',
                    'log'=>"was turned off",//$this->db->real_escape_string(print_r($report1['log'],true)),
                    'datefrom' =>date("Y-m-d",strtotime($date['begin'])),
                    'dateto' => date("Y-m-d",strtotime($date['end'])),
                    'operatorid' => $operator['id'],
                    'month' => $key,
                    'operatorname' => $operator['name'],
                    'manager' => $operator['manager'],
                    'bperiod' => $operator['bperiod'],
                    'weekid' => date("W",strtotime($date['begin'])),
                    'send'=>'0',
                    'realdatefrom'=>date("Y-m-d",strtotime($operator['config']['datefrom'])),
                    'realdateto'=>date("Y-m-d",strtotime($operator['config']['dateto'])),
                    'bperiodtext'=>$operationdate['billperiod'],
                    'duedatetext' => $operationdate['duedate'],
                    'conformid'=>"",
                    'part'=>$part,
                    'tovivaldi'=>$paytovivaldi,
                    'topartner'=>$paytopartner,
                    'invoicetovivaldi'=>$invoicetovivaldi,
                    'endbalans'=>$endbalans,
                    'balans'=>$balans

                );
               // printarray($maindata);
                $this->db->insert_queue_add('main'.$operator['id'], $maindata);
                $this->db->insert_queue_send('main'.$operator['id']);
                echo "MAIN <br>". $this->db->query->last."<br> MAIN END<BR>";
            }else
            {
                echo "empty invoice<br>";
                $this->db->insert_queue('main'.$operator['id'], 'b_invoicemain',array('invoiceid','date','cost','time','timeminut','timetext','confirmation','log','datefrom','dateto','operatorid','month','operatorname','manager','bperiod','weekid','send','realdatefrom','realdateto','bperiodtext','duedatetext','conformid','part','tovivaldi','topartner','invoicetovivaldi','endbalans','balans'));

                $round_time_amount = "0.0";
                $balans=$this->db->select("`endbalans` FROM `b_invoicemain` WHERE `operatorid`='".$operator['id']."' ORDER BY `date` DESC LIMIT 1",0);
                echo $this->db->query->last."<br>";
                echo "Found balals from last invoice ".$balans;
                if($balans){
                    echo "true ".$balans;
                }else
                {
                    $balans=$this->db->select("`balans` FROM `b_operators` WHERE `id`='".$operator['id']."'",0);
                    echo $this->db->query->last."<br>";
                    echo "balans from main tables ".$balans."<br>";
                }
                //die;
                $endbalans=$balans+$invoicetovivaldi+$array['table']['data']['@attributes']['money_amount']-$paytovivaldi+$paytopartner;

                $maindata = array(
                    'invoiceid' => $operator['invoiceid'],
                    'date'=> $operator['config']['date'],
                    //'cost'=>$totalcost,
                    'cost'=>"0.0",
                    //'time'=>$totaltime,
                    'time'=>"0",
                    'timeminut'=>"0",
                    'timetext'=> 0,
                    'confirmation'=> '',
                    'log'=>"was turned off",//$this->db->real_escape_string(print_r($report1['log'],true)),
                    'datefrom' =>date("Y-m-d",strtotime($date['begin'])),
                    'dateto' => date("Y-m-d",strtotime($date['end'])),
                    'operatorid' => $operator['id'],
                    'month' => $key,
                    'operatorname' => $operator['name'],
                    'manager' => $operator['manager'],
                    'bperiod' => $operator['bperiod'],
                    'weekid' => date("W",strtotime($date['begin'])),
                    'send'=>'0',
                    'realdatefrom'=>date("Y-m-d",strtotime($operator['config']['datefrom'])),
                    'realdateto'=>date("Y-m-d",strtotime($operator['config']['dateto'])),
                    'bperiodtext'=>$operationdate['billperiod'],
                    'duedatetext' => $operationdate['duedate'],
                    'conformid'=>"",
                    'part'=>1,
                    'tovivaldi'=>$paytovivaldi,
                    'topartner'=>$paytopartner,
                    'invoicetovivaldi'=>$invoicetovivaldi,
                    'endbalans'=>$endbalans,
                    'balans'=>$balans

                );
                // printarray($maindata);
                $this->db->insert_queue_add('main'.$operator['id'], $maindata);
                $this->db->insert_queue_send('main'.$operator['id']);
                echo "MAIN <br>". $this->db->query->last."<br> MAIN END<BR>";
            }
        }
        $this->db->insert_queue_send($mysqlqueuename);
        echo $this->db->query->last;
        //  printarray($report);
       /* die;
        $summ['timetime']=$this->sectotime($summ['time']['sec']);
        $summ['round_timetime']=$this->sectotime($summ['round_time']['sec']);
        $report1['contractTitle']=$array['table']['@attributes']['contractTitle'];
        $report1['data']=$report;
        $report1['total']=$summ;
        $report1['aliase']=$aliase;
        $report1['datefrom']=$datefrom;
        $report1['dateto']=$dateto;
        $report1['billigperiod']=$operationdate['billperiod'];
        $report1['duedate']=date("d M Y", strtotime($_POST['dateto'] ." + ".$operator['payment']." day"));;
        $report1['date'] = date("d M Y", strtotime($_POST['dateto'] ." + 1 day"));//$operationdate['date'];
        $this->db->delete("from `b_sequense`");
        $this->db->query("INSERT INTO  `callwaytest`.`b_sequense` ( `id`) VALUES (NULL);");
        $report1['invocenumber']=$this->db->select("`id` from `b_sequense`",0);
        $report1['operatordata']=$operator;

        //printarray($report1);
        //echo $this->db->query->last;
        //die;
       */
        return $report1;

    }
    private function sectotime($sec){
        $min =floor ( $sec/60);
        $sec=floor ($sec-$min*60);
        $hour=floor ($min/60);
        $min=$min-$hour*60;
        return $hour.":".$min.":".$sec;
    }
    private function valuedecode($txt){
        $t=explode(" ",trim($txt));
        $r['sec'] = substr($t[1], 1, strlen($t[1])-2);
        $r['time']=trim($t[0]);
        return $r;
    }
    public function testtimedivide(){
        $r = $this->timedivide("199:46:10 [719170] [12313]");
        printarray($r);
    }
    private function timedivide($txt){
        //[round_time_amount] => 199:46:10 [719170] [12313]
        $tarray= array();
        $t= explode(" ",$txt);
        $tarray['time']=$t[0];
        $tarray['sec']=substr($t[1], 1, strlen($t[1])-2);
        $tarray['minute']=substr($t[2], 1, strlen($t[2])-2);
        return $tarray;
        /*
         * Array
(
    [time] => 199:46:10
    [sec] => 719170
    [minute] => 12313
)
         */
    }
    public  function test(){
        $serverip = "95.141.192.5:8080";
        $user = "aconn";
        $password = "AhW2po1c";
        $summ = array();
            $url1 = "http://" . $serverip . "/bgbilling/executer?user=" . $user . "&pswd=" . $password . "&module=contract&action=ContractPayments&date2=31.10.2015&date1=01.10.2014&cid=22&";
            echo $url1 . "<br>";
            //запрос списка логинов
		do {

				try
				{
            $data = simplexml_load_string(file_get_contents($url1));
				} catch (Exception $e) {
				$attempts++;
					echo "Retry logic !!!!!!!!!!!!!!!!!!!!!!!!!".$attempts;
				sleep(1);
				continue;
			}

			break;

		} while($attempts < $NUM_OF_ATTEMPTS);
            $json = json_encode($data);
            $array = json_decode($json, TRUE);
        printarray($array);

        }
    public function test2(){
        $this->db->select("");
    }
}
