<?php
Class servicebuyController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->servicebuy) || json_decode($_SESSION['user_permission_action'])->servicebuy != "servicebuy") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Hóa đơn mua hàng dịch vụ';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'service_buy_document_date';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
        }
        
        $service_buy_model = $this->model->get('servicebuyModel');

        $join = array('table'=>'customer','where'=>'service_buy_customer=customer_id');

        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => '1=1',
        );
        
        
        $tongsodong = count($service_buy_model->getAllService($data,$join));
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
            $search = '( service_buy_number LIKE "%'.$keyword.'%" 
                    OR customer_name LIKE "%'.$keyword.'%" 
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        

        
        $this->view->data['service_buys'] = $service_buy_model->getAllService($data,$join);
        $this->view->data['lastID'] = isset($service_buy_model->getLastService()->service_buy_document_number)?$service_buy_model->getLastService()->service_buy_document_number:0;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('servicebuy/index');
    }

   public function getItem(){
        $items_model = $this->model->get('itemsModel');

        $rowIndex = $_POST['rowIndex'];

        $items = $items_model->getAllItems(array('where'=>'items_type=2','order_by'=>'items_code ASC, items_name ASC'));
        
        $str = '<table class="table_data" id="tblExport2">';
        $str .= '<thead><tr><th class="fix"><input type="checkbox" onclick="checkall(\'checkbox2\', this)" name="checkall"/></th><th class="fix">Mã dịch vụ</th><th class="fix">Tên dịch vụ</th><th class="fix">ĐVT</th><th class="fix">Thuế suất</th></tr></thead>';
        $str .= '<tbody>';

        foreach ($items as $item) {
            $str .= '<tr class="tr" data="'.$item->items_id.'"><td><input name="check_i[]" type="checkbox" class="checkbox2" value="'.$item->items_id.'" data="'.$rowIndex.'" data-code="'.$item->items_code.'" data-name="'.$item->items_name.'" data-unit="'.$item->items_unit.'" data-tax="'.$item->items_tax.'" ></td><td class="fix">'.$item->items_code.'</td><td class="fix">'.$item->items_name.'</td><td class="fix">'.$item->items_unit.'</td><td class="fix">'.$item->items_tax.'</td></tr>';
        }
        
        $str .= '</tbody></table>';
        echo $str;
   }
   public function getItemName(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $items_model = $this->model->get('itemsModel');
            if ($_POST['keyword'] == "*") {
                $list = $items_model->getAllItems();
            }
            else{
                $data = array(
                'where'=>'( items_code LIKE "%'.$_POST['keyword'].'%" OR items_name LIKE "%'.$_POST['keyword'].'%" ) AND items_type=2',
                );
                $list = $items_model->getAllItems($data);
            }
            foreach ($list as $rs) {
                $items_name = $rs->items_code.' | '.$rs->items_name;
                if ($_POST['keyword'] != "*") {
                    $items_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->items_code.' | '.$rs->items_name);
                }
                echo '<li onclick="set_item(\''.$rs->items_id.'\',\''.$rs->items_code.'\',\''.$rs->items_name.'\',\''.$rs->items_unit.'\',\''.$rs->items_tax.'\',\''.$_POST['offset'].'\')">'.$items_name.'</li>';
            }
        }
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
                'where'=>'( customer_code LIKE "%'.$_POST['keyword'].'%" OR customer_name LIKE "%'.$_POST['keyword'].'%" ) AND type_customer=2',
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
            
            $service_buy_model = $this->model->get('servicebuyModel');
            $service_buy_item_model = $this->model->get('servicebuyitemModel');
            $additional_model = $this->model->get('additionalModel');
            $debit_model = $this->model->get('debitModel');
            $account_model = $this->model->get('accountModel');
            $invoice_model = $this->model->get('invoiceModel');

            $items = $_POST['item'];

            $data = array(
                        
                        'service_buy_document_date' => strtotime(str_replace('/','-',$_POST['service_buy_document_date'])),
                        'service_buy_document_number' => trim($_POST['service_buy_document_number']),
                        'service_buy_additional_date' => strtotime(str_replace('/','-',$_POST['service_buy_additional_date'])),
                        'service_buy_customer' => trim($_POST['service_buy_customer']),
                        'service_buy_number' => trim($_POST['service_buy_number']),
                        'service_buy_date' => strtotime(str_replace('/','-',$_POST['service_buy_date'])),
                        'service_buy_symbol' => trim($_POST['service_buy_symbol']),
                        'service_buy_bill_number' => trim($_POST['service_buy_bill_number']),
                        'service_buy_contract_number' => trim($_POST['service_buy_contract_number']),
                        'service_buy_money_type' => trim($_POST['service_buy_money_type']),
                        'service_buy_money_rate' => str_replace(',','',trim($_POST['service_buy_money_rate'])),
                        'service_buy_origin_doc' => trim($_POST['service_buy_origin_doc']),
                        'service_buy_comment' => trim($_POST['service_buy_comment']),
                        'service_buy_type' => trim($_POST['service_buy_type']),
                        );
            

            if ($_POST['yes'] != "") {
                $check = $service_buy_model->queryService('SELECT * FROM service_buy WHERE (service_buy_document_number='.$data['service_buy_document_number'].') AND service_buy_id!='.$_POST['yes']);
                if($check){
                    echo "Chứng từ này đã tồn tại";
                    return false;
                }
                else{
                    $service_buy_model->updateService($data,array('service_buy_id' => trim($_POST['yes'])));
                    echo "Cập nhật thành công";

                    $id_service_buy = $_POST['yes'];

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|service_buy|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                
            }
            else{
                $check = $service_buy_model->queryService('SELECT * FROM service_buy WHERE (service_buy_document_number='.$data['service_buy_document_number'].')');
                if($check){
                    echo "Chứng từ này đã tồn tại";
                    return false;
                }
                else{
                    $data['service_buy_create_user'] = $_SESSION['userid_logined'];
                    $data['service_buy_create_date'] = strtotime(date('d-m-Y'));

                    $service_buy_model->createService($data);
                    echo "Thêm thành công";

                $id_service_buy = $service_buy_model->getLastService()->service_buy_id;

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                    $filename = "action_logs.txt";
                    $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$service_buy_model->getLastService()->service_buy_id."|service_buy|".implode("-",$data)."\n"."\r\n";
                    
                    $fh = fopen($filename, "a") or die("Could not open log file.");
                    fwrite($fh, $text) or die("Could not write file!");
                    fclose($fh);
                }
                    
                
            }

            if (isset($id_service_buy)) {
                $service_buys = $service_buy_model->getService($id_service_buy);

                $money = 0;
                $money_foreign = 0;
                $tax = 0;

                $arr_item = "";
                foreach ($items as $v) {
                    if($v['service_buy_item'] != ""){
                        if ($arr_item=="") {
                            $arr_item .= $v['service_buy_item'];
                        }
                        else{
                            $arr_item .= ','.$v['service_buy_item'];
                        }

                        $debit = 0;
                        $credit = 0;
                        $tax_debit = 0;
                        if (trim($v['service_buy_item_debit']) != "") {
                            $debits = $account_model->getAccountByWhere(array('account_number'=>trim($v['service_buy_item_debit'])));
                            if (!$debits) {
                                $account_model->createAccount(array('account_number'=>trim($v['service_buy_item_debit'])));
                                $debit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $debit = $debits->account_id;
                            }
                        }
                        if (trim($v['service_buy_item_credit']) != "") {
                            $credits = $account_model->getAccountByWhere(array('account_number'=>trim($v['service_buy_item_credit'])));
                            if (!$credits) {
                                $account_model->createAccount(array('account_number'=>trim($v['service_buy_item_credit'])));
                                $credit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $credit = $credits->account_id;
                            }
                        }
                        if (trim($v['service_buy_item_tax_vat_debit']) != "") {
                            $tax_debits = $account_model->getAccountByWhere(array('account_number'=>trim($v['service_buy_item_tax_vat_debit'])));
                            if (!$tax_debits) {
                                $account_model->createAccount(array('account_number'=>trim($v['service_buy_item_tax_vat_debit'])));
                                $tax_debit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $tax_debit = $tax_debits->account_id;
                            }
                        }
                        
                        

                        $data_item = array(
                            'service_buy' => $id_service_buy,
                            'service_buy_item' => $v['service_buy_item'],
                            'service_buy_item_unit' => trim($v['service_buy_item_unit']),
                            'service_buy_item_number' => trim($v['service_buy_item_number']),
                            'service_buy_item_price' => str_replace(',', '', $v['service_buy_item_price']),
                            'service_buy_item_money' => str_replace(',', '', $v['service_buy_item_money']),
                            'service_buy_item_debit' => $debit,
                            'service_buy_item_credit' => $credit,
                            'service_buy_item_tax_vat_percent' => trim($v['service_buy_item_tax_vat_percent']),
                            'service_buy_item_tax_vat' => str_replace(',', '', $v['service_buy_item_tax_vat']),
                            'service_buy_item_tax_vat_debit' => $tax_debit,
                            'service_buy_item_total' => str_replace(',', '', $v['service_buy_item_total']),
                        );

                        $money_foreign += $data_item['service_buy_item_price']*$data_item['service_buy_item_number'];
                        $money += $data_item['service_buy_item_money'];
                        $tax += $data_item['service_buy_item_tax_vat'];

                        $service_buy_items = $service_buy_item_model->getServiceByWhere(array('service_buy'=>$data_item['service_buy'],'service_buy_item'=>$data_item['service_buy_item']));
                        if ($service_buy_items) {
                            $service_buy_item_model->updateService($data_item,array('service_buy_item_id'=>$service_buy_items->service_buy_item_id));
                            $id_service_buy_item = $service_buy_items->service_buy_item_id;

                            if ($service_buys->service_buy_type==1 && $data['service_buy_type'] != 1) {
                                $data_additional = array(
                                    'service_buy_item'=>$id_service_buy_item,
                                    'document_number'=>$service_buys->service_buy_document_number,
                                    'document_date'=>$service_buys->service_buy_document_date,
                                    'additional_date'=>$service_buys->service_buy_additional_date,
                                    'additional_comment'=>$service_buys->service_buy_comment,
                                    'debit'=>$data_item['service_buy_item_debit'],
                                    'credit'=>$data_item['service_buy_item_credit'],
                                    'money'=>$data_item['service_buy_item_money'],
                                );
                                $additional_model->createAdditional($data_additional);
                            }
                            else if ($service_buys->service_buy_type!=1 && $data['service_buy_type'] == 1) {
                                $additional_model->queryAdditional('DELETE FROM additional WHERE service_buy_item='.$id_service_buy_item.' AND debit='.$service_buy_items->service_buy_item_debit);
                            }
                            else {
                                $data_additional = array(
                                    'service_buy_item'=>$id_service_buy_item,
                                    'document_number'=>$service_buys->service_buy_document_number,
                                    'document_date'=>$service_buys->service_buy_document_date,
                                    'additional_date'=>$service_buys->service_buy_additional_date,
                                    'additional_comment'=>$service_buys->service_buy_comment,
                                    'debit'=>$data_item['service_buy_item_debit'],
                                    'credit'=>$data_item['service_buy_item_credit'],
                                    'money'=>$data_item['service_buy_item_money'],
                                );
                                $additional_model->updateAdditional($data_additional,array('service_buy_item'=>$id_service_buy_item,'debit'=>$service_buy_items->service_buy_item_debit));
                            }
                            

                            if($service_buy_items->service_buy_item_tax_vat_debit > 0 && $data_item['service_buy_item_tax_vat_debit'] > 0){
                                $data_additional = array(
                                    'service_buy_item'=>$id_service_buy_item,
                                    'document_number'=>$service_buys->service_buy_document_number,
                                    'document_date'=>$service_buys->service_buy_document_date,
                                    'additional_date'=>$service_buys->service_buy_additional_date,
                                    'additional_comment'=>$service_buys->service_buy_comment,
                                    'debit'=>$data_item['service_buy_item_tax_vat_debit'],
                                    'credit'=>$data_item['service_buy_item_credit'],
                                    'money'=>$data_item['service_buy_item_tax_vat'],
                                );
                                $additional_model->updateAdditional($data_additional,array('service_buy_item'=>$id_service_buy_item,'debit'=>$service_buy_items->service_buy_item_tax_vat_debit));
                            }
                            else if($service_buy_items->service_buy_item_tax_vat_debit == 0 && $data_item['service_buy_item_tax_vat_debit'] > 0){
                                $data_additional = array(
                                    'service_buy_item'=>$id_service_buy_item,
                                    'document_number'=>$service_buys->service_buy_document_number,
                                    'document_date'=>$service_buys->service_buy_document_date,
                                    'additional_date'=>$service_buys->service_buy_additional_date,
                                    'additional_comment'=>$service_buys->service_buy_comment,
                                    'debit'=>$data_item['service_buy_item_tax_vat_debit'],
                                    'credit'=>$data_item['service_buy_item_credit'],
                                    'money'=>$data_item['service_buy_item_tax_vat'],
                                );
                                $additional_model->createAdditional($data_additional);
                            }
                            else{
                                $additional_model->queryAdditional('DELETE FROM additional WHERE service_buy_item='.$id_service_buy_item.' AND debit='.$service_buy_items->service_buy_item_tax_vat_debit);
                            }
                        }
                        else{
                            $service_buy_item_model->createService($data_item);
                            $id_service_buy_item = $service_buy_item_model->getLastService()->service_buy_item_id;

                            if ($data['service_buy_type'] != 1) {
                                $data_additional = array(
                                    'service_buy_item'=>$id_service_buy_item,
                                    'document_number'=>$service_buys->service_buy_document_number,
                                    'document_date'=>$service_buys->service_buy_document_date,
                                    'additional_date'=>$service_buys->service_buy_additional_date,
                                    'additional_comment'=>$service_buys->service_buy_comment,
                                    'debit'=>$data_item['service_buy_item_debit'],
                                    'credit'=>$data_item['service_buy_item_credit'],
                                    'money'=>$data_item['service_buy_item_money'],
                                );
                                $additional_model->createAdditional($data_additional);
                            }

                            if($data_item['service_buy_item_tax_vat_debit'] > 0){
                                $data_additional = array(
                                    'service_buy_item'=>$id_service_buy_item,
                                    'document_number'=>$service_buys->service_buy_document_number,
                                    'document_date'=>$service_buys->service_buy_document_date,
                                    'additional_date'=>$service_buys->service_buy_additional_date,
                                    'additional_comment'=>$service_buys->service_buy_comment,
                                    'debit'=>$data_item['service_buy_item_tax_vat_debit'],
                                    'credit'=>$data_item['service_buy_item_credit'],
                                    'money'=>$data_item['service_buy_item_tax_vat'],
                                );
                                $additional_model->createAdditional($data_additional);
                            }
                            
                        }

                        
                    }
                }

                $item_olds = $service_buy_item_model->queryService('SELECT * FROM service_buy_item WHERE service_buy='.$id_service_buy.' AND service_buy_item NOT IN ('.$arr_item.')');
                foreach ($item_olds as $item_old) {
                    $additional_model->queryAdditional('DELETE FROM additional WHERE service_buy_item='.$item_old->service_buy_item_id);
                    $service_buy_item_model->queryService('DELETE FROM service_buy_item WHERE service_buy_item_id='.$item_old->service_buy_item_id);
                }
                
                $data_buy = array(
                    'service_buy_money'=>$money,
                    'service_buy_money_foreign'=>$money_foreign,
                    'service_buy_tax_vat'=>$tax,
                    'service_buy_total'=>($money+$tax),
                );
                $service_buy_model->updateService($data_buy,array('service_buy_id'=>$id_service_buy));

                $data_debit = array(
                    'service_buy'=>$id_service_buy,
                    'debit_date'=>$service_buys->service_buy_document_date,
                    'debit_customer'=>$service_buys->service_buy_customer,
                    'debit_money'=>($money+$tax),
                    'debit_comment'=>$service_buys->service_buy_comment,
                );

                if ($data['service_buy_money_rate'] > 1) {
                    $data_debit['debit_money_foreign'] = $money_foreign;
                }

                $service_buy_debits = $debit_model->getDebitByWhere(array('service_buy'=>$id_service_buy,'debit_money'=>$service_buys->service_buy_total));
                if ($service_buy_debits) {
                    $debit_model->updateDebit($data_debit,array('debit_id'=>$service_buy_debits->debit_id));
                }
                else{
                    $debit_model->createDebit($data_debit);
                }

                $data_invoice = array(
                    'service_buy'=>$id_service_buy,
                    'invoice_symbol'=>$service_buys->service_buy_symbol,
                    'invoice_date'=>$service_buys->service_buy_date,
                    'invoice_number'=>$service_buys->service_buy_number,
                    'invoice_customer'=>$service_buys->service_buy_customer,
                    'invoice_money'=>$money,
                    'invoice_tax'=>$tax,
                    'invoice_comment'=>$service_buys->service_buy_comment,
                    'invoice_type'=>1,
                );

                $service_buy_invoices = $invoice_model->getInvoiceByWhere(array('service_buy'=>$id_service_buy));
                if ($service_buy_invoices) {
                    $invoice_model->updateInvoice($data_invoice,array('invoice_id'=>$service_buy_invoices->invoice_id));
                }
                else{
                    $invoice_model->createInvoice($data_invoice);
                }
            }
                    
        }
    }

    public function getitemadd(){
        if (isset($_POST['service_buy'])) {
            $account_model = $this->model->get('accountModel');
            $service_buy_item_model = $this->model->get('servicebuyitemModel');
            $join = array('table'=>'items','where'=>'service_buy_item=items_id');
            $service_buy_items = $service_buy_item_model->getAllService(array('where'=>'service_buy='.$_POST['service_buy']),$join);

            $str = "";
            $str2 = "";
            $i = 1;
            foreach ($service_buy_items as $item) {
                $debit = $account_model->getAccount($item->service_buy_item_debit)->account_number;
                $credit = $account_model->getAccount($item->service_buy_item_credit)->account_number;
                $tax_debit = "";
                if ($item->service_buy_item_tax_vat_debit > 0) {
                    $tax_debit = $account_model->getAccount($item->service_buy_item_tax_vat_debit)->account_number;
                }
                

                $str .= '<tr>';
                $str .= '<td class="width-3">'.$i.'</td>';
                $str .= '<td class="width-10">
                  <input data="'.$item->service_buy_item.'" value="'.$item->items_code.'" type="text" name="service_buy_item[]" class="service_buy_item left" required="required" placeholder="Nhập mã hoặc tên" autocomplete="off">
                  <button type="button" class="find_item right" title="Danh mục"><i class="fa fa-search"></i></button>
                </td>';
                $str .= '<td>
                  <input value="'.$item->items_name.'" type="text" name="service_buy_item_name[]" class="service_buy_item_name" required="required" autocomplete="off">
                  <ul class="name_list_id"></ul>
                </td>';
                $str .= '<td class="width-7">
                  <input value="'.$debit.'" type="text" name="service_buy_item_debit[]" class="service_buy_item_debit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_2"></ul>
                </td>';
                $str .= '<td class="width-7">
                  <input value="'.$credit.'" type="text" name="service_buy_item_credit[]" class="service_buy_item_credit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_3"></ul>
                </td>';
                $str .= '<td class="width-7"><input value="'.$item->service_buy_item_unit.'" type="text" name="service_buy_item_unit[]" class="service_buy_item_unit" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-7"><input value="'.$item->service_buy_item_number.'" type="text" name="service_buy_item_number[]" class="service_buy_item_number text-right" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-10"><input value="'.$this->lib->formatMoney($item->service_buy_item_price).'" type="text" name="service_buy_item_price[]" class="service_buy_item_price numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td><input value="'.$this->lib->formatMoney($item->service_buy_item_money).'" type="text" name="service_buy_item_money[]" class="service_buy_item_money numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td><input value="'.$this->lib->formatMoney($item->service_buy_item_total).'" disabled type="text" name="service_buy_item_total[]" class="service_buy_item_total numbers text-right" required="required" autocomplete="off"></td>';
              $str .= '</tr>';

              $str2 .= '<tr>';
                $str2 .= '<td class="width-3">'.$i.'</td>';
                $str2 .= '<td class="width-10"><input value="'.$item->items_code.'" type="text" name="service_buy_item2[]" class="service_buy_item2" disabled required="required" autocomplete="off"></td>';
                $str2 .= '<td><input value="'.$item->items_name.'" type="text" name="service_buy_item_name2[]" class="service_buy_item_name2" disabled required="required" autocomplete="off"></td>';
                $str2 .= '<td><input value="'.$item->service_buy_item_tax_vat_percent.'" type="text" name="service_buy_item_tax_vat_percent[]" class="service_buy_item_tax_vat_percent text-right" autocomplete="off"></td>';
                $str2 .= '<td><input value="'.$this->lib->formatMoney($item->service_buy_item_tax_vat).'" type="text" name="service_buy_item_tax_vat[]" class="service_buy_item_tax_vat numbers text-right" autocomplete="off"></td>';
                $str2 .= '<td>
                  <input value="'.$tax_debit.'" type="text" name="service_buy_item_tax_vat_debit[]" class="service_buy_item_tax_vat_debit" autocomplete="off">
                  <ul class="name_list_id_4"></ul>
                </td>';
                
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
            $service_buy_model = $this->model->get('servicebuyModel');
            $service_buy_item_model = $this->model->get('servicebuyitemModel');
            $additional_model = $this->model->get('additionalModel');
            $debit_model = $this->model->get('debitModel');
            $invoice_model = $this->model->get('invoiceModel');
           
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $service_buys = $service_buy_model->getService($data);
                    $service_buy_items = $service_buy_item_model->getAllService(array('where'=>'service_buy='.$data));
                    foreach ($service_buy_items as $item) {
                        $additional_model->queryAdditional('DELETE FROM additional WHERE service_buy_item='.$item->service_buy_item_id);
                    }
                    $service_buy_item_model->queryService('DELETE FROM service_buy_item WHERE service_buy='.$data);
                    $debit_model->queryDebit('DELETE FROM debit WHERE service_buy='.$data.' AND debit_money='.$service_buys->service_buy_total);
                    $invoice_model->queryInvoice('DELETE FROM invoice WHERE service_buy='.$data);
                       $service_buy_model->deleteService($data);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|service_buy|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    
                    
                }
                return true;
            }
            else{
                    $service_buys = $service_buy_model->getService($_POST['data']);
                    $service_buy_items = $service_buy_item_model->getAllService(array('where'=>'service_buy='.$_POST['data']));
                    foreach ($service_buy_items as $item) {
                        $additional_model->queryAdditional('DELETE FROM additional WHERE service_buy_item='.$item->service_buy_item_id);
                    }
                    $service_buy_item_model->queryService('DELETE FROM service_buy_item WHERE service_buy='.$_POST['data']);
                    $debit_model->queryDebit('DELETE FROM debit WHERE service_buy='.$_POST['data'].' AND debit_money='.$service_buys->service_buy_total);
                    $invoice_model->queryInvoice('DELETE FROM invoice WHERE service_buy='.$_POST['data']);
                        $service_buy_model->deleteService($_POST['data']);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|service_buy|"."\n"."\r\n";
                        
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