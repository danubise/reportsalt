<a href="<?=baseurl('settings/index/useraddview/')?>" class="btn btn-success">Создать пользователя</a>
<br><br>
<form method="post">
    <table class="table table-bordered table-striped">
        <tr><td>Пользователи</td><td>Операции</td></tr>
        <?php
        if(is_array($users)) {
            foreach ($users as $key => $value):
                ?>
                <tr>
                    <td><?= $value['login'] ?></td>
                    <td><a href="<?= $href ?>"><?= $avalue ?></a>/
                        <a href="<?= baseurl('settings/index/useredit/' . $value['id']) ?>">Изменить</a>/
                        <a href="<?= baseurl('settings/index/userdelete/' . $value['id']) ?>">Удалить</a></td>
                </tr>
            <?php endforeach;
        }?>
    </table>
</form>