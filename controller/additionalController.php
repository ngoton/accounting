<?php
Class additionalController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->additional) || json_decode($_SESSION['user_permission_action'])->additional != "additional") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Phát sinh';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;
            $ketthuc = isset($_POST['ketthuc']) ? $_POST['ketthuc'] : null;
            $nv = isset($_POST['nv']) ? $_POST['nv'] : null;
            $tha = isset($_POST['tha']) ? $_POST['tha'] : null;
            $na = isset($_POST['na']) ? $_POST['na'] : null;
            $trangthai = isset($_POST['trangthai']) ? $_POST['trangthai'] : null;
            $code = "";
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'additional_date';
            $order = $this->registry->router->order_by ? $this->registry->router->order : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
            $batdau = '01-'.date('m-Y');
            $ketthuc = date('t-m-Y');
            $nv = 1;
            $tha = date('m');
            $na = date('Y');
            $trangthai = "";
            $code = $this->registry->router->addition;
        }
        $kt = date('d-m-Y',strtotime('+1 day', strtotime($ketthuc)));

        $id = $this->registry->router->param_id;


        $additional_model = $this->model->get('additionalModel');

        $account_model = $this->model->get('accountModel');

        $account_parents = $account_model->getAllAccount(array('order_by'=>'account_number ASC'));
        $account_data = array();
        foreach ($account_parents as $account_parent) {
            $account_data[$account_parent->account_id] = $account_parent->account_number;
        }
        $this->view->data['account_parents'] = $account_parents;
        $this->view->data['account_data'] = $account_data;


        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => 'additional_date >= '.strtotime($batdau).' AND additional_date < '.strtotime($kt),
        );

        if ($id>0) {
            $trangthai = $id;
        }

        if ($trangthai > 0) {
            $data['where'] .= ' AND (debit = '.$trangthai.' OR credit = '.$trangthai.')';
        }
        
        
        $tongsodong = count($additional_model->getAllAdditional($data));
        $tongsotrang = ceil($tongsodong / $sonews);
        

        $this->view->data['page'] = $page;
        $this->view->data['order_by'] = $order_by;
        $this->view->data['order'] = $order;
        $this->view->data['keyword'] = $keyword;
        $this->view->data['pagination_stages'] = $pagination_stages;
        $this->view->data['tongsotrang'] = $tongsotrang;
        $this->view->data['limit'] = $limit;
        $this->view->data['sonews'] = $sonews;
        $this->view->data['batdau'] = $batdau;
        $this->view->data['ketthuc'] = $ketthuc;
        $this->view->data['nv'] = $nv;
        $this->view->data['tha'] = $tha;
        $this->view->data['na'] = $na;
        $this->view->data['trangthai'] = $trangthai;

        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            'where' => 'additional_date >= '.strtotime($batdau).' AND additional_date < '.strtotime($kt),
            );
        
        if ($id>0) {
            $trangthai = $id;
        }

        if ($trangthai > 0) {
            $data['where'] .= ' AND (debit = '.$trangthai.' OR credit = '.$trangthai.')';
        }

        
      
        if ($keyword != '') {
            $search = '( document_number LIKE "%'.$keyword.'%" 
                    OR additional_comment LIKE "%'.$keyword.'%"
                    OR money LIKE "%'.$keyword.'%" 
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        $additionals = $additional_model->getAllAdditional($data);

        
        $this->view->data['additionals'] = $additionals;

        $invoice_buy_item_model = $this->model->get('invoicebuyitemModel');
        $service_buy_item_model = $this->model->get('servicebuyitemModel');
        $invoice_purchase_item_model = $this->model->get('invoicepurchaseitemModel');
        $invoice_sell_item_model = $this->model->get('invoicesellitemModel');
        $invoice_buy_model = $this->model->get('invoicebuyModel');
        $service_buy_model = $this->model->get('servicebuyModel');
        $invoice_purchase_model = $this->model->get('invoicepurchaseModel');
        $invoice_sell_model = $this->model->get('invoicesellModel');
        $customer_model = $this->model->get('customerModel');
        $payment_item_model = $this->model->get('paymentitemModel');
        $additional_other_model = $this->model->get('additionalotherModel');

        $customer_data = array();
        foreach ($additionals as $additional) {
            $cus = 0;
            if ($additional->invoice_buy_item>0) {
                $invoice_buy = $invoice_buy_model->getInvoice($invoice_buy_item_model->getInvoice($additional->invoice_buy_item)->invoice_buy);
                $cus = $invoice_buy->invoice_buy_customer;
            }
            else if ($additional->service_buy_item>0) {
                $service_buy = $service_buy_model->getService($service_buy_item_model->getService($additional->service_buy_item)->service_buy);
                $cus = $service_buy->service_buy_customer;
            }
            else if ($additional->invoice_purchase_item>0) {
                $invoice_purchase = $invoice_purchase_model->getInvoice($invoice_purchase_item_model->getInvoice($additional->invoice_purchase_item)->invoice_purchase);
                $cus = $invoice_purchase->invoice_purchase_customer;
            }
            else if ($additional->invoice_sell_item>0) {
                $invoice_sell = $invoice_sell_model->getInvoice($invoice_sell_item_model->getInvoice($additional->invoice_sell_item)->invoice_sell);
                $cus = $invoice_sell->invoice_sell_customer;
            }
            else if ($additional->payment_item>0) {
                $payment_item = $payment_item_model->getPayment($additional->payment_item);
                $cus = $payment_item->payment_item_customer;
            }
            else if ($additional->additional_other>0) {
                $additional_other = $additional_other_model->getAdditional($additional->additional_other);
                $cus = $additional_other->additional_other_customer;
            }

            if ($cus>0) {
                $customer_data[$additional->additional_id] = $customer_model->getCustomer($cus)->customer_code;
            }
            
        }
        $this->view->data['customer_data'] = $customer_data;

        $this->view->data['lastID'] = isset($additional_model->getLastAdditional()->additional_id)?$additional_model->getLastAdditional()->additional_id:0;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('additional/index');
    }

    
    public function add(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (isset($_POST['yes'])) {
            
            $additional_model = $this->model->get('additionalModel');
            $data = array(
                        
                        'document_number' => trim($_POST['document_number']),
                        'document_date' => strtotime(trim($_POST['document_date'])),
                        'additional_date' => strtotime(trim($_POST['additional_date'])),
                        'additional_comment' => trim($_POST['additional_comment']),
                        'debit' => trim($_POST['debit']),
                        'credit' => trim($_POST['credit']),
                        'money' => trim(str_replace(',','',$_POST['money'])),
                        );
            
            

            if ($_POST['yes'] != "") {
                    $add = $additional_model->getAdditional(trim($_POST['yes']));

                    $additional_model->updateAdditional($data,array('additional_id' => trim($_POST['yes'])));
                    echo "Cập nhật thành công";

                    $account_balance_model->updateAccount($data_debit,array('additional'=>trim($_POST['yes']),'account'=>$add->debit));
                    $account_balance_model->updateAccount($data_credit,array('additional'=>trim($_POST['yes']),'account'=>$add->credit));

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|additional|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                
                
            }
            else{
                
                    $additional_model->createAdditional($data);
                    echo "Thêm thành công";

                    $id_additional = $additional_model->getLastAdditional()->additional_id;
                    $data_debit['additional'] = $id_additional;
                    $data_credit['additional'] = $id_additional;

                    $account_balance_model->createAccount($data_debit);
                    $account_balance_model->createAccount($data_credit);
                

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                    $filename = "action_logs.txt";
                    $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$additional_model->getLastAdditional()->additional_id."|additional|".implode("-",$data)."\n"."\r\n";
                    
                    $fh = fopen($filename, "a") or die("Could not open log file.");
                    fwrite($fh, $text) or die("Could not write file!");
                    fclose($fh);
                
                    
                
            }
                    
        }
    }

    public function delete(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $additional_model = $this->model->get('additionalModel');
           
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                       $additional_model->deleteAdditional($data);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|additional|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    
                    
                }
                return true;
            }
            else{
                        $additional_model->deleteAdditional($_POST['data']);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|additional|"."\n"."\r\n";
                        
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
        if ($_SESSION['role_logined'] > 2 && $_SESSION['role_logined'] != 8) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_FILES['import']['name'] != null) {

            require("lib/Classes/PHPExcel/IOFactory.php");
            require("lib/Classes/PHPExcel.php");

            $account = $this->model->get('accountModel');
            $additional = $this->model->get('additionalModel');

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


                if ($val[3] != null && $val[6] != null && $val[6] > 0 ) {
                    
                    $ngay = PHPExcel_Shared_Date::ExcelToPHP(trim($val[1]));                                      
                    //$ngay = $ngay-3600;

                    $no_id = null;
                    $co_id = null;

                    if (trim($val[4]) != null) {
                        $parents = $account->getAccountByWhere(array('account_number'=>trim($val[4])));
                        if ($parents) {
                            $no_id = $parents->account_id;
                        }
                        else{
                            if (is_numeric(trim($val[4]))) {
                                $acc = array(
                                    'account_number'=>trim($val[4]),
                                    'account_parent'=>0,
                                );
                                $account->createAccount($acc);
                                $no_id = $account->getLastAccount()->account_id;
                            }
                            else{
                                $ac = substr(trim($val[4]), 0, strpos(trim($val[4]), '_'));
                                $acc_parent = $account->getAccountByWhere(array('account_number'=>$ac));
                                $acc = array(
                                    'account_number'=>trim($val[4]),
                                    'account_parent'=>$acc_parent->account_id,
                                );
                                $account->createAccount($acc);
                                $no_id = $account->getLastAccount()->account_id;
                            }
                        }
                    }
                    if (trim($val[5]) != null) {
                        $parents = $account->getAccountByWhere(array('account_number'=>trim($val[5])));
                        if ($parents) {
                            $co_id = $parents->account_id;
                        }
                        else{
                            if (is_numeric(trim($val[5]))) {
                                $acc = array(
                                    'account_number'=>trim($val[5]),
                                    'account_parent'=>0,
                                );
                                $account->createAccount($acc);
                                $co_id = $account->getLastAccount()->account_id;
                            }
                            else{
                                $ac = substr(trim($val[5]), 0, strpos(trim($val[5]), '_'));
                                $acc_parent = $account->getAccountByWhere(array('account_number'=>$ac));
                                $acc = array(
                                    'account_number'=>trim($val[5]),
                                    'account_parent'=>$acc_parent->account_id,
                                );
                                $account->createAccount($acc);
                                $co_id = $account->getLastAccount()->account_id;
                            }
                        }
                    }


                    if (!$additional->getAdditionalByWhere(array('additional_date'=>$ngay,'debit'=>$no_id,'credit'=>$co_id,'money'=>trim($val[6])))) {
                        $additional_data = array(
                            'document_number' => trim($val[0]),
                            'document_date' => $ngay,
                            'additional_date' => $ngay,
                            'additional_comment' => trim($val[3]),
                            'debit' => $no_id,
                            'credit' => $co_id,
                            'money' => trim($val[6]),
                            );

                        $additional->createAdditional($additional_data);

                        

                    }
                    else{
                        $add = $additional->getAdditionalByWhere(array('additional_date'=>$ngay,'debit'=>$no_id,'credit'=>$co_id,'money'=>trim($val[6])));
                        $id_additional = $add->additional_id;

                        $additional_data = array(
                            'document_number' => trim($val[0]),
                            'document_date' => $ngay,
                            'additional_date' => $ngay,
                            'additional_comment' => trim($val[3]),
                            'debit' => $no_id,
                            'credit' => $co_id,
                            'money' => trim($val[6]),
                            );

                            $additional->updateAdditional($additional_data,array('additional_id' => $id_additional));

                        
                    }
                    
                }
                


            }
            return $this->view->redirect('additional');
        }
        $this->view->show('additional/import');

    }

    

}
?>