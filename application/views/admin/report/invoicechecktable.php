<?php
/**
 * Created by PhpStorm.
 * User: Slava
 * Date: 31.10.2015
 * Time: 23:38
 * таблица всерки
 */

?>
<table>
    <thead>
    <tr>
        <form method="post"  action="<?=baseurl('report/invoicechecktable')?>">
            <th><select name="reconciliation[type]">
                    <?php
                    $type['week']="";
                    $type['halfmonth']="";
                    $type['month']="";

                    if(isset($reconciliation['type'])){

                                $type[$reconciliation['type']]="selected";
       }
                    ?>
                    <option value="week" <?=$type['week']?>>Недельный</option>
                    <option value="halfmonth" <?=$type['halfmonth']?>>Полумесячный</option>
                    <option value="month" <?=$type['month']?>>Месячный</option>
                </select></th>
            <th><input type="date" name="reconciliation[datefrom]" value="<?=$reconciliation['datefrom']?>" ></th>
            <th><input type="date" name="reconciliation[dateto]" value="<?=$reconciliation['dateto']?>" ></th>
            <th><select name="reconciliation[companyid]">

                    <?php
                    /*
                    if(trim($reconciliation['companyid'])==""){
                        echo "<option value=\"all\" selected>Все</option>";
                    }else{
                        echo "<option value=\"all\">Все</option>";
                    }*/


                    foreach($companylist as $key=>$value) {
                        $select="";
                        if ($reconciliation['companyid'] == $value['operatorid']) {
                            $select = "selected";
                            $reconciliation['companyname'] = $value['operatorname'] . "(" . $value['operatorid'] . ")" . "(" . $value['bperiod'] . ")";
                        }
                        echo "<option value=\"" . $value['operatorid'] . "\" " . $select . ">" . $value['operatorname'] . "(" . $value['operatorid'] . ")(" . $value['bperiod'] . ")</option>";
                    }
                    ?>
                </select></th>
            <th><input type="submit" name="show" value="Показать"></th>
        </form>
    </tr>
    </thead>
</table>
<form method="post"  action="<?=baseurl('report/invoicechecktable')?>">
    <input type="hidden" name="reconciliation[datefrom]" value="<?=$reconciliation['datefrom']?>" >
    <input type="hidden" name="reconciliation[dateto]" value="<?=$reconciliation['dateto']?>" >
    <input type="hidden"  name="reconciliation[companyid]" value="<?=$reconciliation['companyid']?>">
    <input type="hidden" name="reconciliation[type]" value="<?=$reconciliation['type']?>">
<table class="table table-striped" id="tableNum">
    <tr><h4>Сверка по <?php echo $reconciliation['companyname'] ;?></h4> <?php
        $url=$reconciliation['type']."/".$reconciliation['datefrom']."/".$reconciliation['dateto']."/".$reconciliation['companyid'];
        echo "<a href=".baseurl('report/checktabledownload/'.$url)." target=\"_blank\">Скачать</a>";
        ?></tr>
    <thead>
    <tr>
        <th>Invoice period</th>
        <th>begin balance</th>
        <th>Invoice to Vivaldi</th>
        <th>Invoice to Partner</th>
        <th>Payment to Vivald</th>
        <th>Payment to Partner</th>
        <th>End balance</th>
        <th>Comment</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if(is_array($data)):
        //printarray($data);
        foreach($data as $key=>$line):
            ?>
            <tr >
                <td><?=$line['date']?>&nbsp;</td>
                <td><?=round($line['begin balance'],2)?>&nbsp;</td>
                <td><input type="text" name="balans[<?=$line['id']?>][invoicetovivaldi]" value="<?=round($line['invoicetovivaldi'],2)?>">&nbsp;</td>
                <td><input type="text" name="balans[<?=$line['id']?>][invoicetopartner]" value="<?=round($line['invoicetopartner'],2)?>">&nbsp;</td>
                <td><input type="text" name="balans[<?=$line['id']?>][tovivaldi]" value="<?=round($line['tovivaldi'],2)?>">&nbsp;</td>
                <td><input type="text" name="balans[<?=$line['id']?>][topartner]" value="<?=round($line['topartner'],2)?>">&nbsp;</td>
                <td><?=round($line['End balance'],2)?>&nbsp;</td>

                <td><input type="text" name="balans[<?=$line['id']?>][invoicecomment]" value="<?=$line['invoicecomment']?>">&nbsp;</td>

            </tr>
        <?php
        endforeach;

    /*
     *  $total['Invoice to Vivaldi']+=$value['invoicetovivaldi'];
        $total['Invoices to Partner']+=round($value['cost'],2);
        $total['tovivaldi']+=$value['tovivaldi'];
        $total['topartner']+=$value['topartner'];
        $total['End balance']+= $data[$key]['End balance'];
*/
    ?>
    <tr>
        <td>TOTAL</td>
        <td>&nbsp;</td>
        <td><?=round($total['invoicetovivaldi'],2)?></td>
        <td><?=round($total['invoicetopartner'],2)?></td>
        <td><?=round($total['tovivaldi'],2)?></td>
        <td><?=round($total['topartner'],2)?></td>
        <td>&nbsp;</td>
        <td></td>

    </tr><?php endif; ?>
    </tbody>
</table>
    <input type="submit" name="save" value="Сохранить">
</form>

