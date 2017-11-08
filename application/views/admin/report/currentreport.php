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
 *
 * 'var' => array(
'data'=>$reportdata['data'],
'clientid'=>$_POST['clientid'],
'summ'=>$reportdata['total'],
'contractTitle'=>$reportdata['data']['table']['@attributes']['contractTitle'],
'datefrom'=>$reportdata['datefrom'],
'dateto'=>$reportdata['dateto'],
'aliase'=>$reportdata['aliase']
)
 */

?>

    <table class="table  table-striped" style="width: 500px">
        <tr>
            <form method="post"  enctype="multipart/form-data" action="<?=baseurl('currentreport/downloadreport')?>">

            <th>&nbsp;</th>
            <td><button class="btn btn-primary">Скачать</button></td>
            </form>
        </tr>
    </table>

<?php //printarray($data)?>