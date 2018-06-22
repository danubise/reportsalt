<?php
    /**
     * Created by PhpStorm.
     * User: Slava
     * Date: 12.10.2015
     * Time: 21:51
     */
//include ("./dompdf/dompdf_config.inc.php");
    include ("/var/www/html/report/application/controllers/admin/dompdf/dompdf_config.inc.php");
    require_once ("/var/www/html/report/application/libs/PHPMailer-master/class.phpmailer.php");
//require_once("dompdf/dompdf_config.inc.php");
    class Report extends Core_controller {
        public function __construct() {
            parent::__construct();
            $this->module_name = 'Страница отчетов';
        }
        public function invoicedelete($invoiceid="",$from="",$to="",$operator="",$manager=""){
            //echo $invoiceid."/".$from."/".$to."/".$operator;
            $this->db->delete("from `b_invoicemain` where `invoiceid`='".$invoiceid."'");
            $this->db->delete("from `b_invoicedetail` where `invoiceid`='".$invoiceid."'");
            /*
             *  [invoicetable] => Array
        (
            [datefrom] => 2017-03-29
            [dateto] => 2017-05-31
            [operatorid] => 1495
            [manager] => all
        )

    [show] => Показать
             */
            $this->invoiceresulttable(array(
                'show' => "Показать",
                'invoicetable' => Array(
                    'datefrom' => $from,
                    'dateto' => $to,
                    'operatorid' => $operator,
                    'manager' => $manager
                )));
            return;
            $this->view(
                array(
                    'view' => 'report/invoicetable',
                    'var' => array(
                    )
                )
            );
            return;
        }

        public function invoicedownload($invoiceid=""){
            if($invoiceid==""){
                $this->view(
                    array(
                        'view' => 'report/invoicetable',
                        'var' => array(
                        )
                    )
                );
                return;
            }
            $invoice =$this->htmlformat($invoiceid);
            $dompdf = new DOMPDF;
            $dompdf->load_html($invoice['html']);
            $dompdf->render();
            $dompdf->stream("invoice.pdf");

        }
    public function finalreport(){
        //отоговая сводка по месяцам
        $filtr=false;

        if(isset($_POST['show']) || isset($_POST['download'])){
            /*
             *     [download] => Скачать
    [filtr] => Array
        (
            [datefrom] => 2015-11-01
            [dateto] => 2015-11-30
        )

             */
            //printarray($_POST);
            $filtr=$_POST['filtr'];
            $period=date("Y F",strtotime($filtr['datefrom']));


            $query="'".$period."' as `period`,
	i.*,
	i2.`operatorname`,
	i2.`endbalans`,
        o.`manager`,
        o.`currency`
FROM (
SELECT
    `operatorid`,
    SUM(`cost` ) AS `cost`,
    SUM(`invoicetovivaldi`) as `invoicetovivaldi`,
    SUM(`tovivaldi`) as `tovivaldi`,
    SUM(`topartner`) as `topartner`,
    max(`dateto`) as `maxdateto`
FROM `b_invoicemain`
where
	`datefrom` >= '".$filtr['datefrom']."' AND
        `dateto` <= '".$filtr['dateto']."'
GROUP BY `operatorid`) i
JOIN `b_invoicemain` i2 ON
	i2.`datefrom` >= '".$filtr['datefrom']."' AND
	i2.`dateto` <= '".$filtr['dateto']."' AND
	i2.`operatorid` = i.`operatorid`AND
	i2.`dateto` = i.`maxdateto`
JOIN `b_operators` o ON
	o.`id` = i.`operatorid` AND  o.`disable`=0";
            $finaldata = $this->db->select($query);

            $currencydata= $this->db->select("* from `b_currency` where `date`='".$filtr['dateto']."'");
            $currency = array();
            foreach ($currencydata as $key => $data){
                $currency[$data['currency']] = $data['price'];
            }


        }

        if(isset($_POST['download'])){
            header("Content-Type: text/csv");
            header("Content-Disposition: attachment; filename=finalreport.csv");
// Disable caching
            header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
            header("Pragma: no-cache"); // HTTP 1.0
            header("Expires: 0"); // Proxies

            $output = fopen("php://output", "w");
           /* $line[]="Период";
            $line[]="Наименование компании";
            $line[]="Приход";
            $line[]="Расход";
            $line[]="Балланс на конец месяца ";
            $line[]="Менеджер";
           */
            $line[]="Period";
            $line[]="Operator name";
            $line[]="Coming";
            $line[]="Expense";
            $line[]="End balans";
            $line[]="Manager";
            fputcsv($output, $line,";");
            $total=array(
                'preiod'=>"",
                'text'=>"Final",
                'cost'=>0,
                'invoicetovivaldi'=>0
            );
            $result=array(
                'preiod'=>"",
                'text'=>"Profit",
                'result'=>0
            );
            foreach ($finaldata as $row) {
                $total['cost']+=$row['cost'];
                $total['invoicetovivaldi']+=$row['invoicetovivaldi'];

                foreach($row as $k1 => $v1){
                    $row[$k1]=$this->changepoint($v1);
                }
                /*
                 *         <td><?=$value['period']?></td>
        <td><?=$value['operatorname']?></td>
        <td><?=$value['cost']?></td>
        <td><?=$value['invoicetovivaldi']?></td>
        <td><?=$value['endbalans']?></td>
        <td><?=$value['manager']?></td>
                 */
                $line=array();
                $line[]=$row['period'];
                $line[]=$row['operatorname'];
                $line[]=$row['cost'];
                $line[]=$row['invoicetovivaldi'];
                $line[]=$row['endbalans'];
                $line[]=$row['manager'];
                fputcsv($output, $line,";"); // here you can change delimiter/enclosure

            }
            fputcsv($output, $total,";");
            $result['result']=$total['cost']+$total['invoicetovivaldi'];
            fputcsv($output, $result,";");


            fclose($output);
            die;

        }
        if(!$filtr){
            $filtr['datefrom']=date("Y-m-01",time());
            $filtr['dateto']=date("Y-m-t",time());

        }
        $this->view(
            array(
                'view' => 'report/finalreport',
                'var' => array(
                    'finaldata'=>$finaldata,
                    'filtr'=>$filtr,
                    'currency'=> $currency
                )
            )
        );
        return;
    }

    private function recalculate(){
        for($i=1;$i<=count($_POST['maindata']);$i++){
            $detailsum=$this->db->select("SUM(`cost`) as `cost`,SUM(`time`) as `time`
                                            FROM `b_invoicedetail`
                                            WHERE `invoiceid`='".$_POST['maindata'][0]['invoiceid']."' AND `part`='".$i."'",0);
            $detailsum['timeminut'] = round($detailsum['time']/60);
            $detailsum['timetext'] = $this->sectotime($detailsum['time']);

            $this->db->update("b_invoicemain",$detailsum,"`invoiceid`='".$_POST['maindata'][0]['invoiceid']."' AND `part`='".$i."'");
        }
    }
    public function invoiceedit($invoiceid=""){

            if(isset($_POST['deleteitems'])){
                foreach($_POST['delete'] as $keyId => $value){
                    $this->db->delete("from `b_invoicedetail` where `id`='".$keyId."'");
                }
                $this->recalculate();
            }

            if(isset($_POST['addnewitem'])){

                $invoiceData = $this->db->select("* from `b_invoicemain` where `invoiceid`=".$_POST['maindata'][0]['invoiceid'], false);

                $begintime = explode("-",$invoiceData['datefrom']);
                $endtime = explode("-",$invoiceData['dateto']);

                $newLine['operatorid'] = $_POST['maindata'][0]['operatorid'];
                $newLine['invoiceid'] = $_POST['maindata'][0]['invoiceid'];
                $newLine['dest'] = $_POST['newdata']['dest'];
                $newLine['dest_code'] = $_POST['newdata']['dest_code'];
                $newLine['time'] = $_POST['newdata']['time'];
                $newLine['cost'] = $_POST['newdata']['cost'];
                $newLine['month'] = $invoiceData['month'];
                $newLine['begin'] = $begintime[2].".".$begintime[1].".".$begintime[0];
                $newLine['end'] = $endtime[2].".".$endtime[1].".".$endtime[0];
                $newLine['part'] = $_POST['newdata']['part'];
                $newLine['handmade'] = "1";

                $this->db->insert("b_invoicedetail", $newLine);
                $this->recalculate();
            }

            if(isset($_POST['save']) || isset($_POST['recalculate'])){
                foreach($_POST['maindata'] as $key=>$value){
                    $this->db->update("b_invoicemain",$value,"`id`='".$value['id']."'");
                }

                foreach($_POST['detaildata'] as $key=>$value){
                    if($key!="new"){
                        $this->db->update("b_invoicedetail",$value,"`id`='".$key."'");
                    }
                }
                $this->recalculate();

                if(!isset($_POST['recalculate'])) {
                    echo "<script>window.close();</script>";
                    die;
                }
            }

            if(isset($_POST['maindata'][0]['invoiceid'])){
                $invoiceid=$_POST['maindata'][0]['invoiceid'];
            }

            if($invoiceid==""){
                $this->view(
                    array(
                        'view' => 'report/invoicetable',
                        'var' => array(
                        )
                    )
                );
                return;
            }

            $maindata=$this->db->select("`id`,`invoiceid`,`date`,`comment`,`datefrom`,`dateto`,`operatorid`,`operatorname`,`bperiodtext`,`duedatetext`,`balans`,`realdatefrom`,`realdateto`,  `cost`,  `time`, `timeminut`,`part`
    FROM  `b_invoicemain` WHERE `invoiceid`='".$invoiceid."' ORDER BY  `b_invoicemain`.`date` DESC ");
            $detaildata=$this->db->select("`id`,`dest_code`,`dest`, `time`, `cost`,`part`, `handmade` from `b_invoicedetail` WHERE `invoiceid`='".$invoiceid."'");

            $this->view(
                array(
                    'view' => 'report/invoiceedit',
                    'var' => array(
                        'maindata'=>$maindata,
                        'detaildata'=>$detaildata
                    )
                )
            );
        }
        public function invoiceresulttable($param=""){
            if(is_array($param)){
                $_POST=$param;
            }
            //таблица счетов
            //printarray($_POST);
            $invoicetable="";
            $where ="";
            if(isset($_POST['show'])){
                $invoicetable=$_POST['invoicetable'];

                $and= false;
                if(trim($invoicetable['manager']!="all")){
                    $where.=" `manager`='".$invoicetable['manager']."' ";
                    $and=true;
                }
                if(trim($invoicetable['operatorid']!="all")){
                    if($and)$where.=" AND ";
                    $where.=" `operatorid`='".$invoicetable['operatorid']."' ";
                    $and=true;
                }

                if(trim($invoicetable['datefrom']!="")){
                    if($and)$where.=" AND ";
                    $where.=" `datefrom`>='".$invoicetable['datefrom']."' ";
                    $and=true;
                }
                if(trim($invoicetable['dateto']!="")){
                    if($and)$where.=" AND ";
                    $where.=" `dateto`<='".$invoicetable['dateto']."' ";
                    $and=true;
                }
                if($and) $where=" where ".$where;


                $data=$this->db->select("`id`,`invoiceid`,`date`,`operatorid`,`operatorname`,`bperiodtext`,`duedatetext`,
                `balans`,`realdatefrom`,`realdateto`, SUM(`cost`) AS `cost`, SUM(`time`) AS `time`,`manager`,`send`,`confirm`
                FROM  `b_invoicemain` ".$where." GROUP BY `invoiceid` ORDER BY  `b_invoicemain`.`date` DESC ");
                //echo $this->db->query->last;
                //die;

            }else {
               // $data = $this->db->select("* from `b_invoicemain`");
            }
            $operators = $this->db->select("`id`, `name` from `b_operators` WHERE `disable`=0 GROUP BY `id` ASC");
            $managers = $this->db->select("`manager` from `b_operators` WHERE `disable`=0 GROUP BY `manager` ");
            //printarray($operators);
           // printarray($data);
            //die;
            if(!is_array($invoicetable)){
                $invoicetable['datefrom']=date("Y-m-01",time());
                $invoicetable['dateto']=date("Y-m-t",time());
            }
            $this->view(
                array(
                    'view' => 'report/invoicetable',
                    'var' => array(
                        "data"=>$data,
                        "operators" => $operators,
                        "managers" => $managers,
                        'invoicetable'=> $invoicetable
                    )
                )
            );
        }
        public function recalculateoperator($operatorid=""){
            if($operatorid==""){
                return false;
            }
            //вернуть текущий баланс
                $balans=$this->db->select("`balans` FROM `b_operators` WHERE `id`='".$operatorid."'",0);
                //echo $this->db->query->last."<br>";
               //echo "balans from main tables ".$balans."<br>";
            //вернуть все данные по счетам
            $invoicearr = $this->db->select("`id`,`balans`,`invoicetovivaldi`,`endbalans`,`cost`,`tovivaldi`,`topartner` from `b_invoicemain` WHERE `operatorid`='".$operatorid."' ORDER BY  `b_invoicemain`.`date` ASC ");
            //printarray($invoicearr);
            if(is_array($invoicearr)){
                $invoicearr[0]['balans']=$balans;
                foreach($invoicearr as $key=> $value){
                    /*
                     * [0] => Array
        (
            [id] => 71
            [balans] => 100
            [invoicetovivaldi] => -1211.93
            [endbalans] => -1052.76
            [cost] => 59.17
            [tovivaldi] => 0.00
            [topartner] => 0.00
        )
                    $balans+$invoicetovivaldi+$array['table']['data']['@attributes']['money_amount']+$paytovivaldi-$paytopartner;
                     */
                    if(isset($invoicearr[$key-1]['endbalans'])){
                        $invoicearr[$key]['balans']=$invoicearr[$key-1]['endbalans'];
                        $invoicearr[$key]['endbalans']=$invoicearr[$key-1]['endbalans']+$value['invoicetovivaldi']+$value['cost']-$value['tovivaldi']+$value['topartner'];
                    }else{
                        $invoicearr[$key]['endbalans']=$balans+$value['invoicetovivaldi']+$value['cost']-$value['tovivaldi']+$value['topartner'];
                    }
                    //echo $key.") ". $invoicearr[$key]['balans']." => ".$invoicearr[$key]['endbalans']."<br>";
                }
                $query="";
                foreach($invoicearr as $key=>$value){
                    //UPDATE  `callwaytest`.`b_invoicemain` SET  `balans` =  '1001',
                    //`endbalans` =  '-1052.761' WHERE  `b_invoicemain`.`id` =71;
                    //$query="UPDATE  `callwaytest`.`b_invoicemain` SET  `balans` =  '".$value['balans']."', `endbalans`='".$value['endbalans']."' WHERE  `b_invoicemain`.`id` ='".$value['id']."'; <rb>";
                    //$this->db->query($query);
                    $this->db->update("b_invoicemain",$value,"`id` ='".$value['id']."'");
                    //echo $this->db->query->last."<br>";
                }
               //echo $query;

                //echo $this->db->query->last;

                //printarray($invoicearr);
                return true;

            }else{
                return false;
            }



        }
        private function changepoint($txt){
            return str_replace(".",",",$txt);
        }
        public  function checktabledownload($type="",$datefrom="",$dateto="",$operator="")
        {
            //http://95.141.192.26/report/report/invoicedownload/week/2015-11-01/2015-11-30/1450/1450(1450)(week)/
            $reconciliation = array(
                'type' => $type,
                'datefrom' => $datefrom,
                'dateto' => $dateto,
                'companyid' => $operator
            );

            if ($reconciliation['companyid'] != "all") {
                $selected = "AND `operatorid`='" . $reconciliation['companyid'] . "'";
            } else {
                $selected = "";
            }


            switch ($reconciliation['type']) {
                case "week":
                    $query = "`id`,`balans`,`endbalans`,`datefrom`,`dateto`,`invoicetovivaldi`, `tovivaldi`,`comment`, `topartner`, SUM(  `time` ) AS  `time` ,
 SUM(  `cost` ) AS  `cost`  from `b_invoicemain` where
                        `bperiod` = '" . $reconciliation['type'] . "' and
                        `datefrom` >= '" . $reconciliation['datefrom'] . "'
                         and `dateto` <= '" . $reconciliation['dateto'] . "' " . $selected . " GROUP BY `datefrom`";

                    break;
                case "halfmonth":
                    $query = "`id`,`balans`,`endbalans`,`datefrom`,`dateto`, `cost`, `invoicetovivaldi`, `tovivaldi`,`comment`,`topartner`  from `b_invoicemain` where
                        `bperiod` = '" . $reconciliation['type'] . "' and
                        `datefrom` >= '" . $reconciliation['datefrom'] . "'
                         and `dateto` <= '" . $reconciliation['dateto'] . "' " . $selected . " GROUP BY `datefrom`";


                    break;
                case "month":
                    $query = "`id`,`balans`,`endbalans`,`datefrom`,`dateto`, `cost`, `invoicetovivaldi`, `tovivaldi`,`comment`,`topartner`  from `b_invoicemain` where
                        `bperiod` = '" . $reconciliation['type'] . "' and
                        `datefrom` >= '" . $reconciliation['datefrom'] . "'
                         and `dateto` <= '" . $reconciliation['dateto'] . "' " . $selected . " GROUP BY `datefrom`";

                    break;
            }
            $result = $this->db->select($query);
            // echo $this->db->query->last;
            $data = array();
            $balans = 0;

            $operatordata = $this->db->select("* from `b_operators` where `id`='" . $operator . "'", 0);
            // printarray($operatordata);
            $data['name']['id1'] = "Operator";
            $data['name']['id2'] = $operatordata['id'];
            $data['name']['id3'] = $operatordata['name'];

            $data['date']['from1'] = "From ";
            $data['date']['from2'] = $reconciliation['datefrom'];
            $data['date']['to1'] = "to ";
            $data['date']['to2'] = $reconciliation['dateto'];

            $data['title'] = array("Invoice period", "begin balance", "Invoice to Vivaldi", "Invoice to Partner", "Payment to Vivald", "Payment to Partner", "End balance", "Comment");

            $total = array();
            $lastkey = 0;
            if (is_array($result)) {

            foreach ($result as $key => $value) {
                switch ($reconciliation['type']) {
                    case "week":
                        ;
                        if ($value['datefrom'] == $value['dateto']) {
                            $data[$key]['date'] = date("d M", strtotime($value['datefrom']));
                        } else {
                            $data[$key]['date'] = date("d", strtotime($value['datefrom'])) . date("-d M", strtotime($value['dateto']));
                        }
                        break;
                    case "halfmonth":
                        $data[$key]['date'] = date("d", strtotime($value['datefrom'])) . date("-d M", strtotime($value['dateto']));
                        break;
                    case "month":
                        $data[$key]['date'] = date("M Y ", strtotime($value['datefrom']));
                        break;
                }
                //$data[$key]['id']=$value['id'];

                $data[$key]['begin balance'] = round($value['balans'],2);
                $data[$key]['invoicetovivaldi'] = round($value['invoicetovivaldi'],2);
                $data[$key]['invoicetopartner'] = round($value['cost'],2);
                $data[$key]['tovivaldi'] = round($value['tovivaldi'],2);
                $data[$key]['topartner'] = round($value['topartner'],2);
                $data[$key]['End balance'] = round($value['endbalans'],2);//$data[$key]['begin balance']+$data[$key]['invoicetovivaldi']+ $data[$key]['invoicetopartner']-$data[$key]['tovivaldi']+$data[$key]['topartner'];
                $data[$key]['comment'] = $value['comment'];
                $balans = $data[$key]['End balance'];
                $lastkey = $key;

                $total['balans'] += $value['balans'];
                $total['invoicetovivaldi'] += $value['invoicetovivaldi'];
                $total['invoicetopartner'] += $value['cost'];
                $total['tovivaldi'] += $value['tovivaldi'];
                $total['topartner'] += $value['topartner'];
                $total['End balance'] += $data[$key]['End balance'];

            }
        }
            $lastkey++;


            $data[$lastkey]['date']="Total";
            $data[$lastkey][1]=" ";

            $data[$lastkey]['invoicetovivaldi']=round($total['invoicetovivaldi'],2);
            $data[$lastkey]['invoicetopartner']=round($total['invoicetopartner'],2);
            $data[$lastkey]['tovivaldi']=round($total['tovivaldi'],2);
            $data[$lastkey]['topartner']=round($total['topartner'],2);

           // $data[$lastkey]['End balance']=$value['endbalans'];//$data[$key]['begin balance']+$data[$key]['invoicetovivaldi']+ $data[$key]['invoicetopartner']-$data[$key]['tovivaldi']+$data[$key]['topartner'];
            //$data[$lastkey]['comment']=$value['comment'];

            header("Content-Type: text/csv");
            header("Content-Disposition: attachment; filename=file.csv");
// Disable caching
            header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
            header("Pragma: no-cache"); // HTTP 1.0
            header("Expires: 0"); // Proxies

            $output = fopen("php://output", "w");
            foreach ($data as $row) {
                foreach($row as $k1 => $v1){
                    $row[$k1]=$this->changepoint($v1);
                }
                fputcsv($output, $row,";");
            }

            fclose($output);

        }


        public  function invoicechecktable(){
            //таблица сверок
            $query_com="`id` as `operatorid`,`name` as `operatorname`,`bperiod`  FROM  `b_operators` GROUP BY  `id` ASC";
            $company = $this->db->select($query_com);

            if(isset($_POST['save'])){

                $balans=$_POST['balans'];
                foreach($balans as $key=>$value){
                    $this->db->update("b_invoicemain",$value,"`id`='".$key."'");
                   //echo $this->db->query->last;
                }
                $comment = $_POST['comment'];

                if(!$this->recalculateoperator($_POST['reconciliation']['companyid'])){
                    echo "ошибка перерасчета свекри ";
                    printarray($_POST);

                }
                //die;
            }

            if(isset($_POST['show']) || isset($_POST['save'])){

                $reconciliation=$_POST['reconciliation'];
                $fields=" `operatorid` ,
 `invoiceid` ,
 SUM(  `time` ) AS  `time` ,
 SUM(  `cost` ) AS  `cost` ,
 MIN(  `datefrom` ) AS  `datefrom` ,
 MAX(  `dateto` ) AS  `dateto` ,
 COUNT(  `invoiceid` ) AS  `count` ";
                if($reconciliation['companyid']!="all"){
                    $selected="AND `operatorid`='".$reconciliation['companyid']."'";
                }else
                {
                    $selected="";
                }

                switch($reconciliation['type']){
                    case "week":
                        $query = "`id`,`balans`,`invoiceid`,`invoicecomment`,`endbalans`,`datefrom`,`dateto`,`invoicetovivaldi`, `tovivaldi`,`comment`, `topartner`, SUM(  `time` ) AS  `time` ,
 SUM(  `cost` ) AS  `cost`  from `b_invoicemain` where
                        `bperiod` = '".$reconciliation['type']."' and
                        `datefrom` >= '".$reconciliation['datefrom']."'
                         and `dateto` <= '".$reconciliation['dateto']."' ".$selected." GROUP BY `datefrom`";

                        break;
                    case "halfmonth":
                        $query = "`id`,`balans`,`invoiceid`,`invoicecomment`,`endbalans`,`datefrom`,`dateto`, `cost`, `invoicetovivaldi`, `tovivaldi`,`comment`,`topartner`  from `b_invoicemain` where
                        `bperiod` = '".$reconciliation['type']."' and
                        `datefrom` >= '".$reconciliation['datefrom']."'
                         and `dateto` <= '".$reconciliation['dateto']."' ".$selected." GROUP BY `datefrom`";


                        break;
                    case "month":
                        $query = "`id`,`balans`,`invoiceid`,`invoicecomment`,`endbalans`,`datefrom`,`dateto`, `cost`, `invoicetovivaldi`, `tovivaldi`,`comment`,`topartner`  from `b_invoicemain` where
                        `bperiod` = '".$reconciliation['type']."' and
                        `datefrom` >= '".$reconciliation['datefrom']."'
                         and `dateto` <= '".$reconciliation['dateto']."' ".$selected." GROUP BY `datefrom`";

                        break;
                }
                $result = $this->db->select($query);
                //echo $this->db->query->last;
                $data = array();
                $balans=0;
                if(is_array($result))
                    //echo "8***";
                    //printarray($result);
                //echo "999";
                    $total= array();
                if($result != "")
                    foreach($result as $key=> $value){
                        switch($reconciliation['type']){
                            case "week":;
                                if ($value['datefrom'] == $value['dateto']) {
                                    $data[$key]['date'] = date("d M", strtotime($value['datefrom']));
                                } else {
                                    $data[$key]['date'] = date("d", strtotime($value['datefrom'])) . date("-d M", strtotime($value['dateto']));
                                }
                                break;
                            case "halfmonth":
                                $data[$key]['date'] = date("d", strtotime($value['datefrom'])) . date("-d M", strtotime($value['dateto']));
                                break;
                            case "month":
                                $data[$key]['date'] = date("M Y ", strtotime($value['datefrom']));
                                break;
                        }
                        $data[$key]['id']=$value['id'];

                        $data[$key]['begin balance']=$value['balans'];

                        $data[$key]['invoicetovivaldi']=$value['invoicetovivaldi'];
                        $data[$key]['invoicetopartner']=$value['cost'];
                        $data[$key]['tovivaldi']=$value['tovivaldi'];
                        $data[$key]['topartner']=$value['topartner'];
                        $data[$key]['End balance']=$value['endbalans'];//$data[$key]['begin balance']+$data[$key]['invoicetovivaldi']+ $data[$key]['invoicetopartner']-$data[$key]['tovivaldi']+$data[$key]['topartner'];
                        $data[$key]['invoicecomment']=$value['invoicecomment'];
                        $balans=$data[$key]['End balance'];
                        $lastkey=$key;

                        $total['balans']+= $value['balans'];
                        $total['invoicetovivaldi']+=$value['invoicetovivaldi'];
                        $total['invoicetopartner']+=$value['cost'];
                        $total['tovivaldi']+=$value['tovivaldi'];
                        $total['topartner']+=$value['topartner'];
                        $total['End balance']+= $data[$key]['End balance'];

                    }

                //printarray($data);
               // printarray($result);
                $comment= $this->db->select("* from `b_comment`");
                //printarray($comment);
            }
            if(!isset($reconciliation['datefrom'])){
                $reconciliation['datefrom']=date("Y-m-01",time());
                $reconciliation['dateto']=date("Y-m-t",time());
            }

            $this->view(
                array(
                    'view' => 'report/invoicechecktable',
                    'var' => array(
                        'reconciliation'=>$reconciliation,
                        'data'=>$data,
                        'companylist' => $company,
                        'total'=>$total,
                        'comment'=>$comment

                    )
                )
            );
        }


        public function index() {

            $this->view(
                array(
                    'view' => 'report/index',
                    'var' => array()
                )
            );
        }
        public function select($id){
            $this->view(
                array(
                    'view' => 'report/index',
                    'var' => array(
                        'clientid'=>$id
                    )
                )
            );
            return;
        }
        public function showreportgroup($clientid){
            $reportlist = $this->db->select("* from `b_reports` where `clientid`='".$clientid."'");
            $this->view(
                array(
                    'view' => 'report/showreportgroup',
                    'var' => array(
                        'operators'=>$reportlist
                    )
                )
            );
        }
        public function reporthistorygoup(){
            $reportgroup=$this->db->select("`clientid`,count(*) as totalreport from `b_reports` order by `clientid`");
            /*
             * Array
    (
        [0] => Array
            (
                [clientid] => 22
                [totalreport] => 11
            )

    )

             */
            //printarray($reportgroup);
            //die;
            $this->view(
                array(
                    'view' => 'report/reportgrouphistory',
                    'var' => array(
                        'operators'=>$reportgroup
                    )
                )
            );

        }
        public function invoicehistory(){
            if(isset($_POST['show'])) {
                $invoices = $this->db->select("*");
                }
        }

        public function sendmail()
        {

            $dompdf=new DOMPDF();
            $report=$this->gethtml("");
            $dompdf->load_html($report['html']);
            $dompdf->render();
            $data = $dompdf->output();
            exec("mkdir /var/www/html/report/application/cloud/".$_POST['clientid']);
            $filename=$_POST['clientid']."/".$_POST['clientid']."-".microtime(true).".pdf";

            $reportname="/var/www/html/report/application/cloud/".$filename;

            file_put_contents($reportname, $data);


            $reporttimedata = $this->getreportdata("");
            /*
             *  $report1['data']=$report;
            $report1['total']=$summ;
            $report1['aliase']=$aliase;
            $report1['datefrom']=$datefrom;
            $report1['dateto']=$dateto;
            $report1['billigperiod']=$operationdate['billperiod'];
             */
            $arraydate=array(
                'filename'=>$reportname,
                'clientid'=>$_POST['clientid'],
                'confirm' => false,
                'confirmcode' => md5($reportname)

            );
            $this->db->insert("b_reports",$arraydate);

            $mail['theme']=" Invoice from Vivaldi Telecom LP for the period ".$reporttimedata['billigperiod'];
            $mail['body']="Dear Partner!

Please find attached our invoice for the period ".$reporttimedata['billigperiod']."

Please take the confirm.

Please confirm receipt of this Invoice by pushing the following link

http://95.141.192.26/report/report/confirm/".$arraydate['confirmcode']."
--

WBR,<br>

Financial Department

Vivaldi Telecom

tel/fax:+44 131 6081121

Please find attached our invoice.

Period ".$reporttimedata['billigperiod'].".";
//printarray($report['data']);
            //    die;

            $mail['from']="billind@vivaldy.ru";
            $mail['to']=$report['data']['operatordata']['mail'];

            $email = new PHPMailer();
            $email->From      = $mail['from'];
            $email->FromName  = $mail['from'];
            $email->Subject   = $mail['theme'];
            $email->Body      = $mail['body'];
            $email->AddAddress( $mail['to'] );
            $invocename="invoce".str_replace(" ","-",$report['data']['billigperiod']);
            $email->AddAttachment( $reportname , $invocename );

            $email->Send();
            $invoiceresult = array(
                "invoicenumber" =>$report['data']['invocenumber'],
                "price" =>$report['data']['total']['cost'],
                "periodfrom" =>$report['data']['datefrom'],
                "periodto" => $report['data']['dateto'],
                "clientid" =>$report['data']['operatordata']['id'],
                "date"=>$report['data']['operatordata']['date'],
                "manager"=>$report['data']['operatordata']['manager'],
                "operatorname" => $report['data']['operatordata']['name']
            );
            $this->db->insert("b_invoiceresult",$invoiceresult);
            $this->index();
            // echo $this->db->query->last;

        }
        public function reportformat(){

            $dompdf=new DOMPDF();
            $head="<html><head></head><body>";
            $end="</body></html>";
            $report=file_get_contents('/var/www/html/report/application/views/admin/report/pdf.php');
            $dompdf->load_html($head.$report.$end);
            $dompdf->render();
            $output = $dompdf->output();
            file_put_contents("/var/www/html/report/application/controllers/admin/report.pdf", $output);
            $this->view(
                array(
                    'view' => 'report/pdf',
                    'var' => array()
                )
            );
            return;
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
            if (is_array($detaildata)){
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
            }else{

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
            // $page1=implode($page1);
            // printarray($page1);
            //  die;
            $html['maindata']=$maindata;
            $html['html']=$head.$page1.$detailtable['head'].$detailtable['body'].$detailtable['end'].$end;
            // echo $html['html'];
            //die;
            // $data['billigperiod']=$reportdata['billigperiod'];
            //echo "html ------------------";
            //printarray($html);
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
        private function getreportdata($id){
            $operator = $this->db->select("* from `b_operators` where  `disable`=0 AND `id`='".$_POST['clientid']."'",0);
            //printarray($operator);
            $dateto = date("d.m.Y", strtotime($_POST['dateto']));
            $datefrom = date("d.m.Y", strtotime($_POST['datefrom']));
            //echo $datefrom." ".$dateto."<br>";
            $operationdate= $this->billingdate(array(
                'dateto'=>$dateto,
                'datefrom'=>$datefrom,
                'duedate'=>$operator['payment']
            ));

            //printarray($operationdate);
            //die;
            //$m1 =
            //$operationdate[]['from']=


            $serverip = "95.141.192.5:8080";
            $user = "aconn";
            $password = "AhW2po1c";
            $summ = array();
            $url = "http://" . $serverip . "/bgbilling/executer?user=aconn&pswd=AhW2po1c&module=voiceip&pageSize=100&mask=&contentType=xml&cid=22&pageIndex=1&unit=1&action=LoginsAmount&date2=21.09.2015&mid=4&date1=12.09.2015&";

            //$url="http://".$serverip."/bgbilling/executer?BGBillingSecret=AhW2po1c&module=voiceip&dateto=21.09.2015&action=GetLogins&mid=4&datefrom=12.09.2015&cid=22&";
            //$url1 = "http://" . $serverip . "/bgbilling/executer?user=" . $user . "&pswd=" . $password . "&module=voiceip&dateto=" . $dateto . "&action=GetLogins&mid=4&datefrom=" . $datefrom . "&cid=" . $_POST['clientid'] . "&";
            //$url2="http://" . $serverip . "/bgbilling/executer?user=" . $user . "&pswd=" .$password . "&module=voiceip&direct=1&mid=4&pageSize=100&date2=".$_POST['dateto']."&date1=1".$_POST['datefrom']."&nofree=1&unit=1&pageIndex=1&action=LoginDirect&id=14%2C74%2C428%2C651%2C1040%2C1049%2C1324&contentType=xml&cid=22&mask=&order=&";
            //$url = "http://" . $serverip . "/bgbilling/executer?user=" . $user . "&pswd=" .$password . "&module=voiceip&pageSize=10000&mask=&contentType=xml&cid=".$_POST['clientid']."&pageIndex=1&id=1&unit=1&action=LoginSessions&date2=".$_POST['dateto']."&mid=4&date1=".$_POST['datefrom']."&";

            foreach($operationdate['period'] as $key=>$date) {
                $url1 = "http://" . $serverip . "/bgbilling/executer?user=" . $user . "&pswd=" . $password . "&module=voiceip&dateto=" . $date['end'] . "&action=GetLogins&mid=4&datefrom=" . $date['begin'] . "&cid=" . $_POST['clientid'] . "&";
                //echo $url1 . "<br>";

                $data = simplexml_load_string(file_get_contents($url1));
                $json = json_encode($data);
                $array = json_decode($json, TRUE);
                $report1['log']['url1'][$key]['url']=$url1;
                $report1['log']['url1'][$key]['date']=$date;
                $report1['log']['url1'][$key]['answer1']=$array;

                // printarray($array);

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
                $aliase = "";
                $k1 = true;
                foreach ($array['logins']['item'] as $value) {
                    if ($k1) {
                        $aliase .= $value['@attributes']['id'];
                        $k1 = false;
                    } else {
                        $aliase .= "," . $value['@attributes']['id'];
                    }
                }
                //echo $aliase . "<br>";
                $url2 = "http://" . $serverip . "/bgbilling/executer?user=" . $user . "&pswd=" . $password . "&module=voiceip&direct=1&mid=4&pageSize=100&date2=" .  $date['end'] . "&date1=" .  $date['begin']  . "&nofree=1&unit=1&pageIndex=1&action=LoginDirect&id=" . urlencode($aliase) . "&contentType=xml&cid=22&mask=&order=&";
                //echo $url2 . "<br>";
                //die;

                $data = simplexml_load_string(file_get_contents($url2));
                $json = json_encode($data);
                $array = json_decode($json, TRUE);
                $report1['log']['url2'][$key]['url']=$url1;
                // $report['log']['url2'][$key]['date']=$date;
                $report1['log']['url2'][$key]['answer1']=$array;
                //printarray($array);
                //die;
                //$data = array();



                if (isset($array['table']['data']['row'])) {

                    foreach ($array['table']['data']['row'] as $a1) {
                        //printarray($a1);
                        if(isset($a1['@attributes'])) {

                            $report[$a1['@attributes']['dest_code']] = $this->routesumm($report[$a1['@attributes']['dest_code']], $a1['@attributes']);
                            $summ['count'] += $a1['@attributes']['count'];
                            $r = $this->valuedecode($a1['@attributes']['round_time']);
                            $summ['round_time']['sec'] += $r['sec'];
                            $r = $this->valuedecode($a1['@attributes']['time']);
                            $summ['time']['sec'] += $r['sec'];
                            //$report[$a1['@attributes']['dest_code']]['cost']+= $a1['@attributes']['cost'];
                            $summ['cost']+=$a1['@attributes']['cost'];
                            //echo $a1['@attributes']['cost']."   ".$a1['@attributes']['dest_code']."<br>";
                        }
                        else{
                            $report[$a1['dest_code']] = $this->routesumm($report[$a1['@attributes']['dest_code']], $a1);
                            $summ['count'] += $a1['count'];
                            $r = $this->valuedecode($a1['round_time']);
                            $summ['round_time']['sec'] += $r['sec'];
                            $r = $this->valuedecode($a1['time']);
                            $summ['time']['sec'] += $r['sec'];
                            //$report[$a1['@attributes']['dest_code']]['cost']+= $a1['@attributes']['cost'];
                            $summ['cost']+=$a1['cost'];
                            //echo $a1['@attributes']['cost']."   ".$a1['@attributes']['dest_code']."<br>";
                        }


                    }
                }
            }
            //  printarray($report);
            // die;
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
            return $report1;

        }
        public function showreport($id)
        {
            if (!isset($_POST['clientid'])) {
                $this->view(
                    array(
                        'view' => 'report/index',
                        'var' => array()
                    )
                );
                return;
            }
            $reportdata = $this->getreportdata($id);
            // printarray($reportdata);
            //die;
            $this->view(
                array(
                    'view' => 'report/showreport',
                    'var' => array(
                        'data'=>$reportdata['data'],
                        'clientid'=>$_POST['clientid'],
                        'summ'=>$reportdata['total'],
                        'contractTitle'=>$reportdata['contractTitle'],
                        'datefrom'=>$reportdata['datefrom'],
                        'dateto'=>$reportdata['dateto'],
                        'aliase'=>$reportdata['aliase']
                    )
                )
            );
        }
        public function img($name){
            $file="/var/www/html/report/application/views/admin/report/".$name;
            header('Content-Type: image/jpeg');
            header('Content-Length: ' . filesize($file));
            $img = file_get_contents($file);
            echo $img;
        }

        public function gethtml($id){
            $reportdata = $this->getreportdata($id);
            //printarray($reportdata);
            //die;

            $head="<html><head><meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" /></head><body>";
            $end="</body></html>";

            $page1 = file_get_contents('/var/www/html/report/application/views/admin/report/page1.php');

            $page1 = str_replace("billigperiod",$reportdata['billigperiod'],$page1);
            $page1 = str_replace("traffictotal",round($reportdata['total']['round_time']['sec']/60,2),$page1);
            $page1 = str_replace("costtotal",$reportdata['total']['cost'],$page1);
            $page1 = str_replace("duedate",$reportdata['duedate'],$page1);
            $page1 = str_replace("currentdate",$reportdata['date'],$page1);
            $page1 = str_replace("invocenumber",$reportdata['invocenumber'],$page1);



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
            foreach($reportdata['data'] as $report) {
                $detailtable['body'] .= "
    <tr style=\"height:34px;\">
        <td style=\"min-width:50px\">
            <nobr>".$report['dest']."&nbsp;</nobr>
        </td>
        <td style=\"min-width:50px\">
            <nobr>".$report['timetime']."&nbsp;[".$report['time']."]&nbsp;</nobr>
        </td>
        <td style=\"min-width:50px\">
            <nobr>".$report['cost']."</nobr>
        </td>
    </tr>";


            }
            /*
             *  <td>Total&nbsp;</td>
            <td><?=$summ['dest_code']?>&nbsp;</td>
            <td><?=$summ['count']?>&nbsp;</td>
            <td><?=$summ['cost']?>&nbsp;</td>
            <td><?=$summ['acd']?>&nbsp;</td>
            <td><?=$summ['asr']?>&nbsp;</td>
            <td><?=$summ['round_timetime']?>&nbsp;<?=$summ['round_time']['sec']?>&nbsp;</td>
            <td><?=$summ['timetime']?>&nbsp;<?=$summ['time']['sec']?>&nbsp;</td>
             */
            $detailtable['end']="
    <tr style=\"height:17px;\">
        <td style=\"background-color:#a0e0e0;min-width:50px\">
            <nobr>Total:</nobr>
        </td>
        <td style=\"text-align:right;background-color:#a0e0e0;border:1px solid;border-left-color:#333333;border-right-color:#333333;border-top-color:#333333;border-bottom-color:#333333;min-width:50px\">
            <nobr>".$reportdata['total']['timetime']."&nbsp;[".$reportdata['total']['time']['sec']."]&nbsp;</nobr>
        </td>
        <td style=\"text-align:right;background-color:#a0e0e0;border:1px solid;border-left-color:#333333;border-right-color:#333333;border-top-color:#333333;border-bottom-color:#333333;min-width:50px\">
            <nobr>".$reportdata['total']['cost']."</nobr>
        </td>
    </tr>
    </tbody>
</table>

";
            // $page1=implode($page1);
            // printarray($page1);
            //  die;
            $pdfformat=$head.$page1.$detailtable['head'].$detailtable['body'].$detailtable['end'].$end;
            //echo $pdfformat;
            //die;
            // $data['billigperiod']=$reportdata['billigperiod'];
            return array(
                "html"=>$pdfformat,
                "data" => $reportdata
            );
        }
        public function showhtmlpdf($id){
            $report = $this->gethtml($id);


            echo $report['html'];
        }
        public function downloadreport($id){


            $dompdf=new DOMPDF();
            $report=$this->gethtml($id);
            $dompdf->load_html($report['html']);
            $dompdf->render();
            $dompdf->stream("report.pdf");

            //$output = $dompdf->output();
            //file_put_contents("/var/www/html/report/application/controllers/admin/report.pdf", $output);


        }
        private function valuedecode($txt){
            $t=explode(" ",trim($txt));
            $r['sec'] = substr($t[1], 1, strlen($t[1])-1);
            $r['time']=trim($t[0]);
            return $r;
        }
        private function routesumm($r1,$r2){
            /*
             * [acd] => -1;00:08:20 [500]
                [asr] => -1;100%
                [cost] => 131.20973
                [count] => 2249
                [dest] => Russia, Saratov
                [dest_code] => 5312
                [round_time] => 312:24:15 [1124655]
                [time] => 312:24:15 [1124655]
             */
            $r= array();
            //echo "r1**********";
            // printarray($r1);
            //echo "r2***********";
            //printarray($r2);
            //echo "end********";
            //$r['asr']="asr";

            $r2sec=$this->valuedecode($r2['acd']);
            $r['acd']=floor (($r1['acd']+$r2sec['sec'])/2);

            $r['acdtime']=$this->sectotime($r['acd']);
            $asr=explode(";",$r2['asr']);

            if($r['asr']>$asr[1]) {$r['asr']=$asr[1];}
            else {$r['asr']="100%";}
            $r['cost']=$r1['cost']+$r2['cost'];
            $r['count']=$r1['count']+$r2['count'];
            $r['dest']=$r2['dest'];
            $r['dest_code']=$r2['dest_code'];
            //$r1sec=$this->valuedecode($r1['round_time']);
            $r2sec=$this->valuedecode($r2['round_time']);
            $r['round_time']=$r1['round_time']+$r2sec['sec'];
            $r['round_timetime']=$this->sectotime($r['round_time']);
            $r1sec=$this->valuedecode($r1['time']);
            $r2sec=$this->valuedecode($r2['time']);
            $r['time']=$r1['time']+$r2sec['sec'];
            $r['timetime']=$this->sectotime($r['time']);
            return $r;
        }
        private function sectotime($sec){
            $min =floor ( $sec/60);
            $sec=floor ($sec-$min*60);
            $hour=floor ($min/60);
            $min=$min-$hour*60;
            return $hour.":".$min.":".$sec;
        }

        public function logout() {
            $this->user_model->logout();
            header('Location: '.baseurl());
        }
    }
