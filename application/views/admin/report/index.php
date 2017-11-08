<form method="post"  enctype="multipart/form-data" action="<?=baseurl('report/showreport')?>">
    <table class="table  table-striped" style="width: 500px">
        <tr>
            <th>id клиента&nbsp;</th>
            <td><input name="clientid" class="form-control" value="<?=$clientid?>"></td>
            <th>Начало</th>
            <td><input type="date" name="datefrom" value="01.09.2015" ></td>
            <th>Конец</th>
            <td><input type="date" name="dateto" value="30.09.2015"></td>
        </tr>

        <tr>
            <th>&nbsp;</th>
            <td><button class="btn btn-primary">Показать</button></td>
        </tr>
    </table>
</form>
<?php
if(isset($error)){
    printarray($error);
}

?>