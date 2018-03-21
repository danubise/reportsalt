<?php
/**
 * Created by Unix develop team.
 * User: vlad
 * Date: 22.02.15
 * Time: 16:22
 *
 * Роутинг по скриптам проекта
 * Правило получение пути domain.ru/Контролер/Вьюха
 * Так же возможно создание алисов domain.ru/Приветствие == domain.ru/{Контролер: User}/{Вьюха: Welcome}
 * Конфигурация алиасов: application/data/config_route.php
 */
//printarray($_SERVER);
$clientIP = $_SERVER['REMOTE_ADDR'];
//echo $clientIP;
//echo $_SERVER['REQUEST_URI']; 
$confirm_uri = "/report/report/confirm";
$allowip="213.80.197.198 95.141.192.26 213.80.220.169 127.0.0.1/8 88.147.242.9 95.141.196.238 95.141.192.3 95.31.32.72 95.141.192.88 77.94.195.65 88.147.142.43 176.15.127.14 109.167.136.196 188.235.146.51 88.147.242.207 83.22.104.91 188.227.28.30 78.29.115.138";
$pos = strpos($allowip, $clientIP);
$confirm_access = strpos($_SERVER['REQUEST_URI'], $confirm_uri);
if($confirm_access===false &&  $pos === false){

header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
echo "404";
die;
}

if(!empty($_SERVER['argv'][1]) and !empty($_SERVER['argv'][2])) {
    $DEBUG = TRUE; //Включаем режим дебага
    $method = $_SERVER['argv'][1];
    $action = $_SERVER['argv'][2];
    if($method=='cron' and file_exists(cron.$action.EXT)) {
        echo "START CRON: ".date('Y-m-d G:i:s')."\n=============\n\n";
        require_once(cron.$action.EXT);
        $cron_name = $action."_cron";
        $Controller = new $cron_name();
        echo "\n\n=============\nEND CRON: ".date('Y-m-d G:i:s')."\n";
        die;
    }
}

$construct_route = $_SERVER['REQUEST_URI'];
if(!empty($core_dir)) {
    if(substr($core_dir, -1) != '/') {
        $core_dir .= '/';
    }
    $construct_route = "[START]".$construct_route;
    $construct_route = str_replace("[START]/".$core_dir, "", $construct_route);
}
$construct_route = explode("/", $construct_route);
$construct_route = array_diff($construct_route, array('','index.php'));
$construct_route = array_values($construct_route);
$alias_route = get_alias($construct_route[0]);

if($alias_route===FALSE) {
    $route['controller'] = (($construct_route[0])? $construct_route[0] : 'home'); //контроллер по умолчанию
    $route['view'] = (($construct_route[1])? $construct_route[1] : 'index');
} else {
    $route['controller'] = $alias_route[0];
    $route['view'] = $alias_route[1];
}

if(count($construct_route)>2 or ($alias_route and count($construct_route)>1)) {
    $start_key = 1;
    if($alias_route) $start_key = 0;
    foreach($construct_route as $key=>$value) {
        if($key>$start_key) $route['args'][] = $value;
    }
}

if(check_controller($route['controller'])) {
    $Controller = new $route['controller']();
    if(method_exists($Controller,$route['view']) and(!in_array($route['view'],$Controller->access_metod))) {
        $Controller->{$route['view']}(
            (($route['args'][0])? $route['args'][0] : null),
            (($route['args'][1])? $route['args'][1] : null),
            (($route['args'][2])? $route['args'][2] : null),
            (($route['args'][3])? $route['args'][3] : null),
            (($route['args'][4])? $route['args'][4] : null)
        );
    } else {
        echo 500; //Генерация 500 ошибки (отсутствует метод)
    }
} else {
    echo 404;//Генерация 404 ошибки (отсутствует контролер)
}
