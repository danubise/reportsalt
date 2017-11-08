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
<table class="table table-striped" id="tableNum">
    <tr><h4>Список операторов</h4></tr>
    <thead>
    <tr>
        <th>Operatorid</th>
        <th>Name</th>
        <th>Total Report</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if(is_array($operators)):
        foreach($operators as $key=>$operator):
            ?>
            <tr >
                <td><?=$operator['clientid']?>&nbsp;</td>
                <td><?php
                    if($operator['confirm']){
                        echo "Conirm";
                    }
                    else{
                        echo "Not conirm";
                    }?>&nbsp;</td>
                <td><a href="<?=baseurl('report/downloadreport/'.$operator['id'])?>" target="_blank"><?=$operator['totalreport']?></a>&nbsp;</td>

            </tr>
            <?php
        endforeach;
    endif;
    ?>
    </tbody>
</table>
