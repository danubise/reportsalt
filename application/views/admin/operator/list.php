<?php
/**
 * Created by PhpStorm.
 * User: slava
 * Date: 14.10.15
 * Time: 10:54

printarray($data);
die;
foreach($data['logins']['item'] as $value){
printarray($value);
}
 */

?>
<a href="<?=baseurl('operator/create/')?>" class="btn btn-success">Добавить</a>
<a href="<?=baseurl('operator/export/')?>" class="btn btn-success" target="_blank">Экспорт</a>
<table class="table table-striped" id="tableNum">
    <tr><h4>Список операторов</h4></tr>
    <thead>
    <tr>
        <th>id</th>
        <th>name</th>
        <th>address</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if(is_array($operators)):
        foreach($operators as $key=>$report):
            ?>
            <tr >
                <td><?=$report['id']?>&nbsp;</td>
                <td><?=$report['name']?>&nbsp;</td>
                <td><?=$report['address']?>&nbsp;</td>
                <td><a href="<?= baseurl('operator/modify/' .$report['id']) ?>">Изменить</a>/
                    <a href="<?= baseurl('operator/delete/' . $report['id']) ?>">Удалить</a>
                   <!-- <a href="<?= baseurl('report/select/' .$report['id']) ?>">Отчет</a></td> -->
            </tr>
        <?php
        endforeach;
    endif;
    ?>
    </tbody>
</table>
