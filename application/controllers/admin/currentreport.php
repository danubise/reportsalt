<?php
	class Currentreport extends Core_controller {
        public function __construct() {
            parent::__construct();
            $this->module_name = 'Страница текущего отчетов';
        }

        public function index(){
            $this->view(
                array(
                    'view' => 'report/currentreport'
                )
            );
        }

        public function downloadreport() {

            $serverip = "95.141.192.5:8080";
            $user = "aconn";
            $password = "AhW2po1c";
            $url = 'http://'.$serverip.'/bgbilling/executer?user='.$user.'&pswd='.$password.'&module=contract&action=ContractInfo&list=1&cid=';
            $allOperators = $this->db->select('`id`,`name`,`bperiod`,`currency`,`payment` FROM `b_operators` ');

            foreach($allOperators as $key=>$operatorData){
                $xmlObj = simplexml_load_string(file_get_contents($url.$operatorData['id']));
                $allOperators[$key]['limit']= (string)$xmlObj->contract->attributes()->limit;
                $allOperators[$key]['summa6']= (string)$xmlObj->info->balance->attributes()->summa6;

            }
            header("Content-Type: text/csv");
            header("Content-Disposition: attachment; filename=currentreport.csv");

            header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
            header("Pragma: no-cache"); // HTTP 1.0
            header("Expires: 0"); // Proxies

            $output = fopen("php://output", "w");
            foreach ($allOperators as $row) {
                foreach($row as $k1 => $v1){
                    $row[$k1]=$this->tranlate($k1);
                }
                fputcsv($output, $row,";");
                break;
            }

            foreach ($allOperators as $row) {
                foreach($row as $k1 => $v1){
                    $row[$k1]=$this->changepoint($this->tranlate($v1));
                }
                fputcsv($output, $row,";");
            }

            fclose($output);
        }
        private function tranlate($value){
            switch($value){
                case "currency":
                    return "Currency";
                case "week":
                    return "Week";
                case "manth":
                    return "Month";
                case "halfmonth":
                    return "HalfMonth";
                case "id":
                    return "id";
                case "name":
                    return "Operator name";
                case "bperiod":
                    return "Bill period";
                case "payment":
                    return "Payment day";
                case "limit":
                    return "Limit";
                case "summa6":
                    return "Balans";


            }
            return $value;
        }

        private function changepoint($txt){
            return str_replace(".",",",$txt);
        }

    }