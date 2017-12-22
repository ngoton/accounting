<?php
Class paycashController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->payment) || json_decode($_SESSION['user_permission_action'])->payment != "payment") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Thu chi tiền mặt ngân hàng';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'payment_document_date';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
        }
        
        $payment_model = $this->model->get('paymentModel');


        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => 'payment_bank_type=1 AND payment_type=2 AND payment_check=1 AND payment_in_ex=1',
        );
        
        
        $tongsodong = count($payment_model->getAllPayment($data));
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
            'where' => 'payment_bank_type=1 AND payment_type=2 AND payment_check=1 AND payment_in_ex=1',
            );
        
      
        if ($keyword != '') {
            $search = '( payment_document_number LIKE "%'.$keyword.'%"  
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        

        
        $this->view->data['payments'] = $payment_model->getAllPayment($data);
        $this->view->data['lastID'] = isset($payment_model->getPaymentByWhere(array('payment_bank_type'=>1,'payment_type'=>2,'payment_check'=>1,'payment_in_ex'=>1))->payment_document_number)?$payment_model->getPaymentByWhere(array('payment_bank_type'=>1,'payment_type'=>2,'payment_check'=>1,'payment_in_ex'=>1))->payment_document_number:0;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('paycash/index');
    }

    public function printview() {
        $this->view->disableLayout();
        $this->view->data['lib'] = $this->lib;

        $payment = $this->registry->router->param_id;

        $payment_model = $this->model->get('paymentModel');
        $payments = $payment_model->getPayment($payment);

        $this->view->data['payments'] = $payments;

        $info_model = $this->model->get('infoModel');
        $this->view->data['infos'] = $info_model->getLastInfo();

        $this->view->show('paycash/printview');
    }

   public function getItem(){
        $debit_model = $this->model->get('debitModel');

        $rowIndex = $_POST['rowIndex'];

        $qr = 'SELECT a.*, d.customer_name, d.customer_code, d.customer_id FROM service_buy a, customer d WHERE a.service_buy_customer=d.customer_id AND  (SELECT SUM(c.debit_money) FROM debit c WHERE c.service_buy=a.service_buy_id GROUP BY c.service_buy)>0 ';

        $debits = $debit_model->queryDebit($qr);
        
        $str = '<table class="table_data" id="tblExport2">';
        $str .= '<thead><tr><th class="fix"><input type="checkbox" onclick="checkall(\'checkbox2\', this)" name="checkall"/></th><th class="fix">Số CT</th><th class="fix">Ngày CT</th><th class="fix">Khách hàng</th><th class="fix">Diễn giải</th><th class="fix">Số tiền phải chi</th><th class="fix">Số tiền đã trả</th><th class="fix">Còn lại</th></tr></thead>';
        $str .= '<tbody>';

        foreach ($debits as $debit) {
            $pays = $debit_model->getAllDebit(array('where'=>'debit_money<0 AND service_buy='.$debit->service_buy_id));
            $used=0;
            foreach ($pays as $pay) {
                $used += $pay->debit_money;
            }
            $money = $debit->service_buy_total+$used;
            $str .= '<tr class="tr" data="'.$debit->service_buy_id.'"><td><input name="check_i[]" type="checkbox" class="checkbox2" data="'.$rowIndex.'" value="'.$debit->service_buy_id.'" data-doc="'.$debit->service_buy_document_number.'" data-date="'.date('d/m/Y',$debit->service_buy_document_date).'" data-invoice="'.$debit->service_buy_number.'" data-cus="'.$debit->service_buy_customer.'" data-cuscode="'.$debit->customer_code.'" data-cusname="'.$debit->customer_name.'" data-rece="'.$this->lib->formatMoney($debit->service_buy_total).'" data-pay="'.$this->lib->formatMoney($used).'" data-money="'.$this->lib->formatMoney($money).'" ></td><td class="fix">'.$debit->service_buy_document_number.'</td><td class="fix">'.$this->lib->hien_thi_ngay_thang($debit->service_buy_document_date).'</td><td class="fix">'.$debit->customer_name.'</td><td class="fix">'.$debit->service_buy_comment.'</td><td class="fix">'.$this->lib->formatMoney($debit->service_buy_total).'</td><td class="fix">'.$this->lib->formatMoney($used).'</td><td class="fix"><input style="width:120px" type="text" name="money_add[]" class="money_add numbers" value="'.$this->lib->formatMoney($money).'" max="'.$money.'"></td></tr>';
        }
        
        $str .= '</tbody></table>';
        echo $str;
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
                echo '<li onclick="set_item_customer(\''.$rs->customer_id.'\',\''.$rs->customer_code.'\',\''.$rs->customer_name.'\',\''.$_POST['offset'].'\')">'.$customer_name.'</li>';
            }
        }
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
            
            $payment_model = $this->model->get('paymentModel');
            $payment_item_model = $this->model->get('paymentitemModel');
            $additional_model = $this->model->get('additionalModel');
            $debit_model = $this->model->get('debitModel');
            $account_model = $this->model->get('accountModel');
            $bank_balance_model = $this->model->get('bankbalanceModel');
            $invoice_model = $this->model->get('invoiceModel');

            $items = $_POST['item'];

            $data = array(
                        
                        'payment_document_date' => strtotime(str_replace('/','-',$_POST['payment_document_date'])),
                        'payment_document_number' => trim($_POST['payment_document_number']),
                        'payment_additional_date' => strtotime(str_replace('/','-',$_POST['payment_additional_date'])),
                        'payment_person' => trim($_POST['payment_person']),
                        'payment_origin_doc' => trim($_POST['payment_origin_doc']),
                        'payment_comment' => trim($_POST['payment_comment']),
                        'payment_bank' => trim($_POST['payment_bank']),
                        'payment_bank_type' => trim($_POST['payment_bank_type']),
                        'payment_type' => trim($_POST['payment_type']),
                        'payment_check' => trim($_POST['payment_check']),
                        'payment_in_ex' => trim($_POST['payment_in_ex']),
                        );
            

            if ($_POST['yes'] != "") {
                $check = $payment_model->queryPayment('SELECT * FROM payment WHERE (payment_document_number='.$data['payment_document_number'].') AND payment_id!='.$_POST['yes']);
                if($check){
                    echo "Chứng từ này đã tồn tại";
                    return false;
                }
                else{
                    $payment_model->updatePayment($data,array('payment_id' => trim($_POST['yes'])));
                    echo "Cập nhật thành công";

                    $id_payment = $_POST['yes'];

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|payment|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                
            }
            else{
                $check = $payment_model->queryPayment('SELECT * FROM payment WHERE (payment_document_number='.$data['payment_document_number'].')');
                if($check){
                    echo "Chứng từ này đã tồn tại";
                    return false;
                }
                else{
                    $data['payment_create_user'] = $_SESSION['userid_logined'];
                    $data['payment_create_date'] = strtotime(date('d-m-Y'));

                    $payment_model->createPayment($data);
                    echo "Thêm thành công";

                $id_payment = $payment_model->getLastPayment()->payment_id;

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                    $filename = "action_logs.txt";
                    $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$payment_model->getLastPayment()->payment_id."|payment|".implode("-",$data)."\n"."\r\n";
                    
                    $fh = fopen($filename, "a") or die("Could not open log file.");
                    fwrite($fh, $text) or die("Could not write file!");
                    fclose($fh);
                }
                    
                
            }

            if (isset($id_payment)) {
                $payments = $payment_model->getPayment($id_payment);

                $money = 0;
                $vat = 0;

                $arr_item = "";
                foreach ($items as $v) {
                    if($v['payment_item_customer'] != ""){
                        $debit = 0;
                        $credit = 0;
                        $tax_debit = 0;
                        if (trim($v['payment_item_debit']) != "") {
                            $debits = $account_model->getAccountByWhere(array('account_number'=>trim($v['payment_item_debit'])));
                            if (!$debits) {
                                $account_model->createAccount(array('account_number'=>trim($v['payment_item_debit'])));
                                $debit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $debit = $debits->account_id;
                            }
                        }
                        if (trim($v['payment_item_credit']) != "") {
                            $credits = $account_model->getAccountByWhere(array('account_number'=>trim($v['payment_item_credit'])));
                            if (!$credits) {
                                $account_model->createAccount(array('account_number'=>trim($v['payment_item_credit'])));
                                $credit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $credit = $credits->account_id;
                            }
                        }
                        if (trim($v['payment_item_tax_debit']) != "") {
                            $tax_debits = $account_model->getAccountByWhere(array('account_number'=>trim($v['payment_item_tax_debit'])));
                            if (!$tax_debits) {
                                $account_model->createAccount(array('account_number'=>trim($v['payment_item_tax_debit'])));
                                $tax_debit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $tax_debit = $tax_debits->account_id;
                            }
                        }
                        
                        

                        $data_item = array(
                            'payment' => $id_payment,
                            'payment_item_invoice' => $v['payment_item_invoice'],
                            'payment_item_customer' => trim($v['payment_item_customer']),
                            'payment_item_receivable_money' => str_replace(',', '', $v['payment_item_receivable_money']),
                            'payment_item_pay_money' => str_replace(',', '', $v['payment_item_pay_money']),
                            'payment_item_money' => str_replace(',', '', $v['payment_item_money']),
                            'payment_item_debit' => $debit,
                            'payment_item_credit' => $credit,
                            'payment_item_comment' => trim($v['payment_item_comment']),
                            'payment_item_tax_percent' => trim($v['payment_item_tax_percent']),
                            'payment_item_tax_debit' => $tax_debit,
                            'payment_item_tax' => str_replace(',', '', $v['payment_item_tax']),
                            'payment_item_invoice_number' => trim($v['payment_item_invoice_number']),
                            'payment_item_invoice_date' => strtotime(str_replace('/','-',$v['payment_item_invoice_date'])),
                            'payment_item_invoice_symbol' => trim($v['payment_item_invoice_symbol']),
                        );

                        $money += $data_item['payment_item_money'];
                        $vat += $data_item['payment_item_tax'];

                        $payment_items = $payment_item_model->getPayment($v['payment_item_id']);
                        if ($payment_items) {
                            $payment_item_model->updatePayment($data_item,array('payment_item_id'=>$payment_items->payment_item_id));
                            $id_payment_item = $payment_items->payment_item_id;

                            $data_additional = array(
                                'payment_item'=>$id_payment_item,
                                'document_number'=>$payments->payment_document_number,
                                'document_date'=>$payments->payment_document_date,
                                'additional_date'=>$payments->payment_additional_date,
                                'additional_comment'=>$data_item['payment_item_comment'],
                                'debit'=>$data_item['payment_item_debit'],
                                'credit'=>$data_item['payment_item_credit'],
                                'money'=>$data_item['payment_item_money'],
                            );
                            $additional_model->updateAdditional($data_additional,array('payment_item'=>$id_payment_item,'debit'=>$payment_items->payment_item_debit));

                            if($payment_items->payment_item_tax_debit > 0 && $data_item['payment_item_tax_debit'] > 0){
                                $data_additional = array(
                                    'payment_item'=>$id_payment_item,
                                    'document_number'=>$payments->payment_document_number,
                                    'document_date'=>$payments->payment_document_date,
                                    'additional_date'=>$payments->payment_additional_date,
                                    'additional_comment'=>$data_item['payment_item_comment'],
                                    'debit'=>$data_item['payment_item_tax_debit'],
                                    'credit'=>$data_item['payment_item_credit'],
                                    'money'=>$data_item['payment_item_tax'],
                                );
                                $additional_model->updateAdditional($data_additional,array('payment_item'=>$id_payment_item,'debit'=>$payment_items->payment_item_tax_debit));

                                $data_invoice = array(
                                    'payment_item'=>$id_payment_item,
                                    'invoice_date'=>$data_item['payment_item_invoice_date'],
                                    'invoice_symbol'=>$data_item['payment_item_invoice_symbol'],
                                    'invoice_number'=>$data_item['payment_item_invoice_number'],
                                    'invoice_customer'=>$data_item['payment_item_customer'],
                                    'invoice_money'=>$data_item['payment_item_money'],
                                    'invoice_tax'=>$data_item['payment_item_tax'],
                                    'invoice_comment'=>$data_item['payment_item_comment'],
                                    'invoice_type'=>1,
                                );
                                $invoice_model->updateInvoice($data_invoice,array('payment_item'=>$id_payment_item));
                            }
                            else if($payment_items->payment_item_tax_debit == 0 && $data_item['payment_item_tax_debit'] > 0){
                                $data_additional = array(
                                    'payment_item'=>$id_payment_item,
                                    'document_number'=>$payments->payment_document_number,
                                    'document_date'=>$payments->payment_document_date,
                                    'additional_date'=>$payments->payment_additional_date,
                                    'additional_comment'=>$data_item['payment_item_comment'],
                                    'debit'=>$data_item['payment_item_tax_debit'],
                                    'credit'=>$data_item['payment_item_credit'],
                                    'money'=>$data_item['payment_item_tax'],
                                );
                                $additional_model->createAdditional($data_additional);

                                $data_invoice = array(
                                    'payment_item'=>$id_payment_item,
                                    'invoice_date'=>$data_item['payment_item_invoice_date'],
                                    'invoice_symbol'=>$data_item['payment_item_invoice_symbol'],
                                    'invoice_number'=>$data_item['payment_item_invoice_number'],
                                    'invoice_customer'=>$data_item['payment_item_customer'],
                                    'invoice_money'=>$data_item['payment_item_money'],
                                    'invoice_tax'=>$data_item['payment_item_tax'],
                                    'invoice_comment'=>$data_item['payment_item_comment'],
                                    'invoice_type'=>1,
                                );
                                $invoice_model->createInvoice($data_invoice);
                            }
                            else{
                                $additional_model->queryAdditional('DELETE FROM additional WHERE payment_item='.$id_payment_item.' AND debit='.$payment_items->payment_item_tax_debit);
                                $invoice_model->queryInvoice('DELETE FROM invoice WHERE payment_item='.$id_payment_item);
                            }

                            $data_debit = array(
                                'payment_item'=>$id_payment_item,
                                'debit_date'=>$payments->payment_document_date,
                                'debit_customer'=>$data_item['payment_item_customer'],
                                'debit_money'=>(0-$data_item['payment_item_money']-$data_item['payment_item_tax']),
                                'debit_comment'=>$data_item['payment_item_comment'],
                                'service_buy'=>$data_item['payment_item_invoice'],
                            );

                            $debit_model->updateDebit($data_debit,array('payment_item'=>$id_payment_item));
                        }
                        else{
                            $payment_item_model->createPayment($data_item);
                            $id_payment_item = $payment_item_model->getLastPayment()->payment_item_id;

                            $data_additional = array(
                                'payment_item'=>$id_payment_item,
                                'document_number'=>$payments->payment_document_number,
                                'document_date'=>$payments->payment_document_date,
                                'additional_date'=>$payments->payment_additional_date,
                                'additional_comment'=>$data_item['payment_item_comment'],
                                'debit'=>$data_item['payment_item_debit'],
                                'credit'=>$data_item['payment_item_credit'],
                                'money'=>$data_item['payment_item_money'],
                            );
                            $additional_model->createAdditional($data_additional);

                            if ($data_item['payment_item_tax_debit']>0) {
                                $data_additional = array(
                                    'payment_item'=>$id_payment_item,
                                    'document_number'=>$payments->payment_document_number,
                                    'document_date'=>$payments->payment_document_date,
                                    'additional_date'=>$payments->payment_additional_date,
                                    'additional_comment'=>$data_item['payment_item_comment'],
                                    'debit'=>$data_item['payment_item_tax_debit'],
                                    'credit'=>$data_item['payment_item_credit'],
                                    'money'=>$data_item['payment_item_tax'],
                                );
                                $additional_model->createAdditional($data_additional);

                                $data_invoice = array(
                                    'payment_item'=>$id_payment_item,
                                    'invoice_date'=>$data_item['payment_item_invoice_date'],
                                    'invoice_symbol'=>$data_item['payment_item_invoice_symbol'],
                                    'invoice_number'=>$data_item['payment_item_invoice_number'],
                                    'invoice_customer'=>$data_item['payment_item_customer'],
                                    'invoice_money'=>$data_item['payment_item_money'],
                                    'invoice_tax'=>$data_item['payment_item_tax'],
                                    'invoice_comment'=>$data_item['payment_item_comment'],
                                    'invoice_type'=>1,
                                );
                                $invoice_model->createInvoice($data_invoice);
                            }

                            $data_debit = array(
                                'payment_item'=>$id_payment_item,
                                'debit_date'=>$payments->payment_document_date,
                                'debit_customer'=>$data_item['payment_item_customer'],
                                'debit_money'=>(0-$data_item['payment_item_money']-$data_item['payment_item_tax']),
                                'debit_comment'=>$data_item['payment_item_comment'],
                                'service_buy'=>$data_item['payment_item_invoice'],
                            );

                            $debit_model->createDebit($data_debit);
                            
                        }

                        if ($arr_item=="") {
                            $arr_item .= $id_payment_item;
                        }
                        else{
                            $arr_item .= ','.$id_payment_item;
                        }
                    }
                }

                $item_olds = $payment_item_model->queryPayment('SELECT * FROM payment_item WHERE payment='.$id_payment.' AND payment_item_id NOT IN ('.$arr_item.')');
                foreach ($item_olds as $item_old) {
                    $additional_model->queryAdditional('DELETE FROM additional WHERE payment_item='.$item_old->payment_item_id);
                    $debit_model->queryDebit('DELETE FROM debit WHERE payment_item='.$item_old->payment_item_id);
                    $payment_item_model->queryPayment('DELETE FROM payment_item WHERE payment_item_id='.$item_old->payment_item_id);
                }
                
                $data_pay = array(
                    'payment_money'=>$money+$vat,
                );
                $payment_model->updatePayment($data_pay,array('payment_id'=>$id_payment));

                if ($data['payment_type'] == 1) {
                    $data_bank = array(
                        'payment'=>$id_payment,
                        'bank_balance_date'=>$payments->payment_document_date,
                        'bank'=>$payments->payment_bank,
                        'bank_balance_money'=>$data_pay['payment_money'],
                    );
                }
                else if ($data['payment_type'] == 2) {
                    $data_bank = array(
                        'payment'=>$id_payment,
                        'bank_balance_date'=>$payments->payment_document_date,
                        'bank'=>$payments->payment_bank,
                        'bank_balance_money'=>(0-$data_pay['payment_money']),
                    );
                }
                

                $bank_balances = $bank_balance_model->getBankByWhere(array('payment'=>$id_payment));
                if ($bank_balances) {
                    $bank_balance_model->updateBank($data_bank,array('bank_balance_id'=>$bank_balances->bank_balance_id));
                }
                else{
                    $bank_balance_model->createBank($data_bank);
                }
            }
                    
        }
    }

    public function getitemadd(){
        if (isset($_POST['payment'])) {
            $account_model = $this->model->get('accountModel');
            $service_buy_model = $this->model->get('servicebuyModel');
            $payment_item_model = $this->model->get('paymentitemModel');
            $customer_model = $this->model->get('customerModel');
            
            $payment_items = $payment_item_model->getAllPayment(array('where'=>'payment='.$_POST['payment']));

            $str = "";
            $str2 = "";
            $i = 1;
            foreach ($payment_items as $item) {
                $customer_name = "";
                $customer_code = "";
                if ($item->payment_item_customer>0) {
                    $customers = $customer_model->getCustomer($item->payment_item_customer);
                    $customer_name = $customers->customer_name;
                    $customer_code = $customers->customer_code;
                }
                
                $debit = $account_model->getAccount($item->payment_item_debit)->account_number;
                $credit = $account_model->getAccount($item->payment_item_credit)->account_number;
                $invoice_id = "";
                $invoice_number = "";
                $invoice_date = "";
                $invoice = "";
                if ($item->payment_item_invoice > 0) {
                    $invoices = $service_buy_model->getService($item->payment_item_invoice);
                    $invoice_id = $invoices->service_buy_id;
                    $invoice_number = $invoices->service_buy_document_number;
                    $invoice_date =  $invoices->service_buy_document_date;
                    $invoice = $invoices->service_buy_number;
                }
                $tax_debit = "";
                if ($item->payment_item_tax_debit > 0) {
                    $tax_debit = $account_model->getAccount($item->payment_item_tax_debit)->account_number;
                }

                $str .= '<tr>';
                $str .= '<td class="width-3">'.$i.'</td>';
                $str .= '<td class="width-7">
                  <input alt="'.$item->payment_item_id.'" data="'.$invoice_id.'" value="'.$invoice_number.'" type="text" name="payment_item_invoice[]" class="payment_item_invoice left" placeholder="Nhập mã" autocomplete="off">
                  <button type="button" class="find_item right" title="Danh mục"><i class="fa fa-search"></i></button>
                </td>';
                $str .= '<td class="width-7"><input value="'.($invoice_date>0?date('d/m/Y',$invoice_date):null).'" disabled type="text" name="payment_item_document_date[]" class="payment_item_document_date" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-7"><input value="'.$invoice.'" disabled type="text" name="payment_item_invoice_number2[]" class="payment_item_invoice_number2" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-7">
                  <input data="'.$item->payment_item_customer.'" value="'.$customer_code.'" type="text" name="payment_item_customer[]" class="payment_item_customer" autocomplete="off">
                  <ul class="name_list_id_3"></ul>
                </td>';
                $str .= '<td><input value="'.$customer_name.'" disabled type="text" name="payment_item_customer_name[]" class="payment_item_customer_name" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-10"><input value="'.$this->lib->formatMoney($item->payment_item_receivable_money).'" disabled type="text" name="payment_item_receivable_money[]" class="payment_item_receivable_money numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-10"><input value="'.$this->lib->formatMoney($item->payment_item_pay_money).'" disabled type="text" name="payment_item_pay_money[]" class="payment_item_pay_money numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-10"><input value="'.$this->lib->formatMoney($item->payment_item_money).'" type="text" name="payment_item_money[]" class="payment_item_money numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td><input value="'.$item->payment_item_comment.'" type="text" name="payment_item_comment[]" class="payment_item_comment" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-5">
                  <input value="'.$debit.'" type="text" name="payment_item_debit[]" class="payment_item_debit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id"></ul>
                </td>';
                $str .= '<td class="width-5">
                  <input value="'.$credit.'" type="text" name="payment_item_credit[]" class="payment_item_credit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_2"></ul>
                </td>';
                $str .= '</tr>';

                $str2 .= '<tr>';
                $str2 .= '<td class="width-3">'.$i.'</td>';
                $str2 .= '<td><input value="'.$customer_name.'" disabled type="text" name="payment_item_customer_name2[]" class="payment_item_customer_name2" autocomplete="off"></td>';
                $str2 .= '<td><input value="'.$item->payment_item_tax_percent.'" type="text" name="payment_item_tax_percent[]" class="payment_item_tax_percent text-right"  autocomplete="off"></td>';
                $str2 .= '<td><input value="'.$this->lib->formatMoney($item->payment_item_tax).'" type="text" name="payment_item_tax[]" class="payment_item_tax numbers text-right" autocomplete="off"></td>';
                
                $str2 .= '<td>
                  <input value="'.$tax_debit.'" type="text" name="payment_item_tax_debit[]" class="payment_item_tax_debit keep-val" autocomplete="off">
                  <ul class="name_list_id_4"></ul>
                </td>';
                $str2 .= '<td><input value="'.$item->payment_item_invoice_number.'" type="text" name="payment_item_invoice_number[]" class="payment_item_invoice_number"  autocomplete="off"></td>';
                $str2 .= '<td><input value="'.($item->payment_item_invoice_date>0?date('d/m/Y',$item->payment_item_invoice_date):null).'" type="text" data-inputmask="\'alias\': \'dd/mm/yyyy\'" data-mask name="payment_item_invoice_date[]" class="payment_item_invoice_date date_mask"  autocomplete="off"></td>';
                $str2 .= '<td><input value="'.$item->payment_item_invoice_symbol.'" type="text" name="payment_item_invoice_symbol[]" class="payment_item_invoice_symbol"  autocomplete="off"></td>';
                $str2 .= '</tr>';

              $i++;
            }

            $arr = array(
                'hang'=>$str,
                'thue'=>$str2,
            );
            echo json_encode($arr);
        }
    }

    public function delete(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $payment_model = $this->model->get('paymentModel');
            $payment_item_model = $this->model->get('paymentitemModel');
            $additional_model = $this->model->get('additionalModel');
            $debit_model = $this->model->get('debitModel');
            $bank_balance_model = $this->model->get('bankbalanceModel');
            $invoice_model = $this->model->get('invoiceModel');
           
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $payments = $payment_model->getPayment($data);
                    $payment_items = $payment_item_model->getAllPayment(array('where'=>'payment='.$data));
                    foreach ($payment_items as $item) {
                        $additional_model->queryAdditional('DELETE FROM additional WHERE payment_item='.$item->payment_item_id);
                        $debit_model->queryDebit('DELETE FROM debit WHERE payment_item='.$item->payment_item_id);
                        $invoice_model->queryInvoice('DELETE FROM invoice WHERE payment_item='.$item->payment_item_id);
                    }
                    $payment_item_model->queryPayment('DELETE FROM payment_item WHERE payment='.$data);
                    $bank_balance_model->queryBank('DELETE FROM bank_balance WHERE payment='.$data);
                       $payment_model->deletePayment($data);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|payment|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    
                    
                }
                return true;
            }
            else{
                    $payments = $payment_model->getPayment($_POST['data']);
                    $payment_items = $payment_item_model->getAllPayment(array('where'=>'payment='.$_POST['data']));
                    foreach ($payment_items as $item) {
                        $additional_model->queryAdditional('DELETE FROM additional WHERE payment_item='.$item->payment_item_id);
                        $debit_model->queryDebit('DELETE FROM debit WHERE payment_item='.$item->payment_item_id);
                        $invoice_model->queryInvoice('DELETE FROM invoice WHERE payment_item='.$item->payment_item_id);
                    }
                    $payment_item_model->queryPayment('DELETE FROM payment_item WHERE payment='.$_POST['data']);
                    $bank_balance_model->queryBank('DELETE FROM bank_balance WHERE payment='.$_POST['data']);
                        $payment_model->deletePayment($_POST['data']);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|payment|"."\n"."\r\n";
                        
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