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
        <form method="post"  enctype="multipart/form-data" action="<?=baseurl('report/downloadreport')?>">
        <th>id клиента&nbsp;</th>
        <td><input name="clientid" class="form-control" value="<?=$clientid?>"></td>
        <th>Начало</th>
        <td><input type="date" name="datefrom" value="<?=$datefrom?>" ></td>
        <th>Конец</th>
        <td><input type="date" name="dateto" value="<?=$dateto?>"></td>

    </tr>

    <tr>
        <th>&nbsp;</th>
        <td><button class="btn btn-primary">Скачать</button></td>
        </form>
        <td>
            <form method="post"  enctype="multipart/form-data" action="<?=baseurl('report/showhtmlpdf/')?>">
                <input type="hidden" name="clientid" class="form-control" value="<?=$clientid?>">

                <input type="hidden" name="datefrom" value="<?=$datefrom?>" >

                <input type="hidden" name="dateto" value="<?=$dateto?>">
                <button class="btn btn-primary">Показать</button>
            </form>
        </td>
        <td>

            <form method="post"  enctype="multipart/form-data" action="<?=baseurl('report/sendmail/')?>">
                <input type="hidden" name="clientid" class="form-control" value="<?=$clientid?>">
                <input type="hidden" name="datefrom" value="<?=$datefrom?>" >
                <input type="hidden" name="dateto" value="<?=$dateto?>">
                <button class="btn btn-primary">Отослать</button>
            </form>
        </td>


    </tr>
</table>


<table class="table table-striped" id="tableNum">
    <tr><h4><?=$clientid?>&nbsp; <?=$contractTitle?>&nbsp; <?=$datefrom?> to <?=$dateto?> (<?=$aliase?>)</h4></tr>
    <thead>

    <tr>
        <th>dest</th>
        <th>dest_code</th>
        <th>count</th>
        <th>cost</th>
        <th>acd</th>
        <th>asr</th>
        <th>round_time</th>
        <th>time</th>
    </tr>
    </thead>
    <tbody>
    <?php
    /*
                    'contractTitle'=>$array['table']['@attributes']['contractTitle'],
                    'datefrom'=>$datefrom,
                    'dateto'=>$dateto
*/
   // printarray($data);
        if(is_array($data)):
        foreach($data as $key=>$report):
/*
    [@attributes] => Array
        (
            [acd] => -1;00:00:35 [35]
            [asr] => -1;100%
            [cost] => 0.00607
            [count] => 1
            [dest] => Estonia
            [dest_code] => 993
            [round_time] => 00:00:35 [35]
            [time] => 00:00:35 [35]
        )
*/
    ?>
    <tr >
        <td><?=$report['dest']?>&nbsp;</td>
        <td><?=$report['dest_code']?>&nbsp;</td>
        <td><?=$report['count']?>&nbsp;</td>
        <td><?=$report['cost']?>&nbsp;</td>
        <td><?=$report['acdtime']?>&nbsp;<?=$report['acd']?>&nbsp;</td>
        <td><?=$report['asr']?>&nbsp;</td>
        <td><?=$report['round_timetime']?>&nbsp;<?=$report['round_time']?>&nbsp;</td>
        <td><?=$report['timetime']?>&nbsp;<?=$report['time']?>&nbsp;</td>

    </tr>
    <?php
        endforeach;
        endif;
    ?>
    <tr >
        <td>Total&nbsp;</td>
        <td><?=$summ['dest_code']?>&nbsp;</td>
        <td><?=$summ['count']?>&nbsp;</td>
        <td><?=$summ['cost']?>&nbsp;</td>
        <td><?=$summ['acd']?>&nbsp;</td>
        <td><?=$summ['asr']?>&nbsp;</td>
        <td><?=$summ['round_timetime']?>&nbsp;<?=$summ['round_time']['sec']?>&nbsp;</td>
        <td><?=$summ['timetime']?>&nbsp;<?=$summ['time']['sec']?>&nbsp;</td>


    </tr>
    </tbody>
</table>
<?php //printarray($data)?>