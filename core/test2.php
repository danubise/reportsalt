<?php
/**
 * Created by PhpStorm.
 * User: slava
 * Date: 29.07.15
 * Time: 11:59
 */

$a=array(
    1=>array(11=>"test"),
    2=>array(22=>"test2"),
    3=>array(33=>"test3")
);
    print_r($a);
    foreach($a as $key => $value){
        $a[$key][55]=$key;
    }
    print_r($a);