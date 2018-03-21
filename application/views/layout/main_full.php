<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title><?=(($this->module_name)? ' | '.$this->module_name : '')?></title>
    <link rel="stylesheet" href="<?=baseurl('pub/css/bootstrap.css')?>" media="screen">
    <link rel="stylesheet" href="<?=baseurl('pub/css/bootswatch.min.css')?>">
	<link rel="stylesheet" href="<?=baseurl('pub/css/datepicker3.css')?>">
    <?php
    if(is_array($param['css'])) {
        foreach ($param['css'] as $value) {
            echo "<link rel=\"stylesheet\" href=\"".$value."\" >\n";
        }
    }
    ?>
    <script src="<?=baseurl('pub/js/jquery-2.1.3.js')?>"></script>
    <script src="<?=baseurl('pub/js/bootstrap.min.js')?>"></script>
    <script src="<?=baseurl('pub/js/bootswatch.js')?>"></script>
</head>
<body>
<div class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <a href="<?=baseurl()?>" class="navbar-brand"></a>
            <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div class="navbar-collapse collapse" id="navbar-main">
            <ul class="nav navbar-nav">
            <li>
            <div class="btn-group">
              <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  Traffic
              </button>
              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item btn" href="<?=baseurl("detail_report_time_out")?>">Минуты OUT</a>
                <a class="dropdown-item btn" href="<?=baseurl("detail_report")?>">Трафик OUT</a>
                <a class="dropdown-item btn" href="<?=baseurl("detail_report_incomming")?>">Трафик IN</a>
              </div>
            </div>
            </li>
                <!-- <li><a href="<?=baseurl("detail_report_time_out")?>">Минуты OUT</a></li> --->
                <!-- <li><a href="<?=baseurl("detail_report")?>">Трафик OUT</a></li> --->
                <!-- <li><a href="<?=baseurl("detail_report_incomming")?>">Трафик IN</a></li> --->
				<!-- <li><a href="<?=baseurl("report")?>">Отчеты</a></li> ---->
                <li><a href="<?=baseurl("operator")?>">Операторы</a></li>
                <!-- <li><a href="<?=baseurl("report/reporthistorygoup")?>">История</a></li> --->
                <li><a href="<?=baseurl("report/invoiceresulttable")?>">Счета</a></li>
                <li><a href="<?=baseurl("report/invoicechecktable")?>">Сверка</a></li>
                <li><a href="<?=baseurl("report/finalreport")?>">Итоговый отчет</a></li>
                <li><a href="<?=baseurl("currentreport/getreport")?>">Текущий отчет</a></li>
                <li><a href="<?=baseurl("tester")?>">Тестер маршрутов</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="<?=baseurl("settings")?>"><i class="fa fa-cogs"></i> Настройки</a></li>
                <li><a href="<?=baseurl('home/logout')?>"><i class="fa fa-power-off"></i> Выход</a></li>
            </ul>

        </div>
    </div>
</div>

<div class="well" style="margin-top: 20px;">
	<? include($CONTENT)?>
</div>
<?php
if(is_array($param['js'])) {
    foreach ($param['js'] as $value) {
        echo "<script src=\"".$value."\" ></script>\n";
    }
}
?>
</html>