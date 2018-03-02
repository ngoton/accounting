<?php
Class additionalotherController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->additionalother) || json_decode($_SESSION['user_permission_action'])->additionalother != "additionalother") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Nghiệp vụ kế toán';

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
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'additional_other_document_date';
            $order = $this->registry->router->order_by ? $this->registry->router->order : 'ASC, additional_other_document_number ASC';
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


        $additional_other_model = $this->model->get('additionalotherModel');
        $customer_model = $this->model->get('customerModel');
        $account_model = $this->model->get('accountModel');

        $account_parents = $account_model->getAllAccount(array('order_by'=>'account_number ASC'));
        $account_data = array();
        foreach ($account_parents as $account_parent) {
            $account_data[$account_parent->account_id] = $account_parent->account_number;
        }
        $this->view->data['account_parents'] = $account_parents;
        $this->view->data['account_data'] = $account_data;

        $customers = $customer_model->getAllCustomer();
        $customer_data = array();
        foreach ($customers as $customer) {
            $customer_data[$customer->customer_id] = $customer->customer_name;
        }
        $this->view->data['customer_data'] = $customer_data;

        $bank_model = $this->model->get('bankModel');
        $banks = $bank_model->getAllBank();
        $this->view->data['banks'] = $banks;


        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => 'additional_other_additional_date >= '.strtotime($batdau).' AND additional_other_additional_date < '.strtotime($kt),
        );

        if ($id>0) {
            $trangthai = $id;
        }

        if ($trangthai > 0) {
            $account_choose = $account_model->getAccount($trangthai);
            if ($account_choose->account_parent > 0) {
                $data['where'] .= ' AND (additional_other_debit = '.$trangthai.' OR additional_other_credit = '.$trangthai.')';
            }
            else{
                $data['where'] .= ' AND (additional_other_debit IN (SELECT account_id FROM account WHERE account_parent = '.$trangthai.') OR additional_other_credit IN (SELECT account_id FROM account WHERE account_parent = '.$trangthai.') )';
            }
        }
        
        
        $tongsodong = count($additional_other_model->getAllAdditional($data));
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
            'where' => 'additional_other_additional_date >= '.strtotime($batdau).' AND additional_other_additional_date < '.strtotime($kt),
            );
        
        if ($id>0) {
            $trangthai = $id;
        }

        if ($trangthai > 0) {
            $account_choose = $account_model->getAccount($trangthai);
            if ($account_choose->account_parent > 0) {
                $data['where'] .= ' AND (additional_other_debit = '.$trangthai.' OR additional_other_credit = '.$trangthai.')';
            }
            else{
                $data['where'] .= ' AND (additional_other_debit IN (SELECT account_id FROM account WHERE account_parent = '.$trangthai.') OR additional_other_credit IN (SELECT account_id FROM account WHERE account_parent = '.$trangthai.') )';
            }
        }

        
      
        if ($keyword != '') {
            $search = '( additional_other_document LIKE "%'.$keyword.'%" 
                    OR additional_other_comment LIKE "%'.$keyword.'%"
                    OR additional_other_money LIKE "%'.$keyword.'%" 
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        

        
        $this->view->data['additionals'] = $additional_other_model->getAllAdditional($data);
        $this->view->data['lastID'] = isset($additional_other_model->getLastAdditional()->additional_other_id)?$additional_other_model->getLastAdditional()->additional_other_document_number:0;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('additionalother/index');
    }

    public function getCustomer(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $customer_model = $this->model->get('customerModel');
            if ($_POST['keyword'] == "*") {
                $list = $customer_model->getAllCustomer();
            }
            else{
                $data = array(
                'where'=>'( customer_code LIKE "%'.$_POST['keyword'].'%" OR customer_name LIKE "%'.$_POST['keyword'].'%" )',
                );
                $list = $customer_model->getAllCustomer($data);
            }
            foreach ($list as $rs) {
                $customer_name = $rs->customer_code.' | '.$rs->customer_name;
                if ($_POST['keyword'] != "*") {
                    $customer_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->customer_code.' | '.$rs->customer_name);
                }
                echo '<li onclick="set_item_customer(\''.$rs->customer_id.'\',\''.$rs->customer_code.'\',\''.$rs->customer_name.'\',\''.$rs->customer_company.'\',\''.$rs->customer_mst.'\',\''.$rs->customer_address.'\',\''.$_POST['offset'].'\')">'.$customer_name.'</li>';
            }
        }
    }

    public function add(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (isset($_POST['yes'])) {
            
            $additional_other_model = $this->model->get('additionalotherModel');
            $additional_model = $this->model->get('additionalModel');
            $bank_balance_model = $this->model->get('bankbalanceModel');
            $debit_model = $this->model->get('debitModel');
            $invoice_model = $this->model->get('invoiceModel');
            $data = array(
                        
                        'additional_other_document_number' => trim($_POST['additional_other_document_number']),
                        'additional_other_document_date' => strtotime(str_replace('/','-',$_POST['additional_other_document_date'])),
                        'additional_other_additional_date' => strtotime(str_replace('/','-',$_POST['additional_other_additional_date'])),
                        'additional_other_comment' => trim($_POST['additional_other_comment']),
                        'additional_other_debit' => trim($_POST['additional_other_debit']),
                        'additional_other_credit' => trim($_POST['additional_other_credit']),
                        'additional_other_money' => trim(str_replace(',','',$_POST['additional_other_money'])),
                        'additional_other_customer' => trim($_POST['additional_other_customer']),
                        'additional_other_bank' => trim($_POST['additional_other_bank']),
                        'additional_other_bank_check' => trim($_POST['additional_other_bank_check']),
                        'additional_other_tax_percent' => trim($_POST['additional_other_tax_percent']),
                        'additional_other_tax_debit' => trim($_POST['additional_other_tax_debit']),
                        'additional_other_tax' => str_replace(',', '', $_POST['additional_other_tax']),
                        'additional_other_invoice_number' => trim($_POST['additional_other_invoice_number']),
                        'additional_other_invoice_date' => strtotime(str_replace('/','-',$_POST['additional_other_invoice_date'])),
                        'additional_other_invoice_symbol' => trim($_POST['additional_other_invoice_symbol']),
                        );
            
            

            if ($_POST['yes'] != "") {
                    $add = $additional_other_model->getAdditional(trim($_POST['yes']));
                    $id_additional = $_POST['yes'];
                    $additional_other_model->updateAdditional($data,array('additional_other_id' => trim($_POST['yes'])));

                    $data_additional = array(
                        'additional_other'=>$id_additional,
                        'document_number'=>$data['additional_other_document_number'],
                        'document_date'=>$data['additional_other_document_date'],
                        'additional_date'=>$data['additional_other_additional_date'],
                        'additional_comment'=>$data['additional_other_comment'],
                        'debit'=>$data['additional_other_debit'],
                        'credit'=>$data['additional_other_credit'],
                        'money'=>$data['additional_other_money'],
                    );
                    $additional_model->updateAdditional($data_additional,array('additional_other'=>$id_additional,'debit'=>$add->additional_other_debit));

                    if ($add->additional_other_bank>=0) {
                        if ($data['additional_other_bank']=="") {
                            $bank_balance_model->queryBank('DELETE FROM bank_balance WHERE additional_other='.$id_additional);
                        }
                        else{
                            if ($data['additional_other_bank_check']==1 && $data['additional_other_bank']>=0) {
                                $data_bank = array(
                                    'additional_other'=>$id_additional,
                                    'bank_balance_date'=>$data['additional_other_document_date'],
                                    'bank'=>$data['additional_other_bank'],
                                    'bank_balance_money'=>$data['additional_other_money']+$data['additional_other_tax'],
                                );
                                $bank_balance_model->updateBank($data_bank,array('additional_other'=>$id_additional));
                            }
                            if ($data['additional_other_bank_check']==2 && $data['additional_other_bank']>=0) {
                                $data_bank = array(
                                    'additional_other'=>$id_additional,
                                    'bank_balance_date'=>$data['additional_other_document_date'],
                                    'bank'=>$data['additional_other_bank'],
                                    'bank_balance_money'=>(0-$data['additional_other_money']-$data['additional_other_tax']),
                                );
                                $bank_balance_model->updateBank($data_bank,array('additional_other'=>$id_additional));
                            }
                        }
                    }
                    else{
                        if ($data['additional_other_bank_check']==1 && $data['additional_other_bank']>=0) {
                            $data_bank = array(
                                'additional_other'=>$id_additional,
                                'bank_balance_date'=>$data['additional_other_document_date'],
                                'bank'=>$data['additional_other_bank'],
                                'bank_balance_money'=>$data['additional_other_money']+$data['additional_other_tax'],
                            );
                            $bank_balance_model->createBank($data_bank);
                        }
                        if ($data['additional_other_bank_check']==2 && $data['additional_other_bank']>=0) {
                            $data_bank = array(
                                'additional_other'=>$id_additional,
                                'bank_balance_date'=>$data['additional_other_document_date'],
                                'bank'=>$data['additional_other_bank'],
                                'bank_balance_money'=>(0-$data['additional_other_money']-$data['additional_other_tax']),
                            );
                            $bank_balance_model->createBank($data_bank);
                        }
                    }

                    if ($add->additional_other_customer>0) {
                        if ($data['additional_other_customer']==0 || $data['additional_other_customer']=="") {
                            $debit_model->queryDebit('DELETE FROM debit WHERE additional_other='.$id_additional);
                        }
                        else{
                            if ($data['additional_other_bank_check']>0) {
                                $data_debit = array(
                                    'additional_other'=>$id_additional,
                                    'debit_date'=>$data['additional_other_document_date'],
                                    'debit_customer'=>$data['additional_other_customer'],
                                    'debit_money'=>(0-$data['additional_other_money']-$data['additional_other_tax']),
                                    'debit_comment'=>$data['additional_other_comment'],
                                );
                            }
                            else{
                                $data_debit = array(
                                    'additional_other'=>$id_additional,
                                    'debit_date'=>$data['additional_other_document_date'],
                                    'debit_customer'=>$data['additional_other_customer'],
                                    'debit_money'=>$data['additional_other_money']+$data['additional_other_tax'],
                                    'debit_comment'=>$data['additional_other_comment'],
                                );
                            }
                            $debit_model->updateDebit($data_debit,array('additional_other'=>$id_additional,));
                        }
                    }
                    else{
                        if ($data['additional_other_customer']>0) {
                            if ($data['additional_other_bank_check']>0) {
                                $data_debit = array(
                                    'additional_other'=>$id_additional,
                                    'debit_date'=>$data['additional_other_document_date'],
                                    'debit_customer'=>$data['additional_other_customer'],
                                    'debit_money'=>(0-$data['additional_other_money']-$data['additional_other_tax']),
                                    'debit_comment'=>$data['additional_other_comment'],
                                );
                            }
                            else{
                                $data_debit = array(
                                    'additional_other'=>$id_additional,
                                    'debit_date'=>$data['additional_other_document_date'],
                                    'debit_customer'=>$data['additional_other_customer'],
                                    'debit_money'=>$data['additional_other_money']+$data['additional_other_tax'],
                                    'debit_comment'=>$data['additional_other_comment'],
                                );
                            }
                            $debit_model->createDebit($data_debit);
                        }
                    }
                    
                    if ($add->additional_other_tax_debit>0) {
                        if ($data['additional_other_tax_debit']==0 || $data['additional_other_tax_debit']=="") {
                            $additional_model->queryAdditional('DELETE FROM additional WHERE additional_other='.$id_additional.' AND debit='.$add->additional_other_tax_debit);
                            $invoice_model->queryInvoice('DELETE FROM invoice WHERE additional_other='.$id_additional);
                        }
                        else{
                            $data_additional = array(
                                'additional_other'=>$id_additional,
                                'document_number'=>$data['additional_other_document_number'],
                                'document_date'=>$data['additional_other_document_date'],
                                'additional_date'=>$data['additional_other_additional_date'],
                                'additional_comment'=>$data['additional_other_comment'],
                                'debit'=>$data['additional_other_tax_debit'],
                                'credit'=>$data['additional_other_credit'],
                                'money'=>$data['additional_other_tax'],
                            );
                            $additional_model->updateAdditional($data_additional,array('additional_other'=>$id_additional,'debit'=>$add->additional_other_tax_debit));

                            $data_invoice = array(
                                'additional_other'=>$id_additional,
                                'invoice_date'=>$data['additional_other_invoice_date'],
                                'invoice_number'=>$data['additional_other_invoice_number'],
                                'invoice_customer'=>$data['additional_other_customer'],
                                'invoice_money'=>$data['additional_other_money'],
                                'invoice_tax'=>$data['additional_other_tax'],
                                'invoice_comment'=>$data['additional_other_comment'],
                                'invoice_type'=>1,
                            );
                            $invoice_model->updateInvoice($data_invoice,array('additional_other'=>$id_additional));
                        }
                    }
                    else{
                        if ($data['additional_other_tax_debit']>0) {
                            $data_additional = array(
                                'additional_other'=>$id_additional,
                                'document_number'=>$data['additional_other_document_number'],
                                'document_date'=>$data['additional_other_document_date'],
                                'additional_date'=>$data['additional_other_additional_date'],
                                'additional_comment'=>$data['additional_other_comment'],
                                'debit'=>$data['additional_other_tax_debit'],
                                'credit'=>$data['additional_other_credit'],
                                'money'=>$data['additional_other_tax'],
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_invoice = array(
                                'additional_other'=>$id_additional,
                                'invoice_date'=>$data['additional_other_invoice_date'],
                                'invoice_symbol'=>$data['additional_other_invoice_symbol'],
                                'invoice_number'=>$data['additional_other_invoice_number'],
                                'invoice_customer'=>$data['additional_other_customer'],
                                'invoice_money'=>$data['additional_other_money'],
                                'invoice_tax'=>$data['additional_other_tax'],
                                'invoice_comment'=>$data['additional_other_comment'],
                                'invoice_type'=>1,
                            );
                            $invoice_model->createInvoice($data_invoice);
                        }
                    }

                    echo "Cập nhật thành công";

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|additional|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                
                
            }
            else{
                
                    $additional_other_model->createAdditional($data);
                    echo "Thêm thành công";

                    $id_additional = $additional_other_model->getLastAdditional()->additional_other_id;

                    $data_additional = array(
                        'additional_other'=>$id_additional,
                        'document_number'=>$data['additional_other_document_number'],
                        'document_date'=>$data['additional_other_document_date'],
                        'additional_date'=>$data['additional_other_additional_date'],
                        'additional_comment'=>$data['additional_other_comment'],
                        'debit'=>$data['additional_other_debit'],
                        'credit'=>$data['additional_other_credit'],
                        'money'=>$data['additional_other_money'],
                    );
                    $additional_model->createAdditional($data_additional);

                    if ($data['additional_other_bank_check']==1 && $data['additional_other_bank']>=0) {
                        $data_bank = array(
                            'additional_other'=>$id_additional,
                            'bank_balance_date'=>$data['additional_other_document_date'],
                            'bank'=>$data['additional_other_bank'],
                            'bank_balance_money'=>$data['additional_other_money']+$data['additional_other_tax'],
                        );
                        $bank_balance_model->createBank($data_bank);
                    }
                    else if ($data['additional_other_bank_check']==2 && $data['additional_other_bank']>=0) {
                        $data_bank = array(
                            'additional_other'=>$id_additional,
                            'bank_balance_date'=>$data['additional_other_document_date'],
                            'bank'=>$data['additional_other_bank'],
                            'bank_balance_money'=>(0-$data['additional_other_money']-$data['additional_other_tax']),
                        );
                        $bank_balance_model->createBank($data_bank);
                    }
                    
                    if ($data['additional_other_customer']>0) {
                        if ($data['additional_other_bank_check']>0) {
                            $data_debit = array(
                                'additional_other'=>$id_additional,
                                'debit_date'=>$data['additional_other_document_date'],
                                'debit_customer'=>$data['additional_other_customer'],
                                'debit_money'=>(0-$data['additional_other_money']-$data['additional_other_tax']),
                                'debit_comment'=>$data['additional_other_comment'],
                            );
                        }
                        else{
                            $data_debit = array(
                                'additional_other'=>$id_additional,
                                'debit_date'=>$data['additional_other_document_date'],
                                'debit_customer'=>$data['additional_other_customer'],
                                'debit_money'=>$data['additional_other_money']+$data['additional_other_tax'],
                                'debit_comment'=>$data['additional_other_comment'],
                            );
                        }
                        $debit_model->createDebit($data_debit);
                    }

                    if ($data['additional_other_tax_debit']>0) {
                        $data_additional = array(
                            'additional_other'=>$id_additional,
                            'document_number'=>$data['additional_other_document_number'],
                            'document_date'=>$data['additional_other_document_date'],
                            'additional_date'=>$data['additional_other_additional_date'],
                            'additional_comment'=>$data['additional_other_comment'],
                            'debit'=>$data['additional_other_tax_debit'],
                            'credit'=>$data['additional_other_credit'],
                            'money'=>$data['additional_other_tax'],
                        );
                        $additional_model->createAdditional($data_additional);

                        $data_invoice = array(
                            'additional_other'=>$id_additional,
                            'invoice_date'=>$data['additional_other_invoice_date'],
                            'invoice_symbol'=>$data['additional_other_invoice_symbol'],
                            'invoice_number'=>$data['additional_other_invoice_number'],
                            'invoice_customer'=>$data['additional_other_customer'],
                            'invoice_money'=>$data['additional_other_money'],
                            'invoice_tax'=>$data['additional_other_tax'],
                            'invoice_comment'=>$data['additional_other_comment'],
                            'invoice_type'=>1,
                        );
                        $invoice_model->createInvoice($data_invoice);
                    }
                    
                

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                    $filename = "action_logs.txt";
                    $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$additional_other_model->getLastAdditional()->additional_other_id."|additional|".implode("-",$data)."\n"."\r\n";
                    
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
            $additional_other_model = $this->model->get('additionalotherModel');
            $additional_model = $this->model->get('additionalModel');
            $bank_balance_model = $this->model->get('bankbalanceModel');
            $debit_model = $this->model->get('debitModel');
            $invoice_model = $this->model->get('invoiceModel');

            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $additional_model->queryAdditional('DELETE FROM additional WHERE additional_other='.$data);
                    $bank_balance_model->queryBank('DELETE FROM bank_balance WHERE additional_other='.$data);
                    $debit_model->queryDebit('DELETE FROM debit WHERE additional_other='.$data);
                    $invoice_model->queryInvoice('DELETE FROM invoice WHERE additional_other='.$data);

                       $additional_other_model->deleteAdditional($data);
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
                $additional_model->queryAdditional('DELETE FROM additional WHERE additional_other='.$_POST['data']);
                $bank_balance_model->queryBank('DELETE FROM bank_balance WHERE additional_other='.$_POST['data']);
                $debit_model->queryDebit('DELETE FROM debit WHERE additional_other='.$_POST['data']);
                $invoice_model->queryInvoice('DELETE FROM invoice WHERE additional_other='.$_POST['data']);
                        $additional_other_model->deleteAdditional($_POST['data']);
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


}
?>