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
        <th>Name</th>
        <th>Address</th>
        <th>Company</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if(is_array($operators)):
        foreach($operators as $key=>$operator):
            ?>
            <tr >
                <td><?=$operator['id']?>&nbsp;</td>
                <td><?=$operator['name']?>&nbsp;</td>
                <td><?=$operator['address']?>&nbsp;</td>
                <td><?=$operator['company']?>&nbsp;</td>
                <td><a href="<?= baseurl('operator/modify/' .$operator['id']) ?>">Изменить</a>/
                    <a href="<?= baseurl('operator/delete/' . $operator['id']) ?>">Удалить</a>/
                    <?php if($operator['disable']== "0"){
                         echo "<a href=\"" .baseurl('operator/disable/' .$operator['id'])."\">Отключить</a></td>";
                    }else{
                        echo "<a href=\"" .baseurl('operator/enable/' .$operator['id'])."\">Включить</a></td>";
                    }?>
            </tr>
        <?php
        endforeach;
    endif;
    ?>
    </tbody>
</table>
