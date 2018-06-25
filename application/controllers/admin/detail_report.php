<?php
	class Detail_report extends Core_controller {
		public function __construct() {
			parent::__construct();
			$this->module_name = 'Трафик OUT';
		}
		
		public function index(){
			$var = array();
			$css = array();
			$js = array(
				baseurl('pub/js/bootstrap-datepicker.js'),
				baseurl('pub/js/moment.min.js'),
				baseurl('pub/js/detail_report.js')
			);
			$this->view(
				array(
					'page' => 'main_full',
					'var' => $var,
					'js' => $js,
					'css' => $css,
					'view' => 'detailreport/index'
				)
			);
		}
		
		public function getList() {
			$month = $_POST['month'];
			$year = $_POST['year'];
			$end = date('t', strtotime('01.' . $month . '.' . $year));
			$res = $this->db->select("* from `b_operators` where `disable`=0 ORDER BY `id` ASC");
			if ($res) {
				foreach ($res AS $item) {
					$operators[$item['id']] = $item;
				}
			}
            $currencydata= $this->db->select("* from `b_currency` where `date` LIKE '" . date('Y-m', strtotime($year . "-" . $month . "-01")) . "%'");

			$currency = array();
            if($currencydata) {
                foreach ($currencydata as $key => $data) {
                    $currency[$data['date']][$data['currency']] = $data['price'];
                }
            }
/*
 *     $currency ====
 * [2018-06-22] => Array
        (
            [USD] => 63.7873
            [EUR] => 73.6871
        )
 *
 */
			$res = $this->db->select("* from `b_detail_report` WHERE `date` LIKE '" . date('Y-m', strtotime($year . "-" . $month . "-01")) . "%'");
			if ($res) {
				foreach ($res AS $item) {

                    if($operators[$item['oid']]['currency'] == 'RUB' && isset($currency[$item['date']]['USD'])){
                        $item['money_amount'] = $item['money_amount'] / $currency[$item['date']]['USD'];
                    }
                    if($operators[$item['oid']]['currency'] == 'EUR' && isset($currency[$item['date']]['EUR']) && isset($currency[$item['date']]['USD'])){
                        $item['money_amount'] = $item['money_amount'] * $currency[$item['date']]['EUR'] / $currency[$item['date']]['USD'];

                    }

					$operators[$item['oid']]['b_detail_report'][$item['date']] = $item['money_amount'];
				}
			}
			echo '<hr>
			<table class="table table-striped" id="tableNum">
				<thead>
					<tr>
						<th>id</th>
						<th>name</th>';
						for ($i = 1; $i <= $end; $i++) {
							$date = date('Y-m-d', strtotime($i . '.' . $month . '.' . $year));
							echo '<th date="' . $date . '">';
							if ($date <= date('Y-m-d'))
								echo '<a href="" id="refresh_detail_reports_on_day">';
							echo $i;
							if ($date <= date('Y-m-d'))
								echo '</a>';
							echo '</th>';
						}
					echo '<th>Total</th></tr>
				</thead>
				<tbody>';
				$sumbyday = array();
					foreach ($operators AS $key => $operator) {
					    if(empty($operator['name'])){ continue; }
						$sumbyoperator=0;
						echo '<tr>
							<th>'.$key.'</th>
							<th><a href="" id="refresh_detail_reports">'.$operator['name'].'</a></th>';
							for ($i = 1; $i <= $end; $i++) {
								$date = date('Y-m-d', strtotime($i . '.' . $month . '.' . $year));
								echo '<td id="' . $date . '" oid="' . $key . '" date="' . $date . '">';
								if ($date <= date('Y-m-d'))
									echo '<a href="" id="refresh_detail_report_item">';
									if (isset($operators[$key]['b_detail_report'][$date])) {
										echo number_format($operators[$key]['b_detail_report'][$date], 2, '.', '');
										$sumbyoperator=$sumbyoperator +  $operators[$key]['b_detail_report'][$date];
										if(!isset($sumbyday[$i])){
											$sumbyday[$i] = 0;
										}
										$sumbyday[$i] = $sumbyday[$i] +  $operators[$key]['b_detail_report'][$date];
									} else {
										echo '-';
									}
								if ($date <= date('Y-m-d'))
									echo '</a>';
								echo '</td>';
							}
						
						echo '<td>'.number_format($sumbyoperator, 2, '.', '').'</td></tr>';
					}
					echo '<tr><td></td><td>Total</td>';
					$totalsum =0;
					for($i=1; $i<=$end; $i++){
						if(isset($sumbyday[$i])){
							$totalsum = $totalsum + $sumbyday[$i];
							echo '<td>'.number_format($sumbyday[$i], 2, '.', '').'</td>';
						}else{
							echo '<td>-</td>';
						}
					}
				echo '<td>'.number_format($totalsum, 2, '.', '').'</td></tr></tbody>
			</table>';
		}
		
		public function refresh_detail_report() {
			$oid = $_POST['oid'];
			$date = $_POST['date'];

			$serverip = "95.141.192.5:8080";
			$user = "aconn";
			$password = "AhW2po1c";
			$summ = array();
			$url = 'http://'.$serverip.'/bgbilling/executer?user='.$user.'&pswd='.$password.'&module=voiceip&pageSize=100&direct=1&mask=&contentType=xml&cid='.$oid.'&pageIndex=1&unit=1&action=LoginsAmount&date2='.date('d.m.Y', strtotime($date)).'&mid=4&date1='.date('d.m.Y', strtotime($date));

			$currencydata= $this->db->select("* from `b_currency` where `date` ='".$date."'");
            $currency = array();
            if($currencydata != false) {
                foreach ($currencydata as $key => $data) {
                    $currency[$data['date']][$data['currency']] = $data['price'];
                }
            }

            $operatorCurrency = $this->db->select("currency from `b_operators` where `disable`=0 and `id` = '".$oid."'",false);

			$data = simplexml_load_string(file_get_contents($url));
			if ((string)$data['status'] == 'ok') {
				$data = (float)$data->table->data->attributes()->money_amount;
			
				$check = $this->db->select('* FROM `b_detail_report` WHERE `date` = "' . $date . '" AND `oid` = ' . $oid, 0);
				if ($check) {
					$this->db->update('b_detail_report', array(
						'money_amount' => $data,
					), 'id=' . $check['id']);
				} else {
					$this->db->insert('b_detail_report', array(
						'oid' => $oid,
						'date' => $date,
						'money_amount' => $data
					));
				}
                if($operatorCurrency == 'RUB' && isset($currency[$date]['USD'])){
                    $data = $data / $currency[$date]['USD'];
                }
                if($operatorCurrency == 'EUR' && isset($currency[$date]['EUR']) &&  isset($currency[$date]['USD'])){
                    $data = $data * $currency[$date]['EUR'] / $currency[$date]['USD'];

                }
				echo number_format($data, 2, '.', '');
			} else {
				echo '-';
				$check = $this->db->select('* FROM `b_detail_report` WHERE `date` = "' . $date . '" AND `oid` = ' . $oid, 0);
				if (!$check) {
					$this->db->insert('b_detail_report', array(
						'oid' => $oid,
						'date' => $date,
						'money_amount' => '-'
					));
				}
			}
		}
	}
