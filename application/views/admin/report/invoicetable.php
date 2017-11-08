<?php
/**
 * Created by PhpStorm.
 * User: Slava
 * Date: 21.10.2015
 * Time: 23:45
 */
?>
<table>
<thead>
<tr>
    <form method="post"  action="<?=baseurl('report/invoiceresulttable')?>">
        <th><input type="date" name="invoicetable[datefrom]" value="<?=$invoicetable['datefrom']?>" ></th>
        <th><input type="date" name="invoicetable[dateto]" value="<?=$invoicetable['dateto']?>" ></th>
        <th><select name="invoicetable[operatorid]">

                <?php
                if(trim($invoicetable['operatorid'])==""){
                    echo "<option value=\"all\" selected>Все</option>";
                }else{
                    echo "<option value=\"all\">Все</option>";
                }
                foreach($operators as $key=>$value) {
                    $select="";
                    if ($invoicetable['operatorid'] == $value['id']) {
                        $select = "selected";
                        $invoicetable['operatorname'] = $value['name'] . "(" . $value['id'] . ")";
                    }
                    echo "<option value=\"" . $value['id'] . "\" " . $select . ">" . $value['name'] . "(" . $value['id'] . ")</option>";
                }
                ?>
            </select></th>
        <th><select name="invoicetable[manager]">

                <?php
                if(trim($invoicetable['manager'])==""){
                    echo "<option value=\"all\" selected>Все</option>";
                }else{
                    echo "<option value=\"all\">Все</option>";
                }
                foreach($managers as $key=>$value) {
                    $select="";
                    if($invoicetable['manager']==$value){
                        $select="selected";
                    }
                    echo " <option value =\"".$value."\" ".$select.">$value</option >";
                }
                ?>
            </select></th>
        <th><input type="submit" name="show" value="Показать"></th>
    </form>
</tr>
</thead>
</table>

<table class="table table-striped" id="tableNum">
    <tr><h4>Список операторов</h4></tr>
    <thead>
    <tr>
        <th>Номер</th>
        <th>Цена</th>
        <th>От</th>
        <th>До</th>
        <th>Контора</th>
        <th>Менеджен</th>
        <th>Отправлен</th>
        <th>Подтвержден</th>
    </tr>
    </thead>
    <tbody>
    <?php
    /*
     *    [id] => 59
            [invoiceid] => 141
            [date] => 2015-10-01
            [operatorid] => 1450
            [operatorname] => Novik - Omega
            [bperiodtext] => 01-30 Sep 2015
            [duedatetext] => 09 Oct 2015
            [balans] => 0
            [realdatefrom] => 2015-09-01
            [realdateto] => 2015-09-30
            [cost] => 659.86594
            [time] => 113444
     */
    if(is_array($data)):
        foreach($data as $key=>$line):
            ?>
            <tr >
                <td><?=$line['invoiceid']?>&nbsp;</td>
                <td><?=round($line['cost'],2)?>&nbsp;</td>
                <td><?=$line['realdatefrom']?>&nbsp;</td>
                <td><?=$line['realdateto']?>&nbsp;</td>
                <td><?php echo $line['operatorname']."(".$line['operatorid'].")";?>&nbsp;</td>
                <td><?=$line['manager']?>&nbsp;</td>
                <td><?php
                    if($line['send']=="1" ) {
                        echo "Да";
                    }
                    elseif($line['send']=="0") {
                        echo "Нет";
                    }
                    else{
                        echo "Ошибка";
                    }?>&nbsp;</td>
                <td><?php if($line['confirm'])echo "Да";
                    else{echo "Нет";}?>&nbsp;</td>
                <td><?php echo "<a href=".baseurl('report/invoiceedit/' .$line['invoiceid'])." target=\"_blank\">Изменить</a>/";
                    ?>
                    <a href="<?= baseurl('report/invoicedownload/' . $line['invoiceid']) ?>" target="_blank">Скачать</a>/
                    <a href="<?= baseurl('report/invoicedelete/' . $line['invoiceid']."/".$invoicetable['datefrom']."/".$invoicetable['dateto']."/".$invoicetable['operatorid']."/".$invoicetable['manager']) ?>">Удалить</a>
                </td>

            </tr>
        <?php
        endforeach;
    endif;
    ?>
</tbody>
</table>
