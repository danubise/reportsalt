<form method="post"  enctype="multipart/form-data" action="<?=baseurl('numberpool/index/save')?>">
    <table class="table  table-striped" style="width: 500px">
        <tr>
            <th>Название:&nbsp;</th>
            <td><input name="name" class="form-control"></td>
        </tr>
        <tr>
        <td colspan=2>
        <b>Номера:</b><br>
        <textarea style='height: 320px;' name='numbers' class='form-control'></textarea>
        </td>
        </tr>
        <!--<tr>
            <th>Файл:&nbsp;</th>
            <td><input name="file" type="file" ></td>
        </tr>!-->
        <tr>
            <th>&nbsp;</th>
            <td><button class="btn btn-primary">Добавить</button></td>
        </tr>
     </table>
</form>