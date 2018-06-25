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
<table  class="table table-striped" id="tableNum">
    <thead>
    <tr>
        <form method="post"  action="<?=baseurl('report/finalreport')?>">
            <th><input type="date" name="filtr[datefrom]" value="<?=$filtr['datefrom']?>" ></th>
            <th><input type="date" name="filtr[dateto]" value="<?=$filtr['dateto']?>" ></th>

            <th><input type="submit" name="show" value="Показать"></th>
            <th><input type="submit" name="download" value="Скачать"></th>
            </form>
    <tr>
        <td>Период</td>
        <td>Название оператора</td>
        <td>Приход</td>
        <td>Расход</td>
        <td>Баланс на конец месяца</td>
        <td>Менеджер</td>

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
            if($row['currency'] == 'RUB'){
                $value['cost']=$value['cost']/$currency['USD'];
                $value['invoicetovivaldi']=$value['invoicetovivaldi']/$currency['USD'];
                $value['endbalans']=$value['endbalans']/$currency['USD'];
            }
            if($row['currency'] == 'EUR'){
                $value['cost']=$value['cost']*$currency['EUR']/$currency['USD'];
                $value['invoicetovivaldi']=$value['invoicetovivaldi']*$currency['EUR']/$currency['USD'];
                $value['endbalans']=$value['endbalans']*$currency['EUR']/$currency['USD'];
            }
        ?>
        <tr>
        <td><?=$value['period']?></td>
        <td><?=$value['operatorname']?></td>
        <td><?=$value['cost']?></td>
        <td><?=$value['invoicetovivaldi']?></td>
        <td><?=$value['endbalans']?></td>
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
        <td><?=$cost?></td>
        <td><?=$invoicetovivaldi?></td>
    </tr>
    <tr>
        <td>Прибыль</td>
        <td></td>
        <td><?=$cost+$invoicetovivaldi?></td>
        <td></td>
    </tr>
    <?php endif ;?>
    </thead>
    </table>
