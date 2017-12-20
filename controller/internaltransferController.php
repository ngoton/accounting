<?php
Class internaltransferController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->internaltransfer) || json_decode($_SESSION['user_permission_action'])->internaltransfer != "internaltransfer") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Chuyển tiền nội bộ';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'internal_transfer_document_date';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
        }
        
        $internal_transfer_model = $this->model->get('internaltransferModel');

        $bank_model = $this->model->get('bankModel');
        $banks = $bank_model->getAllBank();
        $this->view->data['banks'] = $banks;


        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => '1=1',
        );
        
        
        $tongsodong = count($internal_transfer_model->getAllBank($data));
        $tongsotrang = ceil($tongsodong / $sonews);
        

        $this->view->data['page'] = $page;
        $this->view->data['order_by'] = $order_by;
        $this->view->data['order'] = $order;
        $this->view->data['keyword'] = $keyword;
        $this->view->data['pagination_stages'] = $pagination_stages;
        $this->view->data['tongsotrang'] = $tongsotrang;
        $this->view->data['limit'] = $limit;
        $this->view->data['sonews'] = $sonews;

        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            'where' => '1=1',
            );
        
      
        if ($keyword != '') {
            $search = '( internal_transfer_document_number LIKE "%'.$keyword.'%"  
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        

        
        $this->view->data['internal_transfers'] = $internal_transfer_model->getAllBank($data);
        $this->view->data['lastID'] = isset($internal_transfer_model->getLastBank()->internal_transfer_document_number)?$internal_transfer_model->getLastBank()->internal_transfer_document_number:0;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('internaltransfer/index');
    }

   
    public function getAccount(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $account_model = $this->model->get('accountModel');
            if ($_POST['keyword'] == "*") {
                $list = $account_model->getAllAccount();
            }
            else{
                $data = array(
                'where'=>'( account_number LIKE "%'.$_POST['keyword'].'%" )',
                );
                $list = $account_model->getAllAccount($data);
            }
            foreach ($list as $rs) {
                $account_name = $rs->account_number.' | '.$rs->account_name;
                if ($_POST['keyword'] != "*") {
                    $account_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->account_number.' | '.$rs->account_name);
                }
                echo '<li onclick="set_item_account(\''.$rs->account_id.'\',\''.$rs->account_number.'\',\''.$_POST['name'].'\',\''.$_POST['offset'].'\')">'.$account_name.'</li>';
            }
        }
    }
   
    public function add(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (isset($_POST['yes'])) {
            
            $internal_transfer_model = $this->model->get('internaltransferModel');
            $internal_transfer_item_model = $this->model->get('internaltransferitemModel');
            $additional_model = $this->model->get('additionalModel');
            $account_model = $this->model->get('accountModel');
            $bank_balance_model = $this->model->get('bankbalanceModel');

            $items = $_POST['item'];

            $data = array(
                        
                        'internal_transfer_document_date' => strtotime(str_replace('/','-',$_POST['internal_transfer_document_date'])),
                        'internal_transfer_document_number' => trim($_POST['internal_transfer_document_number']),
                        'internal_transfer_additional_date' => strtotime(str_replace('/','-',$_POST['internal_transfer_additional_date'])),
                        'internal_transfer_comment' => trim($_POST['internal_transfer_comment']),
                        );
            

            if ($_POST['yes'] != "") {
                $check = $internal_transfer_model->queryBank('SELECT * FROM Bank WHERE (internal_transfer_document_number='.$data['internal_transfer_document_number'].') AND internal_transfer_id!='.$_POST['yes']);
                if($check){
                    echo "Chứng từ này đã tồn tại";
                    return false;
                }
                else{
                    $internal_transfer_model->updateBank($data,array('internal_transfer_id' => trim($_POST['yes'])));
                    echo "Cập nhật thành công";

                    $id_internal_transfer = $_POST['yes'];

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|internal_transfer|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                
            }
            else{
                $check = $internal_transfer_model->queryBank('SELECT * FROM Bank WHERE (internal_transfer_document_number='.$data['internal_transfer_document_number'].')');
                if($check){
                    echo "Chứng từ này đã tồn tại";
                    return false;
                }
                else{
                    $data['internal_transfer_create_user'] = $_SESSION['userid_logined'];
                    $data['internal_transfer_create_date'] = strtotime(date('d-m-Y'));

                    $internal_transfer_model->createBank($data);
                    echo "Thêm thành công";

                $id_internal_transfer = $internal_transfer_model->getLastBank()->internal_transfer_id;

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                    $filename = "action_logs.txt";
                    $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$internal_transfer_model->getLastBank()->internal_transfer_id."|internal_transfer|".implode("-",$data)."\n"."\r\n";
                    
                    $fh = fopen($filename, "a") or die("Could not open log file.");
                    fwrite($fh, $text) or die("Could not write file!");
                    fclose($fh);
                }
                    
                
            }

            if (isset($id_internal_transfer)) {
                $internal_transfers = $internal_transfer_model->getBank($id_internal_transfer);

                $money = 0;

                $arr_item = "";
                foreach ($items as $v) {
                    if($v['internal_transfer_item_money'] != ""){
                        $debit = 0;
                        $credit = 0;
                        if (trim($v['internal_transfer_item_debit']) != "") {
                            $debits = $account_model->getAccountByWhere(array('account_number'=>trim($v['internal_transfer_item_debit'])));
                            if (!$debits) {
                                $account_model->createAccount(array('account_number'=>trim($v['internal_transfer_item_debit'])));
                                $debit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $debit = $debits->account_id;
                            }
                        }
                        if (trim($v['internal_transfer_item_credit']) != "") {
                            $credits = $account_model->getAccountByWhere(array('account_number'=>trim($v['internal_transfer_item_credit'])));
                            if (!$credits) {
                                $account_model->createAccount(array('account_number'=>trim($v['internal_transfer_item_credit'])));
                                $credit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $credit = $credits->account_id;
                            }
                        }
                        
                        

                        $data_item = array(
                            'internal_transfer' => $id_internal_transfer,
                            'internal_transfer_item_out' => $v['internal_transfer_item_out'],
                            'internal_transfer_item_in' => $v['internal_transfer_item_in'],
                            'internal_transfer_item_money' => str_replace(',', '', $v['internal_transfer_item_money']),
                            'internal_transfer_item_debit' => $debit,
                            'internal_transfer_item_credit' => $credit,
                        );

                        $money += $data_item['internal_transfer_item_money'];

                        $internal_transfer_items = $internal_transfer_item_model->getBank($v['internal_transfer_item_id']);
                        if ($internal_transfer_items) {
                            $internal_transfer_item_model->updateBank($data_item,array('internal_transfer_item_id'=>$internal_transfer_items->internal_transfer_item_id));
                            $id_internal_transfer_item = $internal_transfer_items->internal_transfer_item_id;

                            $data_additional = array(
                                'internal_transfer_item'=>$id_internal_transfer_item,
                                'document_number'=>$internal_transfers->internal_transfer_document_number,
                                'document_date'=>$internal_transfers->internal_transfer_document_date,
                                'additional_date'=>$internal_transfers->internal_transfer_additional_date,
                                'additional_comment'=>$internal_transfers->internal_transfer_comment,
                                'debit'=>$data_item['internal_transfer_item_debit'],
                                'credit'=>$data_item['internal_transfer_item_credit'],
                                'money'=>$data_item['internal_transfer_item_money'],
                            );
                            $additional_model->updateAdditional($data_additional,array('internal_transfer_item'=>$id_internal_transfer_item));

                            $data_bank_out = array(
                                'internal_transfer_item'=>$id_internal_transfer_item,
                                'bank_balance_date'=>$internal_transfers->internal_transfer_document_date,
                                'bank'=>$data_item['internal_transfer_item_out'],
                                'bank_balance_money'=>(0-$data_item['internal_transfer_item_money']),
                            );
                            $data_bank_in = array(
                                'internal_transfer_item'=>$id_internal_transfer_item,
                                'bank_balance_date'=>$internal_transfers->internal_transfer_document_date,
                                'bank'=>$data_item['internal_transfer_item_in'],
                                'bank_balance_money'=>$data_item['internal_transfer_item_money'],
                            );
                            
                            $bank_balance_model->updateBank($data_bank_out,array('internal_transfer_item'=>$id_internal_transfer_item,'bank'=>$internal_transfer_items->internal_transfer_item_out));
                            $bank_balance_model->updateBank($data_bank_in,array('internal_transfer_item'=>$id_internal_transfer_item,'bank'=>$internal_transfer_items->internal_transfer_item_in));

                        }
                        else{
                            $internal_transfer_item_model->createBank($data_item);
                            $id_internal_transfer_item = $internal_transfer_item_model->getLastBank()->internal_transfer_item_id;

                            $data_additional = array(
                                'internal_transfer_item'=>$id_internal_transfer_item,
                                'document_number'=>$internal_transfers->internal_transfer_document_number,
                                'document_date'=>$internal_transfers->internal_transfer_document_date,
                                'additional_date'=>$internal_transfers->internal_transfer_additional_date,
                                'additional_comment'=>$internal_transfers->internal_transfer_comment,
                                'debit'=>$data_item['internal_transfer_item_debit'],
                                'credit'=>$data_item['internal_transfer_item_credit'],
                                'money'=>$data_item['internal_transfer_item_money'],
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_bank_out = array(
                                'internal_transfer_item'=>$id_internal_transfer_item,
                                'bank_balance_date'=>$internal_transfers->internal_transfer_document_date,
                                'bank'=>$data_item['internal_transfer_item_out'],
                                'bank_balance_money'=>(0-$data_item['internal_transfer_item_money']),
                            );
                            $data_bank_in = array(
                                'internal_transfer_item'=>$id_internal_transfer_item,
                                'bank_balance_date'=>$internal_transfers->internal_transfer_document_date,
                                'bank'=>$data_item['internal_transfer_item_in'],
                                'bank_balance_money'=>$data_item['internal_transfer_item_money'],
                            );
                            
                            $bank_balance_model->createBank($data_bank_out);
                            $bank_balance_model->createBank($data_bank_in);

                        }

                        if ($arr_item=="") {
                            $arr_item .= $id_internal_transfer_item;
                        }
                        else{
                            $arr_item .= ','.$id_internal_transfer_item;
                        }
                    }
                }

                $item_olds = $internal_transfer_item_model->queryBank('SELECT * FROM internal_transfer_item WHERE internal_transfer='.$id_internal_transfer.' AND internal_transfer_item_id NOT IN ('.$arr_item.')');
                foreach ($item_olds as $item_old) {
                    $additional_model->queryAdditional('DELETE FROM additional WHERE internal_transfer_item='.$item_old->internal_transfer_item_id);
                    $bank_balance_model->queryBank('DELETE FROM bank_balance WHERE internal_transfer_item='.$item_old->internal_transfer_item_id);
                    $internal_transfer_item_model->queryBank('DELETE FROM internal_transfer_item WHERE internal_transfer_item_id='.$item_old->internal_transfer_item_id);
                }
                
                $data_pay = array(
                    'internal_transfer_money'=>$money,
                );
                $internal_transfer_model->updateBank($data_pay,array('internal_transfer_id'=>$id_internal_transfer));

                
            }
                    
        }
    }

    public function getitemadd(){
        if (isset($_POST['internal_transfer'])) {
            $account_model = $this->model->get('accountModel');
            $bank_model = $this->model->get('bankModel');
            $internal_transfer_item_model = $this->model->get('internaltransferitemModel');
            $internal_transfer_items = $internal_transfer_item_model->getAllBank(array('where'=>'internal_transfer='.$_POST['internal_transfer']));

            $banks = $bank_model->getAllBank();

            $str = "";
            $i = 1;
            foreach ($internal_transfer_items as $item) {
                $debit = $account_model->getAccount($item->internal_transfer_item_debit)->account_number;
                $credit = $account_model->getAccount($item->internal_transfer_item_credit)->account_number;
                
                $select_out = '<select name="internal_transfer_item_out[]" class="internal_transfer_item_out" required="required">';
                $select_out .= '<option '.($item->internal_transfer_item_out==0?'selected="selected"':null).' value="0">Tiền mặt</option>';
                foreach ($banks as $bank) {
                    $select_out .= '<option '.($item->internal_transfer_item_out==$bank->bank_id?'selected="selected"':null).' value="'.$bank->bank_id.'">'.$bank->bank_code.'</option>';
                }
                $select_out .= '</select>';

                $select_in = '<select name="internal_transfer_item_in[]" class="internal_transfer_item_in" required="required">';
                $select_in .= '<option '.($item->internal_transfer_item_in==0?'selected="selected"':null).' value="0">Tiền mặt</option>';
                foreach ($banks as $bank) {
                    $select_in .= '<option '.($item->internal_transfer_item_in==$bank->bank_id?'selected="selected"':null).' value="'.$bank->bank_id.'">'.$bank->bank_code.'</option>';
                }
                $select_in .= '</select>';

                $str .= '<tr>';
                $str .= '<td class="width-3">'.$i.'</td>';
                $str .= '<td>'.$select_out.'</td>';
                $str .= '<td>'.$select_in.'</td>';
                $str .= '<td><input alt="'.$item->internal_transfer_item_id.'" value="'.$this->lib->formatMoney($item->internal_transfer_item_money).'" type="text" name="internal_transfer_item_money[]" class="internal_transfer_item_money numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td>
                  <input value="'.$debit.'" type="text" name="internal_transfer_item_debit[]" class="internal_transfer_item_debit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id"></ul>
                </td>';
                $str .= '<td>
                  <input value="'.$credit.'" type="text" name="internal_transfer_item_credit[]" class="internal_transfer_item_credit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_2"></ul>
                </td>';
                $str .= '</tr>';

              $i++;
            }

            echo $str;
        }
    }

    public function delete(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $internal_transfer_model = $this->model->get('internaltransferModel');
            $internal_transfer_item_model = $this->model->get('internaltransferitemModel');
            $additional_model = $this->model->get('additionalModel');
            $bank_balance_model = $this->model->get('bankbalanceModel');
           
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $internal_transfers = $internal_transfer_model->getBank($data);
                    $internal_transfer_items = $internal_transfer_item_model->getAllBank(array('where'=>'internal_transfer='.$data));
                    foreach ($internal_transfer_items as $item) {
                        $additional_model->queryAdditional('DELETE FROM additional WHERE internal_transfer_item='.$item->internal_transfer_item_id);
                        $bank_balance_model->queryBank('DELETE FROM bank_balance WHERE internal_transfer_item='.$item->internal_transfer_item_id);
                    }
                    $internal_transfer_item_model->queryBank('DELETE FROM internal_transfer_item WHERE internal_transfer='.$data);
                    
                       $internal_transfer_model->deleteBank($data);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|internal_transfer|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    
                    
                }
                return true;
            }
            else{
                    $internal_transfers = $internal_transfer_model->getBank($_POST['data']);
                    $internal_transfer_items = $internal_transfer_item_model->getAllBank(array('where'=>'internal_transfer='.$_POST['data']));
                    foreach ($internal_transfer_items as $item) {
                        $additional_model->queryAdditional('DELETE FROM additional WHERE internal_transfer_item='.$item->internal_transfer_item_id);
                        $bank_balance_model->queryBank('DELETE FROM bank_balance WHERE internal_transfer_item='.$item->internal_transfer_item_id);
                    }
                    $internal_transfer_item_model->queryBank('DELETE FROM internal_transfer_item WHERE internal_transfer='.$_POST['data']);
                    
                        $internal_transfer_model->deleteBank($_POST['data']);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|internal_transfer|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    
            }
            
        }
    }

    public function import(){
        $this->view->disableLayout();
        header('Content-Type: text/html; charset=utf-8');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->account) || json_decode($_SESSION['user_permission_action'])->account != "account") {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_FILES['import']['name'] != null) {

            require("lib/Classes/PHPExcel/IOFactory.php");
            require("lib/Classes/PHPExcel.php");

            $account = $this->model->get('accountModel');

            $objPHPExcel = new PHPExcel();
            // Set properties
            if (pathinfo($_FILES['import']['name'], PATHINFO_EXTENSION) == "xls") {
                $objReader = PHPExcel_IOFactory::createReader('Excel5');
            }
            else if (pathinfo($_FILES['import']['name'], PATHINFO_EXTENSION) == "xlsx") {
                $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            }
            
            $objReader->setReadDataOnly(false);

            $objPHPExcel = $objReader->load($_FILES['import']['tmp_name']);
            $objWorksheet = $objPHPExcel->getActiveSheet();


            $highestRow = $objWorksheet->getHighestRow(); // e.g. 10
            $highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'

            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5

            

            for ($row = 2; $row <= $highestRow; ++ $row) {
                $val = array();
                for ($col = 0; $col < $highestColumnIndex; ++ $col) {
                    $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                    // Check if cell is merged
                    foreach ($objWorksheet->getMergeCells() as $cells) {
                        if ($cell->isInRange($cells)) {
                            $currMergedCellsArray = PHPExcel_Cell::splitRange($cells);
                            $cell = $objWorksheet->getCell($currMergedCellsArray[0][0]);
                            if ($col == 1) {
                                $y++;
                            }
                            
                            break;
                            
                        }
                    }

                    $val[] = $cell->getCalculatedValue();
                    //here's my prob..
                    //echo $val;
                }


                if ($val[1] != null) {
                    
                    $a = trim($val[1]);
                    // $parent = substr($a, 0, strpos($a, '_'));
                    $parent = "";
                    $parent_id = null;

                    if (trim($val[3]) != "") {
                        $parent = trim($val[3]);
                    }

                    if ($parent != "") {
                        $parents = $account->getAccountByWhere(array('account_number'=>$parent));
                        if ($parents) {
                            $parent_id = $parents->account_id;
                        }
                    }

                    if (!$account->getAccountByWhere(array('account_number'=>$a))) {
                        $account_data = array(
                            'account_number' => $a,
                            'account_name' => trim($val[2]),
                            'account_parent' => $parent_id,
                            );

                        $account->createAccount($account_data);
                    }
                    else{
                        $id_account = $account->getAccountByWhere(array('account_number'=>$a))->account_id;

                        $account_data = array(
                            'account_number' => trim($val[1]),
                            'account_name' => trim($val[2]),
                            'account_parent' => $parent_id,
                            );

                            $account->updateAccount($account_data,array('account_id' => $id_account));
                    }
                    
                }
                


            }
            return $this->view->redirect('account');
        }
        $this->view->show('account/import');

    }


}
?>