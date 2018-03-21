<?php
	class Detail_report_time_out extends Core_controller {
		public function __construct() {
			parent::__construct();
			$this->module_name = 'Время OUT';
		}
		
		public function index(){
			$var = array();
			$css = array();
			$js = array(
				baseurl('pub/js/bootstrap-datepicker.js'),
				baseurl('pub/js/moment.min.js'),
				baseurl('pub/js/detail_report_time_out.js')
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
			$res = $this->db->select("* from `b_detail_report_time_out` WHERE `date` LIKE '" . date('Y-m', strtotime($year . "-" . $month . "-01")) . "%'");
			if ($res) {
				foreach ($res AS $item) {
					$operators[$item['oid']]['b_detail_report_time_out'][$item['date']] = $item['money_amount'];
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
									if (isset($operators[$key]['b_detail_report_time_out'][$date])) {
										echo $operators[$key]['b_detail_report_time_out'][$date];
										$sumbyoperator=$sumbyoperator +  $operators[$key]['b_detail_report_time_out'][$date];
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
						
						echo '<td>'.$sumbyoperator.'</td></tr>';
					}
					echo '<tr><td></td><td>Total</td>';
					$totalsum =0;
					for($i=1; $i<=$end; $i++){
						if(isset($sumbyday[$i])){
							$totalsum = $totalsum + $sumbyday[$i];
							echo '<td>'.$sumbyday[$i].'</td>';
						}else{
							echo '<td>-</td>';
						}
					}
				echo '<td>'.$totalsum.'</td></tr></tbody>
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
			$data = simplexml_load_string(file_get_contents($url));
			if ((string)$data['status'] == 'ok') {
				$durationtime = (string)$data->table->data->attributes()->round_time_amount;
                //"00:26:48 [1608] [26]"
                $durationtime_temp = explode("[",$durationtime);
                $durationtime_temp = explode("]",$durationtime_temp[2]);
                $data = $durationtime_temp[0];

				$check = $this->db->select('* FROM `b_detail_report_time_out` WHERE `date` = "' . $date . '" AND `oid` = ' . $oid, 0);
				if ($check) {
					$this->db->update('b_detail_report_time_out', array(
						'round_time' => $data,
					), 'id=' . $check['id']);
				} else {
					$this->db->insert('b_detail_report_time_out', array(
						'oid' => $oid,
						'date' => $date,
						'round_time' => $data
					));
				}
				echo $data;
			} else {
				echo '-';
				$check = $this->db->select('* FROM `b_detail_report_time_out` WHERE `date` = "' . $date . '" AND `oid` = ' . $oid, 0);
				if (!$check) {
					$this->db->insert('b_detail_report', array(
						'oid' => $oid,
						'date' => $date,
						'round_time' => '-'
					));
				}
			}
		}
	}
