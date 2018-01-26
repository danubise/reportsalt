
<?php
/**
 * Created by PhpStorm.
 * User: slava
 * Date: 08.11.15
 * Time: 15:57
 * [id] => 58
[invoiceid] => 140
[date] => 2015-10-01
[operatorid] => 1450
[operatorname] => Novik - Omega
[bperiodtext] => 01-30 Sep 2015
[duedatetext] => 09 Oct 2015
[balans] => 0
[realdatefrom] => 2015-09-01
[realdateto] => 2015-09-30
[cost] => 329.93297
[time] => 56722
 */

?>
<style>
    table {
        width: 100%; /* Ширина таблицы */
        border: 1px solid green; /* Рамка вокруг таблицы */
        margin: auto; /* Выравниваем таблицу по центру окна  */
    }
    td {
        text-align: center; /* Выравниваем текст по центру ячейки */
    }
</style>
<table border="1">
    <thead>
    <tr><table border="1"><tr>
        <form method="post"  action="<?=baseurl('report/invoiceedit')?>">
            <td>Номер части</td><td>Номер счета</td><td>Начало</td><td>Конец</td><td>Номер оператора</td><td>Название оператора</td><td>Сумма секунд</td><td>Сумма минуты</td><td>Сумма оплаты</td>
            </tr>
            <?php
            //printarray($maindata);
            foreach($maindata as $key=>$value) {
                echo "<tr><td>".$value['part']."</td><input type=\"hidden\" name=\"maindata[".$key."][id]\" value=\"" . $value['id'] . "\" >";
                //echo "<td></td>";
                echo "<td><input type=\"hidden\" name=\"maindata[".$key."][invoiceid]\" value=\"" . $value['invoiceid'] . "\" >" . $value['invoiceid'] . "</td>";

                echo "<td>" . $value['datefrom'] . "</td>";
                echo "<td>" . $value['dateto'] . "</td>";

                echo "<td><input type=\"hidden\" name=\"maindata[".$key."][operatorid]\" value=\"" . $value['operatorid'] . "\" >" . $value['operatorid'] . "</td>";
                echo "<td><input type=\"hidden\" name=\"maindata[".$key."][operatorname]\" value=\"" . $value['operatorname'] . "\" >" . $value['operatorname'] . "</td>";
                echo "<td><input type=\"text\" name=\"maindata[".$key."][time]\" value=\"" . $value['time'] . "\" ></td>";
                echo "<td><input type=\"text\" name=\"maindata[".$key."][timeminut]\" value=\"" . $value['timeminut'] . "\" ></td>";
                echo "<td><input type=\"text\" name=\"maindata[".$key."][cost]\" value=\"" . $value['cost'] . "\" ></td></tr>";
            }
            ?>

        </table><br>
        <table>
        <?php

        foreach($maindata as $key => $value){
            echo "<tr>Коментарий к счету ".$value['part']."</tr>";
            echo "<tr><textarea type=\"text\" name=\"maindata[".$key."][comment]\" rows=\"2\" style=\"width: 100%\">".$maindata[$key]['comment']."</textarea></tr>";
        }
            ?>
        </table>
        <br>
        <br>
            <table border="1">
                <tr>
                    <td></td>
                    <td>Номер части</td>
                    <td>Направление</td>
                    <td>Название</td>
                    <td>Секунды</td>
                    <td>Цена</td>
                </tr>
                <?php
                 if($detaildata)
                    foreach($detaildata as $key=>$value): ?>
                <tr>
                    <td><?php
                        if($value['handmade']=="1"){
                            echo "<input id=\"checkBox\" type=\"checkbox\" name=\"delete[".$value['id']."]\">";
                        }
                    ?>
                    </td>
                    <td><?=$value['part']?></td>
                    <td><input type="hidden" name="detaildata[<?=$value['id']?>][dest_code]" value="<?=$value['dest_code']?>"> <?=$value['dest_code']?></td>
                    <td><input type="text" name="detaildata[<?=$value['id']?>][dest]" value="<?=$value['dest']?>"></td>
                    <td><input type="text" name="detaildata[<?=$value['id']?>][time]" value="<?=$value['time']?>"></td>
                    <td><input type="text" name="detaildata[<?=$value['id']?>][cost]" value="<?=$value['cost']?>">&nbsp;</td>

                </tr>
                <?php endforeach; ?>

                <tr>
                    <td></td>
                    <td><?=$value['part']?></td>
                    <input type="hidden" name="newdata[part]" value="<?=$value['part']?>">
                    <td><input type="text" name="newdata[dest_code]" value=""></td>
                    <td><input type="text" name="newdata[dest]" value=""></td>
                    <td><input type="text" name="newdata[time]" value=""></td>
                    <td>
                        <input type="text" name="newdata[cost]" value="">&nbsp;
                        <input type="submit" name="addnewitem" value="Добавить">&nbsp;
                    </td>
                </tr>

            </table>

            <th><br>
                <input type="submit" name="deleteitems" value="Удалить">&nbsp;<input type="submit" name="save" value="Сохранить">&nbsp;<input type="submit" name="recalculate" value="Пересчитать"></th>
        </form>
        </tr>
    </thead>
    </table>



