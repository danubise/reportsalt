<?php
/**
 * Created by PhpStorm.
 * User: Slava
 * Date: 14.10.2015
 * Time: 23:45
 */

class Operator extends Core_controller {
    public function __construct() {
        parent::__construct();
        $this->module_name = 'Список операторов';
    }

    public function index() {
        $operators=$this->db->select("* from `b_operators` ORDER BY `id` ASC");
        $this->view(
            array(
                'view' => 'operator/list',
                'var' => array(
                    'operators'=>$operators,
                )
            )
        );
    }
    public function edit($id){
        $operator = $this->db->select("* from `b_operators` where `id`='".$id."'");
        $this->view(
            array(
                'view' => 'operator/edit',
                'var' => array(
                    'operators'=>$operator,
                )
            )
        );
    }
    public function disable($operatorid){
        $this->db->update('b_operators',array('disable' => "1"),"`id`='".$operatorid."'");
        $this->index();
    }
    public function enable($operatorid){
        $this->db->update('b_operators',array('disable' => "0"),"`id`='".$operatorid."'");
        $this->index();
    }

    public function create(){
        $this->view(
            array(
                'view' => 'operator/add',
                'var' => array(
                )
            )
        );
    }

    public function export(){
      $operators = $this->db->select("* FROM  `b_operators` ORDER BY  `b_operators`.`id` ASC");
       //echo $this->db->log();
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=operators.csv");
// Disable caching
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
        header("Pragma: no-cache"); // HTTP 1.0
        header("Expires: 0"); // Proxies

        $output = fopen("php://output", "w");
       /* $line[]="Период";
        $line[]="Наименование компании";
        $line[]="Приход";
        $line[]="Расход";
        $line[]="Балланс на конец месяца ";
        $line[]="Менеджер";
       */
        $line[]="Id";
        $line[]="Operator name";
        $line[]="Address";
        $line[]="Company";
        $line[]="BillPeriod";
        $line[]="Currency";
        $line[]="Days";
        $line[]="Manager";
        $line[]="Email";
        $line[]="Date";
        $line[]="Balans";
        $line[]="contractDate";
        $line[]="bankDetails";
        $line[]="SWIFT";
        $line[]="IBAN";
        $line[]="Disable";
        fputcsv($output, $line,";");

        foreach ($operators as $row) {

            /*
             *         <td><?=$value['period']?></td>
    <td><?=$value['operatorname']?></td>
    <td><?=$value['cost']?></td>
    <td><?=$value['invoicetovivaldi']?></td>
    <td><?=$value['endbalans']?></td>
    <td><?=$value['manager']?></td>
             */
            fputcsv($output, $row,";"); // here you can change delimiter/enclosure

        }

        fclose($output);
        die;
    }
    public function modify ($id){
        if($id=="save"){
            //printarray($_POST);
            $operatoredit=$_POST['operator'];
            //die;
            foreach($_POST['operator'] as $key=>$value){
                $b[trim($key)]=trim($value);
            }
            $operator=array();
            foreach($_POST['operator'] as $key=>$value){
                $operatoredit[$key]=mysql_escape_string(trim($value));
            }

            $this->db->update('b_operators',$operatoredit,"`id`='".$operatoredit['id']."'");
            //echo $this->db->query->last;
            //die;
            $this->index();
            die;
        }
        $check=$this->db->select("* from `b_operators` where `id`='".$id."'",0);

            $this->view(
                array(
                    'view' => 'operator/edit',
                    'var' => array(
                        'operator'=>$check,
                        'edit'=>true,
                    )
                )
            );

    }
    public function add (){
        //printarray($_POST);
        $check=$this->db->select("* from `b_operators` where `id`='".$_POST['operator']['id']."'");
        if(!is_array($check)) {
            $operator=array();
            foreach($_POST['operator'] as $key=>$value){
                $operator[$key]=mysql_escape_string($value);
            }
            $this->db->insert("b_operators", $operator);
           // echo $this->db->query->last;
            $this->index();
        }else{
            $this->view(
                array(
                    'view' => 'operator/add',
                    'var' => array(
                        'operator'=>$_POST['operator'],
                        'error'=>true,
                    )
                )
            );
        }
    }
    public function delete ($id){
        $check=$this->db->delete(" from `b_operators` where `id`='".$id."'");

           $this->index();
    }


    public function logout() {
        $this->user_model->logout();
        header('Location: '.baseurl());
    }
}