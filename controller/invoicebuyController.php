<?php
Class invoicebuyController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->invoicebuy) || json_decode($_SESSION['user_permission_action'])->invoicebuy != "invoicebuy") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Hàng nhập về kho';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'invoice_buy_document_date';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
        }
        
        $invoice_buy_model = $this->model->get('invoicebuyModel');

        $join = array('table'=>'customer','where'=>'invoice_buy_customer=customer_id');

        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => '1=1',
        );
        
        
        $tongsodong = count($invoice_buy_model->getAllInvoice($data,$join));
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
            $search = '( invoice_buy_number LIKE "%'.$keyword.'%" 
                    OR customer_name LIKE "%'.$keyword.'%" 
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        

        
        $this->view->data['invoice_buys'] = $invoice_buy_model->getAllInvoice($data,$join);
        $this->view->data['lastID'] = isset($invoice_buy_model->getLastInvoice()->invoice_buy_document_number)?$invoice_buy_model->getLastInvoice()->invoice_buy_document_number:0;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('invoicebuy/index');
    }
    public function getServicebuy(){
        $service_buy_model = $this->model->get('servicebuyModel');
        $invoice_service_buy_model = $this->model->get('invoiceservicebuyModel');

        //$qr = 'SELECT a.*, d.customer_name FROM service_buy a, customer d WHERE a.service_buy_customer=d.customer_id AND ( a.service_buy_id NOT IN (SELECT b.service_buy FROM invoice_service_buy b) OR a.service_buy_money > (SELECT SUM(c.invoice_service_buy_money) FROM invoice_service_buy c WHERE c.service_buy=a.service_buy_id GROUP BY c.service_buy) )';
        $qr = 'SELECT a.*, d.customer_name FROM service_buy a, customer d WHERE a.service_buy_customer=d.customer_id ';

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
   public function getServicebuy2(){
        $service_buy_model = $this->model->get('servicebuyModel');
        $invoice_service_buy_model = $this->model->get('invoiceservicebuyModel');

        //$qr = 'SELECT a.*, d.customer_name FROM service_buy a, customer d WHERE a.service_buy_customer=d.customer_id AND ( a.service_buy_id NOT IN (SELECT b.service_buy FROM invoice_service_buy b) OR a.service_buy_money > (SELECT SUM(c.invoice_service_buy_money) FROM invoice_service_buy c WHERE c.service_buy=a.service_buy_id GROUP BY c.service_buy) )';
        $qr = 'SELECT a.*, d.customer_name FROM service_buy a, customer d WHERE a.service_buy_customer=d.customer_id ';

        $services = $service_buy_model->queryService($qr);
        
        $str = '<table class="table_data" id="tblExport4">';
        $str .= '<thead><tr><th class="fix"><input type="checkbox" onclick="checkall(\'checkbox4\', this)" name="checkall"/></th><th class="fix">Số CT</th><th class="fix">Ngày CT</th><th class="fix">NCC</th><th class="fix">Diễn giải</th><th class="fix">Tổng tiền</th><th class="fix">Số tiền phân bổ</th></tr></thead>';
        $str .= '<tbody>';

        foreach ($services as $service) {
            $invoice_services = $invoice_service_buy_model->getAllInvoice(array('where'=>'service_buy='.$service->service_buy_id));
            $used=0;
            foreach ($invoice_services as $invoice_service) {
                $used += $invoice_service->invoice_service_buy_money;
            }
            $money = $service->service_buy_money-$used;
            $money_foreign = $service->service_buy_money_foreign;
            $str .= '<tr class="tr3" data="'.$service->service_buy_id.'"><td><input name="check_i3[]" type="checkbox" class="checkbox4" value="'.$service->service_buy_id.'" data-doc="'.$service->service_buy_document_number.'" data-com="'.$service->service_buy_comment.'" data-money="'.$this->lib->formatMoney($money).'" data-money-foreign="'.$money_foreign.'" ></td><td class="fix">'.$service->service_buy_document_number.'</td><td class="fix">'.$this->lib->hien_thi_ngay_thang($service->service_buy_document_date).'</td><td class="fix">'.$service->customer_name.'</td><td class="fix">'.$service->service_buy_comment.'</td><td class="fix">'.$this->lib->formatMoney($money).'</td><td class="fix"><input style="width:120px" type="text" name="money_add2[]" class="money_add2 numbers" value="'.$this->lib->formatMoney($money).'" max="'.$money.'"></td></tr>';
        }
        
        $str .= '</tbody></table>';
        echo $str;
   }

    public function getItem(){
        $items_model = $this->model->get('itemsModel');

        $rowIndex = $_POST['rowIndex'];

        $items = $items_model->getAllItems(array('where'=>'items_type=1','order_by'=>'items_code ASC, items_name ASC'));
        
        $str = '<table class="table_data" id="tblExport2">';
        $str .= '<thead><tr><th class="fix"><input type="checkbox" onclick="checkall(\'checkbox2\', this)" name="checkall"/></th><th class="fix">Mã vật tư</th><th class="fix">Tên vật tư</th><th class="fix">ĐVT</th><th class="fix">Thuế suất</th><th class="fix">Hệ số</th></tr></thead>';
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
            
            $invoice_buy_model = $this->model->get('invoicebuyModel');
            $invoice_buy_item_model = $this->model->get('invoicebuyitemModel');
            $additional_model = $this->model->get('additionalModel');
            $debit_model = $this->model->get('debitModel');
            $account_model = $this->model->get('accountModel');
            $invoice_model = $this->model->get('invoiceModel');
            $stock_model = $this->model->get('stockModel');
            $house_model = $this->model->get('houseModel');
            $invoice_service_buy_model = $this->model->get('invoiceservicebuyModel');
            $tax_model = $this->model->get('taxModel');

            $items = $_POST['item'];
            $service_buys = $_POST['service_buy'];
            $service_buy2s = $_POST['service_buy2'];

            $data = array(
                        
                        'invoice_buy_document_date' => strtotime(str_replace('/','-',$_POST['invoice_buy_document_date'])),
                        'invoice_buy_document_number' => trim($_POST['invoice_buy_document_number']),
                        'invoice_buy_additional_date' => strtotime(str_replace('/','-',$_POST['invoice_buy_additional_date'])),
                        'invoice_buy_customer' => trim($_POST['invoice_buy_customer']),
                        'invoice_buy_number' => trim($_POST['invoice_buy_number']),
                        'invoice_buy_date' => strtotime(str_replace('/','-',$_POST['invoice_buy_date'])),
                        'invoice_buy_symbol' => trim($_POST['invoice_buy_symbol']),
                        'invoice_buy_bill_number' => trim($_POST['invoice_buy_bill_number']),
                        'invoice_buy_contract_number' => trim($_POST['invoice_buy_contract_number']),
                        'invoice_buy_money_type' => trim($_POST['invoice_buy_money_type']),
                        'invoice_buy_money_rate' => str_replace(',','',trim($_POST['invoice_buy_money_rate'])),
                        'invoice_buy_origin_doc' => trim($_POST['invoice_buy_origin_doc']),
                        'invoice_buy_comment' => trim($_POST['invoice_buy_comment']),
                        'invoice_buy_allocation' => trim($_POST['invoice_buy_allocation']),
                        'invoice_buy_allocation2' => trim($_POST['invoice_buy_allocation2']),
                        );
            

            if ($_POST['yes'] != "") {
                $check = $invoice_buy_model->queryInvoice('SELECT * FROM invoice_buy WHERE (invoice_buy_document_number='.$data['invoice_buy_document_number'].') AND invoice_buy_id!='.$_POST['yes']);
                if($check){
                    echo "Chứng từ này đã tồn tại";
                    return false;
                }
                else{
                    $invoice_buy_model->updateInvoice($data,array('invoice_buy_id' => trim($_POST['yes'])));
                    echo "Cập nhật thành công";

                    $id_invoice_buy = $_POST['yes'];

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|invoice_buy|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                
            }
            else{
                $check = $invoice_buy_model->queryInvoice('SELECT * FROM invoice_buy WHERE (invoice_buy_document_number='.$data['invoice_buy_document_number'].')');
                if($check){
                    echo "Chứng từ này đã tồn tại";
                    return false;
                }
                else{
                    $data['invoice_buy_create_user'] = $_SESSION['userid_logined'];
                    $data['invoice_buy_create_date'] = strtotime(date('d-m-Y'));

                    $invoice_buy_model->createInvoice($data);
                    echo "Thêm thành công";

                $id_invoice_buy = $invoice_buy_model->getLastInvoice()->invoice_buy_id;

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                    $filename = "action_logs.txt";
                    $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$invoice_buy_model->getLastInvoice()->invoice_buy_id."|invoice_buy|".implode("-",$data)."\n"."\r\n";
                    
                    $fh = fopen($filename, "a") or die("Could not open log file.");
                    fwrite($fh, $text) or die("Could not write file!");
                    fclose($fh);
                }
                    
                
            }

            if (isset($id_invoice_buy)) {
                $invoice_buys = $invoice_buy_model->getInvoice($id_invoice_buy);

                $money = 0;
                $money_foreign = 0;
                $tax = 0;
                $import = 0;
                $custom_cost = 0;
                $other_cost = 0;
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
                            'invoice_buy'=>$id_invoice_buy,
                            'service_buy'=>$s['service_buy_id'],
                            'invoice_service_buy_money'=>str_replace(',', '', $s['service_buy_total']),
                            'invoice_buy_type'=>2,
                        );

                        $invoice_service_buys = $invoice_service_buy_model->getInvoiceByWhere(array('invoice_buy'=>$data_service['invoice_buy'],'service_buy'=>$data_service['service_buy']));
                        if ($invoice_service_buys) {
                            $invoice_service_buy_model->updateInvoice($data_service,array('invoice_service_buy_id'=>$invoice_service_buys->invoice_service_buy_id));
                        }
                        else{
                            $invoice_service_buy_model->createInvoice($data_service);
                        }
                    }
                }
                $service_olds = $invoice_service_buy_model->queryInvoice('SELECT * FROM invoice_service_buy WHERE invoice_buy='.$id_invoice_buy.' AND invoice_buy_type=2 AND service_buy NOT IN ('.$arr_service.')');
                foreach ($service_olds as $service_olds) {
                    $invoice_service_buy_model->queryInvoice('DELETE FROM invoice_service_buy WHERE invoice_service_buy_id='.$service_olds->invoice_service_buy_id);
                }

                
                $arr_service2 = "";

                foreach ($service_buy2s as $s2) {
                    if($s2['service_buy_id'] != ""){
                        if ($arr_service2=="") {
                            $arr_service2 .= $s2['service_buy_id'];
                        }
                        else{
                            $arr_service2 .= ','.$s2['service_buy_id'];
                        }

                        $data_service = array(
                            'invoice_buy'=>$id_invoice_buy,
                            'service_buy'=>$s2['service_buy_id'],
                            'invoice_service_buy_money'=>str_replace(',', '', $s2['service_buy_total']),
                            'invoice_service_buy_money_foreign'=>str_replace(',', '', $s2['service_buy_total_foreign']),
                            'invoice_buy_type'=>1,
                        );

                        $invoice_service_buys = $invoice_service_buy_model->getInvoiceByWhere(array('invoice_buy'=>$data_service['invoice_buy'],'service_buy'=>$data_service['service_buy']));
                        if ($invoice_service_buys) {
                            $invoice_service_buy_model->updateInvoice($data_service,array('invoice_service_buy_id'=>$invoice_service_buys->invoice_service_buy_id));
                        }
                        else{
                            $invoice_service_buy_model->createInvoice($data_service);
                        }
                    }
                }
                $service_olds = $invoice_service_buy_model->queryInvoice('SELECT * FROM invoice_service_buy WHERE invoice_buy='.$id_invoice_buy.' AND invoice_buy_type=1 AND service_buy NOT IN ('.$arr_service2.')');
                foreach ($service_olds as $service_olds) {
                    $invoice_service_buy_model->queryInvoice('DELETE FROM invoice_service_buy WHERE invoice_service_buy_id='.$service_olds->invoice_service_buy_id);
                }


                foreach ($items as $v) {
                    if($v['invoice_buy_item'] != ""){
                        if ($arr_item=="") {
                            $arr_item .= $v['invoice_buy_item'];
                        }
                        else{
                            $arr_item .= ','.$v['invoice_buy_item'];
                        }

                        $house = 0;
                        $debit = 0;
                        $credit = 0;
                        $import_debit = 0;
                        $tax_debit = 0;
                        $tax_credit = 0;
                        if (trim($v['invoice_buy_item_house']) != "") {
                            $houses = $house_model->getHouseByWhere(array('house_code'=>trim($v['invoice_buy_item_house'])));
                            if (!$houses) {
                                $house_model->createHouse(array('house_code'=>trim($v['invoice_buy_item_house'])));
                                $house = $house_model->getLasthouse()->house_id;
                            }
                            else{
                                $house = $houses->house_id;
                            }
                        }

                        if (trim($v['invoice_buy_item_debit']) != "") {
                            $debits = $account_model->getAccountByWhere(array('account_number'=>trim($v['invoice_buy_item_debit'])));
                            if (!$debits) {
                                $account_model->createAccount(array('account_number'=>trim($v['invoice_buy_item_debit'])));
                                $debit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $debit = $debits->account_id;
                            }
                        }
                        if (trim($v['invoice_buy_item_credit']) != "") {
                            $credits = $account_model->getAccountByWhere(array('account_number'=>trim($v['invoice_buy_item_credit'])));
                            if (!$credits) {
                                $account_model->createAccount(array('account_number'=>trim($v['invoice_buy_item_credit'])));
                                $credit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $credit = $credits->account_id;
                            }
                        }
                        if (trim($v['invoice_buy_item_tax_import_debit']) != "") {
                            $import_debits = $account_model->getAccountByWhere(array('account_number'=>trim($v['invoice_buy_item_tax_import_debit'])));
                            if (!$import_debits) {
                                $account_model->createAccount(array('account_number'=>trim($v['invoice_buy_item_tax_import_debit'])));
                                $import_debit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $import_debit = $import_debits->account_id;
                            }
                        }
                        if (trim($v['invoice_buy_item_tax_vat_debit']) != "") {
                            $tax_debits = $account_model->getAccountByWhere(array('account_number'=>trim($v['invoice_buy_item_tax_vat_debit'])));
                            if (!$tax_debits) {
                                $account_model->createAccount(array('account_number'=>trim($v['invoice_buy_item_tax_vat_debit'])));
                                $tax_debit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $tax_debit = $tax_debits->account_id;
                            }
                        }
                        if (trim($v['invoice_buy_item_tax_vat_credit']) != "") {
                            $tax_credits = $account_model->getAccountByWhere(array('account_number'=>trim($v['invoice_buy_item_tax_vat_credit'])));
                            if (!$tax_credits) {
                                $account_model->createAccount(array('account_number'=>trim($v['invoice_buy_item_tax_vat_credit'])));
                                $tax_credit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $tax_credit = $tax_credits->account_id;
                            }
                        }
                        
                        

                        $data_item = array(
                            'invoice_buy' => $id_invoice_buy,
                            'invoice_buy_item' => $v['invoice_buy_item'],
                            'invoice_buy_item_house' => $house,
                            'invoice_buy_item_unit' => trim($v['invoice_buy_item_unit']),
                            'invoice_buy_item_number' => trim($v['invoice_buy_item_number']),
                            'invoice_buy_item_price' => str_replace(',', '', $v['invoice_buy_item_price']),
                            'invoice_buy_item_money' => str_replace(',', '', $v['invoice_buy_item_money']),
                            'invoice_buy_item_debit' => $debit,
                            'invoice_buy_item_credit' => $credit,
                            'invoice_buy_item_custom_cost' => str_replace(',', '', $v['invoice_buy_item_custom_cost']),
                            'invoice_buy_item_custom_cost_money' => str_replace(',', '', $v['invoice_buy_item_custom_cost_money']),
                            'invoice_buy_item_other_cost' => str_replace(',', '', $v['invoice_buy_item_other_cost']),
                            'invoice_buy_item_custom_rate' => str_replace(',', '', $v['invoice_buy_item_custom_rate']),
                            'invoice_buy_item_tax_import_percent' => trim($v['invoice_buy_item_tax_import_percent']),
                            'invoice_buy_item_tax_import' => str_replace(',', '', $v['invoice_buy_item_tax_import']),
                            'invoice_buy_item_tax_import_debit' => $import_debit,
                            'invoice_buy_item_tax_vat_percent' => trim($v['invoice_buy_item_tax_vat_percent']),
                            'invoice_buy_item_tax_vat' => str_replace(',', '', $v['invoice_buy_item_tax_vat']),
                            'invoice_buy_item_tax_vat_debit' => $tax_debit,
                            'invoice_buy_item_tax_vat_credit' => $tax_credit,
                            'invoice_buy_item_tax_total' => str_replace(',', '', $v['invoice_buy_item_tax_total']),
                            'invoice_buy_item_total' => str_replace(',', '', $v['invoice_buy_item_total']),
                        );

                        $number += $data_item['invoice_buy_item_number'];
                        $money += $data_item['invoice_buy_item_money'];
                        $money_foreign += $data_item['invoice_buy_item_price']*$data_item['invoice_buy_item_number'];
                        $tax += $data_item['invoice_buy_item_tax_vat'];
                        $import += $data_item['invoice_buy_item_tax_import'];
                        $custom_cost += round($data_item['invoice_buy_item_custom_cost_money']*$data_item['invoice_buy_item_number'],2);
                        $other_cost += round($data_item['invoice_buy_item_other_cost']*$data_item['invoice_buy_item_number'],2);

                        $invoice_buy_items = $invoice_buy_item_model->getInvoiceByWhere(array('invoice_buy'=>$data_item['invoice_buy'],'invoice_buy_item'=>$data_item['invoice_buy_item']));
                        if ($invoice_buy_items) {
                            $invoice_buy_item_model->updateInvoice($data_item,array('invoice_buy_item_id'=>$invoice_buy_items->invoice_buy_item_id));
                            $id_invoice_buy_item = $invoice_buy_items->invoice_buy_item_id;

                            $data_additional = array(
                                'invoice_buy_item'=>$id_invoice_buy_item,
                                'document_number'=>$invoice_buys->invoice_buy_document_number,
                                'document_date'=>$invoice_buys->invoice_buy_document_date,
                                'additional_date'=>$invoice_buys->invoice_buy_additional_date,
                                'additional_comment'=>$invoice_buys->invoice_buy_comment,
                                'debit'=>$data_item['invoice_buy_item_debit'],
                                'credit'=>$data_item['invoice_buy_item_credit'],
                                'money'=>($data_item['invoice_buy_item_money']+round($data_item['invoice_buy_item_custom_cost_money']*$data_item['invoice_buy_item_number'],2)+round($data_item['invoice_buy_item_other_cost']*$data_item['invoice_buy_item_number'],2)),
                            );
                            $additional_model->updateAdditional($data_additional,array('invoice_buy_item'=>$id_invoice_buy_item,'debit'=>$invoice_buy_items->invoice_buy_item_debit,'credit'=>$invoice_buy_items->invoice_buy_item_credit));

                            $data_additional = array(
                                'invoice_buy_item'=>$id_invoice_buy_item,
                                'document_number'=>$invoice_buys->invoice_buy_document_number,
                                'document_date'=>$invoice_buys->invoice_buy_document_date,
                                'additional_date'=>$invoice_buys->invoice_buy_additional_date,
                                'additional_comment'=>$invoice_buys->invoice_buy_comment,
                                'debit'=>$data_item['invoice_buy_item_tax_vat_debit'],
                                'credit'=>$data_item['invoice_buy_item_tax_vat_credit'],
                                'money'=>$data_item['invoice_buy_item_tax_vat'],
                            );
                            $additional_model->updateAdditional($data_additional,array('invoice_buy_item'=>$id_invoice_buy_item,'debit'=>$invoice_buy_items->invoice_buy_item_tax_vat_debit,'credit'=>$invoice_buy_items->invoice_buy_item_tax_vat_credit));

                            $data_additional = array(
                                'invoice_buy_item'=>$id_invoice_buy_item,
                                'document_number'=>$invoice_buys->invoice_buy_document_number,
                                'document_date'=>$invoice_buys->invoice_buy_document_date,
                                'additional_date'=>$invoice_buys->invoice_buy_additional_date,
                                'additional_comment'=>$invoice_buys->invoice_buy_comment,
                                'debit'=>$data_item['invoice_buy_item_debit'],
                                'credit'=>$data_item['invoice_buy_item_tax_import_debit'],
                                'money'=>$data_item['invoice_buy_item_tax_import'],
                            );
                            $additional_model->updateAdditional($data_additional,array('invoice_buy_item'=>$id_invoice_buy_item,'debit'=>$invoice_buy_items->invoice_buy_item_debit,'credit'=>$invoice_buy_items->invoice_buy_item_tax_import_debit));

                            $data_stock = array(
                                'invoice_buy_item'=>$id_invoice_buy_item,
                                'stock_date'=>$invoice_buys->invoice_buy_document_date,
                                'stock_item'=>$data_item['invoice_buy_item'],
                                'stock_number'=>$data_item['invoice_buy_item_number'],
                                'stock_price'=>$data_item['invoice_buy_item_total'],
                                'stock_house'=>$data_item['invoice_buy_item_house'],
                            );
                            $stock_model->updateStock($data_stock,array('invoice_buy_item'=>$id_invoice_buy_item));
                        }
                        else{
                            $invoice_buy_item_model->createInvoice($data_item);
                            $id_invoice_buy_item = $invoice_buy_item_model->getLastInvoice()->invoice_buy_item_id;

                            $data_additional = array(
                                'invoice_buy_item'=>$id_invoice_buy_item,
                                'document_number'=>$invoice_buys->invoice_buy_document_number,
                                'document_date'=>$invoice_buys->invoice_buy_document_date,
                                'additional_date'=>$invoice_buys->invoice_buy_additional_date,
                                'additional_comment'=>$invoice_buys->invoice_buy_comment,
                                'debit'=>$data_item['invoice_buy_item_debit'],
                                'credit'=>$data_item['invoice_buy_item_credit'],
                                'money'=>($data_item['invoice_buy_item_money']+round($data_item['invoice_buy_item_custom_cost_money']*$data_item['invoice_buy_item_number'],2)+round($data_item['invoice_buy_item_other_cost']*$data_item['invoice_buy_item_number'],2)),
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_additional = array(
                                'invoice_buy_item'=>$id_invoice_buy_item,
                                'document_number'=>$invoice_buys->invoice_buy_document_number,
                                'document_date'=>$invoice_buys->invoice_buy_document_date,
                                'additional_date'=>$invoice_buys->invoice_buy_additional_date,
                                'additional_comment'=>$invoice_buys->invoice_buy_comment,
                                'debit'=>$data_item['invoice_buy_item_debit'],
                                'credit'=>$data_item['invoice_buy_item_tax_import_debit'],
                                'money'=>$data_item['invoice_buy_item_tax_import'],
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_additional = array(
                                'invoice_buy_item'=>$id_invoice_buy_item,
                                'document_number'=>$invoice_buys->invoice_buy_document_number,
                                'document_date'=>$invoice_buys->invoice_buy_document_date,
                                'additional_date'=>$invoice_buys->invoice_buy_additional_date,
                                'additional_comment'=>$invoice_buys->invoice_buy_comment,
                                'debit'=>$data_item['invoice_buy_item_tax_vat_debit'],
                                'credit'=>$data_item['invoice_buy_item_tax_vat_credit'],
                                'money'=>$data_item['invoice_buy_item_tax_vat'],
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_stock = array(
                                'invoice_buy_item'=>$id_invoice_buy_item,
                                'stock_date'=>$invoice_buys->invoice_buy_document_date,
                                'stock_item'=>$data_item['invoice_buy_item'],
                                'stock_number'=>$data_item['invoice_buy_item_number'],
                                'stock_price'=>$data_item['invoice_buy_item_total'],
                                'stock_house'=>$data_item['invoice_buy_item_house'],
                            );
                            $stock_model->createStock($data_stock);
                            
                        }

                        
                    }
                }

                $item_olds = $invoice_buy_item_model->queryInvoice('SELECT * FROM invoice_buy_item WHERE invoice_buy='.$id_invoice_buy.' AND invoice_buy_item NOT IN ('.$arr_item.')');
                foreach ($item_olds as $item_old) {
                    $additional_model->queryAdditional('DELETE FROM additional WHERE invoice_buy_item='.$item_old->invoice_buy_item_id);
                    $invoice_buy_item_model->queryInvoice('DELETE FROM invoice_buy_item WHERE invoice_buy_item_id='.$item_old->invoice_buy_item_id);
                }
                
                $data_buy = array(
                    'invoice_buy_number_total'=>$number,
                    'invoice_buy_money'=>$money,
                    'invoice_buy_money_foreign'=>$money_foreign,
                    'invoice_buy_tax_vat'=>$tax,
                    'invoice_buy_tax_import'=>$import,
                    'invoice_buy_custom_cost'=>$custom_cost,
                    'invoice_buy_other_cost'=>$other_cost,
                    'invoice_buy_total'=>($money+$tax+$import+$custom_cost+$other_cost),
                );
                $invoice_buy_model->updateInvoice($data_buy,array('invoice_buy_id'=>$id_invoice_buy));

                $data_debit = array(
                    'invoice_buy'=>$id_invoice_buy,
                    'debit_date'=>$invoice_buys->invoice_buy_document_date,
                    'debit_customer'=>$invoice_buys->invoice_buy_customer,
                    'debit_money'=>$money,
                    'debit_money_foreign'=>$money_foreign,
                    'debit_comment'=>$invoice_buys->invoice_buy_comment,
                );

                $invoice_buy_debits = $debit_model->getDebitByWhere(array('invoice_buy'=>$id_invoice_buy,'debit_money'=>$invoice_buys->invoice_buy_money));
                if ($invoice_buy_debits) {
                    $debit_model->updateDebit($data_debit,array('debit_id'=>$invoice_buy_debits->debit_id));
                }
                else{
                    $debit_model->createDebit($data_debit);
                }

                $data_invoice = array(
                    'invoice_buy'=>$id_invoice_buy,
                    'invoice_symbol'=>$invoice_buys->invoice_buy_symbol,
                    'invoice_date'=>$invoice_buys->invoice_buy_date,
                    'invoice_number'=>$invoice_buys->invoice_buy_number,
                    'invoice_customer'=>$invoice_buys->invoice_buy_customer,
                    'invoice_money'=>($money+$custom_cost+$import),
                    'invoice_tax'=>$tax,
                    'invoice_comment'=>$invoice_buys->invoice_buy_comment,
                    'invoice_type'=>1,
                );

                $invoice_buy_invoices = $invoice_model->getInvoiceByWhere(array('invoice_buy'=>$id_invoice_buy));
                if ($invoice_buy_invoices) {
                    $invoice_model->updateInvoice($data_invoice,array('invoice_id'=>$invoice_buy_invoices->invoice_id));
                }
                else{
                    $invoice_model->createInvoice($data_invoice);
                }

                $data_tax_import = array(
                    'invoice_buy'=>$id_invoice_buy,
                    'tax_date'=>$invoice_buys->invoice_buy_date,
                    'tax_money'=>$import,
                    'tax_comment'=>$invoice_buys->invoice_buy_comment,
                    'tax_type'=>1,
                );
                $data_tax_vat = array(
                    'invoice_buy'=>$id_invoice_buy,
                    'tax_date'=>$invoice_buys->invoice_buy_date,
                    'tax_money'=>$tax,
                    'tax_comment'=>$invoice_buys->invoice_buy_comment,
                    'tax_type'=>2,
                );

                $tax_imports = $tax_model->getTaxByWhere(array('invoice_buy'=>$id_invoice_buy,'tax_money'=>$invoice_buys->invoice_buy_tax_import,'tax_type'=>1));
                if ($tax_imports) {
                    $tax_model->updateTax($data_tax_import,array('tax_id'=>$tax_imports->tax_id));
                }
                else if(!$tax_imports){
                    $tax_model->createTax($data_tax_import);
                }

                $tax_vats = $tax_model->getTaxByWhere(array('invoice_buy'=>$id_invoice_buy,'tax_money'=>$invoice_buys->invoice_buy_tax_vat,'tax_type'=>2));
                if ($tax_vats) {
                    $tax_model->updateTax($data_tax_vat,array('tax_id'=>$tax_vats->tax_id));
                }
                else if(!$tax_vats){
                    $tax_model->createTax($data_tax_vat);
                }
            }
                    
        }
    }

    public function getserviceadd(){
        if (isset($_POST['invoice_buy'])) {
            $invoice_service_buy_model = $this->model->get('invoiceservicebuyModel');
            $join = array('table'=>'service_buy','where'=>'service_buy=service_buy_id AND invoice_buy_type=2');
            $invoice_service_buys = $invoice_service_buy_model->getAllInvoice(array('where'=>'invoice_buy='.$_POST['invoice_buy']),$join);

            $str = "";
            $i = 1;
            foreach ($invoice_service_buys as $service) {
                $str .= '<tr id="'.$service->service_buy.$service->invoice_buy.'">';
                $str .= '<td class="width-3">'.$i.'</td>';
                $str .= '<td class="width-10"><input data="'.$service->service_buy.'" value="'.$service->service_buy_document_number.'" type="text" name="service_buy[]" class="service_buy" required="required"></td>';
                $str .= '<td class="width-10"><input value="'.$this->lib->formatMoney($service->invoice_service_buy_money).'" type="text" name="service_buy_total[]" class="service_buy_total numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td><input value="'.$service->service_buy_comment.'" type="text" name="service_buy_comment[]" class="service_buy_comment" required="required"> <i class="fa fa-remove delcost" title="Xóa" id="'.$service->service_buy.'" data="'.$service->invoice_buy.'"></i></td>';
                $str .= '</tr>';

                $i++;  
            }
            $str .= '<tr>';
            $str .= '<td class="width-3">'.$i.'</td>';
            $str .= '<td class="width-10"><input type="text" name="service_buy[]" class="service_buy" required="required"></td>';
            $str .= '<td class="width-10"><input type="text" name="service_buy_total[]" class="service_buy_total numbers text-right" required="required" autocomplete="off"></td>';
            $str .= '<td><input type="text" name="service_buy_comment[]" class="service_buy_comment" required="required"> <i class="fa fa-remove" title="Xóa"></i></td>';
            $str .= '</tr>';

            echo $str;
        }
    }

    public function getserviceadd2(){
        if (isset($_POST['invoice_buy'])) {
            $invoice_service_buy_model = $this->model->get('invoiceservicebuyModel');
            $join = array('table'=>'service_buy','where'=>'service_buy=service_buy_id AND invoice_buy_type=1');
            $invoice_service_buys = $invoice_service_buy_model->getAllInvoice(array('where'=>'invoice_buy='.$_POST['invoice_buy']),$join);

            $str = "";
            $i = 1;
            foreach ($invoice_service_buys as $service) {
                $str .= '<tr id="'.$service->service_buy.$service->invoice_buy.'">';
                $str .= '<td class="width-3">'.$i.'</td>';
                $str .= '<td class="width-10"><input data="'.$service->service_buy.'" value="'.$service->service_buy_document_number.'" type="text" name="service_buy2[]" class="service_buy2" required="required"></td>';
                $str .= '<td class="width-10"><input data="'.$service->invoice_service_buy_money_foreign.'" value="'.$this->lib->formatMoney($service->invoice_service_buy_money).'" type="text" name="service_buy_total2[]" class="service_buy_total2 numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td><input value="'.$service->service_buy_comment.'" type="text" name="service_buy_comment2[]" class="service_buy_comment2" required="required"> <i class="fa fa-remove delcost2" title="Xóa" id="'.$service->service_buy.'" data="'.$service->invoice_buy.'"></i></td>';
                $str .= '</tr>';

                $i++;
            }
            $str .= '<tr>';
            $str .= '<td class="width-3">'.$i.'</td>';
            $str .= '<td class="width-10"><input type="text" name="service_buy2[]" class="service_buy2" required="required"></td>';
            $str .= '<td class="width-10"><input type="text" name="service_buy_total2[]" class="service_buy_total2 numbers text-right" required="required" autocomplete="off"></td>';
            $str .= '<td><input type="text" name="service_buy_comment2[]" class="service_buy_comment2" required="required"> <i class="fa fa-remove" title="Xóa"></i></td>';
            $str .= '</tr>';

            echo $str;
        }
    }

    public function getitemadd(){
        if (isset($_POST['invoice_buy'])) {
            $account_model = $this->model->get('accountModel');
            $house_model = $this->model->get('houseModel');
            $invoice_buy_item_model = $this->model->get('invoicebuyitemModel');
            $join = array('table'=>'items','where'=>'invoice_buy_item=items_id');
            $invoice_buy_items = $invoice_buy_item_model->getAllInvoice(array('where'=>'invoice_buy='.$_POST['invoice_buy']),$join);

            $str = "";
            $str2 = "";
            $i = 1;
            foreach ($invoice_buy_items as $item) {
                $house = $house_model->getHouse($item->invoice_buy_item_house)->house_code;
                $debit = $account_model->getAccount($item->invoice_buy_item_debit)->account_number;
                $credit = $account_model->getAccount($item->invoice_buy_item_credit)->account_number;
                $import_debit = $account_model->getAccount($item->invoice_buy_item_tax_import_debit)->account_number;
                $tax_debit = $account_model->getAccount($item->invoice_buy_item_tax_vat_debit)->account_number;
                $tax_credit = $account_model->getAccount($item->invoice_buy_item_tax_vat_credit)->account_number;
                

                $str .= '<tr>';
                $str .= '<td class="width-3">'.$i.'</td>';
                $str .= '<td class="width-10">
                  <input data="'.$item->invoice_buy_item.'" value="'.$item->items_code.'" type="text" name="invoice_buy_item[]" class="invoice_buy_item left" required="required" placeholder="Nhập mã hoặc tên" autocomplete="off">
                  <button type="button" class="find_item right" title="Danh mục"><i class="fa fa-search"></i></button>
                </td>';
                $str .= '<td>
                  <input value="'.$item->items_name.'" type="text" name="invoice_buy_item_name[]" class="invoice_buy_item_name" required="required" autocomplete="off">
                  <ul class="name_list_id"></ul>
                </td>';
                $str .= '<td class="width-5">
                  <input value="'.$house.'" type="text" name="invoice_buy_item_house[]" class="invoice_buy_item_house keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_4"></ul>
                </td>';
                $str .= '<td class="width-5">
                  <input value="'.$debit.'" type="text" name="invoice_buy_item_debit[]" class="invoice_buy_item_debit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_2"></ul>
                </td>';
                $str .= '<td class="width-7">
                  <input value="'.$credit.'" type="text" name="invoice_buy_item_credit[]" class="invoice_buy_item_credit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_3"></ul>
                </td>';
                $str .= '<td class="width-5"><input value="'.$item->invoice_buy_item_unit.'" type="text" name="invoice_buy_item_unit[]" class="invoice_buy_item_unit" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-5"><input alt="'.$item->items_stuff.'" value="'.$item->invoice_buy_item_number.'" type="text" name="invoice_buy_item_number[]" class="invoice_buy_item_number text-right" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-7"><input value="'.$this->lib->formatMoney($item->invoice_buy_item_price,2).'" type="text" name="invoice_buy_item_price[]" class="invoice_buy_item_price numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-10"><input value="'.$this->lib->formatMoney($item->invoice_buy_item_money).'" type="text" name="invoice_buy_item_money[]" class="invoice_buy_item_money numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-5"><input value="'.$item->invoice_buy_item_custom_cost.'" type="text" name="invoice_buy_item_custom_cost[]" class="invoice_buy_item_custom_cost text-right" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-7"><input value="'.$this->lib->formatMoney($item->invoice_buy_item_custom_cost_money,2).'" type="text" name="invoice_buy_item_custom_cost_money[]" class="invoice_buy_item_custom_cost_money numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-7"><input value="'.$this->lib->formatMoney($item->invoice_buy_item_other_cost,2).'" type="text" name="invoice_buy_item_other_cost[]" class="invoice_buy_item_other_cost numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-10"><input value="'.$this->lib->formatMoney($item->invoice_buy_item_total,2).'" disabled type="text" name="invoice_buy_item_total[]" class="invoice_buy_item_total numbers text-right" required="required" autocomplete="off"></td>';
              $str .= '</tr>';

              $str2 .= '<tr>';
                $str2 .= '<td class="width-3">'.$i.'</td>';
                $str2 .= '<td class="width-10"><input value="'.$item->items_code.'" type="text" name="invoice_buy_item2[]" class="invoice_buy_item2" disabled required="required" autocomplete="off"></td>';
                $str2 .= '<td><input value="'.$item->items_name.'" type="text" name="invoice_buy_item_name2[]" class="invoice_buy_item_name2" disabled required="required" autocomplete="off"></td>';
                $str2 .= '<td class="width-7"><input value="'.$this->lib->formatMoney($item->invoice_buy_item_custom_rate).'" type="text" name="invoice_buy_item_custom_rate[]" class="invoice_buy_item_custom_rate numbers keep-val text-right" required="required" autocomplete="off"></td>';
                $str2 .= '<td class="width-7"><input value="'.$item->invoice_buy_item_tax_import_percent.'" type="text" name="invoice_buy_item_tax_import_percent[]" class="invoice_buy_item_tax_import_percent keep-val text-right" required="required" autocomplete="off"></td>';
                $str2 .= '<td class="width-10"><input value="'.$this->lib->formatMoney($item->invoice_buy_item_tax_import).'" type="text" name="invoice_buy_item_tax_import[]" class="invoice_buy_item_tax_import numbers text-right" required="required" autocomplete="off"></td>';
                $str2 .= '<td class="width-7">
                  <input value="'.$import_debit.'" type="text" name="invoice_buy_item_tax_import_debit[]" class="invoice_buy_item_tax_import_debit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_5"></ul>
                </td>';
                $str2 .= '<td class="width-7"><input value="'.$item->invoice_buy_item_tax_vat_percent.'" type="text" name="invoice_buy_item_tax_vat_percent[]" class="invoice_buy_item_tax_vat_percent keep-val text-right" required="required" autocomplete="off"></td>';
                $str2 .= '<td class="width-10"><input value="'.$this->lib->formatMoney($item->invoice_buy_item_tax_vat).'" type="text" name="invoice_buy_item_tax_vat[]" class="invoice_buy_item_tax_vat numbers text-right" required="required" autocomplete="off"></td>';
                $str2 .= '<td class="width-10">
                  <input value="'.$tax_debit.'" type="text" name="invoice_buy_item_tax_vat_debit[]" class="invoice_buy_item_tax_vat_debit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_6"></ul>
                </td>';
                $str2 .= '<td class="width-7">
                  <input value="'.$tax_credit.'" type="text" name="invoice_buy_item_tax_vat_credit[]" class="invoice_buy_item_tax_vat_credit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_7"></ul>
                </td>';
                $str2 .= '<td ><input value="'.$this->lib->formatMoney($item->invoice_buy_item_tax_total).'" type="text" name="invoice_buy_item_tax_total[]" class="invoice_buy_item_tax_total numbers text-right" required="required" autocomplete="off"></td>';
                
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

    public function deleteservice(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $invoice_service_buy_model = $this->model->get('invoiceservicebuyModel');
            $invoice_service_buy_model->queryInvoice('DELETE FROM invoice_service_buy WHERE invoice_buy_type=2 AND invoice_buy='.$_POST['invoice_buy'].' AND service_buy='.$_POST['service_buy']);
        }
    }
    public function deleteservice2(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $invoice_service_buy_model = $this->model->get('invoiceservicebuyModel');
            $invoice_service_buy_model->queryInvoice('DELETE FROM invoice_service_buy WHERE invoice_buy_type=1 AND invoice_buy='.$_POST['invoice_buy'].' AND service_buy='.$_POST['service_buy']);
        }
    }

    public function delete(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $invoice_buy_model = $this->model->get('invoicebuyModel');
            $invoice_buy_item_model = $this->model->get('invoicebuyitemModel');
            $additional_model = $this->model->get('additionalModel');
            $debit_model = $this->model->get('debitModel');
            $invoice_model = $this->model->get('invoiceModel');
            $stock_model = $this->model->get('stockModel');
            $invoice_service_buy_model = $this->model->get('invoiceservicebuyModel');
            $tax_model = $this->model->get('taxModel');
           
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $invoice_buys = $invoice_buy_model->getInvoice($data);
                    $invoice_buy_items = $invoice_buy_item_model->getAllInvoice(array('where'=>'invoice_buy='.$data));
                    foreach ($invoice_buy_items as $item) {
                        $additional_model->queryAdditional('DELETE FROM additional WHERE invoice_buy_item='.$item->invoice_buy_item_id);
                        $stock_model->queryStock('DELETE FROM stock WHERE invoice_buy_item='.$item->invoice_buy_item_id);
                    }
                    $invoice_buy_item_model->queryInvoice('DELETE FROM invoice_buy_item WHERE invoice_buy='.$data);
                    $debit_model->queryDebit('DELETE FROM debit WHERE invoice_buy='.$data.' AND debit_money='.$invoice_buys->invoice_buy_money);
                    $invoice_model->queryInvoice('DELETE FROM invoice WHERE invoice_buy='.$data);
                    $invoice_service_buy_model->queryInvoice('DELETE FROM invoice_service_buy WHERE invoice_buy='.$data);
                    $tax_model->queryTax('DELETE FROM tax WHERE invoice_buy='.$data);
                       $invoice_buy_model->deleteInvoice($data);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|invoice_buy|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    
                    
                }
                return true;
            }
            else{
                    $invoice_buys = $invoice_buy_model->getInvoice($_POST['data']);
                    $invoice_buy_items = $invoice_buy_item_model->getAllInvoice(array('where'=>'invoice_buy='.$_POST['data']));
                    foreach ($invoice_buy_items as $item) {
                        $additional_model->queryAdditional('DELETE FROM additional WHERE invoice_buy_item='.$item->invoice_buy_item_id);
                        $stock_model->queryStock('DELETE FROM stock WHERE invoice_buy_item='.$item->invoice_buy_item_id);
                    }
                    $invoice_buy_item_model->queryInvoice('DELETE FROM invoice_buy_item WHERE invoice_buy='.$_POST['data']);
                    $debit_model->queryDebit('DELETE FROM debit WHERE invoice_buy='.$_POST['data'].' AND debit_money='.$invoice_buys->invoice_buy_money);
                    $invoice_model->queryInvoice('DELETE FROM invoice WHERE invoice_buy='.$_POST['data']);
                    $invoice_service_buy_model->queryInvoice('DELETE FROM invoice_service_buy WHERE invoice_buy='.$_POST['data']);
                    $tax_model->queryTax('DELETE FROM tax WHERE invoice_buy='.$_POST['data']);
                        $invoice_buy_model->deleteInvoice($_POST['data']);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|invoice_buy|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    
            }
            
        }
    }

    public function import(){
        ini_set('max_execution_time', 2000); //300 seconds = 5 minutes
        $this->view->disableLayout();
        header('Content-Type: text/html; charset=utf-8');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_FILES['import']['name'] != null) {

            require("lib/Classes/PHPExcel/IOFactory.php");
            require("lib/Classes/PHPExcel.php");

            $invoice_buy_model = $this->model->get('invoicebuyModel');
            $invoice_buy_item_model = $this->model->get('invoicebuyitemModel');
            $debit_model = $this->model->get('debitModel');
            $additional_model = $this->model->get('additionalModel');
            $invoice_model = $this->model->get('invoiceModel');
            $account_model = $this->model->get('accountModel');
            $stock_model = $this->model->get('stockModel');
            $items_model = $this->model->get('itemsModel');
            $house_model = $this->model->get('houseModel');
            $tax_model = $this->model->get('taxModel');

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

            $i = 0;
            while ($objPHPExcel->setActiveSheetIndex($i)){
                $objWorksheet = $objPHPExcel->getActiveSheet();

                $nameWorksheet = trim($objWorksheet->getTitle()); // tên sheet là tháng lương (8.2014 => 08/2014)
                
                $highestRow = $objWorksheet->getHighestRow(); // e.g. 10
                $highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'

                $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5

                
                if ($nameWorksheet=="CHITIET-NHAPKHAU") {
                    $cost = array();
                    $money = array();
                    $tax = array();
                    $money_foreign = array();
                    $import = array();
                    $custom_cost = array();
                    $other_cost = array();
                    $number = array();
                    $ids = "0";

                    for ($row = 2; $row <= $highestRow; ++ $row) {
                        $val = array();
                        for ($col = 0; $col < $highestColumnIndex; ++ $col) {
                            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                            // Check if cell is merged
                            foreach ($objWorksheet->getMergeCells() as $cells) {
                                if ($cell->isInRange($cells)) {
                                    $currMergedCellsArray = PHPExcel_Cell::splitRange($cells);
                                    $cell = $objWorksheet->getCell($currMergedCellsArray[0][0]);
                                    break;
                                }
                            }
                            $val[] = trim($cell->getCalculatedValue());
                            //here's my prob..
                            //echo $val;
                        }

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $invoice_buys = $invoice_buy_model->getInvoiceByWhere(array('invoice_buy_document_number'=>$val[1]));
                            if (!$invoice_buys) {
                                $invoice_buys = $invoice_buy_model->getInvoiceByWhere(array('invoice_buy_number'=>$val[1]));
                            }
                            $id_invoice_buy = $invoice_buys->invoice_buy_id;
                            $ids .= ",".$id_invoice_buy;
                            $invoice_buy_item = $items_model->getItemsByWhere(array('items_code'=>$val[2]));
                            $debit = $account_model->getAccountByWhere(array('account_number'=>$val[4]))->account_id;
                            $credit = $account_model->getAccountByWhere(array('account_number'=>$val[5]))->account_id;
                            $tax_debit = $account_model->getAccountByWhere(array('account_number'=>$val[19]))->account_id;
                            $tax_credit = $account_model->getAccountByWhere(array('account_number'=>$val[20]))->account_id;
                            $import_debit = $account_model->getAccountByWhere(array('account_number'=>$val[16]))->account_id;
                            $house = $house_model->getHouseByWhere(array('house_code'=>$val[3]))->house_id;
                            $data_item = array(
                                'invoice_buy' => $id_invoice_buy,
                                'invoice_buy_item' => $invoice_buy_item->items_id,
                                'invoice_buy_item_house' => $house,
                                'invoice_buy_item_unit' => $invoice_buy_item->items_unit,
                                'invoice_buy_item_number' => $val[7],
                                'invoice_buy_item_price' => $val[8],
                                'invoice_buy_item_money' => $val[9],
                                'invoice_buy_item_debit' => $debit,
                                'invoice_buy_item_credit' => $credit,
                                'invoice_buy_item_custom_cost' => $val[10],
                                'invoice_buy_item_custom_cost_money' => $val[11],
                                'invoice_buy_item_other_cost' => $val[12],
                                'invoice_buy_item_custom_rate' => $val[13],
                                'invoice_buy_item_tax_import_percent' => $val[14],
                                'invoice_buy_item_tax_import' => $val[15],
                                'invoice_buy_item_tax_import_debit' => $import_debit,
                                'invoice_buy_item_tax_vat_percent' => $val[17],
                                'invoice_buy_item_tax_vat' => $val[18],
                                'invoice_buy_item_tax_vat_debit' => $tax_debit,
                                'invoice_buy_item_tax_vat_credit' => $tax_credit,
                                'invoice_buy_item_tax_total' => $val[18]+$val[15],
                                'invoice_buy_item_total' => $val[21],
                            );

                            $invoice_buy_items = $invoice_buy_item_model->getInvoiceByWhere(array('invoice_buy'=>$data_item['invoice_buy'],'invoice_buy_item'=>$data_item['invoice_buy_item']));
                            if ($invoice_buy_items) {
                                $invoice_buy_item_model->updateInvoice($data_item,array('invoice_buy_item_id'=>$invoice_buy_items->invoice_buy_item_id));
                                $id_invoice_buy_item = $invoice_buy_items->invoice_buy_item_id;

                                $data_additional = array(
                                    'invoice_buy_item'=>$id_invoice_buy_item,
                                    'document_number'=>$invoice_buys->invoice_buy_document_number,
                                    'document_date'=>$invoice_buys->invoice_buy_document_date,
                                    'additional_date'=>$invoice_buys->invoice_buy_additional_date,
                                    'additional_comment'=>$invoice_buys->invoice_buy_comment,
                                    'debit'=>$data_item['invoice_buy_item_debit'],
                                    'credit'=>$data_item['invoice_buy_item_credit'],
                                    'money'=>($data_item['invoice_buy_item_money']+round($data_item['invoice_buy_item_custom_cost_money']*$data_item['invoice_buy_item_number'],2)+round($data_item['invoice_buy_item_other_cost']*$data_item['invoice_buy_item_number'],2)),
                                );
                                $additional_model->updateAdditional($data_additional,array('invoice_buy_item'=>$id_invoice_buy_item,'debit'=>$invoice_buy_items->invoice_buy_item_debit,'credit'=>$invoice_buy_items->invoice_buy_item_credit));

                                $data_additional = array(
                                    'invoice_buy_item'=>$id_invoice_buy_item,
                                    'document_number'=>$invoice_buys->invoice_buy_document_number,
                                    'document_date'=>$invoice_buys->invoice_buy_document_date,
                                    'additional_date'=>$invoice_buys->invoice_buy_additional_date,
                                    'additional_comment'=>$invoice_buys->invoice_buy_comment,
                                    'debit'=>$data_item['invoice_buy_item_tax_vat_debit'],
                                    'credit'=>$data_item['invoice_buy_item_tax_vat_credit'],
                                    'money'=>$data_item['invoice_buy_item_tax_vat'],
                                );
                                $additional_model->updateAdditional($data_additional,array('invoice_buy_item'=>$id_invoice_buy_item,'debit'=>$invoice_buy_items->invoice_buy_item_tax_vat_debit,'credit'=>$invoice_buy_items->invoice_buy_item_tax_vat_credit));

                                $data_additional = array(
                                    'invoice_buy_item'=>$id_invoice_buy_item,
                                    'document_number'=>$invoice_buys->invoice_buy_document_number,
                                    'document_date'=>$invoice_buys->invoice_buy_document_date,
                                    'additional_date'=>$invoice_buys->invoice_buy_additional_date,
                                    'additional_comment'=>$invoice_buys->invoice_buy_comment,
                                    'debit'=>$data_item['invoice_buy_item_debit'],
                                    'credit'=>$data_item['invoice_buy_item_tax_import_debit'],
                                    'money'=>$data_item['invoice_buy_item_tax_import'],
                                );
                                $additional_model->updateAdditional($data_additional,array('invoice_buy_item'=>$id_invoice_buy_item,'debit'=>$invoice_buy_items->invoice_buy_item_debit,'credit'=>$invoice_buy_items->invoice_buy_item_tax_import_debit));

                                $data_stock = array(
                                    'invoice_buy_item'=>$id_invoice_buy_item,
                                    'stock_date'=>$invoice_buys->invoice_buy_document_date,
                                    'stock_item'=>$data_item['invoice_buy_item'],
                                    'stock_number'=>$data_item['invoice_buy_item_number'],
                                    'stock_price'=>$data_item['invoice_buy_item_total'],
                                    'stock_house'=>$data_item['invoice_buy_item_house'],
                                );
                                $stock_model->updateStock($data_stock,array('invoice_buy_item'=>$id_invoice_buy_item));
                            }
                            else{
                                $invoice_buy_item_model->createInvoice($data_item);
                                $id_invoice_buy_item = $invoice_buy_item_model->getLastInvoice()->invoice_buy_item_id;

                                $data_additional = array(
                                    'invoice_buy_item'=>$id_invoice_buy_item,
                                    'document_number'=>$invoice_buys->invoice_buy_document_number,
                                    'document_date'=>$invoice_buys->invoice_buy_document_date,
                                    'additional_date'=>$invoice_buys->invoice_buy_additional_date,
                                    'additional_comment'=>$invoice_buys->invoice_buy_comment,
                                    'debit'=>$data_item['invoice_buy_item_debit'],
                                    'credit'=>$data_item['invoice_buy_item_credit'],
                                    'money'=>($data_item['invoice_buy_item_money']+round($data_item['invoice_buy_item_custom_cost_money']*$data_item['invoice_buy_item_number'],2)+round($data_item['invoice_buy_item_other_cost']*$data_item['invoice_buy_item_number'],2)),
                                );
                                $additional_model->createAdditional($data_additional);

                                $data_additional = array(
                                    'invoice_buy_item'=>$id_invoice_buy_item,
                                    'document_number'=>$invoice_buys->invoice_buy_document_number,
                                    'document_date'=>$invoice_buys->invoice_buy_document_date,
                                    'additional_date'=>$invoice_buys->invoice_buy_additional_date,
                                    'additional_comment'=>$invoice_buys->invoice_buy_comment,
                                    'debit'=>$data_item['invoice_buy_item_debit'],
                                    'credit'=>$data_item['invoice_buy_item_tax_import_debit'],
                                    'money'=>$data_item['invoice_buy_item_tax_import'],
                                );
                                $additional_model->createAdditional($data_additional);

                                $data_additional = array(
                                    'invoice_buy_item'=>$id_invoice_buy_item,
                                    'document_number'=>$invoice_buys->invoice_buy_document_number,
                                    'document_date'=>$invoice_buys->invoice_buy_document_date,
                                    'additional_date'=>$invoice_buys->invoice_buy_additional_date,
                                    'additional_comment'=>$invoice_buys->invoice_buy_comment,
                                    'debit'=>$data_item['invoice_buy_item_tax_vat_debit'],
                                    'credit'=>$data_item['invoice_buy_item_tax_vat_credit'],
                                    'money'=>$data_item['invoice_buy_item_tax_vat'],
                                );
                                $additional_model->createAdditional($data_additional);

                                $data_stock = array(
                                    'invoice_buy_item'=>$id_invoice_buy_item,
                                    'stock_date'=>$invoice_buys->invoice_buy_document_date,
                                    'stock_item'=>$data_item['invoice_buy_item'],
                                    'stock_number'=>$data_item['invoice_buy_item_number'],
                                    'stock_price'=>$data_item['invoice_buy_item_total'],
                                    'stock_house'=>$data_item['invoice_buy_item_house'],
                                );
                                $stock_model->createStock($data_stock);
                            }

                            


                            if ($val[6]==1) {
                                $money_type[$id_invoice_buy]=1;
                                $money_rate[$id_invoice_buy]=$val[6];
                            }
                            else{
                                $money_type[$id_invoice_buy]=2;
                                $money_rate[$id_invoice_buy]=$val[6];
                            }

                            $money[$id_invoice_buy] = isset($money[$id_invoice_buy])?$money[$id_invoice_buy]+$data_item['invoice_buy_item_money']:$data_item['invoice_buy_item_money'];
                            $tax[$id_invoice_buy] = isset($tax[$id_invoice_buy])?$tax[$id_invoice_buy]+$data_item['invoice_buy_item_tax_vat']:$data_item['invoice_buy_item_tax_vat'];
                            $money_foreign[$id_invoice_buy] = isset($money_foreign[$id_invoice_buy])?$money_foreign[$id_invoice_buy]+$data_item['invoice_buy_item_price']*$data_item['invoice_buy_item_number']:$data_item['invoice_buy_item_price']*$data_item['invoice_buy_item_number'];
                            $import[$id_invoice_buy] = isset($import[$id_invoice_buy])?$import[$id_invoice_buy]+$data_item['invoice_buy_item_tax_import']:$data_item['invoice_buy_item_tax_import'];
                            $custom_cost[$id_invoice_buy] = isset($custom_cost[$id_invoice_buy])?$custom_cost[$id_invoice_buy]+round($data_item['invoice_buy_item_custom_cost_money']*$data_item['invoice_buy_item_number'],2):round($data_item['invoice_buy_item_custom_cost_money']*$data_item['invoice_buy_item_number'],2);
                            $other_cost[$id_invoice_buy] = isset($other_cost[$id_invoice_buy])?$other_cost[$id_invoice_buy]+round($data_item['invoice_buy_item_other_cost']*$data_item['invoice_buy_item_number'],2):round($data_item['invoice_buy_item_other_cost']*$data_item['invoice_buy_item_number'],2);
                            $number[$id_invoice_buy] = isset($number[$id_invoice_buy])?$number[$id_invoice_buy]+$data_item['invoice_buy_item_number']:$data_item['invoice_buy_item_number'];
                        }
                        
                    }//
                    $invoice_buys = $invoice_buy_model->getAllInvoice(array('where'=>'invoice_buy_id IN ('.$ids.')'));
                    foreach ($invoice_buys as $invoice_buy) {
                        $data_buy = array(
                            'invoice_buy_money_type'=>$money_type[$invoice_buy->invoice_buy_id],
                            'invoice_buy_money_rate'=>$money_rate[$invoice_buy->invoice_buy_id],
                            'invoice_buy_number_total'=>$number[$invoice_buy->invoice_buy_id],
                            'invoice_buy_money'=>$money[$invoice_buy->invoice_buy_id],
                            'invoice_buy_money_foreign'=>$money_foreign[$invoice_buy->invoice_buy_id],
                            'invoice_buy_tax_vat'=>$tax[$invoice_buy->invoice_buy_id],
                            'invoice_buy_tax_import'=>$import[$invoice_buy->invoice_buy_id],
                            'invoice_buy_custom_cost'=>$custom_cost[$invoice_buy->invoice_buy_id],
                            'invoice_buy_other_cost'=>$other_cost[$invoice_buy->invoice_buy_id],
                            'invoice_buy_total'=>($money[$invoice_buy->invoice_buy_id]+$tax[$invoice_buy->invoice_buy_id]+$import[$invoice_buy->invoice_buy_id]+$custom_cost[$invoice_buy->invoice_buy_id]+$other_cost[$invoice_buy->invoice_buy_id]),
                        );
                        $invoice_buy_model->updateInvoice($data_buy,array('invoice_buy_id'=>$invoice_buy->invoice_buy_id));

                        $data_debit = array(
                            'invoice_buy'=>$invoice_buy->invoice_buy_id,
                            'debit_date'=>$invoice_buy->invoice_buy_document_date,
                            'debit_customer'=>$invoice_buy->invoice_buy_customer,
                            'debit_money'=>$money[$invoice_buy->invoice_buy_id],
                            'debit_money_foreign'=>$money_foreign[$invoice_buy->invoice_buy_id],
                            'debit_comment'=>$invoice_buy->invoice_buy_comment,
                        );

                        $invoice_buy_debits = $debit_model->getDebitByWhere(array('invoice_buy'=>$invoice_buy->invoice_buy_id,'debit_money'=>$invoice_buy->invoice_buy_money));
                        if ($invoice_buy_debits) {
                            $debit_model->updateDebit($data_debit,array('debit_id'=>$invoice_buy_debits->debit_id));
                        }
                        else{
                            $debit_model->createDebit($data_debit);
                        }

                        $data_invoice = array(
                            'invoice_buy'=>$invoice_buy->invoice_buy_id,
                            'invoice_symbol'=>$invoice_buy->invoice_buy_symbol,
                            'invoice_date'=>$invoice_buy->invoice_buy_date,
                            'invoice_number'=>$invoice_buy->invoice_buy_number,
                            'invoice_customer'=>$invoice_buy->invoice_buy_customer,
                            'invoice_money'=>($money[$invoice_buy->invoice_buy_id]+$custom_cost[$invoice_buy->invoice_buy_id]+$import[$invoice_buy->invoice_buy_id]),
                            'invoice_tax'=>$tax[$invoice_buy->invoice_buy_id],
                            'invoice_comment'=>$invoice_buy->invoice_buy_comment,
                            'invoice_type'=>1,
                        );

                        $invoice_buy_invoices = $invoice_model->getInvoiceByWhere(array('invoice_buy'=>$invoice_buy->invoice_buy_id));
                        if ($invoice_buy_invoices) {
                            $invoice_model->updateInvoice($data_invoice,array('invoice_id'=>$invoice_buy_invoices->invoice_id));
                        }
                        else{
                            $invoice_model->createInvoice($data_invoice);
                        }

                        $data_tax_import = array(
                            'invoice_buy'=>$invoice_buy->invoice_buy_id,
                            'tax_date'=>$invoice_buy->invoice_buy_date,
                            'tax_money'=>$import[$invoice_buy->invoice_buy_id],
                            'tax_comment'=>$invoice_buy->invoice_buy_comment,
                            'tax_type'=>1,
                        );
                        $data_tax_vat = array(
                            'invoice_buy'=>$invoice_buy->invoice_buy_id,
                            'tax_date'=>$invoice_buy->invoice_buy_date,
                            'tax_money'=>$tax[$invoice_buy->invoice_buy_id],
                            'tax_comment'=>$invoice_buy->invoice_buy_comment,
                            'tax_type'=>2,
                        );

                        $tax_imports = $tax_model->getTaxByWhere(array('invoice_buy'=>$invoice_buy->invoice_buy_id,'tax_money'=>$invoice_buy->invoice_buy_tax_import,'tax_type'=>1));
                        if ($tax_imports) {
                            $tax_model->updateTax($data_tax_import,array('tax_id'=>$tax_imports->tax_id));
                        }
                        else if(!$tax_imports){
                            $tax_model->createTax($data_tax_import);
                        }

                        $tax_vats = $tax_model->getTaxByWhere(array('invoice_buy'=>$invoice_buy->invoice_buy_id,'tax_money'=>$invoice_buy->invoice_buy_tax_vat,'tax_type'=>2));
                        if ($tax_vats) {
                            $tax_model->updateTax($data_tax_vat,array('tax_id'=>$tax_vats->tax_id));
                        }
                        else if(!$tax_vats){
                            $tax_model->createTax($data_tax_vat);
                        }

                        
                        
                    }
                    
                }//CHITIET-NHAPKHAU
                
                

                $i++;
            }
            return $this->view->redirect('invoicebuy');
        }
        $this->view->show('invoicebuy/import');
    }


}
?>