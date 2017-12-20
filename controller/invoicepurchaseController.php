<?php
Class invoicepurchaseController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->invoicepurchase) || json_decode($_SESSION['user_permission_action'])->invoicepurchase != "invoicepurchase") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Hóa đơn mua hàng';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'invoice_purchase_document_date';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
        }
        
        $invoice_purchase_model = $this->model->get('invoicepurchaseModel');

        $join = array('table'=>'customer','where'=>'invoice_purchase_customer=customer_id');

        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => '1=1',
        );
        
        
        $tongsodong = count($invoice_purchase_model->getAllInvoice($data,$join));
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
            $search = '( invoice_purchase_number LIKE "%'.$keyword.'%" 
                    OR customer_name LIKE "%'.$keyword.'%" 
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        

        
        $this->view->data['invoice_purchases'] = $invoice_purchase_model->getAllInvoice($data,$join);
        $this->view->data['lastID'] = isset($invoice_purchase_model->getLastInvoice()->invoice_purchase_document_number)?$invoice_purchase_model->getLastInvoice()->invoice_purchase_document_number:0;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('invoicepurchase/index');
    }

   public function getItem(){
        $items_model = $this->model->get('itemsModel');

        $rowIndex = $_POST['rowIndex'];

        $items = $items_model->getAllItems(array('where'=>'items_type=1','order_by'=>'items_code ASC, items_name ASC'));
        
        $str = '<table class="table_data" id="tblExport2">';
        $str .= '<thead><tr><th class="fix"><input type="checkbox" onclick="checkall(\'checkbox2\', this)" name="checkall"/></th><th class="fix">Mã hàng hóa</th><th class="fix">Tên hàng hóa</th><th class="fix">ĐVT</th><th class="fix">Thuế suất</th><th class="fix">Hệ số</th></tr></thead>';
        $str .= '<tbody>';

        foreach ($items as $item) {
            $str .= '<tr class="tr" data="'.$item->items_id.'"><td><input name="check_i[]" type="checkbox" class="checkbox2" value="'.$item->items_id.'" data="'.$rowIndex.'" data-stuff="'.$item->items_stuff.'" data-code="'.$item->items_code.'" data-name="'.$item->items_name.'" data-unit="'.$item->items_unit.'" data-tax="'.$item->items_tax.'" ></td><td class="fix">'.$item->items_code.'</td><td class="fix">'.$item->items_name.'</td><td class="fix">'.$item->items_unit.'</td><td class="fix">'.$item->items_tax.'</td><td class="fix">'.$item->items_stuff.'</td></tr>';
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
                'where'=>'( items_code LIKE "%'.$_POST['keyword'].'%" OR items_name LIKE "%'.$_POST['keyword'].'%" ) AND items_type=1',
                );
                $list = $items_model->getAllItems($data);
            }
            foreach ($list as $rs) {
                $items_name = $rs->items_code.' | '.$rs->items_name;
                if ($_POST['keyword'] != "*") {
                    $items_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->items_code.' | '.$rs->items_name);
                }
                echo '<li onclick="set_item(\''.$rs->items_id.'\',\''.$rs->items_code.'\',\''.$rs->items_name.'\',\''.$rs->items_unit.'\',\''.$rs->items_tax.'\',\''.$rs->items_stuff.'\',\''.$_POST['offset'].'\')">'.$items_name.'</li>';
            }
        }
    }

    public function getHouse(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $house_model = $this->model->get('houseModel');
            if ($_POST['keyword'] == "*") {
                $list = $house_model->getAllHouse();
            }
            else{
                $data = array(
                'where'=>'( house_code LIKE "%'.$_POST['keyword'].'%" OR house_name LIKE "%'.$_POST['keyword'].'%" )',
                );
                $list = $house_model->getAllHouse($data);
            }
            foreach ($list as $rs) {
                $house_name = $rs->house_code.' | '.$rs->house_name;
                if ($_POST['keyword'] != "*") {
                    $house_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->house_code.' | '.$rs->house_name);
                }
                echo '<li onclick="set_item_house(\''.$rs->house_id.'\',\''.$rs->house_code.'\',\''.$rs->house_name.'\',\''.$_POST['offset'].'\')">'.$house_name.'</li>';
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
    public function getServicebuy(){
        $service_buy_model = $this->model->get('servicebuyModel');
        $invoice_service_buy_model = $this->model->get('invoiceservicebuyModel');

        $qr = 'SELECT a.*, d.customer_name FROM service_buy a, customer d WHERE a.service_buy_customer=d.customer_id AND ( a.service_buy_id NOT IN (SELECT b.service_buy FROM invoice_service_buy b) OR a.service_buy_money > (SELECT SUM(c.invoice_service_buy_money) FROM invoice_service_buy c WHERE c.service_buy=a.service_buy_id GROUP BY c.service_buy) )';

        $services = $service_buy_model->queryService($qr);
        
        $str = '<table class="table_data" id="tblExport3">';
        $str .= '<thead><tr><th class="fix"><input type="checkbox" onclick="checkall(\'checkbox3\', this)" name="checkall"/></th><th class="fix">Số CT</th><th class="fix">Ngày CT</th><th class="fix">NCC</th><th class="fix">Diễn giải</th><th class="fix">Tổng tiền</th><th class="fix">Số tiền phân bổ</th></tr></thead>';
        $str .= '<tbody>';

        foreach ($services as $service) {
            $invoice_services = $invoice_service_buy_model->getAllInvoice(array('where'=>'service_buy='.$service->service_buy_id));
            $used=0;
            foreach ($invoice_services as $invoice_service) {
                $used += $invoice_service->invoice_service_buy_money;
            }
            $money = $service->service_buy_money-$used;
            $str .= '<tr class="tr2" data="'.$service->service_buy_id.'"><td><input name="check_i2[]" type="checkbox" class="checkbox3" value="'.$service->service_buy_id.'" data-doc="'.$service->service_buy_document_number.'" data-com="'.$service->service_buy_comment.'" data-money="'.$this->lib->formatMoney($money).'" ></td><td class="fix">'.$service->service_buy_document_number.'</td><td class="fix">'.$this->lib->hien_thi_ngay_thang($service->service_buy_document_date).'</td><td class="fix">'.$service->customer_name.'</td><td class="fix">'.$service->service_buy_comment.'</td><td class="fix">'.$this->lib->formatMoney($money).'</td><td class="fix"><input style="width:120px" type="text" name="money_add[]" class="money_add numbers" value="'.$this->lib->formatMoney($money).'" max="'.$money.'"></td></tr>';
        }
        
        $str .= '</tbody></table>';
        echo $str;
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
            
            $invoice_purchase_model = $this->model->get('invoicepurchaseModel');
            $invoice_purchase_item_model = $this->model->get('invoicepurchaseitemModel');
            $additional_model = $this->model->get('additionalModel');
            $debit_model = $this->model->get('debitModel');
            $account_model = $this->model->get('accountModel');
            $invoice_model = $this->model->get('invoiceModel');
            $house_model = $this->model->get('houseModel');
            $stock_model = $this->model->get('stockModel');
            $invoice_service_buy_model = $this->model->get('invoiceservicebuyModel');

            $items = $_POST['item'];
            $service_buys = $_POST['service_buy'];

            $data = array(
                        
                        'invoice_purchase_document_date' => strtotime(str_replace('/','-',$_POST['invoice_purchase_document_date'])),
                        'invoice_purchase_document_number' => trim($_POST['invoice_purchase_document_number']),
                        'invoice_purchase_additional_date' => strtotime(str_replace('/','-',$_POST['invoice_purchase_additional_date'])),
                        'invoice_purchase_customer' => trim($_POST['invoice_purchase_customer']),
                        'invoice_purchase_number' => trim($_POST['invoice_purchase_number']),
                        'invoice_purchase_date' => strtotime(str_replace('/','-',$_POST['invoice_purchase_date'])),
                        'invoice_purchase_symbol' => trim($_POST['invoice_purchase_symbol']),
                        'invoice_purchase_bill_number' => trim($_POST['invoice_purchase_bill_number']),
                        'invoice_purchase_contract_number' => trim($_POST['invoice_purchase_contract_number']),
                        'invoice_purchase_money_type' => trim($_POST['invoice_purchase_money_type']),
                        'invoice_purchase_money_rate' => str_replace(',','',trim($_POST['invoice_purchase_money_rate'])),
                        'invoice_purchase_origin_doc' => trim($_POST['invoice_purchase_origin_doc']),
                        'invoice_purchase_comment' => trim($_POST['invoice_purchase_comment']),
                        'invoice_purchase_allocation' => trim($_POST['invoice_purchase_allocation']),
                        );
            

            if ($_POST['yes'] != "") {
                $check = $invoice_purchase_model->queryInvoice('SELECT * FROM invoice_purchase WHERE (invoice_purchase_document_number='.$data['invoice_purchase_document_number'].') AND invoice_purchase_id!='.$_POST['yes']);
                if($check){
                    echo "Chứng từ này đã tồn tại";
                    return false;
                }
                else{
                    $invoice_purchase_model->updateInvoice($data,array('invoice_purchase_id' => trim($_POST['yes'])));
                    echo "Cập nhật thành công";

                    $id_invoice_purchase = $_POST['yes'];

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|invoice_purchase|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                
            }
            else{
                $check = $invoice_purchase_model->queryInvoice('SELECT * FROM invoice_purchase WHERE (invoice_purchase_document_number='.$data['invoice_purchase_document_number'].')');
                if($check){
                    echo "Chứng từ này đã tồn tại";
                    return false;
                }
                else{
                    $data['invoice_purchase_create_user'] = $_SESSION['userid_logined'];
                    $data['invoice_purchase_create_date'] = strtotime(date('d-m-Y'));

                    $invoice_purchase_model->createInvoice($data);
                    echo "Thêm thành công";

                $id_invoice_purchase = $invoice_purchase_model->getLastInvoice()->invoice_purchase_id;

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                    $filename = "action_logs.txt";
                    $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$invoice_purchase_model->getLastInvoice()->invoice_purchase_id."|invoice_purchase|".implode("-",$data)."\n"."\r\n";
                    
                    $fh = fopen($filename, "a") or die("Could not open log file.");
                    fwrite($fh, $text) or die("Could not write file!");
                    fclose($fh);
                }
                    
                
            }

            if (isset($id_invoice_purchase)) {
                $invoice_purchases = $invoice_purchase_model->getInvoice($id_invoice_purchase);

                $money = 0;
                $tax = 0;
                $cost = 0;
                $number = 0;

                $arr_item = "";
                $arr_service = "";

                foreach ($service_buys as $s) {
                    if($s['service_buy_id'] != ""){
                        if ($arr_service=="") {
                            $arr_service .= $s['service_buy_id'];
                        }
                        else{
                            $arr_service .= ','.$s['service_buy_id'];
                        }

                        $data_service = array(
                            'invoice_purchase'=>$id_invoice_purchase,
                            'service_buy'=>$s['service_buy_id'],
                            'invoice_service_buy_money'=>str_replace(',', '', $s['service_buy_total']),
                        );

                        $invoice_service_buys = $invoice_service_buy_model->getInvoiceByWhere(array('invoice_purchase'=>$data_service['invoice_purchase'],'service_buy'=>$data_service['service_buy']));
                        if ($invoice_service_buys) {
                            $invoice_service_buy_model->updateInvoice($data_service,array('invoice_service_buy_id'=>$invoice_service_buys->invoice_service_buy_id));
                        }
                        else{
                            $invoice_service_buy_model->createInvoice($data_service);
                        }
                    }
                }
                $service_olds = $invoice_service_buy_model->queryInvoice('SELECT * FROM invoice_service_buy WHERE invoice_purchase='.$id_invoice_purchase.' AND service_buy NOT IN ('.$arr_service.')');
                foreach ($service_olds as $service_olds) {
                    $invoice_service_buy_model->queryInvoice('DELETE FROM invoice_service_buy WHERE invoice_service_buy_id='.$service_olds->invoice_service_buy_id);
                }

                foreach ($items as $v) {
                    if($v['invoice_purchase_item'] != ""){
                        if ($arr_item=="") {
                            $arr_item .= $v['invoice_purchase_item'];
                        }
                        else{
                            $arr_item .= ','.$v['invoice_purchase_item'];
                        }

                        $debit = 0;
                        $credit = 0;
                        $tax_debit = 0;
                        $house = 0;
                        if (trim($v['invoice_purchase_item_debit']) != "") {
                            $debits = $account_model->getAccountByWhere(array('account_number'=>trim($v['invoice_purchase_item_debit'])));
                            if (!$debits) {
                                $account_model->createAccount(array('account_number'=>trim($v['invoice_purchase_item_debit'])));
                                $debit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $debit = $debits->account_id;
                            }
                        }
                        if (trim($v['invoice_purchase_item_credit']) != "") {
                            $credits = $account_model->getAccountByWhere(array('account_number'=>trim($v['invoice_purchase_item_credit'])));
                            if (!$credits) {
                                $account_model->createAccount(array('account_number'=>trim($v['invoice_purchase_item_credit'])));
                                $credit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $credit = $credits->account_id;
                            }
                        }
                        if (trim($v['invoice_purchase_item_tax_vat_debit']) != "") {
                            $tax_debits = $account_model->getAccountByWhere(array('account_number'=>trim($v['invoice_purchase_item_tax_vat_debit'])));
                            if (!$tax_debits) {
                                $account_model->createAccount(array('account_number'=>trim($v['invoice_purchase_item_tax_vat_debit'])));
                                $tax_debit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $tax_debit = $tax_debits->account_id;
                            }
                        }
                        if (trim($v['invoice_purchase_item_house']) != "") {
                            $houses = $house_model->getHouseByWhere(array('house_code'=>trim($v['invoice_purchase_item_house'])));
                            if (!$houses) {
                                $house_model->createHouse(array('house_code'=>trim($v['invoice_purchase_item_house'])));
                                $house = $house_model->getLasthouse()->house_id;
                            }
                            else{
                                $house = $houses->house_id;
                            }
                        }
                        
                        

                        $data_item = array(
                            'invoice_purchase' => $id_invoice_purchase,
                            'invoice_purchase_item' => $v['invoice_purchase_item'],
                            'invoice_purchase_item_unit' => trim($v['invoice_purchase_item_unit']),
                            'invoice_purchase_item_number' => trim($v['invoice_purchase_item_number']),
                            'invoice_purchase_item_price' => str_replace(',', '', $v['invoice_purchase_item_price']),
                            'invoice_purchase_item_money' => str_replace(',', '', $v['invoice_purchase_item_money']),
                            'invoice_purchase_item_debit' => $debit,
                            'invoice_purchase_item_credit' => $credit,
                            'invoice_purchase_item_tax_vat_percent' => trim($v['invoice_purchase_item_tax_vat_percent']),
                            'invoice_purchase_item_tax_vat' => str_replace(',', '', $v['invoice_purchase_item_tax_vat']),
                            'invoice_purchase_item_tax_vat_debit' => $tax_debit,
                            'invoice_purchase_item_total' => str_replace(',', '', $v['invoice_purchase_item_total']),
                            'invoice_purchase_item_cost' => str_replace(',', '', $v['invoice_purchase_item_cost']),
                            'invoice_purchase_item_house' => $house,
                        );

                        $number += $data_item['invoice_purchase_item_number'];
                        $money += $data_item['invoice_purchase_item_money'];
                        $cost += round($data_item['invoice_purchase_item_cost']*$data_item['invoice_purchase_item_number'],2);
                        $tax += $data_item['invoice_purchase_item_tax_vat'];

                        $invoice_purchase_items = $invoice_purchase_item_model->getInvoiceByWhere(array('invoice_purchase'=>$data_item['invoice_purchase'],'invoice_purchase_item'=>$data_item['invoice_purchase_item']));
                        if ($invoice_purchase_items) {
                            $invoice_purchase_item_model->updateInvoice($data_item,array('invoice_purchase_item_id'=>$invoice_purchase_items->invoice_purchase_item_id));
                            $id_invoice_purchase_item = $invoice_purchase_items->invoice_purchase_item_id;

                            $data_additional = array(
                                'invoice_purchase_item'=>$id_invoice_purchase_item,
                                'document_number'=>$invoice_purchases->invoice_purchase_document_number,
                                'document_date'=>$invoice_purchases->invoice_purchase_document_date,
                                'additional_date'=>$invoice_purchases->invoice_purchase_additional_date,
                                'additional_comment'=>$invoice_purchases->invoice_purchase_comment,
                                'debit'=>$data_item['invoice_purchase_item_debit'],
                                'credit'=>$data_item['invoice_purchase_item_credit'],
                                'money'=>$data_item['invoice_purchase_item_money'],
                            );
                            $additional_model->updateAdditional($data_additional,array('invoice_purchase_item'=>$id_invoice_purchase_item,'debit'=>$invoice_purchase_items->invoice_purchase_item_debit));

                            $data_stock = array(
                                'invoice_purchase_item'=>$id_invoice_purchase_item,
                                'stock_date'=>$invoice_purchases->invoice_purchase_document_date,
                                'stock_item'=>$data_item['invoice_purchase_item'],
                                'stock_number'=>$data_item['invoice_purchase_item_number'],
                                'stock_price'=>$data_item['invoice_purchase_item_total'],
                                'stock_house'=>$data_item['invoice_purchase_item_house'],
                            );
                            $stock_model->updateStock($data_stock,array('invoice_purchase_item'=>$id_invoice_purchase_item));

                            if($invoice_purchase_items->invoice_purchase_item_tax_vat_debit > 0 && $data_item['invoice_purchase_item_tax_vat_debit'] > 0){
                                $data_additional = array(
                                    'invoice_purchase_item'=>$id_invoice_purchase_item,
                                    'document_number'=>$invoice_purchases->invoice_purchase_document_number,
                                    'document_date'=>$invoice_purchases->invoice_purchase_document_date,
                                    'additional_date'=>$invoice_purchases->invoice_purchase_additional_date,
                                    'additional_comment'=>$invoice_purchases->invoice_purchase_comment,
                                    'debit'=>$data_item['invoice_purchase_item_tax_vat_debit'],
                                    'credit'=>$data_item['invoice_purchase_item_credit'],
                                    'money'=>$data_item['invoice_purchase_item_tax_vat'],
                                );
                                $additional_model->updateAdditional($data_additional,array('invoice_purchase_item'=>$id_invoice_purchase_item,'debit'=>$invoice_purchase_items->invoice_purchase_item_tax_vat_debit));
                            }
                            else if($invoice_purchase_items->invoice_purchase_item_tax_vat_debit == 0 && $data_item['invoice_purchase_item_tax_vat_debit'] > 0){
                                $data_additional = array(
                                    'invoice_purchase_item'=>$id_invoice_purchase_item,
                                    'document_number'=>$invoice_purchases->invoice_purchase_document_number,
                                    'document_date'=>$invoice_purchases->invoice_purchase_document_date,
                                    'additional_date'=>$invoice_purchases->invoice_purchase_additional_date,
                                    'additional_comment'=>$invoice_purchases->invoice_purchase_comment,
                                    'debit'=>$data_item['invoice_purchase_item_tax_vat_debit'],
                                    'credit'=>$data_item['invoice_purchase_item_credit'],
                                    'money'=>$data_item['invoice_purchase_item_tax_vat'],
                                );
                                $additional_model->createAdditional($data_additional);
                            }
                            else{
                                $additional_model->queryAdditional('DELETE FROM additional WHERE invoice_purchase_item='.$id_invoice_purchase_item.' AND debit='.$invoice_purchase_items->invoice_purchase_item_tax_vat_debit);
                            }
                        }
                        else{
                            $invoice_purchase_item_model->createInvoice($data_item);
                            $id_invoice_purchase_item = $invoice_purchase_item_model->getLastInvoice()->invoice_purchase_item_id;

                            $data_additional = array(
                                'invoice_purchase_item'=>$id_invoice_purchase_item,
                                'document_number'=>$invoice_purchases->invoice_purchase_document_number,
                                'document_date'=>$invoice_purchases->invoice_purchase_document_date,
                                'additional_date'=>$invoice_purchases->invoice_purchase_additional_date,
                                'additional_comment'=>$invoice_purchases->invoice_purchase_comment,
                                'debit'=>$data_item['invoice_purchase_item_debit'],
                                'credit'=>$data_item['invoice_purchase_item_credit'],
                                'money'=>$data_item['invoice_purchase_item_money'],
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_stock = array(
                                'invoice_purchase_item'=>$id_invoice_purchase_item,
                                'stock_date'=>$invoice_purchases->invoice_purchase_document_date,
                                'stock_item'=>$data_item['invoice_purchase_item'],
                                'stock_number'=>$data_item['invoice_purchase_item_number'],
                                'stock_price'=>$data_item['invoice_purchase_item_total'],
                                'stock_house'=>$data_item['invoice_purchase_item_house'],
                            );
                            $stock_model->createStock($data_stock);

                            if($data_item['invoice_purchase_item_tax_vat_debit'] > 0){
                                $data_additional = array(
                                    'invoice_purchase_item'=>$id_invoice_purchase_item,
                                    'document_number'=>$invoice_purchases->invoice_purchase_document_number,
                                    'document_date'=>$invoice_purchases->invoice_purchase_document_date,
                                    'additional_date'=>$invoice_purchases->invoice_purchase_additional_date,
                                    'additional_comment'=>$invoice_purchases->invoice_purchase_comment,
                                    'debit'=>$data_item['invoice_purchase_item_tax_vat_debit'],
                                    'credit'=>$data_item['invoice_purchase_item_credit'],
                                    'money'=>$data_item['invoice_purchase_item_tax_vat'],
                                );
                                $additional_model->createAdditional($data_additional);
                            }
                            
                        }

                        
                    }
                }

                $item_olds = $invoice_purchase_item_model->queryInvoice('SELECT * FROM invoice_purchase_item WHERE invoice_purchase='.$id_invoice_purchase.' AND invoice_purchase_item NOT IN ('.$arr_item.')');
                foreach ($item_olds as $item_old) {
                    $additional_model->queryAdditional('DELETE FROM additional WHERE invoice_purchase_item='.$item_old->invoice_purchase_item_id);
                    $stock_model->queryStock('DELETE FROM stock WHERE invoice_purchase_item='.$item_old->invoice_purchase_item_id);
                    $invoice_purchase_item_model->queryInvoice('DELETE FROM invoice_purchase_item WHERE invoice_purchase_item_id='.$item_old->invoice_purchase_item_id);
                }
                
                $data_buy = array(
                    'invoice_purchase_number_total'=>$number,
                    'invoice_purchase_money'=>$money,
                    'invoice_purchase_tax_vat'=>$tax,
                    'invoice_purchase_cost'=>$cost,
                    'invoice_purchase_total'=>($money+$tax+$cost),
                );
                $invoice_purchase_model->updateInvoice($data_buy,array('invoice_purchase_id'=>$id_invoice_purchase));

                $data_debit = array(
                    'invoice_purchase'=>$id_invoice_purchase,
                    'debit_date'=>$invoice_purchases->invoice_purchase_document_date,
                    'debit_customer'=>$invoice_purchases->invoice_purchase_customer,
                    'debit_money'=>($money+$tax),
                    'debit_comment'=>$invoice_purchases->invoice_purchase_comment,
                );

                $invoice_purchase_debits = $debit_model->getDebitByWhere(array('invoice_purchase'=>$id_invoice_purchase,'debit_money'=>($invoice_purchases->invoice_purchase_money+$invoice_purchases->invoice_purchase_tax_vat)));
                if ($invoice_purchase_debits) {
                    $debit_model->updateDebit($data_debit,array('debit_id'=>$invoice_purchase_debits->debit_id));
                }
                else{
                    $debit_model->createDebit($data_debit);
                }

                $data_invoice = array(
                    'invoice_purchase'=>$id_invoice_purchase,
                    'invoice_symbol'=>$invoice_purchases->invoice_purchase_symbol,
                    'invoice_date'=>$invoice_purchases->invoice_purchase_date,
                    'invoice_number'=>$invoice_purchases->invoice_purchase_number,
                    'invoice_customer'=>$invoice_purchases->invoice_purchase_customer,
                    'invoice_money'=>$money,
                    'invoice_tax'=>$tax,
                    'invoice_comment'=>$invoice_purchases->invoice_purchase_comment,
                    'invoice_type'=>1,
                );

                $invoice_purchase_invoices = $invoice_model->getInvoiceByWhere(array('invoice_purchase'=>$id_invoice_purchase));
                if ($invoice_purchase_invoices) {
                    $invoice_model->updateInvoice($data_invoice,array('invoice_id'=>$invoice_purchase_invoices->invoice_id));
                }
                else{
                    $invoice_model->createInvoice($data_invoice);
                }
            }
                    
        }
    }

    public function getserviceadd(){
        if (isset($_POST['invoice_purchase'])) {
            $invoice_service_buy_model = $this->model->get('invoiceservicebuyModel');
            $join = array('table'=>'service_buy','where'=>'service_buy=service_buy_id');
            $invoice_service_buys = $invoice_service_buy_model->getAllInvoice(array('where'=>'invoice_purchase='.$_POST['invoice_purchase']),$join);

            $str = "";
            $i = 1;
            foreach ($invoice_service_buys as $service) {
                $str .= '<tr>';
                $str .= '<td class="width-3">'.$i.'</td>';
                $str .= '<td class="width-10"><input data="'.$service->service_buy.'" value="'.$service->service_buy_document_number.'" type="text" name="service_buy[]" class="service_buy" required="required"></td>';
                $str .= '<td class="width-10"><input value="'.$this->lib->formatMoney($service->invoice_service_buy_money).'" type="text" name="service_buy_total[]" class="service_buy_total numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td><input value="'.$service->service_buy_comment.'" type="text" name="service_buy_comment[]" class="service_buy_comment" required="required"></td>';
                $str .= '</tr>';
            }

            echo $str;
        }
    }
    public function getitemadd(){
        if (isset($_POST['invoice_purchase'])) {
            $account_model = $this->model->get('accountModel');
            $house_model = $this->model->get('houseModel');
            $invoice_purchase_item_model = $this->model->get('invoicepurchaseitemModel');
            $join = array('table'=>'items','where'=>'invoice_purchase_item=items_id');
            $invoice_purchase_items = $invoice_purchase_item_model->getAllInvoice(array('where'=>'invoice_purchase='.$_POST['invoice_purchase']),$join);

            $str = "";
            $str2 = "";
            $str3 = "";
            $i = 1;
            foreach ($invoice_purchase_items as $item) {
                $debit = $account_model->getAccount($item->invoice_purchase_item_debit)->account_number;
                $credit = $account_model->getAccount($item->invoice_purchase_item_credit)->account_number;
                $tax_debit = "";
                if ($item->invoice_purchase_item_tax_vat_debit > 0) {
                    $tax_debit = $account_model->getAccount($item->invoice_purchase_item_tax_vat_debit)->account_number;
                }
                $house = "";
                $house_debit = "";
                $house_credit = "";
                if ($item->invoice_purchase_item_house > 0) {
                    $house = $house_model->getHouse($item->invoice_purchase_item_house)->house_code;
                }

                $str .= '<tr>';
                $str .= '<td class="width-3">'.$i.'</td>';
                $str .= '<td class="width-10">
                  <input data="'.$item->invoice_purchase_item.'" value="'.$item->items_code.'" type="text" name="invoice_purchase_item[]" class="invoice_purchase_item left" required="required" placeholder="Nhập mã hoặc tên" autocomplete="off">
                  <button type="button" class="find_item right" title="Danh mục"><i class="fa fa-search"></i></button>
                </td>';
                $str .= '<td>
                  <input value="'.$item->items_name.'" type="text" name="invoice_purchase_item_name[]" class="invoice_purchase_item_name" required="required" autocomplete="off">
                  <ul class="name_list_id"></ul>
                </td>';
                $str .= '<td class="width-7">
                  <input value="'.$house.'" type="text" name="invoice_purchase_item_house[]" class="invoice_purchase_item_house keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_5"></ul>
                </td>';
                $str .= '<td class="width-7">
                  <input value="'.$debit.'" type="text" name="invoice_purchase_item_debit[]" class="invoice_purchase_item_debit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_2"></ul>
                </td>';
                $str .= '<td class="width-7">
                  <input value="'.$credit.'" type="text" name="invoice_purchase_item_credit[]" class="invoice_purchase_item_credit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_3"></ul>
                </td>';
                $str .= '<td class="width-7"><input value="'.$item->invoice_purchase_item_unit.'" type="text" name="invoice_purchase_item_unit[]" class="invoice_purchase_item_unit" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-7"><input alt="'.$item->items_stuff.'" value="'.$item->invoice_purchase_item_number.'" type="text" name="invoice_purchase_item_number[]" class="invoice_purchase_item_number text-right" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-10"><input value="'.$this->lib->formatMoney($item->invoice_purchase_item_price).'" type="text" name="invoice_purchase_item_price[]" class="invoice_purchase_item_price numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td><input value="'.$this->lib->formatMoney($item->invoice_purchase_item_money).'" type="text" name="invoice_purchase_item_money[]" class="invoice_purchase_item_money numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-10"><input value="'.$this->lib->formatMoney($item->invoice_purchase_item_cost).'" disabled type="text" name="invoice_purchase_item_cost[]" class="invoice_purchase_item_cost numbers text-right" autocomplete="off"></td>';
                $str .= '<td><input value="'.$this->lib->formatMoney($item->invoice_purchase_item_total).'" disabled type="text" name="invoice_purchase_item_total[]" class="invoice_purchase_item_total numbers text-right" required="required" autocomplete="off"></td>';
              $str .= '</tr>';

              $str2 .= '<tr>';
                $str2 .= '<td class="width-3">'.$i.'</td>';
                $str2 .= '<td class="width-10"><input value="'.$item->items_code.'" type="text" name="invoice_purchase_item2[]" class="invoice_purchase_item2" disabled required="required" autocomplete="off"></td>';
                $str2 .= '<td><input value="'.$item->items_name.'" type="text" name="invoice_purchase_item_name2[]" class="invoice_purchase_item_name2" disabled required="required" autocomplete="off"></td>';
                $str2 .= '<td><input value="'.$item->invoice_purchase_item_tax_vat_percent.'" type="text" name="invoice_purchase_item_tax_vat_percent[]" class="invoice_purchase_item_tax_vat_percent keep-val text-right" required="required" autocomplete="off"></td>';
                $str2 .= '<td><input value="'.$this->lib->formatMoney($item->invoice_purchase_item_tax_vat).'" type="text" name="invoice_purchase_item_tax_vat[]" class="invoice_purchase_item_tax_vat numbers text-right" required="required" autocomplete="off"></td>';
                $str2 .= '<td>
                  <input value="'.$tax_debit.'" type="text" name="invoice_purchase_item_tax_vat_debit[]" class="invoice_purchase_item_tax_vat_debit keep-val" required="required" autocomplete="off">
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
            $invoice_purchase_model = $this->model->get('invoicepurchaseModel');
            $invoice_purchase_item_model = $this->model->get('invoicepurchaseitemModel');
            $additional_model = $this->model->get('additionalModel');
            $debit_model = $this->model->get('debitModel');
            $invoice_model = $this->model->get('invoiceModel');
            $stock_model = $this->model->get('stockModel');
            $invoice_service_buy_model = $this->model->get('invoiceservicebuyModel');
           
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $invoice_purchases = $invoice_purchase_model->getInvoice($data);
                    $invoice_purchase_items = $invoice_purchase_item_model->getAllInvoice(array('where'=>'invoice_purchase='.$data));
                    foreach ($invoice_purchase_items as $item) {
                        $additional_model->queryAdditional('DELETE FROM additional WHERE invoice_purchase_item='.$item->invoice_purchase_item_id);
                        $stock_model->queryStock('DELETE FROM stock WHERE invoice_purchase_item='.$item->invoice_purchase_item_id);
                    }
                    $invoice_purchase_item_model->queryInvoice('DELETE FROM invoice_purchase_item WHERE invoice_purchase='.$data);
                    $debit_model->queryDebit('DELETE FROM debit WHERE invoice_purchase='.$data.' AND debit_money='.($invoice_purchases->invoice_purchase_money+$invoice_purchases->invoice_purchase_tax_vat));
                    $invoice_model->queryInvoice('DELETE FROM invoice WHERE invoice_purchase='.$data);
                    $invoice_service_buy_model->queryInvoice('DELETE FROM invoice_service_buy WHERE invoice_purchase='.$data);
                       $invoice_purchase_model->deleteInvoice($data);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|invoice_purchase|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    
                    
                }
                return true;
            }
            else{
                    $invoice_purchases = $invoice_purchase_model->getInvoice($_POST['data']);
                    $invoice_purchase_items = $invoice_purchase_item_model->getAllInvoice(array('where'=>'invoice_purchase='.$_POST['data']));
                    foreach ($invoice_purchase_items as $item) {
                        $additional_model->queryAdditional('DELETE FROM additional WHERE invoice_purchase_item='.$item->invoice_purchase_item_id);
                        $stock_model->queryStock('DELETE FROM stock WHERE invoice_purchase_item='.$item->invoice_purchase_item_id);
                    }
                    $invoice_purchase_item_model->queryInvoice('DELETE FROM invoice_purchase_item WHERE invoice_purchase='.$_POST['data']);
                    $debit_model->queryDebit('DELETE FROM debit WHERE invoice_purchase='.$_POST['data'].' AND debit_money='.($invoice_purchases->invoice_purchase_money+$invoice_purchases->invoice_purchase_tax_vat));
                    $invoice_model->queryInvoice('DELETE FROM invoice WHERE invoice_purchase='.$_POST['data']);
                    $invoice_service_buy_model->queryInvoice('DELETE FROM invoice_service_buy WHERE invoice_purchase='.$_POST['data']);
                        $invoice_purchase_model->deleteInvoice($_POST['data']);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|invoice_purchase|"."\n"."\r\n";
                        
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