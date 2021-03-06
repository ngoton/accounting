<?php
Class accountController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->account) || json_decode($_SESSION['user_permission_action'])->account != "account") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Số tài khoản';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'account_number';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
        }
        
        $account_type_model = $this->model->get('accounttypeModel');
        $account_types = $account_type_model->getAllAccount();
        $this->view->data['account_types'] = $account_types;

        $account_model = $this->model->get('accountModel');

        $account_parents = $account_model->getAllAccount(array('order_by'=>'account_number ASC'));
        $account_data = array();
        foreach ($account_parents as $account_parent) {
            $account_data[$account_parent->account_id] = $account_parent->account_name;
        }
        $this->view->data['account_parents'] = $account_parents;
        $this->view->data['account_data'] = $account_data;

        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => '1=1',
        );
        
        
        $tongsodong = count($account_model->getAllAccount($data));
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
            $search = '( account_number LIKE "%'.$keyword.'%" 
                    OR account_name LIKE "%'.$keyword.'%" 
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        

        
        $this->view->data['accounts'] = $account_model->getAllAccount($data);
        $this->view->data['lastID'] = isset($account_model->getLastAccount()->account_id)?$account_model->getLastAccount()->account_id:0;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('account/index');
    }

   
   
    public function add(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (isset($_POST['yes'])) {
            $additional_model = $this->model->get('additionalModel');
            $account_model = $this->model->get('accountModel');
            $invoice_model = $this->model->get('invoiceModel');
            $data = array(
                        
                        'account_number' => trim($_POST['account_number']),
                        'account_name' => trim($_POST['account_name']),
                        'account_parent' => trim($_POST['account_parent']),
                        'account_type' => trim($_POST['account_type']),
                        'account_code' => trim($_POST['account_code']),
                        'account_debit_dauky' => str_replace(',','',$_POST['account_debit_dauky']),
                        'account_credit_dauky' => str_replace(',','',$_POST['account_credit_dauky']),
                        );
            

            if ($_POST['yes'] != "") {
                $check = $account_model->queryAccount('SELECT * FROM account WHERE (account_number='.$data['account_number'].' OR account_name='.$data['account_name'].') AND account_id!='.$_POST['yes']);
                if($check){
                    echo "Tài khoản đã tồn tại";
                    return false;
                }
                else{
                    $account_model->updateAccount($data,array('account_id' => trim($_POST['yes'])));
                    echo "Cập nhật thành công";

                    $id_account = $_POST['yes'];

                    $accs = $account_model->getAccount($id_account);

                    if ($data['account_number'] == "1331") {
                        if ($data['account_debit_dauky']>0 || $data['account_credit_dauky']>0 || $accs->account_debit_dauky>0 || $accs->account_credit_dauky>0) {
                            if ($data['account_debit_dauky']>0) {
                                $data_invoice = array(
                                    'account'=>$id_account,
                                    'invoice_symbol'=>"",
                                    'invoice_date'=>1,
                                    'invoice_number'=>"",
                                    'invoice_customer'=>"",
                                    'invoice_money'=>"",
                                    'invoice_tax'=>$data['account_debit_dauky'],
                                    'invoice_comment'=>"Số dư đầu kỳ",
                                    'invoice_type'=>1,
                                );
                            }
                            elseif ($data['account_credit_dauky']>0) {
                                $data_invoice = array(
                                    'account'=>$id_account,
                                    'invoice_symbol'=>"",
                                    'invoice_date'=>1,
                                    'invoice_number'=>"",
                                    'invoice_customer'=>"",
                                    'invoice_money'=>"",
                                    'invoice_tax'=>$data['account_debit_dauky'],
                                    'invoice_comment'=>"Số dư đầu kỳ",
                                    'invoice_type'=>2,
                                );
                            }
                            $qr = $invoice_model->getInvoiceByWhere(array('account'=>$id_account,'invoice_date'=>1));
                            if ($qr) {
                                $invoice_model->updateInvoice($data_invoice,array('account'=>$id_account,'invoice_date'=>1));
                            }
                            else{
                                $invoice_model->createInvoice($data_invoice);
                            }
                        }
                    }
                    

                    $qr = $additional_model->getAdditionalByWhere(array('account'=>$id_account,'debit'=>$id_account,'additional_date'=>1));
                    if ($qr) {
                        $data_additional = array(
                            'account'=>$id_account,
                            'document_number'=>"",
                            'document_date'=>1,
                            'additional_date'=>1,
                            'additional_comment'=>"Số dư đầu kỳ ".$data['account_name'],
                            'debit'=>$id_account,
                            'credit'=>0,
                            'money'=>$data['account_debit_dauky'],
                        );
                        $additional_model->updateAdditional($data_additional,array('account'=>$id_account,'debit'=>$id_account,'additional_date'=>1));
                    
                    }
                    else{
                        if ($data['account_debit_dauky']>0) {
                            $data_additional = array(
                                'account'=>$id_account,
                                'document_number'=>"",
                                'document_date'=>1,
                                'additional_date'=>1,
                                'additional_comment'=>"Số dư đầu kỳ ".$data['account_name'],
                                'debit'=>$id_account,
                                'credit'=>0,
                                'money'=>$data['account_debit_dauky'],
                            );
                            $additional_model->createAdditional($data_additional);
                        }
                    }

                    $qr2 = $additional_model->getAdditionalByWhere(array('account'=>$id_account,'credit'=>$id_account,'additional_date'=>1));
                    if ($qr2) {
                        $data_additional = array(
                            'account'=>$id_account,
                            'document_number'=>"",
                            'document_date'=>1,
                            'additional_date'=>1,
                            'additional_comment'=>"Số dư đầu kỳ ".$data['account_name'],
                            'debit'=>0,
                            'credit'=>$id_account,
                            'money'=>$data['account_credit_dauky'],
                        );
                        $additional_model->updateAdditional($data_additional,array('account'=>$id_account,'credit'=>$id_account,'additional_date'=>1));
                    }
                    else{
                        if ($data['account_credit_dauky']>0) {
                            $data_additional = array(
                                'account'=>$id_account,
                                'document_number'=>"",
                                'document_date'=>1,
                                'additional_date'=>1,
                                'additional_comment'=>"Số dư đầu kỳ ".$data['account_name'],
                                'debit'=>0,
                                'credit'=>$id_account,
                                'money'=>$data['account_credit_dauky'],
                            );
                            $additional_model->createAdditional($data_additional);
                        }
                    }
                    
                    

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|account|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                
            }
            else{
                $check = $account_model->queryAccount('SELECT * FROM account WHERE (account_number='.$data['account_number'].' OR account_name='.$data['account_name'].')');
                if($check){
                    echo "Tài khoản đã tồn tại";
                    return false;
                }
                else{
                    $account_model->createAccount($data);
                    echo "Thêm thành công";

                $id_account = $account->getLastAccount()->account_id;

                if ($data['account_number'] == "1331") {
                    if ($data['account_debit_dauky']>0 || $data['account_credit_dauky']>0) {
                        if ($data['account_debit_dauky']>0) {
                            $data_invoice = array(
                                'account'=>$id_account,
                                'invoice_symbol'=>"",
                                'invoice_date'=>1,
                                'invoice_number'=>"",
                                'invoice_customer'=>"",
                                'invoice_money'=>"",
                                'invoice_tax'=>$data['account_debit_dauky'],
                                'invoice_comment'=>"Số dư đầu kỳ",
                                'invoice_type'=>1,
                            );
                        }
                        elseif ($data['account_credit_dauky']>0) {
                            $data_invoice = array(
                                'account'=>$id_account,
                                'invoice_symbol'=>"",
                                'invoice_date'=>1,
                                'invoice_number'=>"",
                                'invoice_customer'=>"",
                                'invoice_money'=>"",
                                'invoice_tax'=>$data['account_debit_dauky'],
                                'invoice_comment'=>"Số dư đầu kỳ",
                                'invoice_type'=>2,
                            );
                        }
                        
                        $invoice_model->createInvoice($data_invoice);
                    }
                    
                }

                if ($data['account_debit_dauky']>0) {
                    $data_additional = array(
                        'account'=>$id_account,
                        'document_number'=>"",
                        'document_date'=>1,
                        'additional_date'=>1,
                        'additional_comment'=>"Số dư đầu kỳ ".$data['account_name'],
                        'debit'=>$id_account,
                        'credit'=>0,
                        'money'=>$data['account_debit_dauky'],
                    );
                    $additional_model->createAdditional($data_additional);
                }
                if ($data['account_credit_dauky']>0) {
                    $data_additional = array(
                        'account'=>$id_account,
                        'document_number'=>"",
                        'document_date'=>1,
                        'additional_date'=>1,
                        'additional_comment'=>"Số dư đầu kỳ ".$data['account_name'],
                        'debit'=>0,
                        'credit'=>$id_account,
                        'money'=>$data['account_credit_dauky'],
                    );
                    $additional_model->createAdditional($data_additional);
                }

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                    $filename = "action_logs.txt";
                    $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$account_model->getLastAccount()->account_id."|account|".implode("-",$data)."\n"."\r\n";
                    
                    $fh = fopen($filename, "a") or die("Could not open log file.");
                    fwrite($fh, $text) or die("Could not write file!");
                    fclose($fh);
                }
                    
                
            }
                    
        }
    }

    public function delete(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $account_model = $this->model->get('accountModel');
            $additional_model = $this->model->get('additionalModel');
            $invoice_model = $this->model->get('invoiceModel');
           
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $additional_model->queryAdditional('DELETE FROM additional WHERE account='.$data);
                    $invoice_model->queryInvoice('DELETE FROM invoice WHERE account='.$data);
                       $account_model->deleteAccount($data);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|account|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    
                    
                }
                return true;
            }
            else{
                $additional_model->queryAdditional('DELETE FROM additional WHERE account='.$_POST['data']);
                $invoice_model->queryInvoice('DELETE FROM invoice WHERE account='.$_POST['data']);
                        $account_model->deleteAccount($_POST['data']);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|account|"."\n"."\r\n";
                        
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
                            'account_code' => trim($val[4]),
                            );

                        $account->createAccount($account_data);
                    }
                    else{
                        $id_account = $account->getAccountByWhere(array('account_number'=>$a))->account_id;

                        $account_data = array(
                            'account_number' => trim($val[1]),
                            'account_name' => trim($val[2]),
                            'account_parent' => $parent_id,
                            'account_code' => trim($val[4]),
                            );

                            $account->updateAccount($account_data,array('account_id' => $id_account));
                    }
                    
                }
                


            }
            return $this->view->redirect('account');
        }
        $this->view->show('account/import');

    }

    public function importasset(){
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
            $account_balance = $this->model->get('accountbalanceModel');

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

            $nameWorksheet = trim($objWorksheet->getTitle());


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
                    if (!$account->getAccountByWhere(array('account_number'=>$a))) {
                        if (is_numeric($a)) {
                            $acc = array(
                                'account_number'=>$a,
                                'account_parent'=>0,
                            );
                            $account->createAccount($acc);
                            $id_account = $account->getLastAccount()->account_id;
                        }
                        else{
                            $ac = substr($a, 0, strpos($a, '_'));
                            $acc_parent = $account->getAccountByWhere(array('account_number'=>$ac));
                            $acc = array(
                                'account_number'=>$a,
                                'account_parent'=>$acc_parent->account_id,
                            );
                            $account->createAccount($acc);
                            $id_account = $account->getLastAccount()->account_id;
                        }

                    }
                    else{
                        $id_account = $account->getAccountByWhere(array('account_number'=>$a))->account_id;
                    }

                    if ($val[3] != null) {
                        $account_data = array(
                            'account_balance_date' => strtotime($nameWorksheet),
                            'account' => $id_account,
                            'money' => trim($val[3]),
                            'week' => (int)date('W', strtotime($nameWorksheet)),
                            'year' => (int)date('Y', strtotime($nameWorksheet)),
                            );

                        if($account_data['week'] == 53){
                            $account_data['week'] = 1;
                            $account_data['year'] = $account_data['year']+1;

                            $account_data['week'] = 1;
                            $account_data['year'] = $account_data['year']+1;
                        }
                        if (((int)date('W', strtotime($nameWorksheet)) == 1) && ((int)date('m', strtotime($nameWorksheet)) == 12) ) {
                            $account_data['year'] = (int)date('Y', strtotime($nameWorksheet))+1;
                            $account_data['year'] = (int)date('Y', strtotime($nameWorksheet))+1;
                        }

                        $account_balance->createAccount($account_data);
                    }
                    if ($val[4] != null) {
                        $account_data = array(
                            'account_balance_date' => strtotime($nameWorksheet),
                            'account' => $id_account,
                            'money' => 0-trim($val[4]),
                            'week' => (int)date('W', strtotime($nameWorksheet)),
                            'year' => (int)date('Y', strtotime($nameWorksheet)),
                            );

                        if($account_data['week'] == 53){
                            $account_data['week'] = 1;
                            $account_data['year'] = $account_data['year']+1;

                            $account_data['week'] = 1;
                            $account_data['year'] = $account_data['year']+1;
                        }
                        if (((int)date('W', strtotime($nameWorksheet)) == 1) && ((int)date('m', strtotime($nameWorksheet)) == 12) ) {
                            $account_data['year'] = (int)date('Y', strtotime($nameWorksheet))+1;
                            $account_data['year'] = (int)date('Y', strtotime($nameWorksheet))+1;
                        }

                        $account_balance->createAccount($account_data);
                    }
                    
                    
                }
                


            }
            return $this->view->redirect('account');
        }
        $this->view->show('account/importasset');

    }

    

}
?>