<?php
/**
 * Created by PhpStorm.
 * User: slava
 * Date: 18.11.15
 * Time: 15:54
 *             <select name="filrt[type]">
<option value="week" >Недельный</option>
<option value="halfmonth" >Полумесячный</option>
<option value="month" >Месячный</option>
</select>
 *             <select name="filrt[month]">
<option value="1" >Январь</option>
<option value="2" >Февраль</option>
<option value="3" >Март</option>
<option value="4" >Апрель</option>
<option value="5" >Май</option>
<option value="6" >Июнь</option>
<option value="7" >Июль</option>
<option value="8" >Август</option>
<option value="9" >Сентябрь</option>
<option value="10" >Октябрь</option>
<option value="11" >Ноябрь</option>
<option value="12" >Декабрь</option>
</select>
 */
//print_r($filtr);
?>
<form method="post"  action="<?=baseurl('report/finalreport')?>">
    <table>
        <tr>
            <td>
                <select name="filtr[company]">
                    <option value="" selected>Все</option>
                    <option value="Vivaldi Canada" <?php if($filtr['company']=="Vivaldi Canada") echo "selected"; ?>>Vivaldi Canada</option>
                    <option value="Vivaldi Bulgary" <?php if($filtr['company']=="Vivaldi Bulgary") echo "selected"; ?>>Vivaldi Bulgary</option>
                </select>
            </td>
            <td><input type="date" name="filtr[datefrom]" value="<?=$filtr['datefrom']?>" ></td>
            <td><input type="date" name="filtr[dateto]" value="<?=$filtr['dateto']?>" ></td>
            <td><input type="submit" name="show" value="Показать"></td>
            <td><input type="submit" name="download" value="Скачать"></td>
        </tr>
    </table>
</form>
<table  class="table table-striped" id="tableNum">
    <tr>
        <th>Период</th>
        <th>Название оператора</th>
        <th>Приход</th>
        <th>Расход</th>
        <th>Баланс на конец месяца</th>
        <th>Менеджер</th>

    </tr>
        <?php
        /*
         * [0] => Array
        (
            [operatorid] => 1689
            [cost] => 96.27
            [invoicetovivaldi] => -48.54
            [tovivaldi] => 0
            [topartner] => 0
            [maxdateto] => 2015-11-15
            [operatorname] => Dial Telecommunications LTD
            [endbalans] => 0
            [manager] => Malysheva D.
        )

         */
        if(isset($finaldata)):
        foreach($finaldata as $key=>$value):
            if($value['currency'] == 'RUB' && $currency['USD'] > 0){
                $value['cost']=$value['cost']/$currency['USD'];
                $value['invoicetovivaldi']=$value['invoicetovivaldi']/$currency['USD'];
                $value['endbalans']=$value['endbalans']/$currency['USD'];
            }
            if($value['currency'] == 'EUR' && $currency['USD'] > 0){
                $value['cost']=$value['cost']*$currency['EUR']/$currency['USD'];
                $value['invoicetovivaldi']=$value['invoicetovivaldi']*$currency['EUR']/$currency['USD'];
                $value['endbalans']=$value['endbalans']*$currency['EUR']/$currency['USD'];
            }
        ?>
        <tr>
        <td><?=$value['period']?></td>
        <td><?=$value['operatorname']?></td>
        <td><?=number_format($value['cost'], 2, '.', '')?></td>
        <td><?=number_format($value['invoicetovivaldi'], 2, '.', '')?></td>
        <td><?=number_format($value['endbalans'], 2, '.', '')?></td>
        <td><?=$value['manager']?></td>

        </tr>
    <?php
    $cost+=$value['cost'];
    $invoicetovivaldi+=$value['invoicetovivaldi'];
    endforeach;

        ?>
    <tr>
        <td>Итого</td>
        <td></td>
        <td><?=number_format($cost, 2, '.', '')?></td>
        <td><?=number_format($invoicetovivaldi, 2, '.', '')?></td>
    </tr>
    <tr>
        <td>Прибыль</td>
        <td></td>
        <td><?=number_format($cost+$invoicetovivaldi, 2, '.', '')?></td>
        <td></td>
    </tr>
    <?php endif ;?>

    </table>
