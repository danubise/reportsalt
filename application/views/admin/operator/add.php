<form method="post"  enctype="multipart/form-data" action="<?=baseurl('operator/add')?>">
    <table class="table  table-striped" style="width: 500px">
        <tr>
            <th>Добавление оператора:&nbsp;<?php
if(isset($error)){echo "<font color=\"red\">Оператор уже существует</font>";}
                ?></th>

        </tr>
        <tr>
            <td>id</td>
            <td><input name="operator[id]" class="form-control" value="<?=$operator['id']?>"></td>
        </tr>
        <tr>
            <td>Название</td>
            <td><input name="operator[name]" class="form-control" value="<?=$operator['name']?>"></td>
        </tr>
        <tr>
            <td>Адресс</td>
            <td><input name="operator[address]" class="form-control" value="<?=$operator['address']?>"></td>
        </tr>
        <tr>
            <td>Биллинг период</td>
            <td><select name="operator[bperiod]">
                    <option value="week" selected>Недельный</option>
                    <option value="halfmonth">Полумесячный</option>
                    <option value="month">Месячный</option>

                </select>
                <!---<input name="operator[bperiod]" class="form-control" value="<?=$operator['bperiod']?>"></td>
                -->
        </tr>
        <tr>
            <td>оплата</td>
            <td><input name="operator[payment]" class="form-control" value="<?=$operator['payment']?>"></td>
        </tr>
        <tr>
            <td>Менеджер</td>
            <td><input name="operator[manager]" class="form-control" value="<?=$operator['manager']?>"></td>
        </tr>
        <tr>
            <td>Электронный адрес</td>
            <td><input name="operator[mail]" class="form-control" value="<?=$operator['mail']?>"></td>
        </tr>
        <tr>
            <td>Дата сл.бил</td>
            <td><input type="date" name="operator[billoperate]" class="form-control" value="<?=$operator['billoperate']?>"></td>
        </tr>
        <tr>
            <td>Баланс</td>
            <td><input type="text" name="operator[balans]" class="form-control" value="<?=$operator['balans']?>"></td>
        </tr>
        <tr>
            <td>Дата контракта</td>
            <td><input type="text" name="operator[contractDate]" class="form-control" value="<?=$operator['contractDate']?>"></td>
        </tr>
        <tr>
            <td>Название банка и адрес</td>
            <td><input type="text" name="operator[bankDetails]" class="form-control" value="<?=$operator['bankDetails']?>"></td>
        </tr>
        <tr>
            <td>SWIFT</td>
            <td><input type="text" name="operator[swift]" class="form-control" value="<?=$operator['swift']?>"></td>
        </tr>
        <tr>
            <td>IBAN</td>
            <td><input type="text" name="operator[iban]" class="form-control" value="<?=$operator['iban']?>"></td>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <td><button class="btn btn-primary">Добавить</button></td>
        </tr>
    </table>
</form>